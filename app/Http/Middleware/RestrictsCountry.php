<?php

namespace App\Http\Middleware;

use Closure;

class RestrictsCountry
{
    public function handle($request, Closure $next, ...$country_symbols)
    {
        $ip = $request->ip();

        if (config('app.debug') || !config('constants.is_check_restricts_country'))
        {
            return $next($request);
        }

        if ($request->hasHeader('cf-ipcountry'))
        {
            $country_code = $request->header('cf-ipcountry');
            if (in_array(strtoupper($country_code), $country_symbols))
            {
                return $next($request);
            }
        }

        if ($request->hasHeader('cf-connecting-ip'))
        {
            $ip = $request->header('cf-connecting-ip');
        }

        $method = $request->method();
        $abort_type = strtolower($method) === 'get' ? 413 : 401;
        $s = file_get_contents('http://ip2c.org/' . $ip);

        switch ($s[0])
        {
            case '1':
                $reply = explode(';', $s);
                $founded = array_intersect($country_symbols, $reply);
                if (is_array($founded) && count($founded) > 0)
                {
                    return $next($request);
                }
                break;
            case '0': //'Something wrong';
            case '2': // Not Found In Database
            default:
                break;
        }


        $s2 = json_decode(file_get_contents('http://ip-api.com/json/' . $ip));
        $country_code = isset($s2->countryCode) ? $s2->countryCode : null;
        if ($country_code && in_array($country_code, $country_symbols))
        {
            return $next($request);
        }
        else
        {
            $s3 = json_decode(file_get_contents('http://ipwhois.app/json/' . $ip));
            $country_code = isset($s3->country_code) ? $s3->country_code : null;
            if ($country_code && in_array($country_code, $country_symbols))
            {
                return $next($request);
            }
            else
            {
                abort($abort_type);
            }
        }
    }
}
