<?php

namespace App\Services\Payment;

use Braintree\Gateway;
use Braintree\PayPalAccount;
use Braintree\Result\Successful;

/**
 * Braintree provides abstraction for "PayPal Braintree" payment gateway interaction.
 *
 * Example:
 *
 * ```
 * $braintree = new Braintree([
 *     'environment' => 'sandbox',
 *     'merchantId' => 'your_merchant_id',
 *     'publicKey' => 'your_public_key',
 *     'privateKey' => 'your_private_key'
 * ]);
 * ```
 *
 * @see https://articles.braintreepayments.com/get-started/try-it-out
 * @see https://www.braintreepayments.com/sandbox
 * @see https://github.com/braintree/braintree_php
 */
class Braintree
{
    protected Gateway $gateway;

    public function __construct(array $config)
    {
        $this->gateway = new Gateway($config);
    }

    /**
     * Create a Braintree customer for the given attributes.
     *
     * @param string $paymentMethodNonce
     * @param array $options
     * @return array created customer data.
     * @throws \RuntimeException
     */
    public function createCustomer(string $paymentMethodNonce, array $options = []): array
    {
        $response = $this->gateway->customer()->create(
            array_replace_recursive([
                //'firstName' => '',
                //'lastName' => '',
                //'email' => '',
                'paymentMethodNonce' => $paymentMethodNonce,
                'creditCard' => [
                    'options' => [
                        'verifyCard' => true,
                    ],
                ],
            ], $options)
        );

        if (! $response->success) {
            throw new \RuntimeException('Unable to create Braintree customer: ' . $response->getMessage());
        }

        $paymentMethod = $response->customer->defaultPaymentMethod();

        $isPaypalAccount = $paymentMethod instanceof PaypalAccount;

        return [
            'customer_id' => $response->customer->id,
            'paypal_email' => $isPaypalAccount ? $paymentMethod->email : null,
            'card_brand' => $isPaypalAccount ? null : $paymentMethod->cardType,
            'card_last_four' => $isPaypalAccount ? null : $paymentMethod->last4,
        ];
    }

    /**
     * Make a "one off" charge on the customer for the given amount.
     *
     * @see https://developer.paypal.com/braintree/docs/reference/request/transaction/sale/php
     *
     * @param int $customerId
     * @param int $amount
     * @param array $options
     * @return array transaction data.
     * @throws \RuntimeException
     */
    public function charge($customerId, $amount, array $options = []): array
    {
        $paymentMethod = $this->gateway->customer()->find($customerId)->defaultPaymentMethod();

        $response = $this->gateway->transaction()->sale(array_merge([
            'amount' => number_format($amount, 2, '.', ''),
            'paymentMethodToken' => $paymentMethod->token,
            'options' => [
                'submitForSettlement' => true,
            ],
        ], $options));

        if (! $response->success) {
            throw new \RuntimeException('Braintree was unable to perform a charge: ' . $response->getMessage());
        }

        return $response->transaction->jsonSerialize();
    }
}
