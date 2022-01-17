Install/Update Scripts
======================

Some scripts should be executed after project updated from VCS:

```
composer install # ensure PHP dependencies are up-to-date with 'composer.lock'
php artisan migrate --seed # ensure DB structure is up-to-date
php artisan cache:clear
# and so on
```

There are several places where post-update scripts can be placed. But the most practical way is usage of internal console
command. Such command can access project's configuration such as `config('app.env')` to adjust its own behavior.

See [mad-web/laravel-initializer](https://github.com/mad-web/laravel-initializer).

See:

* [App\Initializers\Install](../app/Initializers/Install.php)
* [App\Initializers\Update](../app/Initializers/Update.php)
