<?php

namespace App\Http\Controllers\Admin\User\Customer;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserCustomer\StoreUserCustomerRequest;
use App\Http\Requests\Admin\UserCustomer\UpdateUserCustomerRequest;
use App\Models\User\Customer\UserCustomer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function get_data()
    {
        $total = UserCustomer::query()->count();
        $active = UserCustomer::query()
            ->where('is_active', true)
            ->count();
        $inactive = UserCustomer::query()
            ->where('is_active', false)
            ->count();
        $reservations = UserCustomer::query()
            ->whereHas('reservations')->count();

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

        $query = UserCustomer::query();


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
                        'mobile_number',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'email',
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

            'mobile_number',

            'email',

            'is_active',

            'created_at',

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
           Sort Direction
        ====================================================== */

        $dir = $dir === 'asc'
            ? 'asc'
            : 'desc';


        $query->orderBy(
            $columnName,
            $dir
        );


        /* ======================================================
           Pagination
        ====================================================== */

        $customers = $query->paginate(
            $length
        );


        /* ======================================================
           Transform
        ====================================================== */

        $customers->getCollection()
            ->transform(function ($customer) {

                return [

                    'id' =>
                        $customer->id,

                    'first_name' =>
                        $customer->first_name,

                    'last_name' =>
                        $customer->last_name,

                    'full_name' =>
                        trim(
                            $customer->first_name .
                            ' ' .
                            $customer->last_name
                        ),

                    'mobile_number' =>
                        $customer->mobile_number,

                    'email' =>
                        $customer->email,


                    'is_active' =>
                        $customer->is_active,

                    'fa_is_active' =>
                        $customer->is_active
                            ? 'فعال'
                            : 'غیرفعال',

                ];

            });


        /* ======================================================
           Response
        ====================================================== */

        return ApiResponse::success(
            $customers
        );
    }

    public function show(UserCustomer $customer)
    {
        return ApiResponse::success([
            'customer' => $customer,
        ]);
    }

    public function update(UpdateUserCustomerRequest $request, UserCustomer $customer)
    {
        $customer->email = $request->email;
        $customer->last_name = $request->last_name;
        $customer->is_active = $request->is_active;
        $customer->first_name = $request->first_name;
        $customer->mobile_number = $request->mobile_number;
        if (!empty($request->password))
        {
            $customer->password = \Hash::make($request->password);
        }
        $customer->save();
        return ApiResponse::success([
            'customer' => $customer->fresh(),
        ]);
    }

    public function store(StoreUserCustomerRequest $request)
    {
        $data = $request->validated();

        if (!empty($data['password']))
        {
            $data['password'] = \Hash::make($data['password']);
        }

        $customer = UserCustomer::create($data);

        return ApiResponse::success([
            'customer' => $customer,
        ]);
    }
}
