<?php

use App\Actions\Training\ArchiveWorkoutPlan;
use App\Actions\Training\CreateExercise;
use App\Actions\Training\GetExercises;
use App\Actions\Training\GetWorkoutPlan;
use App\Actions\Training\GetWorkoutPlans;
use App\Actions\Training\RestoreWorkoutPlan;
use App\Actions\Training\SaveWorkoutPlan;
use App\Enums\Training\MuscleGroup;
use App\Models\Training\Exercise;
use App\Models\Training\WorkoutPlanExercise;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public int $planId;

    #[Locked]
    public bool $archived = false;

    public string $name = '';

    /** @var array<string, array{exercise_id: int, name: string, sets: int, reps_min: int|string, reps_max: int|string, rest_seconds: int}> */
    public array $items = [];

    public ?string $expanded = null;

    public string $search = '';

    public string $newExerciseName = '';

    public string $newExerciseGroup = '';

    public function mount(int $plan, GetWorkoutPlan $getPlan): void
    {
        $workoutPlan = $getPlan->handle($this->user(), $plan);

        $this->planId = $workoutPlan->id;
        $this->archived = $workoutPlan->isArchived();
        $this->name = $workoutPlan->name;
        $this->items = $workoutPlan->exercises
            ->mapWithKeys(fn (WorkoutPlanExercise $item) => [(string) Str::uuid() => [
                'exercise_id' => $item->exercise_id,
                'name' => $item->exercise->name,
                'sets' => $item->sets,
                'reps_min' => $item->reps_min,
                'reps_max' => $item->reps_max,
                'rest_seconds' => $item->rest_seconds,
            ]])
            ->all();
    }

    public function toggle(string $key): void
    {
        $this->expanded = $this->expanded === $key ? null : $key;
    }

    public function addExercise(int $exerciseId): void
    {
        $exercise = Exercise::query()->availableTo($this->user())->findOrFail($exerciseId);

        $key = (string) Str::uuid();
        $this->items[$key] = [
            'exercise_id' => $exercise->id,
            'name' => $exercise->name,
            'sets' => 3,
            'reps_min' => 8,
            'reps_max' => 12,
            'rest_seconds' => 90,
        ];
        $this->expanded = $key;
        $this->search = '';
        $this->dispatch('close-modal', name: 'add-exercise');
    }

    public function createExercise(CreateExercise $create): void
    {
        $data = $this->validate([
            'newExerciseName' => ['required', 'string', 'max:80'],
            'newExerciseGroup' => ['required', Rule::enum(MuscleGroup::class)],
        ]);

        $exercise = $create->handle($this->user(), $data['newExerciseName'], MuscleGroup::from($data['newExerciseGroup']));
        $this->reset('newExerciseName', 'newExerciseGroup');

        $this->addExercise($exercise->id);
    }

    public function removeItem(string $key): void
    {
        unset($this->items[$key]);
    }

    public function moveItem(string $key, int $index): void
    {
        $item = $this->items[$key] ?? null;

        if ($item === null) {
            return;
        }

        $rest = array_diff_key($this->items, [$key => true]);
        $this->items = array_slice($rest, 0, $index, true) + [$key => $item] + array_slice($rest, $index, null, true);
    }

    public function save(SaveWorkoutPlan $save): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:60'],
            'items.*.sets' => ['required', 'integer', 'min:1', 'max:10'],
            'items.*.reps_min' => ['required', 'integer', 'min:1', 'max:100'],
            'items.*.reps_max' => ['required', 'integer', 'min:1', 'max:100'],
            'items.*.rest_seconds' => ['required', 'integer', 'min:0', 'max:600'],
        ]);

        $save->handle($this->user(), $this->planId, $this->name, array_values(array_map(fn (array $item) => [
            'exercise_id' => (int) $item['exercise_id'],
            'sets' => (int) $item['sets'],
            'reps_min' => (int) $item['reps_min'],
            'reps_max' => (int) $item['reps_max'],
            'rest_seconds' => (int) $item['rest_seconds'],
        ], $this->items)));

        $this->redirectRoute('training.plans', navigate: true);
    }

    public function archive(ArchiveWorkoutPlan $archive): void
    {
        $archive->handle($this->user(), $this->planId);

        $this->redirectRoute('training.plans', navigate: true);
    }

    public function restore(RestoreWorkoutPlan $restore): void
    {
        $restore->handle($this->user(), $this->planId);

        $this->redirectRoute('training.plans', navigate: true);
    }

    private function user(): User
    {
        /** @var User */
        return Auth::user();
    }

    public function with(): array
    {
        $user = $this->user();
        $plan = app(GetWorkoutPlan::class)->handle($user, $this->planId);

        return [
            'letter' => app(GetWorkoutPlans::class)->handle($user)->letterFor($plan),
            'catalog' => app(GetExercises::class)->handle($user, $this->search),
            'groups' => MuscleGroup::cases(),
        ];
    }
};
?>

<x-ui.page :nav="false">
    <x-slot:header>
        <x-ui.top-bar :back="route('training.plans')" :back-label="__('Back to plans')" :title="$archived ? $name : __('Plan :letter', ['letter' => $letter])">
            <button type="button" x-data x-on:click="$dispatch('open-modal', 'plan-options')" aria-label="{{ __('More options') }}" class="grid size-11 place-items-center rounded-xl text-ink hover:bg-surface-2">
                <x-ui.icon name="more" class="size-[22px]" :stroke="2.4" />
            </button>
        </x-ui.top-bar>
    </x-slot>

    <x-ui.input :label="__('Plan name')" wire:model="name" maxlength="60" />

    <x-ui.section-label class="mt-1.5">{{ __('Exercises · :count', ['count' => count($items)]) }}</x-ui.section-label>

    <div wire:sort="moveItem" class="flex flex-col gap-2.5">
        @foreach ($items as $key => $item)
            <div wire:key="item-{{ $key }}" wire:sort:item="{{ $key }}">
                @if ($expanded === $key)
                    <x-ui.card variant="highlight" class="p-3.5">
                        <div class="flex items-center justify-between gap-2">
                            <button type="button" wire:click="toggle('{{ $key }}')" class="min-h-9 flex-1 text-left font-medium">{{ $item['name'] }}</button>
                            <x-ui.button size="sm" variant="ghost" wire:click="removeItem('{{ $key }}')" class="min-h-9 px-2 text-sm text-ink-muted">{{ __('Remove') }}</x-ui.button>
                        </div>

                        <div class="mt-2 grid grid-cols-2 gap-2.5">
                            <x-ui.stepper :label="__('Sets')" wire:model="items.{{ $key }}.sets" :value="(int) $item['sets']" :min="1" :max="10" :decrease-label="__('One set less')" :increase-label="__('One set more')" />
                            <x-ui.stepper :label="__('Rest')" wire:model="items.{{ $key }}.rest_seconds" :value="(int) $item['rest_seconds']" :min="0" :max="600" :step="15" unit="s" :decrease-label="__('15 seconds less')" :increase-label="__('15 seconds more')" />
                        </div>

                        <div class="mt-2.5 mb-1 text-xs text-ink-muted">{{ __('Rep range') }}</div>
                        <div class="flex items-center gap-2">
                            <x-ui.input numeric hide-label :label="__('Minimum reps')" inputmode="numeric" wire:model="items.{{ $key }}.reps_min" />
                            <span class="text-ink-muted">{{ __('to') }}</span>
                            <x-ui.input numeric hide-label :label="__('Maximum reps')" inputmode="numeric" wire:model="items.{{ $key }}.reps_max" />
                        </div>
                    </x-ui.card>
                @else
                    <x-ui.list-row
                        handle
                        class="min-h-15 cursor-pointer"
                        :title="$item['name']"
                        :subtitle="__(':sets × :min–:max · :rest s', ['sets' => $item['sets'], 'min' => $item['reps_min'], 'max' => $item['reps_max'], 'rest' => $item['rest_seconds']])"
                        wire:click="toggle('{{ $key }}')"
                    />
                @endif
            </div>
        @endforeach
    </div>

    @if ($items === [])
        <p class="text-sm text-ink-muted">{{ __('No exercises yet. Add the first one below.') }}</p>
    @endif

    @if ($errors->any())
        <x-ui.alert>{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-slot:footer>
        <x-ui.action-bar>
            <x-ui.button variant="secondary" x-data x-on:click="$dispatch('open-modal', 'add-exercise')">{{ __('Add exercise') }}</x-ui.button>
            <x-ui.button wire:click="save">{{ __('Save') }}</x-ui.button>
        </x-ui.action-bar>
    </x-slot>

    <x-ui.modal name="add-exercise" :title="__('Add exercise')">
        <div class="flex flex-col gap-3">
            <x-ui.input :label="__('Search')" hide-label :placeholder="__('Search exercise')" wire:model.live.debounce.250ms="search" type="search" />

            <div class="flex max-h-72 flex-col gap-1 overflow-y-auto">
                @forelse ($catalog as $group => $exercises)
                    <x-ui.section-label>{{ App\Enums\Training\MuscleGroup::from($group)->label() }}</x-ui.section-label>
                    @foreach ($exercises as $exercise)
                        <button type="button" wire:key="pick-{{ $exercise->id }}" wire:click="addExercise({{ $exercise->id }})" class="flex min-h-11 items-center justify-between rounded-xl px-3 text-left hover:bg-surface-2">
                            <span>{{ $exercise->name }}</span>
                            <x-ui.icon name="plus" class="text-brand-text" />
                        </button>
                    @endforeach
                @empty
                    <p class="text-sm text-ink-muted">{{ __('No exercise found. Create it below.') }}</p>
                @endforelse
            </div>

            <form wire:submit="createExercise" class="flex flex-col gap-2.5 border-t border-line pt-3">
                <x-ui.section-label class="mt-0">{{ __('New exercise') }}</x-ui.section-label>
                <x-ui.input :label="__('Name')" wire:model="newExerciseName" maxlength="80" />
                <x-ui.select :label="__('Muscle group')" wire:model="newExerciseGroup">
                    <option value="">{{ __('Choose') }}</option>
                    @foreach ($groups as $group)
                        <option value="{{ $group->value }}">{{ $group->label() }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.button type="submit" variant="secondary">{{ __('Create and add') }}</x-ui.button>
            </form>
        </div>
    </x-ui.modal>

    <x-ui.modal name="plan-options" :title="__('Plan options')">
        @if ($archived)
            <p class="mb-3 text-sm text-ink-muted">{{ __('This plan is archived and out of the rotation.') }}</p>
            <x-ui.button wire:click="restore" class="w-full">{{ __('Restore to rotation') }}</x-ui.button>
        @else
            <p class="mb-3 text-sm text-ink-muted">{{ __('Archived plans leave the rotation but keep their history.') }}</p>
            <x-ui.button variant="secondary" wire:click="archive" class="w-full">{{ __('Archive plan') }}</x-ui.button>
        @endif
    </x-ui.modal>
</x-ui.page>
