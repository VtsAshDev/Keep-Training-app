<?php

namespace App\Actions\Training;

use App\Models\Training\WorkoutPlan;
use App\Models\User;

/**
 * Plans are archived, never deleted, so the history keeps pointing to them.
 */
class ArchiveWorkoutPlan
{
    public function handle(User $user, int $planId): WorkoutPlan
    {
        $plan = $user->workoutPlans()->findOrFail($planId);

        $plan->update(['archived_at' => now()]);

        return $plan;
    }
}
