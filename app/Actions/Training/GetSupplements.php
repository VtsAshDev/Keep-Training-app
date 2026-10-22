<?php

namespace App\Actions\Training;

use App\Models\Training\Supplement;
use App\Models\User;
use Illuminate\Support\Collection;

class GetSupplements
{
    /**
     * @return Collection<array-key, Collection<int, Supplement>> Keyed by usage rule value, daily first.
     */
    public function handle(User $user): Collection
    {
        return $user->supplements()
            ->orderBy('name')
            ->get()
            ->toBase()
            ->groupBy(fn (Supplement $supplement) => $supplement->usage_rule->value)
            ->sortKeys();
    }
}
