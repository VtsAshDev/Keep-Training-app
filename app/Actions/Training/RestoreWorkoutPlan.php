<?php

namespace App\Actions\Training;

use App\Models\Training\WorkoutPlan;
use App\Models\User;

/**
 * Brings an archived plan back at the end of the rotation.
 */
class RestoreWorkoutPlan
{
    public function handle(User $user, int $planId): WorkoutPlan
    {
        $plan = $user->workoutPlans()->findOrFail($planId);

        $plan->update([
            'archived_at' => null,
            'position' => (int) $user->workoutPlans()->max('position') + 1,
        ]);

        return $plan;
    }
}
