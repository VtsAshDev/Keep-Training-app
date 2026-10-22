<?php

namespace App\Actions\Training;

use App\Exceptions\Training\TrainingRuleException;
use App\Models\Training\WorkoutSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Starts a workout from a plan, with every set prefilled from the last workout with that exercise.
 */
class StartWorkoutSession
{
    public function __construct(
        private GetActiveWorkoutSession $activeSession,
        private GetPreviousSets $previousSets,
    ) {}

    public function handle(User $user, int $planId): WorkoutSession
    {
        $plan = $user->workoutPlans()->with('exercises')->findOrFail($planId);

        if ($plan->isArchived()) {
            throw TrainingRuleException::planArchived();
        }

        if ($this->activeSession->handle($user) !== null) {
            throw TrainingRuleException::workoutInProgress();
        }

        return DB::transaction(function () use ($user, $plan) {
            $session = $user->workoutSessions()->create([
                'workout_plan_id' => $plan->id,
                'started_at' => now(),
            ]);

            foreach ($plan->exercises as $item) {
                $previous = $this->previousSets->handle($user, $item->exercise_id);

                for ($number = 1; $number <= $item->sets; $number++) {
                    // Same set of the last workout; extra sets repeat the last one done.
                    $source = $previous->get($number) ?? $previous->last();

                    $session->sets()->create([
                        'exercise_id' => $item->exercise_id,
                        'set_number' => $number,
                        'weight_kg' => $source?->weight_kg,
                        'reps' => $source?->reps,
                        'completed' => false,
                    ]);
                }
            }

            return $session->load('sets');
        });
    }
}
