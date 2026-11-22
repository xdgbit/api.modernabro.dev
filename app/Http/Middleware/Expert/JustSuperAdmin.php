<?php

namespace App\Http\Middleware\Expert;

use Closure;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JustSuperAdmin
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {

        $user = auth('user_expert')->user();

        if (!$user)
        {
            return $this->deny($request, 'احراز هویت انجام نشده است.', 401);
        }

        if (!$user->is_super_admin)
        {
            return $this->deny($request, 'شما مجوز دسترسی ندارید.', 403);
        }

        return $next($request);
    }

    private function deny(Request $request, string $message, int $status): Response
    {
        // درخواست‌های axios / API
        if ($request->expectsJson() || $request->ajax())
        {
            return ApiResponse::error($message, $status);
        }

        // درخواست معمولی مرورگر
        abort($status, $message);
    }
}