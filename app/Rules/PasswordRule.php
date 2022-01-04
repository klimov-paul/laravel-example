<?php

namespace App\Rules;

use Illuminate\Validation\Rules\Password;

class PasswordRule extends Password
{
    public function __construct()
    {
        parent::__construct(8);

        $this->letters()
            ->numbers()
            ->symbols();
    }
}
