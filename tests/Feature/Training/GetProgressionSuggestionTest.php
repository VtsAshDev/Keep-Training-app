<?php

use App\Actions\Training\GetProgressionSuggestion;
use App\Models\Training\Exercise;
use App\Models\Training\WorkoutPlan;
use App\Models\Training\WorkoutPlanExercise;
use App\Models\Training\WorkoutSession;
use App\Models\Training\WorkoutSet;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->exercise = Exercise::factory()->for($this->user)->create();
    $this->item = WorkoutPlanExercise::factory()->create([
        'workout_plan_id' => WorkoutPlan::factory()->for($this->user),
        'exercise_id' => $this->exercise->id,
        'sets' => 3,
        'reps_min' => 8,
        'reps_max' => 12,
    ]);
});

/**
 * @param  list<array{0: float, 1: int}>  $sets
 */
function lastWorkoutSets(User $user, Exercise $exercise, array $sets): void
{
    $session = WorkoutSession::factory()->for($user)->create();
    foreach ($sets as $index => [$weight, $reps]) {
        WorkoutSet::factory()->create([
            'workout_session_id' => $session->id,
            'exercise_id' => $exercise->id,
            'set_number' => $index + 1,
            'weight_kg' => $weight,
            'reps' => $reps,
        ]);
    }
}

function suggestion(User $user, WorkoutPlanExercise $item): mixed
{
    return app(GetProgressionSuggestion::class)->handle($user, $item->id);
}

test('suggests more weight when every set reached the top of the range', function () {
    lastWorkoutSets($this->user, $this->exercise, [[60, 12], [60, 12], [60, 13]]);

    $suggestion = suggestion($this->user, $this->item);

    expect($suggestion->lastWeightKg)->toBe(60.0)
        ->and($suggestion->suggestedWeightKg)->toBe(62.5);
});

test('does not suggest when one set fell short of the top of the range', function () {
    lastWorkoutSets($this->user, $this->exercise, [[60, 12], [60, 12], [60, 10]]);

    expect(suggestion($this->user, $this->item))->toBeNull();
});

test('does not suggest when fewer sets than planned were done', function () {
    lastWorkoutSets($this->user, $this->exercise, [[60, 12], [60, 12]]);

    expect(suggestion($this->user, $this->item))->toBeNull();
});

test('does not suggest without history', function () {
    expect(suggestion($this->user, $this->item))->toBeNull();
});

test('refuses a plan item of another user', function () {
    expect(fn () => suggestion(User::factory()->create(), $this->item))
        ->toThrow(ModelNotFoundException::class);
});
