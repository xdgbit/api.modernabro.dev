<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Role;

class RoleController extends Controller
{
    public function list()
    {
//        dd('sadsa');
        $roles = Role::query()
            ->select(['id', 'title'])
            ->get();
//        dd($roles);
        return ApiResponse::success([
            'roles' => $roles,
        ]);
    }
}
