<?php

namespace Tests\Support\Payment;

use App\Services\Payment\Braintree;

class BraintreeMock extends Braintree
{
    /**
     * @var array mock balance sums in format: `[paymentMethodToken => balanceSum]`
     */
    public array $balances = [];

    public function __construct()
    {
        parent::__construct([]);
    }

    /**
     * {@inheritdoc}
     */
    public function createCustomerWithPaymentMethod(string $paymentMethodNonce, array $options = []): array
    {
        $customerId = microtime();
        $token = uniqid();

        $this->balances[$token] = 0;

        return [
            'customer_id' => $customerId,
            'token' => $token,
            'paypal_email' => null,
            'card_brand' => 'visa',
            'card_last_four' => '4321',
            'card_expiration_month' => 12,
            'card_expiration_year' => (int) date('Y', strtotime('+5 year')),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function createPaymentMethod(string $customerId, string $paymentMethodNonce, array $options = []): array
    {
        $token = uniqid();

        if (!isset($this->balances[$token])) {
            $this->balances[$token] = 0;
        }

        return [
            'customer_id' => $customerId,
            'token' => $token,
            'paypal_email' => null,
            'card_brand' => 'visa',
            'card_last_four' => '4321',
            'card_expiration_month' => 12,
            'card_expiration_year' => (int) date('Y', strtotime('+5 year')),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function sale(string $paymentMethodToken, int|float $amount, array $options = []): array
    {
        $this->balances[$paymentMethodToken] -= $amount;

        return [
            'id' => uniqid(),
            'amount' => number_format($amount, 2, '.', ''),
        ];
    }
}
