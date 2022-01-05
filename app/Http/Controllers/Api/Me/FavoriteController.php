<?php

namespace App\Http\Controllers\Api\Me;

use App\Http\Controllers\Controller;
use App\Http\Resources\FavoriteResource;
use App\Models\Book;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    protected function user(): User
    {
        return Auth::guard('web')->user();
    }

    public function index()
    {
        $favorites = $this->user()
            ->favorites()
            ->with('book')
            ->orderBy('id', 'desc')
            ->paginate();

        return FavoriteResource::collection($favorites);
    }

    public function store(Request $request)
    {
        $data = $this->validate($request, [
            'book_id' => ['required', 'int', 'exists:books,id'],
        ]);

        $book = Book::query()->findOrFail($data['book_id']);

        $favorite = $book->favoriteBy($this->user());

        return new FavoriteResource($favorite);
    }

    public function show(Favorite $favorite)
    {
        if ($favorite->user_id !== $this->user()->id) {
            abort(404);
        }

        return new FavoriteResource($favorite);
    }

    public function destroy(Favorite $favorite)
    {
        if ($favorite->user_id !== $this->user()->id) {
            abort(404);
        }

        $favorite->delete();

        return new FavoriteResource($favorite);
    }
}
