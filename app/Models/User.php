<?php

namespace App\Models;

use App\Enums\UserStatus;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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
