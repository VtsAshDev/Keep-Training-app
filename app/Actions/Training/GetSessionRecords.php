<?php

namespace App\Actions\Training;

use App\Data\Training\PersonalRecord;
use App\Models\Training\WorkoutSession;
use App\Models\Training\WorkoutSet;
use App\Models\User;

/**
 * A record is the best completed set of an exercise in the workout (heaviest, then most reps)
 * beating every earlier finished workout. The first time an exercise is done is not a record.
 */
class GetSessionRecords
{
    /**
     * @return list<PersonalRecord>
     */
    public function handle(User $user, WorkoutSession $session): array
    {
        abort_unless($session->user_id === $user->id, 404);

        $records = [];

        $session->loadMissing('sets.exercise');
        foreach ($session->sets->where('completed', true)->groupBy('exercise_id') as $exerciseId => $sets) {
            $best = self::best($sets->all());

            $previous = WorkoutSet::query()
                ->where('exercise_id', $exerciseId)
                ->where('completed', true)
                ->whereHas('session', fn ($query) => $query
                    ->where('user_id', $user->id)
                    ->whereNotNull('finished_at')
                    ->where('started_at', '<', $session->started_at))
                ->get();

            if ($best === null || $previous->isEmpty()) {
                continue;
            }

            $previousBest = self::best($previous->all());
            $beats = $previousBest === null
                || (float) $best->weight_kg > (float) $previousBest->weight_kg
                || ((float) $best->weight_kg === (float) $previousBest->weight_kg && $best->reps > $previousBest->reps);

            if ($beats) {
                $records[] = new PersonalRecord($best->exercise, (float) $best->weight_kg, (int) $best->reps);
            }
        }

        return $records;
    }

    /**
     * @param  array<int, WorkoutSet>  $sets
     */
    private static function best(array $sets): ?WorkoutSet
    {
        $best = null;
        foreach ($sets as $set) {
            if ($set->weight_kg === null || $set->reps === null) {
                continue;
            }
            if ($best === null
                || (float) $set->weight_kg > (float) $best->weight_kg
                || ((float) $set->weight_kg === (float) $best->weight_kg && $set->reps > $best->reps)) {
                $best = $set;
            }
        }

        return $best;
    }
}
