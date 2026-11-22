<?php

namespace App\Http\Middleware;

use Closure;

class SetDebugForDeveloper
{
    public function handle($request, Closure $next)
    {
        if (config('developer.app_debug_for_developer'))
        {

            if (session_status() === PHP_SESSION_NONE)
            {
                session_start();
            }

            if (
                $request->has('developer_token') &&
                hash_equals(
                    (string)config('developer.app_developer_secret_key'),
                    (string)$request->input('developer_token')
                )
            )
            {
                $_SESSION['developer_token'] = $request->input('developer_token');
            }

            if (!empty($_SESSION['developer_token']))
            {
                config(['app.debug' => true]);
            }
            else
            {
                config(['app.debug' => false]);
            }
        }

        return $next($request);
    }
}
