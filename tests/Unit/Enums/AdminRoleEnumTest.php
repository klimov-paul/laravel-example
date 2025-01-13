<?php

namespace Tests\Unit\Enums;

use App\Enums\AdminPermissionEnum;
use App\Enums\AdminRoleEnum;
use Tests\TestCase;

class AdminRoleEnumTest extends TestCase
{
    public function testFindByPermission(): void
    {
        $roles = AdminRoleEnum::findByPermission(AdminPermissionEnum::CATEGORIES);
        foreach ($roles as $role) {
            $this->assertContainsEquals(AdminPermissionEnum::CATEGORIES(), $role->getAdminPermissions());
        }

        $roles = AdminRoleEnum::findByPermission(AdminPermissionEnum::ADMINS);
        foreach ($roles as $role) {
            $this->assertContainsEquals(AdminPermissionEnum::ADMINS(), $role->getAdminPermissions());
        }
    }
}
