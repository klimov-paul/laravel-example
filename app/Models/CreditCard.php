<?php

namespace App\Models;

use App\Services\Payment\Braintree;
use Exception;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;
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
 * @property string $external_id
 * @property string $owner_email
 * @property string $brand
 * @property string $last_four
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

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'external_id',
        'owner_email',
        'brand',
        'last_four',
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

        $this->user()->associate($user);

        $this->createAsPaymentGatewayCustomer($creditCardToken);

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
                'details' => json_encode(['error' => $e->getMessage()]),
            ], $attributes));
        }

        return $this->payments()->create(array_merge([
            'type' => $type,
            'status' => PaymentStatus::SUCCESS,
            'amount' => $amount,
            'details' => json_encode($paymentResult),
        ], $attributes));
    }

    protected function createAsPaymentGatewayCustomer(string $paymentMethodNonce): self
    {
        $nameParts = explode(' ', $this->user->name);

        $customerData = $this->paymentGateway()->createCustomer($paymentMethodNonce, [
            'firstName' => $nameParts[0] ?? null,
            'lastName' => $nameParts[1] ?? null,
            'email' => $this->user->email,
        ]);

        $this->external_id = $customerData['customer_id'];
        $this->owner_email = $customerData['paypal_email'];
        $this->brand = $customerData['card_brand'];
        $this->last_four = $customerData['card_last_four'];
        $this->save();

        return $this;
    }

    protected function charge($amount, array $options): array
    {
        return $this->paymentGateway()->charge($this->external_id, $amount, $options);
    }

    protected function paymentGateway(): Braintree
    {
        return App::get(Braintree::class);
    }
}
