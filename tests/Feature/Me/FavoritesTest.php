<?php

namespace Tests\Feature\Me;

use App\Models\User;
use Database\Factories\BookFactory;
use Database\Factories\UserFactory;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\Me\FavoriteController
 */
class FavoritesTest extends TestCase
{
    protected User $user;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = UserFactory::new()->create();
    }

    public function testIndex(): void
    {
        $this->actingAs($this->user);

        $books = BookFactory::new()->count(2)->create();

        foreach ($books as $book) {
            $book->favoriteBy($this->user);
        }

        $this->getJson(route('api.me.favorites.index', ['sort' => '-id']))
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'created_at',
                        'book' => [
                            'id',
                            'title',
                        ],
                    ],
                ],
            ])
            ->assertJsonCount(2, 'data')
            ->assertJson([
                'data' => [
                    [
                        'book' => [
                            'id' => $books[1]->id,
                        ],
                    ],
                    [
                        'book' => [
                            'id' => $books[0]->id,
                        ],
                    ],
                ],
            ]);
    }

    public function testStore(): void
    {
        $this->actingAs($this->user);

        $book = BookFactory::new()->create();

        $this->postJson(route('api.me.favorites.store'), ['book_id' => $book->id])
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'created_at',
                    'book' => [
                        'id',
                    ],
                ],
            ])
            ->assertJson([
                'data' => [
                    'book' => [
                        'id' => $book->id,
                    ],
                ],
            ]);

        $this->assertTrue($this->user->favorites()->where('book_id', $book->id)->exists());
    }
}
