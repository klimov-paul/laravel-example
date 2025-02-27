<?php

namespace Tests\Unit\Services\Payment;

use App\Services\Payment\Braintree;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Payment\BraintreeTrait;
use Tests\TestCase;

#[Group('external-service')]
#[Group('braintree')]
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

    public function testCharge(): void
    {
        $paymentMethod = $this->paymentGateway->createCustomerWithPaymentMethod($this->validPaymentMethodNonce());

        $this->assertFalse(empty($paymentMethod['customer_id']));
        $this->assertFalse(empty($paymentMethod['token']));
        $this->assertFalse(empty($paymentMethod['card_brand']));
        $this->assertFalse(empty($paymentMethod['card_last_four']));

        $result = $this->paymentGateway->sale($paymentMethod['token'], 100);

        $this->assertFalse(empty($result['id']));

        $anotherPaymentMethod = $this->paymentGateway->createPaymentMethod($paymentMethod['customer_id'], $this->validPaymentMethodNonce());
        $this->assertFalse(empty($anotherPaymentMethod['customer_id']));
        $this->assertFalse(empty($anotherPaymentMethod['token']));
        $this->assertFalse(empty($anotherPaymentMethod['card_brand']));
        $this->assertFalse(empty($anotherPaymentMethod['card_last_four']));

        $result = $this->paymentGateway->sale($anotherPaymentMethod['token'], 200);

        $this->assertFalse(empty($result['id']));
    }
}
