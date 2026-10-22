<?php

namespace App\Exceptions\Training;

use DomainException;

/**
 * A Training business rule refused the operation; the message is user-facing.
 */
class TrainingRuleException extends DomainException
{
    public static function workoutInProgress(): self
    {
        return new self(__('training.errors.workout_in_progress'));
    }

    public static function sessionNotInProgress(): self
    {
        return new self(__('training.errors.session_not_in_progress'));
    }

    public static function planArchived(): self
    {
        return new self(__('training.errors.plan_archived'));
    }

    public static function supplementUnavailable(): self
    {
        return new self(__('training.errors.supplement_unavailable'));
    }
}
