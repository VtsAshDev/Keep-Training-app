@props([
    'label' => null,
    'hint' => null,
    'error' => null,
    'name' => null,
    'type' => 'text',
])

@php
    $name ??= $attributes->whereStartsWith('wire:model')->first();
    $id = $attributes->get('id') ?? $name ?? 'input-'.Str::random(8);
    $error ??= $name && isset($errors) ? $errors->first($name) : null;
@endphp

<div class="flex flex-col gap-1.5">
    @if ($label)
        <label for="{{ $id }}" class="text-sm font-semibold text-ink">{{ $label }}</label>
    @endif

    <input
        id="{{ $id }}"
        type="{{ $type }}"
        @if ($name) name="{{ $name }}" @endif
        @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
        {{ $attributes->class([
            'h-11 w-full rounded-lg border bg-surface-raised px-3 text-base text-ink placeholder:text-ink-muted transition',
            'focus:border-brand-500 focus:outline-2 focus:outline-brand-500/30 disabled:opacity-50',
            $error ? 'border-danger' : 'border-line',
        ]) }}
    >

    @if ($error)
        <p id="{{ $id }}-error" class="text-sm text-danger">{{ $error }}</p>
    @elseif ($hint)
        <p class="text-sm text-ink-muted">{{ $hint }}</p>
    @endif
</div>
