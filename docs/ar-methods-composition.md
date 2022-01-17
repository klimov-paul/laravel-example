ActiveRecord Methods Composition
================================

Pick Correct Method Owner
-------------------------

Term "encapsulation" in OOP means "uniting data with the functions, which process it". It is not about "public, private and protected".
Uniting of the fields with the functions to process them gives us "class".

OOP itself does not define the way how 2 classes should interact with each other. It is a complicated matter.
However, there is one basic rule, which should be used: encapsulation principle.
It says that in 2 classes interaction the method, which provide this interaction, should belong to the class, which has more
internal fields affected by interaction.

Do not put everything in `User` model. In this way it will end up in thousands of code lines.

See [App\Models\SubscriptionPlan](../app/Models/SubscriptionPlan.php). It defines all the data for the user's subscription,
while from user we need only his ID.
The most common mistake is putting `subscribe()` method inside `User` model following the "real-world-modelling" principle:

```php
$user->subscribe($subscriptionPlan);
```

It seems to sound correctly: "user subscribes to a subscription plan", but it produces a wrong code.

The correct will be:

```php
$subscriptionPlan->subscribe($user);
```

It sounds a little weird: "subscription plan subscribes a user", but still grammatically correct.

In such approach you write your code **around** the `User` instead of doing it through it.


Express Business Logic in Method Name
-------------------------------------

Open [App\Models\User](../app/Models/User.php), find `signup()` method.
Open [App\Http\Controllers\Api\Auth\SignupController](../app/Http/Controllers/Api/Auth/SignupController.php) see `signup()` method usage:

```php
(new User())->signup($validatedData);
```

Spell it out: "new user signs up".

The code should speak to you.

Methods, which create model instances from scratch can be made as static. But, in general, it is recommended to avoid static
method usage.


Do not mix up "Model" and "Controller" layers
---------------------------------------------

Open [App\Models\Book](../app/Models/Book.php), method `rent()`.
Open [App\Http\Controllers\Api\Me\RentController](../app/Http/Controllers/Api/Me/RentController.php) see `store()`.

Do not put validation logic inside the model's method. Validation should interact with user and thus with "View" layer.
It is better to be performed via validation rule. See [App\Rules\AllowBookRentRule](../app/Rules/AllowBookRentRule.php).

Keep in mind that we may create rent without an active subscription in some cases. For example: give a "present" to the most
valued customers.

Also see method `rent()` usage in unit tests.


Extract complex logic into separated classes
--------------------------------------------

Open [App\Services\Subscription\SubscriptionCheckout](../app/Services/Subscription/SubscriptionCheckout.php).
Open [App\Http\Controllers\Api\Me\SubscriptionController](../app/Http/Controllers/Api/Me/SubscriptionController.php) see `store()`.

Imagine we put all "subscription checkout" logic into some ActiveRecord model - it would not be good.
Pay attention how we use ActiveRecord instances inside the dedicated class - it works around them, around their relation mechanism.

Actually you can extract a separated class per each business process, like "signup".

```php
class Signup
{
    public function signup(array $data): User
    {
        $user = new User();
        // ...
        
        return $user;
    }
}
```

Some process may be close eanough to each other to be united under the same class.
Open [App\Services\Subscription\SubscriptionProlonger](../app/Services/Subscription/SubscriptionProlonger.php).
`SubscriptionProlonger` handles both "pending" and "expired" subscriptions as they require similar attention.
