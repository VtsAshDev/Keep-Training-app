<?php

namespace App\Data\Training;

final readonly class WeeklyGoalProgress
{
    /**
     * @param  list<'done'|'today'|'pending'>  $days  Monday to Sunday, ready for the week-dots component.
     */
    public function __construct(
        public int $completed,
        public int $goal,
        public bool $met,
        public int $streak,
        public array $days,
    ) {}
}
