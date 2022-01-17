Performance Optimization
========================

See [Laravel official optimization guide](https://laravel.com/docs/deployment#optimization).


Composer Autoloader Optimization
--------------------------------

Separate "require" and "require-dev" Composer sections.

At production always use `composer install --no-dev`.


Code Caching
------------

### Config Cache

Parsing ".env" file and building all configs from directory listing takes significant time, while executed per each request.

Use following command to cache the config:

```
php artisan config:cache
```

**Heads up!** Make sure you do not use `env()` helper function anywhere outside "./config" folder, as it will be broken
once you cache the configuration.


### Route Cache

You may do not know it, but Laravel uses Symfony routing under the hood. Most of the classes packed in "illuminate/routing"
serves only for "pretty syntax" creation. Make sure to optimize routing configuration using cache:

```
php artisan route:cache
```

### Events Cache

```
php artisan event:cache
```

### Views Cache

```
php artisan view:cache
```

