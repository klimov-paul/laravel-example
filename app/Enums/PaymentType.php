<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static PaymentType Subscription()
 * @method static PaymentType Purchase()
 * @method static PaymentType Donation()
 */
final class PaymentType extends Enum
{
    const SUBSCRIPTION = 1;
    const PURCHASE = 2;
    const DONATION = 3;
    const REFUND = 4;
}
