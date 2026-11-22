<?php

use App\Http\Controllers\Admin\Account\ChangePasswordController;
use App\Http\Controllers\Admin\Article\ArticleController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\Product\CategoryController;
use App\Http\Controllers\Admin\Product\ImageController;
use App\Http\Controllers\Admin\Product\ProductController;
use App\Http\Controllers\Admin\Product\ReviewController;
use App\Http\Controllers\Admin\Product\SeoController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\User\Customer\AddressController;
use App\Http\Controllers\Admin\User\Customer\CustomerController;
use App\Http\Controllers\Admin\User\ExpertController;
use App\Http\Controllers\TestOrDeveloper\TestController;
use App\Http\Middleware\Expert\JustSuperAdmin;
use Illuminate\Support\Facades\Route;

Route::post('/test', [TestController::class, 'list'])->name('admin.test.list');

Route::group(['prefix' => 'dashboard'], function () {
    Route::get('/get_data', [DashboardController::class, 'get_data'])->name('admin.dashboard.get_data');
    Route::post('/get_badge_data', [DashboardController::class, 'get_badge_data'])->name('admin.dashboard.get_badge_data');
});
Route::group(['prefix' => 'account'], function () {
    Route::post('/change_password', [ChangePasswordController::class, 'change_password'])->name('admin.account.change_password');
});
Route::middleware(['auth:user_expert', 'permission:users.create'])
    ->get('/expert/test-permission', function () {
        return 'Permission Access Granted';
    });
Route::group(['prefix' => 'product'], function () {
    Route::get('/stats', [ProductController::class, 'stats']);
    Route::post('/jspanel', [ProductController::class, 'jspanel']);
    Route::get('/datatable', [ProductController::class, 'datatable']);
    Route::patch('/update/{product}', [ProductController::class, 'update']);
    Route::post('/store', [ProductController::class, 'store']);
    Route::group(['prefix' => 'seo'], function () {
        Route::get('/show/{product}', [SeoController::class, 'show']);
        Route::put('/update/{product}', [SeoController::class, 'update']);
    });
    Route::group(['prefix' => 'images'], function () {
        Route::get('/list/{product}', [ImageController::class, 'list']);
        Route::put('/order/{product}', [ImageController::class, 'order']);
        Route::post('/upload/{product}', [ImageController::class, 'upload']);
        Route::put('/cover/{product}/{productFile}', [ImageController::class, 'cover']);
        Route::delete('/delete/{product}/{productFile}', [ImageController::class, 'delete']);
    });
    Route::group(['prefix' => 'reviews'], function () {
        Route::get('/stats', [ReviewController::class, 'stats']);
        Route::post('/reject/{id}', [ReviewController::class, 'reject']);
        Route::get('/datatable/', [ReviewController::class, 'datatable']);
        Route::post('/approve/{id}', [ReviewController::class, 'approve']);
    });
    Route::group(['prefix' => 'categories'], function () {
        Route::get('/stats', [CategoryController::class, 'stats']);
        Route::post('/store', [CategoryController::class, 'store']);
        Route::post('/jspanel', [CategoryController::class, 'jspanel']);
        Route::get('/datatable', [CategoryController::class, 'datatable']);
        Route::patch('/update/{productCategory}', [CategoryController::class, 'update']);
        Route::delete('/destroy/{productCategory}', [CategoryController::class, 'delete']);
    });
});
Route::group(['prefix' => 'orders'], function () {
    Route::get('/stats', [OrderController::class, 'stats']);
    Route::post('/store', [OrderController::class, 'store']);
    Route::post('/jspanel', [OrderController::class, 'jspanel']);
    Route::get('/datatable', [OrderController::class, 'datatable']);
    Route::patch('/update/{order}', [OrderController::class, 'update']);
    Route::post('/statusPanel', [OrderController::class, 'statusPanel']);
    Route::delete('/destroy/{order}', [OrderController::class, 'delete']);
    Route::patch('/changeStatus/{order}', [OrderController::class, 'changeStatus']);
});
Route::prefix('locations')->group(function () {
    Route::get('provinces', [LocationController::class, 'provinces'])->name('provinces');
    Route::get('cities/{province}', [LocationController::class, 'cities'])->name('cities');
});
Route::group(['prefix' => 'payment-methods'], function () {
    Route::post('/store', [PaymentMethodController::class, 'store'])->name('admin.payment-methods.store');
    Route::get('/get_data', [PaymentMethodController::class, 'get_data'])->name('admin.payment-methods.get_data');
});
Route::group(['prefix' => 'article'], function () {
    Route::post('/store', [ArticleController::class, 'store'])->name('admin.article.store');
    Route::get('/show/{article}', [ArticleController::class, 'show'])->name('admin.article.show');
    Route::get('/get_data', [ArticleController::class, 'get_data'])->name('admin.article.get_data');
    Route::get('/datatable', [ArticleController::class, 'datatable'])->name('admin.article.datatable');
    Route::patch('/update/{article}', [ArticleController::class, 'update'])->name('admin.article.update');
    Route::delete('/delete/{article}', [ArticleController::class, 'delete'])->name('admin.article.delete');
    Route::group(['prefix' => 'category'], function () {
        Route::get('/list', [CategoryController::class, 'list'])->name('admin.article.category.list');
        Route::post('/store', [CategoryController::class, 'store'])->name('admin.article.category.store');
        Route::get('/datatable', [CategoryController::class, 'datatable'])->name('admin.article.category.datatable');
        Route::get('/show/{articleCategory}', [CategoryController::class, 'show'])->name('admin.article.category.show');
        Route::patch('/update/{articleCategory}', [CategoryController::class, 'update'])->name('admin.article.category.update');
        Route::delete('/delete/{articleCategory}', [CategoryController::class, 'delete'])->name('admin.article.category.delete');
    });
});
Route::group(['prefix' => 'customer'], function () {
    Route::post('/store', [CustomerController::class, 'store'])->name('admin.customer.store');
    Route::get('/show/{customer}', [CustomerController::class, 'show'])->name('admin.customer.show');
    Route::get('/get_data', [CustomerController::class, 'get_data'])->name('admin.customer.get_data');
    Route::get('/datatable', [CustomerController::class, 'datatable'])->name('admin.customer.datatable');
    Route::patch('/update/{customer}', [CustomerController::class, 'update'])->name('admin.customer.update');
    Route::group(['prefix' => 'address'], function () {
        Route::get('/{customer}', [AddressController::class, 'index']);
        Route::post('/store/{customer}', [AddressController::class, 'store']);
        Route::patch('/update/{customer}/{address}', [AddressController::class, 'update']);
        Route::delete('/destroy/{customer}/{address}', [AddressController::class, 'destroy']);
        Route::patch('/set_default/{customer}/{address}', [AddressController::class, 'set_default']);
    });
});

Route::middleware(JustSuperAdmin::class)
    ->prefix('expert')
    ->group(function () {

        Route::post('/store', [ExpertController::class, 'store'])
            ->name('admin.expert.store');

        Route::get('/show/{expert}', [ExpertController::class, 'show'])
            ->name('admin.expert.show');

        Route::get('/get_data', [ExpertController::class, 'get_data'])
            ->name('admin.expert.get_data');

        Route::get('/datatable', [ExpertController::class, 'datatable'])
            ->name('admin.expert.datatable');

        Route::patch('/update/{expert}', [ExpertController::class, 'update'])
            ->name('admin.expert.update');
        Route::get('/roles', [ExpertController::class, 'roles'])
            ->name('admin.expert.roles');
    });
Route::group(['prefix' => 'settings'], function () {
    Route::get('/get_data', [SettingController::class, 'get_data'])->name('admin.settings.get_data');
    Route::put('/update', [SettingController::class, 'update'])->name('admin.settings.update');
});
Route::group(['prefix' => 'contact-message'], function () {
    Route::get('/get_data', [ContactMessageController::class, 'get_data'])->name('admin.contact-message.get_data');
    Route::get('/datatable', [ContactMessageController::class, 'datatable'])->name('admin.contact-message.datatable');
    Route::post('/jspanel', [ContactMessageController::class, 'jspanel'])->name('admin.contact-message.jspanel');
    Route::post('/toggle_read/{contactMessage}', [ContactMessageController::class, 'toggleRead'])
        ->middleware(JustSuperAdmin::class)
        ->name('admin.contact-message.toggle_read');
    Route::delete('/delete/{contactMessage}', [ContactMessageController::class, 'destroy'])
        ->middleware(JustSuperAdmin::class)
        ->name('admin.contact-message.delete');
});
Route::view('/{any?}', 'admin.app')
    ->where('any', '.*')->name('admin.index');