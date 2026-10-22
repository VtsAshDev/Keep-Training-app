<?php

use App\Models\Training\Supplement;
use App\Models\Training\WorkoutPlan;
use App\Models\Training\WorkoutPlanExercise;
use App\Models\Training\WorkoutSession;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

beforeEach(fn () => $this->travelTo(now()->parse('2026-10-22 10:00')));

test('first access shows the empty state and the supplements invitation', function () {
    $this->actingAs(User::factory()->create(['name' => 'Vitor Guimarães']))
        ->get('/hoje')
        ->assertOk()
        ->assertSee('Bem-vindo, Vitor')
        ->assertSee('Crie sua primeira ficha')
        ->assertSee('Adicionar suplemento')
        ->assertSee('Quinta, 22 out');
});

test('shows the suggested plan and starts it', function () {
    $user = User::factory()->create();
    $plan = WorkoutPlan::factory()->for($user)->create(['name' => 'Costas e bíceps', 'position' => 1]);
    WorkoutPlanExercise::factory()->count(2)->create(['workout_plan_id' => $plan->id]);

    $component = Livewire::actingAs($user)->test('core.today')
        ->assertSee('Ficha A')
        ->assertSee('Costas e bíceps · 2 exercícios')
        ->call('start', $plan->id);

    $session = $user->workoutSessions()->sole();
    $component->assertRedirect(route('training.session', $session->id));
});

test('offers to resume the workout in progress instead of starting another', function () {
    $user = User::factory()->create();
    $session = WorkoutSession::factory()->for($user)->inProgress()->create(['started_at' => '2026-10-22 09:40']);

    $this->actingAs($user)->get('/hoje')
        ->assertSee('Treino em andamento')
        ->assertSee('Começou às 09:40')
        ->assertSee(route('training.session', $session->id))
        ->assertDontSee('Iniciar treino');
});

test('training day supplements are disabled until a workout starts and daily ones toggle', function () {
    $user = User::factory()->create();
    $daily = Supplement::factory()->for($user)->create(['name' => 'Creatina']);
    Supplement::factory()->for($user)->trainingDays()->create(['name' => 'Whey protein']);

    Livewire::actingAs($user)->test('core.today')
        ->assertSee('Se treinar hoje')
        ->assertSee('0 de 1')
        ->call('toggleSupplement', $daily->id)
        ->assertSee('1 de 1');
});

test('refusals from the rules show as a message', function () {
    $user = User::factory()->create();
    $whey = Supplement::factory()->for($user)->trainingDays()->create();

    Livewire::actingAs($user)->test('core.today')
        ->call('toggleSupplement', $whey->id)
        ->assertHasErrors('training')
        ->assertSee('Este suplemento só pode ser marcado depois de iniciar o treino do dia.');
});

test('cannot toggle a supplement of another user', function () {
    $foreign = Supplement::factory()->create();

    expect(fn () => Livewire::actingAs(User::factory()->create())->test('core.today')->call('toggleSupplement', $foreign->id))
        ->toThrow(ModelNotFoundException::class);
    expect($foreign->logs()->count())->toBe(0);
});
