<?php

namespace App\Data\Training;

use App\Models\Training\WorkoutSession;

final readonly class WorkoutView
{
    /**
     * @param  list<WorkoutExerciseView>  $exercises  In plan order.
     */
    public function __construct(
        public WorkoutSession $session,
        public string $letter,
        public array $exercises,
    ) {}

    public function doneSets(): int
    {
        return array_sum(array_map(fn (WorkoutExerciseView $exercise) => $exercise->doneSets(), $this->exercises));
    }

    public function totalSets(): int
    {
        return array_sum(array_map(fn (WorkoutExerciseView $exercise) => $exercise->sets->count(), $this->exercises));
    }

    /**
     * The first exercise with pending sets, which the screen keeps expanded.
     */
    public function current(): ?WorkoutExerciseView
    {
        foreach ($this->exercises as $exercise) {
            if (! $exercise->isDone()) {
                return $exercise;
            }
        }

        return null;
    }
}
