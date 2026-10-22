<?php

namespace App\Data\Training;

use App\Models\Training\WorkoutPlan;

final readonly class PlanOverview
{
    /**
     * @param  list<PlanListItem>  $active  In rotation order.
     * @param  list<PlanListItem>  $archived
     */
    public function __construct(
        public array $active,
        public array $archived,
    ) {}

    public function next(): ?PlanListItem
    {
        foreach ($this->active as $item) {
            if ($item->isNext) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Active plans are lettered A, B, C... by rotation order; archived ones use their initial.
     */
    public function letterFor(?WorkoutPlan $plan): string
    {
        if ($plan === null) {
            return '–';
        }

        foreach ([...$this->active, ...$this->archived] as $item) {
            if ($item->plan->id === $plan->id) {
                return $item->letter;
            }
        }

        return mb_strtoupper(mb_substr($plan->name, 0, 1));
    }
}
