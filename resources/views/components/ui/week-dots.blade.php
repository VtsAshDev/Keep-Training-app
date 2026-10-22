{{-- days: seven states from Monday to Sunday, each "done", "today" or "pending". --}}
@props([
    'days' => [],
])

@php
    $labels = __('ui.weekday_initials');
@endphp

<ol {{ $attributes->class('grid grid-cols-7 gap-1.5 text-center text-xs text-ink-muted') }}>
    @foreach ($days as $index => $state)
        <li class="flex flex-col items-center gap-1.5" data-state="{{ $state }}">
            <span @class([
                'grid size-8 place-items-center rounded-full',
                'bg-brand-500 text-on-brand' => $state === 'done',
                'border-2 border-brand-500' => $state === 'today',
                'bg-surface-2' => $state === 'pending',
            ])>
                @if ($state === 'done')
                    <x-ui.icon name="check" :stroke="3" class="size-4" />
                @endif
            </span>
            <span @class(['font-semibold text-brand-text' => $state === 'today'])>{{ $labels[$index] ?? '' }}</span>
        </li>
    @endforeach
</ol>
