<?php

namespace App\Data\Training;

use App\Models\Training\WorkoutPlan;
use Carbon\CarbonInterface;

final readonly class PlanListItem
{
    public function __construct(
        public WorkoutPlan $plan,
        public string $letter,
        public int $exerciseCount,
        public ?CarbonInterface $lastDoneAt,
        public bool $isNext,
    ) {}
}
