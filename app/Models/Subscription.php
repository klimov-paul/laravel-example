<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Subscription represents particular user's subscription time period.
 *
 * @property int $id
 * @property int $user_id
 * @property int $subscription_plan_id
 * @property int $status
 * @property \Illuminate\Support\Carbon|string $begin_at
 * @property \Illuminate\Support\Carbon|string $end_at
 * @property bool $is_recurrent
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\User $user
 * @property-read \App\Models\SubscriptionPlan $subscriptionPlan
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Payment[] $payments
 *
 * @method static \Illuminate\Database\Eloquent\Builder|static query()
 * @method static \Illuminate\Database\Eloquent\Builder|static expired()
 */
class Subscription extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'user_id',
        'subscription_plan_id',
        'begin_at',
        'end_at',
        'status',
        'is_recurrent',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'begin_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|\App\Models\User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|\App\Models\SubscriptionPlan
     */
    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany|\App\Models\Payment
     */
    public function payments()
    {
        return $this->belongsToMany(Payment::class, 'payment_has_subscription');
    }

    public function scopeExpired(Builder $query)
    {
        return $query->where('end_at', '<', now());
    }
}
