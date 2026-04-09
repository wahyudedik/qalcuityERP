<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'info@qalcuity.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('Wahyu123456789@'),
                'role' => 'super_admin',
                'tenant_id' => null, 
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
