<?php

namespace App\Http\Controllers\Auth\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function submit(Request $request)
    {
        $adminLoginVerified = session('admin_login_verified');

        if (auth()->guard('user_expert')->check())
        {
            auth()->guard('user_expert')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($adminLoginVerified)
            {
                session(['admin_login_verified' => true]);
            }
        }

        return redirect()->route('admin.login.index');
    }
}
