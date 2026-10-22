{{-- Wordmark as text. stacked puts FOCUS under KEEP, as on the entry screen. --}}
@props([
    'size' => 'md',
    'stacked' => false,
])

<span {{ $attributes->class([
    'inline-block font-display font-bold uppercase italic leading-[.95] text-ink',
    match ($size) {
        'sm' => 'text-[26px]',
        'lg' => 'text-[64px]',
        default => 'text-[40px]',
    },
]) }}>Keep{!! $stacked ? '<br>' : ' ' !!}<span class="text-brand-text">Focus</span></span>
