<?php

namespace App\Actions\Training;

use App\Enums\Training\UsageRule;
use App\Exceptions\Training\TrainingRuleException;
use App\Models\Training\SupplementLog;
use App\Models\User;

/**
 * Marks or unmarks today's dose of one of the user's supplements.
 */
class ToggleSupplementLog
{
    public function __construct(
        private IsTrainingDay $isTrainingDay,
    ) {}

    public function handle(User $user, int $supplementId): SupplementLog
    {
        $supplement = $user->supplements()->findOrFail($supplementId);

        if ($supplement->usage_rule === UsageRule::TrainingDays && ! $this->isTrainingDay->handle($user)) {
            throw TrainingRuleException::supplementUnavailable();
        }

        $today = now()->startOfDay();

        // whereDate because the date cast stores a full timestamp.
        $log = $user->supplementLogs()
            ->where('supplement_id', $supplement->id)
            ->whereDate('date', $today->toDateString())
            ->first()
            ?? $user->supplementLogs()->make(['supplement_id' => $supplement->id, 'date' => $today]);

        $log->taken = ! $log->taken;
        $log->save();

        return $log;
    }
}
