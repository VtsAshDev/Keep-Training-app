<?php

namespace App\Enums\Training;

enum Equipment: string
{
    case Barbell = 'barbell';
    case Dumbbell = 'dumbbell';
    case Machine = 'machine';
    case Cable = 'cable';
    case Bodyweight = 'bodyweight';
    case Kettlebell = 'kettlebell';
    case Band = 'band';
    case Other = 'other';

    public function label(): string
    {
        return __("training.equipment.{$this->value}");
    }
}
