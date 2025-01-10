<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Services\Payment\Braintree;
use Illuminate\Http\Request;

class BraintreeController extends Controller
{
    public function __construct()
    {
        $this->middleware('throttle:60,1');
        $this->middleware('auth:web');
    }

    public function generateClientToken(Braintree $braintree, Request $request)
    {
        return [
            'data' => [
                'token' => $braintree->generateClientToken(PaymentMethod::findLatestCustomerId($request->user()->id)),
            ],
        ];
    }
}
