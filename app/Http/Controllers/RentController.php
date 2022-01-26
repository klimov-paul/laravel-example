<?php

namespace App\Http\Controllers;

class RentController extends Controller
{
    public function index()
    {
        return view('rents.index');
    }
}
