<?php

namespace App\Models;

use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * SubscriptionPlan defines the terms of the user service subscription.
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property float $price
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Subscription[] $subscriptions
 *
 * @method static \Illuminate\Database\Eloquent\Builder|static query()
 */
class SubscriptionPlan extends Model
{
    use SoftDeletes;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'name',
        'description',
        'price',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|\App\Models\Subscription
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function subscribe(User $user, int $monthCount = 1, array $attributes = []): Subscription
    {
        $beginAt = now();
        $endAt = now()->addMonths($monthCount);

        /* @var $subscription \App\Models\Subscription */
        $subscription = $user->subscriptions()->make(array_merge([
            'begin_at' => $beginAt,
            'end_at' => $endAt,
            'status' => SubscriptionStatus::ACTIVE,
            'is_recurrent' => true,
        ], $attributes));
        $subscription->subscriptionPlan()->associate($this);
        $subscription->save();

        $user->subscriptions()
            ->whereKeyNot($subscription->id)
            ->where('status', SubscriptionStatus::ACTIVE)
            ->update(['status' => SubscriptionStatus::ARCHIVED]);

        $user->subscriptions()
            ->whereKeyNot($subscription->id)
            ->where('status', SubscriptionStatus::PENDING)
            ->update(['status' => SubscriptionStatus::CANCELLED]);

        $user->unsetRelation('activeSubscription');

        return $subscription;
    }
}
