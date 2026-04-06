<?php

namespace App\Services;

use App\Models\SampleDataTemplate;
use App\Models\SampleDataLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SampleDataGeneratorService
{
    /**
     * Generate sample data based on industry
     */
    public function generateForIndustry(string $industry, int $tenantId, int $userId): array
    {
        try {
            // Create log entry
            $log = SampleDataLog::create([
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'status' => 'processing',
                'started_at' => now(),
            ]);

            $recordsCreated = 0;
            $generatedData = [];

            // Generate data based on industry
            switch ($industry) {
                case 'retail':
                    $result = $this->generateRetailData($tenantId);
                    break;
                case 'restaurant':
                    $result = $this->generateRestaurantData($tenantId);
                    break;
                case 'hotel':
                    $result = $this->generateHotelData($tenantId);
                    break;
                case 'construction':
                    $result = $this->generateConstructionData($tenantId);
                    break;
                case 'agriculture':
                    $result = $this->generateAgricultureData($tenantId);
                    break;
                default:
                    $result = $this->generateGenericData($tenantId);
            }

            $recordsCreated = $result['records_created'];
            $generatedData = $result['generated_data'];

            // Update log
            $log->update([
                'status' => 'completed',
                'records_created' => $recordsCreated,
                'generated_data' => $generatedData,
                'completed_at' => now(),
            ]);

            // Update profile
            $profile = \App\Models\OnboardingProfile::where('tenant_id', $tenantId)
                ->where('user_id', $userId)
                ->first();

            if ($profile) {
                $profile->update(['sample_data_generated' => true]);
            }

            return [
                'success' => true,
                'records_created' => $recordsCreated,
                'generated_data' => $generatedData,
            ];

        } catch (\Throwable $e) {
            Log::error('Sample data generation failed: ' . $e->getMessage());

            if (isset($log)) {
                $log->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'completed_at' => now(),
                ]);
            }

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate Retail sample data
     */
    protected function generateRetailData(int $tenantId): array
    {
        $recordsCreated = 0;

        // Sample Products
        $products = [
            ['name' => 'T-Shirt Basic', 'sku' => 'TS-001', 'price' => 99000, 'stock' => 100],
            ['name' => 'Jeans Slim Fit', 'sku' => 'JN-002', 'price' => 299000, 'stock' => 50],
            ['name' => 'Sneakers Sport', 'sku' => 'SN-003', 'price' => 499000, 'stock' => 30],
            ['name' => 'Backpack Canvas', 'sku' => 'BP-004', 'price' => 199000, 'stock' => 75],
            ['name' => 'Watch Digital', 'sku' => 'WT-005', 'price' => 399000, 'stock' => 40],
        ];

        foreach ($products as $product) {
            try {
                \App\Models\Product::create([
                    'tenant_id' => $tenantId,
                    'name' => $product['name'],
                    'sku' => $product['sku'],
                    'selling_price' => $product['price'],
                    'purchase_price' => $product['price'] * 0.6,
                    'stock' => $product['stock'],
                    'category' => 'Fashion',
                    'is_active' => true,
                ]);
                $recordsCreated++;
            } catch (\Throwable $e) {
                Log::warning("Failed to create product: {$product['name']}");
            }
        }

        // Sample Customers
        $customers = [
            ['name' => 'Budi Santoso', 'phone' => '081234567890', 'email' => 'budi@example.com'],
            ['name' => 'Siti Rahayu', 'phone' => '081234567891', 'email' => 'siti@example.com'],
            ['name' => 'Ahmad Wijaya', 'phone' => '081234567892', 'email' => 'ahmad@example.com'],
        ];

        foreach ($customers as $customer) {
            try {
                \App\Models\Customer::create([
                    'tenant_id' => $tenantId,
                    'name' => $customer['name'],
                    'phone' => $customer['phone'],
                    'email' => $customer['email'],
                ]);
                $recordsCreated++;
            } catch (\Throwable $e) {
                Log::warning("Failed to create customer: {$customer['name']}");
            }
        }

        return [
            'records_created' => $recordsCreated,
            'generated_data' => [
                'products' => count($products),
                'customers' => count($customers),
            ]
        ];
    }

    /**
     * Generate Restaurant sample data
     */
    protected function generateRestaurantData(int $tenantId): array
    {
        $recordsCreated = 0;

        // Sample Menu Items
        $menuItems = [
            ['name' => 'Nasi Goreng Spesial', 'category' => 'Main Course', 'price' => 35000],
            ['name' => 'Mie Ayam Bakso', 'category' => 'Main Course', 'price' => 28000],
            ['name' => 'Sate Ayam', 'category' => 'Main Course', 'price' => 30000],
            ['name' => 'Es Teh Manis', 'category' => 'Beverages', 'price' => 8000],
            ['name' => 'Es Jeruk', 'category' => 'Beverages', 'price' => 12000],
            ['name' => 'Pisang Goreng', 'category' => 'Desserts', 'price' => 15000],
        ];

        foreach ($menuItems as $item) {
            try {
                \App\Models\MenuItem::create([
                    'tenant_id' => $tenantId,
                    'name' => $item['name'],
                    'category' => $item['category'],
                    'price' => $item['price'],
                    'is_available' => true,
                ]);
                $recordsCreated++;
            } catch (\Throwable $e) {
                Log::warning("Failed to create menu item: {$item['name']}");
            }
        }

        // Sample Tables
        for ($i = 1; $i <= 10; $i++) {
            try {
                \App\Models\Table::create([
                    'tenant_id' => $tenantId,
                    'table_number' => $i,
                    'capacity' => 4,
                    'status' => 'available',
                ]);
                $recordsCreated++;
            } catch (\Throwable $e) {
                Log::warning("Failed to create table {$i}");
            }
        }

        return [
            'records_created' => $recordsCreated,
            'generated_data' => [
                'menu_items' => count($menuItems),
                'tables' => 10,
            ]
        ];
    }

    /**
     * Generate Hotel sample data
     */
    protected function generateHotelData(int $tenantId): array
    {
        $recordsCreated = 0;

        // Sample Room Types
        $roomTypes = [
            ['name' => 'Standard Room', 'price' => 350000, 'capacity' => 2],
            ['name' => 'Deluxe Room', 'price' => 550000, 'capacity' => 2],
            ['name' => 'Suite Room', 'price' => 850000, 'capacity' => 4],
        ];

        foreach ($roomTypes as $type) {
            try {
                \App\Models\RoomType::create([
                    'tenant_id' => $tenantId,
                    'name' => $type['name'],
                    'base_price' => $type['price'],
                    'max_occupancy' => $type['capacity'],
                ]);
                $recordsCreated++;
            } catch (\Throwable $e) {
                Log::warning("Failed to create room type: {$type['name']}");
            }
        }

        // Sample Rooms
        $floors = ['1', '2', '3'];
        foreach ($floors as $floor) {
            for ($i = 1; $i <= 5; $i++) {
                try {
                    \App\Models\Room::create([
                        'tenant_id' => $tenantId,
                        'room_number' => "{$floor}0{$i}",
                        'floor' => $floor,
                        'room_type_id' => 1,
                        'status' => 'available',
                    ]);
                    $recordsCreated++;
                } catch (\Throwable $e) {
                    Log::warning("Failed to create room {$floor}0{$i}");
                }
            }
        }

        return [
            'records_created' => $recordsCreated,
            'generated_data' => [
                'room_types' => count($roomTypes),
                'rooms' => 15,
            ]
        ];
    }

    /**
     * Generate Construction sample data
     */
    protected function generateConstructionData(int $tenantId): array
    {
        $recordsCreated = 0;

        // Sample Projects
        $projects = [
            ['name' => 'Pembangunan Rumah Tinggal', 'budget' => 500000000],
            ['name' => 'Renovasi Kantor', 'budget' => 250000000],
            ['name' => 'Pembangunan Gudang', 'budget' => 750000000],
        ];

        foreach ($projects as $project) {
            try {
                \App\Models\Project::create([
                    'tenant_id' => $tenantId,
                    'name' => $project['name'],
                    'budget' => $project['budget'],
                    'status' => 'in_progress',
                    'start_date' => now()->subMonths(2),
                    'end_date' => now()->addMonths(4),
                ]);
                $recordsCreated++;
            } catch (\Throwable $e) {
                Log::warning("Failed to create project: {$project['name']}");
            }
        }

        return [
            'records_created' => $recordsCreated,
            'generated_data' => [
                'projects' => count($projects),
            ]
        ];
    }

    /**
     * Generate Agriculture sample data
     */
    protected function generateAgricultureData(int $tenantId): array
    {
        $recordsCreated = 0;

        // Sample Crop Cycles
        $crops = [
            ['name' => 'Padi Varietas IR64', 'area' => 2.5, 'planting_date' => now()->subDays(45)],
            ['name' => 'Jagung Hibrida', 'area' => 1.8, 'planting_date' => now()->subDays(30)],
            ['name' => 'Tomat Cherry', 'area' => 0.5, 'planting_date' => now()->subDays(20)],
        ];

        foreach ($crops as $crop) {
            try {
                \App\Models\CropCycle::create([
                    'tenant_id' => $tenantId,
                    'crop_name' => $crop['name'],
                    'area_hectares' => $crop['area'],
                    'planting_date' => $crop['planting_date'],
                    'expected_harvest_date' => $crop['planting_date']->addDays(90),
                    'growth_stage' => 'vegetative',
                    'status' => 'active',
                ]);
                $recordsCreated++;
            } catch (\Throwable $e) {
                Log::warning("Failed to create crop cycle: {$crop['name']}");
            }
        }

        return [
            'records_created' => $recordsCreated,
            'generated_data' => [
                'crop_cycles' => count($crops),
            ]
        ];
    }

    /**
     * Generate Generic sample data
     */
    protected function generateGenericData(int $tenantId): array
    {
        return $this->generateRetailData($tenantId);
    }

    /**
     * Get available templates for industry
     */
    public function getTemplates(string $industry): array
    {
        return SampleDataTemplate::where('industry', $industry)
            ->where('is_active', true)
            ->orderBy('usage_count', 'desc')
            ->get()
            ->toArray();
    }
}
