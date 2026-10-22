<?php

namespace App\Actions\Training;

use App\Models\Training\Exercise;
use App\Models\Training\WorkoutPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

/**
 * Saves the plan name and replaces its exercises, in the given order.
 * Past workouts keep their sets because they point to exercises, not plan items.
 */
class SaveWorkoutPlan
{
    /**
     * @param  list<array{exercise_id: int, sets: int, reps_min: int, reps_max: int, rest_seconds: int}>  $items
     */
    public function handle(User $user, int $planId, string $name, array $items): WorkoutPlan
    {
        $plan = $user->workoutPlans()->findOrFail($planId);

        $exerciseIds = array_values(array_unique(array_column($items, 'exercise_id')));
        $available = Exercise::query()->availableTo($user)->whereKey($exerciseIds)->count();

        if ($available !== count($exerciseIds)) {
            throw (new ModelNotFoundException)->setModel(Exercise::class, $exerciseIds);
        }

        return DB::transaction(function () use ($plan, $name, $items) {
            $plan->update(['name' => trim($name)]);
            $plan->exercises()->delete();

            foreach ($items as $index => $item) {
                $plan->exercises()->create([
                    'exercise_id' => $item['exercise_id'],
                    'position' => $index + 1,
                    'sets' => $item['sets'],
                    'reps_min' => min($item['reps_min'], $item['reps_max']),
                    'reps_max' => max($item['reps_min'], $item['reps_max']),
                    'rest_seconds' => $item['rest_seconds'],
                ]);
            }

            return $plan->load('exercises');
        });
    }
}
