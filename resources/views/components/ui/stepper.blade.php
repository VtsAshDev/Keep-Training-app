{{-- Binds with wire:model through x-modelable. size lg is the weekly goal stepper. --}}
@props([
    'label' => null,
    'value' => 0,
    'min' => 0,
    'max' => null,
    'step' => 1,
    'unit' => null,
    'size' => 'md',
    'decreaseLabel' => __('Decrease'),
    'increaseLabel' => __('Increase'),
])

@php
    $lg = $size === 'lg';
@endphp

<div
    x-data="{ value: @js($value), min: @js($min), max: @js($max), step: @js($step) }"
    x-modelable="value"
    {{ $attributes->whereStartsWith('wire:model') }}
    {{ $attributes->whereDoesntStartWith('wire:model')->class('flex flex-col') }}
>
    @if ($label)
        <span class="mb-1 text-xs text-ink-muted">{{ $label }}</span>
    @endif

    <div @class(['flex items-center border border-line bg-bg', 'h-14 rounded-xl' => $lg, 'h-11 rounded-[10px]' => ! $lg])>
        <button
            type="button"
            x-on:click="value = Math.max(min, value - step)"
            x-bind:disabled="value <= min"
            aria-label="{{ $decreaseLabel }}"
            @class(['grid h-full place-items-center text-ink disabled:text-ink-muted', 'w-14 text-2xl' => $lg, 'w-11 text-xl' => ! $lg])
        >−</button>

        <span class="flex-1 text-center" aria-live="polite">
            <span x-text="value" @class(['font-display font-semibold tabular-nums', 'text-3xl' => $lg, 'text-xl' => ! $lg])>{{ $value }}</span>
            @if ($unit)
                <span @class(['text-sm text-ink-muted' => $lg, 'font-display text-xl font-semibold' => ! $lg])>{{ $unit }}</span>
            @endif
        </span>

        <button
            type="button"
            x-on:click="value = max === null ? value + step : Math.min(max, value + step)"
            x-bind:disabled="max !== null && value >= max"
            aria-label="{{ $increaseLabel }}"
            @class(['grid h-full place-items-center text-ink disabled:text-ink-muted', 'w-14 text-2xl' => $lg, 'w-11 text-xl' => ! $lg])
        >+</button>
    </div>
</div>
