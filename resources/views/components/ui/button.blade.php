@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
])

@php
    $classes = [
        'inline-flex items-center justify-center gap-2 rounded-lg font-display font-semibold uppercase tracking-wide transition select-none',
        'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500',
        'active:translate-y-px disabled:pointer-events-none disabled:opacity-50',
        match ($size) {
            'sm' => 'h-9 px-3 text-sm',
            'lg' => 'h-13 px-6 text-lg',
            default => 'h-11 px-5 text-base',
        },
        match ($variant) {
            'secondary' => 'border border-line bg-surface-raised text-ink hover:bg-surface-muted',
            'ghost' => 'text-ink hover:bg-surface-muted',
            default => 'bg-brand-500 text-on-brand hover:bg-brand-600',
        },
    ];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif
