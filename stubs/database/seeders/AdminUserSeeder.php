<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $config = config('os.default_user');

        $admin = User::firstOrCreate(
            ['email' => $config['email']],
            [
                'name' => $config['name'],
                'password' => $config['password'],
                'email_verified_at' => now(),
                'must_change_password' => false,
            ]
        );

        $admin->assignRole(RoleName::SuperAdmin);
    }
}
