@php
    $modal = "dev-modal-{$theme}";
    $brand = ['bg-brand-50', 'bg-brand-100', 'bg-brand-200', 'bg-brand-300', 'bg-brand-400', 'bg-brand-500', 'bg-brand-600', 'bg-brand-700', 'bg-brand-800', 'bg-brand-900'];
    $surfaces = ['bg' => 'bg-bg', 'surface' => 'bg-surface', 'surface-2' => 'bg-surface-2', 'line' => 'bg-line', 'brand-soft' => 'bg-brand-soft'];
    $section = 'font-display text-sm font-semibold uppercase tracking-widest text-ink-muted';
@endphp

<div class="mx-auto flex max-w-md flex-col gap-4 px-5 py-8">
    <header class="flex items-center justify-between">
        <x-ui.logo size="sm" />
        <span class="{{ $section }}">{{ $theme }}</span>
    </header>

    <h2 class="{{ $section }}">Marca</h2>
    <div class="flex items-center gap-5">
        <x-ui.monogram />
        <x-ui.logo size="lg" stacked />
    </div>
    <div class="flex gap-3">
        @foreach (['icon-192.png', 'icon-maskable-512.png', 'apple-touch-icon.png', 'favicon-32.png'] as $file)
            <img src="/icons/{{ $file }}" alt="{{ $file }}" title="{{ $file }}" class="size-12 rounded-md">
        @endforeach
    </div>

    <h2 class="{{ $section }}">Cores</h2>
    <div class="grid grid-cols-10 gap-1">
        @foreach ($brand as $class)
            <div class="aspect-square rounded-md {{ $class }}" title="{{ Str::after($class, 'bg-') }}"></div>
        @endforeach
    </div>
    <div class="grid grid-cols-5 gap-2 text-xs">
        @foreach ($surfaces as $token => $class)
            <div>
                <div class="h-10 rounded-md border border-line {{ $class }}"></div>
                <span class="text-ink-muted">{{ $token }}</span>
            </div>
        @endforeach
    </div>
    <p class="text-sm">
        <span class="text-ink">ink</span> ·
        <span class="text-ink-muted">ink-muted</span> ·
        <span class="text-brand-text">brand-text</span> ·
        <span class="text-brand-soft-ink">brand-soft-ink</span> ·
        <span class="text-danger">danger</span> ·
        <span class="rounded bg-brand-500 px-1 text-on-brand">on-brand</span>
    </p>

    <h2 class="{{ $section }}">Tipografia</h2>
    <div>
        <p class="font-display text-[40px] leading-tight font-semibold uppercase">Ficha B</p>
        <p class="font-display text-[26px] font-semibold uppercase">Remada curvada</p>
        <p>Barlow para o texto corrido: ação, ênfase e acentuação em português.</p>
        <p class="font-display text-[38px] font-semibold tabular-nums">62,5 kg · 01:12</p>
    </div>

    <h2 class="{{ $section }}">Button</h2>
    <div class="flex gap-2">
        <x-ui.button icon="play" class="flex-1">Iniciar treino</x-ui.button>
        <x-ui.button variant="secondary">Trocar</x-ui.button>
    </div>
    <div class="flex flex-wrap gap-2">
        <x-ui.button variant="ghost">Ghost</x-ui.button>
        <x-ui.button size="sm" variant="secondary">Pequeno</x-ui.button>
        <x-ui.button disabled>Desabilitado</x-ui.button>
        <x-ui.button variant="secondary" href="#">Link</x-ui.button>
    </div>
    <x-ui.button variant="add">Nova ficha</x-ui.button>

    <h2 class="{{ $section }}">Input e select</h2>
    <x-ui.input label="Nome da ficha" name="plan-{{ $theme }}" value="Costas e bíceps" />
    <x-ui.input label="Carga (kg)" name="weight-{{ $theme }}" inputmode="decimal" error="Informe a carga." />
    <x-ui.input label="Observação" name="notes-{{ $theme }}" hint="Opcional." />
    <div class="flex items-center gap-2">
        <x-ui.input numeric hide-label label="Repetições mínimas" name="min-{{ $theme }}" value="8" />
        <span class="text-ink-muted">até</span>
        <x-ui.input numeric hide-label label="Repetições máximas" name="max-{{ $theme }}" value="12" />
    </div>
    <x-ui.select label="Evolução de carga" name="exercise-{{ $theme }}">
        <option>Remada curvada</option>
        <option>Puxada frontal</option>
    </x-ui.select>

    <h2 class="{{ $section }}">Checkbox e tag</h2>
    <div class="flex flex-wrap items-center gap-4">
        <x-ui.checkbox label="Marcado" checked />
        <x-ui.checkbox label="Vazio" />
        <x-ui.checkbox label="Desabilitado" disabled />
        <x-ui.tag>Se treinar hoje</x-ui.tag>
    </div>

    <h2 class="{{ $section }}">Stepper</h2>
    <div class="grid grid-cols-2 gap-2.5">
        <x-ui.stepper label="Séries" :value="3" :min="1" :max="10" />
        <x-ui.stepper label="Descanso" :value="90" :min="0" :step="15" unit="s" />
    </div>
    <x-ui.stepper size="lg" :value="4" :min="1" :max="7" unit="treinos por semana" />

    <h2 class="{{ $section }}">Card, week-dots e progress-bar</h2>
    <x-ui.card title="Treino de hoje">
        <div class="mt-1 font-display text-[40px] leading-tight font-semibold uppercase">Ficha B</div>
        <div class="text-[15px] text-ink-muted">Costas e bíceps · 6 exercícios</div>
        <div class="mt-4 flex gap-2">
            <x-ui.button icon="play" class="flex-1">Iniciar treino</x-ui.button>
            <x-ui.button variant="secondary">Trocar</x-ui.button>
        </div>
    </x-ui.card>
    <x-ui.card title="Meta da semana">
        <x-slot:aside>
            <span class="font-display text-[22px] font-semibold tabular-nums">3 <span class="text-base text-ink-muted">de 4</span></span>
        </x-slot>
        <x-ui.week-dots class="mt-3" :days="['done', 'done', 'pending', 'today', 'pending', 'pending', 'pending']" />
        <x-ui.progress-bar class="mt-3" :value="75" label="Meta da semana" />
        <p class="mt-3.5 flex items-center gap-1.5 text-sm text-brand-text"><x-ui.icon name="flame" class="size-[18px]" /> 6 semanas seguidas cumprindo a meta</p>
    </x-ui.card>
    <x-ui.card variant="brand" title="Treino em andamento">
        <div class="mt-1 font-display text-[40px] leading-tight font-semibold uppercase">Ficha B</div>
        <div class="text-[15px] tabular-nums">Começou às 18:40 · 7 de 18 séries</div>
    </x-ui.card>

    <h2 class="{{ $section }}">Supplement-item</h2>
    <x-ui.card title="Suplementos de hoje" class="py-2">
        <x-slot:aside>
            <span class="text-sm text-ink-muted tabular-nums">1 de 2</span>
        </x-slot>
        <div class="mt-1">
            <x-ui.supplement-item name="Creatina" detail="5 g · manhã" checked />
            <x-ui.supplement-item name="Vitamina D" detail="2.000 UI · almoço" />
            <x-ui.supplement-item name="Whey protein" detail="30 g · pós-treino" tag="Se treinar hoje" disabled />
        </div>
    </x-ui.card>

    <h2 class="{{ $section }}">Empty-state</h2>
    <x-ui.empty-state title="Crie sua primeira ficha" text="Ex.: Ficha A peito e tríceps, Ficha B costas e bíceps, Ficha C pernas.">
        <x-ui.button href="#">Criar ficha</x-ui.button>
    </x-ui.empty-state>

    <h2 class="{{ $section }}">Tabs e list-row</h2>
    <x-ui.tabs label="Seções do treino" :items="[
        ['label' => 'Fichas', 'href' => '#', 'active' => true],
        ['label' => 'Histórico', 'href' => '#'],
        ['label' => 'Exercícios', 'href' => '#'],
        ['label' => 'Suplementos', 'href' => '#'],
    ]" />
    <x-ui.list-row href="#" handle letter="A" title="Peito e tríceps" subtitle="6 exercícios · terça" />
    <x-ui.list-row href="#" handle letter="B" letter-active highlight accent-subtitle title="Costas e bíceps" subtitle="Próxima do rodízio" />
    <x-ui.list-row title="Creatina" subtitle="5 g · 08:00 · com água">
        <x-slot:trailing>
            <button type="button" aria-label="Editar Creatina" class="grid size-11 place-items-center rounded-xl text-ink-muted hover:bg-surface-2"><x-ui.icon name="edit" /></button>
        </x-slot>
    </x-ui.list-row>
    <x-ui.list-row letter="B" title="Quinta, 22 out" subtitle="54 min · 8.420 kg · 2 recordes" />

    <h2 class="{{ $section }}">Top-bar</h2>
    <div class="-mx-5 overflow-hidden border-y border-line">
        <x-ui.top-bar back="#" back-label="Voltar para Hoje" title="Ficha B" subtitle="32:14 · 7 de 18 séries" class="pt-3">
            <x-ui.button size="sm" variant="secondary">Finalizar</x-ui.button>
        </x-ui.top-bar>
        <x-ui.progress-bar size="sm" :value="39" label="Progresso do treino" />
    </div>

    <h2 class="{{ $section }}">Set-row</h2>
    <x-ui.card variant="highlight">
        <div class="font-display text-[26px] leading-tight font-semibold uppercase">Remada curvada</div>
        <div class="text-sm text-ink-muted">3 séries · 8–12 reps · 90 s</div>
        <div class="mt-3 flex items-center gap-2 rounded-[10px] bg-brand-soft px-3 py-2.5 text-sm text-brand-soft-ink">
            <x-ui.icon name="arrow-up" class="size-[18px]" :stroke="2.2" /> Você bateu 12 reps em tudo. Tente 62,5 kg.
        </div>
        <x-ui.set-table class="mt-3.5">
            <x-ui.set-row number="1" previous="60 × 12" weight="62,5" reps="10" state="done" />
            <x-ui.set-row number="2" previous="60 × 12" weight="62,5" reps="12" state="current" />
            <x-ui.set-row number="3" previous="60 × 12" weight="62,5" reps="12" />
        </x-ui.set-table>
    </x-ui.card>

    <h2 class="{{ $section }}">Rest-timer</h2>
    <div class="-mx-5">
        <x-ui.rest-timer :fixed="false" :seconds="72" />
    </div>
    <x-ui.button variant="secondary" x-data x-on:click="$dispatch('rest-timer-start', { seconds: 90 })">Iniciar descanso de 90 s</x-ui.button>

    <h2 class="{{ $section }}">Stat e record-banner</h2>
    <div class="grid grid-cols-3 gap-2">
        <x-ui.stat boxed label="Duração" value="54" unit="min" />
        <x-ui.stat boxed label="Volume" value="8.420" unit="kg" />
        <x-ui.stat boxed label="Séries" value="18" />
    </div>
    <div class="flex gap-5">
        <x-ui.stat label="Atual" value="62,5" unit="kg" />
        <x-ui.stat label="Recorde" value="62,5" unit="× 10" accent />
        <x-ui.stat label="8 treinos" value="+12,5" unit="kg" />
    </div>
    <x-ui.record-banner title="2 recordes batidos" :records="[
        ['label' => 'Remada curvada', 'value' => '62,5 kg × 10'],
        ['label' => 'Rosca direta', 'value' => '16 kg × 12'],
    ]" />

    <h2 class="{{ $section }}">Line-chart</h2>
    <x-ui.card>
        <x-ui.line-chart :points="[50, 52.5, 55, 55, 57.5, 60, 60, 62.5]" :labels="['3 set', '22 out']" label="Carga da remada curvada subiu de 50 kg para 62,5 kg nos últimos 8 treinos" />
    </x-ui.card>

    <h2 class="{{ $section }}">Modal</h2>
    <x-ui.button variant="secondary" x-data x-on:click="$dispatch('open-modal', '{{ $modal }}')">Abrir modal</x-ui.button>
    <x-ui.modal :name="$modal" title="Trocar ficha">
        <p class="text-ink-muted">O rodízio segue a partir da ficha que você realmente fizer.</p>

        <x-slot:footer>
            <x-ui.button variant="secondary" x-on:click="$dispatch('close-modal', '{{ $modal }}')">Cancelar</x-ui.button>
            <x-ui.button x-on:click="$dispatch('close-modal', '{{ $modal }}')">Confirmar</x-ui.button>
        </x-slot>
    </x-ui.modal>

    <h2 class="{{ $section }}">Bottom-nav</h2>
    <div class="-mx-5">
        <x-ui.bottom-nav :fixed="false" />
    </div>
</div>
