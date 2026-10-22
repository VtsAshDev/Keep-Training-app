{{-- Inline message for refused actions and confirmations. --}}
<div role="alert" {{ $attributes->class('rounded-xl bg-brand-soft px-3.5 py-3 text-sm text-brand-soft-ink') }}>
    {{ $slot }}
</div>
