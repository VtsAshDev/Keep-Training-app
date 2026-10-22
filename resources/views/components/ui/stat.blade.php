{{-- boxed: small card, as in the workout summary. accent paints the number orange. --}}
@props([
    'label',
    'value',
    'unit' => null,
    'accent' => false,
    'boxed' => false,
])

<div {{ $attributes->class(['rounded-[14px] border border-line bg-surface p-3' => $boxed]) }}>
    <div @class(['text-ink-muted', 'text-[13px]' => $boxed, 'text-xs' => ! $boxed])>{{ $label }}</div>
    <div @class([
        'font-display font-semibold tabular-nums',
        'text-[28px]' => $boxed,
        'text-[26px]' => ! $boxed,
        'text-brand-text' => $accent,
    ])>{{ $value }}@if ($unit)<span class="text-[15px] text-ink-muted"> {{ $unit }}</span>@endif</div>
</div>
