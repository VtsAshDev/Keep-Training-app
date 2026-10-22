<x-layouts::app>
    <main class="mx-auto flex min-h-dvh max-w-sm flex-col justify-center gap-10 px-4 py-10">
        <div class="flex flex-col gap-3 text-center">
            <x-ui.logo size="lg" />
            <p class="text-ink-muted">{{ __('Your training, supplements and weekly goal in one place.') }}</p>
        </div>

        <livewire:core.login />
    </main>
</x-layouts::app>
