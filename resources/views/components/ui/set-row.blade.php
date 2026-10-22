{{--
    state: pending, current or done. weight/reps arrive already formatted (e.g. "62,5").
    weightModel/repsModel bind the inputs; action is the wire:click that completes the set.
--}}
@props([
    'number',
    'previous' => null,
    'weight' => null,
    'reps' => null,
    'state' => 'pending',
    'weightModel' => null,
    'repsModel' => null,
    'action' => null,
])

@php
    $done = $state === 'done';
    $muted = $state === 'pending';
@endphp

<div {{ $attributes->class([
    'grid min-h-13 grid-cols-[40px_1fr_76px_64px_48px] items-center gap-2 rounded-[10px] px-0.5',
    'bg-surface-2' => $done,
]) }}>
    <span @class(['text-center font-display text-lg font-semibold tabular-nums', 'text-ink-muted' => $muted])>{{ $number }}</span>
    <span class="text-sm text-ink-muted tabular-nums">{{ $previous ?? '—' }}</span>

    @if ($done)
        <span class="text-center font-display text-xl font-semibold tabular-nums">{{ $weight }}</span>
        <span class="text-center font-display text-xl font-semibold tabular-nums">{{ $reps }}</span>
    @else
        <x-ui.input numeric hide-label :label="__('Weight for set :number', ['number' => $number])" inputmode="decimal" :value="$weight" :wire:model.blur="$weightModel" :muted="$muted" />
        <x-ui.input numeric hide-label :label="__('Reps for set :number', ['number' => $number])" inputmode="numeric" :value="$reps" :wire:model.blur="$repsModel" :muted="$muted" />
    @endif

    <button
        type="button"
        @if ($action) wire:click="{{ $action }}" @endif
        aria-label="{{ $done ? __('Set :number completed', ['number' => $number]) : __('Complete set :number', ['number' => $number]) }}"
        @class([
            'grid size-11 place-items-center rounded-[10px] transition',
            'bg-brand-500 text-on-brand' => $done,
            'border border-line text-ink-muted hover:text-ink' => ! $done,
        ])
    >
        <x-ui.icon name="check" :stroke="$done ? 3 : 2.4" />
    </button>
</div>
