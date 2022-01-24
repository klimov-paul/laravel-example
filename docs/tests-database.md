Database Testing
================

Almost all web applications interact with a Database. Thus, in order to test an application we need setup a database
structure and some test data into it. Also, it is crucial that tests do not have "side effects": execution of the one test
should not affect the other. Thus there should be a mechanism to revert all database changes made within a particular test case.

Laravel provides several traits, which handles this:

 - `Illuminate\Foundation\Testing\RefreshDatabase`
 - `Illuminate\Foundation\Testing\LazilyRefreshDatabase`
 - `Illuminate\Foundation\Testing\DatabaseTransactions`

Remember that tests should run fast. Thus it is recommended to use `DatabaseTransactions` trait. It wraps all test execution
into a database transaction, which will be reverted after the test is complete. This allows us to manipulate the database
records without fear to affect other tests and without necessity to clear the database and re-apply the migrations, which
would take a lot of time.

Database migrations and basic seeders applied on project's working copy installation should be enough to support unit tests. 

See [Tests\TestCase](../tests/TestCase.php) for trait application example.


Factory Usage
-------------

While writing tests, you should not rely on existing database records as may change along with the seeders. Instead, you
should create all necessary entities via [DB factory](https://laravel.com/docs/database-testing#creating-models-using-factories) mechanism.

Note: do not use Laravel standard trait `Illuminate\Database\Eloquent\Factories\HasFactory`. DB factories along with `Faker`
are development dependencies (remember composer optimization topic) and have value only in unit tests, while Eloquent models
used all the time. There is no need to add extra code parsing for their source just to be able manipulating over factories.
Factor should be instantiated via `new()` static method, it will do just the same thing as `HasFactory::factory()`. For example:

```php
<?php

namespace Tests\Feature;

use Database\Factories\BookFactory;
use Tests\TestCase;

class BooksTest extends TestCase
{
    public function testIndex()
    {
        $books = BookFactory::new()->count(2)->create(); // same as `Book::factory()->count(2)->create()`
        // ...
    }
}
```

In order to support IDE autocompletion, you should add PHPDoc for methods `make()` and `create()`. For example:

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @method \App\Models\Book|\App\Models\Book[] make(array $attributes = [])
 * @method \App\Models\Book|\App\Models\Book[] create(array $attributes = [])
 */
class BookFactory extends Factory
{
    // ...
}
```

Create all necessary models via factories with required attributes and state.
See examples:

 - [Tests\Unit\Rules\AllowBookRentRuleTest](../tests/Unit/Rules/AllowBookRentRuleTest.php)
 - [Tests\Feature\BooksTest](../tests/Feature/BooksTest.php)

> Note: do not use "magic numbers" for particular records ID or username in the tests, as they may be broken on tests re-run.
