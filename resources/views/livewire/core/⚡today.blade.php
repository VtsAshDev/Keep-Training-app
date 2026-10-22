<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<div>
    <main class="mx-auto flex max-w-md flex-col gap-6 px-4 pt-6 pb-28">
        <x-ui.logo size="sm" />
        <h1 class="font-display text-4xl font-bold uppercase">{{ __('Today') }}</h1>
    </main>

    <x-ui.bottom-nav />
</div>
