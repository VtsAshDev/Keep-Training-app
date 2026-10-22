<?php

use App\Actions\Training\RecordWorkoutSet;
use App\Exceptions\Training\TrainingRuleException;
use App\Models\Training\WorkoutSession;
use App\Models\Training\WorkoutSet;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

test('saves weight and reps and completes the set', function () {
    $session = WorkoutSession::factory()->inProgress()->create();
    $set = WorkoutSet::factory()->create(['workout_session_id' => $session->id, 'completed' => false]);

    $saved = app(RecordWorkoutSet::class)->handle($session->user, $set->id, 62.5, 10);

    expect((float) $saved->fresh()->weight_kg)->toBe(62.5)
        ->and($saved->fresh()->reps)->toBe(10)
        ->and($saved->fresh()->completed)->toBeTrue();
});

test('can undo a completed set', function () {
    $session = WorkoutSession::factory()->inProgress()->create();
    $set = WorkoutSet::factory()->create(['workout_session_id' => $session->id, 'completed' => true]);

    app(RecordWorkoutSet::class)->handle($session->user, $set->id, 60, 12, completed: false);

    expect($set->fresh()->completed)->toBeFalse();
});

test('refuses a set of a finished workout', function () {
    $session = WorkoutSession::factory()->create();
    $set = WorkoutSet::factory()->create(['workout_session_id' => $session->id]);

    expect(fn () => app(RecordWorkoutSet::class)->handle($session->user, $set->id, 70, 8))
        ->toThrow(TrainingRuleException::class);
});

test('refuses a set of another user', function () {
    $session = WorkoutSession::factory()->inProgress()->create();
    $set = WorkoutSet::factory()->create(['workout_session_id' => $session->id, 'weight_kg' => 50]);

    expect(fn () => app(RecordWorkoutSet::class)->handle(User::factory()->create(), $set->id, 999, 1))
        ->toThrow(ModelNotFoundException::class);
    expect((float) $set->fresh()->weight_kg)->toBe(50.0);
});
