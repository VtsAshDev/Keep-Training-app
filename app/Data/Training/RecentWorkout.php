<?php

namespace App\Data\Training;

use App\Models\Training\WorkoutSession;

final readonly class RecentWorkout
{
    public function __construct(
        public WorkoutSession $session,
        public string $letter,
        public int $durationMinutes,
        public float $volumeKg,
        public int $records,
    ) {}
}
