Mock External Dependencies
==========================

Remember that tests should run fast and there is no need to re-check same thing several times.
External API services will consume extra time per each request, may even consume money per each request.
It is better to avoid thier unnecessary calls.

See [App\Services\Payment\Braintree](../app/Services/Payment/Braintree.php) as an external service example.

See [Tests\Support\Payment\BraintreeTrait](../tests/Support/Payment/BraintreeTrait.php). It serves 2 purposes:

 - ensure sandbox environment for the external API test
 - mock external service to speed up test execution.

See [Tests\Support\Payment\BraintreeMock](../tests/Support/Payment/BraintreeMock.php).

See test for actual API via "sandbox" at [Tests\Unit\Services\Payment\BraintreeTest](../tests/Unit/Services/Payment/BraintreeTest.php).
Once testing for API interaction is complete, it could be mocked for other tests.
See [Tests\Unit\Services\Subscription\SubscriptionCheckoutTest](../tests/Unit/Services/Subscription/SubscriptionCheckoutTest.php) for the
mock usage example.
