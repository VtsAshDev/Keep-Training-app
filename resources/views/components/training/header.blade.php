{{-- Header of the Treino section: title and the pill tabs. --}}
@props([
    'active',
])

<x-ui.page-header :title="__('Training')">
    <x-ui.tabs :label="__('Training sections')" :items="[
        ['label' => __('Plans'), 'href' => route('training.plans'), 'active' => $active === 'plans'],
        ['label' => __('History'), 'href' => route('training.history'), 'active' => $active === 'history'],
        ['label' => __('Exercises'), 'href' => route('training.exercises'), 'active' => $active === 'exercises'],
        ['label' => __('Supplements'), 'href' => route('training.supplements'), 'active' => $active === 'supplements'],
    ]" />
</x-ui.page-header>
