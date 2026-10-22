<?php

use App\Actions\Training\GetExerciseProgress;
use App\Actions\Training\GetRecentWorkouts;
use App\Actions\Training\GetSessionRecords;
use App\Actions\Training\GetTodayTraining;
use App\Actions\Training\GetTrainedExercises;
use App\Actions\Training\GetWorkoutSummary;
use App\Actions\Training\GetWorkoutView;
use App\Actions\Training\StartWorkoutSession;
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
    $this->row = Exercise::factory()->for($this->user)->create(['name' => 'Remada curvada']);
    $this->curl = Exercise::factory()->for($this->user)->create(['name' => 'Rosca direta']);
    $this->plan = WorkoutPlan::factory()->for($this->user)->create(['name' => 'Costas e bíceps', 'position' => 1]);
    WorkoutPlanExercise::factory()->create(['workout_plan_id' => $this->plan->id, 'exercise_id' => $this->curl->id, 'position' => 2, 'sets' => 2]);
    WorkoutPlanExercise::factory()->create(['workout_plan_id' => $this->plan->id, 'exercise_id' => $this->row->id, 'position' => 1, 'sets' => 3, 'reps_max' => 12]);
});

/**
 * @param  array<int, list<array{0: float, 1: int}>>  $sets  exercise id => [weight, reps] per set
 */
function workoutWith(User $user, ?WorkoutPlan $plan, string $startedAt, array $sets, int $minutes = 54): WorkoutSession
{
    $session = WorkoutSession::factory()->for($user)->create([
        'workout_plan_id' => $plan?->id,
        'started_at' => $startedAt,
        'finished_at' => now()->parse($startedAt)->addMinutes($minutes),
    ]);
    foreach ($sets as $exerciseId => $rows) {
        foreach ($rows as $i => [$weight, $reps]) {
            WorkoutSet::factory()->create(['workout_session_id' => $session->id, 'exercise_id' => $exerciseId, 'set_number' => $i + 1, 'weight_kg' => $weight, 'reps' => $reps]);
        }
    }

    return $session;
}

test('today shows the suggested plan and the last workout', function () {
    workoutWith($this->user, $this->plan, '2026-10-20 07:00', [$this->row->id => [[60, 12]]]);

    $today = app(GetTodayTraining::class)->handle($this->user);

    expect($today->hasPlans)->toBeTrue()
        ->and($today->suggested?->plan->id)->toBe($this->plan->id)
        ->and($today->activeSession)->toBeNull()
        ->and($today->lastWorkout?->plan?->id)->toBe($this->plan->id)
        ->and($today->lastWorkoutLetter)->toBe('A');
});

test('today offers to resume the workout in progress with its progress', function () {
    $session = app(StartWorkoutSession::class)->handle($this->user, $this->plan->id);
    $session->sets->first()->update(['completed' => true]);

    $today = app(GetTodayTraining::class)->handle($this->user);

    expect($today->activeSession?->id)->toBe($session->id)
        ->and($today->activeLetter)->toBe('A')
        ->and([$today->activeDoneSets, $today->activeTotalSets])->toBe([1, 5]);
});

test('the workout view lists exercises in plan order with previous sets and the suggestion', function () {
    workoutWith($this->user, $this->plan, '2026-10-20 07:00', [$this->row->id => [[60, 12], [60, 12], [60, 12]]]);
    $session = app(StartWorkoutSession::class)->handle($this->user, $this->plan->id);

    $view = app(GetWorkoutView::class)->handle($this->user, $session->id);

    expect(array_map(fn ($exercise) => $exercise->exercise->name, $view->exercises))->toBe(['Remada curvada', 'Rosca direta'])
        ->and($view->letter)->toBe('A')
        ->and($view->totalSets())->toBe(5)
        ->and($view->current()?->exercise->id)->toBe($this->row->id)
        ->and($view->exercises[0]->previous)->toHaveCount(3)
        ->and($view->exercises[0]->suggestion?->suggestedWeightKg)->toBe(62.5);
});

test('the workout view refuses a workout of another user', function () {
    expect(fn () => app(GetWorkoutView::class)->handle(User::factory()->create(), WorkoutSession::factory()->create()->id))
        ->toThrow(ModelNotFoundException::class);
});

test('a record beats the best earlier set, by weight and then reps', function () {
    workoutWith($this->user, $this->plan, '2026-10-15 07:00', [$this->row->id => [[60, 12]], $this->curl->id => [[16, 12]]]);
    $session = workoutWith($this->user, $this->plan, '2026-10-20 07:00', [
        $this->row->id => [[62.5, 8], [60, 12]],
        $this->curl->id => [[16, 12]],
    ]);

    $records = app(GetSessionRecords::class)->handle($this->user, $session);

    expect($records)->toHaveCount(1)
        ->and($records[0]->exercise->id)->toBe($this->row->id)
        ->and([$records[0]->weightKg, $records[0]->reps])->toBe([62.5, 8]);
});

test('the first time an exercise is done is not a record', function () {
    $session = workoutWith($this->user, $this->plan, '2026-10-20 07:00', [$this->row->id => [[60, 12]]]);

    expect(app(GetSessionRecords::class)->handle($this->user, $session))->toBe([]);
});

test('the summary has duration, volume, sets, records and the weekly goal', function () {
    workoutWith($this->user, $this->plan, '2026-10-15 07:00', [$this->row->id => [[60, 12]]]);
    $session = workoutWith($this->user, $this->plan, '2026-10-22 07:00', [
        $this->row->id => [[62.5, 10], [62.5, 10]],
        $this->curl->id => [[16, 12]],
    ], minutes: 54);

    $summary = app(GetWorkoutSummary::class)->handle($this->user, $session->id);

    expect($summary->durationMinutes)->toBe(54)
        ->and($summary->volumeKg)->toBe(62.5 * 10 * 2 + 16 * 12)
        ->and($summary->completedSets)->toBe(3)
        ->and($summary->records)->toHaveCount(1)
        ->and($summary->exercises)->toBe([
            ['name' => 'Remada curvada', 'sets' => 2, 'topWeightKg' => 62.5],
            ['name' => 'Rosca direta', 'sets' => 1, 'topWeightKg' => 16.0],
        ])
        ->and($summary->weeklyGoal->completed)->toBe(1);
});

test('the summary refuses a workout of another user', function () {
    expect(fn () => app(GetWorkoutSummary::class)->handle(User::factory()->create(), WorkoutSession::factory()->create()->id))
        ->toThrow(ModelNotFoundException::class);
});

test('recent workouts come newest first with volume and records', function () {
    workoutWith($this->user, $this->plan, '2026-10-15 07:00', [$this->row->id => [[60, 12]]], minutes: 61);
    workoutWith($this->user, $this->plan, '2026-10-20 07:00', [$this->row->id => [[62.5, 10]]], minutes: 47);
    workoutWith(User::factory()->create(), null, '2026-10-21 07:00', []);

    $recent = app(GetRecentWorkouts::class)->handle($this->user);

    expect($recent)->toHaveCount(2)
        ->and($recent[0]->session->started_at->toDateString())->toBe('2026-10-20')
        ->and([$recent[0]->durationMinutes, $recent[0]->volumeKg, $recent[0]->records])->toBe([47, 625.0, 1])
        ->and($recent[1]->records)->toBe(0);
});

test('exercise progress shows the top weight of the last workouts', function () {
    foreach ([50, 52.5, 55, 55, 57.5, 60, 60, 62.5, 65] as $i => $weight) {
        workoutWith($this->user, $this->plan, now()->parse('2026-09-01 07:00')->addDays($i * 3)->toDateTimeString(), [$this->row->id => [[$weight, 10], [$weight - 5, 12]]]);
    }

    $progress = app(GetExerciseProgress::class)->handle($this->user, $this->row->id);

    expect($progress->points)->toBe([52.5, 55.0, 55.0, 57.5, 60.0, 60.0, 62.5, 65.0])
        ->and($progress->workouts)->toBe(9)
        ->and($progress->currentKg)->toBe(65.0)
        ->and($progress->deltaKg)->toBe(12.5)
        ->and([$progress->record?->weightKg, $progress->record?->reps])->toBe([65.0, 10])
        ->and($progress->firstDate?->toDateString())->toBe('2026-09-04');
});

test('history lists only exercises the user trained', function () {
    workoutWith($this->user, $this->plan, '2026-10-20 07:00', [$this->row->id => [[60, 12]]]);
    workoutWith(User::factory()->create(), null, '2026-10-20 07:00', [Exercise::factory()->catalog()->create()->id => [[60, 12]]]);

    expect(app(GetTrainedExercises::class)->handle($this->user)->pluck('name')->all())->toBe(['Remada curvada']);
});

test('exercise progress refuses an exercise of another user', function () {
    expect(fn () => app(GetExerciseProgress::class)->handle($this->user, Exercise::factory()->create()->id))
        ->toThrow(ModelNotFoundException::class);
});
