<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\User\Customer\UserCustomer;
use App\Models\User\Customer\UserCustomerVerifyMobileCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public function sendOtp(Request $request)
    {
        $request->merge([
            'mobile_number' => $this->normalizeMobileNumber(
                $request->input('mobile_number')
            ),
        ]);

        $validated = $request->validate([
            'mobile_number' => ['required', 'regex:/^09\d{9}$/'],
        ]);

        $mobile = $validated['mobile_number'];
        $bypass = is_set_developer_token();
        if (!$bypass)
        {
            $ipKey = 'customer-otp-ip:' . $request->ip();
            $mobileKey = 'customer-otp-mobile:' . $mobile;

            if (RateLimiter::tooManyAttempts($ipKey, 20))
            {
                return response()->json([
                    'message' => 'تعداد درخواست‌ها بیش از حد مجاز است.',
                ], 429);
            }

            if (RateLimiter::tooManyAttempts($mobileKey, 5))
            {
                return response()->json([
                    'message' => 'تعداد درخواست کد تأیید بیش از حد مجاز است.',
                ], 429);
            }

            RateLimiter::hit($ipKey, 3600);
            RateLimiter::hit($mobileKey, 900);
        }

        // اگر مشتری وجود نداشته باشد، به عنوان مشتری جدید ساخته می‌شود
        $customer = UserCustomer::firstOrCreate(
            ['mobile_number' => $mobile],
            ['is_active' => false]
        );

        $isNewCustomer = $customer->wasRecentlyCreated;

        $availableRequests = $customer->verify_mobile_available_requests;

        if (!$bypass)
        {
            if ($availableRequests <= 0)
            {
                return response()->json([
                    'message' => 'سقف درخواست روزانه کد تأیید تمام شده است.',
                ], 429);
            }

            $pendingTime = $customer->verify_mobile_pending_time_to_next_request;

            if ($pendingTime > 0)
            {
                return response()->json([
                    'message'     => 'برای درخواست مجدد کمی صبر کنید.',
                    'retry_after' => $pendingTime,
                ], 429);
            }
        }

        $otp = (string)random_int(100000, 999999);

        DB::transaction(function () use ($customer, $otp, &$verifyCode) {
            UserCustomerVerifyMobileCode::where(
                'user_customer_id',
                $customer->id
            )->whereNull('archived_at')
                ->update(['archived_at' => now()]);

            $verifyCode = UserCustomerVerifyMobileCode::create([
                'user_customer_id' => $customer->id,
                'verify_code'      => $otp,
                'attempts'         => 0,
                'expired_at'       => now()->addMinutes(15),
            ]);
        });

        // TODO: ارسال پیامک به شماره $mobile با کد $otp

        return response()->json([
            'message'            => 'کد تأیید ارسال شد.',
            'mobile_number'      => $mobile,
            'is_new'             => $isNewCustomer,
            'has_password'       => (bool)$customer->has_password,
            'expires_in'         => 900,
            'available_requests' => $bypass ? $availableRequests : $availableRequests - 1,
        ]);
    }

    private function normalizeMobileNumber(?string $mobile): ?string
    {
        if (!$mobile)
        {
            return null;
        }

        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        $mobile = str_replace($persian, $english, $mobile);
        $mobile = str_replace($arabic, $english, $mobile);

        $mobile = preg_replace('/[\s\-\(\)]/', '', $mobile);

        if (str_starts_with($mobile, '+98'))
        {
            $mobile = '0' . substr($mobile, 3);
        }
        elseif (str_starts_with($mobile, '98'))
        {
            $mobile = '0' . substr($mobile, 2);
        }

        return $mobile;
    }

    public function verifyOtp(Request $request)
    {
        $request->merge([
            'mobile_number' => $this->normalizeMobileNumber(
                $request->input('mobile_number')
            ),
        ]);

        $validated = $request->validate([
            'mobile_number' => ['required', 'regex:/^09\d{9}$/'],
            'verify_code'   => ['required', 'digits:6'],
        ]);

        $bypass = is_set_developer_token();

        $result = DB::transaction(function () use ($validated, $bypass) {

            $customer = UserCustomer::where(
                'mobile_number',
                $validated['mobile_number']
            )->lockForUpdate()->first();

            if (!$customer)
            {
                return ['error' => 'کد تأیید نامعتبر است.', 'status' => 422];
            }

            $verifyCode = UserCustomerVerifyMobileCode::where(
                'user_customer_id',
                $customer->id
            )->active()
                ->lockForUpdate()
                ->latest('id')
                ->first();

            if (!$verifyCode || $verifyCode->expired_at->isPast())
            {
                return ['error' => 'کد تأیید منقضی شده است.', 'status' => 422];
            }

            if (!$bypass && $verifyCode->attempts >= 5)
            {
                return ['error' => 'تعداد تلاش‌های مجاز تمام شده است.', 'status' => 429];
            }

            $verifyCode->increment('attempts');

            if ($verifyCode->verify_code !== $validated['verify_code'])
            {
                return ['error' => 'کد تأیید اشتباه است.', 'status' => 422];
            }

            $verifyCode->update([
                'success_attempt_at' => now(),
                'archived_at'        => now(),
            ]);

            $customer->update([
                'mobile_verified_at' => now(),
                'is_active'          => true,
            ]);

            return ['customer' => $customer];
        });

        if (isset($result['error']))
        {
            return response()->json([
                'message' => $result['error'],
            ], $result['status']);
        }

        $customer = $result['customer'];

        $token = $customer->createToken('customer-nuxt')->plainTextToken;

        return response()->json([
            'message'    => 'شماره موبایل با موفقیت تأیید شد.',
            'token'      => $token,
            'token_type' => 'Bearer',
            'customer'   => [
                'id'            => $customer->id,
                'first_name'    => $customer->first_name,
                'last_name'     => $customer->last_name,
                'mobile_number' => $customer->mobile_number,
                'has_password'  => $customer->has_password,
            ],
        ]);
    }

    public function loginPassword(Request $request)
    {
        $request->merge([
            'mobile_number' => $this->normalizeMobileNumber(
                $request->input('mobile_number')
            ),
        ]);

        $validated = $request->validate([
            'mobile_number' => ['required', 'regex:/^09\d{9}$/'],
            'password'      => ['required', 'string'],
        ]);

        $mobileKey = 'customer-password:' . $validated['mobile_number'];

        if (RateLimiter::tooManyAttempts($mobileKey, 10))
        {
            return response()->json([
                'message'     => 'تعداد تلاش‌های ورود بیش از حد مجاز است.',
                'retry_after' => RateLimiter::availableIn($mobileKey),
            ], 429);
        }

        $customer = UserCustomer::where(
            'mobile_number',
            $validated['mobile_number']
        )->first();

        if (
            !$customer ||
            !$customer->is_active ||
            !$customer->mobile_verified_at ||
            !$customer->password ||
            !Hash::check($validated['password'], $customer->password)
        )
        {
            RateLimiter::hit($mobileKey, 900);

            return response()->json([
                'message' => 'شماره موبایل یا رمز عبور اشتباه است.',
            ], 401);
        }

        RateLimiter::clear($mobileKey);

        $customer->update([
            'last_login_at' => now(),
        ]);

        $token = $customer->createToken('customer-nuxt')->plainTextToken;

        return response()->json([
            'message'    => 'ورود موفقیت‌آمیز بود.',
            'token'      => $token,
            'token_type' => 'Bearer',
            'customer'   => [
                'id'            => $customer->id,
                'first_name'    => $customer->first_name,
                'last_name'     => $customer->last_name,
                'mobile_number' => $customer->mobile_number,
                'has_password'  => $customer->has_password,
            ],
        ]);
    }

    public function me(Request $request)
    {
        $customer = $request->user();

        return response()->json([
            'message'  => 'ok',
            'customer' => [
                'id'            => $customer->id,
                'first_name'    => $customer->first_name,
                'last_name'     => $customer->last_name,
                'mobile_number' => $customer->mobile_number,
                'has_password'  => (bool)$customer->has_password,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'با موفقیت خارج شدید.',
        ]);
    }
}