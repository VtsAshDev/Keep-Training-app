{{-- Big uppercase screen title (TREINO, AJUSTES), with optional content below such as tabs. --}}
@props([
    'title',
])

<header {{ $attributes->class('px-5 pt-[max(1.5rem,env(safe-area-inset-top))]') }}>
    <h1 class="font-display text-[34px] leading-tight font-semibold uppercase">{{ $title }}</h1>

    @if ($slot->isNotEmpty())
        <div class="mt-3">{{ $slot }}</div>
    @endif
</header>
