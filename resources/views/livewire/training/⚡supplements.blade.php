<?php

use App\Actions\Training\DeleteSupplement;
use App\Actions\Training\GetSupplements;
use App\Actions\Training\SaveSupplement;
use App\Enums\Training\UsageRule;
use App\Models\User;
use App\Support\Weight;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public ?int $editingId = null;

    public string $name = '';

    public string $dose = '';

    public string $unit = '';

    public string $doseNotes = '';

    public string $scheduledTime = '';

    public string $usageRule = 'daily';

    public string $notes = '';

    public function create(): void
    {
        $this->resetForm();
        $this->dispatch('open-modal', name: 'supplement-form');
    }

    public function edit(int $supplementId): void
    {
        $supplement = $this->user()->supplements()->findOrFail($supplementId);

        $this->resetValidation();
        $this->editingId = $supplement->id;
        $this->name = $supplement->name;
        $this->dose = Weight::format($supplement->dose);
        $this->unit = $supplement->unit;
        $this->doseNotes = (string) $supplement->dose_notes;
        $this->scheduledTime = (string) $supplement->scheduled_time;
        $this->usageRule = $supplement->usage_rule->value;
        $this->notes = (string) $supplement->notes;
        $this->dispatch('open-modal', name: 'supplement-form');
    }

    public function save(SaveSupplement $save): void
    {
        $this->dose = str_replace(',', '.', trim($this->dose));

        $data = $this->validate([
            'name' => ['required', 'string', 'max:80'],
            'dose' => ['required', 'numeric', 'min:0.01', 'max:99999'],
            'unit' => ['required', 'string', 'max:30'],
            'doseNotes' => ['nullable', 'string', 'max:120'],
            'scheduledTime' => ['nullable', 'string', 'max:60'],
            'usageRule' => ['required', Rule::enum(UsageRule::class)],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $save->handle($this->user(), $this->editingId, [
            'name' => $data['name'],
            'dose' => $data['dose'],
            'unit' => $data['unit'],
            'dose_notes' => $data['doseNotes'] ?: null,
            'scheduled_time' => $data['scheduledTime'] ?: null,
            'usage_rule' => UsageRule::from($data['usageRule']),
            'notes' => $data['notes'] ?: null,
        ]);

        $this->resetForm();
        $this->dispatch('close-modal', name: 'supplement-form');
    }

    public function delete(DeleteSupplement $delete): void
    {
        if ($this->editingId !== null) {
            $delete->handle($this->user(), $this->editingId);
        }

        $this->resetForm();
        $this->dispatch('close-modal', name: 'supplement-form');
    }

    private function resetForm(): void
    {
        $this->reset('editingId', 'name', 'dose', 'unit', 'doseNotes', 'scheduledTime', 'usageRule', 'notes');
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
            'groups' => app(GetSupplements::class)->handle($this->user()),
            'rules' => UsageRule::cases(),
        ];
    }
};
?>

@php
    use App\Support\Weight;
@endphp

<x-ui.page>
    <x-slot:header>
        <x-training.header active="supplements" />
    </x-slot>

    @forelse ($groups as $rule => $supplements)
        <x-ui.section-label>{{ App\Enums\Training\UsageRule::from($rule)->label() }}</x-ui.section-label>
        @foreach ($supplements as $supplement)
            <x-ui.list-row
                wire:key="supplement-{{ $supplement->id }}"
                class="min-h-17"
                :title="$supplement->name"
                :subtitle="collect([Weight::format($supplement->dose).' '.$supplement->unit, $supplement->scheduled_time, $supplement->dose_notes])->filter()->implode(' · ')"
            >
                <x-slot:trailing>
                    <button type="button" wire:click="edit({{ $supplement->id }})" aria-label="{{ __('Edit :name', ['name' => $supplement->name]) }}" class="grid size-11 place-items-center rounded-xl text-ink-muted hover:bg-surface-2">
                        <x-ui.icon name="edit" />
                    </button>
                </x-slot>
            </x-ui.list-row>
        @endforeach
    @empty
        <x-ui.empty-state icon="supplement" :title="__('No supplements yet')" :text="__('Add what you take to check it off on the Today screen.')" />
    @endforelse

    <p class="mt-1 text-[13px]/relaxed text-ink-muted">{{ __('Training day supplements unlock on the Today screen as soon as you start a workout.') }}</p>

    <x-ui.button variant="add" wire:click="create">{{ __('New supplement') }}</x-ui.button>

    <x-ui.modal name="supplement-form" :title="$editingId ? __('Edit supplement') : __('New supplement')">
        <form wire:submit="save" class="flex flex-col gap-3">
            <x-ui.input :label="__('Name')" wire:model="name" maxlength="80" />
            <div class="grid grid-cols-2 gap-2.5">
                <x-ui.input :label="__('Dose')" wire:model="dose" inputmode="decimal" />
                <x-ui.input :label="__('Unit')" wire:model="unit" :placeholder="__('g, scoop, capsule')" maxlength="30" />
            </div>
            <x-ui.input :label="__('Time')" wire:model="scheduledTime" :placeholder="__('E.g. 08:00, post-workout')" maxlength="60" />
            <x-ui.input :label="__('How to take')" wire:model="doseNotes" :placeholder="__('E.g. with water')" maxlength="120" />
            <x-ui.select :label="__('When to take')" wire:model="usageRule">
                @foreach ($rules as $option)
                    <option value="{{ $option->value }}">{{ $option->label() }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.input :label="__('Notes')" wire:model="notes" maxlength="255" />

            <div class="mt-1 flex gap-2 *:flex-1">
                @if ($editingId)
                    <x-ui.button variant="secondary" wire:click="delete" wire:confirm="{{ __('Delete this supplement?') }}">{{ __('Delete') }}</x-ui.button>
                @endif
                <x-ui.button type="submit">{{ __('Save') }}</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</x-ui.page>
