@props([
    'name',
    'stroke' => 2,
])

<svg {{ $attributes->class(['shrink-0', 'size-5' => ! str_contains((string) $attributes->get('class'), 'size-')]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="{{ $stroke }}" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('home')
            <path d="M3 11l9-7 9 7v9a1 1 0 0 1-1 1h-5v-6h-6v6H4a1 1 0 0 1-1-1z" />
            @break
        @case('training')
            <path d="M6 7v10M18 7v10M3 10v4M21 10v4M6 12h12" />
            @break
        @case('settings')
            <path d="M4 7h9M17 7h3M4 17h3M11 17h9" />
            <circle cx="15" cy="7" r="2" />
            <circle cx="9" cy="17" r="2" />
            @break
        @case('check')
            <path d="M5 12l5 5 9-10" />
            @break
        @case('back')
            <path d="M15 5l-7 7 7 7" />
            @break
        @case('chevron-right')
            <path d="M9 5l7 7-7 7" />
            @break
        @case('chevron-down')
            <path d="M6 9l6 6 6-6" />
            @break
        @case('plus')
            <path d="M12 5v14M5 12h14" />
            @break
        @case('grip')
            <path d="M9 6h.01M15 6h.01M9 12h.01M15 12h.01M9 18h.01M15 18h.01" />
            @break
        @case('edit')
            <path d="M4 20h4L19 9l-4-4L4 16zM13 7l4 4" />
            @break
        @case('play')
            <path d="M7 4v16l13-8z" />
            @break
        @case('flame')
            <path d="M12 3c1 4 5 5 5 10a5 5 0 0 1-10 0c0-2 1-3 2-4 0 2 1 3 2 3 0-3-1-5 1-9z" />
            @break
        @case('arrow-up')
            <path d="M12 19V5M6 11l6-6 6 6" />
            @break
        @case('trophy')
            <path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0zM7 6H4a3 3 0 0 0 3 4M17 6h3a3 3 0 0 1-3 4" />
            @break
        @case('more')
            <path d="M12 5h.01M12 12h.01M12 19h.01" />
            @break
        @case('close')
            <path d="M18 6 6 18M6 6l12 12" />
            @break
        @case('supplement')
            <path d="M10.5 20.5 3.5 13.5a4.95 4.95 0 1 1 7-7l7 7a4.95 4.95 0 1 1-7 7zM8.5 8.5l7 7" />
            @break
    @endswitch
</svg>
