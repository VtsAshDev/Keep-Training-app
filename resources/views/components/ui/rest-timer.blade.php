{{--
    Starts with $dispatch('rest-timer-start', { seconds: 90 }) (or $this->dispatch in Livewire).
    Counts against an end timestamp, so it stays right when the phone sleeps.
--}}
@props([
    'seconds' => 0,
    'fixed' => true,
])

<section
    aria-label="{{ __('Rest timer') }}"
    x-data="{
        endsAt: 0,
        remaining: 0,
        timer: null,
        get clock() {
            const m = Math.floor(this.remaining / 60), s = this.remaining % 60;
            return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
        },
        start(seconds) { this.endsAt = Date.now() + seconds * 1000; this.tick(); clearInterval(this.timer); this.timer = setInterval(() => this.tick(), 250); },
        tick() {
            this.remaining = Math.max(0, Math.ceil((this.endsAt - Date.now()) / 1000));
            if (this.remaining === 0) { clearInterval(this.timer); }
        },
        add(delta) { this.endsAt += delta * 1000; this.tick(); },
        skip() { this.endsAt = 0; this.tick(); },
    }"
    x-init="@js($seconds) > 0 && start(@js($seconds))"
    x-on:rest-timer-start.window="start($event.detail?.seconds ?? $event.detail)"
    x-show="remaining > 0"
    x-cloak
    {{ $attributes->class([
        'flex items-center gap-2.5 bg-brand-500 px-4 pt-3.5 pb-[max(0.875rem,env(safe-area-inset-bottom))] text-on-brand',
        'fixed inset-x-0 bottom-0 z-40' => $fixed,
    ]) }}
>
    <div class="flex-1">
        <div class="text-[13px] font-semibold">{{ __('Rest') }}</div>
        <div class="font-display text-[38px] leading-none font-semibold tabular-nums" x-text="clock" aria-live="off"></div>
    </div>

    <button type="button" x-on:click="add(-15)" class="min-h-12 min-w-13 rounded-xl border-2 border-on-brand font-semibold">−15</button>
    <button type="button" x-on:click="add(15)" class="min-h-12 min-w-13 rounded-xl border-2 border-on-brand font-semibold">+15</button>
    <button type="button" x-on:click="skip()" class="min-h-12 rounded-xl bg-on-brand px-3.5 font-semibold text-brand-400">{{ __('Skip') }}</button>
</section>
