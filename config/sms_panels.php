<?php

return [
    'sms_ir'   =>
        [
            'ultra_fast_method' =>
                [
                    'api_url'    => env('SMSIR_ULTRA_FAST_API_URL'),
                    'api_key'    => env('SMSIR_ULTRA_FAST_API_KEY'),
                    'secret_key' => env('SMSIR_ULTRA_FAST_SECRET_KEY'),
                ]
        ],
];
