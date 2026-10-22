<div {{ $attributes->class('flex flex-col gap-1.5') }}>
    <div class="grid grid-cols-[40px_1fr_76px_64px_48px] items-center gap-2 px-0.5 text-xs text-ink-muted">
        <span class="text-center">{{ __('Set') }}</span>
        <span>{{ __('Previous') }}</span>
        <span class="text-center">{{ __('kg') }}</span>
        <span class="text-center">{{ __('Reps') }}</span>
        <span></span>
    </div>

    {{ $slot }}
</div>
