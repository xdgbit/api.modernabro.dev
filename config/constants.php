<?php
return
    [
        'admin_login_token' => env('ADMIN_LOGIN_TOKEN'),
        'auth'        =>
            [
                'user_customer' =>
                    [

                        'verify_mobile' =>
                            [
                                'pending_time_between_requests' => 60,
                                'limit_request_per_day'         => 3,
                                'limit_check_per_code'          => 5,
                            ]
                    ]
            ]
    ];