<?php

use App\Models\Training\WorkoutPlan;
use App\Models\User;
use Livewire\Livewire;

test('validation messages are in Portuguese with friendly field names', function () {
    Livewire::actingAs(User::factory()->create())->test('training.supplements')
        ->call('create')
        ->set('dose', '0')
        ->call('save')
        ->assertHasErrors(['name', 'dose', 'unit'])
        ->assertSee('O campo nome é obrigatório.')
        ->assertSee('O campo dose precisa ser pelo menos 0.01.')
        ->assertSee('O campo unidade é obrigatório.');
});

test('login validation is in Portuguese', function () {
    Livewire::test('core.login')
        ->set('email', 'nao-e-email')
        ->set('password', '123')
        ->call('login')
        ->assertSee('O campo e-mail precisa ser um e-mail válido.')
        ->assertSee('O campo senha precisa ter pelo menos 8 caracteres.');
});

test('plan item fields use their names in messages', function () {
    $user = User::factory()->create();
    $plan = WorkoutPlan::factory()->for($user)->create();

    $component = Livewire::actingAs($user)->test('training.plan-editor', ['plan' => $plan->id])
        ->set('newExerciseName', 'Remada')
        ->set('newExerciseGroup', 'back')
        ->call('createExercise');

    $key = array_key_first($component->get('items'));
    $component->set("items.{$key}.reps_max", '')
        ->call('save')
        ->assertSee('O campo repetições máximas é obrigatório.');
});
