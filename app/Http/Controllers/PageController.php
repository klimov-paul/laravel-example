<?php

namespace App\Http\Controllers;

class PageController extends Controller
{
    public function home()
    {
        return view('pages/home');
    }

    public function faq()
    {
        return view('pages/faq');
    }

    public function contact()
    {
        return view('pages/contact');
    }
}
