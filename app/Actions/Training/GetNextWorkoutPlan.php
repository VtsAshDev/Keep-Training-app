<?php

namespace App\Actions\Training;

use App\Models\Training\WorkoutPlan;
use App\Models\User;

/**
 * Rotation: the active plan right after, by position, the plan of the last finished workout.
 * Missing a day never advances it; archived plans still anchor it by their position.
 */
class GetNextWorkoutPlan
{
    public function handle(User $user): ?WorkoutPlan
    {
        $active = $user->workoutPlans()->active()->orderBy('position')->orderBy('id')->get();

        if ($active->isEmpty()) {
            return null;
        }

        $lastPlan = $user->workoutSessions()
            ->finished()
            ->whereNotNull('workout_plan_id')
            ->latest('finished_at')
            ->latest('id')
            ->first()
            ?->plan;

        if ($lastPlan === null) {
            return $active->first();
        }

        return $active->first(fn (WorkoutPlan $plan) => $plan->position > $lastPlan->position
            || ($plan->position === $lastPlan->position && $plan->id > $lastPlan->id)
        ) ?? $active->first();
    }
}
