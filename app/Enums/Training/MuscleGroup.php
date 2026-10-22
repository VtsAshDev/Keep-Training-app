<?php

namespace App\Enums\Training;

enum MuscleGroup: string
{
    case Chest = 'chest';
    case Back = 'back';
    case Shoulders = 'shoulders';
    case Biceps = 'biceps';
    case Triceps = 'triceps';
    case Forearms = 'forearms';
    case Abs = 'abs';
    case Quadriceps = 'quadriceps';
    case Hamstrings = 'hamstrings';
    case Glutes = 'glutes';
    case Calves = 'calves';
    case FullBody = 'full_body';
    case Cardio = 'cardio';

    public function label(): string
    {
        return __("training.muscle_group.{$this->value}");
    }
}
