<?php

namespace App\Enums\Training;

enum UsageRule: string
{
    case Daily = 'daily';
    case TrainingDays = 'training_days';

    public function label(): string
    {
        return __("training.usage_rule.{$this->value}");
    }
}
