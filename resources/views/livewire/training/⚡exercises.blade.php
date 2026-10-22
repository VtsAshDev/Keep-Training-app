<?php

use App\Actions\Training\CreateExercise;
use App\Actions\Training\DeleteExercise;
use App\Actions\Training\GetExercises;
use App\Actions\Training\UpdateExercise;
use App\Enums\Training\Equipment;
use App\Enums\Training\MuscleGroup;
use App\Exceptions\Training\TrainingRuleException;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    public string $search = '';

    #[Locked]
    public ?int $editingId = null;

    public string $name = '';

    public string $muscleGroup = '';

    public string $equipment = '';

    public function create(): void
    {
        $this->resetForm();
        $this->dispatch('open-modal', name: 'exercise-form');
    }

    public function edit(int $exerciseId): void
    {
        $exercise = $this->user()->exercises()->findOrFail($exerciseId);

        $this->resetValidation();
        $this->editingId = $exercise->id;
        $this->name = $exercise->name;
        $this->muscleGroup = $exercise->muscle_group->value;
        $this->equipment = $exercise->equipment->value ?? '';
        $this->dispatch('open-modal', name: 'exercise-form');
    }

    public function save(CreateExercise $create, UpdateExercise $update): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:80'],
            'muscleGroup' => ['required', Rule::enum(MuscleGroup::class)],
            'equipment' => ['nullable', Rule::enum(Equipment::class)],
        ]);

        $group = MuscleGroup::from($data['muscleGroup']);
        $equipment = Equipment::tryFrom((string) $data['equipment']);

        $this->editingId === null
            ? $create->handle($this->user(), $data['name'], $group, $equipment)
            : $update->handle($this->user(), $this->editingId, $data['name'], $group, $equipment);

        $this->resetForm();
        $this->dispatch('close-modal', name: 'exercise-form');
    }

    public function delete(DeleteExercise $delete): void
    {
        if ($this->editingId === null) {
            return;
        }

        try {
            $delete->handle($this->user(), $this->editingId);
        } catch (TrainingRuleException $e) {
            $this->addError('name', $e->getMessage());

            return;
        }

        $this->resetForm();
        $this->dispatch('close-modal', name: 'exercise-form');
    }

    private function resetForm(): void
    {
        $this->reset('editingId', 'name', 'muscleGroup', 'equipment');
        $this->resetValidation();
    }

    private function user(): User
    {
        /** @var User */
        return Auth::user();
    }

    public function with(): array
    {
        return [
            'groups' => app(GetExercises::class)->handle($this->user(), $this->search),
            'muscleGroups' => MuscleGroup::cases(),
            'equipments' => Equipment::cases(),
        ];
    }
};
?>

<x-ui.page>
    <x-slot:header>
        <x-training.header active="exercises" />
    </x-slot>

    <x-ui.input :label="__('Search')" hide-label type="search" :placeholder="__('Search exercise')" wire:model.live.debounce.250ms="search" />

    @forelse ($groups as $group => $exercises)
        <x-ui.section-label>{{ App\Enums\Training\MuscleGroup::from($group)->label() }}</x-ui.section-label>
        @foreach ($exercises as $exercise)
            <x-ui.list-row
                wire:key="exercise-{{ $exercise->id }}"
                class="min-h-15"
                :title="$exercise->name"
                :subtitle="$exercise->equipment?->label() ?? ($exercise->user_id === null ? __('Default catalog') : null)"
            >
                @if ($exercise->user_id !== null)
                    <x-slot:trailing>
                        <button type="button" wire:click="edit({{ $exercise->id }})" aria-label="{{ __('Edit :name', ['name' => $exercise->name]) }}" class="grid size-11 place-items-center rounded-xl text-ink-muted hover:bg-surface-2">
                            <x-ui.icon name="edit" />
                        </button>
                    </x-slot>
                @endif
            </x-ui.list-row>
        @endforeach
    @empty
        <x-ui.empty-state
            :title="$search === '' ? __('Register your exercises') : __('No exercise found')"
            :text="$search === '' ? __('Add the exercises you do to build your plans.') : __('Create it with the button below.')"
        />
    @endforelse

    <x-ui.button variant="add" wire:click="create">{{ __('New exercise') }}</x-ui.button>

    <x-ui.modal name="exercise-form" :title="$editingId ? __('Edit exercise') : __('New exercise')">
        <form wire:submit="save" class="flex flex-col gap-3">
            <x-ui.input :label="__('Name')" wire:model="name" maxlength="80" />
            <x-ui.select :label="__('Muscle group')" wire:model="muscleGroup">
                <option value="">{{ __('Choose') }}</option>
                @foreach ($muscleGroups as $option)
                    <option value="{{ $option->value }}">{{ $option->label() }}</option>
                @endforeach
            </x-ui.select>
            @error('muscleGroup') <p class="text-sm text-danger">{{ $message }}</p> @enderror
            <x-ui.select :label="__('Equipment')" wire:model="equipment">
                <option value="">{{ __('None') }}</option>
                @foreach ($equipments as $option)
                    <option value="{{ $option->value }}">{{ $option->label() }}</option>
                @endforeach
            </x-ui.select>

            <div class="mt-1 flex gap-2 *:flex-1">
                @if ($editingId)
                    <x-ui.button variant="secondary" wire:click="delete" wire:confirm="{{ __('Delete this exercise?') }}">{{ __('Delete') }}</x-ui.button>
                @endif
                <x-ui.button type="submit">{{ __('Save') }}</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</x-ui.page>
