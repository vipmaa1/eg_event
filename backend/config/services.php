<?php

return [
    'paymob' => [
        'api_key' => env('PAYMOB_API_KEY'),
        'hmac_secret' => env('PAYMOB_HMAC_SECRET'),
        'integration_id' => env('PAYMOB_INTEGRATION_ID'),
        'iframe_id' => env('PAYMOB_IFRAME_ID'),
    ],
    'fawry' => [
        'merchant_code' => env('FAWRY_MERCHANT_CODE'),
        'security_code' => env('FAWRY_SECURITY_CODE'),
    ],
    'fcm' => [
        'server_key' => env('FCM_SERVER_KEY'),
    ],
];
