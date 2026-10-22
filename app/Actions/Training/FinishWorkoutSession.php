<?php

namespace App\Actions\Training;

use App\Exceptions\Training\TrainingRuleException;
use App\Models\Training\WorkoutSession;
use App\Models\User;

class FinishWorkoutSession
{
    public function handle(User $user, int $sessionId): WorkoutSession
    {
        $session = $user->workoutSessions()->findOrFail($sessionId);

        if (! $session->isInProgress()) {
            throw TrainingRuleException::sessionNotInProgress();
        }

        $session->update(['finished_at' => now()]);

        return $session;
    }
}
