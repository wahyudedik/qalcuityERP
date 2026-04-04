<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\RestaurantMenu;
use App\Models\RestaurantTable;
use Illuminate\Database\Seeder;

class FbSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenantId = 1; // Adjust as needed

        // Create Restaurant Menus
        $breakfastMenu = RestaurantMenu::create([
            'tenant_id' => $tenantId,
            'name' => 'Breakfast Menu',
            'description' => 'Delicious breakfast options',
            'type' => 'breakfast',
            'available_from' => '06:00:00',
            'available_until' => '10:30:00',
            'is_active' => true,
            'display_order' => 1,
        ]);

        $lunchMenu = RestaurantMenu::create([
            'tenant_id' => $tenantId,
            'name' => 'Lunch Menu',
            'description' => 'Fresh lunch selections',
            'type' => 'lunch',
            'available_from' => '11:00:00',
            'available_until' => '15:00:00',
            'is_active' => true,
            'display_order' => 2,
        ]);

        $dinnerMenu = RestaurantMenu::create([
            'tenant_id' => $tenantId,
            'name' => 'Dinner Menu',
            'description' => 'Exquisite dinner dishes',
            'type' => 'dinner',
            'available_from' => '17:00:00',
            'available_until' => '22:00:00',
            'is_active' => true,
            'display_order' => 3,
        ]);

        $roomServiceMenu = RestaurantMenu::create([
            'tenant_id' => $tenantId,
            'name' => 'Room Service Menu',
            'description' => '24-hour room service',
            'type' => 'room_service',
            'is_active' => true,
            'display_order' => 4,
        ]);

        $minibarMenu = RestaurantMenu::create([
            'tenant_id' => $tenantId,
            'name' => 'Mini-bar Items',
            'description' => 'In-room mini-bar selection',
            'type' => 'minibar',
            'is_active' => true,
            'display_order' => 5,
        ]);

        // Breakfast Items
        MenuItem::create([
            'menu_id' => $breakfastMenu->id,
            'tenant_id' => $tenantId,
            'name' => 'Continental Breakfast',
            'description' => 'Croissant, jam, butter, coffee/tea',
            'price' => 75000,
            'cost' => 25000,
            'category' => 'Set Menu',
            'is_available' => true,
            'preparation_time' => 10,
            'allergens' => ['gluten', 'dairy'],
            'dietary_info' => ['vegetarian_option'],
        ]);

        MenuItem::create([
            'menu_id' => $breakfastMenu->id,
            'tenant_id' => $tenantId,
            'name' => 'American Breakfast',
            'description' => 'Eggs, bacon, sausage, toast, hash browns',
            'price' => 95000,
            'cost' => 35000,
            'category' => 'Set Menu',
            'is_available' => true,
            'preparation_time' => 15,
            'allergens' => ['eggs', 'gluten'],
        ]);

        MenuItem::create([
            'menu_id' => $breakfastMenu->id,
            'tenant_id' => $tenantId,
            'name' => 'Pancake Stack',
            'description' => 'Fluffy pancakes with maple syrup and berries',
            'price' => 65000,
            'cost' => 20000,
            'category' => 'Main Course',
            'is_available' => true,
            'preparation_time' => 12,
            'allergens' => ['gluten', 'eggs', 'dairy'],
            'dietary_info' => ['vegetarian'],
        ]);

        // Lunch Items
        MenuItem::create([
            'menu_id' => $lunchMenu->id,
            'tenant_id' => $tenantId,
            'name' => 'Grilled Chicken Sandwich',
            'description' => 'With lettuce, tomato, and special sauce',
            'price' => 85000,
            'cost' => 30000,
            'category' => 'Sandwiches',
            'is_available' => true,
            'preparation_time' => 15,
            'allergens' => ['gluten'],
        ]);

        MenuItem::create([
            'menu_id' => $lunchMenu->id,
            'tenant_id' => $tenantId,
            'name' => 'Caesar Salad',
            'description' => 'Romaine lettuce, croutons, parmesan, caesar dressing',
            'price' => 70000,
            'cost' => 25000,
            'category' => 'Salads',
            'is_available' => true,
            'preparation_time' => 10,
            'allergens' => ['dairy', 'gluten'],
            'dietary_info' => ['vegetarian'],
        ]);

        MenuItem::create([
            'menu_id' => $lunchMenu->id,
            'tenant_id' => $tenantId,
            'name' => 'Beef Burger Deluxe',
            'description' => 'Angus beef patty with cheese, lettuce, tomato, onion',
            'price' => 110000,
            'cost' => 45000,
            'category' => 'Burgers',
            'is_available' => true,
            'preparation_time' => 18,
            'allergens' => ['gluten', 'dairy'],
        ]);

        // Dinner Items
        MenuItem::create([
            'menu_id' => $dinnerMenu->id,
            'tenant_id' => $tenantId,
            'name' => 'Grilled Salmon',
            'description' => 'Atlantic salmon with lemon butter sauce, seasonal vegetables',
            'price' => 185000,
            'cost' => 75000,
            'category' => 'Seafood',
            'is_available' => true,
            'preparation_time' => 25,
            'allergens' => ['fish', 'dairy'],
        ]);

        MenuItem::create([
            'menu_id' => $dinnerMenu->id,
            'tenant_id' => $tenantId,
            'name' => 'Ribeye Steak',
            'description' => '300g premium ribeye with mashed potatoes and gravy',
            'price' => 250000,
            'cost' => 110000,
            'category' => 'Steaks',
            'is_available' => true,
            'preparation_time' => 30,
            'allergens' => ['dairy'],
        ]);

        MenuItem::create([
            'menu_id' => $dinnerMenu->id,
            'tenant_id' => $tenantId,
            'name' => 'Chicken Parmigiana',
            'description' => 'Breaded chicken breast with marinara and mozzarella',
            'price' => 145000,
            'cost' => 55000,
            'category' => 'Italian',
            'is_available' => true,
            'preparation_time' => 22,
            'allergens' => ['gluten', 'dairy'],
        ]);

        // Room Service Items (subset of main menu)
        MenuItem::create([
            'menu_id' => $roomServiceMenu->id,
            'tenant_id' => $tenantId,
            'name' => 'Club Sandwich',
            'description' => 'Triple-decker with chicken, bacon, lettuce, tomato',
            'price' => 95000,
            'cost' => 35000,
            'category' => 'Sandwiches',
            'is_available' => true,
            'preparation_time' => 15,
            'allergens' => ['gluten'],
        ]);

        MenuItem::create([
            'menu_id' => $roomServiceMenu->id,
            'tenant_id' => $tenantId,
            'name' => 'Mie Goreng Special',
            'description' => 'Indonesian fried noodles with vegetables and egg',
            'price' => 75000,
            'cost' => 25000,
            'category' => 'Local Cuisine',
            'is_available' => true,
            'preparation_time' => 15,
            'allergens' => ['eggs', 'gluten'],
        ]);

        // Mini-bar Items
        MenuItem::create([
            'menu_id' => $minibarMenu->id,
            'tenant_id' => $tenantId,
            'name' => 'Coca-Cola',
            'description' => '330ml can',
            'price' => 25000,
            'cost' => 8000,
            'category' => 'Soft Drinks',
            'is_available' => true,
            'daily_limit' => 10,
        ]);

        MenuItem::create([
            'menu_id' => $minibarMenu->id,
            'tenant_id' => $tenantId,
            'name' => 'Mineral Water',
            'description' => '600ml bottle',
            'price' => 15000,
            'cost' => 4000,
            'category' => 'Water',
            'is_available' => true,
            'daily_limit' => 15,
        ]);

        MenuItem::create([
            'menu_id' => $minibarMenu->id,
            'tenant_id' => $tenantId,
            'name' => 'Potato Chips',
            'description' => 'Classic salted flavor',
            'price' => 20000,
            'cost' => 7000,
            'category' => 'Snacks',
            'is_available' => true,
            'daily_limit' => 8,
        ]);

        MenuItem::create([
            'menu_id' => $minibarMenu->id,
            'tenant_id' => $tenantId,
            'name' => 'Chocolate Bar',
            'description' => 'Premium dark chocolate',
            'price' => 30000,
            'cost' => 12000,
            'category' => 'Snacks',
            'is_available' => true,
            'daily_limit' => 6,
        ]);

        MenuItem::create([
            'menu_id' => $minibarMenu->id,
            'tenant_id' => $tenantId,
            'name' => 'Beer - Heineken',
            'description' => '330ml bottle',
            'price' => 45000,
            'cost' => 18000,
            'category' => 'Alcoholic Beverages',
            'is_available' => true,
            'daily_limit' => 5,
        ]);

        // Create Restaurant Tables
        for ($i = 1; $i <= 20; $i++) {
            RestaurantTable::create([
                'tenant_id' => $tenantId,
                'table_number' => $i,
                'capacity' => $i <= 5 ? 2 : ($i <= 15 ? 4 : 6),
                'location' => $i <= 10 ? 'Indoor' : 'Outdoor',
                'status' => 'available',
                'is_active' => true,
            ]);
        }

        $this->command->info('F&B module seed data created successfully!');
    }
}
