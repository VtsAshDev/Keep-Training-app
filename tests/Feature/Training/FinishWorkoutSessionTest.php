<?php

use App\Actions\Training\FinishWorkoutSession;
use App\Exceptions\Training\TrainingRuleException;
use App\Models\Training\WorkoutSession;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

test('finishes the workout now', function () {
    $this->travelTo(now()->parse('2026-10-22 19:34'));
    $session = WorkoutSession::factory()->inProgress()->create(['started_at' => '2026-10-22 18:40']);

    $finished = app(FinishWorkoutSession::class)->handle($session->user, $session->id);

    expect($finished->finished_at?->toDateTimeString())->toBe('2026-10-22 19:34:00');
});

test('refuses a workout that is already finished', function () {
    $session = WorkoutSession::factory()->create();

    expect(fn () => app(FinishWorkoutSession::class)->handle($session->user, $session->id))
        ->toThrow(TrainingRuleException::class);
});

test('refuses a workout of another user', function () {
    $session = WorkoutSession::factory()->inProgress()->create();

    expect(fn () => app(FinishWorkoutSession::class)->handle(User::factory()->create(), $session->id))
        ->toThrow(ModelNotFoundException::class);
    expect($session->fresh()->isInProgress())->toBeTrue();
});
