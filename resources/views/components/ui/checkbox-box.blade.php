{{-- The square box shared by checkbox and supplement-item; attributes go to the input. --}}
<span class="grid size-[22px] shrink-0 place-items-center">
    <input
        type="checkbox"
        {{ $attributes->class([
            'peer col-start-1 row-start-1 size-[22px] appearance-none rounded-md border-2 border-line bg-surface transition',
            'checked:border-brand-500 checked:bg-brand-500 disabled:bg-surface-2',
            'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500',
        ]) }}
    >
    <x-ui.icon name="check" :stroke="3.5" class="pointer-events-none col-start-1 row-start-1 size-4 text-on-brand opacity-0 peer-checked:opacity-100" />
</span>
