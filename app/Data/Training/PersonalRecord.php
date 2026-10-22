<?php

namespace App\Data\Training;

use App\Models\Training\Exercise;

final readonly class PersonalRecord
{
    public function __construct(
        public Exercise $exercise,
        public float $weightKg,
        public int $reps,
    ) {}
}
