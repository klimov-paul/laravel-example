<?php

namespace App\Http\Controllers;

class BookController extends Controller
{
    public function index()
    {
        return view('books.index');
    }
}
