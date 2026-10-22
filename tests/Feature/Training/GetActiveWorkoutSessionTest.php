<?php

use App\Actions\Training\GetActiveWorkoutSession;
use App\Models\Training\WorkoutSession;
use App\Models\User;

test('returns the workout in progress', function () {
    $user = User::factory()->create();
    WorkoutSession::factory()->for($user)->create();
    $active = WorkoutSession::factory()->for($user)->inProgress()->create();

    expect(app(GetActiveWorkoutSession::class)->handle($user)?->id)->toBe($active->id);
});

test('returns null when every workout is finished', function () {
    $user = User::factory()->create();
    WorkoutSession::factory()->for($user)->create();

    expect(app(GetActiveWorkoutSession::class)->handle($user))->toBeNull();
});

test('ignores workouts in progress of other users', function () {
    $user = User::factory()->create();
    WorkoutSession::factory()->inProgress()->create();

    expect(app(GetActiveWorkoutSession::class)->handle($user))->toBeNull();
});
