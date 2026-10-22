<x-layouts::app title="Design system">
    <div class="grid min-h-dvh lg:grid-cols-2">
        @foreach (['light', 'dark'] as $theme)
            <div data-theme="{{ $theme }}" class="bg-bg text-ink">
                @include('dev.partials.ui-showcase', ['theme' => $theme])
            </div>
        @endforeach
    </div>
</x-layouts::app>
