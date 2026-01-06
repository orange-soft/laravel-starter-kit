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
        'password' => env('DEFAULT_USER_PASSWORD', 'password'),
    ],

];
