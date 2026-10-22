<?php

namespace Database\Factories\Training;

use App\Enums\Training\Equipment;
use App\Enums\Training\MuscleGroup;
use App\Models\Training\Exercise;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Exercise>
 */
class ExerciseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->unique()->words(2, true),
            'muscle_group' => fake()->randomElement(MuscleGroup::cases()),
            'equipment' => fake()->randomElement(Equipment::cases()),
        ];
    }

    /**
     * An exercise from the shared default catalog.
     */
    public function catalog(): static
    {
        return $this->state(fn (array $attributes) => ['user_id' => null]);
    }
}
