<?php

use App\Actions\Training\StartWorkoutSession;
use App\Enums\Training\MuscleGroup;
use App\Models\Training\Exercise;
use App\Models\Training\Supplement;
use App\Models\Training\WorkoutPlan;
use App\Models\Training\WorkoutPlanExercise;
use App\Models\Training\WorkoutSession;
use App\Models\Training\WorkoutSet;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

beforeEach(function () {
    $this->travelTo(now()->parse('2026-10-22 18:40'));
    $this->user = User::factory()->create();
});

test('training pages require login', function (string $url) {
    $this->get($url)->assertRedirect('/login');
})->with(['/treino/fichas', '/treino/historico', '/treino/exercicios', '/treino/suplementos', '/configuracoes']);

test('every training page renders with the section tabs', function (string $url, string $active) {
    $this->actingAs($this->user)->get($url)
        ->assertOk()
        ->assertSeeInOrder(['Treino', 'Fichas', 'Histórico', 'Exercícios', 'Suplementos'])
        ->assertSee($active);
})->with([
    ['/treino/fichas', 'Crie sua primeira ficha'],
    ['/treino/historico', 'Nenhum treino ainda'],
    ['/treino/exercicios', 'Cadastre seus exercícios'],
    ['/treino/suplementos', 'Nenhum suplemento ainda'],
]);

test('creating a plan opens its editor', function () {
    $component = Livewire::actingAs($this->user)->test('training.plans')->call('create');

    $plan = $this->user->workoutPlans()->sole();
    expect($plan->name)->toBe('Nova ficha');
    $component->assertRedirect(route('training.plans.edit', $plan->id));
});

test('plans are listed in rotation order with the next one highlighted and can be moved', function () {
    $a = WorkoutPlan::factory()->for($this->user)->create(['name' => 'Peito', 'position' => 1]);
    $b = WorkoutPlan::factory()->for($this->user)->create(['name' => 'Costas', 'position' => 2]);

    Livewire::actingAs($this->user)->test('training.plans')
        ->assertSeeInOrder(['Peito', 'Costas'])
        ->assertSee('Próxima do rodízio')
        ->call('move', $b->id, 0)
        ->assertSeeInOrder(['Costas', 'Peito']);
});

test('the plan editor adds, configures and saves exercises', function () {
    $plan = WorkoutPlan::factory()->for($this->user)->create(['name' => 'Nova ficha']);
    $row = Exercise::factory()->for($this->user)->create(['name' => 'Remada curvada']);

    $component = Livewire::actingAs($this->user)->test('training.plan-editor', ['plan' => $plan->id])
        ->set('name', 'Costas e bíceps')
        ->call('addExercise', $row->id)
        ->set('newExerciseName', 'Rosca direta')
        ->set('newExerciseGroup', MuscleGroup::Biceps->value)
        ->call('createExercise');

    $keys = array_keys($component->get('items'));
    $component->set("items.{$keys[0]}.sets", 4)
        ->set("items.{$keys[0]}.reps_min", 6)
        ->set("items.{$keys[0]}.reps_max", 8)
        ->call('save')
        ->assertRedirect(route('training.plans'));

    $plan->refresh()->load('exercises.exercise');
    expect($plan->name)->toBe('Costas e bíceps')
        ->and($plan->exercises->map(fn ($item) => [$item->exercise->name, $item->sets, $item->reps_min, $item->reps_max])->all())
        ->toBe([['Remada curvada', 4, 6, 8], ['Rosca direta', 3, 8, 12]]);
});

test('the plan editor archives the plan', function () {
    $plan = WorkoutPlan::factory()->for($this->user)->create();

    Livewire::actingAs($this->user)->test('training.plan-editor', ['plan' => $plan->id])->call('archive');

    expect($plan->fresh()->isArchived())->toBeTrue();
});

test('the plan editor and the workout refuse records of another user', function () {
    $this->actingAs($this->user)->get(route('training.plans.edit', WorkoutPlan::factory()->create()->id))->assertNotFound();
    $this->actingAs($this->user)->get(route('training.session', WorkoutSession::factory()->inProgress()->create()->id))->assertNotFound();
    $this->actingAs($this->user)->get(route('training.summary', WorkoutSession::factory()->create()->id))->assertNotFound();
});

test('the plan editor refuses exercises of another user', function () {
    $plan = WorkoutPlan::factory()->for($this->user)->create();

    expect(fn () => Livewire::actingAs($this->user)->test('training.plan-editor', ['plan' => $plan->id])->call('addExercise', Exercise::factory()->create()->id))
        ->toThrow(ModelNotFoundException::class);
});

/**
 * @return array{0: WorkoutSession, 1: Exercise}
 */
function startedWorkout(User $user): array
{
    $plan = WorkoutPlan::factory()->for($user)->create(['position' => 1]);
    $row = Exercise::factory()->for($user)->create(['name' => 'Remada curvada']);
    WorkoutPlanExercise::factory()->create(['workout_plan_id' => $plan->id, 'exercise_id' => $row->id, 'sets' => 3, 'reps_max' => 12, 'rest_seconds' => 90]);

    $previous = WorkoutSession::factory()->for($user)->create(['workout_plan_id' => $plan->id, 'started_at' => '2026-10-20 07:00', 'finished_at' => '2026-10-20 08:00']);
    foreach ([1, 2, 3] as $number) {
        WorkoutSet::factory()->create(['workout_session_id' => $previous->id, 'exercise_id' => $row->id, 'set_number' => $number, 'weight_kg' => 60, 'reps' => 12]);
    }

    return [app(StartWorkoutSession::class)->handle($user, $plan->id), $row];
}

test('the workout shows prefilled sets and the progression tip', function () {
    [$session] = startedWorkout($this->user);

    $this->actingAs($this->user)->get(route('training.session', $session->id))
        ->assertOk()
        ->assertSee('Ficha A')
        ->assertSee('Remada curvada')
        ->assertSee('60 × 12')
        ->assertSee('value="60"', false)
        ->assertSee('Você bateu 12 reps em tudo. Tente 62,5 kg.')
        ->assertDontSee('Navegação principal');
});

test('completing a set saves it and starts the rest timer', function () {
    [$session] = startedWorkout($this->user);
    $set = $session->sets->first();

    Livewire::actingAs($this->user)->test('training.workout', ['session' => $session->id])
        ->set("sets.{$set->id}.weight", '62,5')
        ->set("sets.{$set->id}.reps", '10')
        ->call('complete', $set->id)
        ->assertDispatched('rest-timer-start', seconds: 90);

    expect((float) $set->fresh()->weight_kg)->toBe(62.5)
        ->and($set->fresh()->reps)->toBe(10)
        ->and($set->fresh()->completed)->toBeTrue();
});

test('edited values are saved right away so the workout can be resumed', function () {
    [$session] = startedWorkout($this->user);
    $set = $session->sets->last();

    Livewire::actingAs($this->user)->test('training.workout', ['session' => $session->id])
        ->set("sets.{$set->id}.weight", '65');

    expect((float) $set->fresh()->weight_kg)->toBe(65.0)
        ->and($set->fresh()->completed)->toBeFalse();
});

test('finishing the workout opens the summary', function () {
    [$session] = startedWorkout($this->user);
    $set = $session->sets->first();
    $set->update(['weight_kg' => 62.5, 'reps' => 10, 'completed' => true]);

    Livewire::actingAs($this->user)->test('training.workout', ['session' => $session->id])
        ->call('finish')
        ->assertRedirect(route('training.summary', $session->id));

    $this->actingAs($this->user)->get(route('training.summary', $session->id))
        ->assertOk()
        ->assertSee('Treino concluído')
        ->assertSee('1 recorde batido')
        ->assertSee('62,5 kg × 10')
        ->assertSee('Voltar para Hoje');
});

test('a finished workout redirects to its summary', function () {
    $session = WorkoutSession::factory()->for($this->user)->create();

    Livewire::actingAs($this->user)->test('training.workout', ['session' => $session->id])
        ->assertRedirect(route('training.summary', $session->id));
});

test('history shows load progress and recent workouts', function () {
    [$session, $row] = startedWorkout($this->user);
    $session->sets->each->update(['weight_kg' => 62.5, 'reps' => 10, 'completed' => true]);
    $session->update(['finished_at' => now()->addHour()]);

    $this->actingAs($this->user)->get('/treino/historico')
        ->assertOk()
        ->assertSee('Evolução de carga')
        ->assertSee('Remada curvada')
        ->assertSee('+2,5')
        ->assertSee('Treinos recentes')
        ->assertSee('Quinta, 22 out');
});

test('exercises can be created, edited and deleted', function () {
    $component = Livewire::actingAs($this->user)->test('training.exercises')
        ->call('create')
        ->set('name', 'Supino reto')
        ->set('muscleGroup', MuscleGroup::Chest->value)
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('Supino reto');

    $exercise = $this->user->exercises()->sole();

    $component->call('edit', $exercise->id)
        ->set('name', 'Supino inclinado')
        ->call('save')
        ->assertSee('Supino inclinado')
        ->call('edit', $exercise->id)
        ->call('delete');

    expect($this->user->exercises()->count())->toBe(0);
});

test('an exercise in use cannot be deleted', function () {
    $item = WorkoutPlanExercise::factory()->create(['workout_plan_id' => WorkoutPlan::factory()->for($this->user)]);

    Livewire::actingAs($this->user)->test('training.exercises')
        ->call('edit', $item->exercise_id)
        ->call('delete')
        ->assertHasErrors('name');

    expect(Exercise::find($item->exercise_id))->not->toBeNull();
});

test('supplements can be created, edited and deleted', function () {
    $component = Livewire::actingAs($this->user)->test('training.supplements')
        ->call('create')
        ->set('name', 'Creatina')
        ->set('dose', '5')
        ->set('unit', 'g')
        ->set('scheduledTime', '08:00')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('5 g · 08:00');

    $supplement = $this->user->supplements()->sole();

    $component->call('edit', $supplement->id)
        ->set('dose', '2,5')
        ->set('usageRule', 'training_days')
        ->call('save')
        ->assertSee('Dias de treino')
        ->assertSee('2,5 g');

    $component->call('edit', $supplement->id)->call('delete');

    expect(Supplement::count())->toBe(0);
});

test('editing a supplement of another user is refused', function () {
    expect(fn () => Livewire::actingAs($this->user)->test('training.supplements')->call('edit', Supplement::factory()->create()->id))
        ->toThrow(ModelNotFoundException::class);
});

test('settings update the weekly goal and show the account', function () {
    Livewire::actingAs($this->user)->test('core.settings')
        ->assertSee($this->user->email)
        ->assertSee('Unidade de carga')
        ->set('goal', 5);

    expect($this->user->fresh()->weekly_workout_goal)->toBe(5);
});

test('signing out goes back to the entry page', function () {
    $this->actingAs($this->user)->post('/sair')->assertRedirect('/');

    $this->assertGuest();
});
