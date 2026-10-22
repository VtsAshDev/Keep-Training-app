@props([
    'label' => null,
])

<label class="inline-flex cursor-pointer items-center gap-3 text-ink select-none has-disabled:cursor-not-allowed has-disabled:opacity-50">
    <span class="grid size-6 shrink-0 place-items-center">
        <input
            type="checkbox"
            {{ $attributes->class([
                'peer col-start-1 row-start-1 size-6 appearance-none rounded-md border-2 border-line bg-surface-raised transition',
                'checked:border-brand-500 checked:bg-brand-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500',
            ]) }}
        >
        <svg class="pointer-events-none col-start-1 row-start-1 size-4 text-on-brand opacity-0 peer-checked:opacity-100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M20 6 9 17l-5-5" />
        </svg>
    </span>

    @if ($label)
        <span>{{ $label }}</span>
    @endif

    {{ $slot }}
</label>
