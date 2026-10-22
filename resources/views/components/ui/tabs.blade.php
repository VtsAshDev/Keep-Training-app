{{-- items: list of ['label' => ..., 'href' => ..., 'active' => bool]. --}}
@props([
    'items' => [],
    'label' => null,
])

<nav @if ($label) aria-label="{{ $label }}" @endif {{ $attributes->class('-mx-5 flex gap-1.5 overflow-x-auto px-5 [scrollbar-width:none]') }}>
    @foreach ($items as $item)
        <a
            href="{{ $item['href'] }}"
            @if ($item['active'] ?? false) aria-current="page" @endif
            @class([
                'flex min-h-10 shrink-0 items-center rounded-full px-3.5 text-sm transition',
                'bg-ink font-semibold text-bg' => $item['active'] ?? false,
                'border border-line text-ink hover:bg-surface-2' => ! ($item['active'] ?? false),
            ])
        >{{ $item['label'] }}</a>
    @endforeach
</nav>
