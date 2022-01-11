<?php

namespace App\Http\Controllers\Api\Me;

use App\Http\Controllers\Controller;
use App\Http\Resources\RentResource;
use App\Models\User;
use App\Rules\AllowBookRentRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RentController extends Controller
{
    public function index()
    {
        $rents = $this->user()
            ->rents()
            ->paginate();

        return RentResource::collection($rents);
    }

    public function store(Request $request)
    {
        $user = $this->user();

        $this->validate($request, [
            'book_id' => ['required', 'int', $allowRentRule = new AllowBookRentRule($user)],
        ]);

        $rent = $allowRentRule->getBook()->rent($user);

        return new RentResource($rent);
    }

    public function show(Rent $rent)
    {
        if ($rent->user_id !== $this->user()->id) {
            abort(404);
        }

        return new RentResource($rent);
    }

    protected function user(): User
    {
        return Auth::guard()->user();
    }
}
