<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Book is a particulate book description (e.g. class of books - not an instance).
 *
 * @property int $id
 * @property string $isbn
 * @property string $title
 * @property string $description
 * @property string $author
 * @property float $price
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Category[] $categories
 *
 * @method static \Illuminate\Database\Eloquent\Builder|static query()
 */
class Book extends Model
{
    use SoftDeletes;

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'isbn',
        'title',
        'description',
        'author',
        'price',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany|\App\Models\Category
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'book_has_category', 'book_id', 'category_id', 'id');
    }

    public function favoriteBy(User $user): Favorite
    {
        return Favorite::query()->firstOrCreate([
            'book_id' => $this->id,
            'user_id' => $user->id,
        ]);
    }

    public function unfavoriteBy(User $user): void
    {
        Favorite::query()
            ->where('book_id', $this->id)
            ->where('user_id', $user->id)
            ->delete();
    }
}
