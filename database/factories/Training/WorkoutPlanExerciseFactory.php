<?php

namespace Database\Factories\Training;

use App\Models\Training\Exercise;
use App\Models\Training\WorkoutPlan;
use App\Models\Training\WorkoutPlanExercise;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkoutPlanExercise>
 */
class WorkoutPlanExerciseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workout_plan_id' => WorkoutPlan::factory(),
            'exercise_id' => fn (array $attributes) => Exercise::factory()->create([
                'user_id' => WorkoutPlan::query()->whereKey($attributes['workout_plan_id'])->firstOrFail()->user_id,
            ])->id,
            'position' => 1,
            'sets' => 3,
            'reps_min' => 8,
            'reps_max' => 12,
            'rest_seconds' => 90,
            'notes' => null,
        ];
    }
}
