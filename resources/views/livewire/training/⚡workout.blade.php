<?php

use App\Actions\Training\FinishWorkoutSession;
use App\Actions\Training\GetWorkoutView;
use App\Actions\Training\RecordWorkoutSet;
use App\Exceptions\Training\TrainingRuleException;
use App\Models\Training\WorkoutSet;
use App\Models\User;
use App\Support\Weight;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public int $sessionId;

    /** @var array<int, array{weight: string, reps: string}> Inputs by set id. */
    public array $sets = [];

    public ?int $focus = null;

    public function mount(int $session, GetWorkoutView $view): void
    {
        $workout = $view->handle($this->user(), $session);

        if (! $workout->session->isInProgress()) {
            $this->redirectRoute('training.summary', ['session' => $workout->session->id], navigate: true);

            return;
        }

        $this->sessionId = $workout->session->id;
        foreach ($workout->session->sets as $set) {
            $this->sets[$set->id] = ['weight' => Weight::format($set->weight_kg), 'reps' => (string) ($set->reps ?? '')];
        }
    }

    /**
     * Inputs save as soon as they change, so closing the app keeps the values.
     */
    public function updatedSets(mixed $value, string $key): void
    {
        [$setId] = explode('.', $key);
        $set = $this->set((int) $setId);

        $this->record($set, $set->completed);
    }

    public function complete(int $setId): void
    {
        $set = $this->set($setId);
        $completing = ! $set->completed;

        if (! $this->record($set, $completing)) {
            return;
        }

        if ($completing) {
            $rest = $this->restFor($set);
            if ($rest > 0) {
                $this->dispatch('rest-timer-start', seconds: $rest);
            }
        }
    }

    public function show(int $exerciseId): void
    {
        $this->focus = $exerciseId;
    }

    public function finish(FinishWorkoutSession $finish): void
    {
        try {
            $finish->handle($this->user(), $this->sessionId);
        } catch (TrainingRuleException) {
            // Already finished elsewhere; the summary is still the right place.
        }

        $this->redirectRoute('training.summary', ['session' => $this->sessionId], navigate: true);
    }

    private function record(WorkoutSet $set, bool $completed): bool
    {
        $input = $this->sets[$set->id] ?? ['weight' => '', 'reps' => ''];
        $reps = trim($input['reps']) === '' ? null : max(0, (int) $input['reps']);

        try {
            app(RecordWorkoutSet::class)->handle($this->user(), $set->id, Weight::parse($input['weight']), $reps, $completed);
        } catch (TrainingRuleException $e) {
            $this->addError('training', $e->getMessage());

            return false;
        }

        return true;
    }

    private function set(int $setId): WorkoutSet
    {
        return WorkoutSet::query()
            ->where('workout_session_id', $this->sessionId)
            ->whereHas('session', fn ($query) => $query->where('user_id', $this->user()->id))
            ->findOrFail($setId);
    }

    private function restFor(WorkoutSet $set): int
    {
        foreach (app(GetWorkoutView::class)->handle($this->user(), $this->sessionId)->exercises as $exercise) {
            if ($exercise->exercise->id === $set->exercise_id) {
                return $exercise->planItem?->rest_seconds ?? 0;
            }
        }

        return 0;
    }

    private function user(): User
    {
        /** @var User */
        return Auth::user();
    }

    public function with(): array
    {
        return ['workout' => app(GetWorkoutView::class)->handle($this->user(), $this->sessionId)];
    }
};
?>

@php
    $current = $workout->current();
    $expandedId = $focus ?? $current?->exercise->id;
    $total = max(1, $workout->totalSets());
@endphp

<x-ui.page :nav="false" class="pb-36">
    <x-slot:header>
        <div class="sticky top-0 z-30 bg-bg">
            <x-ui.top-bar :back="route('today')" :back-label="__('Back to Today')" :title="__('Plan :letter', ['letter' => $workout->letter])">
                <x-slot:meta>
                    <span
                        x-data="{ start: @js($workout->session->started_at->getTimestamp()), now: Date.now() / 1000 }"
                        x-init="setInterval(() => now = Date.now() / 1000, 1000)"
                        x-text="(() => { const s = Math.max(0, Math.floor(now - start)); return String(Math.floor(s / 60)).padStart(2, '0') + ':' + String(s % 60).padStart(2, '0') })()"
                    ></span>
                    · {{ __(':done of :total sets', ['done' => $workout->doneSets(), 'total' => $workout->totalSets()]) }}
                </x-slot>

                <x-ui.button size="sm" variant="outline" wire:click="finish" wire:confirm="{{ __('Finish the workout now?') }}">{{ __('Finish') }}</x-ui.button>
            </x-ui.top-bar>
            <x-ui.progress-bar size="sm" :value="$workout->doneSets() / $total * 100" :label="__('Workout progress')" />
        </div>
    </x-slot>

    @error('training')
        <x-ui.alert>{{ $message }}</x-ui.alert>
    @enderror

    @foreach ($workout->exercises as $exercise)
        @if ($exercise->exercise->id === $expandedId)
            @php $item = $exercise->planItem; @endphp
            <x-ui.card variant="highlight" class="p-4" wire:key="exercise-{{ $exercise->exercise->id }}">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="font-display text-[26px] leading-tight font-semibold uppercase">{{ $exercise->exercise->name }}</div>
                        @if ($item)
                            <div class="mt-0.5 text-sm text-ink-muted">{{ __(':sets sets · :min–:max reps · :rest s', ['sets' => $item->sets, 'min' => $item->reps_min, 'max' => $item->reps_max, 'rest' => $item->rest_seconds]) }}</div>
                        @endif
                    </div>
                    <span class="text-sm text-ink-muted tabular-nums">{{ $exercise->doneSets() }}/{{ $exercise->sets->count() }}</span>
                </div>

                @if ($exercise->suggestion && $item)
                    <div class="mt-3 flex items-center gap-2 rounded-[10px] bg-brand-soft px-3 py-2.5 text-sm text-brand-soft-ink">
                        <x-ui.icon name="arrow-up" class="size-[18px]" :stroke="2.2" />
                        {{ __('You hit :reps reps on every set. Try :weight kg.', ['reps' => $item->reps_max, 'weight' => App\Support\Weight::format($exercise->suggestion->suggestedWeightKg)]) }}
                    </div>
                @endif

                <x-ui.set-table class="mt-3.5">
                    @php $firstPending = $exercise->sets->firstWhere('completed', false)?->id; @endphp
                    @foreach ($exercise->sets as $set)
                        @php $previous = $exercise->previous->get($set->set_number); @endphp
                        <x-ui.set-row
                            wire:key="set-{{ $set->id }}"
                            :number="$set->set_number"
                            :previous="$previous ? App\Support\Weight::format($previous->weight_kg).' × '.$previous->reps : null"
                            :weight="$sets[$set->id]['weight'] ?? ''"
                            :reps="$sets[$set->id]['reps'] ?? ''"
                            :state="$set->completed ? 'done' : ($set->id === $firstPending ? 'current' : 'pending')"
                            weight-model="sets.{{ $set->id }}.weight"
                            reps-model="sets.{{ $set->id }}.reps"
                            action="complete({{ $set->id }})"
                        />
                    @endforeach
                </x-ui.set-table>
            </x-ui.card>
        @else
            <button type="button" wire:key="exercise-{{ $exercise->exercise->id }}" wire:click="show({{ $exercise->exercise->id }})" class="flex min-h-12 items-center gap-2.5 rounded-xl border border-line bg-surface px-3.5 text-left">
                @if ($exercise->isDone())
                    <x-ui.icon name="check" :stroke="2.4" class="text-brand-500" />
                @else
                    <span class="size-5 rounded-full border-2 border-line"></span>
                @endif
                <span @class(['flex-1', 'text-ink-muted' => $exercise->isDone()])>{{ $exercise->exercise->name }}</span>
                <span class="text-sm text-ink-muted tabular-nums">{{ $exercise->doneSets() }}/{{ $exercise->sets->count() }}</span>
            </button>
        @endif
    @endforeach

    @if ($workout->exercises === [])
        <x-ui.empty-state :title="__('No exercises in this plan')" :text="__('Add exercises to the plan to log the sets here.')" />
    @endif

    <x-slot:footer>
        <x-ui.rest-timer />
    </x-slot>
</x-ui.page>
