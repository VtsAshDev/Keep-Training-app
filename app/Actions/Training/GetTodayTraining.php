<?php

namespace App\Actions\Training;

use App\Data\Training\TodayTraining;
use App\Models\User;

/**
 * Public entry point of the Training module for the Today screen.
 */
class GetTodayTraining
{
    public function __construct(
        private GetWorkoutPlans $plans,
        private GetActiveWorkoutSession $activeSession,
    ) {}

    public function handle(User $user): TodayTraining
    {
        $overview = $this->plans->handle($user);
        $active = $this->activeSession->handle($user)?->load(['plan', 'sets']);
        $last = $user->workoutSessions()->finished()->with('plan')->latest('finished_at')->latest('id')->first();

        return new TodayTraining(
            hasPlans: $overview->active !== [],
            suggested: $overview->next(),
            activeSession: $active,
            activeLetter: $overview->letterFor($active?->plan),
            activeDoneSets: $active?->sets->where('completed', true)->count() ?? 0,
            activeTotalSets: $active?->sets->count() ?? 0,
            lastWorkout: $last,
            lastWorkoutLetter: $overview->letterFor($last?->plan),
        );
    }
}
