<?php namespace App\Http\Middleware;

use Closure;

class CheckSessionTimeOutForExperts
{
    public function handle($request, Closure $next)
    {
        // session()->forget('Expert_lastActivityTime');
        if (!session()->has('Expert_lastActivityTime'))
        {
            session()->put('Expert_lastActivityTime', now());
        }

        $timeout = 30; // also you can this value in your config file and use here

        if (now()->diffInMinutes(session('Expert_lastActivityTime')) >= ($timeout))
        {

            if (auth()->guard('user_expert')->check())
            {
                auth()->guard('user_expert')->logout();

                session()->forget('Expert_lastActivityTime');

                if ($request->method() === 'GET')
                {
                    return redirect()->route('admin.login.index');
                }
                else
                {
                    return abort(401);
                }
            }
        }

        session()->put('Expert_lastActivityTime', now());

        return $next($request);
    }
}
