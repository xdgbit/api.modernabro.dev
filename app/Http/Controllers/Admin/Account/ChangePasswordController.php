<?php

namespace App\Http\Controllers\Admin\Account;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Account\ChangePasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ChangePasswordController extends Controller
{
    public function change_password(ChangePasswordRequest $request)
    {
        $admin = auth('user_expert')->user();

        if (!$admin || !$admin->is_super_admin) {
            return ApiResponse::error(
                'شما اجازه انجام این عملیات را ندارید.',
                null,
                403
            );
        }

        $validator = \Illuminate\Support\Facades\Validator::make(
            $request->all(),
            [
                'current_password' => ['required', 'string'],
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                    'different:current_password',
                ],
            ]
        );

        if ($validator->fails()) {
            return ApiResponse::error(
                'اطلاعات وارد شده معتبر نیست.',
                $validator->errors(),
                422
            );
        }

        $validated = $validator->validated();

        if (!Hash::check($validated['current_password'], $admin->password)) {
            return ApiResponse::error(
                'رمز عبور فعلی صحیح نیست.',
                [
                    'current_password' => ['رمز عبور فعلی صحیح نیست.'],
                ],
                422
            );
        }

        $admin->password = Hash::make($validated['password']);
        $admin->save();

        return ApiResponse::success(
            null,
            'رمز عبور با موفقیت تغییر کرد.'
        );
    }
}