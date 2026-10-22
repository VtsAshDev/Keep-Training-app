<?php

namespace App\Data\Training;

use App\Models\Training\Exercise;
use App\Models\Training\WorkoutPlanExercise;
use App\Models\Training\WorkoutSet;
use Illuminate\Support\Collection;

final readonly class WorkoutExerciseView
{
    /**
     * @param  Collection<int, WorkoutSet>  $sets  Sets of this workout, by set number.
     * @param  Collection<int, WorkoutSet>  $previous  Completed sets of the last workout, keyed by set number.
     */
    public function __construct(
        public Exercise $exercise,
        public ?WorkoutPlanExercise $planItem,
        public Collection $sets,
        public Collection $previous,
        public ?ProgressionSuggestion $suggestion,
    ) {}

    public function doneSets(): int
    {
        return $this->sets->where('completed', true)->count();
    }

    public function isDone(): bool
    {
        return $this->sets->isNotEmpty() && $this->doneSets() === $this->sets->count();
    }
}
