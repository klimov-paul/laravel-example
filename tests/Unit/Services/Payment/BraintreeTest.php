<?php

namespace Tests\Unit\Services\Payment;

use App\Services\Payment\Braintree;
use Tests\Support\BraintreeTrait;
use Tests\TestCase;

/**
 * @group external-service
 * @group braintree
 */
class BraintreeTest extends TestCase
{
    use BraintreeTrait;

    protected Braintree $paymentGateway;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->skipOnBraintreeInvalidConfig();

        $this->paymentGateway = $this->app->make(Braintree::class);
    }

    public function testCharge()
    {
        $customerData = $this->paymentGateway->createCustomer($this->validCreditCardToken());

        $this->assertFalse(empty($customerData['customer_id']));
        $this->assertFalse(empty($customerData['card_brand']));
        $this->assertFalse(empty($customerData['card_last_four']));

        $result = $this->paymentGateway->charge($customerData['customer_id'], 100);

        $this->assertFalse(empty($result['id']));
    }
}
