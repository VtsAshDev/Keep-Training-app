@props([
    'fixed' => true,
])

@php
    $items = [
        ['label' => __('Today'), 'href' => url('/hoje'), 'active' => request()->is('hoje'), 'icon' => 'today'],
        ['label' => __('Training'), 'href' => url('/treino/fichas'), 'active' => request()->is('treino*'), 'icon' => 'training'],
        ['label' => __('Settings'), 'href' => url('/configuracoes'), 'active' => request()->is('configuracoes*'), 'icon' => 'settings'],
    ];
@endphp

<nav
    aria-label="{{ __('Main navigation') }}"
    {{ $attributes->class([
        'border-t border-line bg-surface-raised/95 pb-[env(safe-area-inset-bottom)] backdrop-blur',
        'fixed inset-x-0 bottom-0 z-40' => $fixed,
    ]) }}
>
    <ul class="mx-auto grid max-w-md grid-cols-3">
        @foreach ($items as $item)
            <li>
                <a
                    href="{{ $item['href'] }}"
                    @if ($item['active']) aria-current="page" @endif
                    @class([
                        'flex flex-col items-center gap-1 py-2.5 text-xs font-semibold uppercase tracking-wide transition',
                        'text-accent' => $item['active'],
                        'text-ink-muted hover:text-ink' => ! $item['active'],
                    ])
                >
                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        @switch($item['icon'])
                            @case('today')
                                <path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8" />
                                <path d="M3 10a2 2 0 0 1 .709-1.528l7-6a2 2 0 0 1 2.582 0l7 6A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                                @break
                            @case('training')
                                <path d="M14.4 14.4 9.6 9.6" />
                                <path d="M18.657 21.485a2 2 0 1 1-2.829-2.828l-1.767 1.768a2 2 0 1 1-2.829-2.829l6.364-6.364a2 2 0 1 1 2.829 2.829l-1.768 1.767a2 2 0 1 1 2.828 2.829z" />
                                <path d="m21.5 21.5-1.4-1.4M3.9 3.9 2.5 2.5" />
                                <path d="M6.404 12.768a2 2 0 1 1-2.829-2.829l1.768-1.767a2 2 0 1 1-2.828-2.829l2.828-2.828a2 2 0 1 1 2.829 2.828l1.767-1.768a2 2 0 1 1 2.829 2.829z" />
                                @break
                            @case('settings')
                                <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
                                <circle cx="12" cy="12" r="3" />
                                @break
                        @endswitch
                    </svg>
                    {{ $item['label'] }}
                </a>
            </li>
        @endforeach
    </ul>
</nav>
