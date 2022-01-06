<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static PaymentStatus Success()
 * @method static PaymentStatus Failed()
 */
final class PaymentStatus extends Enum
{
    const SUCCESS = 5;
    const FAILED = 1;
}
