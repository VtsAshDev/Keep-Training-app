{{-- Screen header with back link and an optional action slot on the right. --}}
@props([
    'title',
    'subtitle' => null,
    'back' => null,
    'backLabel' => __('Back'),
])

<header {{ $attributes->class('flex items-center gap-2 border-b border-line px-3 pt-[max(0.75rem,env(safe-area-inset-top))] pb-3') }}>
    @if ($back)
        <a href="{{ $back }}" aria-label="{{ $backLabel }}" class="grid size-11 shrink-0 place-items-center rounded-xl text-ink hover:bg-surface-2">
            <x-ui.icon name="back" class="size-6" />
        </a>
    @endif

    <div class="min-w-0 flex-1">
        <div class="truncate font-display text-[22px] leading-none font-semibold uppercase">{{ $title }}</div>
        @if ($subtitle)
            <div class="mt-0.5 text-[13px] text-ink-muted tabular-nums">{{ $subtitle }}</div>
        @endif
    </div>

    {{ $slot }}
</header>
