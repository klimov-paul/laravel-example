<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Cashier\Billable;
use App\Enums\PaymentType;
use App\Enums\PaymentStatus;
use App\Enums\CreditCardStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CreditCard represents particular user's credit card linked via external payment gateway.
 *
 * @property int $id
 * @property int $user_id
 * @property int $status
 * @property string $braintree_id
 * @property string $paypal_email
 * @property string $card_brand
 * @property string $card_last_four
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @property-read \App\Models\User $user
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Payment[] $payments
 *
 * @method static \Illuminate\Database\Eloquent\Builder|static query()
 */
class CreditCard extends Model
{
    use SoftDeletes;
    use Billable;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'braintree_id',
        'paypal_email',
        'card_brand',
        'card_last_four',
        'status',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|\App\Models\User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|\App\Models\Payment
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function deactivate(): bool
    {
        return $this->update(['status' => CreditCardStatus::INACTIVE]);
    }

    public function createForUser(User $user, string $creditCardToken): self
    {
        if ($this->exists) {
            throw new \LogicException('Unable to save already existing credit card.');
        }

        $this->status = CreditCardStatus::ACTIVE;

        $this->createAsBraintreeCustomer($creditCardToken);

        static::query()
            ->where('user_id', $user->id)
            ->whereKeyNot($this->id)
            ->where('status', CreditCardStatus::ACTIVE)
            ->update(['status' => CreditCardStatus::INACTIVE]);

        $user->unsetRelation('activeCreditCard');

        return $this;
    }

    /**
     * Performs charge over this credit card, creating a `Payment` from results.
     * @see https://developers.braintreepayments.com/reference/request/transaction/sale/php
     *
     * @param float $amount payment amount in major units, e.g. dollars
     * @param int $type payment type.
     * @param array $options additional transaction options.
     * @param array $attributes additional payment attributes.
     * @return \App\Models\Payment
     */
    public function pay($amount, $type, array $options = [], array $attributes = []): Payment
    {
        $options['customer']['email'] = $this->user->email;

        if (empty($options['lineItems'])) {
            $options['lineItems'][] = [
                'name' => PaymentType::getDescription($type),
                'unitAmount' => $amount,
                'totalAmount' => $amount,
            ];
        }

        foreach ($options['lineItems'] as &$lineItem) {
            $lineItem = array_merge([
                'kind' => $type === PaymentType::REFUND ? 'credit' : 'debit',
                'quantity' => 1,
            ], $lineItem);

            if (! isset($lineItem['totalAmount'])) {
                $lineItem['totalAmount'] = $lineItem['unitAmount'] * $lineItem['quantity'];
            }
        }

        try {
            $paymentResult = $this->charge($amount, $options);
        } catch (Exception $e) {
            return $this->payments()->create(array_merge([
                'type' => $type,
                'status' => PaymentStatus::FAILED,
                'amount' => $amount,
                'data' => ['error' => $e->getMessage()],
            ], $attributes));
        }

        return $this->payments()->create(array_merge([
            'type' => $type,
            'status' => PaymentStatus::SUCCESS,
            'amount' => $amount,
            'details' => $paymentResult->transaction,
        ], $attributes));
    }
}
