<?php

namespace Database\Seeders;

use App\Models\HousekeepingSupply;
use App\Models\HousekeepingTask;
use App\Models\HousekeepingTaskAssignment;
use App\Models\LinenInventory;
use App\Models\MaintenanceRequest;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

class HousekeepingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenantId = 1; // Default tenant

        // Create housekeeping staff users
        $staff = User::create([
            'name' => 'Housekeeping Staff',
            'email' => 'housekeeping@demo.com',
            'password' => bcrypt('password'),
            'phone' => '+6281234567890',
            'role' => 'housekeeping',
            'tenant_id' => $tenantId,
            'is_active' => true,
        ]);

        $technician = User::create([
            'name' => 'Maintenance Technician',
            'email' => 'technician@demo.com',
            'password' => bcrypt('password'),
            'phone' => '+6281234567891',
            'role' => 'maintenance',
            'tenant_id' => $tenantId,
            'is_active' => true,
        ]);

        // Create Linen Inventory Items
        $linenItems = [
            [
                'item_name' => 'Bath Towel Standard',
                'category' => 'Bathroom',
                'size' => 'Standard',
                'color' => 'White',
                'material' => 'Cotton',
                'par_level' => 3,
                'total_quantity' => 200,
                'available_quantity' => 150,
                'in_use_quantity' => 40,
                'soiled_quantity' => 8,
                'damaged_quantity' => 2,
                'unit_cost' => 25000,
            ],
            [
                'item_name' => 'Hand Towel',
                'category' => 'Bathroom',
                'size' => 'Small',
                'color' => 'White',
                'material' => 'Cotton',
                'par_level' => 3,
                'total_quantity' => 200,
                'available_quantity' => 160,
                'in_use_quantity' => 35,
                'soiled_quantity' => 5,
                'unit_cost' => 15000,
            ],
            [
                'item_name' => 'Bed Sheet King',
                'category' => 'Bedroom',
                'size' => 'King',
                'color' => 'White',
                'material' => 'Cotton',
                'par_level' => 3,
                'total_quantity' => 100,
                'available_quantity' => 70,
                'in_use_quantity' => 25,
                'soiled_quantity' => 5,
                'unit_cost' => 75000,
            ],
            [
                'item_name' => 'Pillowcase Standard',
                'category' => 'Bedroom',
                'size' => 'Standard',
                'color' => 'White',
                'material' => 'Cotton',
                'par_level' => 4,
                'total_quantity' => 200,
                'available_quantity' => 140,
                'in_use_quantity' => 50,
                'soiled_quantity' => 10,
                'unit_cost' => 20000,
            ],
            [
                'item_name' => 'Pool Towel',
                'category' => 'Pool',
                'size' => 'Large',
                'color' => 'Blue',
                'material' => 'Microfiber',
                'par_level' => 2,
                'total_quantity' => 50,
                'available_quantity' => 35,
                'in_use_quantity' => 12,
                'soiled_quantity' => 3,
                'unit_cost' => 35000,
            ],
        ];

        foreach ($linenItems as $item) {
            $item['tenant_id'] = $tenantId;
            $item['item_code'] = LinenInventory::generateItemCode($tenantId, $item['category']);
            LinenInventory::create($item);
        }

        // Create Housekeeping Supplies
        $supplies = [
            [
                'item_name' => 'Shampoo Bottle 30ml',
                'category' => 'Amenities',
                'brand' => 'Hotel Brand',
                'unit_of_measure' => 'bottle',
                'quantity_on_hand' => 500,
                'reorder_point' => 100,
                'reorder_quantity' => 500,
                'unit_cost' => 2500,
                'storage_location' => 'Storage Room A-1',
            ],
            [
                'item_name' => 'Soap Bar 20g',
                'category' => 'Amenities',
                'brand' => 'Hotel Brand',
                'unit_of_measure' => 'piece',
                'quantity_on_hand' => 600,
                'reorder_point' => 150,
                'reorder_quantity' => 600,
                'unit_cost' => 1500,
                'storage_location' => 'Storage Room A-1',
            ],
            [
                'item_name' => 'Coffee Pods',
                'category' => 'Minibar',
                'brand' => 'Nescafe',
                'unit_of_measure' => 'piece',
                'quantity_on_hand' => 300,
                'reorder_point' => 50,
                'reorder_quantity' => 200,
                'unit_cost' => 3000,
                'storage_location' => 'Minibar Storage B-2',
            ],
            [
                'item_name' => 'Toilet Paper Roll',
                'category' => 'Amenities',
                'unit_of_measure' => 'roll',
                'quantity_on_hand' => 200,
                'reorder_point' => 50,
                'reorder_quantity' => 200,
                'unit_cost' => 5000,
                'storage_location' => 'Storage Room A-2',
            ],
            [
                'item_name' => 'Multi-Surface Cleaner',
                'category' => 'Cleaning Supplies',
                'brand' => 'Wipex',
                'unit_of_measure' => 'bottle',
                'quantity_on_hand' => 20,
                'reorder_point' => 10,
                'reorder_quantity' => 24,
                'unit_cost' => 35000,
                'storage_location' => 'Chemical Storage C-1',
            ],
        ];

        foreach ($supplies as $supply) {
            $supply['tenant_id'] = $tenantId;
            $supply['item_code'] = HousekeepingSupply::generateItemCode($tenantId, $supply['category']);
            HousekeepingSupply::create($supply);
        }

        // Create sample maintenance requests
        $rooms = Room::where('tenant_id', $tenantId)->limit(5)->get();

        MaintenanceRequest::create([
            'tenant_id' => $tenantId,
            'room_id' => $rooms->first()?->id ?? 1,
            'reported_by' => 1,
            'assigned_to' => $technician->id,
            'title' => 'AC tidak dingin',
            'category' => 'HVAC',
            'description' => 'AC di kamar tidak mengeluarkan udara dingin',
            'status' => 'in_progress',
            'priority' => 'high',
            'assigned_at' => now()->subHours(4),
            'started_at' => now()->subHours(3),
        ]);

        MaintenanceRequest::create([
            'tenant_id' => $tenantId,
            'room_id' => $rooms[1]?->id ?? 2,
            'reported_by' => 1,
            'title' => 'Keran air bocor',
            'category' => 'Plumbing',
            'description' => 'Keran air di wastafel kamar mandi bocor',
            'status' => 'reported',
            'priority' => 'normal',
        ]);

        MaintenanceRequest::create([
            'tenant_id' => $tenantId,
            'room_id' => $rooms[2]?->id ?? 3,
            'reported_by' => 1,
            'assigned_to' => $technician->id,
            'title' => 'Lampu kamar mati',
            'category' => 'Electrical',
            'description' => 'Lampu utama kamar tidak menyala',
            'status' => 'completed',
            'priority' => 'low',
            'assigned_at' => now()->subDays(2),
            'started_at' => now()->subDays(2),
            'completed_at' => now()->subDays(1),
            'cost' => 50000,
            'resolution_notes' => 'Mengganti ballast lampu',
        ]);

        // Create sample housekeeping tasks
        $taskTypes = ['checkout_clean', 'stay_clean', 'deep_clean', 'inspection'];

        foreach ($rooms as $index => $room) {
            HousekeepingTask::create([
                'tenant_id' => $tenantId,
                'room_id' => $room->id,
                'assigned_to' => $index % 2 === 0 ? $staff->id : null,
                'type' => $taskTypes[$index % count($taskTypes)],
                'status' => ['pending', 'in_progress', 'completed', 'pending'][$index % 4],
                'priority' => ['low', 'normal', 'high', 'urgent'][$index % 4],
                'scheduled_at' => now()->addHours($index),
                'estimated_duration' => [30, 45, 120, 20][$index % 4],
                'notes' => 'Sample housekeeping task',
            ]);
        }

        $this->command->info('Housekeeping module seeded successfully!');
    }
}
