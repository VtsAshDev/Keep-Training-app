<?php

namespace Database\Factories\Training;

use App\Models\Training\Exercise;
use App\Models\Training\WorkoutSession;
use App\Models\Training\WorkoutSet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkoutSet>
 */
class WorkoutSetFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workout_session_id' => WorkoutSession::factory(),
            'exercise_id' => fn (array $attributes) => Exercise::factory()->create([
                'user_id' => WorkoutSession::query()->whereKey($attributes['workout_session_id'])->firstOrFail()->user_id,
            ])->id,
            'set_number' => 1,
            'weight_kg' => 50,
            'reps' => 10,
            'completed' => true,
        ];
    }
}
