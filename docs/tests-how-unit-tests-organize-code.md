How Unit Tests Organize the Code
================================

Laravel segregates all automated tests into a 3 groups:

 - Unit tests, which test isolated application components
 - [Feature (HTTP) tests](https://laravel.com/docs/http-tests), which test entire HTTP route handling
 - Browser tests (performed via [Laravel Dusk](https://laravel.com/docs/dusk)), which test application behavior Browser, including JavaScript

Despite "Feature" and "Browser" tests are the closest to the project's raw use case definition, they should not dominate
over the application's tests. In fact the most tests should be written at "Unit" level.

Open [App\Http\Controllers\Api\Me\RentController](../app/Http/Controllers/Api/Me/RentController.php) see method `store()`.
It has a complex logic, which defines whether particular user can rent a particular book. There are various scenarios, like
subscription is missing or rent quota is exhausted and so on. However, instead of cover all the scenarios via "Feature" test,
we cover them via "Unit" test for the separated entity [App\Rules\AllowBookRentRule](../app/Rules/AllowBookRentRule.php).

Open [Tests\Unit\Rules\AllowBookRentRuleTest](../tests/Unit/Rules/AllowBookRentRuleTest.php), see how all the possible scenarios
are covered by tests.

Open [Tests\Feature\Me\RentsTest](../tests/Feature/Me/RentsTest.php), see that it contains only a basic test for `store`
scenario.

"Browser" test is always heavier than "Feature" test, and "Feature" test is always heavier than "Unit" one.
You should always try to cover all complex logic at "Unit" tests level. If you do this right, it will organize the application's
class structure of itself.
You will have to extract extra classes and methods, creating extra abstraction layers in order to be able writing "Unit" test for them.

See also:

 - [Tests\Unit\Services\Subscription\SubscriptionCheckoutTest](../tests/Unit/Services/Subscription/SubscriptionCheckoutTest.php)
 - [Tests\Unit\Services\Subscription\SubscriptionProlongerTest](../tests/Unit/Services/Subscription/SubscriptionProlongerTest.php)


Test Structure
--------------

Each test should consist of 3 virtual parts:

 - "Given" ("Initial state")
 - "When" ("Action")
 - "Then" ("Assertions")

When proper written, a particular unit test sounds like a project's use case. Test "formalizes" raw use cases.

See [Tests\Unit\Services\Subscription\SubscriptionProlongerTest](../tests/Unit/Services/Subscription/SubscriptionProlongerTest.php) for example.


Tests Performance
-----------------

Tests should run fast, otherwise they have no purpose.
Each time before making a `git commit` or `git push` you should always run the **entire** tests, ensuring nothing is broken
by your change-set. If tests execution takes several hours, it ends up with developers ignoring this vital step, and eventually
break of the application.


Auth Testing
------------

There is no need to re-test same thing over and over. In particular this applies to the "auth" feature.
Laravel provides and abstraction layer for the auth user handling via "Auth Guards". Thus, you should test user's login
only once at the dedicated test, at all other places use `actingAs()` test method to mock up auth guard.

Open [Tests\Feature\Auth\LoginTest](../tests/Feature/Auth/LoginTest.php), see the login logic tested at HTTP level.
Open [Tests\Feature\Me\RentsTest](../tests/Feature/Me/RentsTest.php), see `actingAs()` usage for logged-in user mock.
