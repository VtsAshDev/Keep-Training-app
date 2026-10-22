<?php

use App\Actions\Training\ArchiveWorkoutPlan;
use App\Actions\Training\CreateWorkoutPlan;
use App\Actions\Training\GetWorkoutPlans;
use App\Actions\Training\MoveWorkoutPlan;
use App\Actions\Training\RestoreWorkoutPlan;
use App\Actions\Training\SaveWorkoutPlan;
use App\Models\Training\Exercise;
use App\Models\Training\WorkoutPlan;
use App\Models\Training\WorkoutPlanExercise;
use App\Models\Training\WorkoutSession;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @return list<string>
 */
function activeNames(User $user): array
{
    return $user->workoutPlans()->active()->orderBy('position')->orderBy('id')->pluck('name')->all();
}

test('new plans go to the end of the rotation', function () {
    $user = User::factory()->create();
    WorkoutPlan::factory()->for($user)->create(['position' => 3]);

    expect(app(CreateWorkoutPlan::class)->handle($user, 'Pernas')->position)->toBe(4);
});

test('saves the name and replaces the exercises in order', function () {
    $user = User::factory()->create();
    $plan = WorkoutPlan::factory()->for($user)->create();
    WorkoutPlanExercise::factory()->create(['workout_plan_id' => $plan->id]);
    [$a, $b] = Exercise::factory()->for($user)->count(2)->create();
    $catalog = Exercise::factory()->catalog()->create();

    $saved = app(SaveWorkoutPlan::class)->handle($user, $plan->id, 'Costas e bíceps', [
        ['exercise_id' => $b->id, 'sets' => 4, 'reps_min' => 6, 'reps_max' => 8, 'rest_seconds' => 120],
        ['exercise_id' => $a->id, 'sets' => 3, 'reps_min' => 12, 'reps_max' => 10, 'rest_seconds' => 60],
        ['exercise_id' => $catalog->id, 'sets' => 2, 'reps_min' => 15, 'reps_max' => 20, 'rest_seconds' => 45],
    ]);

    expect($saved->name)->toBe('Costas e bíceps')
        ->and($saved->exercises->pluck('exercise_id')->all())->toBe([$b->id, $a->id, $catalog->id])
        ->and($saved->exercises->pluck('position')->all())->toBe([1, 2, 3])
        ->and([$saved->exercises[1]->reps_min, $saved->exercises[1]->reps_max])->toBe([10, 12]);
});

test('refuses exercises of another user in a plan', function () {
    $user = User::factory()->create();
    $plan = WorkoutPlan::factory()->for($user)->create();
    $foreign = Exercise::factory()->create();

    expect(fn () => app(SaveWorkoutPlan::class)->handle($user, $plan->id, 'X', [
        ['exercise_id' => $foreign->id, 'sets' => 3, 'reps_min' => 8, 'reps_max' => 12, 'rest_seconds' => 90],
    ]))->toThrow(ModelNotFoundException::class);
    expect($plan->exercises()->count())->toBe(0);
});

test('refuses to save a plan of another user', function () {
    expect(fn () => app(SaveWorkoutPlan::class)->handle(User::factory()->create(), WorkoutPlan::factory()->create()->id, 'X', []))
        ->toThrow(ModelNotFoundException::class);
});

test('moves a plan to a new place in the rotation', function () {
    $user = User::factory()->create();
    foreach (['A', 'B', 'C', 'D'] as $i => $name) {
        WorkoutPlan::factory()->for($user)->create(['name' => $name, 'position' => $i + 1]);
    }
    $d = $user->workoutPlans()->where('name', 'D')->first();

    app(MoveWorkoutPlan::class)->handle($user, $d->id, 1);

    expect(activeNames($user))->toBe(['A', 'D', 'B', 'C']);

    app(MoveWorkoutPlan::class)->handle($user, $d->id, 10);

    expect(activeNames($user))->toBe(['A', 'B', 'C', 'D']);
});

test('moving keeps positions strictly increasing even when they were tied', function () {
    $user = User::factory()->create();
    $a = WorkoutPlan::factory()->for($user)->create(['name' => 'A', 'position' => 1]);
    $b = WorkoutPlan::factory()->for($user)->create(['name' => 'B', 'position' => 1]);

    app(MoveWorkoutPlan::class)->handle($user, $a->id, 1);

    expect(activeNames($user))->toBe(['B', 'A'])
        ->and($b->fresh()->position)->toBeLessThan($a->fresh()->position);
});

test('refuses to move a plan of another user', function () {
    expect(fn () => app(MoveWorkoutPlan::class)->handle(User::factory()->create(), WorkoutPlan::factory()->create()->id, 0))
        ->toThrow(ModelNotFoundException::class);
});

test('archives and restores a plan at the end of the rotation', function () {
    $user = User::factory()->create();
    $a = WorkoutPlan::factory()->for($user)->create(['name' => 'A', 'position' => 1]);
    WorkoutPlan::factory()->for($user)->create(['name' => 'B', 'position' => 2]);

    app(ArchiveWorkoutPlan::class)->handle($user, $a->id);
    expect(activeNames($user))->toBe(['B']);

    app(RestoreWorkoutPlan::class)->handle($user, $a->id);
    expect(activeNames($user))->toBe(['B', 'A']);
});

test('refuses to archive a plan of another user', function () {
    $plan = WorkoutPlan::factory()->create();

    expect(fn () => app(ArchiveWorkoutPlan::class)->handle(User::factory()->create(), $plan->id))->toThrow(ModelNotFoundException::class);
    expect(fn () => app(RestoreWorkoutPlan::class)->handle(User::factory()->create(), $plan->id))->toThrow(ModelNotFoundException::class);
    expect($plan->fresh()->isArchived())->toBeFalse();
});

test('the overview letters active plans by rotation order and marks the next one', function () {
    $this->travelTo(now()->parse('2026-10-22 10:00'));
    $user = User::factory()->create();
    $a = WorkoutPlan::factory()->for($user)->create(['name' => 'Peito e tríceps', 'position' => 1]);
    $b = WorkoutPlan::factory()->for($user)->create(['name' => 'Costas e bíceps', 'position' => 2]);
    WorkoutPlan::factory()->for($user)->archived()->create(['name' => 'Antiga', 'position' => 3]);
    WorkoutPlanExercise::factory()->count(2)->create(['workout_plan_id' => $a->id]);
    WorkoutSession::factory()->for($user)->create(['workout_plan_id' => $a->id, 'started_at' => '2026-10-20 07:00', 'finished_at' => '2026-10-20 08:00']);

    $overview = app(GetWorkoutPlans::class)->handle($user);

    expect(array_map(fn ($item) => $item->letter, $overview->active))->toBe(['A', 'B'])
        ->and($overview->active[0]->exerciseCount)->toBe(2)
        ->and($overview->active[0]->lastDoneAt?->toDateString())->toBe('2026-10-20')
        ->and($overview->next()?->plan->id)->toBe($b->id)
        ->and($overview->archived[0]->letter)->toBe('A')
        ->and($overview->letterFor($b))->toBe('B');
});
