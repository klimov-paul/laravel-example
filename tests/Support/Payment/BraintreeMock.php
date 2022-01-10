<?php

namespace Tests\Support\Payment;

use App\Services\Payment\Braintree;

class BraintreeMock extends Braintree
{
    /**
     * @var array mock balance sums in format: `[customerId => balanceSum]`
     */
    public array $balances = [];

    public function __construct()
    {
        parent::__construct([]);
    }

    /**
     * {@inheritdoc}
     */
    public function createCustomer(string $paymentMethodNonce, array $options = []): array
    {
        $customerId = microtime();

        $this->balances[$customerId] = 0;

        return [
            'customer_id' => $customerId,
            'paypal_email' => null,
            'card_brand' => 'visa',
            'card_last_four' => '4321',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function charge($customerId, $amount, array $options = []): array
    {
        $this->balances[$customerId] -= $amount;

        return [
            'id' => uniqid(),
            'amount' => number_format($amount, 2, '.', ''),
        ];
    }
}
