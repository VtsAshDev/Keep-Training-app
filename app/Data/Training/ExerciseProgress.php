<?php

namespace App\Data\Training;

use App\Models\Training\Exercise;
use Carbon\CarbonInterface;

final readonly class ExerciseProgress
{
    /**
     * @param  list<float>  $points  Top weight of each workout, oldest first.
     */
    public function __construct(
        public Exercise $exercise,
        public array $points,
        public ?CarbonInterface $firstDate,
        public ?CarbonInterface $lastDate,
        public int $workouts,
        public ?float $currentKg,
        public ?PersonalRecord $record,
        public ?float $deltaKg,
    ) {}
}
