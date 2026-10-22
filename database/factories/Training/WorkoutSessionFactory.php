<?php

namespace Database\Factories\Training;

use App\Models\Training\WorkoutPlan;
use App\Models\Training\WorkoutSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkoutSession>
 */
class WorkoutSessionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'workout_plan_id' => fn (array $attributes) => WorkoutPlan::factory()->create(['user_id' => $attributes['user_id']])->id,
            'started_at' => now()->subHour(),
            'finished_at' => now(),
            'notes' => null,
        ];
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => ['finished_at' => null]);
    }
}
