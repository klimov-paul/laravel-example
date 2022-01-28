<?php

namespace App\Gates;

use App\Enums\AdminPermissionEnum;
use App\Enums\AdminRoleEnum;
use App\Models\Admin;

/**
 * AdminPermission defines the auth gate, which restricts access to admin panel sections.
 *
 * Usage via `Gate` facade example:
 *
 * ```
 * if (Gate::allows(AdminPermissionEnum::PERMISSION_NAME()->ability())) {
 *     // The current user can access admin section
 * }
 * ```
 *
 * Usage via model example:
 *
 * ```php
 * if ($user->can(AdminPermissionEnum::PERMISSION_NAME()->ability())) {
 *     // The user can access admin section
 * }
 * ```
 *
 * Usage in controller example:
 *
 * ```php
 * use App\Enums\UserPermissionEnum;
 * use App\Http\Controllers\Controller;
 *
 * class ProtectedSectionController extends Controller
 * {
 *     public function index()
 *     {
 *         $this->authorize(AdminPermissionEnum::PERMISSION_NAME()->ability());
 *
 *         // ...
 *     }
 * }
 * ```
 *
 * Usage via middleware example:
 *
 * ```php
 * Route::get('protected-items', function () {
 *     // The current user may see protected items
 * })->middleware('can:' . AdminPermissionEnum::PROTECTED_ITEMS()->ability());
 * ```
 *
 * @see \App\Enums\AdminPermissionEnum
 * @see \App\Providers\AuthServiceProvider
 */
class AdminPermission
{
    /**
     * @param \App\Models\Admin|object $user user attempting to access.
     * @param \App\Enums\AdminPermissionEnum|string $permission permission instance or raw ID.
     * @return bool whether this user has specified permission.
     */
    public static function check(object $user, $permission): bool
    {
        if (!$user instanceof Admin) {
            return false;
        }

        if (empty($user->role)) {
            return false;
        }

        $role = new AdminRoleEnum($user->role); // ensure 'role' value is valid

        if (!$permission instanceof AdminPermissionEnum) {
            $permission = new AdminPermissionEnum($permission); // ensure 'permission' value is valid
        }

        $rolePermissions = $role->getAdminPermissions();

        if (!in_array($permission, $rolePermissions)) {
            return false;
        }

        return true;
    }
}
