<?php

namespace App\Data\Training;

final readonly class ProgressionSuggestion
{
    public function __construct(
        public float $lastWeightKg,
        public float $suggestedWeightKg,
    ) {}
}
