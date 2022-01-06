<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static CreditCardStatus Active()
 * @method static CreditCardStatus Inactive()
 * @method static CreditCardStatus Expired()
 */
final class CreditCardStatus extends Enum
{
    const ACTIVE = 5;
    const INACTIVE = 1;
    const EXPIRED = 2;
}
