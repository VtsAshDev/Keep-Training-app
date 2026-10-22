<?php

namespace App\Data\Training;

use App\Models\Training\WorkoutSession;

final readonly class WorkoutSummary
{
    /**
     * @param  list<PersonalRecord>  $records
     * @param  list<array{name: string, sets: int, topWeightKg: float}>  $exercises
     */
    public function __construct(
        public WorkoutSession $session,
        public string $letter,
        public int $durationMinutes,
        public float $volumeKg,
        public int $completedSets,
        public array $records,
        public array $exercises,
        public WeeklyGoalProgress $weeklyGoal,
    ) {}
}
