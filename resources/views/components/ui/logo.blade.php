@props([
    'size' => 'md',
])

<span {{ $attributes->class([
    'font-display font-bold uppercase italic leading-none tracking-tight text-ink',
    match ($size) {
        'sm' => 'text-xl',
        'lg' => 'text-5xl',
        default => 'text-3xl',
    },
]) }}>Keep <span class="text-brand-500">Focus</span></span>
