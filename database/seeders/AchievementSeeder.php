<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        $achievements = [
            // Sales
            ['key' => 'first_sale', 'name' => 'Penjualan Pertama!', 'description' => 'Buat order penjualan pertama Anda', 'icon' => '🛒', 'category' => 'sales', 'color' => 'blue', 'points' => 10, 'requirement_type' => 'count', 'requirement_model' => 'App\\Models\\SalesOrder', 'requirement_action' => 'created', 'requirement_value' => 1, 'sort_order' => 1],
            ['key' => 'sales_10', 'name' => '10 Order Penjualan', 'description' => 'Selesaikan 10 order penjualan', 'icon' => '📈', 'category' => 'sales', 'color' => 'blue', 'points' => 25, 'requirement_type' => 'count', 'requirement_model' => 'App\\Models\\SalesOrder', 'requirement_action' => 'created', 'requirement_value' => 10, 'sort_order' => 2],
            ['key' => 'sales_100', 'name' => '100 Order Penjualan', 'description' => 'Capai 100 order penjualan — luar biasa!', 'icon' => '🚀', 'category' => 'sales', 'color' => 'blue', 'points' => 100, 'requirement_type' => 'count', 'requirement_model' => 'App\\Models\\SalesOrder', 'requirement_action' => 'created', 'requirement_value' => 100, 'sort_order' => 3],
            ['key' => 'first_customer', 'name' => 'Pelanggan Pertama', 'description' => 'Tambahkan pelanggan pertama Anda', 'icon' => '🤝', 'category' => 'sales', 'color' => 'blue', 'points' => 10, 'requirement_type' => 'count', 'requirement_model' => 'App\\Models\\Customer', 'requirement_action' => 'created', 'requirement_value' => 1, 'sort_order' => 4],
            ['key' => 'customer_50', 'name' => '50 Pelanggan', 'description' => 'Jaringan pelanggan Anda mencapai 50!', 'icon' => '👥', 'category' => 'sales', 'color' => 'blue', 'points' => 50, 'requirement_type' => 'count', 'requirement_model' => 'App\\Models\\Customer', 'requirement_action' => 'created', 'requirement_value' => 50, 'sort_order' => 5],

            // Finance
            ['key' => 'first_invoice', 'name' => 'Faktur Pertama', 'description' => 'Buat faktur penjualan pertama', 'icon' => '📄', 'category' => 'finance', 'color' => 'emerald', 'points' => 10, 'requirement_type' => 'count', 'requirement_model' => 'App\\Models\\Invoice', 'requirement_action' => 'created', 'requirement_value' => 1, 'sort_order' => 6],
            ['key' => 'transaction_50', 'name' => '50 Transaksi', 'description' => 'Catat 50 transaksi keuangan', 'icon' => '💰', 'category' => 'finance', 'color' => 'emerald', 'points' => 50, 'requirement_type' => 'count', 'requirement_model' => 'App\\Models\\Transaction', 'requirement_action' => 'created', 'requirement_value' => 50, 'sort_order' => 7],
            ['key' => 'transaction_100', 'name' => '100 Transaksi!', 'description' => 'Capai 100 transaksi — Anda ahli keuangan!', 'icon' => '🏦', 'category' => 'finance', 'color' => 'emerald', 'points' => 100, 'requirement_type' => 'count', 'requirement_model' => 'App\\Models\\Transaction', 'requirement_action' => 'created', 'requirement_value' => 100, 'sort_order' => 8],
            ['key' => 'journal_master', 'name' => 'Ahli Jurnal', 'description' => 'Buat 50 jurnal akuntansi', 'icon' => '📒', 'category' => 'finance', 'color' => 'emerald', 'points' => 75, 'requirement_type' => 'count', 'requirement_model' => 'App\\Models\\JournalEntry', 'requirement_action' => 'created', 'requirement_value' => 50, 'sort_order' => 9],

            // Inventory
            ['key' => 'first_product', 'name' => 'Produk Pertama', 'description' => 'Tambahkan produk pertama ke katalog', 'icon' => '📦', 'category' => 'inventory', 'color' => 'amber', 'points' => 10, 'requirement_type' => 'count', 'requirement_model' => 'App\\Models\\Product', 'requirement_action' => 'created', 'requirement_value' => 1, 'sort_order' => 10],
            ['key' => 'product_50', 'name' => '50 Produk Terdaftar', 'description' => 'Katalog Anda punya 50 produk!', 'icon' => '🏪', 'category' => 'inventory', 'color' => 'amber', 'points' => 50, 'requirement_type' => 'count', 'requirement_model' => 'App\\Models\\Product', 'requirement_action' => 'created', 'requirement_value' => 50, 'sort_order' => 11],
            ['key' => 'stock_30_days', 'name' => 'Stok Terjaga 30 Hari', 'description' => 'Tidak ada stok menipis selama 30 hari berturut-turut', 'icon' => '🛡️', 'category' => 'inventory', 'color' => 'amber', 'points' => 150, 'requirement_type' => 'streak', 'requirement_model' => null, 'requirement_action' => 'no_low_stock', 'requirement_value' => 30, 'sort_order' => 12],

            // General
            ['key' => 'ai_explorer', 'name' => 'Penjelajah AI', 'description' => 'Gunakan fitur AI sebanyak 5 kali', 'icon' => '🤖', 'category' => 'general', 'color' => 'purple', 'points' => 25, 'requirement_type' => 'count', 'requirement_model' => null, 'requirement_action' => 'ai_action', 'requirement_value' => 5, 'sort_order' => 13],
            ['key' => 'daily_login_7', 'name' => 'Login 7 Hari Berturut', 'description' => 'Akses ERP selama 7 hari berturut-turut', 'icon' => '🔥', 'category' => 'general', 'color' => 'purple', 'points' => 30, 'requirement_type' => 'streak', 'requirement_model' => null, 'requirement_action' => 'daily_login', 'requirement_value' => 7, 'sort_order' => 14],
            ['key' => 'daily_login_30', 'name' => 'Login 30 Hari Berturut', 'description' => 'Konsistensi luar biasa — 30 hari berturut!', 'icon' => '⚡', 'category' => 'general', 'color' => 'purple', 'points' => 100, 'requirement_type' => 'streak', 'requirement_model' => null, 'requirement_action' => 'daily_login', 'requirement_value' => 30, 'sort_order' => 15],
        ];

        foreach ($achievements as $data) {
            Achievement::updateOrCreate(['key' => $data['key']], $data);
        }
    }
}
