{{--
    letter: square tile on the left (letterActive paints it orange).
    Slots: leading (replaces the tile), trailing (replaces the chevron).
--}}
@props([
    'title',
    'subtitle' => null,
    'href' => null,
    'handle' => false,
    'letter' => null,
    'letterActive' => false,
    'highlight' => false,
    'accentSubtitle' => false,
])

@php
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @endif
    {{ $attributes->class([
        'flex min-h-16 items-center gap-3 rounded-[14px] border bg-surface py-2 pr-3.5 text-ink',
        $handle ? 'pl-1.5' : 'pl-3.5',
        $highlight ? 'border-brand-500' : 'border-line',
        'transition hover:bg-surface-2' => $href,
    ]) }}
>
    @if ($handle)
        <span class="flex w-8 shrink-0 cursor-grab justify-center text-ink-muted" aria-hidden="true">
            <x-ui.icon name="grip" />
        </span>
    @endif

    @isset($leading)
        {{ $leading }}
    @elseif ($letter)
        <span @class([
            'grid size-10 shrink-0 place-items-center rounded-[10px] font-display text-[22px] font-semibold',
            'bg-brand-500 text-on-brand' => $letterActive,
            'bg-surface-2' => ! $letterActive,
        ])>{{ $letter }}</span>
    @endisset

    <span class="min-w-0 flex-1">
        <span class="block truncate font-medium">{{ $title }}</span>
        @if ($subtitle)
            <span @class(['block truncate text-[13px] tabular-nums', 'text-brand-text' => $accentSubtitle, 'text-ink-muted' => ! $accentSubtitle])>{{ $subtitle }}</span>
        @endif
    </span>

    @isset($trailing)
        {{ $trailing }}
    @elseif ($href)
        <x-ui.icon name="chevron-right" class="text-ink-muted" />
    @endisset
</{{ $tag }}>
