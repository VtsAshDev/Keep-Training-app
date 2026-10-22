<?php

namespace App\Enums\Core;

enum LoginMethod: string
{
    case Google = 'google';
    case Email = 'email';

    public function label(): string
    {
        return __("core.login_method.{$this->value}");
    }
}
