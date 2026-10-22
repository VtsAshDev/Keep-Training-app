<x-layouts::app>
    <div class="mx-auto flex min-h-dvh max-w-md flex-col">
        <main class="flex flex-1 flex-col justify-center px-7 py-10">
            <x-ui.monogram />
            <h1 class="mt-7"><x-ui.logo size="lg" stacked /></h1>
            <p class="mt-4.5 max-w-75 text-lg/normal text-ink-muted">{{ __('Your workouts, supplements and habits in one place. Open it, see today\'s workout and go.') }}</p>
        </main>

        <div class="px-7 pb-[max(2.75rem,env(safe-area-inset-bottom))]">
            <livewire:core.login />
        </div>
    </div>
</x-layouts::app>
