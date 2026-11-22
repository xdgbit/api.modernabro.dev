<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Location\City;
use App\Models\Location\Province;
use App\Http\Controllers\Controller;

class LocationController extends Controller
{
    public function provinces()
    {
        $provinces = Province::query()
            ->orderBy('title')
            ->get(['id', 'title']);

        return ApiResponse::success(['provinces' => $provinces]);
    }
    public function cities(Province $province)
    {
        $cities = City::query()
            ->where('province_id', $province->id)
            ->orderBy('title')
            ->get(['id', 'title']);

        return ApiResponse::success(['cities' => $cities]);
    }
}