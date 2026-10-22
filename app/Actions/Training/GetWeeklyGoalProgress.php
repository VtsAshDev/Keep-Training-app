<?php

namespace App\Actions\Training;

use App\Data\Training\WeeklyGoalProgress;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Weeks run Monday to Sunday. The streak counts consecutive weeks that met the goal;
 * the current week adds to it once met, but only breaks it after it ends.
 */
class GetWeeklyGoalProgress
{
    public function handle(User $user): WeeklyGoalProgress
    {
        $today = CarbonImmutable::now();
        $weekStart = $today->startOfWeek(CarbonInterface::MONDAY);
        $goal = max(1, $user->weekly_workout_goal);

        /** @var array<string, int> $perWeek */
        $perWeek = [];
        /** @var array<string, true> $trainedDays */
        $trainedDays = [];

        $startedAt = $user->workoutSessions()->finished()->pluck('started_at');

        foreach ($startedAt as $date) {
            $date = CarbonImmutable::parse($date);
            $week = $date->startOfWeek(CarbonInterface::MONDAY)->toDateString();
            $perWeek[$week] = ($perWeek[$week] ?? 0) + 1;
            $trainedDays[$date->toDateString()] = true;
        }

        $completed = $perWeek[$weekStart->toDateString()] ?? 0;
        $met = $completed >= $goal;

        $streak = $met ? 1 : 0;
        $week = $weekStart->subWeek();
        while (($perWeek[$week->toDateString()] ?? 0) >= $goal) {
            $streak++;
            $week = $week->subWeek();
        }

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $weekStart->addDays($i);
            $days[] = match (true) {
                isset($trainedDays[$day->toDateString()]) => 'done',
                $day->isSameDay($today) => 'today',
                default => 'pending',
            };
        }

        return new WeeklyGoalProgress($completed, $goal, $met, $streak, $days);
    }
}
