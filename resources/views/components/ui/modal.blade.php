{{--
    Open with $dispatch('open-modal', 'name') in Alpine or
    $this->dispatch('open-modal', name: 'name') in Livewire; close with 'close-modal'.
    wire:ignore.self keeps Livewire updates from removing the "open" attribute (which closed
    the modal on every search or validation error) while the content still updates.
--}}
@props([
    'name',
    'title' => null,
])

<dialog
    wire:ignore.self
    x-data="{ matches: (event) => (event.detail?.name ?? event.detail) === @js($name) }"
    x-on:open-modal.window="matches($event) && $el.showModal()"
    x-on:close-modal.window="matches($event) && $el.close()"
    x-on:click.self="$el.close()"
    aria-labelledby="{{ $name }}-title"
    {{ $attributes->class('m-auto w-[calc(100%-2rem)] max-w-md rounded-2xl border border-line bg-surface p-0 text-ink shadow-2xl backdrop:bg-black/60') }}
>
    <div class="flex flex-col gap-4 p-4.5">
        <header class="flex items-center justify-between gap-3">
            <h2 id="{{ $name }}-title" class="font-display text-2xl font-semibold uppercase">{{ $title }}</h2>

            <button type="button" x-on:click="$el.closest('dialog').close()" class="grid size-11 place-items-center rounded-xl text-ink-muted hover:bg-surface-2 hover:text-ink" aria-label="{{ __('Close') }}">
                <x-ui.icon name="close" />
            </button>
        </header>

        <div>{{ $slot }}</div>

        @isset($footer)
            <footer class="flex gap-2 *:flex-1">{{ $footer }}</footer>
        @endisset
    </div>
</dialog>
