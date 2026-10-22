@props([
    'fixed' => true,
])

@php
    // Livewire updates hit /livewire/update, so match against the page path.
    $path = trim(Livewire\Livewire::originalPath(), '/');
    $items = [
        ['label' => __('Today'), 'href' => url('/hoje'), 'active' => $path === 'hoje', 'icon' => 'home'],
        ['label' => __('Training'), 'href' => url('/treino/fichas'), 'active' => Str::startsWith($path, 'treino'), 'icon' => 'training'],
        ['label' => __('Settings'), 'href' => url('/configuracoes'), 'active' => Str::startsWith($path, 'configuracoes'), 'icon' => 'settings'],
    ];
@endphp

<nav
    aria-label="{{ __('Main navigation') }}"
    {{ $attributes->class([
        'border-t border-line bg-surface px-2 pt-1.5 pb-[max(0.5rem,env(safe-area-inset-bottom))]',
        'fixed inset-x-0 bottom-0 z-40' => $fixed,
    ]) }}
>
    <ul class="mx-auto flex max-w-md">
        @foreach ($items as $item)
            <li class="flex-1">
                <a
                    href="{{ $item['href'] }}"
                    wire:navigate
                    @if ($item['active']) aria-current="page" @endif
                    @class([
                        'flex flex-col items-center gap-0.75 py-2 text-xs transition',
                        'font-semibold text-brand-500' => $item['active'],
                        'font-medium text-ink-muted hover:text-ink' => ! $item['active'],
                    ])
                >
                    <x-ui.icon :name="$item['icon']" class="size-6" />
                    {{ $item['label'] }}
                </a>
            </li>
        @endforeach
    </ul>
</nav>
