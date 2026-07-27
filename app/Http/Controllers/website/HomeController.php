<?php

namespace App\Http\Controllers\website;

use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index()
    {
        return view('website.pages.home');
    }
}
