{{-- Extra attributes (checked, disabled, wire:click...) go to the checkbox. --}}
@props([
    'name',
    'detail' => null,
    'tag' => null,
])

<label class="flex min-h-13 cursor-pointer items-center gap-3 border-b border-line last:border-b-0 has-disabled:cursor-not-allowed">
    <x-ui.checkbox-box :attributes="$attributes" />

    <span class="min-w-0 flex-1">
        <span @class(['block font-medium', 'text-ink-muted' => (bool) $attributes->get('disabled')])>{{ $name }}</span>
        @if ($detail)
            <span class="block text-[13px] text-ink-muted">{{ $detail }}</span>
        @endif
    </span>

    @if ($tag)
        <x-ui.tag>{{ $tag }}</x-ui.tag>
    @endif
</label>
