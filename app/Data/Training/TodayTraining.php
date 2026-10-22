<?php

namespace App\Data\Training;

use App\Models\Training\WorkoutSession;

final readonly class TodayTraining
{
    public function __construct(
        public bool $hasPlans,
        public ?PlanListItem $suggested,
        public ?WorkoutSession $activeSession,
        public string $activeLetter,
        public int $activeDoneSets,
        public int $activeTotalSets,
        public ?WorkoutSession $lastWorkout,
        public string $lastWorkoutLetter,
    ) {}
}
