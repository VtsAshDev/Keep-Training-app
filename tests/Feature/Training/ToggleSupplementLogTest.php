<?php

use App\Actions\Training\ToggleSupplementLog;
use App\Exceptions\Training\TrainingRuleException;
use App\Models\Training\Supplement;
use App\Models\Training\SupplementLog;
use App\Models\Training\WorkoutSession;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

beforeEach(fn () => $this->travelTo(now()->parse('2026-10-22 10:00')));

test('marks and unmarks the same log of today', function () {
    $user = User::factory()->create();
    $supplement = Supplement::factory()->for($user)->create();
    $toggle = app(ToggleSupplementLog::class);

    expect($toggle->handle($user, $supplement->id)->taken)->toBeTrue()
        ->and($toggle->handle($user, $supplement->id)->taken)->toBeFalse()
        ->and($toggle->handle($user, $supplement->id)->taken)->toBeTrue()
        ->and(SupplementLog::count())->toBe(1);
});

test('each day has its own log', function () {
    $user = User::factory()->create();
    $supplement = Supplement::factory()->for($user)->create();

    app(ToggleSupplementLog::class)->handle($user, $supplement->id);
    $this->travelTo(now()->addDay());
    $log = app(ToggleSupplementLog::class)->handle($user, $supplement->id);

    expect(SupplementLog::count())->toBe(2)
        ->and($log->date->toDateString())->toBe('2026-10-23');
});

test('training day supplements are refused before a workout starts today', function () {
    $user = User::factory()->create();
    $supplement = Supplement::factory()->for($user)->trainingDays()->create();

    expect(fn () => app(ToggleSupplementLog::class)->handle($user, $supplement->id))
        ->toThrow(TrainingRuleException::class);

    WorkoutSession::factory()->for($user)->inProgress()->create(['started_at' => now()]);

    expect(app(ToggleSupplementLog::class)->handle($user, $supplement->id)->taken)->toBeTrue();
});

test('refuses a supplement of another user', function () {
    $supplement = Supplement::factory()->create();

    expect(fn () => app(ToggleSupplementLog::class)->handle(User::factory()->create(), $supplement->id))
        ->toThrow(ModelNotFoundException::class);
    expect(SupplementLog::count())->toBe(0);
});

test('the log is unique per user, supplement and date', function () {
    $user = User::factory()->create();
    $supplement = Supplement::factory()->for($user)->create();
    SupplementLog::factory()->for($user)->create(['supplement_id' => $supplement->id, 'date' => now()->startOfDay()]);

    expect(fn () => SupplementLog::factory()->for($user)->create(['supplement_id' => $supplement->id, 'date' => now()->startOfDay()]))
        ->toThrow(QueryException::class);
});
