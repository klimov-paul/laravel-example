<?php

namespace Tests\Feature\Admin;

use App\Enums\AdminRoleEnum;
use App\Models\Admin;
use Database\Factories\AdminFactory;
use PHPUnit\Framework\Attributes\Depends;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Admin\AdminController
 */
class AdminsTest extends TestCase
{
    protected Admin $admin;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = AdminFactory::new()->create();
    }

    public function testIndex(): void
    {
        $this->actingAs($this->admin, 'web-admin');

        $admins = AdminFactory::new()->count(2)->create();

        $this->getJson(route('admin.admins.index', ['sort' => '-id']))
            ->assertSuccessful();
    }

    #[Depends('testIndex')]
    public function testPermissions(): void
    {
        $this->admin->update(['role' => AdminRoleEnum::CONTENT_MANAGER]);

        $this->actingAs($this->admin, 'web-admin');

        $this->getJson(route('admin.admins.index'))
            ->assertForbidden();
    }
}
