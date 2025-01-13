<?php

namespace Models;

use App\Enums\AdminPermissionEnum;
use App\Enums\AdminRoleEnum;
use App\Models\Admin;
use Database\Factories\AdminFactory;
use Tests\TestCase;

class AdminTest extends TestCase
{
    public function testHasPermission(): void
    {
        $admin = AdminFactory::new()->create([
            'role' => AdminRoleEnum::MASTER_ADMIN,
        ]);

        $foundAdmin = Admin::query()
            ->hasPermission(AdminPermissionEnum::ADMINS)
            ->where('id', $admin->id)
            ->first();

        $this->assertNotEmpty($foundAdmin);

        $admin = AdminFactory::new()->create([
            'role' => AdminRoleEnum::CONTENT_MANAGER,
        ]);

        $foundAdmin = Admin::query()
            ->hasPermission(AdminPermissionEnum::ADMINS)
            ->where('id', $admin->id)
            ->first();

        $this->assertEmpty($foundAdmin);
    }
}
