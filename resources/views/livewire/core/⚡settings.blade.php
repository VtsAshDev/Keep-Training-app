<?php

use App\Actions\Core\GetLoginMethod;
use App\Actions\Core\UpdateWeeklyGoal;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    public int $goal = 4;

    public function mount(): void
    {
        $this->goal = $this->user()->weekly_workout_goal;
    }

    public function updatedGoal(): void
    {
        $this->goal = max(UpdateWeeklyGoal::MIN, min(UpdateWeeklyGoal::MAX, $this->goal));

        app(UpdateWeeklyGoal::class)->handle($this->user(), $this->goal);
    }

    private function user(): User
    {
        /** @var User */
        return Auth::user();
    }

    public function with(): array
    {
        return [
            'user' => $this->user(),
            'loginMethod' => app(GetLoginMethod::class)->handle($this->user()),
        ];
    }
};
?>

<x-ui.page>
    <x-slot:header>
        <x-ui.page-header :title="__('Settings')" />
    </x-slot>

    <x-ui.card>
        <h2 class="font-medium">{{ __('Weekly workout goal') }}</h2>
        <p class="mt-0.5 text-sm/relaxed text-ink-muted">{{ __('Weeks run Monday to Sunday. The streak counts the weeks in which you hit the goal.') }}</p>
        <x-ui.stepper
            class="mt-3.5"
            size="lg"
            wire:model.live="goal"
            :value="$goal"
            :min="App\Actions\Core\UpdateWeeklyGoal::MIN"
            :max="App\Actions\Core\UpdateWeeklyGoal::MAX"
            :unit="__('workouts per week')"
            :decrease-label="__('Lower goal')"
            :increase-label="__('Raise goal')"
        />
    </x-ui.card>

    <x-ui.card :title="__('Account')">
        <div class="mt-2.5 flex items-center gap-3">
            <span class="grid size-12 place-items-center rounded-full bg-brand-soft font-display text-[22px] font-semibold text-brand-soft-ink">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
            <span class="min-w-0 flex-1">
                <span class="block truncate font-medium">{{ $user->name }}</span>
                <span class="block truncate text-[13px] text-ink-muted">{{ $user->email }}</span>
            </span>
        </div>
        @if ($loginMethod)
            <p class="mt-3 text-[13px] text-ink-muted">{{ $loginMethod->label() }}</p>
        @endif
    </x-ui.card>

    <x-ui.card class="py-1">
        <div class="flex min-h-13 items-center justify-between border-b border-line">
            <span>{{ __('Appearance') }}</span>
            <span class="text-sm text-ink-muted">{{ __('Follows the system') }}</span>
        </div>
        <div class="flex min-h-13 items-center justify-between">
            <span>{{ __('Weight unit') }}</span>
            <span class="text-sm text-ink-muted">kg</span>
        </div>
    </x-ui.card>

    <form method="POST" action="{{ route('logout') }}" class="mt-1 flex flex-col">
        @csrf
        <x-ui.button type="submit" variant="secondary">{{ __('Sign out') }}</x-ui.button>
    </form>

    <p class="text-center text-xs text-ink-muted">{{ config('app.name') }} · {{ __('version :version', ['version' => config('app.version')]) }}</p>
</x-ui.page>
