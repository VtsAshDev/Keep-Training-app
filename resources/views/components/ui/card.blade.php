@props([
    'title' => null,
])

<section {{ $attributes->class('rounded-2xl border border-line bg-surface-raised p-5 text-ink') }}>
    @if ($title || isset($actions))
        <header class="mb-4 flex items-center justify-between gap-3">
            @if ($title)
                <h2 class="font-display text-xl font-bold uppercase tracking-wide">{{ $title }}</h2>
            @endif

            {{ $actions ?? '' }}
        </header>
    @endif

    {{ $slot }}
</section>
