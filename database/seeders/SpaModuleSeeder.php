<?php

namespace Database\Seeders;

use App\Models\SpaTherapist;
use App\Models\SpaTreatment;
use App\Models\SpaPackage;
use App\Models\SpaPackageItem;
use Illuminate\Database\Seeder;

class SpaModuleSeeder extends Seeder
{
    /**
     * Run the database seeds for Spa Module
     */
    public function run(): void
    {
        $tenantId = 1; // Default tenant for seeding

        // Create Spa Therapists
        $therapists = [
            ['employee_number' => 'TH001', 'name' => 'Maria Santos', 'phone' => '081234567890', 'email' => 'maria@spa.com', 'specializations' => ['massage', 'aromatherapy'], 'hourly_rate' => 15, 'rating' => 5, 'total_treatments' => 0, 'status' => 'available'],
            ['employee_number' => 'TH002', 'name' => 'Putri Wijaya', 'phone' => '081234567891', 'email' => 'putri@spa.com', 'specializations' => ['facial', 'body_treatment'], 'hourly_rate' => 12, 'rating' => 4, 'total_treatments' => 0, 'status' => 'available'],
            ['employee_number' => 'TH003', 'name' => 'Sarah Johnson', 'phone' => '081234567892', 'email' => 'sarah@spa.com', 'specializations' => ['massage', 'reflexology'], 'hourly_rate' => 18, 'rating' => 5, 'total_treatments' => 0, 'status' => 'available'],
            ['employee_number' => 'TH004', 'name' => 'Dewi Lestari', 'phone' => '081234567893', 'email' => 'dewi@spa.com', 'specializations' => ['facial', 'scrub'], 'hourly_rate' => 10, 'rating' => 4, 'total_treatments' => 0, 'status' => 'available'],
            ['employee_number' => 'TH005', 'name' => 'Ayu Kartika', 'phone' => '081234567894', 'email' => 'ayu@spa.com', 'specializations' => ['massage', 'hot_stone'], 'hourly_rate' => 14, 'rating' => 5, 'total_treatments' => 0, 'status' => 'off_duty'],
        ];

        foreach ($therapists as $therapist) {
            SpaTherapist::create(array_merge($therapist, ['tenant_id' => $tenantId]));
        }

        // Create Spa Treatments
        $treatments = [
            // Massage Category
            ['name' => 'Swedish Massage', 'description' => 'Classic relaxation massage with long strokes', 'category' => 'massage', 'duration_minutes' => 60, 'price' => 350000, 'cost' => 50000, 'preparation_time' => 10, 'cleanup_time' => 10, 'max_daily_bookings' => 20],
            ['name' => 'Deep Tissue Massage', 'description' => 'Intense pressure targeting muscle tension', 'category' => 'massage', 'duration_minutes' => 90, 'price' => 500000, 'cost' => 70000, 'preparation_time' => 10, 'cleanup_time' => 10, 'max_daily_bookings' => 15],
            ['name' => 'Hot Stone Therapy', 'description' => 'Heated stones placed on key points of the body', 'category' => 'massage', 'duration_minutes' => 90, 'price' => 550000, 'cost' => 80000, 'preparation_time' => 15, 'cleanup_time' => 15, 'max_daily_bookings' => 12],
            ['name' => 'Balinese Traditional Massage', 'description' => 'Traditional Indonesian massage techniques', 'category' => 'massage', 'duration_minutes' => 120, 'price' => 650000, 'cost' => 90000, 'preparation_time' => 10, 'cleanup_time' => 10, 'max_daily_bookings' => 10],

            // Facial Category
            ['name' => 'Classic Facial', 'description' => 'Deep cleansing and hydration treatment', 'category' => 'facial', 'duration_minutes' => 60, 'price' => 400000, 'cost' => 100000, 'preparation_time' => 5, 'cleanup_time' => 10, 'max_daily_bookings' => 15],
            ['name' => 'Anti-Aging Facial', 'description' => 'Advanced treatment to reduce fine lines', 'category' => 'facial', 'duration_minutes' => 90, 'price' => 600000, 'cost' => 150000, 'preparation_time' => 5, 'cleanup_time' => 10, 'max_daily_bookings' => 10],
            ['name' => 'Brightening Facial', 'description' => 'Vitamin C treatment for glowing skin', 'category' => 'facial', 'duration_minutes' => 75, 'price' => 500000, 'cost' => 120000, 'preparation_time' => 5, 'cleanup_time' => 10, 'max_daily_bookings' => 12],

            // Body Treatment Category
            ['name' => 'Body Scrub', 'description' => 'Exfoliating treatment with natural ingredients', 'category' => 'body_treatment', 'duration_minutes' => 60, 'price' => 380000, 'cost' => 80000, 'preparation_time' => 10, 'cleanup_time' => 15, 'max_daily_bookings' => 15],
            ['name' => 'Body Wrap Detox', 'description' => 'Detoxifying seaweed body wrap', 'category' => 'body_treatment', 'duration_minutes' => 90, 'price' => 550000, 'cost' => 120000, 'preparation_time' => 10, 'cleanup_time' => 15, 'max_daily_bookings' => 10],

            // Reflexology Category
            ['name' => 'Foot Reflexology', 'description' => 'Pressure point therapy for feet', 'category' => 'reflexology', 'duration_minutes' => 60, 'price' => 300000, 'cost' => 40000, 'preparation_time' => 5, 'cleanup_time' => 5, 'max_daily_bookings' => 20],
            ['name' => 'Hand & Arm Massage', 'description' => 'Relaxing massage for hands and arms', 'category' => 'reflexology', 'duration_minutes' => 45, 'price' => 250000, 'cost' => 35000, 'preparation_time' => 5, 'cleanup_time' => 5, 'max_daily_bookings' => 20],

            // Aromatherapy Category
            ['name' => 'Aromatherapy Massage', 'description' => 'Massage with essential oils', 'category' => 'aromatherapy', 'duration_minutes' => 90, 'price' => 520000, 'cost' => 90000, 'preparation_time' => 10, 'cleanup_time' => 10, 'max_daily_bookings' => 12],
        ];

        foreach ($treatments as $treatment) {
            SpaTreatment::create(array_merge($treatment, ['tenant_id' => $tenantId, 'is_active' => true, 'booked_today' => 0]));
        }

        // Create Spa Packages
        $packages = [
            [
                'name' => 'Romantic Couple Package',
                'description' => 'Perfect spa experience for couples',
                'package_price' => 1500000,
                'total_duration_minutes' => 180,
                'items' => [
                    ['treatment_name' => 'Swedish Massage', 'sequence' => 1],
                    ['treatment_name' => 'Body Scrub', 'sequence' => 2],
                    ['treatment_name' => 'Classic Facial', 'sequence' => 3],
                ]
            ],
            [
                'name' => 'Ultimate Relaxation',
                'description' => 'Complete relaxation journey',
                'package_price' => 1200000,
                'total_duration_minutes' => 180,
                'items' => [
                    ['treatment_name' => 'Balinese Traditional Massage', 'sequence' => 1],
                    ['treatment_name' => 'Aromatherapy Massage', 'sequence' => 2],
                ]
            ],
            [
                'name' => 'Quick Refresh',
                'description' => 'Express spa treatment',
                'package_price' => 550000,
                'total_duration_minutes' => 105,
                'items' => [
                    ['treatment_name' => 'Foot Reflexology', 'sequence' => 1],
                    ['treatment_name' => 'Classic Facial', 'sequence' => 2],
                ]
            ],
            [
                'name' => 'Luxury Spa Day',
                'description' => 'Full day pampering experience',
                'package_price' => 2000000,
                'total_duration_minutes' => 270,
                'items' => [
                    ['treatment_name' => 'Hot Stone Therapy', 'sequence' => 1],
                    ['treatment_name' => 'Body Wrap Detox', 'sequence' => 2],
                    ['treatment_name' => 'Anti-Aging Facial', 'sequence' => 3],
                    ['treatment_name' => 'Foot Reflexology', 'sequence' => 4],
                ]
            ],
        ];

        foreach ($packages as $packageData) {
            $items = $packageData['items'];
            unset($packageData['items']);

            $package = SpaPackage::create(array_merge($packageData, ['tenant_id' => $tenantId, 'is_active' => true]));

            // Add package items
            foreach ($items as $itemData) {
                $treatment = SpaTreatment::where('tenant_id', $tenantId)
                    ->where('name', $itemData['treatment_name'])
                    ->first();

                if ($treatment) {
                    SpaPackageItem::create([
                        'tenant_id' => $tenantId,
                        'package_id' => $package->id,
                        'treatment_id' => $treatment->id,
                        'sequence_order' => $itemData['sequence'],
                    ]);
                }
            }

            // Recalculate package price
            $package->recalculatePrice();
        }

        $this->command->info('Spa Module seeded successfully!');
        $this->command->info('- 5 Therapists created');
        $this->command->info('- 12 Treatments added');
        $this->command->info('- 4 Spa Packages created with items');
    }
}
