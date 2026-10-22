<?php

namespace App\Actions\Training;

use App\Models\Training\Exercise;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Default catalog plus the user's exercises, grouped by muscle group (enum value) and sorted by name.
 */
class GetExercises
{
    /**
     * @return Collection<array-key, Collection<int, Exercise>>
     */
    public function handle(User $user, string $search = ''): Collection
    {
        $search = trim($search);

        return Exercise::query()
            ->availableTo($user)
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy('name')
            ->get()
            ->toBase()
            ->groupBy(fn (Exercise $exercise) => $exercise->muscle_group->value)
            ->sortKeys();
    }
}
