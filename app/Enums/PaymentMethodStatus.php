<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static PaymentMethodStatus Active()
 * @method static PaymentMethodStatus Inactive()
 * @method static PaymentMethodStatus Expired()
 */
final class PaymentMethodStatus extends Enum
{
    const ACTIVE = 5;
    const INACTIVE = 1;
    const EXPIRED = 2;
}
