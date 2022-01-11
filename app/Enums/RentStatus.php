<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static Pending()
 * @method static static Active()
 * @method static static Overdue()
 * @method static static Complete()
 * @method static static Canceled()
 * @method static static Lost()
 */
final class RentStatus extends Enum
{
    const PENDING = 10;
    const ACTIVE = 5;
    const OVERDUE = 8;
    const COMPLETE = 3;
    const CANCELED = 2;
    const LOST = 1;
}
