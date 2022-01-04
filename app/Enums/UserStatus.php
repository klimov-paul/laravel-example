<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @see \App\Models\User::$status
 *
 * @method static static Active()
 * @method static static Pending()
 * @method static static Banned()
 * @method static static Inactive()
 */
final class UserStatus extends Enum
{
    const ACTIVE = 5;

    const PENDING = 3;

    const BANNED = 2;

    const INACTIVE = 1;
}
