@props([
    'icon' => 'training',
    'title',
    'text' => null,
])

<x-ui.card variant="dashed" :attributes="$attributes">
    <span class="grid size-11 place-items-center rounded-xl bg-brand-soft text-brand-text">
        <x-ui.icon :name="$icon" class="size-6" />
    </span>

    <div class="mt-3 font-display text-2xl font-semibold uppercase">{{ $title }}</div>

    @if ($text)
        <p class="mt-1 text-sm/relaxed text-ink-muted">{{ $text }}</p>
    @endif

    @if ($slot->isNotEmpty())
        <div class="mt-4 flex flex-col">{{ $slot }}</div>
    @endif
</x-ui.card>
