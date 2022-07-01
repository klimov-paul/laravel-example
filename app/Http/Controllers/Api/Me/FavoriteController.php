<?php

namespace App\Http\Controllers\Api\Me;

use App\Http\Controllers\Controller;
use App\Http\Resources\FavoriteResource;
use App\Models\Book;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminatech\DataProvider\DataProvider;
use Illuminatech\ModelRules\Exists;

class FavoriteController extends Controller
{
    protected function user(): User
    {
        return Auth::guard('web')->user();
    }

    public function index(Request $request)
    {
        $favorites = (new DataProvider(
            $this->user()
                ->favorites()
                ->with('book')
        ))
            ->filters([
                'id',
                'search' => [
                    'book.title',
                    'book.description',
                    'book.author',
                ],
            ])
            ->sort(['id', 'created_at'])
            ->paginate($request);

        return FavoriteResource::collection($favorites);
    }

    public function store(Request $request)
    {
        $data = $this->validate($request, [
            'book_id' => ['required', 'int', $bookRule = Exists::new(Book::class)],
        ]);

        /** @var Book $book */
        $book = $bookRule->getModel();

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
