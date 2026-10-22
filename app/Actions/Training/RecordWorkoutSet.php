<?php

namespace App\Actions\Training;

use App\Exceptions\Training\TrainingRuleException;
use App\Models\Training\WorkoutSet;
use App\Models\User;

/**
 * Saves the weight and reps of a set in the workout in progress.
 */
class RecordWorkoutSet
{
    public function handle(User $user, int $setId, ?float $weightKg, ?int $reps, bool $completed = true): WorkoutSet
    {
        $set = WorkoutSet::query()
            ->with('session')
            ->whereHas('session', fn ($query) => $query->where('user_id', $user->id))
            ->findOrFail($setId);

        if (! $set->session->isInProgress()) {
            throw TrainingRuleException::sessionNotInProgress();
        }

        $set->update([
            'weight_kg' => $weightKg,
            'reps' => $reps,
            'completed' => $completed,
        ]);

        return $set;
    }
}
