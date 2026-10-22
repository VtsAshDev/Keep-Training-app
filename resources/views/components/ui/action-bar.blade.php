{{-- Sticky bottom bar with the screen actions (e.g. Adicionar exercício / Salvar). --}}
<div {{ $attributes->class('sticky bottom-0 flex gap-2 border-t border-line bg-bg px-4 pt-3 pb-[max(1rem,env(safe-area-inset-bottom))] *:flex-1') }}>
    {{ $slot }}
</div>
