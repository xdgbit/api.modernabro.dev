<?php

namespace App\Http\Middleware;

use App\Models\SystemConfigs;
use Closure;

class MaintenanceSystem
{
    public function handle($request, Closure $next, $type = false)
    {
        if (is_set_developer_token())
        {
            return $next($request);
        }
        else
        {

            $system_config = SystemConfigs::notArchive()->last()->first();

            if ($system_config)
            {
                if ($system_config->all_is_maintenance === '1')
                {
                    abort(503, $system_config->all_maintenance_message);
                }
                else
                {
                    if (!$type === false)
                    {
                        if ($type === 'customer')
                        {
                            if ($system_config->customer_is_maintenance === '1')
                            {
                                abort(503, $system_config->customer_maintenance_message);
                            }
                            else
                            {
                                return $next($request);
                            }
                        }
                        elseif ($type === 'expert')
                        {
                            if ($system_config->expert_is_maintenance === '1')
                            {
                                abort(503, $system_config->expert_maintenance_message);
                            }
                            else
                            {
                                return $next($request);
                            }
                        }
                        else
                        {
                            abort(500, 'invalid maintenance type');
                        }
                    }
                    else
                    {
                        return $next($request);
                    }
                }

            }
            else
            {
                abort(500, 'not found system config record');
            }
        }
    }
}
