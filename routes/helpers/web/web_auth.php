<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\Admin\LoginController as AdminLoginController;
use App\Http\Controllers\Auth\Customer\LoginController as CustomerLoginController;

use App\Http\Controllers\Auth\Admin\LogoutController as AdminLogoutController;
use App\Http\Controllers\Auth\Customer\LogoutController as CustomerLogoutController;

Route::group(['prefix' => 'admin', 'namespace' => 'Admin'], function () {
    Route::post('/logout', [AdminLogoutController::class, 'submit'])->name('admin.logout.submit');

    Route::group(['prefix' => 'login', 'middleware' => ['guest:user_expert']], function () {
        Route::get('/', [AdminLoginController::class, 'index'])->name('admin.login.index');
        Route::post('/attempt', [AdminLoginController::class, 'attempt'])->name('admin.login.attempt');
    });
});
