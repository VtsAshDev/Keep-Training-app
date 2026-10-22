{{-- variant: default, highlight (orange border), brand (filled orange), dashed (empty state). --}}
@props([
    'title' => null,
    'variant' => 'default',
])

<section {{ $attributes->class([
    'rounded-2xl p-4.5',
    match ($variant) {
        'highlight' => 'border border-brand-500 bg-surface text-ink',
        'brand' => 'bg-brand-500 text-on-brand',
        'dashed' => 'border border-dashed border-brand-500 bg-surface text-ink',
        default => 'border border-line bg-surface text-ink',
    },
]) }}>
    @if ($title || isset($aside))
        <header class="flex items-baseline justify-between gap-3">
            @if ($title)
                <h2 @class(['text-sm', 'text-ink-muted' => $variant !== 'brand', 'font-semibold' => $variant === 'brand'])>{{ $title }}</h2>
            @endif

            {{ $aside ?? '' }}
        </header>
    @endif

    {{ $slot }}
</section>
