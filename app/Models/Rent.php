<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Rent logs the particular book rent by user.
 *
 * @property int $id
 * @property int $user_id
 * @property int $book_id
 * @property int $status
 * @property \Illuminate\Support\Carbon|string $begin_at
 * @property \Illuminate\Support\Carbon|string $due_at
 * @property \Illuminate\Support\Carbon|string $end_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|static query()
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
}
