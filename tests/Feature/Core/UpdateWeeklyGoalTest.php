<?php

use App\Actions\Core\UpdateWeeklyGoal;
use App\Models\User;

test('updates the weekly goal', function () {
    $user = User::factory()->create();

    app(UpdateWeeklyGoal::class)->handle($user, 5);

    expect($user->fresh()->weekly_workout_goal)->toBe(5);
});

test('new users start with a goal of four workouts', function () {
    expect(User::factory()->create()->fresh()->weekly_workout_goal)->toBe(4);
});

test('refuses goals outside one to seven', function (int $goal) {
    expect(fn () => app(UpdateWeeklyGoal::class)->handle(User::factory()->create(), $goal))
        ->toThrow(InvalidArgumentException::class);
})->with([0, 8]);
