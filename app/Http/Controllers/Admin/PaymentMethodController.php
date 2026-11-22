<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Sys\PaymentMethod\SysDirectPaymentConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    /**
     * تبدیل اعداد فارسی/عربی به انگلیسی
     */
    private function toEnglishDigits(?string $value): string
    {
        return strtr((string) $value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
    }

    /**
     * دریافت تنظیمات انتقال بانکی
     */
    public function get_data(): JsonResponse
    {
        $config = SysDirectPaymentConfig::first();

        return ApiResponse::success([
            'bank_name'      => $config->bank_name ?? '',
            'account_holder' => $config->account_holder ?? '',
            'card_number'    => $config->card_number ?? '',
            'account_number' => $config->account_number ?? '',
            'iban'           => $config->iban ?? '',
            'bank_methods'   => $config->bank_methods ?? [],
        ]);
    }

    /**
     * ذخیره تنظیمات انتقال بانکی (رکورد تکی)
     */
    public function save(Request $request): JsonResponse
    {
        // نرمال‌سازی ورودی‌ها قبل از اعتبارسنجی
        $request->merge([
            'card_number'    => preg_replace('/\D/', '', $this->toEnglishDigits($request->input('card_number'))),
            'account_number' => preg_replace('/\D/', '', $this->toEnglishDigits($request->input('account_number'))),
            'iban'           => strtoupper(preg_replace('/\s/', '', $this->toEnglishDigits($request->input('iban')))),
        ]);

        $validated = $request->validate([
            'bank_name'      => ['required', 'string', 'max:100'],
            'account_holder' => ['required', 'string', 'max:150'],
            'card_number'    => ['nullable', 'digits:16'],
            'account_number' => ['nullable', 'string', 'min:6', 'max:30'],
            'iban'           => ['nullable', 'regex:/^IR\d{24}$/'],

            'bank_methods'               => ['nullable', 'array', 'max:20'],
            'bank_methods.*.title'       => ['required', 'string', 'max:100'],
            'bank_methods.*.description' => ['nullable', 'string', 'max:255'],
            'bank_methods.*.enabled'     => ['boolean'],
        ], [
            'card_number.digits' => 'شماره کارت باید ۱۶ رقم باشد.',
            'iban.regex'         => 'شماره شبا باید با IR شروع شود و ۲۴ رقم بعد از آن داشته باشد.',
        ]);

        if (
            empty($validated['card_number']) &&
            empty($validated['account_number']) &&
            empty($validated['iban'])
        ) {
            return response()->json([
                'success' => false,
                'message' => 'حداقل یکی از شماره کارت، شماره حساب یا شبا باید وارد شود.',
                'errors'  => [
                    'card_number' => ['حداقل یکی از شماره کارت، شماره حساب یا شبا باید وارد شود.'],
                ],
            ], 422);
        }

        $config = SysDirectPaymentConfig::first() ?? new SysDirectPaymentConfig();

        $config->bank_name      = $validated['bank_name'];
        $config->account_holder = $validated['account_holder'];
        $config->card_number    = $validated['card_number'] ?? null;
        $config->account_number = $validated['account_number'] ?? null;
        $config->iban           = $validated['iban'] ?? null;
        $config->bank_methods   = $validated['bank_methods'] ?? [];
        $config->save();

        return ApiResponse::success(
            $config,
            'تنظیمات انتقال بانکی با موفقیت ذخیره شد.'
        );
    }
}