<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestOrDeveloper\TestController;

/*______________________________________________________________________________________*/
// Test Routes || Developer Routes

Route::group(['prefix' => 'test'], function () {
    Route::get('/vue', [TestController::class, 'testVue'])->name('test.testVue');
    Route::get('/list', [TestController::class, 'list'])->name('test.list');
    Route::get('/common', [TestController::class, 'common'])->name('test.common');
    Route::post('/validation', function (Request $request) {

        $errors = [];

        if (!$request->username)
        {
            $errors['username'][] = 'نام کاربری الزامی است.';
        }

        if ($request->username === 'admin')
        {
            $errors['username'][] = 'این نام کاربری قبلاً استفاده شده است.';
        }

        if (!$request->password)
        {
            $errors['password'][] = 'رمز عبور الزامی است.';
        }

        if ($request->password && strlen($request->password) < 8)
        {
            $errors['password'][] = 'رمز عبور باید حداقل ۸ کاراکتر باشد.';
        }

        if ($errors)
        {
            return response()->json([
                'message' => 'اطلاعات وارد شده صحیح نیست.',
                'errors'  => $errors,
            ], 422);
        }

        return response()->json([
            'message' => 'اطلاعات با موفقیت ثبت شد.',
        ]);

    })->name('test.validation');

});

Route::group(['prefix' => 'dev'], function () {

});