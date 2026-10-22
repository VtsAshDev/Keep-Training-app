<?php

namespace App\Actions\Training;

use App\Data\Training\TodaySupplement;
use App\Enums\Training\UsageRule;
use App\Models\Training\Supplement;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Supplements for the Today screen: daily ones are always available,
 * training_days ones only after a workout starts today.
 */
class GetTodaySupplements
{
    public function __construct(
        private IsTrainingDay $isTrainingDay,
    ) {}

    /**
     * @return Collection<int, TodaySupplement>
     */
    public function handle(User $user): Collection
    {
        $today = now()->toDateString();
        $trainingDay = $this->isTrainingDay->handle($user);

        return $user->supplements()
            ->with(['logs' => fn ($query) => $query->whereDate('date', $today)])
            ->orderBy('usage_rule')
            ->orderBy('name')
            ->get()
            ->map(fn (Supplement $supplement) => new TodaySupplement(
                supplement: $supplement,
                taken: (bool) $supplement->logs->first()?->taken,
                available: $supplement->usage_rule === UsageRule::Daily || $trainingDay,
            ));
    }
}
