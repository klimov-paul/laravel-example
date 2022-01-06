<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @see \App\Models\Subscription::$status
 *
 * @method static static Active()
 * @method static static Archived()
 * @method static static Pending()
 * @method static static Cancelled()
 */
final class SubscriptionStatus extends Enum
{
    const ACTIVE = 4;
    const ARCHIVED = 3;
    const PENDING = 2;
    const CANCELLED = 1;
}
