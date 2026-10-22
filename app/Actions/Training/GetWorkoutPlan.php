<?php

namespace App\Actions\Training;

use App\Models\Training\WorkoutPlan;
use App\Models\User;

/**
 * One of the user's plans with its exercises, for the plan editor.
 */
class GetWorkoutPlan
{
    public function handle(User $user, int $planId): WorkoutPlan
    {
        return $user->workoutPlans()->with('exercises.exercise')->findOrFail($planId);
    }
}
