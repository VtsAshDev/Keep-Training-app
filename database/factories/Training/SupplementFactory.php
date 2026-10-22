<?php

namespace Database\Factories\Training;

use App\Enums\Training\UsageRule;
use App\Models\Training\Supplement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Supplement>
 */
class SupplementFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->randomElement(['Creatina', 'Vitamina D', 'Whey protein', 'Ômega 3']),
            'dose' => 5,
            'unit' => 'g',
            'dose_notes' => null,
            'scheduled_time' => 'manhã',
            'usage_rule' => UsageRule::Daily,
            'notes' => null,
        ];
    }

    public function trainingDays(): static
    {
        return $this->state(fn (array $attributes) => ['usage_rule' => UsageRule::TrainingDays]);
    }
}
