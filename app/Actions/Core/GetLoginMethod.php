<?php

namespace App\Actions\Core;

use App\Enums\Core\LoginMethod;
use App\Models\User;

class GetLoginMethod
{
    public function handle(User $user): ?LoginMethod
    {
        return LoginMethod::tryFrom((string) session(RecordLogin::SESSION_KEY)) ?? $user->last_login_method;
    }
}
