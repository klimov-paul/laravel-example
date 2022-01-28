Authorization Gates
===================

See [Laravel docs about gates](https://laravel.com/docs/authorization#gates).

You do not need a complex RBAC to restrict access for particular application area.
For the most cases a simple role and permissions system is enough.

Open [App\Enums\AdminPermissionEnum](../app/Enums/AdminPermissionEnum.php) - see a list of segregated admin permissions
Open [App\Enums\AdminRoleEnum](../app/Enums/AdminRoleEnum.php) - see the list of possible admin roles.
Note that each role defines a list of permissions available for it.

Open [App\Gates\AdminPermission](../app/Gates/AdminPermission.php) - see permissions check implementation.
Open [App\Providers\AuthServiceProvider](../app/Providers/AuthServiceProvider.php) - see a new gate defined per each admin permission.

Open [App\Http\Controllers\Admin\AdminController](../app/Http/Controllers/Admin/AdminController.php) - see permission check at constructor.


Additional Gate Context
-----------------------

We could rewrite our gate definition in the way it accepts permission as argument. For example

```php
<?php

use App\Gates\AdminPermission;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('admin-permission', function (object $admin, $permission) {
            return AdminPermission::check($admin, $permission);
        });
    }
}
```

In this case our controller will look like following:

```php
<?php

use App\Enums\AdminPermissionEnum;
use App\Http\Controllers\Controller;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->authorize('admin-permission', [AdminPermissionEnum::ADMINS]);
    }
    
    // ...
}
```

In the first example a multiple gates were defined to simplify the code. Also, it allows permission checks via middleware.
For example:

```php
<?php

use App\Enums\AdminPermissionEnum;

Route::middleware('can:' . AdminPermissionEnum::ADMINS()->ability())->get('admins', AdminController::class . '@index');
```

The most common case for extra context in gate check is passing an Eloquent model instance. For example:

```php
<?php

use App\Models\Article;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('article-edit', function (object $user, Article $article) {
            if ($article->author_id === $user->id) {
                return true;
            }
            
            return false;
        });
    }
}
```

For the permission checks over entire CRUD policies are used.
