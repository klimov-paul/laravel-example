<?php

namespace App\Providers;

use App\Enums\AdminPermissionEnum;
use App\Gates\AdminPermission;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // \App\Models\Model::class => \App\Policies\ModelPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        foreach (AdminPermissionEnum::getInstances() as $permission) {
            Gate::define($permission->ability(), function (object $admin) use ($permission) {
                return AdminPermission::check($admin, $permission);
            });
        }
    }
}
