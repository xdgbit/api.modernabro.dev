@php
    $userExpert = auth('user_expert')->user();

    $authUserExpert = [
        'first_name' => $userExpert ? $userExpert->first_name : null,
        'last_name' => $userExpert ? $userExpert->last_name : null,
        'is_super_admin' => $userExpert ? (bool) $userExpert->is_super_admin : false,
        'username' => $userExpert ? $userExpert->username : null,
    ];
@endphp

<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        window.super_admin_common_channel =
        @json(check_production_channel(get_super_admin_common_event_channel(true)));

        window.expert_common_channel =
        @json(check_production_channel(get_expert_common_event_channel()));

        window.private_pusher_channel =
        @json(check_production_channel(auth()->guard('user_expert')->user()->private_pusher_channel));

        window.instanceId = @json(config('beams.expert.instanceId'));
        window.authUserExpert = @json($authUserExpert);

        window.pusher_options = @json(public_pusher_configs());
    </script>

    @vite('resources/js/entrypoint/app.js')

    <title>Admin</title>
</head>

<body>

<div id="app"></div>

</body>
</html>