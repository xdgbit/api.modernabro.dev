<?php

namespace App\Http\Controllers\Admin\User;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\User\UserGuest;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Guest\StoreGuestRequest;
use App\Http\Requests\Admin\Guest\UpdateGuestRequest;

class GuestController extends Controller
{
    public function show(UserGuest $guest)
    {

        $guest->loadCount('reservationRooms');

        return ApiResponse::success(array_merge(
            $guest->toArray(),
            ['reservations_count' => $guest->reservation_rooms_count]
        ));
    }

    public function get_data()
    {
        $total = UserGuest::query()->count();
        $active = UserGuest::query()
            ->where('is_active', true)
            ->count();
        $inactive = UserGuest::query()
            ->where('is_active', false)
            ->count();
        $reservations = UserGuest::query()
            ->whereHas('reservationRooms')
            ->count();

        $data = [
            'total'        => ConvertNumbersEntoFa($total),
            'active'       => ConvertNumbersEntoFa($active),
            'inactive'     => ConvertNumbersEntoFa($inactive),
            'reservations' => ConvertNumbersEntoFa($reservations)
        ];

        return ApiResponse::success($data);
    }

    public function datatable(Request $request)
    {
        $search = $request->input('search');

        $dir = $request->input(
            'dir',
            'desc'
        );

        $length = (int)$request->input(
            'length',
            10
        );

        $columnName = $request->input(
            'column_name',
            'id'
        );


        /* ======================================================
           Query
        ====================================================== */

        $query = UserGuest::query()
            ->withCount('reservationRooms');


        /* ======================================================
           Search
        ====================================================== */

        if ($search)
        {
            $query->where(function ($query) use ($search) {

                $query
                    ->where(
                        'first_name',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'last_name',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'national_code',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'mobile_number',
                        'like',
                        "%{$search}%"
                    );

            });
        }


        /* ======================================================
           Allowed Columns
        ====================================================== */

        $allowedColumns = [

            'id',

            'first_name',

            'last_name',

            'national_code',

            'mobile_number',

            'birth_date',

            'gender',

            'is_active',

            'created_at',

            'age',

        ];


        if (!in_array(
            $columnName,
            $allowedColumns,
            true
        ))
        {
            $columnName = 'id';
        }


        /* ======================================================
           Age Sorting
        ====================================================== */

        if ($columnName === 'age')
        {
            $columnName = 'birth_date';
        }


        /* ======================================================
           Sort Direction
        ====================================================== */

        $dir = $dir === 'asc'
            ? 'asc'
            : 'desc';


        /* ======================================================
           Order
        ====================================================== */

        $query->orderBy(
            $columnName,
            $dir
        );


        /* ======================================================
           Pagination
        ====================================================== */

        $guests = $query->paginate(
            $length
        );


        /* ======================================================
           Transform
        ====================================================== */

        $guests->getCollection()
            ->transform(function ($guest) {
                return [

                    'id' =>
                        $guest->id,

                    'first_name' =>
                        $guest->first_name,

                    'last_name' =>
                        $guest->last_name,

                    'full_name' =>
                        trim(
                            $guest->first_name .
                            ' ' .
                            $guest->last_name
                        ),

                    'national_code' =>
                        $guest->national_code,

                    'mobile_number' =>
                        $guest->mobile_number,

                    'birth_date' =>
                        $guest->birth_date,

                    'age' =>
                        $guest->age,

                    'gender' =>
                        $guest->gender,


                    'reservations_count' => $guest->reservation_rooms_count,
                    'is_active'          =>
                        $guest->is_active,

                    'fa_is_active' =>
                        $guest->is_active
                            ? 'فعال'
                            : 'غیرفعال',

                ];

            });


        /* ======================================================
           Response
        ====================================================== */

        return ApiResponse::success(
            $guests
        );
    }

    public function update(UpdateGuestRequest $request, UserGuest $guest)
    {
        $data = $request->validated();

        $guest->update($data);

        return ApiResponse::success([
            'guest' => $guest->fresh(),
        ]);
    }

    public function store(StoreGuestRequest $request)
    {
        $data = $request->validated();

        $guest = UserGuest::create($data);

        return ApiResponse::success([
            'guest' => $guest->fresh(),
        ]);
    }

    public function reservationsDatatable(Request $request, UserGuest $guest)
    {
        $search = $request->input('search');

        $dir = $request->input('dir', 'desc') === 'asc'
            ? 'asc'
            : 'desc';

        $length = (int)$request->input('length', 10);

        $columnName = $request->input('column_name', 'id');


        /* ======================================================
           Query
        ====================================================== */

        $query = $guest->reservationRooms()
            ->with(['reservation', 'unit']);

        /* ======================================================
           Search
        ====================================================== */

        if ($search)
        {
            $query->where(function ($query) use ($search) {

                $query
                    ->whereHas('reservation', function ($q) use ($search) {
                        $q->where('code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('unit', function ($q) use ($search) {
                        $q->where('title', 'like', "%{$search}%");
                    });

            });
        }


        /* ======================================================
           Sorting
        ====================================================== */

        $reservationColumns = [
            'code'      => 'code',
            'check_in'  => 'check_in',
            'check_out' => 'check_out',
            'nights'    => 'nights',
            'status'    => 'status',
        ];

        $ownColumns = ['id', 'guest_count', 'total_amount'];

        if (isset($reservationColumns[ $columnName ]))
        {
            $col = $reservationColumns[ $columnName ];

            $query->orderBy(
                \App\Models\Reservation\Reservation::select($col)
                    ->whereColumn(
                        'reservations.id',
                        'reservation_rooms.reservation_id'
                    )
                    ->limit(1),
                $dir
            );
        }
        elseif (in_array($columnName, $ownColumns, true))
        {
            $query->orderBy('reservation_rooms.' . $columnName, $dir);
        }
        else
        {
            $query->orderBy('reservation_rooms.id', 'desc');
        }


        /* ======================================================
           Pagination
        ====================================================== */

        $rows = $query->paginate($length);


        /* ======================================================
           Transform
        ====================================================== */

        $rows->getCollection()
            ->transform(function ($item) {

                $reservation = $item->reservation;

                return [

                    'id' => $item->id,

                    'reservation_id' => $item->reservation_id,
                    'unit_id'        => $item->accommodation_unit_id,

                    'code' => $reservation?->code,

                    'room_name' => $item->unit?->title,

                    'check_in' => $reservation?->jalali_check_in,

                    'check_out' => $reservation?->jalali_check_out,

                    'nights' => $reservation?->nights,

                    'guest_count' => $item->guest_count,

                    'total_amount' => $item->total_amount,

                    'fa_total_amount' => $item->fa_total_amount,

                    'status' => $reservation?->status,

                    'status_label' => $reservation?->status_label,

                    'status_class' => $reservation?->status_class,

                ];

            });


        /* ======================================================
           Response
        ====================================================== */

        return ApiResponse::success($rows);
    }
}
