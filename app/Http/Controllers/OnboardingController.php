<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function show()
    {
        $user   = auth()->user();
        $tenant = $user->tenant;

        // Sudah onboarding atau bukan admin → skip
        if (!$tenant || $tenant->onboarding_completed || !$user->isAdmin()) {
            return redirect()->route('dashboard');
        }

        return view('onboarding.wizard', compact('tenant'));
    }

    public function complete(Request $request)
    {
        $user   = auth()->user();
        $tenant = $user->tenant;

        abort_if(!$tenant || !$user->isAdmin(), 403);

        $data = $request->validate([
            'business_name'       => 'required|string|max:255',
            'business_type'       => 'nullable|string|max:50',
            'business_description'=> 'nullable|string|max:500',
            'phone'               => 'nullable|string|max:20',
            'address'             => 'nullable|string|max:500',
            'warehouse_name'      => 'required|string|max:255',
            'products'            => 'nullable|array|max:10',
            'products.*.name'     => 'required_with:products|string|max:255',
            'products.*.price'    => 'nullable|numeric|min:0',
            'products.*.unit'     => 'nullable|string|max:20',
            'expense_categories'  => 'nullable|string',
        ]);

        // Update tenant info
        $tenant->update([
            'name'                 => $data['business_name'],
            'business_type'        => $data['business_type'] ?? $tenant->business_type,
            'business_description' => $data['business_description'] ?? null,
            'phone'                => $data['phone'] ?? null,
            'address'              => $data['address'] ?? null,
            'onboarding_completed' => true,
        ]);

        // Buat gudang utama
        $warehouse = Warehouse::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => $data['warehouse_name']],
            [
                'code'      => strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $data['warehouse_name']), 0, 4)) . '-01',
                'is_active' => true,
            ]
        );

        // Buat produk awal
        foreach ($data['products'] ?? [] as $item) {
            $name = trim($item['name'] ?? '');
            if (!$name) continue;

            $product = Product::firstOrCreate(
                ['tenant_id' => $tenant->id, 'name' => $name],
                [
                    'sku'        => strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 6)) . '-' . rand(100, 999),
                    'price_sell' => $item['price'] ?? 0,
                    'price_buy'  => 0,
                    'unit'       => $item['unit'] ?? 'pcs',
                    'stock_min'  => 5,
                    'is_active'  => true,
                ]
            );

            ProductStock::firstOrCreate(
                ['product_id' => $product->id, 'warehouse_id' => $warehouse->id],
                ['quantity' => 0]
            );
        }

        // Buat kategori pengeluaran
        $categories = array_filter(array_map('trim', explode(',', $data['expense_categories'] ?? '')));
        if (empty($categories)) {
            $categories = ['Bahan Baku', 'Operasional', 'Gaji Karyawan'];
        }
        foreach ($categories as $catName) {
            if (!$catName) continue;
            ExpenseCategory::firstOrCreate(
                ['tenant_id' => $tenant->id, 'name' => $catName],
                [
                    'code'      => strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $catName), 0, 5)) . '-' . rand(10, 99),
                    'type'      => 'expense',
                    'is_active' => true,
                ]
            );
        }

        return redirect()->route('dashboard')->with('success', 'Setup awal selesai! Selamat datang di Qalcuity ERP.');
    }

    public function skip()
    {
        $tenant = auth()->user()->tenant;
        if ($tenant) {
            $tenant->update(['onboarding_completed' => true]);
        }
        return redirect()->route('dashboard');
    }
}
