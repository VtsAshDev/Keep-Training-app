<?php

namespace App\Actions\Training;

use App\Exceptions\Training\TrainingRuleException;
use App\Models\Training\WorkoutPlanExercise;
use App\Models\Training\WorkoutSet;
use App\Models\User;

/**
 * Deleting is only allowed while the exercise is in no plan and no workout history.
 */
class DeleteExercise
{
    public function handle(User $user, int $exerciseId): void
    {
        $exercise = $user->exercises()->findOrFail($exerciseId);

        $inUse = WorkoutPlanExercise::query()->where('exercise_id', $exercise->id)->exists()
            || WorkoutSet::query()->where('exercise_id', $exercise->id)->exists();

        if ($inUse) {
            throw TrainingRuleException::exerciseInUse();
        }

        $exercise->delete();
    }
}
