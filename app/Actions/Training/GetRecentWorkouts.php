<?php

namespace App\Actions\Training;

use App\Data\Training\RecentWorkout;
use App\Models\Training\WorkoutSession;
use App\Models\User;

class GetRecentWorkouts
{
    public function __construct(
        private GetWorkoutPlans $plans,
        private GetSessionRecords $records,
    ) {}

    /**
     * @return list<RecentWorkout>
     */
    public function handle(User $user, int $limit = 10): array
    {
        $overview = $this->plans->handle($user);

        return array_values($user->workoutSessions()
            ->finished()
            ->with(['plan', 'sets.exercise'])
            ->latest('started_at')
            ->limit($limit)
            ->get()
            ->map(fn (WorkoutSession $session) => new RecentWorkout(
                session: $session,
                letter: $overview->letterFor($session->plan),
                durationMinutes: GetWorkoutSummary::durationMinutes($session),
                volumeKg: GetWorkoutSummary::volume($session),
                records: count($this->records->handle($user, $session)),
            ))
            ->all());
    }
}
