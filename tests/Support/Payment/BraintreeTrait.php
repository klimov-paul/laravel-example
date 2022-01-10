<?php

namespace Tests\Support\Payment;

use App\Services\Payment\Braintree;

/**
 * BraintreeTrait provide set of helper methods for Braintree related tests.
 *
 * @see https://articles.braintreepayments.com/get-started/try-it-out
 * @see https://developers.braintreepayments.com/reference/general/testing/php#credit-card-numbers
 * @see https://developers.braintreepayments.com/reference/general/testing/php#payment-method-nonces
 *
 * @mixin \Tests\TestCase
 */
trait BraintreeTrait
{
    /**
     * Skips test in case Braintree configuration is incorrect.
     */
    protected function skipOnBraintreeInvalidConfig()
    {
        if (config('services.braintree.environment') !== 'sandbox') {
            $this->markTestSkipped("Unable to run test while Braintree is not in 'sandbox' mode.");
        }

        if (!config('services.braintree.merchantId')) {
            $this->markTestSkipped("'Merchant ID' configuration for Braintree is missing.");
        }
    }

    /**
     * Returns valid credit card token (nonce) for testing.
     * @see https://developers.braintreepayments.com/reference/general/testing/php#payment-method-nonces
     *
     * @return string
     */
    protected function validCreditCardToken()
    {
        return 'fake-valid-visa-nonce';
    }

    /**
     * Returns valid credit card number for testing.
     * Credit card expiration date can be picked as any month/year from the future.
     * @see https://developers.braintreepayments.com/reference/general/testing/php#credit-card-numbers
     *
     * @return string
     */
    public function validCreditCardNumber()
    {
        return '378282246310005';
    }

    /**
     * @return \Tests\Support\Payment\BraintreeMock mock instance.
     */
    protected function mockBraintree(): BraintreeMock
    {
        $mock = new BraintreeMock();

        $this->app->instance(Braintree::class, $mock);

        return $mock;
    }
}
