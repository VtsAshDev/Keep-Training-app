{{-- numeric: centered Barlow Condensed cell, as in set rows and repetition ranges. muted: dimmed value (pending set). --}}
@props([
    'label' => null,
    'hideLabel' => false,
    'hint' => null,
    'error' => null,
    'name' => null,
    'type' => 'text',
    'numeric' => false,
    'muted' => false,
])

@php
    $name ??= $attributes->whereStartsWith('wire:model')->first();
    $id = $attributes->get('id') ?? $name ?? 'input-'.Str::random(8);
    $error ??= $name && isset($errors) ? $errors->first($name) : null;
@endphp

<div class="flex flex-col">
    @if ($label)
        <label for="{{ $id }}" @class(['sr-only' => $hideLabel, 'mb-1.5 text-[13px] text-ink-muted' => ! $hideLabel])>{{ $label }}</label>
    @endif

    <input
        id="{{ $id }}"
        type="{{ $type }}"
        @if ($name) name="{{ $name }}" @endif
        @if ($error) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
        {{ $attributes->class([
            'w-full border placeholder:text-ink-muted transition',
            $muted ? 'text-ink-muted' : 'text-ink',
            'focus:border-brand-500 focus:outline-2 focus:outline-brand-500/30 disabled:text-ink-muted',
            $numeric
                ? 'h-11 rounded-[10px] bg-bg text-center font-display text-xl font-semibold tabular-nums'
                : 'h-12 rounded-xl bg-surface px-3.5 text-base',
            $error ? 'border-danger' : 'border-line',
        ]) }}
    >

    @if ($error)
        <p id="{{ $id }}-error" class="mt-1.5 text-sm text-danger">{{ $error }}</p>
    @elseif ($hint)
        <p class="mt-1.5 text-sm text-ink-muted">{{ $hint }}</p>
    @endif
</div>
