<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Super Admin User
    |--------------------------------------------------------------------------
    |
    | These values are used by the AdminUserSeeder to create the default
    | super admin user. You can override the password via the .env file.
    |
    */

    'default_user' => [
        'name' => env('DEFAULT_USER_NAME', 'Super Admin'),
        'email' => env('DEFAULT_USER_EMAIL', 'superadmin@os.my'),
        'password' => env('DEFAULT_USER_PASSWORD'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Configure how notifications are dispatched. When 'queue' is true,
    | notifications will be queued for background processing. When false,
    | notifications will be sent immediately (synchronously).
    |
    | Default is false to work out of the box without queue worker setup.
    | Set to true once you have configured a queue driver.
    |
    */

    'notifications' => [
        'queue' => env('OS_NOTIFICATIONS_QUEUE', false),
    ],

];
