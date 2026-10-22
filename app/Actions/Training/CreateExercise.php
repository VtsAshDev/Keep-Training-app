<?php

namespace App\Actions\Training;

use App\Enums\Training\Equipment;
use App\Enums\Training\MuscleGroup;
use App\Models\Training\Exercise;
use App\Models\User;

class CreateExercise
{
    public function handle(User $user, string $name, MuscleGroup $muscleGroup, ?Equipment $equipment = null): Exercise
    {
        return $user->exercises()->create([
            'name' => trim($name),
            'muscle_group' => $muscleGroup,
            'equipment' => $equipment,
        ]);
    }
}
