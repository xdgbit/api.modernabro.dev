<?php

namespace App\Services\Admin;

use App\Models\Accommodation\AccommodationUnit;
use App\Models\Reservation\Reservation;
use App\Models\User\UserGuest;
use Carbon\Carbon;
use Closure;

class DashboardService
{
    /**
     * وضعیت‌های رزرو که واحد را در تاریخ انتخابی اشغال می‌کنند
     * (باید با RoomController یکی باشد)
     */
    private const BLOCKING_RESERVATION_STATUSES = [
        Reservation::STATUS_AWAITING_PAYMENT,
        Reservation::STATUS_AWAITING_GUEST_INFO,
        Reservation::STATUS_CONFIRMED,
        Reservation::STATUS_CHECKED_IN,
    ];

    /**
     * وضعیت‌های خود واحد که مستقل از رزرو هستند
     */
    private const UNIT_OWN_STATUSES = ['maintenance', 'cleaning', 'inactive'];

    private const UNIT_STATUSES = [
        'available',
        'reserved',
        'occupied',
        'maintenance',
        'cleaning',
        'inactive',
    ];


    /*
    |--------------------------------------------------------------------------
    | Get Dashboard Data
    |--------------------------------------------------------------------------
    */

    public function getData(): array
    {
        $today = Carbon::today()->toDateString();

        // یک بار محاسبه می‌شود و در آمار و وضعیت واحدها مشترک است
        $unitStatus = $this->getUnitStatus($today);

        return [
            'stats'              => $this->getStats($unitStatus, $today),
            'unit_status'        => $unitStatus,
            'latest_guest'       => $this->getLatestGuest(),
            'latest_reservation' => $this->getLatestReservation(),
            'latest_unit'        => $this->getLatestUnit(),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Latest Guest
    |--------------------------------------------------------------------------
    */

    protected function getLatestGuest(): ?array
    {
        $guest = UserGuest::query()
            ->latest('created_at')
            ->first();

        if (!$guest)
        {
            return null;
        }

        return [
            'id'            => $guest->id,
            'full_name'     => $guest->full_name,
            'mobile_number' => $guest->mobile_number,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Latest Reservation
    |--------------------------------------------------------------------------
    */

    protected function getLatestReservation(): ?array
    {
        $reservation = Reservation::query()
            ->latest('created_at')
            ->first();

        if (!$reservation)
        {
            return null;
        }

        return [
            'code'         => $reservation->code,
            'status'       => $reservation->status,
            'status_label' => $reservation->status_label,
            'created_at'   => $reservation->created_at,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Latest Unit
    |--------------------------------------------------------------------------
    */

    protected function getLatestUnit(): ?array
    {
        $unit = AccommodationUnit::query()
            ->latest('created_at')
            ->first();

        if (!$unit)
        {
            return null;
        }

        return [
            'id'           => $unit->id,
            'code'         => $unit->code,
            'status'       => $unit->status,
            'status_label' => $unit->status_label,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Stats
    |--------------------------------------------------------------------------
    */

    protected function getStats(array $unitStatus, string $today): array
    {
        /*
         * رزروهای امروز و دیروز
         */
        $todayReservations = Reservation::query()
            ->whereDate('created_at', $today)
            ->count();

        $yesterdayReservations = Reservation::query()
            ->whereDate('created_at', Carbon::parse($today)->subDay()->toDateString())
            ->count();

        $todayReservationsTrend = $this->calculatePercentageChange(
            $yesterdayReservations,
            $todayReservations
        );

        /*
         * مهمانان فعال: مهمان‌هایی که همین الان در حال اقامت‌اند
         * (رزرو check-in شده و بازهٔ اقامت شامل امروز است)
         */
        $activeGuests = Reservation::query()
            ->where('status', Reservation::STATUS_CHECKED_IN)
            ->whereDate('check_in', '<=', $today)
            ->whereDate('check_out', '>', $today)
            ->distinct()
            ->count('user_customer_id');

        return [

            'today_reservations' => [
                'value'       => $todayReservations,
                'description' => 'رزرو ثبت شده امروز',
                'trend'       => $todayReservationsTrend,
                'trend_label' => 'نسبت به دیروز',
            ],

            'available_units' => [
                'value'       => $unitStatus['available'],
                'total'       => $unitStatus['total'],
                'description' => "از {$unitStatus['total']} واحد",
                'trend'       => null,
                'trend_label' => '',
            ],

            'active_guests' => [
                'value'       => $activeGuests,
                'description' => 'مهمان فعال',
                'trend'       => null,
                'trend_label' => 'در حال حاضر',
            ],

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Percentage Change
    |--------------------------------------------------------------------------
    */

    protected function calculatePercentageChange(
        float|int $previous,
        float|int $current
    ): ?float
    {
        if ($previous == 0)
        {
            return $current == 0
                ? 0
                : null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }


    /*
    |--------------------------------------------------------------------------
    | Unit Status (همان منطق صفحهٔ اتاق‌ها، برای امروز)
    |--------------------------------------------------------------------------
    */

    protected function getUnitStatus(string $date): array
    {
        $result = [
            'total' => AccommodationUnit::query()->count(),
        ];

        foreach (self::UNIT_STATUSES as $status)
        {
            $query = AccommodationUnit::query();

            $this->applyStatusFilter($query, $status, $date);

            $result[$status] = $query->count();
        }

        // کلیدهای قبلی که فرانت استفاده می‌کند
        return [
            'available'   => $result['available'],
            'reserved'    => $result['reserved'],
            'in_use'      => $result['occupied'],
            'cleaning'    => $result['cleaning'],
            'maintenance' => $result['maintenance'],
            'inactive'    => $result['inactive'],
            'total'       => $result['total'],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Helpers (مشابه RoomController)
    |--------------------------------------------------------------------------
    */

    /**
     * رزروهایی که تاریخ انتخابی بین ورود (شامل) و خروج (غیرشامل) آن‌هاست
     */
    private function overlapScope(string $date): Closure
    {
        return function ($query) use ($date) {
            $query
                ->whereIn('status', self::BLOCKING_RESERVATION_STATUSES)
                ->whereDate('check_in', '<=', $date)
                ->whereDate('check_out', '>', $date);
        };
    }

    private function applyStatusFilter($query, string $status, string $date): void
    {
        $overlap = $this->overlapScope($date);

        if (in_array($status, self::UNIT_OWN_STATUSES, true))
        {
            $query->where('status', $status);

            return;
        }

        // available / reserved / occupied فقط برای واحدهایی که تعمیرات، نظافت یا غیرفعال نیستند
        $query->whereNotIn('status', self::UNIT_OWN_STATUSES);

        if ($status === 'occupied')
        {
            $query->whereHas('reservationRooms.reservation', function ($query) use ($overlap) {
                $overlap($query);
                $query->where('status', Reservation::STATUS_CHECKED_IN);
            });
        }
        elseif ($status === 'reserved')
        {
            $query
                ->whereHas('reservationRooms.reservation', $overlap)
                ->whereDoesntHave('reservationRooms.reservation', function ($query) use ($overlap) {
                    $overlap($query);
                    $query->where('status', Reservation::STATUS_CHECKED_IN);
                });
        }
        elseif ($status === 'available')
        {
            $query->whereDoesntHave('reservationRooms.reservation', $overlap);
        }
    }
}