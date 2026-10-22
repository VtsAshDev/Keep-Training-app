<?php

use App\Actions\Training\GetTodaySupplements;
use App\Actions\Training\GetTodayTraining;
use App\Actions\Training\GetWeeklyGoalProgress;
use App\Actions\Training\GetWorkoutPlans;
use App\Actions\Training\StartWorkoutSession;
use App\Actions\Training\ToggleSupplementLog;
use App\Exceptions\Training\TrainingRuleException;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    public function start(int $planId, StartWorkoutSession $start): void
    {
        try {
            $session = $start->handle($this->user(), $planId);
        } catch (TrainingRuleException $e) {
            $this->addError('training', $e->getMessage());

            return;
        }

        $this->redirectRoute('training.session', ['session' => $session->id], navigate: true);
    }

    public function toggleSupplement(int $supplementId, ToggleSupplementLog $toggle): void
    {
        try {
            $toggle->handle($this->user(), $supplementId);
        } catch (TrainingRuleException $e) {
            $this->addError('training', $e->getMessage());
        }
    }

    private function user(): User
    {
        /** @var User */
        return Auth::user();
    }

    public function with(): array
    {
        $user = $this->user();

        return [
            'user' => $user,
            'training' => app(GetTodayTraining::class)->handle($user),
            'goal' => app(GetWeeklyGoalProgress::class)->handle($user),
            'supplements' => app(GetTodaySupplements::class)->handle($user),
            'plans' => app(GetWorkoutPlans::class)->handle($user)->active,
        ];
    }
};
?>

@php
    use App\Support\Dates;
    $available = $supplements->where('available', true);
@endphp

<x-ui.page>
    <x-slot:header>
        <header class="flex items-center justify-between px-5 pt-[max(1.5rem,env(safe-area-inset-top))]">
            <x-ui.logo size="sm" />
            <span class="text-sm text-ink-muted">{{ Dates::long(now()) }}</span>
        </header>
    </x-slot>

    <h1 class="sr-only">{{ __('Today') }}</h1>

    @error('training')
        <x-ui.alert>{{ $message }}</x-ui.alert>
    @enderror

    @if ($training->activeSession)
        <x-ui.card variant="brand">
            <div class="flex items-center gap-2 text-sm font-semibold">
                <span class="size-2.5 rounded-full bg-on-brand"></span>{{ __('Workout in progress') }}
            </div>
            <div class="mt-1 font-display text-[40px] leading-tight font-semibold uppercase">{{ __('Plan :letter', ['letter' => $training->activeLetter]) }}</div>
            <div class="text-[15px] tabular-nums">
                {{ __('Started at :time · :done of :total sets', ['time' => $training->activeSession->started_at->format('H:i'), 'done' => $training->activeDoneSets, 'total' => $training->activeTotalSets]) }}
            </div>
            <x-ui.button variant="inverse" :href="route('training.session', $training->activeSession->id)" wire:navigate class="mt-4 w-full">{{ __('Resume workout') }}</x-ui.button>
        </x-ui.card>
    @elseif ($training->suggested)
        <x-ui.card :title="__('Today\'s workout')">
            <div class="mt-1 font-display text-[40px] leading-tight font-semibold uppercase">{{ __('Plan :letter', ['letter' => $training->suggested->letter]) }}</div>
            <div class="text-[15px] text-ink-muted">{{ $training->suggested->plan->name }} · {{ trans_choice(':count exercise|:count exercises', $training->suggested->exerciseCount) }}</div>
            <div class="mt-4 flex gap-2">
                <x-ui.button icon="play" wire:click="start({{ $training->suggested->plan->id }})" class="flex-1">{{ __('Start workout') }}</x-ui.button>
                <x-ui.button variant="secondary" x-data x-on:click="$dispatch('open-modal', 'swap-plan')">{{ __('Swap') }}</x-ui.button>
            </div>
            @if ($training->lastWorkout)
                <div class="mt-3 text-[13px] text-ink-muted">
                    {{ __('Last workout: Plan :letter, :day', ['letter' => $training->lastWorkoutLetter, 'day' => Dates::relative($training->lastWorkout->started_at)]) }}
                </div>
            @endif
        </x-ui.card>

        <x-ui.modal name="swap-plan" :title="__('Swap plan')">
            <p class="mb-3 text-sm text-ink-muted">{{ __('The rotation continues from the plan you actually do.') }}</p>
            <div class="flex flex-col gap-2">
                @foreach ($plans as $item)
                    <x-ui.list-row
                        :letter="$item->letter"
                        :letter-active="$item->isNext"
                        :title="$item->plan->name"
                        :subtitle="trans_choice(':count exercise|:count exercises', $item->exerciseCount)"
                        wire:click="start({{ $item->plan->id }})"
                        class="cursor-pointer hover:bg-surface-2"
                    />
                @endforeach
            </div>
        </x-ui.modal>
    @else
        <div class="pt-2 pb-1">
            <p class="font-display text-[34px] leading-tight font-semibold uppercase">{{ __('Welcome, :name', ['name' => Str::before($user->name, ' ')]) }}</p>
            <p class="mt-1.5 text-[15px]/relaxed text-ink-muted">{{ __('Build your plans and Keep Focus suggests the workout of each day, in rotation.') }}</p>
        </div>
        <x-ui.empty-state :title="__('Create your first plan')" :text="__('E.g. Plan A chest and triceps, Plan B back and biceps, Plan C legs.')">
            <x-ui.button :href="route('training.plans')" wire:navigate>{{ __('Create plan') }}</x-ui.button>
        </x-ui.empty-state>
    @endif

    <x-ui.card :title="__('Weekly goal')">
        <x-slot:aside>
            <span class="font-display text-[22px] font-semibold tabular-nums">{{ $goal->completed }} <span class="text-base text-ink-muted">{{ __('of :goal', ['goal' => $goal->goal]) }}</span></span>
        </x-slot>

        @if ($training->activeSession || ! $training->hasPlans)
            <x-ui.progress-bar class="mt-3" :value="$goal->completed / $goal->goal * 100" :label="__('Weekly goal')" />
            @if ($training->activeSession && ! $goal->met && $goal->completed + 1 >= $goal->goal)
                <p class="mt-2.5 text-sm text-ink-muted">{{ __('Finishing this workout completes the weekly goal.') }}</p>
            @elseif (! $training->hasPlans)
                <a href="{{ route('settings') }}" wire:navigate class="mt-2.5 inline-block text-sm text-brand-text">{{ __('Adjust weekly goal') }}</a>
            @endif
        @else
            <x-ui.week-dots class="mt-3" :days="$goal->days" />
            @if ($goal->streak > 0)
                <p class="mt-3.5 flex items-center gap-1.5 text-sm text-brand-text">
                    <x-ui.icon name="flame" class="size-[18px]" />
                    {{ trans_choice(':count week in a row meeting the goal|:count weeks in a row meeting the goal', $goal->streak) }}
                </p>
            @endif
        @endif
    </x-ui.card>

    <x-ui.card :title="__('Today\'s supplements')" class="py-2">
        @if ($supplements->isEmpty())
            <p class="mt-1.5 text-[15px]">{{ __('Add what you take to check it off here every day.') }}</p>
            <a href="{{ route('training.supplements') }}" wire:navigate class="mt-2.5 mb-2 inline-block text-sm text-brand-text">{{ __('Add supplement') }}</a>
        @else
            <x-slot:aside>
                <span class="text-sm text-ink-muted tabular-nums">{{ __(':done of :total', ['done' => $available->where('taken', true)->count(), 'total' => $available->count()]) }}</span>
            </x-slot>
            <div class="mt-1">
                @foreach ($supplements as $item)
                    @php
                        $detail = collect([App\Support\Weight::format($item->supplement->dose).' '.$item->supplement->unit, $item->supplement->scheduled_time])->filter()->implode(' · ');
                        $trainingDays = $item->supplement->usage_rule === App\Enums\Training\UsageRule::TrainingDays;
                    @endphp
                    <x-ui.supplement-item
                        wire:key="supplement-{{ $item->supplement->id }}"
                        :name="$item->supplement->name"
                        :detail="$detail"
                        :tag="$trainingDays ? ($item->available ? __('Training day') : __('If you train today')) : null"
                        :checked="$item->taken"
                        :disabled="! $item->available"
                        wire:click="toggleSupplement({{ $item->supplement->id }})"
                    />
                @endforeach
            </div>
        @endif
    </x-ui.card>
</x-ui.page>
