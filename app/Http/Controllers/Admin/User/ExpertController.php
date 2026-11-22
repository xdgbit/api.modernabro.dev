<?php

namespace App\Http\Controllers\Admin\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Expert\StoreUserExpertRequest;
use App\Http\Requests\Admin\Expert\UpdateUserExpertRequest;
use App\Models\User\UserExpert;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class ExpertController extends Controller
{
    public function roles()
    {
        $roles = Role::query()
            ->where('guard_name', 'user_expert')
            ->whereIn('name', [
                'super_admin',
                'accommodation_manager',
                'reservation_operator',
            ])
            ->get(['id', 'name'])
            ->map(function ($role) {
                return [
                    'id'    => $role->id,
                    'name'  => $role->name,
                    'label' => match ($role->name)
                    {
                        'super_admin' => 'مدیر کل سیستم',
                        'accommodation_manager' => 'مدیر اقامتگاه',
                        'reservation_operator' => 'اپراتور رزرو',
                    },
                ];
            });

        return ApiResponse::success([
            'roles' => $roles,
        ]);
    }

    public function get_data()
    {
        $total = UserExpert::query()->count();
        $active = UserExpert::query()->where('is_active', true)->count();
        $inactive = UserExpert::query()->where('is_active', false)->count();
        $reservations = UserExpert::query()->count();

        return ApiResponse::success([
            'total'        => ConvertNumbersEntoFa($total),
            'active'       => ConvertNumbersEntoFa($active),
            'inactive'     => ConvertNumbersEntoFa($inactive),
            'reservations' => ConvertNumbersEntoFa($reservations),
        ]);
    }

    public function datatable(Request $request)
    {
        $search = $request->input('search');
        $dir = $request->input('dir', 'desc');
        $length = (int)$request->input('length', 10);
        $columnName = $request->input('column_name', 'id');

        $query = UserExpert::query();

        if ($search)
        {
            $query->where(function ($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $allowedColumns = [
            'id',
            'first_name',
            'last_name',
            'mobile_number',
            'email',
            'is_active',
            'created_at',
        ];

        if (!in_array($columnName, $allowedColumns, true))
        {
            $columnName = 'id';
        }

        $dir = $dir === 'asc' ? 'asc' : 'desc';

        $experts = $query->orderBy($columnName, $dir)->paginate($length);

        $experts->getCollection()->transform(function ($expert) {
            return [
                'id'                  => $expert->id,
                'first_name'          => $expert->first_name,
                'last_name'           => $expert->last_name,
                'full_name'           => trim($expert->first_name . ' ' . $expert->last_name),
                'mobile_number'       => $expert->mobile_number,
                'username'            => $expert->username,
                'email'               => $expert->email,
                'national_code'       => $expert->national_code,
                'is_active'           => $expert->is_active,
                'jalali_submitted_at' => $expert->jalali_submitted_at,
                'fa_is_active'        => $expert->is_active ? 'فعال' : 'غیرفعال',
            ];
        });

        return ApiResponse::success($experts);
    }

    public function show(UserExpert $expert)
    {
        $expert->load('roles');

        return ApiResponse::success([
            'expert' => array_merge(
                $expert->toArray(),
                [
                    'role_id' => $expert->roles->first()?->id,
                ]
            ),
        ]);
    }

    public function update(UpdateUserExpertRequest $request, UserExpert $expert)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $expert) {

            if (!empty($data['password']))
            {
                $expert->password = Hash::make($data['password']);
            }

            $expert->first_name = $data['first_name'];
            $expert->last_name = $data['last_name'];
            $expert->is_active = $data['is_active'];
            $expert->email = $data['email'] ?? $expert->email;
            $expert->mobile_number = $data['mobile_number'] ?? $expert->mobile_number;
            $expert->national_code = $data['national_code'] ?? $expert->national_code;
            $expert->username = $data['username'] ?? $expert->username;

            $expert->save();

            if (array_key_exists('role_id', $data))
            {
                $role = Role::query()
                    ->where('guard_name', 'user_expert')
                    ->findOrFail($data['role_id']);

                $expert->syncRoles([$role]);
            }
        });

        return ApiResponse::success([
            'expert' => $expert->fresh()->load('roles'),
        ]);
    }

    public function store(StoreUserExpertRequest $request)
    {
        $data = $request->validated();

        $role = Role::query()
            ->where('guard_name', 'user_expert')
            ->findOrFail($data['role_id']);

        $expert = DB::transaction(function () use ($data, $role) {
            $expert = new UserExpert();

            $expert->email = $data['email'] ?? null;
            $expert->first_name = $data['first_name'];
            $expert->last_name = $data['last_name'];
            $expert->is_active = $data['is_active'];
            $expert->password = Hash::make($data['password']);
            $expert->mobile_number = $data['mobile_number'];
            $expert->national_code = $data['national_code'];
            $expert->username = $data['username'] ?? null;
            $expert->submitted_at = Carbon::now();
            $expert->submitted_by = auth()->guard('user_expert')->id();

            $expert->save();

            $expert->syncRoles([$role]);

            return $expert;
        });

        return ApiResponse::success([
            'expert' => $expert->load('roles'),
        ]);
    }
}