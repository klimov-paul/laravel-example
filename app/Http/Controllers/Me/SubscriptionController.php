<?php

namespace App\Http\Controllers\Me;

use App\Http\Controllers\Controller;

class SubscriptionController extends Controller
{
    public function index()
    {
        return view('me.subscription.index');
    }

    public function purchase()
    {
        return view('me.subscription.purchase');
    }
}
