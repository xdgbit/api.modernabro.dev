<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        // درخواست‌های API هیچ‌وقت هدایت نمی‌شوند؛ پاسخ JSON ۴۰۱ برمی‌گردد
        if ($request->expectsJson() || $request->is('api/*')) {
            return null;
        }

        // فقط برای صفحات وب باقی‌مانده (مثلاً پنل ادمین)
        $routeName = optional($request->route())->getName() ?? '';

        return str_contains($routeName, 'admin')
            ? route('admin.login.index')
            : route('auth.login.index');
    }
}