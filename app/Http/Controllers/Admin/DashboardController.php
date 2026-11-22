<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order\Order;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardService;

class DashboardController extends Controller
{
    public function get_data(DashboardService $dashboardService): \Illuminate\Http\JsonResponse
    {
        return ApiResponse::success($dashboardService->getData());
    }

    public function get_badge_data(): \Illuminate\Http\JsonResponse
    {
        //reservation_badge
//        $reservation_badge = Reservation::query()
//            ->where('status', 'precheck')
//            ->where('precheck_expired_at', '>', now())
//            ->count();

        $order_badge = Order::query()->count();
        $data = [
            'order_badge' => $order_badge
        ];

        return ApiResponse::success($data);
    }
}