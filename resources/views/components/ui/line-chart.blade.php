{{-- Plain SVG line chart. points: numbers in order; labels: [first, last] shown under the chart. --}}
@props([
    'points' => [],
    'labels' => [],
    'label' => '',
])

@php
    $width = 350;
    $top = 20;
    $bottom = 120;
    $left = 20;
    $right = 328;

    $values = array_values(array_map('floatval', $points));
    $count = count($values);
    $min = $count ? min($values) : 0;
    $max = $count ? max($values) : 0;

    $coords = [];
    foreach ($values as $i => $value) {
        $x = $count > 1 ? $left + $i * ($right - $left) / ($count - 1) : ($left + $right) / 2;
        $y = $max > $min ? $bottom - ($value - $min) * ($bottom - $top) / ($max - $min) : ($top + $bottom) / 2;
        $coords[] = [round($x, 1), round($y, 1)];
    }
@endphp

<div {{ $attributes }}>
    <svg viewBox="0 0 {{ $width }} 140" role="img" aria-label="{{ $label }}" class="block h-auto w-full">
        @foreach ([$top, ($top + $bottom) / 2, $bottom] as $grid)
            <line x1="0" y1="{{ $grid }}" x2="{{ $width }}" y2="{{ $grid }}" class="stroke-line" stroke-width="1" />
        @endforeach

        @if ($count > 1)
            <polyline points="{{ collect($coords)->map(fn ($c) => implode(',', $c))->implode(' ') }}" fill="none" class="stroke-brand-500" stroke-width="3" stroke-linejoin="round" stroke-linecap="round" />
        @endif

        @foreach ($coords as $i => [$x, $y])
            @if ($loop->last)
                <circle cx="{{ $x }}" cy="{{ $y }}" r="6" class="fill-brand-500 stroke-surface" stroke-width="2" />
            @else
                <circle cx="{{ $x }}" cy="{{ $y }}" r="4" class="fill-brand-500" />
            @endif
        @endforeach
    </svg>

    @if ($labels)
        <div class="mt-1 flex justify-between text-xs text-ink-muted">
            <span>{{ $labels[0] ?? '' }}</span>
            <span>{{ $labels[1] ?? '' }}</span>
        </div>
    @endif
</div>
