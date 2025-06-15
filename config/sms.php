<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default SMS Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default SMS driver that will be used to
    | send SMS messages. By default, we will use the Twilio driver
    | however you may specify any of the other wonderful drivers provided here.
    |
    | Supported: "twilio", "log", "array"
    |
    */

    'default' => env('SMS_DRIVER', 'twilio'),

    /*
    |--------------------------------------------------------------------------
    | SMS Drivers
    |--------------------------------------------------------------------------
    |
    | Here you may configure the driver for each service that is used by your
    | application. A default configuration has been added for each driver.
    |
    */
    'drivers' => [
        'twilio' => [
            'driver' => 'twilio',
            'sid' => env('TWILIO_SID'),
            'token' => env('TWILIO_AUTH_TOKEN'),
            'from' => env('TWILIO_FROM'),
            'verify' => [
                'enabled' => env('TWILIO_VERIFY_ENABLED', false),
                'service_sid' => env('TWILIO_VERIFY_SERVICE_SID'),
            ],
        ],

        'log' => [
            'driver' => 'log',
            'channel' => env('SMS_LOG_CHANNEL'),
        ],

        'array' => [
            'driver' => 'array',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Number
    |--------------------------------------------------------------------------
    |
    | You may specify a default "from" number for all SMS messages. This number
    | will be used if no other "from" number is specified when sending a message.
    |
    */
    'from' => env('SMS_FROM_NUMBER'),
];
