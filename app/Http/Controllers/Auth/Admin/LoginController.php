<?php


namespace App\Http\Controllers\Auth\Admin;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use GrantHolle\Altcha\Rules\ValidAltcha;
use Illuminate\Support\Facades\RateLimiter;

class LoginController extends Controller
{
    public function index(Request $request)
    {
        $token = config('constants.admin_login_token');

        if ($token && $request->query('token') === $token)
        {
            session(['admin_login_verified' => true]);
        }

        if ($token && !session('admin_login_verified'))
        {
            abort(404);
        }

        return view('admin.login');
    }

    public function attempt(Request $request)
    {
        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'max:255',
            ],
            'password' => [
                'required',
                'string',
                'max:255',
            ],
            'altcha'   => [
                'required',
                new ValidAltcha,
            ],
        ]);

        /*
         * Normalize username.
         */
        $username = trim($validated['username']);

        /*
         * Rate limit by username + IP.
         *
         * 10 failed attempts / 15 minutes.
         */
        $rateLimitKey = sprintf(
            'admin-login:%s:%s',
            Str::lower($username),
            $request->ip()
        );

        if (RateLimiter::tooManyAttempts($rateLimitKey, 10))
        {
            return response()->json([
                'success'     => false,
                'errors'      => [
                    'credential' => [
                        'تعداد تلاش‌های ورود بیش از حد مجاز است. لطفاً بعداً مجدداً تلاش کنید.',
                    ],
                ],
                'retry_after' => RateLimiter::availableIn($rateLimitKey),
            ], 429);
        }

        $attempt = auth()
            ->guard('user_expert')
            ->attempt(
                [
                    'username' => $username,
                    'password' => $validated['password'],
                    'status'   => true,
                ],
                false
            );

        if (!$attempt)
        {
            /*
             * Count only failed login attempts.
             */
            RateLimiter::hit($rateLimitKey, 15 * 60);

            return response()->json([
                'success' => false,
                'errors'  => [
                    'credential' => [
                        'نام کاربری یا رمز عبور اشتباه است.',
                    ],
                ],
            ], 422);
        }

        /*
         * Successful login:
         * clear failed-attempt limiter.
         */
        RateLimiter::clear($rateLimitKey);

        /*
         * Prevent session fixation.
         */
        $request->session()->regenerate();

        /*
         * Optional: store login metadata.
         */
        $request->session()->put(
            'admin_user_agent',
            $request->userAgent()
        );

        return response()->json([
            'success' => true,
//            'redirect' => route('admin'),
        ]);
    }
}
