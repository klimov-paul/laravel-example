Database Seeding
================

You should distinguish default DB seed from dedicated one.
See [Database\Seeders\DatabaseSeeder](../database/seeders/DatabaseSeeder.php), it contains seeds, which should be applicable 
for the database in repeating mode. Each time migrations are applied this default seeder should be invoked.
In such way you can maintain dictionary-type tables content, keeping them up-to-date with DB structure changes.
Thus, it is common to place conditions, which checks whether particular table is empty, before seeding it up.
See [Database\Seeders\CategorySeeder](../database/seeders/CategorySeeder.php) for example.
Default seeders should populate only data, without which application can not function.

Seeders mainly serve for pre-populating DB records for the application visual demonstration.
Such seeders should be separated from the default one, and should be applied manually:

```
php artisan db:seed --class DemoSeeder
```

[Database\Seeders\DemoSeeder](../database/seeders/DemoSeeder.php) for example.

Demo seeders can be applied along with default ones for the development usability at 'local' environment.
