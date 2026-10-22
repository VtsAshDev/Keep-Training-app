<?php

use App\Actions\Training\CreateExercise;
use App\Actions\Training\DeleteExercise;
use App\Actions\Training\GetExercises;
use App\Actions\Training\UpdateExercise;
use App\Enums\Training\Equipment;
use App\Enums\Training\MuscleGroup;
use App\Exceptions\Training\TrainingRuleException;
use App\Models\Training\Exercise;
use App\Models\Training\WorkoutPlanExercise;
use App\Models\Training\WorkoutSet;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

test('creates an exercise for the user', function () {
    $user = User::factory()->create();

    $exercise = app(CreateExercise::class)->handle($user, '  Remada curvada ', MuscleGroup::Back, Equipment::Barbell);

    expect($exercise->user_id)->toBe($user->id)
        ->and($exercise->name)->toBe('Remada curvada')
        ->and($exercise->muscle_group)->toBe(MuscleGroup::Back);
});

test('lists the catalog and the user exercises grouped by muscle group, with search', function () {
    $user = User::factory()->create();
    Exercise::factory()->catalog()->create(['name' => 'Supino reto', 'muscle_group' => MuscleGroup::Chest]);
    Exercise::factory()->for($user)->create(['name' => 'Remada curvada', 'muscle_group' => MuscleGroup::Back]);
    Exercise::factory()->for($user)->create(['name' => 'Puxada frontal', 'muscle_group' => MuscleGroup::Back]);
    Exercise::factory()->create(['name' => 'Remada do vizinho', 'muscle_group' => MuscleGroup::Back]);

    $groups = app(GetExercises::class)->handle($user);

    expect($groups->keys()->all())->toBe(['back', 'chest'])
        ->and($groups['back']->pluck('name')->all())->toBe(['Puxada frontal', 'Remada curvada'])
        ->and(app(GetExercises::class)->handle($user, 'remada')->flatten()->pluck('name')->all())->toBe(['Remada curvada']);
});

test('updates only the user exercises', function () {
    $user = User::factory()->create();
    $own = Exercise::factory()->for($user)->create();

    app(UpdateExercise::class)->handle($user, $own->id, 'Rosca direta', MuscleGroup::Biceps);

    expect($own->fresh()->name)->toBe('Rosca direta');
    expect(fn () => app(UpdateExercise::class)->handle($user, Exercise::factory()->catalog()->create()->id, 'X', MuscleGroup::Abs))
        ->toThrow(ModelNotFoundException::class);
    expect(fn () => app(UpdateExercise::class)->handle($user, Exercise::factory()->create()->id, 'X', MuscleGroup::Abs))
        ->toThrow(ModelNotFoundException::class);
});

test('deletes an unused exercise', function () {
    $user = User::factory()->create();
    $exercise = Exercise::factory()->for($user)->create();

    app(DeleteExercise::class)->handle($user, $exercise->id);

    expect(Exercise::find($exercise->id))->toBeNull();
});

test('refuses to delete an exercise in a plan or in the history', function () {
    $item = WorkoutPlanExercise::factory()->create();
    $set = WorkoutSet::factory()->create();

    expect(fn () => app(DeleteExercise::class)->handle($item->plan->user, $item->exercise_id))->toThrow(TrainingRuleException::class);
    expect(fn () => app(DeleteExercise::class)->handle($set->session->user, $set->exercise_id))->toThrow(TrainingRuleException::class);
});

test('refuses to delete an exercise of another user', function () {
    expect(fn () => app(DeleteExercise::class)->handle(User::factory()->create(), Exercise::factory()->create()->id))
        ->toThrow(ModelNotFoundException::class);
});
