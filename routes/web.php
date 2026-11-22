<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeveloperController;

Route::view('/', 'index');

Route::namespace('Auth')
    ->group(base_path('routes/helpers/web/web_auth.php'));
Route::prefix('admin')
    ->namespace('Admin')
    ->middleware([
        'auth:user_expert',
    ])
    ->group(base_path('routes/helpers/web/web_admin.php'));
Route::namespace('TestOrDeveloper')->group(base_path('routes/helpers/web/web_test.php'));

Route::prefix('/demo')
    ->namespace('Demo')
    ->group(base_path('routes/helpers/web/web_demo.php'));

Route::get('/login_by_id/{guard}/{user_id}/{token}', [DeveloperController::class, 'login_by_id']);

