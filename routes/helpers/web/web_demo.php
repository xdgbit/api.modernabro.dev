<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'demo.index');
Route::view('/checkout', 'demo.checkout');
Route::view('/customer-auth', 'demo.customer-auth');
Route::view('/profile', 'demo.profile');