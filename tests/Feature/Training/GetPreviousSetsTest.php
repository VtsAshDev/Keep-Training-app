<?php

use App\Actions\Training\GetPreviousSets;
use App\Models\Training\Exercise;
use App\Models\Training\WorkoutSession;
use App\Models\Training\WorkoutSet;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

test('returns the completed sets of the last finished workout keyed by set number', function () {
    $user = User::factory()->create();
    $exercise = Exercise::factory()->for($user)->create();
    $old = WorkoutSession::factory()->for($user)->create(['finished_at' => '2026-10-15 19:00']);
    $last = WorkoutSession::factory()->for($user)->create(['finished_at' => '2026-10-20 19:00']);
    $inProgress = WorkoutSession::factory()->for($user)->inProgress()->create();
    WorkoutSet::factory()->create(['workout_session_id' => $old->id, 'exercise_id' => $exercise->id, 'weight_kg' => 40]);
    WorkoutSet::factory()->create(['workout_session_id' => $last->id, 'exercise_id' => $exercise->id, 'set_number' => 2, 'weight_kg' => 52]);
    WorkoutSet::factory()->create(['workout_session_id' => $last->id, 'exercise_id' => $exercise->id, 'set_number' => 1, 'weight_kg' => 50]);
    WorkoutSet::factory()->create(['workout_session_id' => $inProgress->id, 'exercise_id' => $exercise->id, 'weight_kg' => 99, 'completed' => true]);

    $sets = app(GetPreviousSets::class)->handle($user, $exercise->id);

    expect($sets->keys()->all())->toBe([1, 2])
        ->and($sets->map(fn ($set) => (float) $set->weight_kg)->all())->toBe([1 => 50.0, 2 => 52.0]);
});

test('accepts exercises from the default catalog', function () {
    $user = User::factory()->create();

    expect(app(GetPreviousSets::class)->handle($user, Exercise::factory()->catalog()->create()->id))->toBeEmpty();
});

test('refuses an exercise of another user', function () {
    expect(fn () => app(GetPreviousSets::class)->handle(User::factory()->create(), Exercise::factory()->create()->id))
        ->toThrow(ModelNotFoundException::class);
});
