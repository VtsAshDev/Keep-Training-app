<?php

namespace App\Actions\Training;

use App\Models\Training\WorkoutPlan;
use App\Models\User;

/**
 * Moves an active plan to a new index in the rotation (as reported by wire:sort).
 * The existing position values are reused, so archived plans keep their place.
 */
class MoveWorkoutPlan
{
    public function handle(User $user, int $planId, int $index): void
    {
        $active = $user->workoutPlans()->active()->orderBy('position')->orderBy('id')->get();
        $plan = $active->firstWhere('id', $planId) ?? $user->workoutPlans()->active()->findOrFail($planId);

        $positions = $active->pluck('position')->sort()->values();
        $ordered = $active->reject(fn (WorkoutPlan $item) => $item->id === $plan->id)->values();
        $ordered->splice(max(0, min($index, $ordered->count())), 0, [$plan]);

        foreach ($ordered as $i => $item) {
            // Equal positions would make the rotation ambiguous, so keep them strictly increasing.
            $position = max((int) $positions[$i], $i > 0 ? $ordered[$i - 1]->position + 1 : 1);

            if ($item->position !== $position) {
                $item->update(['position' => $position]);
            }
        }
    }
}
