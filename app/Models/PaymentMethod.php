<?php

namespace App\Models;

use App\Services\Payment\Braintree;
use Exception;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;
use App\Enums\PaymentType;
use App\Enums\PaymentMethodStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PaymentMethod represents particular user's persistent payment method e.g. "credit card", "PayPal account" etc.
 * linked via external payment gateway.
 *
 * @property int $id
 * @property int $user_id
 * @property int $status
 * @property string $customer_id
 * @property string $token
 * @property string|null $paypal_email
 * @property string|null $card_brand
 * @property string|null $card_last_four
 * @property int|null $card_expiration_month
 * @property int|null $card_expiration_year
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @property-read \App\Models\User $user
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Payment[] $payments
 *
 * @method static \Illuminate\Database\Eloquent\Builder|static query()
 */
class PaymentMethod extends Model
{
    use SoftDeletes;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'external_id',
        'paypal_email',
        'card_brand',
        'card_last_four',
        'card_expiration_month',
        'card_expiration_year',
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
        return $this->update(['status' => PaymentMethodStatus::INACTIVE]);
    }

    /**
     * @param int $userId user ID.
     * @return string|null Braintree customer ID, `null` if not exist.
     */
    public static function findLatestCustomerId(int $userId): ?string
    {
        $previousPaymentMethod = static::query()
            ->where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->take(1)
            ->first();

        if (empty($previousPaymentMethod)) {
            return null;
        }

        return $previousPaymentMethod->customer_id;
    }

    public function createForUser(User $user, string $paymentMethodNonce): self
    {
        if ($this->exists) {
            throw new \LogicException('Unable to save already existing credit card.');
        }

        $this->status = PaymentMethodStatus::ACTIVE;

        $this->user()->associate($user);

        $customerId = static::findLatestCustomerId($user->id);

        if (empty($customerId)) {
            $this->createAsPaymentGatewayCustomer($paymentMethodNonce);
        } else {
            $this->createAsPaymentGatewayMethod($customerId, $paymentMethodNonce);
        }

        static::query()
            ->where('user_id', $user->id)
            ->whereKeyNot($this->id)
            ->where('status', PaymentMethodStatus::ACTIVE)
            ->update(['status' => PaymentMethodStatus::INACTIVE]);

        $user->unsetRelation('activeCreditCard');

        return $this;
    }

    protected function createAsPaymentGatewayCustomer(string $paymentMethodNonce): self
    {
        $nameParts = explode(' ', $this->user->name);

        $paymentMethod = $this->paymentGateway()->createCustomerWithPaymentMethod($paymentMethodNonce, [
            'firstName' => $nameParts[0] ?? null,
            'lastName' => $nameParts[1] ?? null,
            'email' => $this->user->email,
        ]);

        return $this->createFromPaymentMethodData($paymentMethod);
    }

    protected function createAsPaymentGatewayMethod(string $customerId, string $paymentMethodNonce): self
    {
        $paymentMethod = $this->paymentGateway()->createPaymentMethod($customerId, $paymentMethodNonce);

        return $this->createFromPaymentMethodData($paymentMethod);
    }

    protected function createFromPaymentMethodData(array $paymentMethodData): self
    {
        $this->customer_id = $paymentMethodData['customer_id'];
        $this->token = $paymentMethodData['token'];
        $this->paypal_email = $paymentMethodData['paypal_email'];
        $this->card_brand = $paymentMethodData['card_brand'];
        $this->card_last_four = $paymentMethodData['card_last_four'];
        $this->card_expiration_month = $paymentMethodData['card_expiration_month'];
        $this->card_expiration_year = $paymentMethodData['card_expiration_year'];
        $this->save();

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

    protected function charge($amount, array $options): array
    {
        return $this->paymentGateway()->charge($this->token, $amount, $options);
    }

    protected function paymentGateway(): Braintree
    {
        return App::get(Braintree::class);
    }
}
