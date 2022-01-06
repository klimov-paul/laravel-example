<?php

namespace App\Models;

use App\Enums\CreditCardStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\UserStatus;
use App\Notifications\UserSignedUp;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

/**
 * User represents system customer.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property int $status
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property \Illuminate\Database\Eloquent\Collection|\Laravel\Sanctum\PersonalAccessToken[] $tokens
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\CreditCard[] $creditCards
 * @property \App\Models\CreditCard|null $activeCreditCard
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Subscription[] $subscriptions
 * @property \App\Models\Subscription|null $activeSubscription
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Favorite[] $favorites
 *
 * @method static \Illuminate\Database\Eloquent\Builder|static query()
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use Notifiable;
    use SoftDeletes;
    use MustVerifyEmail;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
    ];

    /**
     * {@inheritdoc}
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|\App\Models\Favorite
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|\App\Models\CreditCard
     */
    public function creditCards(): HasMany
    {
        return $this->hasMany(CreditCard::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne|\App\Models\CreditCard
     */
    public function activeCreditCard(): HasOne
    {
        return $this->hasOne(CreditCard::class)->where('status', CreditCardStatus::ACTIVE);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|\App\Models\Subscription
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne|\App\Models\Subscription
     */
    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->where('status', SubscriptionStatus::ACTIVE);
    }

    /**
     * Registers new user account.
     * New account remains 'pending' waiting for identity confirmation, keeping login forbidden.
     * Sends email verification notification.
     * @see confirmIdentity()
     *
     * @param  array  $attributes
     * @return static self reference.
     */
    public function signup(array $attributes): self
    {
        if ($this->exists) {
            throw new \LogicException('Unable to signup already existing account.');
        }

        if (isset($attributes['password'])) {
            $password = $attributes['password'];
        } else {
            $password = Str::random(8);
            $attributes['password'] = $password;
        }

        $this->fill($attributes);
        $this->password = bcrypt($password);
        $this->status = UserStatus::PENDING;
        $this->save();

        $this->notify(new UserSignedUp($password));

        return $this;
    }

    /**
     * Marks merchant as the one with confirmed identity (e.g. being a live person), allowing log in the system.
     * This method sends merchant's projects to moderation.
     * @see signup()
     *
     * @return static self reference.
     */
    public function confirmIdentity(): self
    {
        if ($this->status !== UserStatus::PENDING) {
            throw new \LogicException('Identity confirmation can be performed only for pending accounts.');
        }

        $this->status = UserStatus::ACTIVE;
        $this->save();

        return $this;
    }
}
