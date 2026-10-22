<?php

namespace App\Actions\Core;

use App\Enums\Core\LoginMethod;
use App\Models\User;

/**
 * Remembers how the user signed in: in the session for this access, and on the
 * user as a fallback for when "remember me" restores the login in a new session.
 */
class RecordLogin
{
    public const string SESSION_KEY = 'login_method';

    public function handle(User $user, LoginMethod $method): void
    {
        $user->update(['last_login_method' => $method]);

        session()->put(self::SESSION_KEY, $method->value);
    }
}
