<?php

namespace App\Http\Controllers\Admin;

use App\Models\SystemConfig;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;

class SettingController extends Controller
{
    public function update(Request $request)
    {
        $validated = $request->validate([
            'site_name'       => ['nullable', 'string', 'max:255'],
            'phone'           => ['required', 'string', 'max:50'],
            'phone_secondary' => ['nullable', 'string', 'max:50'],
            'email'           => ['nullable', 'email', 'max:255'],
            'address'         => ['nullable', 'string', 'max:1000'],
            'footer_text'     => ['nullable', 'string', 'max:1000'],
        ]);

        $systemConfig = \DB::transaction(function () use ($validated) {

            // حذف تمام تنظیمات قبلی
            SystemConfig::query()->delete();

            // ایجاد تنظیمات جدید
            return SystemConfig::query()->create($validated);
        });

        return ApiResponse::success(
            $systemConfig,
            'تنظیمات با موفقیت ذخیره شد.'
        );
    }

    public function get_data(): \Illuminate\Http\JsonResponse
    {
        $system_config = SystemConfig::query()->first();

        return ApiResponse::success($system_config);
    }
}
