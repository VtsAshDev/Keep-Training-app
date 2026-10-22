<?php

namespace App\Actions\Training;

use App\Models\Training\WorkoutSession;
use App\Models\User;

/**
 * The workout started and not finished yet, which the Today screen offers to resume.
 */
class GetActiveWorkoutSession
{
    public function handle(User $user): ?WorkoutSession
    {
        return $user->workoutSessions()->inProgress()->latest('started_at')->first();
    }
}
