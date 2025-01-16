<?php

namespace App\Services\Payment;

use Braintree\Gateway;
use Braintree\PayPalAccount;

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
 * @see https://developer.paypal.com/braintree/docs/start/hello-server/php
 */
class Braintree
{
    protected Gateway $gateway;

    public function __construct(array $config)
    {
        $this->gateway = new Gateway($config);
    }

    /**
     * Generates new client token, which should be passed to API client (e.g. JavaScript).
     *
     * @see https://developer.paypal.com/braintree/docs/start/hello-client/javascript/v3/
     *
     * @param int|null $customerId
     * @return string client token.
     */
    public function generateClientToken($customerId = null): string
    {
        return $this->gateway->clientToken()->generate([
            'customerId' => $customerId,
        ]);
    }

    /**
     * Create a Braintree customer with payment method from nonce received from client-side SDK.
     *
     * @see https://developer.paypal.com/braintree/docs/reference/request/customer/create/php
     *
     * @param string $paymentMethodNonce nonce received from client-side SDK.
     * @param array $options
     * @return array created payment method data.
     * @throws \RuntimeException
     */
    public function createCustomerWithPaymentMethod(string $paymentMethodNonce, array $options = []): array
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

        if (!$response->success) {
            throw new \RuntimeException('Unable to create Braintree customer: ' . $response->message);
        }

        return $this->formatPaymentMethod($response->customer->defaultPaymentMethod());
    }

    /**
     * Creates new payment method for existing customer.
     *
     * @see https://developer.paypal.com/braintree/docs/reference/request/payment-method/create/php
     *
     * @param string $customerId Braintree customer ID.
     * @param string $paymentMethodNonce nonce received from client-side SDK.
     * @param array $options
     * @return array created payment method data.
     */
    public function createPaymentMethod(string $customerId, string $paymentMethodNonce, array $options = []): array
    {
        $response = $this->gateway->paymentMethod()->create(
            array_replace_recursive([
                'customerId' => $customerId,
                'paymentMethodNonce' => $paymentMethodNonce,
                'options' => [
                    'verifyCard' => true,
                ],
            ], $options)
        );

        if (!$response->success) {
            throw new \RuntimeException('Unable to create Braintree payment method: ' . $response->message);
        }

        return $this->formatPaymentMethod($response->paymentMethod);
    }

    protected function formatPaymentMethod(object $paymentMethod): array
    {
        $isPaypalAccount = $paymentMethod instanceof PaypalAccount;

        return [
            'customer_id' => $paymentMethod->customerId,
            'token' => $paymentMethod->token,
            'paypal_email' => $isPaypalAccount ? $paymentMethod->email : null,
            'card_brand' => $isPaypalAccount ? null : $paymentMethod->cardType,
            'card_last_four' => $isPaypalAccount ? null : $paymentMethod->last4,
            'card_expiration_month' => $isPaypalAccount ? null : $paymentMethod->expirationMonth,
            'card_expiration_year' => $isPaypalAccount ? null : $paymentMethod->expirationYear,
        ];
    }

    /**
     * Make a "one off" charge on the customer for the given amount.
     *
     * @see https://developer.paypal.com/braintree/docs/reference/request/transaction/sale/php
     *
     * @param string $paymentMethodToken
     * @param int|float $amount amount in major units.
     * @param array $options
     * @return array transaction data.
     * @throws \RuntimeException
     */
    public function sale(string $paymentMethodToken, int|float $amount, array $options = []): array
    {
        $paymentMethod = $this->gateway->paymentMethod()->find($paymentMethodToken);

        $response = $this->gateway->transaction()->sale(array_merge([
            'amount' => number_format($amount, 2, '.', ''),
            'paymentMethodToken' => $paymentMethod->token,
            'options' => [
                'submitForSettlement' => true,
            ],
        ], $options));

        if (!$response->success) {
            $errorMessage = $response->message;
            $errorCode = 0;

            if (!empty($response->transaction->status)) {
                $errorMessage .= ': ' . $response->transaction->status;
            }
            if (!empty($response->transaction->processorResponseCode)) {
                $errorMessage .= ' #' . $response->transaction->processorResponseCode;
                $errorCode = (int) $response->transaction->processorResponseCode;
            }
            if (!empty($response->transaction->processorResponseText)) {
                $errorMessage .= ' (' . $response->transaction->processorResponseText . ')';
            }

            throw new \RuntimeException('Braintree was unable to perform a sale: ' . $errorMessage, $errorCode);
        }

        return $response->transaction->jsonSerialize();
    }
}
