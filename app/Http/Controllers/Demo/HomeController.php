<?php

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index()
    {
        return view('demo.index');
    }
}
