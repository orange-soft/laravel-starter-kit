<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $config = config('os.default_user');

        if (empty($config['password'])) {
            throw new \RuntimeException(
                'DEFAULT_USER_PASSWORD must be set in your .env file before seeding the admin user.'
            );
        }

        $admin = User::firstOrCreate(
            ['email' => $config['email']],
            [
                'name' => $config['name'],
                'password' => Hash::make($config['password']),
                'email_verified_at' => now(),
                'must_change_password' => true,
            ]
        );

        $admin->assignRole(RoleName::SuperAdmin);
    }
}
