<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ImportController extends Controller
{
    private function tenantId(): int
    {
        return auth()->user()->tenant_id;
    }

    public function index()
    {
        return view('import.index');
    }

    // ─── Products ─────────────────────────────────────────────────

    public function importProducts(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:2048']);

        $rows    = $this->parseCsv($request->file('file'));
        $headers = array_map('strtolower', array_map('trim', $rows[0] ?? []));

        $required = ['name'];
        foreach ($required as $col) {
            if (!in_array($col, $headers)) {
                return back()->with('error', "Kolom wajib tidak ditemukan: \"{$col}\". Pastikan baris pertama adalah header.");
            }
        }

        $warehouse = Warehouse::where('tenant_id', $this->tenantId())->first();

        $created = 0; $skipped = 0; $errors = [];

        foreach (array_slice($rows, 1) as $i => $row) {
            if (count($row) < 1 || empty(trim($row[0] ?? ''))) continue;

            $data = array_combine($headers, array_pad($row, count($headers), ''));

            $name = trim($data['name'] ?? '');
            if (!$name) { $skipped++; continue; }

            if (Product::where('tenant_id', $this->tenantId())->where('name', $name)->exists()) {
                $skipped++;
                continue;
            }

            $validator = Validator::make($data, [
                'name'       => 'required|string|max:255',
                'price_sell' => 'nullable|numeric|min:0',
                'price_buy'  => 'nullable|numeric|min:0',
                'unit'       => 'nullable|string|max:20',
                'stock_min'  => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                $errors[] = "Baris " . ($i + 2) . ": " . implode(', ', $validator->errors()->all());
                continue;
            }

            $sku = !empty($data['sku']) ? trim($data['sku'])
                : strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 6)) . '-' . rand(100, 999);

            $product = Product::create([
                'tenant_id'  => $this->tenantId(),
                'name'       => $name,
                'sku'        => $sku,
                'barcode'    => $data['barcode'] ?? null,
                'category'   => $data['category'] ?? null,
                'unit'       => $data['unit'] ?? 'pcs',
                'price_sell' => (float) ($data['price_sell'] ?? 0),
                'price_buy'  => (float) ($data['price_buy'] ?? 0),
                'stock_min'  => (int) ($data['stock_min'] ?? 5),
                'description'=> $data['description'] ?? null,
                'is_active'  => true,
            ]);

            if ($warehouse) {
                $initialStock = (int) ($data['initial_stock'] ?? 0);
                ProductStock::create([
                    'product_id'   => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'quantity'     => $initialStock,
                ]);
            }

            $created++;
        }

        return back()->with('import_result', [
            'type'    => 'products',
            'created' => $created,
            'skipped' => $skipped,
            'errors'  => $errors,
        ]);
    }

    // ─── Employees ────────────────────────────────────────────────

    public function importEmployees(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:2048']);

        $rows    = $this->parseCsv($request->file('file'));
        $headers = array_map('strtolower', array_map('trim', $rows[0] ?? []));

        if (!in_array('name', $headers)) {
            return back()->with('error', 'Kolom "name" wajib ada di baris pertama (header).');
        }

        $created = 0; $skipped = 0; $errors = [];

        foreach (array_slice($rows, 1) as $i => $row) {
            if (empty(trim($row[0] ?? ''))) continue;

            $data = array_combine($headers, array_pad($row, count($headers), ''));
            $name = trim($data['name'] ?? '');
            if (!$name) { $skipped++; continue; }

            if (Employee::where('tenant_id', $this->tenantId())->where('name', $name)->exists()) {
                $skipped++;
                continue;
            }

            $joinDate = null;
            if (!empty($data['join_date'])) {
                try { $joinDate = \Carbon\Carbon::parse($data['join_date'])->format('Y-m-d'); } catch (\Exception $e) {}
            }

            Employee::create([
                'tenant_id'   => $this->tenantId(),
                'name'        => $name,
                'email'       => $data['email'] ?? null,
                'phone'       => $data['phone'] ?? null,
                'position'    => $data['position'] ?? null,
                'department'  => $data['department'] ?? null,
                'join_date'   => $joinDate ?? today(),
                'status'      => 'active',
                'salary'      => (float) ($data['salary'] ?? 0),
                'bank_name'   => $data['bank_name'] ?? null,
                'bank_account'=> $data['bank_account'] ?? null,
                'address'     => $data['address'] ?? null,
                'employee_id' => $data['employee_id'] ?? ('EMP-' . str_pad($created + 1, 4, '0', STR_PAD_LEFT)),
            ]);

            $created++;
        }

        return back()->with('import_result', [
            'type'    => 'employees',
            'created' => $created,
            'skipped' => $skipped,
            'errors'  => $errors,
        ]);
    }

    // ─── Customers ────────────────────────────────────────────────

    public function importCustomers(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:2048']);

        $rows    = $this->parseCsv($request->file('file'));
        $headers = array_map('strtolower', array_map('trim', $rows[0] ?? []));

        if (!in_array('name', $headers)) {
            return back()->with('error', 'Kolom "name" wajib ada di baris pertama (header).');
        }

        $created = 0; $skipped = 0; $errors = [];

        foreach (array_slice($rows, 1) as $i => $row) {
            if (empty(trim($row[0] ?? ''))) continue;

            $data = array_combine($headers, array_pad($row, count($headers), ''));
            $name = trim($data['name'] ?? '');
            if (!$name) { $skipped++; continue; }

            if (Customer::where('tenant_id', $this->tenantId())->where('name', $name)->exists()) {
                $skipped++;
                continue;
            }

            Customer::create([
                'tenant_id'    => $this->tenantId(),
                'name'         => $name,
                'email'        => $data['email'] ?? null,
                'phone'        => $data['phone'] ?? null,
                'company'      => $data['company'] ?? null,
                'address'      => $data['address'] ?? null,
                'npwp'         => $data['npwp'] ?? null,
                'credit_limit' => (float) ($data['credit_limit'] ?? 0),
                'is_active'    => true,
            ]);

            $created++;
        }

        return back()->with('import_result', [
            'type'    => 'customers',
            'created' => $created,
            'skipped' => $skipped,
            'errors'  => $errors,
        ]);
    }

    // ─── CSV Template Download ────────────────────────────────────

    public function downloadTemplate(string $type)
    {
        $templates = [
            'products'  => ['name', 'sku', 'barcode', 'category', 'unit', 'price_sell', 'price_buy', 'stock_min', 'initial_stock', 'description'],
            'employees' => ['name', 'employee_id', 'email', 'phone', 'position', 'department', 'join_date', 'salary', 'bank_name', 'bank_account', 'address'],
            'customers' => ['name', 'email', 'phone', 'company', 'address', 'npwp', 'credit_limit'],
        ];

        abort_if(!isset($templates[$type]), 404);

        $headers = $templates[$type];
        $csv     = implode(',', $headers) . "\n";

        // Add example row
        $examples = [
            'products'  => ['Produk Contoh', 'SKU-001', '', 'Kategori A', 'pcs', '10000', '7000', '5', '100', 'Deskripsi produk'],
            'employees' => ['Budi Santoso', 'EMP-001', 'budi@email.com', '08123456789', 'Staff', 'Operasional', '2024-01-01', '3000000', 'BCA', '1234567890', 'Jakarta'],
            'customers' => ['PT Maju Jaya', 'info@majujaya.com', '02112345678', 'PT Maju Jaya', 'Jakarta Selatan', '', '5000000'],
        ];

        $csv .= implode(',', $examples[$type]) . "\n";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"template-import-{$type}.csv\"",
        ]);
    }

    // ─── Helper ───────────────────────────────────────────────────

    private function parseCsv($file): array
    {
        $rows = [];
        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                $rows[] = $row;
            }
            fclose($handle);
        }
        return $rows;
    }
}
