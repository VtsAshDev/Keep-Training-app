<?php

use App\Actions\Training\IsTrainingDay;
use App\Models\Training\WorkoutSession;
use App\Models\User;

beforeEach(fn () => $this->travelTo(now()->parse('2026-10-22 21:00')));

test('a day with a workout started is a training day, even before finishing it', function () {
    $user = User::factory()->create();
    WorkoutSession::factory()->for($user)->inProgress()->create(['started_at' => '2026-10-22 20:30']);

    expect(app(IsTrainingDay::class)->handle($user))->toBeTrue();
});

test('a workout on another day does not make today a training day', function () {
    $user = User::factory()->create();
    WorkoutSession::factory()->for($user)->create(['started_at' => '2026-10-21 23:30', 'finished_at' => '2026-10-22 00:20']);

    expect(app(IsTrainingDay::class)->handle($user))->toBeFalse();
});

test('checks a given date', function () {
    $user = User::factory()->create();
    WorkoutSession::factory()->for($user)->create(['started_at' => '2026-10-20 07:00', 'finished_at' => '2026-10-20 08:00']);

    expect(app(IsTrainingDay::class)->handle($user, now()->parse('2026-10-20')))->toBeTrue();
});

test('workouts of other users do not count', function () {
    WorkoutSession::factory()->inProgress()->create(['started_at' => '2026-10-22 20:30']);

    expect(app(IsTrainingDay::class)->handle(User::factory()->create()))->toBeFalse();
});

test('a late workout in São Paulo counts for that local day', function () {
    expect(config('app.timezone'))->toBe('America/Sao_Paulo');

    $user = User::factory()->create();
    // 22:30 in São Paulo is already the next day in UTC.
    WorkoutSession::factory()->for($user)->inProgress()->create(['started_at' => now()->setTime(22, 30)]);
    $this->travelTo(now()->parse('2026-10-22 23:00'));

    expect(app(IsTrainingDay::class)->handle($user))->toBeTrue();
});
