<?php

namespace App\Actions\Training;

use App\Data\Training\ExerciseProgress;
use App\Data\Training\PersonalRecord;
use App\Models\Training\Exercise;
use App\Models\Training\WorkoutSet;
use App\Models\User;
use Carbon\CarbonImmutable;

/**
 * Load evolution of one exercise: the top weight of each of the last workouts with it.
 */
class GetExerciseProgress
{
    public function handle(User $user, int $exerciseId, int $workouts = 8): ExerciseProgress
    {
        $exercise = Exercise::query()->availableTo($user)->findOrFail($exerciseId);

        $sets = WorkoutSet::query()
            ->with('session')
            ->where('exercise_id', $exercise->id)
            ->where('completed', true)
            ->whereNotNull('weight_kg')
            ->whereHas('session', fn ($query) => $query->where('user_id', $user->id)->whereNotNull('finished_at'))
            ->get();

        $perSession = $sets
            ->groupBy('workout_session_id')
            ->map(fn ($sessionSets) => [
                'date' => CarbonImmutable::parse($sessionSets->first()->session->started_at),
                'top' => (float) $sessionSets->max(fn (WorkoutSet $set) => (float) $set->weight_kg),
            ])
            ->sortBy(fn (array $row) => $row['date']->getTimestamp())
            ->values();

        $window = $perSession->slice(-$workouts)->values();

        $best = $sets->sort(fn (WorkoutSet $a, WorkoutSet $b) => [(float) $b->weight_kg, $b->reps] <=> [(float) $a->weight_kg, $a->reps])->first();

        return new ExerciseProgress(
            exercise: $exercise,
            points: array_values(array_map(floatval(...), $window->pluck('top')->all())),
            firstDate: $window->first()['date'] ?? null,
            lastDate: $window->last()['date'] ?? null,
            workouts: $perSession->count(),
            currentKg: $window->last()['top'] ?? null,
            record: $best ? new PersonalRecord($exercise, (float) $best->weight_kg, (int) $best->reps) : null,
            deltaKg: $window->count() > 1 ? $window->last()['top'] - $window->first()['top'] : null,
        );
    }
}
