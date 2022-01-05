<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
 * @method static \Illuminate\Database\Eloquent\Builder|static query()
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
}
