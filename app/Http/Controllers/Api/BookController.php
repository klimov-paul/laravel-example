<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Models\Book;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::query()
            ->orderBy('id', 'desc')
            ->paginate();

        return BookResource::collection($books);
    }

    public function show(Book $book)
    {
        return new BookResource($book);
    }
}
