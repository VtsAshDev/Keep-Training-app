<?php

namespace App\Actions\Training;

use App\Data\Training\WorkoutSummary;
use App\Models\Training\WorkoutSession;
use App\Models\Training\WorkoutSet;
use App\Models\User;

class GetWorkoutSummary
{
    public function __construct(
        private GetWorkoutPlans $plans,
        private GetSessionRecords $records,
        private GetWeeklyGoalProgress $weeklyGoal,
    ) {}

    public function handle(User $user, int $sessionId): WorkoutSummary
    {
        $session = $user->workoutSessions()->with(['plan', 'sets.exercise'])->findOrFail($sessionId);
        $completed = $session->sets->where('completed', true);

        $exercises = [];
        foreach ($completed as $set) {
            $row = $exercises[$set->exercise_id] ?? ['name' => $set->exercise->name, 'sets' => 0, 'topWeightKg' => 0.0];
            $row['sets']++;
            $row['topWeightKg'] = max($row['topWeightKg'], (float) $set->weight_kg);
            $exercises[$set->exercise_id] = $row;
        }

        return new WorkoutSummary(
            session: $session,
            letter: $this->plans->handle($user)->letterFor($session->plan),
            durationMinutes: self::durationMinutes($session),
            volumeKg: self::volume($session),
            completedSets: $completed->count(),
            records: $this->records->handle($user, $session),
            exercises: array_values($exercises),
            weeklyGoal: $this->weeklyGoal->handle($user),
        );
    }

    public static function durationMinutes(WorkoutSession $session): int
    {
        $end = $session->finished_at ?? now();

        return (int) max(0, round($session->started_at->diffInMinutes($end)));
    }

    /**
     * Sum of weight × reps of the completed sets.
     */
    public static function volume(WorkoutSession $session): float
    {
        return (float) $session->sets
            ->where('completed', true)
            ->sum(fn (WorkoutSet $set) => (float) $set->weight_kg * (int) $set->reps);
    }
}
