<?php

use App\Actions\Training\GetTodaySupplements;
use App\Models\Training\Supplement;
use App\Models\Training\SupplementLog;
use App\Models\Training\WorkoutSession;
use App\Models\User;

beforeEach(fn () => $this->travelTo(now()->parse('2026-10-22 10:00')));

test('daily supplements are always available', function () {
    $user = User::factory()->create();
    Supplement::factory()->for($user)->create();

    $today = app(GetTodaySupplements::class)->handle($user);

    expect($today)->toHaveCount(1)
        ->and($today->first()->available)->toBeTrue()
        ->and($today->first()->taken)->toBeFalse();
});

test('training day supplements are only available after a workout starts today', function () {
    $user = User::factory()->create();
    Supplement::factory()->for($user)->trainingDays()->create();

    expect(app(GetTodaySupplements::class)->handle($user)->first()->available)->toBeFalse();

    WorkoutSession::factory()->for($user)->inProgress()->create(['started_at' => now()]);

    expect(app(GetTodaySupplements::class)->handle($user)->first()->available)->toBeTrue();
});

test('taken reflects only the log of today', function () {
    $user = User::factory()->create();
    $supplement = Supplement::factory()->for($user)->create();
    SupplementLog::factory()->for($user)->create(['supplement_id' => $supplement->id, 'date' => now()->subDay()->startOfDay()]);

    expect(app(GetTodaySupplements::class)->handle($user)->first()->taken)->toBeFalse();

    SupplementLog::factory()->for($user)->create(['supplement_id' => $supplement->id, 'date' => now()->startOfDay()]);

    expect(app(GetTodaySupplements::class)->handle($user)->first()->taken)->toBeTrue();
});

test('lists only the supplements of the user', function () {
    Supplement::factory()->create();

    expect(app(GetTodaySupplements::class)->handle(User::factory()->create()))->toBeEmpty();
});
