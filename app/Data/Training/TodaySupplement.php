<?php

namespace App\Data\Training;

use App\Models\Training\Supplement;

final readonly class TodaySupplement
{
    /**
     * @param  bool  $available  False for training_days supplements until a workout starts today.
     */
    public function __construct(
        public Supplement $supplement,
        public bool $taken,
        public bool $available,
    ) {}
}
