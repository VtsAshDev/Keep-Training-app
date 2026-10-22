{{--
    Open with $dispatch('open-modal', 'name') in Alpine or
    $this->dispatch('open-modal', name: 'name') in Livewire; close with 'close-modal'.
--}}
@props([
    'name',
    'title' => null,
])

<dialog
    x-data="{ matches: (event) => (event.detail?.name ?? event.detail) === @js($name) }"
    x-on:open-modal.window="matches($event) && $el.showModal()"
    x-on:close-modal.window="matches($event) && $el.close()"
    x-on:click.self="$el.close()"
    aria-labelledby="{{ $name }}-title"
    {{ $attributes->class('m-auto w-[calc(100%-2rem)] max-w-md rounded-2xl border border-line bg-surface-raised p-0 text-ink shadow-2xl backdrop:bg-black/60') }}
>
    <div class="flex flex-col gap-4 p-5">
        <header class="flex items-center justify-between gap-3">
            <h2 id="{{ $name }}-title" class="font-display text-2xl font-bold uppercase tracking-wide">{{ $title }}</h2>

            <button type="button" x-on:click="$el.closest('dialog').close()" class="rounded-md p-1 text-ink-muted hover:bg-surface-muted hover:text-ink" aria-label="{{ __('Close') }}">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M18 6 6 18M6 6l12 12" />
                </svg>
            </button>
        </header>

        <div>{{ $slot }}</div>

        @isset($footer)
            <footer class="flex justify-end gap-2">{{ $footer }}</footer>
        @endisset
    </div>
</dialog>
