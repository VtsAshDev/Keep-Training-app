<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div class="mx-auto flex min-h-dvh max-w-md flex-col">
    <header class="flex items-center justify-between px-5 pt-[max(1.5rem,env(safe-area-inset-top))] pb-3">
        <x-ui.logo size="sm" />
        <span class="text-sm text-ink-muted">{{ Str::ucfirst(Str::before(now()->isoFormat('dddd'), '-')) }}, {{ now()->isoFormat('D MMM') }}</span>
    </header>

    <main class="flex flex-1 flex-col gap-3.5 px-5 pt-2 pb-28">
        <h1 class="sr-only">{{ __('Today') }}</h1>
    </main>

    <x-ui.bottom-nav />
</div>
