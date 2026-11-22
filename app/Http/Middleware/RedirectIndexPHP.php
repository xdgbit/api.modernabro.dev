<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIndexPHP
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string|null ...$guards
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $searchFor = "index.php";

        $strPosition = strpos($request->fullUrl(), $searchFor);

        if ($strPosition !== false)
        {
            $url = substr($request->fullUrl(), $strPosition + strlen($searchFor));

            return redirect(config('app.url') . $url, 301);
        }

        return $next($request);
    }
}
