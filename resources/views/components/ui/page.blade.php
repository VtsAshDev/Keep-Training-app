{{-- Phone-width screen. nav shows the bottom navigation; footer holds sticky bars (action-bar, rest-timer). --}}
@props([
    'nav' => true,
])

<div {{ $attributes->class('mx-auto flex min-h-dvh max-w-md flex-col') }}>
    {{ $header ?? '' }}

    <main @class(['flex flex-1 flex-col gap-3 px-5 pt-4', 'pb-28' => $nav, 'pb-6' => ! $nav])>
        {{ $slot }}
    </main>

    {{ $footer ?? '' }}

    @if ($nav)
        <x-ui.bottom-nav />
    @endif
</div>
