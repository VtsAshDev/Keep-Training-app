<?php

use App\Actions\Training\StartWorkoutSession;
use App\Exceptions\Training\TrainingRuleException;
use App\Models\Training\Exercise;
use App\Models\Training\WorkoutPlan;
use App\Models\Training\WorkoutPlanExercise;
use App\Models\Training\WorkoutSession;
use App\Models\Training\WorkoutSet;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

beforeEach(function () {
    $this->travelTo(now()->parse('2026-10-22 18:40'));
    $this->user = User::factory()->create();
    $this->plan = WorkoutPlan::factory()->for($this->user)->create(['position' => 1]);
    $this->row = Exercise::factory()->for($this->user)->create(['name' => 'Remada curvada']);
    WorkoutPlanExercise::factory()->create(['workout_plan_id' => $this->plan->id, 'exercise_id' => $this->row->id, 'sets' => 3]);
});

/**
 * @param  list<array{0: float, 1: int, 2?: bool}>  $sets  weight, reps and completed per set
 */
function pastWorkout(User $user, Exercise $exercise, array $sets, string $finishedAt = '2026-10-20 19:30', ?WorkoutPlan $plan = null): WorkoutSession
{
    $session = WorkoutSession::factory()->for($user)->create([
        'workout_plan_id' => $plan?->id,
        'started_at' => now()->parse($finishedAt)->subHour(),
        'finished_at' => $finishedAt,
    ]);

    foreach ($sets as $index => $set) {
        WorkoutSet::factory()->create([
            'workout_session_id' => $session->id,
            'exercise_id' => $exercise->id,
            'set_number' => $index + 1,
            'weight_kg' => $set[0],
            'reps' => $set[1],
            'completed' => $set[2] ?? true,
        ]);
    }

    return $session;
}

test('creates the workout with one pending set per planned set', function () {
    $session = app(StartWorkoutSession::class)->handle($this->user, $this->plan->id);

    expect($session->user_id)->toBe($this->user->id)
        ->and($session->workout_plan_id)->toBe($this->plan->id)
        ->and($session->started_at->toDateTimeString())->toBe('2026-10-22 18:40:00')
        ->and($session->isInProgress())->toBeTrue()
        ->and($session->sets)->toHaveCount(3)
        ->and($session->sets->pluck('set_number')->all())->toBe([1, 2, 3])
        ->and($session->sets->every(fn ($set) => $set->completed === false))->toBeTrue();
});

test('sets start empty without a previous workout with the exercise', function () {
    $session = app(StartWorkoutSession::class)->handle($this->user, $this->plan->id);

    expect($session->sets->every(fn ($set) => $set->weight_kg === null && $set->reps === null))->toBeTrue();
});

test('prefills each set with the same set of the last workout with the exercise', function () {
    pastWorkout($this->user, $this->row, [[55, 12], [55, 12], [55, 11]], '2026-10-15 19:30');
    pastWorkout($this->user, $this->row, [[60, 12], [60, 11], [60, 10]], '2026-10-20 19:30');

    $sets = app(StartWorkoutSession::class)->handle($this->user, $this->plan->id)->sets;

    expect($sets->map(fn ($set) => [(float) $set->weight_kg, $set->reps])->all())
        ->toBe([[60.0, 12], [60.0, 11], [60.0, 10]]);
});

test('extra planned sets repeat the last set done', function () {
    pastWorkout($this->user, $this->row, [[60, 12], [62.5, 10]]);

    $sets = app(StartWorkoutSession::class)->handle($this->user, $this->plan->id)->sets;

    expect($sets->map(fn ($set) => [(float) $set->weight_kg, $set->reps])->all())
        ->toBe([[60.0, 12], [62.5, 10], [62.5, 10]]);
});

test('prefills from the exercise history even when it was done in another plan', function () {
    $otherPlan = WorkoutPlan::factory()->for($this->user)->create(['position' => 2]);
    pastWorkout($this->user, $this->row, [[57.5, 10], [57.5, 10], [57.5, 9]], plan: $otherPlan);

    $sets = app(StartWorkoutSession::class)->handle($this->user, $this->plan->id)->sets;

    expect((float) $sets->first()->weight_kg)->toBe(57.5);
});

test('ignores sets that were not completed in the last workout', function () {
    pastWorkout($this->user, $this->row, [[60, 12], [70, 3, false], [70, 2, false]]);

    $sets = app(StartWorkoutSession::class)->handle($this->user, $this->plan->id)->sets;

    expect($sets->map(fn ($set) => (float) $set->weight_kg)->all())->toBe([60.0, 60.0, 60.0]);
});

test('ignores the history of other users with the same catalog exercise', function () {
    $catalog = Exercise::factory()->catalog()->create();
    WorkoutPlanExercise::factory()->create(['workout_plan_id' => $this->plan->id, 'exercise_id' => $catalog->id, 'position' => 2, 'sets' => 1]);
    pastWorkout(User::factory()->create(), $catalog, [[100, 5]]);

    $set = app(StartWorkoutSession::class)->handle($this->user, $this->plan->id)
        ->sets->firstWhere('exercise_id', $catalog->id);

    expect($set->weight_kg)->toBeNull();
});

test('refuses to start while another workout is in progress', function () {
    WorkoutSession::factory()->for($this->user)->inProgress()->create();

    expect(fn () => app(StartWorkoutSession::class)->handle($this->user, $this->plan->id))
        ->toThrow(TrainingRuleException::class);
    expect($this->user->workoutSessions()->count())->toBe(1);
});

test('refuses an archived plan', function () {
    $this->plan->update(['archived_at' => now()]);

    expect(fn () => app(StartWorkoutSession::class)->handle($this->user, $this->plan->id))
        ->toThrow(TrainingRuleException::class);
});

test('refuses a plan of another user', function () {
    $intruder = User::factory()->create();

    expect(fn () => app(StartWorkoutSession::class)->handle($intruder, $this->plan->id))
        ->toThrow(ModelNotFoundException::class);
    expect(WorkoutSession::count())->toBe(0);
});
