<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * AdminPermissionEnum determines the list of admin panel permissions checks.
 *
 * @see \App\Enums\AdminRoleEnum
 *
 * @method static static ADMINS()
 * @method static static USERS()
 * @method static static CATEGORIES()
 * @method static static BOOKS()
 * @method static static SUBSCRIPTION_PLANS()
 * @method static static SUBSCRIPTIONS()
 * @method static static RENTS()
 * @method static static TELESCOPE()
 */
final class AdminPermissionEnum extends Enum
{
    const ADMINS = 'admins';
    const USERS = 'users';

    const CATEGORIES = 'categories';
    const BOOKS = 'books';
    const SUBSCRIPTION_PLANS = 'subscription-plans';

    const SUBSCRIPTIONS = 'subscriptions';
    const RENTS = 'rents';

    const TELESCOPE = 'telescope';

    /**
     * Returns the authorization ability name for the Gate.
     * @see \App\Gates\AdminPermission
     *
     * @return string auth ability name.
     */
    public function ability(): string
    {
        return 'admin-' . $this->value;
    }
}
