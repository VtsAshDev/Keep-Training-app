<?php

namespace App\Actions\Training;

use App\Data\Training\PlanListItem;
use App\Data\Training\PlanOverview;
use App\Models\Training\WorkoutPlan;
use App\Models\User;
use Carbon\CarbonImmutable;

class GetWorkoutPlans
{
    public function __construct(
        private GetNextWorkoutPlan $nextPlan,
    ) {}

    public function handle(User $user): PlanOverview
    {
        $plans = $user->workoutPlans()
            ->withCount('exercises')
            ->withMax(['sessions as last_done_at' => fn ($query) => $query->whereNotNull('finished_at')], 'started_at')
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        $nextId = $this->nextPlan->handle($user)?->id;
        $item = fn (WorkoutPlan $plan, string $letter) => new PlanListItem(
            plan: $plan,
            letter: $letter,
            exerciseCount: (int) $plan->getAttribute('exercises_count'),
            lastDoneAt: $plan->getAttribute('last_done_at') ? CarbonImmutable::parse($plan->getAttribute('last_done_at')) : null,
            isNext: $plan->id === $nextId,
        );

        $active = $plans->filter(fn (WorkoutPlan $plan) => ! $plan->isArchived())->values();
        $archived = $plans->filter(fn (WorkoutPlan $plan) => $plan->isArchived())->values();

        return new PlanOverview(
            active: array_values($active->map(fn (WorkoutPlan $plan, int $index) => $item($plan, self::letter($index)))->all()),
            archived: array_values($archived->map(fn (WorkoutPlan $plan) => $item($plan, mb_strtoupper(mb_substr($plan->name, 0, 1))))->all()),
        );
    }

    /**
     * A, B, ..., Z, AA, AB...
     */
    private static function letter(int $index): string
    {
        $letter = '';
        for ($n = $index + 1; $n > 0; $n = intdiv($n - 1, 26)) {
            $letter = chr(65 + ($n - 1) % 26).$letter;
        }

        return $letter;
    }
}
