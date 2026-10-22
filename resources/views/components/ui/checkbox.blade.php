@props([
    'label' => null,
])

<label class="inline-flex min-h-11 cursor-pointer items-center gap-3 text-ink select-none has-disabled:cursor-not-allowed has-disabled:text-ink-muted">
    <x-ui.checkbox-box :attributes="$attributes" />

    @if ($label)
        <span>{{ $label }}</span>
    @endif

    {{ $slot }}
</label>
