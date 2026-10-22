<?php

namespace App\Actions\Training;

use App\Enums\Training\UsageRule;
use App\Models\Training\Supplement;
use App\Models\User;

/**
 * Creates a supplement, or updates one of the user's when an id is given.
 */
class SaveSupplement
{
    /**
     * @param  array{name: string, dose: float|string, unit: string, dose_notes?: string|null, scheduled_time?: string|null, usage_rule: UsageRule, notes?: string|null}  $data
     */
    public function handle(User $user, ?int $supplementId, array $data): Supplement
    {
        if ($supplementId === null) {
            return $user->supplements()->create($data);
        }

        $supplement = $user->supplements()->findOrFail($supplementId);
        $supplement->update($data);

        return $supplement;
    }
}
