<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * AdminRoleEnum determines the list of possible administrator roles.
 *
 * @see \App\Models\Admin::$role
 * @see \App\Enums\AdminPermissionEnum
 *
 * @method static static MASTER_ADMIN()
 * @method static static STAFF_MANAGER()
 * @method static static CUSTOMER_MANAGER()
 * @method static static INVENTORY_MANAGER()
 * @method static static CONTENT_MANAGER()
 */
final class AdminRoleEnum extends Enum
{
    const MASTER_ADMIN = 'master-admin';
    const STAFF_MANAGER = 'staff-manager';
    const CUSTOMER_MANAGER = 'customer-manager';
    const INVENTORY_MANAGER = 'inventory-manager';
    const CONTENT_MANAGER = 'content-manager';

    /**
     * @return AdminPermissionEnum[]
     */
    public function getAdminPermissions(): array
    {
        $permissionsMap = $this->adminPermissionsMap();

        if (! isset($permissionsMap[$this->value])) {
            return [];
        }

        return $permissionsMap[$this->value];
    }

    /**
     * @return AdminPermissionEnum[][]
     */
    private function adminPermissionsMap(): array
    {
        return [
            self::STAFF_MANAGER => [
                AdminPermissionEnum::ADMINS(),
            ],
            self::CUSTOMER_MANAGER => [
                AdminPermissionEnum::USERS(),
                AdminPermissionEnum::CATEGORIES(),
                AdminPermissionEnum::SUBSCRIPTION_PLANS(),
                AdminPermissionEnum::SUBSCRIPTIONS(),
            ],
            self::INVENTORY_MANAGER => [
                AdminPermissionEnum::BOOKS(),
                AdminPermissionEnum::RENTS(),
                AdminPermissionEnum::CATEGORIES(),
            ],
            self::CONTENT_MANAGER => [
                AdminPermissionEnum::CATEGORIES(),
            ],
            self::MASTER_ADMIN => array_values(AdminPermissionEnum::getInstances()),
        ];
    }

    /**
     * Finds roles, which has the specified permission(s).
     *
     * @param \App\Enums\AdminPermissionEnum[]|\App\Enums\AdminPermissionEnum|string[]|string $permission required permission or permission list.
     * @return static[] list of matched roles.
     */
    public static function findByPermission($permission): array
    {
        if (!is_iterable($permission)) {
            $permission = [$permission];
        }

        $expectedPermissions = [];
        foreach ($permission as $value) {
            $expectedPermissions[] = new AdminPermissionEnum($value);
        }

        $result = [];
        foreach (static::getInstances() as $value) {
            if (array_diff($expectedPermissions, $value->getAdminPermissions()) === []) {
                $result[] = $value;
            }
        }

        return $result;
    }
}
