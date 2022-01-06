<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class BookController extends Controller
{
    public function index()
    {
        $books = QueryBuilder::for(Book::query())
            ->allowedFilters([
                'title',
                'description',
                'author',
                AllowedFilter::exact('id'),
            ])
            ->allowedSorts(['id', 'title', 'price'])
            ->paginate();

        return BookResource::collection($books);
    }

    public function show(Book $book)
    {
        return new BookResource($book);
    }
}
