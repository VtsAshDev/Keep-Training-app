<?php

namespace App\Actions\Training;

use App\Models\User;
use Carbon\CarbonInterface;

/**
 * There is no schedule: a day is a training day when a workout was started on it.
 */
class IsTrainingDay
{
    public function handle(User $user, ?CarbonInterface $date = null): bool
    {
        $date ??= now();

        return $user->workoutSessions()->whereDate('started_at', $date->toDateString())->exists();
    }
}
