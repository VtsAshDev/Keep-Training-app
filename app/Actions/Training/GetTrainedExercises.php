<?php

namespace App\Actions\Training;

use App\Models\Training\Exercise;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Exercises with at least one completed set in a finished workout, for the history selector.
 */
class GetTrainedExercises
{
    /**
     * @return Collection<int, Exercise>
     */
    public function handle(User $user): Collection
    {
        return Exercise::query()
            ->availableTo($user)
            ->whereHas('sets', fn ($query) => $query
                ->where('completed', true)
                ->whereHas('session', fn ($query) => $query->where('user_id', $user->id)->whereNotNull('finished_at')))
            ->orderBy('name')
            ->get();
    }
}
