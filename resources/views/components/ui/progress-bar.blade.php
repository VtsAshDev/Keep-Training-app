{{-- size: md (8 px, rounded, weekly goal) or sm (4 px, workout header). --}}
@props([
    'value' => 0,
    'size' => 'md',
    'label' => null,
])

@php
    $value = max(0, min(100, (float) $value));
@endphp

<div
    role="progressbar"
    aria-valuemin="0"
    aria-valuemax="100"
    aria-valuenow="{{ round($value) }}"
    @if ($label) aria-label="{{ $label }}" @endif
    {{ $attributes->class(['overflow-hidden bg-surface-2', 'h-2 rounded' => $size === 'md', 'h-1' => $size === 'sm']) }}
>
    <div class="h-full bg-brand-500 {{ $size === 'md' ? 'rounded' : '' }}" style="width: {{ $value }}%"></div>
</div>
