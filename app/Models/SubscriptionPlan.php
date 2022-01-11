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
 * @property float $max_book_price
 * @property int $max_rent_count
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Category[] $categories
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
        'max_book_price',
        'max_rent_count',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany|\App\Models\Category
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'subscription_plan_has_category', 'subscription_plan_id', 'category_id', 'id');
    }

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

    /**
     * Checks whether given book is allowed to be rented by this subscription plan or not.
     *
     * @param Book $book
     * @return bool
     */
    public function allowBook(Book $book): bool
    {
        if ($book->price > $this->max_book_price) {
            return false;
        }

        $allowedCategoryIds = $this->categories->pluck('id')->toArray();

        foreach ($book->categories as $category) {
            if (!in_array($category->id, $allowedCategoryIds, true)) {
                return false;
            }
        }

        return true;
    }

    public function countAvailableRentSlots(User $user): int
    {
        $currentRentCount = count($user->currentRents);

        return max(0, $this->max_rent_count - $currentRentCount);
    }

    public function hasAvailableRentSlots(User $user): bool
    {
        return $this->countAvailableRentSlots($user) > 0;
    }
}
