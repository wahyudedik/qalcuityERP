<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Super Admin User
        $this->call([
            SuperAdminSeeder::class,
        ]);

        // 2. Sample Data Templates
        $this->call([
            SampleDataTemplateSeeder::class,
        ]);

        // 2. Complete Demo Data (all modules included)
        $this->call([
            TenantDemoSeeder::class,
        ]);
    }
}
