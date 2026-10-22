@props([
    'label' => null,
    'name' => null,
])

@php
    $name ??= $attributes->whereStartsWith('wire:model')->first();
    $id = $attributes->get('id') ?? $name ?? 'select-'.Str::random(8);
@endphp

<div class="flex flex-col">
    @if ($label)
        <label for="{{ $id }}" class="mb-1.5 text-[13px] text-ink-muted">{{ $label }}</label>
    @endif

    <select
        id="{{ $id }}"
        @if ($name) name="{{ $name }}" @endif
        {{ $attributes->class('h-11 w-full rounded-[10px] border border-line bg-surface-2 px-3 text-base font-medium text-ink focus:border-brand-500 focus:outline-2 focus:outline-brand-500/30') }}
    >
        {{ $slot }}
    </select>
</div>
