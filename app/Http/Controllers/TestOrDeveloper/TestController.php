<?php

namespace App\Http\Controllers\TestOrDeveloper;

use App\Jobs\PusherNotifications\BeamsNotification;
use App\Jobs\SendUltraFastSMS;
use App\Models\User\UserCustomer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

//use Spatie\Permission\Models\Permission;
//use Spatie\Permission\Models\Role;
class TestController extends Controller
{
    public function common(Request $request)
    {
        //product_categories
        $permissions = [
            'reservations.view',
            'reservations.create',
            'reservations.edit',
            'reservations.cancel',
            'reservations.approve',
            'reservations.notify_host',
            'reservations.release_unit',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'user_expert',
            ]);
        }

        $operator = Role::findByName('reservation_operator', 'user_expert');

        $operator->syncPermissions($permissions);
        dd('sdsada');
//        $user = \App\Models\User\UserExpert::find(91);
//
//        $user->assignRole('super_admin');
//        dd('super_admin ..');
//        $user = \App\Models\User\UserExpert::role('super_admin', 'user_expert')->first();
//        $user = \App\Models\User\UserExpert::with('roles')->get();
//
//        dd($user);
        Permission::firstOrCreate([
            'name'       => 'reservations.notify_host',
            'guard_name' => 'user_expert',
        ]);

        $operator = Role::findByName('reservation_operator', 'user_expert');

        $operator->givePermissionTo('reservations.notify_host');
        dd('c');
        $permissions = [
            'reservations.units.release',
            'reservations.notify_host',
        ];

        foreach ($permissions as $permission)
        {
            Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => 'user_expert',
            ]);
        }

        $operator = Role::findByName(
            'reservation_operator',
            'user_expert'
        );

        $operator->givePermissionTo($permissions);
        dd('xxxxxxxxxxxxxxx');

        $permissions = [
            'reservations.view',
            'reservations.create',
            'reservations.edit',
            'reservations.cancel',
            'reservations.approve',
            'reservations.reject',
        ];

        foreach ($permissions as $permission)
        {
            Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => 'user_expert',
            ]);
        }

        $operator = Role::findByName('reservation_operator', 'user_expert');

        $operator->syncPermissions($permissions);


        dd('VB0');


        Role::firstOrCreate([
            'name'       => 'super_admin',
            'guard_name' => 'user_expert',
        ]);

        Role::firstOrCreate([
            'name'       => 'accommodation_manager',
            'guard_name' => 'user_expert',
        ]);

        Role::firstOrCreate([
            'name'       => 'reservation_operator',
            'guard_name' => 'user_expert',
        ]);
        dd('dsadsadasdsadas');
//       dd($user->getDefaultGuardName());
        $user = \App\Models\User\UserExpert::first();
        dd($user->roles->pluck('guard_name')->unique());
//        $user->assignRole('admin');
        $user->hasRole('admin');
        dd('dasdsadsa', $user->can('users.create'));

        $role = \Spatie\Permission\Models\Role::findByName('admin', 'user_expert');

//        $role->givePermissionTo([
//            'users.view',
//            'users.create',
//            'users.edit',
//            'users.delete',
//        ]);
//        ;
        dd('cccccc', $role->permissions);
//        \Spatie\Permission\Models\Permission::create([
//            'name' => 'users.view',
//            'guard_name' => 'user_expert',
//        ]);
//
//        \Spatie\Permission\Models\Permission::create([
//            'name' => 'users.create',
//            'guard_name' => 'user_expert',
//        ]);
//
//        \Spatie\Permission\Models\Permission::create([
//            'name' => 'users.edit',
//            'guard_name' => 'user_expert',
//        ]);
//
//        \Spatie\Permission\Models\Permission::create([
//            'name' => 'users.delete',
//            'guard_name' => 'user_expert',
//        ]);
////        \Spatie\Permission\Models\Role::create([
////            'name' => 'admin',
////            'guard_name' => 'user_expert',
////        ]);
        dd('ddddd');
        $reservation = [
            'id'   => 5,
            'code' => 'SADSADSAD'
        ];
        $reservation = json_encode($reservation, true);
        $reservation = json_decode($reservation);
        dd($reservation, $reservation->code);


//        $r['id']
        $user_customer = UserCustomer::query()->find(4776);

        $msg =
            [
                'type'    => 'success',
                'title'   => 'برزورسانی وضعیت رزرو :',
                'message' => "رزرو شما با موفقیت برزورسانی شد."
            ];

        event(new \App\Events\ReservationCustomerUpdatedPrivate($user_customer, $msg));

        dd('sadsada');

        event(new \App\Events\ExpertBadgeUpdate(['badge_type' => 'reservation_badge']));

        dd('.... Test .... Expert Badge Update ....');
        $common_title = 'رزرو شما ثبت شد';
        $common_message = 'رزرو شما با موفقیت ثبت شده است و در انتظار بررسی کارشناس می‌باشد. نتیجه بررسی متعاقباً به شما اطلاع داده خواهد شد.';
        $url = route('frontend.home');
        $common_device_interest = check_production_channel(get_expert_common_event_channel());
        $beams_notification_common_job = new BeamsNotification([$common_device_interest], $common_title, $common_message, $url);
//        dispatch($beams_notification_common_job)->onQueue('beams_notification_bfu_insert');
        dispatch_sync($beams_notification_common_job);
        dd('Test BeamsNotification');

//        $address =
//        $d = 0.4;
        $template_id = '42296';

//        $template_id = '528087';

        $verify_code_value = '12506';
        $mobile_number = '09150785844';
        $job = new SendUltraFastSMS(
            ['VerifyCode' => $verify_code_value],
            $template_id,
            $mobile_number,
            'verify_mobile'
        );
        dispatch_sync($job);

        dd('AAAi ... ...');
//        $reservation = Reservation::create([
//            'code'                  => Reservation::generate_code(),
//            'user_customer_id'      => $validated['user_customer_id'],
//            'accommodation_id'      => $validated['accommodation_id'],
//            'accommodation_unit_id' => $unit->id,
//            'check_in'              => $checkIn->toDateString(),
//            'check_out'             => $checkOut->toDateString(),
//            'guest_count'           => $validated['guest_count'],
//            'unit_price'            => $nights > 0 ? $totalAmount / $nights : 0,
//            'nights'                => $nights,
//            'total_amount'          => $totalAmount,
//            'status'                => 'pending',
//            'submitted_at'          => Carbon::now(),
//            'submitted_by'          => auth()->guard('user_expert')->id(),
//            'description'           => $validated['description'] ?? null,
//        ]);
    }

    public function testVue()
    {
        return view('test');
    }
}