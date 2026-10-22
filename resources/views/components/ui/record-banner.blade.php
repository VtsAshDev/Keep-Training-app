{{-- records: list of ['label' => exercise, 'value' => "62,5 kg × 10"]. --}}
@props([
    'title',
    'records' => [],
])

<section {{ $attributes->class('rounded-[14px] bg-brand-soft px-4 py-3.5') }}>
    <div class="flex items-center gap-2 text-sm font-semibold text-brand-soft-ink">
        <x-ui.icon name="trophy" class="size-[18px]" />
        {{ $title }}
    </div>

    <ul class="mt-2.5 flex flex-col gap-1.5">
        @foreach ($records as $record)
            <li class="flex justify-between gap-3 text-[15px]">
                <span>{{ $record['label'] }}</span>
                <span class="font-display text-lg font-semibold tabular-nums">{{ $record['value'] }}</span>
            </li>
        @endforeach
    </ul>
</section>
