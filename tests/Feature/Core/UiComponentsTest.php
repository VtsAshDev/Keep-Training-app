<?php

test('button renders the primary variant by default', function () {
    $this->blade('<x-ui.button>Salvar</x-ui.button>')
        ->assertSee('type="button"', false)
        ->assertSee('bg-brand-500 text-on-brand', false)
        ->assertSee('Salvar');
});

test('button renders secondary and ghost variants', function (string $variant, string $class) {
    $this->blade('<x-ui.button :variant="$variant">Ok</x-ui.button>', ['variant' => $variant])
        ->assertSee($class, false)
        ->assertDontSee('bg-brand-500', false);
})->with([
    'secondary' => ['secondary', 'border border-line bg-surface-raised'],
    'ghost' => ['ghost', 'hover:bg-surface-muted'],
]);

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

test('checkbox renders its label', function () {
    $this->blade('<x-ui.checkbox label="Creatina" checked />')
        ->assertSee('type="checkbox"', false)
        ->assertSee('checked', false)
        ->assertSee('Creatina');
});

test('card renders title and actions', function () {
    $this->blade('<x-ui.card title="Ficha B"><x-slot:actions>Editar</x-slot> Conteúdo</x-ui.card>')
        ->assertSeeInOrder(['Ficha B', 'Editar', 'Conteúdo']);
});

test('modal listens for its own name', function () {
    $this->blade('<x-ui.modal name="swap-plan" title="Trocar ficha">Corpo<x-slot:footer>Rodapé</x-slot></x-ui.modal>')
        ->assertSee('<dialog', false)
        ->assertSee('swap-plan', false)
        ->assertSeeInOrder(['Trocar ficha', 'Corpo', 'Rodapé']);
});

test('bottom nav shows the core destinations', function () {
    $this->blade('<x-ui.bottom-nav />')
        ->assertSeeInOrder(['Hoje', 'Treino', 'Configurações'])
        ->assertSee(url('/treino/fichas'), false)
        ->assertSee(url('/configuracoes'), false);
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
        ->assertSeeInOrder(['Button', 'Input', 'Checkbox', 'Card', 'Modal', 'Bottom nav']);
});
