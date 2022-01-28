Authorization Policies
======================

See [Laravel docs about policies](https://laravel.com/docs/authorization#creating-policies).

Policy defines a list of gates for CRUD operations access over specific Eloquent model.

```php
<?php

use App\Models\Article;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ArticlePolicy
{
    use HandlesAuthorization;

    public function view(User $user, Article $model)
    {
        return true;
    }

    public function create(User $user)
    {
        return false;
    }

    public function update(User $user, Article $model)
    {
        if ($model->author_id === $user->id) {
            return true;
        }
        
        if ($user->is_moderator) {
            return true;
        }
        
        return false;
    }
    
    // ...
}
```

Controller example:

```php
<?php

use App\Http\Controllers\Controller;
use App\Models\Article;

class ArticleController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Article::class, 'article');
    }
    
    // ...
}
```
