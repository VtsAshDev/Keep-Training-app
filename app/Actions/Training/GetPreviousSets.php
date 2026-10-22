<?php

namespace App\Actions\Training;

use App\Models\Training\Exercise;
use App\Models\Training\WorkoutSet;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Completed sets of the last finished workout that included the exercise, keyed by set number.
 */
class GetPreviousSets
{
    /**
     * @return Collection<int, WorkoutSet>
     */
    public function handle(User $user, int $exerciseId): Collection
    {
        $exercise = Exercise::query()->availableTo($user)->findOrFail($exerciseId);

        $lastSession = $user->workoutSessions()
            ->finished()
            ->whereHas('sets', fn ($query) => $query->where('exercise_id', $exercise->id)->where('completed', true))
            ->latest('finished_at')
            ->latest('id')
            ->first();

        if ($lastSession === null) {
            return collect();
        }

        return $lastSession->sets()
            ->where('exercise_id', $exercise->id)
            ->where('completed', true)
            ->orderBy('set_number')
            ->get()
            ->keyBy('set_number');
    }
}
