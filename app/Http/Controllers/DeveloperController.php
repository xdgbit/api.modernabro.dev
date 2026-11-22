<?php

namespace App\Http\Controllers;


class DeveloperController extends Controller
{
    public function login_by_id($guard, $user_id, $token)
    {
        if (
            !in_array($guard, ['user_customer', 'user_expert']) ||
            (string)intval($user_id) !== $user_id ||
            $token !== config('developer.app_developer_secret_key')
        )
        {
            abort(403);
        }

        $res = auth()->guard($guard)->loginUsingId($user_id);

        if (!$res)
        {
            return redirect()->route('auth.login.index');
        }

        if ($guard === 'user_expert')
        {
            session(['admin_login_verified' => true]);

            return redirect()->route('admin.index');
        }

        return redirect()->route('frontend.profile.index');
    }
}
