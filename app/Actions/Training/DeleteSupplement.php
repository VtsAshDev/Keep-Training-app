<?php

namespace App\Actions\Training;

use App\Models\User;

class DeleteSupplement
{
    public function handle(User $user, int $supplementId): void
    {
        $user->supplements()->findOrFail($supplementId)->delete();
    }
}
