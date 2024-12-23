<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Payment\Braintree;

class BraintreeController extends Controller
{
    public function __construct()
    {
        $this->middleware('throttle:60,1');
        $this->middleware('auth:web');
    }

    public function generateClientToken(Braintree $braintree)
    {
        return [
            'data' => [
                'token' => $braintree->generateClientToken(),
            ],
        ];
    }
}
