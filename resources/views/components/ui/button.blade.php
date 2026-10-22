@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
    'icon' => null,
])

@php
    $icon ??= $variant === 'add' ? 'plus' : null;

    $classes = [
        'inline-flex items-center justify-center gap-2 font-semibold transition select-none',
        'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500',
        'active:translate-y-px disabled:pointer-events-none disabled:opacity-50',
        match ($size) {
            'sm' => 'min-h-11 rounded-xl px-4 text-base',
            default => 'min-h-13 rounded-xl px-4 text-[17px]',
        },
        match ($variant) {
            'secondary' => 'border border-line text-ink font-medium hover:bg-surface-2',
            'ghost' => 'text-ink font-medium hover:bg-surface-2',
            'add' => 'w-full rounded-[14px] border border-dashed border-line text-brand-text hover:bg-surface-2',
            default => 'bg-brand-500 text-on-brand hover:bg-brand-600',
        },
    ];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        @if ($icon) <x-ui.icon :name="$icon" :stroke="2.2" /> @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>
        @if ($icon) <x-ui.icon :name="$icon" :stroke="2.2" /> @endif
        {{ $slot }}
    </button>
@endif
