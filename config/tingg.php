<?php

return [
    'api' => [
        'url' => env('TINGG_API_URL'),
        'callback_url' => env('TINGG_API_CALLBACK_URL'),
        'username' => env('TINGG_API_USERNAME'),
        'password' => env('TINGG_API_PASSWORD'),
    ],
    'sender_phone_number' => env('TINGG_SENDER_PHONE_NUMBER'),
    'sender_name' => env('TINGG_SENDER_NAME'),
];
