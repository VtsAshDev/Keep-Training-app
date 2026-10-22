<?php

namespace App\Actions\Training;

use App\Data\Training\ProgressionSuggestion;
use App\Models\Training\WorkoutPlanExercise;
use App\Models\User;

/**
 * Double progression: when every planned set of the last workout reached reps_max,
 * suggest more weight. It is only a suggestion; prefilled weights stay as they were.
 */
class GetProgressionSuggestion
{
    public const float INCREMENT_KG = 2.5;

    public function __construct(
        private GetPreviousSets $previousSets,
    ) {}

    public function handle(User $user, int $planExerciseId): ?ProgressionSuggestion
    {
        $item = WorkoutPlanExercise::query()
            ->whereHas('plan', fn ($query) => $query->where('user_id', $user->id))
            ->findOrFail($planExerciseId);

        $previous = $this->previousSets->handle($user, $item->exercise_id);

        if ($previous->count() < $item->sets) {
            return null;
        }

        if ($previous->contains(fn ($set) => $set->reps === null || $set->reps < $item->reps_max)) {
            return null;
        }

        $lastWeight = (float) $previous->max(fn ($set) => (float) $set->weight_kg);

        if ($lastWeight <= 0) {
            return null;
        }

        return new ProgressionSuggestion($lastWeight, $lastWeight + self::INCREMENT_KG);
    }
}
