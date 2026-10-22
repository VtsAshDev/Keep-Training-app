<?php

namespace App\Actions\Training;

use App\Data\Training\WorkoutExerciseView;
use App\Data\Training\WorkoutView;
use App\Models\Training\WorkoutPlanExercise;
use App\Models\User;

/**
 * Everything the workout screen shows: exercises in plan order with their sets,
 * the previous workout values and the progression suggestion.
 */
class GetWorkoutView
{
    public function __construct(
        private GetWorkoutPlans $plans,
        private GetPreviousSets $previousSets,
        private GetProgressionSuggestion $suggestion,
    ) {}

    public function handle(User $user, int $sessionId): WorkoutView
    {
        $session = $user->workoutSessions()->with(['plan.exercises', 'sets.exercise'])->findOrFail($sessionId);

        $planItems = $session->plan?->exercises->keyBy('exercise_id') ?? collect();
        $order = $planItems->map(fn (WorkoutPlanExercise $item) => $item->position);

        $exercises = array_values($session->sets
            ->groupBy('exercise_id')
            ->sortBy(fn ($sets, $exerciseId) => [$order->get($exerciseId, PHP_INT_MAX), $sets->min('id')])
            ->map(function ($sets, $exerciseId) use ($user, $session, $planItems) {
                /** @var WorkoutPlanExercise|null $item */
                $item = $planItems->get($exerciseId);
                $exercise = $sets->first()->exercise;

                return new WorkoutExerciseView(
                    exercise: $exercise,
                    planItem: $item,
                    sets: $sets->sortBy('set_number')->values(),
                    previous: $session->isInProgress() ? $this->previousSets->handle($user, $exercise->id) : collect(),
                    suggestion: $item && $session->isInProgress() ? $this->suggestion->handle($user, $item->id) : null,
                );
            })
            ->all());

        return new WorkoutView(
            session: $session,
            letter: $this->plans->handle($user)->letterFor($session->plan),
            exercises: $exercises,
        );
    }
}
