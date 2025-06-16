<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Africa's Talking Configuration
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for Africa's Talking API.
    |
    */


    /*
    |--------------------------------------------------------------------------
    | Username
    |--------------------------------------------------------------------------
    |
    | This is the username for your Africa's Talking account.
    |
    */
    'username' => env('AFRICASTALKING_USERNAME'),

    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    |
    | This is the API key for your Africa's Talking account.
    |
    */
    'api_key' => env('AFRICASTALKING_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Sender ID
    |--------------------------------------------------------------------------
    |
    | This is the sender ID that will be used when sending SMS messages.
    | Must be approved by Africa's Talking for production use.
    |
    */
    'sender_id' => env('AFRICASTALKING_SENDER_ID', 'YOUR_SENDER_ID'),

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    |
    | Set to 'production' when you are ready to go live.
    |
    */
    'environment' => env('AFRICASTALKING_ENV', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | Queue
    |--------------------------------------------------------------------------
    |
    | Whether to queue the SMS messages or send them immediately.
    |
    */
    'queue' => env('AFRICASTALKING_QUEUE', true),

    /*
    |--------------------------------------------------------------------------
    | Queue Connection
    |--------------------------------------------------------------------------
    |
    | The queue connection to use for sending SMS messages.
    |
    */
    'queue_connection' => env('AFRICASTALKING_QUEUE_CONNECTION', env('QUEUE_CONNECTION', 'sync')),

    /*
    |--------------------------------------------------------------------------
    | Queue Name
    |--------------------------------------------------------------------------
    |
    | The queue name to use for sending SMS messages.
    |
    */
    'queue_name' => env('AFRICASTALKING_QUEUE_NAME', 'default'),
];
