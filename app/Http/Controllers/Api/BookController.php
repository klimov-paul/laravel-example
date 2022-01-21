<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminatech\DataProvider\DataProvider;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $books = (new DataProvider(Book::query()))
            ->filters([
                'id',
                'search' => [
                    'title',
                    'description',
                    'author',
                ],
            ])
            ->sort([
                'id',
                'title',
                'price',
            ])
            ->paginate($request);

        return BookResource::collection($books);
    }

    public function show(Book $book)
    {
        return new BookResource($book);
    }
}
