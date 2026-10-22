<?php

namespace App\Actions\Core;

use App\Models\User;
use InvalidArgumentException;

class UpdateWeeklyGoal
{
    public const int MIN = 1;

    public const int MAX = 7;

    public function handle(User $user, int $goal): User
    {
        if ($goal < self::MIN || $goal > self::MAX) {
            throw new InvalidArgumentException('Weekly goal must be between '.self::MIN.' and '.self::MAX.'.');
        }

        $user->update(['weekly_workout_goal' => $goal]);

        return $user;
    }
}
