<?php

use App\Actions\Training\CreateWorkoutPlan;
use App\Actions\Training\GetWorkoutPlans;
use App\Actions\Training\MoveWorkoutPlan;
use App\Actions\Training\RestoreWorkoutPlan;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    public function create(CreateWorkoutPlan $create): void
    {
        $plan = $create->handle($this->user(), __('New plan'));

        $this->redirectRoute('training.plans.edit', ['plan' => $plan->id], navigate: true);
    }

    public function move(int $planId, int $index, MoveWorkoutPlan $move): void
    {
        $move->handle($this->user(), $planId, $index);
    }

    public function restore(int $planId, RestoreWorkoutPlan $restore): void
    {
        $restore->handle($this->user(), $planId);
    }

    private function user(): User
    {
        /** @var User */
        return Auth::user();
    }

    public function with(): array
    {
        return ['overview' => app(GetWorkoutPlans::class)->handle($this->user())];
    }
};
?>

@php
    use App\Support\Dates;
    $subtitle = fn ($item) => $item->isNext
        ? __('Next in rotation')
        : collect([
            trans_choice(':count exercise|:count exercises', $item->exerciseCount),
            $item->lastDoneAt ? Dates::relative($item->lastDoneAt) : null,
        ])->filter()->implode(' · ');
@endphp

<x-ui.page>
    <x-slot:header>
        <x-training.header active="plans" />
    </x-slot>

    @if ($overview->active === [])
        <x-ui.empty-state :title="__('Create your first plan')" :text="__('E.g. Plan A chest and triceps, Plan B back and biceps, Plan C legs.')">
            <x-ui.button wire:click="create">{{ __('Create plan') }}</x-ui.button>
        </x-ui.empty-state>
    @else
        <p class="text-sm text-ink-muted">{{ __('Rotation order · drag to reorder') }}</p>

        <div wire:sort="move" class="flex flex-col gap-2.5">
            @foreach ($overview->active as $item)
                <x-ui.list-row
                    wire:key="plan-{{ $item->plan->id }}"
                    wire:sort:item="{{ $item->plan->id }}"
                    :href="route('training.plans.edit', $item->plan->id)"
                    wire:navigate
                    handle
                    class="min-h-18"
                    :letter="$item->letter"
                    :letter-active="$item->isNext"
                    :highlight="$item->isNext"
                    :accent-subtitle="$item->isNext"
                    :title="$item->plan->name"
                    :subtitle="$subtitle($item)"
                />
            @endforeach
        </div>

        <x-ui.button variant="add" wire:click="create">{{ __('New plan') }}</x-ui.button>
    @endif

    @if ($overview->archived !== [])
        <div x-data="{ open: false }">
            <button type="button" x-on:click="open = ! open" x-bind:aria-expanded="open" class="flex min-h-12 w-full items-center justify-between px-1 text-sm text-ink-muted">
                <span>{{ __('Archived (:count)', ['count' => count($overview->archived)]) }}</span>
                <x-ui.icon name="chevron-down" class="size-[18px] transition" x-bind:class="open && 'rotate-180'" />
            </button>

            <div x-show="open" x-cloak class="flex flex-col gap-2.5">
                @foreach ($overview->archived as $item)
                    <x-ui.list-row wire:key="archived-{{ $item->plan->id }}" :letter="$item->letter" :title="$item->plan->name" :subtitle="trans_choice(':count exercise|:count exercises', $item->exerciseCount)">
                        <x-slot:trailing>
                            <x-ui.button size="sm" variant="ghost" wire:click="restore({{ $item->plan->id }})">{{ __('Restore') }}</x-ui.button>
                        </x-slot>
                    </x-ui.list-row>
                @endforeach
            </div>
        </div>
    @endif
</x-ui.page>
