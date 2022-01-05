<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Favorite allows particular user to create his own books wish list for the future rent.
 *
 * @property int $id
 * @property int $user_id
 * @property int $book_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\User $user
 * @property-read \App\Models\Book $book
 *
 * @method static \Illuminate\Database\Eloquent\Builder|static query()
 */
class Favorite extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'user_id',
        'book_id',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|\App\Models\User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|\App\Models\Book
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
    }
}
