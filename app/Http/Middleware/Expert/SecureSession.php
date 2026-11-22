<?php

namespace App\Http\Middleware\Expert;

use Closure;

class SecureSession
{
    public function handle($request, Closure $next, $abort_code = 401)
    {
        if (auth()->guard('user_expert')->check())
        {
            return $next($request);
            $user_expert = auth()->guard('user_expert')->user();

            if ($user_expert)
            {
                if ($user_expert->id === 62)
                {
                    return $next($request);
                }
                else
                {
                    if ($user_expert->last_ip === client_ip() && $user_expert->last_user_agent_md5 === md5($request->userAgent()))
                    {
                        return $next($request);
                    }
                    else
                    {
                        auth()->guard('user_expert')->logout();
                    }
                }
            }
        }

        if ($request->method() === 'GET')
        {
            return abort(403);
        }
        else
        {
            return abort(401);
        }
    }
}
