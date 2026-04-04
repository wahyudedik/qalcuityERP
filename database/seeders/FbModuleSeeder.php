<?php

namespace Database\Seeders;

use App\Models\FbSupply;
use App\Models\MenuItem;
use App\Models\RestaurantMenu;
use Illuminate\Database\Seeder;

class FbModuleSeeder extends Seeder
{
    /**
     * Run the database seeds for F&B Module
     */
    public function run(): void
    {
        $tenantId = 1; // Default tenant for seeding

        // Create Restaurant Menus
        $breakfastMenu = RestaurantMenu::create([
            'tenant_id' => $tenantId,
            'name' => 'Breakfast Menu',
            'description' => 'Morning breakfast selections',
            'type' => 'breakfast',
            'available_from' => '06:00:00',
            'available_until' => '10:30:00',
            'is_active' => true,
        ]);

        $lunchMenu = RestaurantMenu::create([
            'tenant_id' => $tenantId,
            'name' => 'Lunch Menu',
            'description' => 'Midday dining options',
            'type' => 'lunch',
            'available_from' => '11:00:00',
            'available_until' => '15:00:00',
            'is_active' => true,
        ]);

        $dinnerMenu = RestaurantMenu::create([
            'tenant_id' => $tenantId,
            'name' => 'Dinner Menu',
            'description' => 'Evening fine dining',
            'type' => 'dinner',
            'available_from' => '17:00:00',
            'available_until' => '22:00:00',
            'is_active' => true,
        ]);

        $roomServiceMenu = RestaurantMenu::create([
            'tenant_id' => $tenantId,
            'name' => 'Room Service Menu',
            'description' => '24-hour in-room dining',
            'type' => 'room_service',
            'available_from' => '00:00:00',
            'available_until' => '23:59:00',
            'is_active' => true,
        ]);

        // Create Menu Items for Breakfast
        MenuItem::create([
            'tenant_id' => $tenantId,
            'menu_id' => $breakfastMenu->id,
            'name' => 'American Breakfast',
            'description' => 'Eggs, bacon, sausage, toast, hash browns',
            'price' => 85000,
            'cost' => 35000,
            'preparation_time' => 15,
            'is_available' => true,
            'daily_limit' => 50,
            'sold_today' => 0,
        ]);

        MenuItem::create([
            'tenant_id' => $tenantId,
            'menu_id' => $breakfastMenu->id,
            'name' => 'Continental Breakfast',
            'description' => 'Croissant, jam, butter, coffee/tea',
            'price' => 55000,
            'cost' => 20000,
            'preparation_time' => 10,
            'is_available' => true,
            'daily_limit' => 100,
            'sold_today' => 0,
        ]);

        MenuItem::create([
            'tenant_id' => $tenantId,
            'menu_id' => $breakfastMenu->id,
            'name' => 'Indonesian Breakfast',
            'description' => 'Nasi goreng, telur mata sapi, acar',
            'price' => 65000,
            'cost' => 25000,
            'preparation_time' => 12,
            'is_available' => true,
            'daily_limit' => 60,
            'sold_today' => 0,
        ]);

        // Create Menu Items for Lunch/Dinner
        MenuItem::create([
            'tenant_id' => $tenantId,
            'menu_id' => $lunchMenu->id,
            'name' => 'Grilled Salmon',
            'description' => 'Fresh Atlantic salmon with lemon butter sauce',
            'price' => 185000,
            'cost' => 85000,
            'preparation_time' => 25,
            'is_available' => true,
            'daily_limit' => 30,
            'sold_today' => 0,
        ]);

        MenuItem::create([
            'tenant_id' => $tenantId,
            'menu_id' => $lunchMenu->id,
            'name' => 'Beef Tenderloin Steak',
            'description' => '250g tenderloin with mashed potatoes and vegetables',
            'price' => 225000,
            'cost' => 110000,
            'preparation_time' => 30,
            'is_available' => true,
            'daily_limit' => 25,
            'sold_today' => 0,
        ]);

        MenuItem::create([
            'tenant_id' => $tenantId,
            'menu_id' => $lunchMenu->id,
            'name' => 'Chicken Caesar Salad',
            'description' => 'Grilled chicken breast on romaine lettuce with caesar dressing',
            'price' => 95000,
            'cost' => 38000,
            'preparation_time' => 15,
            'is_available' => true,
            'daily_limit' => 40,
            'sold_today' => 0,
        ]);

        MenuItem::create([
            'tenant_id' => $tenantId,
            'menu_id' => $dinnerMenu->id,
            'name' => 'Wagyu Beef Burger',
            'description' => 'Premium wagyu patty with cheese, lettuce, tomato',
            'price' => 165000,
            'cost' => 75000,
            'preparation_time' => 20,
            'is_available' => true,
            'daily_limit' => 35,
            'sold_today' => 0,
        ]);

        MenuItem::create([
            'tenant_id' => $tenantId,
            'menu_id' => $dinnerMenu->id,
            'name' => 'Pasta Carbonara',
            'description' => 'Spaghetti with creamy carbonara sauce and pancetta',
            'price' => 115000,
            'cost' => 45000,
            'preparation_time' => 18,
            'is_available' => true,
            'daily_limit' => 45,
            'sold_today' => 0,
        ]);

        // Create F&B Supplies
        $supplies = [
            ['name' => 'Chicken Breast', 'unit' => 'kg', 'current_stock' => 50.00, 'minimum_stock' => 15.00, 'cost_per_unit' => 45000, 'supplier_name' => 'PT Fresh Meat'],
            ['name' => 'Salmon Fillet', 'unit' => 'kg', 'current_stock' => 20.00, 'minimum_stock' => 8.00, 'cost_per_unit' => 185000, 'supplier_name' => 'Seafood Premium'],
            ['name' => 'Beef Tenderloin', 'unit' => 'kg', 'current_stock' => 30.00, 'minimum_stock' => 10.00, 'cost_per_unit' => 220000, 'supplier_name' => 'Prime Cuts Ltd'],
            ['name' => 'Rice', 'unit' => 'kg', 'current_stock' => 100.00, 'minimum_stock' => 30.00, 'cost_per_unit' => 12000, 'supplier_name' => 'Beras Nusantara'],
            ['name' => 'Eggs', 'unit' => 'pcs', 'current_stock' => 200.00, 'minimum_stock' => 50.00, 'cost_per_unit' => 2500, 'supplier_name' => 'Telur Segar'],
            ['name' => 'Tomato Sauce', 'unit' => 'liter', 'current_stock' => 15.00, 'minimum_stock' => 5.00, 'cost_per_unit' => 35000, 'supplier_name' => 'Sauce Factory'],
            ['name' => 'Olive Oil', 'unit' => 'liter', 'current_stock' => 10.00, 'minimum_stock' => 3.00, 'cost_per_unit' => 85000, 'supplier_name' => 'Mediterranean Imports'],
            ['name' => 'Butter', 'unit' => 'kg', 'current_stock' => 12.00, 'minimum_stock' => 5.00, 'cost_per_unit' => 65000, 'supplier_name' => 'Dairy Fresh'],
            ['name' => 'Cheese Cheddar', 'unit' => 'kg', 'current_stock' => 8.00, 'minimum_stock' => 3.00, 'cost_per_unit' => 125000, 'supplier_name' => 'Cheese World'],
            ['name' => 'Lettuce Romaine', 'unit' => 'kg', 'current_stock' => 10.00, 'minimum_stock' => 4.00, 'cost_per_unit' => 28000, 'supplier_name' => 'Green Garden'],
            ['name' => 'Potatoes', 'unit' => 'kg', 'current_stock' => 60.00, 'minimum_stock' => 20.00, 'cost_per_unit' => 15000, 'supplier_name' => 'Farm Direct'],
            ['name' => 'Onions', 'unit' => 'kg', 'current_stock' => 25.00, 'minimum_stock' => 8.00, 'cost_per_unit' => 18000, 'supplier_name' => 'Farm Direct'],
            ['name' => 'Garlic', 'unit' => 'kg', 'current_stock' => 8.00, 'minimum_stock' => 3.00, 'cost_per_unit' => 35000, 'supplier_name' => 'Spice Market'],
            ['name' => 'Flour', 'unit' => 'kg', 'current_stock' => 40.00, 'minimum_stock' => 15.00, 'cost_per_unit' => 11000, 'supplier_name' => 'Mill Best'],
            ['name' => 'Sugar', 'unit' => 'kg', 'current_stock' => 30.00, 'minimum_stock' => 10.00, 'cost_per_unit' => 14000, 'supplier_name' => 'Sweet Supply'],
            ['name' => 'Coffee Beans', 'unit' => 'kg', 'current_stock' => 15.00, 'minimum_stock' => 5.00, 'cost_per_unit' => 125000, 'supplier_name' => 'Coffee Roasters'],
            ['name' => 'Tea Bags', 'unit' => 'box', 'current_stock' => 20.00, 'minimum_stock' => 8.00, 'cost_per_unit' => 45000, 'supplier_name' => 'Tea House'],
            ['name' => 'Salt', 'unit' => 'kg', 'current_stock' => 10.00, 'minimum_stock' => 3.00, 'cost_per_unit' => 8000, 'supplier_name' => 'Basic Ingredients'],
            ['name' => 'Black Pepper', 'unit' => 'kg', 'current_stock' => 3.00, 'minimum_stock' => 1.00, 'cost_per_unit' => 95000, 'supplier_name' => 'Spice Market'],
            ['name' => 'Cooking Oil', 'unit' => 'liter', 'current_stock' => 25.00, 'minimum_stock' => 10.00, 'cost_per_unit' => 18000, 'supplier_name' => 'Oil Mills'],
        ];

        foreach ($supplies as $supply) {
            FbSupply::create(array_merge($supply, ['tenant_id' => $tenantId]));
        }

        $this->command->info('F&B Module seeded successfully!');
        $this->command->info('- 4 Restaurant Menus created');
        $this->command->info('- 8 Menu Items added');
        $this->command->info('- 20 F&B Supplies initialized');
    }
}
