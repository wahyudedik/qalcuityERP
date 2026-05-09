<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ChartOfAccount;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Supplier;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportController extends Controller
{
    // tenantId() inherited from parent Controller

    public function index()
    {
        return view('import.index');
    }

    // ─── Products ─────────────────────────────────────────────────

    public function importProducts(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt,xlsx,xls|max:5120']);

        $rows = $this->parseFile($request->file('file'));
        if (! is_array($rows) || empty($rows)) {
            return back()->with('error', 'File parsing failed or file is empty.');
        }
        if (! isset($rows[0]) || ! is_array($rows[0])) {
            return back()->with('error', 'File header row is invalid.');
        }
        $headers = $this->normalizeHeaders($rows[0]);

        if (! in_array('name', $headers)) {
            return back()->with('error', 'Kolom "name" wajib ada di baris pertama (header).');
        }

        $warehouse = Warehouse::where('tenant_id', $this->tenantId())->first();
        $created = 0;
        $skipped = 0;
        $updated = 0;
        $errors = [];
        $mode = $request->input('mode', 'skip'); // skip | update

        foreach (array_slice($rows, 1) as $i => $row) {
            if (! is_array($row) || count($row) < 1 || empty(trim($row[0] ?? ''))) {
                continue;
            }
            $data = $this->mapRow($headers, $row);
            $name = trim($data['name'] ?? '');
            if (! $name) {
                $skipped++;

                continue;
            }

            $validator = Validator::make($data, [
                'name' => 'required|string|max:255',
                'price_sell' => 'nullable|numeric|min:0',
                'price_buy' => 'nullable|numeric|min:0',
                'stock_min' => 'nullable|integer|min:0',
            ]);
            if ($validator->fails()) {
                $errors[] = 'Baris '.($i + 2).': '.implode(', ', $validator->errors()->all());

                continue;
            }

            $existing = Product::where('tenant_id', $this->tenantId())->where('name', $name)->first();

            if ($existing && $mode === 'update') {
                $existing->update(array_filter([
                    'barcode' => $data['barcode'] ?? null,
                    'category' => $data['category'] ?? null,
                    'unit' => $data['unit'] ?: null,
                    'price_sell' => isset($data['price_sell']) && $data['price_sell'] !== '' ? (float) $data['price_sell'] : null,
                    'price_buy' => isset($data['price_buy']) && $data['price_buy'] !== '' ? (float) $data['price_buy'] : null,
                    'stock_min' => isset($data['stock_min']) && $data['stock_min'] !== '' ? (int) $data['stock_min'] : null,
                ], fn ($v) => $v !== null));
                $updated++;

                continue;
            } elseif ($existing) {
                $skipped++;

                continue;
            }

            $sku = ! empty($data['sku']) ? trim($data['sku'])
                : strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 6)).'-'.rand(100, 999);

            $product = Product::create([
                'tenant_id' => $this->tenantId(),
                'name' => $name,
                'sku' => $sku,
                'barcode' => $data['barcode'] ?? null,
                'category' => $data['category'] ?? null,
                'unit' => $data['unit'] ?? 'pcs',
                'price_sell' => (float) ($data['price_sell'] ?? 0),
                'price_buy' => (float) ($data['price_buy'] ?? 0),
                'stock_min' => (int) ($data['stock_min'] ?? 5),
                'description' => $data['description'] ?? null,
                'is_active' => true,
            ]);

            if ($warehouse && ! empty($data['initial_stock']) && (int) $data['initial_stock'] > 0) {
                ProductStock::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'quantity' => (int) $data['initial_stock'],
                ]);
            }
            $created++;
        }

        ActivityLog::record('bulk_import', "Import produk: {$created} dibuat, {$updated} diperbarui, {$skipped} dilewati");

        return back()->with('import_result', compact('created', 'skipped', 'updated', 'errors') + ['type' => 'products']);
    }

    // ─── Customers ────────────────────────────────────────────────

    public function importCustomers(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt,xlsx,xls|max:5120']);

        $rows = $this->parseFile($request->file('file'));
        if (! is_array($rows) || empty($rows)) {
            return back()->with('error', 'File parsing failed or file is empty.');
        }
        if (! isset($rows[0]) || ! is_array($rows[0])) {
            return back()->with('error', 'File header row is invalid.');
        }
        $headers = $this->normalizeHeaders($rows[0]);

        if (! in_array('name', $headers)) {
            return back()->with('error', 'Kolom "name" wajib ada di baris pertama (header).');
        }

        $created = 0;
        $skipped = 0;
        $updated = 0;
        $errors = [];
        $mode = $request->input('mode', 'skip');

        foreach (array_slice($rows, 1) as $i => $row) {
            if (empty(trim($row[0] ?? ''))) {
                continue;
            }
            $data = $this->mapRow($headers, $row);
            $name = trim($data['name'] ?? '');
            if (! $name) {
                $skipped++;

                continue;
            }

            $existing = Customer::where('tenant_id', $this->tenantId())->where('name', $name)->first();

            if ($existing && $mode === 'update') {
                $existing->update(array_filter([
                    'email' => $data['email'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'company' => $data['company'] ?? null,
                    'address' => $data['address'] ?? null,
                    'npwp' => $data['npwp'] ?? null,
                    'credit_limit' => isset($data['credit_limit']) && $data['credit_limit'] !== '' ? (float) $data['credit_limit'] : null,
                ], fn ($v) => $v !== null));
                $updated++;
            } elseif ($existing) {
                $skipped++;
            } else {
                Customer::create([
                    'tenant_id' => $this->tenantId(),
                    'name' => $name,
                    'email' => $data['email'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'company' => $data['company'] ?? null,
                    'address' => $data['address'] ?? null,
                    'npwp' => $data['npwp'] ?? null,
                    'credit_limit' => (float) ($data['credit_limit'] ?? 0),
                    'is_active' => true,
                ]);
                $created++;
            }
        }

        ActivityLog::record('bulk_import', "Import customer: {$created} dibuat, {$updated} diperbarui, {$skipped} dilewati");

        return back()->with('import_result', compact('created', 'skipped', 'updated', 'errors') + ['type' => 'customers']);
    }

    // ─── Suppliers ────────────────────────────────────────────────

    public function importSuppliers(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt,xlsx,xls|max:5120']);

        $rows = $this->parseFile($request->file('file'));
        if (! is_array($rows) || empty($rows)) {
            return back()->with('error', 'File parsing failed or file is empty.');
        }
        if (! isset($rows[0]) || ! is_array($rows[0])) {
            return back()->with('error', 'File header row is invalid.');
        }
        $headers = $this->normalizeHeaders($rows[0]);

        if (! in_array('name', $headers)) {
            return back()->with('error', 'Kolom "name" wajib ada di baris pertama (header).');
        }

        $created = 0;
        $skipped = 0;
        $updated = 0;
        $errors = [];
        $mode = $request->input('mode', 'skip');

        foreach (array_slice($rows, 1) as $i => $row) {
            if (empty(trim($row[0] ?? ''))) {
                continue;
            }
            $data = $this->mapRow($headers, $row);
            $name = trim($data['name'] ?? '');
            if (! $name) {
                $skipped++;

                continue;
            }

            $existing = Supplier::where('tenant_id', $this->tenantId())->where('name', $name)->first();

            if ($existing && $mode === 'update') {
                $existing->update(array_filter([
                    'email' => $data['email'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'company' => $data['company'] ?? null,
                    'address' => $data['address'] ?? null,
                    'npwp' => $data['npwp'] ?? null,
                    'bank_name' => $data['bank_name'] ?? null,
                    'bank_account' => $data['bank_account'] ?? null,
                    'bank_holder' => $data['bank_holder'] ?? null,
                ], fn ($v) => $v !== null));
                $updated++;
            } elseif ($existing) {
                $skipped++;
            } else {
                Supplier::create([
                    'tenant_id' => $this->tenantId(),
                    'name' => $name,
                    'email' => $data['email'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'company' => $data['company'] ?? null,
                    'address' => $data['address'] ?? null,
                    'npwp' => $data['npwp'] ?? null,
                    'bank_name' => $data['bank_name'] ?? null,
                    'bank_account' => $data['bank_account'] ?? null,
                    'bank_holder' => $data['bank_holder'] ?? null,
                    'is_active' => true,
                ]);
                $created++;
            }
        }

        ActivityLog::record('bulk_import', "Import supplier: {$created} dibuat, {$updated} diperbarui, {$skipped} dilewati");

        return back()->with('import_result', compact('created', 'skipped', 'updated', 'errors') + ['type' => 'suppliers']);
    }

    // ─── Employees ────────────────────────────────────────────────

    public function importEmployees(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt,xlsx,xls|max:5120']);

        $rows = $this->parseFile($request->file('file'));
        if (! is_array($rows) || empty($rows)) {
            return back()->with('error', 'File parsing failed or file is empty.');
        }
        if (! isset($rows[0]) || ! is_array($rows[0])) {
            return back()->with('error', 'File header row is invalid.');
        }
        $headers = $this->normalizeHeaders($rows[0]);

        if (! in_array('name', $headers)) {
            return back()->with('error', 'Kolom "name" wajib ada di baris pertama (header).');
        }

        $created = 0;
        $skipped = 0;
        $updated = 0;
        $errors = [];
        $mode = $request->input('mode', 'skip');
        $counter = Employee::where('tenant_id', $this->tenantId())->count();

        foreach (array_slice($rows, 1) as $i => $row) {
            if (empty(trim($row[0] ?? ''))) {
                continue;
            }
            $data = $this->mapRow($headers, $row);
            $name = trim($data['name'] ?? '');
            if (! $name) {
                $skipped++;

                continue;
            }

            $joinDate = null;
            if (! empty($data['join_date'])) {
                try {
                    $joinDate = Carbon::parse($data['join_date'])->format('Y-m-d');
                } catch (\Exception $e) {
                }
            }

            $existing = Employee::where('tenant_id', $this->tenantId())->where('name', $name)->first();

            if ($existing && $mode === 'update') {
                $existing->update(array_filter([
                    'email' => $data['email'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'position' => $data['position'] ?? null,
                    'department' => $data['department'] ?? null,
                    'salary' => isset($data['salary']) && $data['salary'] !== '' ? (float) $data['salary'] : null,
                    'bank_name' => $data['bank_name'] ?? null,
                    'bank_account' => $data['bank_account'] ?? null,
                    'address' => $data['address'] ?? null,
                ], fn ($v) => $v !== null));
                $updated++;
            } elseif ($existing) {
                $skipped++;
            } else {
                $counter++;
                Employee::create([
                    'tenant_id' => $this->tenantId(),
                    'name' => $name,
                    'email' => $data['email'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'position' => $data['position'] ?? null,
                    'department' => $data['department'] ?? null,
                    'join_date' => $joinDate ?? today(),
                    'status' => 'active',
                    'salary' => (float) ($data['salary'] ?? 0),
                    'bank_name' => $data['bank_name'] ?? null,
                    'bank_account' => $data['bank_account'] ?? null,
                    'address' => $data['address'] ?? null,
                    'employee_id' => $data['employee_id'] ?? ('EMP-'.str_pad($counter, 4, '0', STR_PAD_LEFT)),
                ]);
                $created++;
            }
        }

        ActivityLog::record('bulk_import', "Import karyawan: {$created} dibuat, {$updated} diperbarui, {$skipped} dilewati");

        return back()->with('import_result', compact('created', 'skipped', 'updated', 'errors') + ['type' => 'employees']);
    }

    // ─── Warehouses ───────────────────────────────────────────────

    public function importWarehouses(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt,xlsx,xls|max:5120']);

        $rows = $this->parseFile($request->file('file'));
        if (! is_array($rows) || empty($rows)) {
            return back()->with('error', 'File parsing failed or file is empty.');
        }
        if (! isset($rows[0]) || ! is_array($rows[0])) {
            return back()->with('error', 'File header row is invalid.');
        }
        $headers = $this->normalizeHeaders($rows[0]);

        if (! in_array('name', $headers)) {
            return back()->with('error', 'Kolom "name" wajib ada di baris pertama (header).');
        }

        $created = 0;
        $skipped = 0;
        $updated = 0;
        $errors = [];
        $mode = $request->input('mode', 'skip');

        foreach (array_slice($rows, 1) as $i => $row) {
            if (empty(trim($row[0] ?? ''))) {
                continue;
            }
            $data = $this->mapRow($headers, $row);
            $name = trim($data['name'] ?? '');
            if (! $name) {
                $skipped++;

                continue;
            }

            $existing = Warehouse::where('tenant_id', $this->tenantId())->where('name', $name)->first();

            if ($existing && $mode === 'update') {
                $existing->update(array_filter([
                    'code' => $data['code'] ?? null,
                    'address' => $data['address'] ?? null,
                ], fn ($v) => $v !== null));
                $updated++;
            } elseif ($existing) {
                $skipped++;
            } else {
                Warehouse::create([
                    'tenant_id' => $this->tenantId(),
                    'name' => $name,
                    'code' => $data['code'] ?? strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 5)),
                    'address' => $data['address'] ?? null,
                    'is_active' => true,
                ]);
                $created++;
            }
        }

        ActivityLog::record('bulk_import', "Import gudang: {$created} dibuat, {$updated} diperbarui, {$skipped} dilewati");

        return back()->with('import_result', compact('created', 'skipped', 'updated', 'errors') + ['type' => 'warehouses']);
    }

    // ─── Chart of Accounts ────────────────────────────────────────

    public function importChartOfAccounts(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt,xlsx,xls|max:5120']);

        $rows = $this->parseFile($request->file('file'));
        if (! is_array($rows) || empty($rows)) {
            return back()->with('error', 'File parsing failed or file is empty.');
        }
        if (! isset($rows[0]) || ! is_array($rows[0])) {
            return back()->with('error', 'File header row is invalid.');
        }
        $headers = $this->normalizeHeaders($rows[0]);

        $required = ['code', 'name', 'type'];
        foreach ($required as $col) {
            if (! in_array($col, $headers)) {
                return back()->with('error', "Kolom wajib tidak ditemukan: \"{$col}\".");
            }
        }

        $validTypes = ['asset', 'liability', 'equity', 'revenue', 'expense', 'cogs'];
        $created = 0;
        $skipped = 0;
        $updated = 0;
        $errors = [];
        $mode = $request->input('mode', 'skip');

        foreach (array_slice($rows, 1) as $i => $row) {
            if (empty(trim($row[0] ?? ''))) {
                continue;
            }
            $data = $this->mapRow($headers, $row);

            $code = trim($data['code'] ?? '');
            $name = trim($data['name'] ?? '');
            $type = strtolower(trim($data['type'] ?? ''));

            if (! $code || ! $name) {
                $skipped++;

                continue;
            }

            if (! in_array($type, $validTypes)) {
                $errors[] = 'Baris '.($i + 2).": Tipe \"{$type}\" tidak valid. Gunakan: ".implode(', ', $validTypes);

                continue;
            }

            $normalBalance = in_array($type, ['asset', 'expense', 'cogs']) ? 'debit' : 'credit';
            $level = strlen(preg_replace('/[^0-9]/', '', $code)) <= 1 ? 1 : (strlen(preg_replace('/[^0-9]/', '', $code)) <= 2 ? 2 : 3);
            $isHeader = ! empty($data['is_header']) && in_array(strtolower($data['is_header']), ['1', 'yes', 'ya', 'true']);

            // Find parent by code prefix
            $parentId = null;
            if (strlen($code) > 1) {
                $parentCode = substr($code, 0, -1);
                while (strlen($parentCode) > 0) {
                    $parent = ChartOfAccount::where('tenant_id', $this->tenantId())->where('code', $parentCode)->first();
                    if ($parent) {
                        $parentId = $parent->id;
                        break;
                    }
                    $parentCode = substr($parentCode, 0, -1);
                }
            }

            $existing = ChartOfAccount::where('tenant_id', $this->tenantId())->where('code', $code)->first();

            if ($existing && $mode === 'update') {
                $existing->update(array_filter([
                    'name' => $name,
                    'type' => $type,
                    'normal_balance' => $normalBalance,
                    'description' => $data['description'] ?? null,
                    'is_header' => $isHeader,
                    'parent_id' => $parentId,
                ], fn ($v) => $v !== null));
                $updated++;
            } elseif ($existing) {
                $skipped++;
            } else {
                ChartOfAccount::create([
                    'tenant_id' => $this->tenantId(),
                    'code' => $code,
                    'name' => $name,
                    'type' => $type,
                    'normal_balance' => $normalBalance,
                    'level' => $level,
                    'is_header' => $isHeader,
                    'is_active' => true,
                    'description' => $data['description'] ?? null,
                    'parent_id' => $parentId,
                ]);
                $created++;
            }
        }

        ActivityLog::record('bulk_import', "Import CoA: {$created} dibuat, {$updated} diperbarui, {$skipped} dilewati");

        return back()->with('import_result', compact('created', 'skipped', 'updated', 'errors') + ['type' => 'coa']);
    }

    // ═══════════════════════════════════════════════════════════════
    // BULK EXPORT — download master data as CSV
    // ═══════════════════════════════════════════════════════════════

    public function exportProducts()
    {
        $tid = $this->tenantId();
        $products = Product::where('tenant_id', $tid)->where('is_active', true)
            ->with('productStocks')
            ->orderBy('name')->get();

        $headers = ['name', 'sku', 'barcode', 'category', 'unit', 'price_sell', 'price_buy', 'stock_min', 'total_stock', 'description'];

        $rows = $products->map(fn ($p) => [
            $p->name,
            $p->sku,
            $p->barcode,
            $p->category,
            $p->unit,
            $p->price_sell,
            $p->price_buy,
            $p->stock_min,
            $p->productStocks->sum('quantity'),
            $p->description,
        ]);

        return $this->downloadCsv('export-produk-'.date('Ymd'), $headers, $rows);
    }

    public function exportCustomers()
    {
        $tid = $this->tenantId();
        $items = Customer::where('tenant_id', $tid)->orderBy('name')->get();

        $headers = ['name', 'email', 'phone', 'company', 'address', 'npwp', 'credit_limit', 'is_active'];
        $rows = $items->map(fn ($c) => [
            $c->name,
            $c->email,
            $c->phone,
            $c->company,
            $c->address,
            $c->npwp,
            $c->credit_limit,
            $c->is_active ? '1' : '0',
        ]);

        return $this->downloadCsv('export-customer-'.date('Ymd'), $headers, $rows);
    }

    public function exportSuppliers()
    {
        $tid = $this->tenantId();
        $items = Supplier::where('tenant_id', $tid)->orderBy('name')->get();

        $headers = ['name', 'email', 'phone', 'company', 'address', 'npwp', 'bank_name', 'bank_account', 'bank_holder', 'is_active'];
        $rows = $items->map(fn ($s) => [
            $s->name,
            $s->email,
            $s->phone,
            $s->company,
            $s->address,
            $s->npwp,
            $s->bank_name,
            $s->bank_account,
            $s->bank_holder,
            $s->is_active ? '1' : '0',
        ]);

        return $this->downloadCsv('export-supplier-'.date('Ymd'), $headers, $rows);
    }

    public function exportEmployees()
    {
        $tid = $this->tenantId();
        $items = Employee::where('tenant_id', $tid)->orderBy('name')->get();

        $headers = ['name', 'employee_id', 'email', 'phone', 'position', 'department', 'join_date', 'status', 'salary', 'bank_name', 'bank_account', 'address'];
        $rows = $items->map(fn ($e) => [
            $e->name,
            $e->employee_id,
            $e->email,
            $e->phone,
            $e->position,
            $e->department,
            $e->join_date?->format('Y-m-d'),
            $e->status,
            $e->salary,
            $e->bank_name,
            $e->bank_account,
            $e->address,
        ]);

        return $this->downloadCsv('export-karyawan-'.date('Ymd'), $headers, $rows);
    }

    public function exportWarehouses()
    {
        $tid = $this->tenantId();
        $items = Warehouse::where('tenant_id', $tid)->orderBy('name')->get();

        $headers = ['name', 'code', 'address', 'is_active'];
        $rows = $items->map(fn ($w) => [$w->name, $w->code, $w->address, $w->is_active ? '1' : '0']);

        return $this->downloadCsv('export-gudang-'.date('Ymd'), $headers, $rows);
    }

    public function exportChartOfAccounts()
    {
        $tid = $this->tenantId();
        $items = ChartOfAccount::where('tenant_id', $tid)->orderBy('code')->get();

        $headers = ['code', 'name', 'type', 'normal_balance', 'level', 'is_header', 'description'];
        $rows = $items->map(fn ($a) => [
            $a->code,
            $a->name,
            $a->type,
            $a->normal_balance,
            $a->level,
            $a->is_header ? '1' : '0',
            $a->description,
        ]);

        return $this->downloadCsv('export-coa-'.date('Ymd'), $headers, $rows);
    }

    // ─── CSV Template Download ────────────────────────────────────

    public function downloadTemplate(string $type)
    {
        $templates = [
            'products' => [
                'headers' => ['name', 'sku', 'barcode', 'category', 'unit', 'price_sell', 'price_buy', 'stock_min', 'initial_stock', 'description'],
                'examples' => ['Produk Contoh', 'SKU-001', '8991234567890', 'Kategori A', 'pcs', '10000', '7000', '5', '100', 'Deskripsi produk'],
            ],
            'customers' => [
                'headers' => ['name', 'email', 'phone', 'company', 'address', 'npwp', 'credit_limit'],
                'examples' => ['PT Maju Jaya', 'info@majujaya.com', '02112345678', 'PT Maju Jaya', 'Jakarta Selatan', '01.234.567.8-901.000', '5000000'],
            ],
            'suppliers' => [
                'headers' => ['name', 'email', 'phone', 'company', 'address', 'npwp', 'bank_name', 'bank_account', 'bank_holder'],
                'examples' => ['CV Sumber Makmur', 'info@sumber.com', '02198765432', 'CV Sumber Makmur', 'Bandung', '02.345.678.9-012.000', 'BCA', '1234567890', 'CV Sumber Makmur'],
            ],
            'employees' => [
                'headers' => ['name', 'employee_id', 'email', 'phone', 'position', 'department', 'join_date', 'salary', 'bank_name', 'bank_account', 'address'],
                'examples' => ['Budi Santoso', 'EMP-001', 'budi@email.com', '08123456789', 'Staff', 'Operasional', '2024-01-01', '3000000', 'BCA', '1234567890', 'Jakarta'],
            ],
            'warehouses' => [
                'headers' => ['name', 'code', 'address'],
                'examples' => ['Gudang Utama', 'GU-01', 'Jl. Industri No. 1, Jakarta'],
            ],
            'coa' => [
                'headers' => ['code', 'name', 'type', 'is_header', 'description'],
                'examples' => ['1100', 'Kas & Bank', 'asset', '1', 'Akun kas dan bank'],
            ],
        ];

        abort_if(! isset($templates[$type]), 404);

        $t = $templates[$type];
        $csv = implode(',', $t['headers'])."\n".implode(',', $t['examples'])."\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"template-import-{$type}.csv\"",
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════════════

    /**
     * Parse CSV or Excel file into array of rows.
     */
    private function parseFile($file): array
    {
        $ext = strtolower($file->getClientOriginalExtension());

        if (in_array($ext, ['xlsx', 'xls'])) {
            return $this->parseExcel($file);
        }

        return $this->parseCsv($file);
    }

    private function parseCsv($file): array
    {
        $rows = [];
        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
            // Detect BOM and skip
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }
            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                $rows[] = $row;
            }
            fclose($handle);
        }

        return $rows;
    }

    private function parseExcel($file): array
    {
        $rows = [];
        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            foreach ($sheet->toArray() as $row) {
                $rows[] = array_map(fn ($v) => (string) ($v ?? ''), $row);
            }
        } catch (\Throwable $e) {
            // Fallback: try as CSV
            return $this->parseCsv($file);
        }

        return $rows;
    }

    private function normalizeHeaders(array $row): array
    {
        return array_map(fn ($h) => strtolower(trim(str_replace(["\xEF\xBB\xBF", "\r", "\n"], '', $h))), $row);
    }

    private function mapRow(array $headers, array $row): array
    {
        return array_combine($headers, array_pad(array_map('trim', $row), count($headers), ''));
    }

    private function downloadCsv(string $filename, array $headers, $rows): Response
    {
        $csv = "\xEF\xBB\xBF"; // UTF-8 BOM for Excel compat
        $csv .= implode(',', $headers)."\n";

        foreach ($rows as $row) {
            $csv .= implode(',', array_map(function ($v) {
                $v = str_replace('"', '""', (string) $v);

                return str_contains($v, ',') || str_contains($v, '"') || str_contains($v, "\n") ? "\"{$v}\"" : $v;
            }, is_array($row) ? $row : $row->toArray()))."\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ]);
    }
}
