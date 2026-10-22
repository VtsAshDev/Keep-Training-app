<?php

namespace App\Actions\Training;

use App\Enums\Training\Equipment;
use App\Enums\Training\MuscleGroup;
use App\Models\Training\Exercise;
use App\Models\User;

/**
 * Only the user's own exercises can change; the default catalog is read-only.
 */
class UpdateExercise
{
    public function handle(User $user, int $exerciseId, string $name, MuscleGroup $muscleGroup, ?Equipment $equipment = null): Exercise
    {
        $exercise = $user->exercises()->findOrFail($exerciseId);

        $exercise->update([
            'name' => trim($name),
            'muscle_group' => $muscleGroup,
            'equipment' => $equipment,
        ]);

        return $exercise;
    }
}
