<?php

namespace App\Actions\Training;

use App\Models\Training\WorkoutPlan;
use App\Models\User;

/**
 * New plans go to the end of the rotation.
 */
class CreateWorkoutPlan
{
    public function handle(User $user, string $name): WorkoutPlan
    {
        return $user->workoutPlans()->create([
            'name' => trim($name),
            'position' => (int) $user->workoutPlans()->max('position') + 1,
        ]);
    }
}
