<?php

use App\Actions\Training\GetWorkoutSummary;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public int $sessionId;

    public bool $allExercises = false;

    public function mount(int $session, GetWorkoutSummary $summary): void
    {
        $workout = $summary->handle($this->user(), $session);

        if ($workout->session->isInProgress()) {
            $this->redirectRoute('training.session', ['session' => $workout->session->id], navigate: true);

            return;
        }

        $this->sessionId = $workout->session->id;
    }

    private function user(): User
    {
        /** @var User */
        return Auth::user();
    }

    public function with(): array
    {
        return ['summary' => app(GetWorkoutSummary::class)->handle($this->user(), $this->sessionId)];
    }
};
?>

@php
    $exercises = $allExercises ? $summary->exercises : array_slice($summary->exercises, 0, 3);
    $hidden = count($summary->exercises) - count($exercises);
    $plan = $summary->session->plan;
@endphp

<x-ui.page :nav="false">
    <div class="pt-[max(2.5rem,env(safe-area-inset-top))]">
        <span class="grid size-14 place-items-center rounded-full bg-brand-500 text-on-brand">
            <x-ui.icon name="check" :stroke="3" class="size-7.5" />
        </span>
        <h1 class="mt-4 font-display text-[40px] leading-tight font-bold uppercase italic">{{ __('Workout completed') }}</h1>
        <p class="mt-1 text-[15px] text-ink-muted">
            {{ collect([__('Plan :letter', ['letter' => $summary->letter]), $plan?->name, App\Support\Dates::long($summary->session->started_at)])->filter()->implode(' · ') }}
        </p>
    </div>

    <div class="grid grid-cols-3 gap-2">
        <x-ui.stat boxed :label="__('Duration')" :value="$summary->durationMinutes" unit="min" />
        <x-ui.stat boxed :label="__('Volume')" :value="App\Support\Weight::format($summary->volumeKg, 0)" unit="kg" />
        <x-ui.stat boxed :label="__('Sets')" :value="$summary->completedSets" />
    </div>

    @if ($summary->records !== [])
        <x-ui.record-banner
            :title="trans_choice(':count record broken|:count records broken', count($summary->records))"
            :records="array_map(fn ($record) => ['label' => $record->exercise->name, 'value' => App\Support\Weight::format($record->weightKg).' kg × '.$record->reps], $summary->records)"
        />
    @endif

    @if ($summary->exercises !== [])
        <x-ui.card class="px-4 py-1.5">
            @foreach ($exercises as $exercise)
                <div class="flex min-h-11 items-center justify-between gap-3 border-b border-line text-[15px] last:border-b-0">
                    <span>{{ $exercise['name'] }}</span>
                    <span class="text-ink-muted tabular-nums">{{ $exercise['sets'] }} × {{ App\Support\Weight::format($exercise['topWeightKg']) }} kg</span>
                </div>
            @endforeach
            @if ($hidden > 0)
                <button type="button" wire:click="$set('allExercises', true)" class="flex min-h-11 w-full items-center justify-between text-[15px]">
                    <span>{{ trans_choice('+ :count exercise|+ :count exercises', $hidden) }}</span>
                    <span class="text-ink-muted">{{ __('See all') }}</span>
                </button>
            @endif
        </x-ui.card>
    @endif

    <div class="flex items-center gap-2.5 text-[15px]">
        <span class="font-display text-[22px] font-semibold text-brand-text tabular-nums">{{ __(':done of :total', ['done' => $summary->weeklyGoal->completed, 'total' => $summary->weeklyGoal->goal]) }}</span>
        <span class="text-ink-muted">{{ $summary->weeklyGoal->met ? __('Weekly goal met') : __('Weekly goal') }}</span>
    </div>

    <x-slot:footer>
        <x-ui.action-bar>
            <x-ui.button :href="route('today')" wire:navigate>{{ __('Back to Today') }}</x-ui.button>
        </x-ui.action-bar>
    </x-slot>
</x-ui.page>
