<?php

namespace Tests\Feature;

use Database\Factories\BookFactory;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\Api\BookController
 */
class BooksTest extends TestCase
{
    public function testIndex()
    {
        $books = BookFactory::new()->count(2)->create();

        $this->getJson(route('api.books.index'))
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'title',
                        'description',
                    ],
                ],
            ])
            ->assertJson([
                'data' => [
                    [
                        'id' => $books[1]->id,
                    ],
                    [
                        'id' => $books[0]->id,
                    ],
                ],
            ]);
    }

    public function testShow()
    {
        $book = BookFactory::new()->create();

        $this->getJson(route('api.books.show', [$book]))
            ->assertSuccessful()
            ->assertJson([
                'data' => [
                    'id' => $book->id,
                    'title' => $book->title,
                    'description' => $book->description,
                    'author' => $book->author,
                    'price' => $book->price,
                ],
            ]);
    }
}
