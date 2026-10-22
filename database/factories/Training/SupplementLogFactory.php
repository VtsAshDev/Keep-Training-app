<?php

namespace Database\Factories\Training;

use App\Models\Training\Supplement;
use App\Models\Training\SupplementLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupplementLog>
 */
class SupplementLogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'supplement_id' => fn (array $attributes) => Supplement::factory()->create(['user_id' => $attributes['user_id']])->id,
            'date' => now()->toDateString(),
            'taken' => true,
        ];
    }
}
