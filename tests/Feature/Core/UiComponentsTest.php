<?php

test('button renders the primary variant by default', function () {
    $this->blade('<x-ui.button>Salvar</x-ui.button>')
        ->assertSee('type="button"', false)
        ->assertSee('bg-brand-500 text-on-brand', false)
        ->assertSee('min-h-13', false)
        ->assertSee('Salvar');
});

test('button renders secondary, ghost and add variants', function (string $variant, string $class) {
    $this->blade('<x-ui.button :variant="$variant">Ok</x-ui.button>', ['variant' => $variant])
        ->assertSee($class, false)
        ->assertDontSee('bg-brand-500', false);
})->with([
    'secondary' => ['secondary', 'border border-line text-ink'],
    'ghost' => ['ghost', 'hover:bg-surface-2'],
    'add' => ['add', 'border-dashed border-line text-brand-text'],
]);

test('add button shows the plus icon', function () {
    $this->blade('<x-ui.button variant="add">Nova ficha</x-ui.button>')
        ->assertSee('M12 5v14M5 12h14', false);
});

test('button with href renders a link', function () {
    $this->blade('<x-ui.button href="/hoje">Hoje</x-ui.button>')
        ->assertSee('<a href="/hoje"', false)
        ->assertDontSee('<button', false);
});

test('input links its label and shows the validation error', function () {
    $this->withViewErrors(['email' => 'E-mail inválido.'])
        ->blade('<x-ui.input label="E-mail" wire:model="email" />')
        ->assertSee('for="email"', false)
        ->assertSee('id="email"', false)
        ->assertSee('aria-invalid="true"', false)
        ->assertSee('E-mail inválido.');
});

test('input shows the hint when there is no error', function () {
    $this->blade('<x-ui.input label="Descanso" name="rest" hint="Em segundos." />')
        ->assertSee('Em segundos.')
        ->assertDontSee('aria-invalid', false);
});

test('numeric input is a centered cell with a hidden label', function () {
    $this->blade('<x-ui.input numeric hide-label label="Repetições mínimas" name="min" />')
        ->assertSee('class="sr-only"', false)
        ->assertSee('text-center font-display', false);
});

test('select renders its options', function () {
    $this->blade('<x-ui.select label="Exercício" name="exercise"><option>Remada</option></x-ui.select>')
        ->assertSee('<select', false)
        ->assertSeeInOrder(['Exercício', 'Remada']);
});

test('checkbox renders its label', function () {
    $this->blade('<x-ui.checkbox label="Creatina" checked />')
        ->assertSee('type="checkbox"', false)
        ->assertSee('checked', false)
        ->assertSee('Creatina');
});

test('card renders title and aside', function () {
    $this->blade('<x-ui.card title="Meta da semana"><x-slot:aside>3 de 4</x-slot> Conteúdo</x-ui.card>')
        ->assertSeeInOrder(['Meta da semana', '3 de 4', 'Conteúdo']);
});

test('card variants', function (string $variant, string $class) {
    $this->blade('<x-ui.card :variant="$variant">X</x-ui.card>', ['variant' => $variant])
        ->assertSee($class, false);
})->with([
    'highlight' => ['highlight', 'border-brand-500 bg-surface'],
    'brand' => ['brand', 'bg-brand-500 text-on-brand'],
    'dashed' => ['dashed', 'border-dashed border-brand-500'],
]);

// wire:ignore.self: without it, any Livewire update (search, validation error) closed the open modal.
test('modal listens for its own name and survives Livewire updates', function () {
    $this->blade('<x-ui.modal name="swap-plan" title="Trocar ficha">Corpo<x-slot:footer>Rodapé</x-slot></x-ui.modal>')
        ->assertSee('<dialog', false)
        ->assertSee('wire:ignore.self', false)
        ->assertSee('swap-plan', false)
        ->assertSeeInOrder(['Trocar ficha', 'Corpo', 'Rodapé']);
});

test('tag renders with the soft brand colors', function () {
    $this->blade('<x-ui.tag>Se treinar hoje</x-ui.tag>')
        ->assertSee('bg-brand-soft', false)
        ->assertSee('Se treinar hoje');
});

test('stepper exposes its bounds to alpine', function () {
    $this->blade('<x-ui.stepper label="Séries" :value="3" :min="1" :max="10" />')
        ->assertSee('x-modelable="value"', false)
        ->assertSee('value: 3, min: 1, max: 10, step: 1', false)
        ->assertSee('aria-label="Diminuir"', false)
        ->assertSee('aria-label="Aumentar"', false);
});

test('stepper forwards wire:model to the alpine root', function () {
    $this->blade('<x-ui.stepper wire:model="goal" :value="4" />')
        ->assertSee('wire:model="goal"', false);
});

test('tabs mark the active item', function () {
    $this->blade('<x-ui.tabs :items="$items" />', ['items' => [
        ['label' => 'Fichas', 'href' => '/treino/fichas', 'active' => true],
        ['label' => 'Histórico', 'href' => '/treino/historico'],
    ]])
        ->assertSee('aria-current="page"', false)
        ->assertSeeInOrder(['Fichas', 'Histórico']);
});

test('bottom nav shows Hoje, Treino and Ajustes', function () {
    $this->blade('<x-ui.bottom-nav />')
        ->assertSeeInOrder(['Hoje', 'Treino', 'Ajustes'])
        ->assertSee(url('/treino/fichas'), false)
        ->assertSee(url('/configuracoes'), false);
});

test('top bar renders the back link and the action', function () {
    $this->blade('<x-ui.top-bar back="/hoje" back-label="Voltar para Hoje" title="Ficha B" subtitle="32:14"><button>Finalizar</button></x-ui.top-bar>')
        ->assertSee('href="/hoje"', false)
        ->assertSee('aria-label="Voltar para Hoje"', false)
        ->assertSeeInOrder(['Ficha B', '32:14', 'Finalizar']);
});

test('list row with href renders a link with chevron and highlight', function () {
    $this->blade('<x-ui.list-row href="/treino/fichas/2" handle letter="B" letter-active highlight title="Costas e bíceps" subtitle="Próxima do rodízio" />')
        ->assertSee('<a', false)
        ->assertSee('border-brand-500', false)
        ->assertSee('M9 5l7 7-7 7', false)
        ->assertSeeInOrder(['B', 'Costas e bíceps', 'Próxima do rodízio']);
});

test('list row without href renders a div with the trailing slot', function () {
    $this->blade('<x-ui.list-row title="Creatina"><x-slot:trailing>Editar</x-slot></x-ui.list-row>')
        ->assertDontSee('<a', false)
        ->assertDontSee('M9 5l7 7-7 7', false)
        ->assertSee('Editar');
});

test('set row renders the done state as text', function () {
    $this->blade('<x-ui.set-row number="1" previous="60 × 12" weight="62,5" reps="10" state="done" />')
        ->assertSee('bg-surface-2', false)
        ->assertSee('aria-label="Série 1 concluída"', false)
        ->assertDontSee('<input', false)
        ->assertSeeInOrder(['60 × 12', '62,5', '10']);
});

// live.blur: in Livewire 4 a plain .blur only syncs in the browser and never saves the set.
test('set row renders inputs bound to livewire for the current set', function () {
    $this->blade('<x-ui.set-row number="2" weight="62,5" reps="12" state="current" weight-model="sets.2.weight" reps-model="sets.2.reps" action="completeSet(2)" />')
        ->assertSee('wire:model.live.blur="sets.2.weight"', false)
        ->assertSee('wire:model.live.blur="sets.2.reps"', false)
        ->assertSee('wire:click="completeSet(2)"', false)
        ->assertSee('Carga da série 2')
        ->assertSee('aria-label="Concluir série 2"', false);
});

test('set table renders the column headers', function () {
    $this->blade('<x-ui.set-table>linhas</x-ui.set-table>')
        ->assertSeeInOrder(['Série', 'Anterior', 'kg', 'Reps', 'linhas']);
});

test('supplement item renders the tag and disabled state', function () {
    $this->blade('<x-ui.supplement-item name="Whey protein" detail="30 g · pós-treino" tag="Se treinar hoje" disabled />')
        ->assertSee('disabled', false)
        ->assertSeeInOrder(['Whey protein', '30 g · pós-treino', 'Se treinar hoje']);
});

test('rest timer listens for the start event', function () {
    $this->blade('<x-ui.rest-timer :seconds="90" />')
        ->assertSee('x-on:rest-timer-start.window', false)
        ->assertSee('x-init="90 > 0 && start(90)"', false)
        ->assertSeeInOrder(['Descanso', '−15', '+15', 'Pular']);
});

test('week dots render the seven days with their states', function () {
    $view = $this->blade('<x-ui.week-dots :days="$days" />', ['days' => ['done', 'done', 'pending', 'today', 'pending', 'pending', 'pending']]);

    $view->assertSeeInOrder(['S', 'T', 'Q', 'Q', 'S', 'S', 'D']);
    expect(substr_count((string) $view, 'data-state="done"'))->toBe(2)
        ->and(substr_count((string) $view, 'data-state="today"'))->toBe(1);
});

test('progress bar clamps its value', function () {
    $this->blade('<x-ui.progress-bar :value="140" />')
        ->assertSee('aria-valuenow="100"', false)
        ->assertSee('width: 100%', false);
});

test('stat renders label, value and unit', function () {
    $this->blade('<x-ui.stat boxed label="Volume" value="8.420" unit="kg" />')
        ->assertSeeInOrder(['Volume', '8.420', 'kg']);
});

test('record banner lists the records', function () {
    $this->blade('<x-ui.record-banner title="2 recordes batidos" :records="$records" />', ['records' => [
        ['label' => 'Remada curvada', 'value' => '62,5 kg × 10'],
        ['label' => 'Rosca direta', 'value' => '16 kg × 12'],
    ]])
        ->assertSeeInOrder(['2 recordes batidos', 'Remada curvada', '62,5 kg × 10', 'Rosca direta', '16 kg × 12']);
});

test('empty state renders title, text and action', function () {
    $this->blade('<x-ui.empty-state title="Crie sua primeira ficha" text="Ex.: Ficha A"><a href="#">Criar ficha</a></x-ui.empty-state>')
        ->assertSee('border-dashed', false)
        ->assertSeeInOrder(['Crie sua primeira ficha', 'Ex.: Ficha A', 'Criar ficha']);
});

test('line chart maps the lowest and highest points to the chart edges', function () {
    $this->blade('<x-ui.line-chart :points="[50, 55, 62.5]" :labels="[\'3 set\', \'22 out\']" label="Evolução" />')
        ->assertSee('points="20,120 174,80 328,20"', false)
        ->assertSee('aria-label="Evolução"', false)
        ->assertSeeInOrder(['3 set', '22 out']);
});

test('logo renders the wordmark as text', function () {
    $this->blade('<x-ui.logo stacked />')
        ->assertSee('Keep<br><span class="text-brand-text">Focus</span>', false);
});

test('dev ui page is not available outside the local environment', function () {
    $this->get('/dev/ui')->assertNotFound();
});

test('dev ui view shows every component in both modes', function () {
    $this->view('dev.ui')
        ->assertSee('data-theme="light"', false)
        ->assertSee('data-theme="dark"', false)
        ->assertSee('dev-modal-light', false)
        ->assertSee('dev-modal-dark', false)
        ->assertSeeInOrder([
            'Marca', 'Cores', 'Tipografia', 'Button', 'Input e select', 'Checkbox e tag', 'Stepper',
            'Card, week-dots e progress-bar', 'Supplement-item', 'Empty-state', 'Tabs e list-row',
            'Top-bar', 'Set-row', 'Rest-timer', 'Stat e record-banner', 'Line-chart', 'Modal', 'Bottom-nav',
        ]);
});
