@php
    $modal = "dev-modal-{$theme}";
    $brand = ['bg-brand-50', 'bg-brand-100', 'bg-brand-200', 'bg-brand-300', 'bg-brand-400', 'bg-brand-500', 'bg-brand-600', 'bg-brand-700', 'bg-brand-800', 'bg-brand-900', 'bg-brand-950'];
    $surfaces = ['surface' => 'bg-surface', 'surface-raised' => 'bg-surface-raised', 'surface-muted' => 'bg-surface-muted', 'line' => 'bg-line'];
@endphp

<div class="mx-auto flex max-w-md flex-col gap-6 px-4 py-8">
    <header class="flex items-center justify-between">
        <x-ui.logo />
        <span class="font-display text-sm font-semibold uppercase tracking-widest text-ink-muted">{{ $theme }}</span>
    </header>

    <x-ui.card title="Button">
        <div class="flex flex-wrap gap-2">
            <x-ui.button>Primary</x-ui.button>
            <x-ui.button variant="secondary">Secondary</x-ui.button>
            <x-ui.button variant="ghost">Ghost</x-ui.button>
        </div>
        <div class="mt-3 flex flex-wrap items-center gap-2">
            <x-ui.button size="sm">Small</x-ui.button>
            <x-ui.button size="lg">Large</x-ui.button>
            <x-ui.button disabled>Disabled</x-ui.button>
            <x-ui.button variant="secondary" href="#">Link</x-ui.button>
        </div>
    </x-ui.card>

    <x-ui.card title="Input">
        <div class="flex flex-col gap-4">
            <x-ui.input label="E-mail" name="email-{{ $theme }}" type="email" placeholder="voce@exemplo.com" />
            <x-ui.input label="Carga (kg)" name="weight-{{ $theme }}" type="number" inputmode="decimal" error="Informe a carga." class="tabular-nums" />
            <x-ui.input label="Descanso" name="rest-{{ $theme }}" hint="Em segundos." value="90" class="tabular-nums" />
            <x-ui.input label="Desabilitado" name="disabled-{{ $theme }}" value="Somente leitura" disabled />
        </div>
    </x-ui.card>

    <x-ui.card title="Checkbox">
        <div class="flex flex-col gap-3">
            <x-ui.checkbox label="Creatina · 5 g" checked />
            <x-ui.checkbox label="Whey · 30 g" />
            <x-ui.checkbox label="Desabilitado" disabled />
        </div>
    </x-ui.card>

    <x-ui.card title="Card">
        <x-slot:actions>
            <x-ui.button size="sm" variant="ghost">Editar</x-ui.button>
        </x-slot>

        <p class="text-ink-muted">Treino de hoje</p>
        <p class="font-display text-3xl font-bold uppercase">Ficha B</p>
        <p class="mt-2 font-display text-5xl font-bold tabular-nums">3<span class="text-ink-muted">/4</span></p>
        <p class="text-sm text-ink-muted">treinos na semana</p>
    </x-ui.card>

    <x-ui.card title="Modal">
        <x-ui.button variant="secondary" x-data x-on:click="$dispatch('open-modal', '{{ $modal }}')">Abrir modal</x-ui.button>

        <x-ui.modal :name="$modal" title="Trocar ficha">
            <p class="text-ink-muted">O rodízio segue a partir da ficha que você realmente fizer.</p>

            <x-slot:footer>
                <x-ui.button variant="ghost" x-on:click="$dispatch('close-modal', '{{ $modal }}')">Cancelar</x-ui.button>
                <x-ui.button x-on:click="$dispatch('close-modal', '{{ $modal }}')">Confirmar</x-ui.button>
            </x-slot>
        </x-ui.modal>
    </x-ui.card>

    <x-ui.card title="Bottom nav">
        <div class="-mx-5 -mb-5 overflow-hidden rounded-b-2xl">
            <x-ui.bottom-nav :fixed="false" />
        </div>
    </x-ui.card>

    <x-ui.card title="Cores">
        <div class="grid grid-cols-6 gap-1">
            @foreach ($brand as $class)
                <div class="aspect-square rounded-md {{ $class }}" title="{{ Str::after($class, 'bg-') }}"></div>
            @endforeach
            <div class="grid aspect-square place-items-center rounded-md bg-brand-500 font-display font-bold text-on-brand">Aa</div>
        </div>
        <div class="mt-3 grid grid-cols-4 gap-2 text-xs">
            @foreach ($surfaces as $token => $class)
                <div>
                    <div class="h-10 rounded-md border border-line {{ $class }}"></div>
                    <span class="text-ink-muted">{{ $token }}</span>
                </div>
            @endforeach
        </div>
        <p class="mt-3"><span class="text-ink">ink</span> · <span class="text-ink-muted">ink-muted</span> · <span class="text-accent">accent</span> · <span class="text-danger">danger</span> · <span class="text-success">success</span></p>
    </x-ui.card>

    <x-ui.card title="Tipografia">
        <p class="font-display text-4xl font-bold uppercase">Barlow Condensed</p>
        <p class="font-display text-2xl font-semibold uppercase">Supino reto · 4 × 8–12</p>
        <p class="mt-2">Barlow para o texto corrido: ação, ênfase e acentuação em português.</p>
        <p class="font-display text-4xl font-bold tabular-nums">82,5 kg · 1:30</p>
    </x-ui.card>
</div>
