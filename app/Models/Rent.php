<?php

namespace App\Models;

use App\Enums\RentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Rent logs the particular book rent by user.
 *
 * @property int $id
 * @property int $user_id
 * @property int $book_id
 * @property int $status
 * @property \Illuminate\Support\Carbon|string $begin_at
 * @property \Illuminate\Support\Carbon|string|null $due_at
 * @property \Illuminate\Support\Carbon|string $end_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\User $user
 * @property-read \App\Models\Book $book
 *
 * @method static \Illuminate\Database\Eloquent\Builder|static query()
 * @method static \Illuminate\Database\Eloquent\Builder|static current()
 */
class Rent extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'user_id',
        'book_id',
        'status',
        'begin_at',
        'due_at',
        'end_at',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'begin_at' => 'datetime',
        'due_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    /**
     * {@inheritdoc}
     */
    protected static function booted()
    {
        parent::booted();

        static::creating(function (self $model) {
            if (empty($model->begin_at)) {
                $model->begin_at = Carbon::now();
            }

            if (empty($model->due_at)) {
                $model->due_at = Carbon::create($model->begin_at)->addDays(config('rent.due_days_period'));
            }
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|\App\Models\User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|\App\Models\Book
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function scopeCurrent(Builder $query): Builder
    {
        return $query->whereIn('status', [RentStatus::PENDING, RentStatus::ACTIVE, RentStatus::OVERDUE]);
    }
}
