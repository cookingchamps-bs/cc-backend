<?php

namespace App\Support;

use Illuminate\Validation\Rules\Password;

class PasswordRules
{
    public static function parent(): Password
    {
        return Password::min(8)->letters()->numbers()->uncompromised();
    }

    public static function kid(): Password
    {
        return Password::min(8)->letters()->numbers();
    }
}