# Dokumen Desain — Audit & Perbaikan Komprehensif Qalcuity ERP

## Overview

Dokumen ini menjabarkan pendekatan teknis untuk audit menyeluruh dan perbaikan komprehensif Qalcuity ERP. Cakupan mencakup 25 area requirement yang dikelompokkan ke dalam lapisan: database, backend (model/controller/service), frontend (Blade/Alpine.js), UI/UX (dark mode, responsivitas), notifikasi, kontrol akses, alur bisnis per modul, performa, keamanan, integrasi eksternal, dan fitur baru.

Pendekatan yang digunakan adalah **audit-then-fix**: setiap area diaudit secara sistematis untuk menemukan semua masalah, kemudian diperbaiki secara batch. Setiap perbaikan harus backward-compatible dan tidak merusak fungsionalitas yang sudah berjalan.

### Prinsip Desain

1. **Non-destructive**: Perbaikan tidak boleh menghapus data atau mengubah skema secara breaking
2. **Tenant-safe**: Semua perubahan harus mempertahankan isolasi data antar tenant
3. **Incremental**: Perbaikan dilakukan per area, dapat di-deploy secara bertahap
4. **Testable**: Setiap perbaikan memiliki kriteria verifikasi yang jelas
5. **Bahasa Indonesia**: Semua teks UI, pesan error, dan notifikasi dalam Bahasa Indonesia

---

## Architecture

### Lapisan Aplikasi

```
┌─────────────────────────────────────────────────────────────┐
│                    Browser / Client                          │
│         Alpine.js 3 + Tailwind CSS 3 + Chart.js 4           │
└─────────────────────┬───────────────────────────────────────┘
                      │ HTTP/HTTPS
┌─────────────────────▼───────────────────────────────────────┐
│                  Laravel 13 / PHP 8.3                        │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌────────────┐  │
│  │Middleware│  │Controller│  │ Service  │  │Notification│  │
│  │  Stack   │  │  Layer   │  │  Layer   │  │  System    │  │
│  └──────────┘  └──────────┘  └──────────┘  └────────────┘  │
│  ┌──────────────────────────────────────────────────────┐   │
│  │              Eloquent ORM + Models                    │   │
│  │         (BelongsToTenant global scope)                │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────┬───────────────────────────────────────┘
                      │
┌─────────────────────▼───────────────────────────────────────┐
│                    MySQL Database                            │
│              (Multi-tenant, tenant_id isolation)             │
└─────────────────────────────────────────────────────────────┘
```

### Middleware Stack (Urutan Eksekusi)

```
Request
  → web (session, cookies, CSRF)
  → auth (autentikasi)
  → verified (email verification)
  → CheckTenantActive (tenant aktif?)
  → EnforceTenantIsolation (isolasi data)
  → CheckModulePlanAccess (akses modul per paket)
  → role / permission (RBAC)
  → Controller
```

### Strategi Audit

Setiap area audit mengikuti pola:

```
1. SCAN   → Identifikasi semua instance yang perlu diperiksa
2. VERIFY → Periksa setiap instance terhadap kriteria
3. FIX    → Perbaiki instance yang tidak memenuhi kriteria
4. TEST   → Verifikasi perbaikan tidak merusak fungsionalitas lain
```

---

## Components and Interfaces

### 1. Database Layer — ENUM & Schema Fixes

**Komponen yang terlibat:**
- `database/migrations/` — semua file migration
- `app/Models/` — definisi `$casts` dan konstanta status
- `app/Http/Requests/` — aturan validasi `in:` untuk ENUM

**Pola perbaikan ENUM:**

```php
// Migration (definisi sumber kebenaran)
$table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'voided', 'paid']);

// Model (konstanta untuk type safety)
class Invoice extends Model {
    const STATUS_DRAFT   = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_VOIDED  = 'voided';
    const STATUS_PAID    = 'paid';

    protected $casts = [
        'status' => 'string',
    ];
}

// Form Request (validasi input)
'status' => ['required', Rule::in(Invoice::STATUSES)],
```

**Tabel yang perlu diaudit (prioritas tinggi):**
- `invoices.status` — tambahkan `voided`
- `sales_orders.status` — sinkronkan dengan controller
- `purchase_orders.status` — sinkronkan dengan controller
- `rooms.status` — Hotel module
- `guests.vip_level` — Hotel module
- `housekeeping_tasks.deep_cleaning` — Hotel module
- Semua tabel dengan kolom `status`, `type`, `level`, `category`

### 2. Route & Controller Layer

**Komponen yang terlibat:**
- `routes/web.php`, `routes/api.php`, `routes/auth.php`, `routes/healthcare.php`
- `app/Http/Controllers/` — semua controller dan subdirektori

**Pola verifikasi route:**

```php
// Setiap route harus memiliki:
// 1. Controller class yang ada di filesystem
// 2. Method yang ada di controller tersebut
// 3. Middleware yang terdaftar di bootstrap/app.php
// 4. Named route yang konsisten dengan penggunaan di view

// Contoh route yang benar:
Route::resource('invoices', InvoiceController::class)
    ->middleware(['auth', 'tenant.isolation', 'permission:sales,view']);
```

**Halaman error yang harus ada:**
- `resources/views/errors/403.blade.php` — Akses ditolak (Bahasa Indonesia)
- `resources/views/errors/404.blade.php` — Halaman tidak ditemukan
- `resources/views/errors/500.blade.php` — Error server

### 3. Model & Service Layer

**Komponen yang terlibat:**
- `app/Models/` — semua Eloquent model
- `app/Services/` — semua service class
- `app/Traits/BelongsToTenant.php` — trait isolasi tenant

**Checklist model:**

```php
// Model tenant-scoped yang benar:
class Invoice extends Model {
    use BelongsToTenant;      // WAJIB untuk semua model tenant
    use AuditsChanges;         // untuk model dengan data sensitif
    use HasTransactionIsolation; // untuk model keuangan

    protected $fillable = [...]; // atau $guarded = []

    // Relasi harus mereferensikan model yang ada
    public function tenant(): BelongsTo {
        return $this->belongsTo(Tenant::class);
    }
}
```

### 4. View & Blade Layer

**Komponen yang terlibat:**
- `resources/views/` — semua file Blade
- `resources/views/components/` — Blade components
- `resources/views/layouts/` — layout templates

**Pola view yang aman:**

```blade
{{-- Null-safe access untuk data yang mungkin null --}}
{{ $invoice?->customer?->name ?? 'Tidak ada pelanggan' }}

{{-- Pengecekan sebelum akses relasi --}}
@if($invoice->payments->isNotEmpty())
    @foreach($invoice->payments as $payment)
        ...
    @endforeach
@endif

{{-- CSRF dan method spoofing --}}
<form method="POST" action="{{ route('invoices.update', $invoice) }}">
    @csrf
    @method('PUT')
    ...
</form>
```

### 5. Dark Mode System

**Komponen yang terlibat:**
- `resources/views/layouts/app.blade.php` — script deteksi tema
- `resources/js/app.js` — Alpine.js theme store
- Semua file Blade — class dark mode

**Arsitektur dark mode:**

```html
<!-- Di <head>, SEBELUM konten lain (mencegah FOUC) -->
<script>
    (function() {
        const theme = localStorage.getItem('theme') || 'system';
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (theme === 'dark' || (theme === 'system' && prefersDark)) {
            document.documentElement.classList.add('dark');
        }
    })();
</script>
```

```javascript
// Alpine.js store untuk theme management
Alpine.store('theme', {
    current: localStorage.getItem('theme') || 'system',
    toggle() {
        this.current = this.current === 'dark' ? 'light' : 'dark';
        localStorage.setItem('theme', this.current);
        this.apply();
    },
    apply() {
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const isDark = this.current === 'dark' || (this.current === 'system' && prefersDark);
        document.documentElement.classList.toggle('dark', isDark);
    }
});
```

**Kelas Tailwind dark mode yang wajib ada di setiap komponen:**

| Komponen | Light | Dark |
|----------|-------|------|
| Background halaman | `bg-gray-50` | `dark:bg-gray-900` |
| Card/Panel | `bg-white` | `dark:bg-gray-800` |
| Teks utama | `text-gray-900` | `dark:text-gray-100` |
| Teks sekunder | `text-gray-500` | `dark:text-gray-400` |
| Border | `border-gray-200` | `dark:border-gray-700` |
| Input | `bg-white border-gray-300` | `dark:bg-gray-700 dark:border-gray-600` |
| Tabel header | `bg-gray-50` | `dark:bg-gray-700` |
| Tabel row hover | `hover:bg-gray-50` | `dark:hover:bg-gray-700` |

### 6. Notification System

**Komponen yang terlibat:**
- `app/Notifications/` — semua notification class
- `app/Jobs/SendErpNotificationBatch.php` — batch notification job
- `resources/views/notifications/` — template notifikasi

**Notification class yang perlu dibuat (modul yang belum ada):**

```
Purchasing:
  - PurchaseOrderApprovedNotification
  - GoodsReceivedNotification

HRM:
  - LeaveApprovedNotification
  - LeaveRejectedNotification
  - ContractExpiryNotification

Payroll:
  - PayrollProcessedNotification (sudah ada, verifikasi)
  - PayslipAvailableNotification

POS:
  - CashierSessionOpenedNotification
  - CashierSessionClosedNotification

Project:
  - TaskAssignedNotification
  - DeadlineApproachingNotification

Asset:
  - MaintenanceDueNotification (sudah ada, verifikasi)

Manufacturing:
  - WorkOrderCompletedNotification
  - MaterialShortageNotification

Construction:
  - ProjectMilestoneNotification
  - BudgetExceededNotification (sudah ada, verifikasi)

Agriculture:
  - HarvestReminderNotification
  - PlantingScheduleNotification
```

**Interface notifikasi yang konsisten:**

```php
class ExampleNotification extends Notification implements ShouldQueue {
    use Queueable;

    public function via(object $notifiable): array {
        // Hormati preferensi pengguna
        return $notifiable->getNotificationChannels(static::class);
    }

    public function toMail(object $notifiable): MailMessage { ... }
    public function toArray(object $notifiable): array { ... }
    public function toBroadcast(object $notifiable): BroadcastMessage { ... }
}
```

### 7. Access Control Layer

**Komponen yang terlibat:**
- `app/Http/Middleware/CheckModulePlanAccess.php`
- `app/Http/Middleware/EnforceTenantIsolation.php`
- `app/Services/PlanModuleMap.php` (atau config)
- `resources/views/layouts/sidebar.blade.php`

**PlanModuleMap — struktur yang benar:**

```php
// config/plan_modules.php atau PlanModuleMap service
return [
    'starter' => [
        'accounting', 'inventory', 'sales', 'purchasing', 'pos', 'reports'
    ],
    'professional' => [
        'accounting', 'inventory', 'sales', 'purchasing', 'pos', 'reports',
        'hrm', 'payroll', 'crm', 'project', 'asset', 'analytics'
    ],
    'enterprise' => [
        // semua modul
    ],
    // modul industri spesifik sebagai add-on
];
```

**Sidebar filtering logic:**

```blade
@foreach($menuItems as $item)
    @if(
        auth()->user()->canAccessModule($item['module']) &&
        auth()->user()->hasPermission($item['module'], 'view') &&
        tenant()->isModuleActive($item['module'])
    )
        <x-sidebar-item :item="$item" />
    @endif
@endforeach
```

### 8. Business Flow Integrity

**Alur Sales (state machine):**

```
Quotation (draft → sent → accepted/rejected)
    ↓ accepted
Sales Order (draft → confirmed → processing → delivered → completed/cancelled)
    ↓ confirmed
Delivery Order (draft → picking → shipped → delivered)
    ↓ delivered
Invoice (draft → sent → partial_paid → paid → voided)
    ↓ paid
Journal Entry (posted → locked)
```

**Invariant yang harus dijaga:**
- Setiap transisi status harus valid (tidak bisa skip state)
- Setiap transaksi keuangan harus menghasilkan jurnal dengan debit = kredit
- Pembatalan/void harus membuat jurnal pembalik
- Stok harus selalu ≥ 0 (kecuali konfigurasi allow negative stock)

### 9. Performance & Security

**Index database yang wajib ada:**

```sql
-- Semua tabel tenant-scoped
CREATE INDEX idx_tenant_id ON table_name (tenant_id);

-- Query yang sering dijalankan
CREATE INDEX idx_invoices_status_date ON invoices (tenant_id, status, invoice_date);
CREATE INDEX idx_stock_product_warehouse ON stock_movements (tenant_id, product_id, warehouse_id);
CREATE INDEX idx_journal_period ON journal_entries (tenant_id, accounting_period_id, posted_at);
```

**Cache strategy:**

```php
// Cache key selalu menyertakan tenant_id
$cacheKey = "dashboard_stats_{$tenantId}_{$period}";
Cache::remember($cacheKey, 300, fn() => $this->calculateStats());

// Invalidasi cache saat data berubah
Cache::forget("dashboard_stats_{$tenantId}_*");
```

**Security headers (middleware AddSecurityHeaders):**

```php
$response->headers->set('X-Frame-Options', 'SAMEORIGIN');
$response->headers->set('X-Content-Type-Options', 'nosniff');
$response->headers->set('X-XSS-Protection', '1; mode=block');
$response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
$response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
```

---

## Data Models

### Model Audit Checklist

Setiap model harus memenuhi:

| Kriteria | Wajib | Keterangan |
|----------|-------|------------|
| `use BelongsToTenant` | Ya (jika tenant-scoped) | Auto-filter by tenant_id |
| `$fillable` atau `$guarded` | Ya | Mencegah mass assignment |
| `$casts` sesuai kolom | Ya | Type safety |
| Relasi valid | Ya | Model yang direferensikan harus ada |
| `use AuditsChanges` | Untuk model sensitif | Audit trail otomatis |

### Struktur Notification Preference

```php
// Tabel: notification_preferences
// tenant_id, user_id, notification_type, channel (in_app/email/push), enabled (bool)

class NotificationPreference extends Model {
    use BelongsToTenant;

    protected $fillable = [
        'user_id', 'notification_type', 'channel', 'enabled'
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];
}
```

### Cache Key Convention

```
Format: {feature}_{tenant_id}_{identifier}_{period?}

Contoh:
- dashboard_stats_123_monthly_2025-01
- sidebar_menu_123_user_456
- module_access_123_professional
- notification_prefs_123_user_456
```

---

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system — essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

Library PBT yang digunakan: **`giorgiosironi/eris`** (sudah ada di composer.json sebagai dev dependency).

### Property 1: Tenant Data Isolation

*For any* query yang dilakukan oleh pengguna dari tenant A, semua record yang dikembalikan harus memiliki `tenant_id` yang sama dengan tenant A, dan tidak boleh ada record dari tenant lain.

**Validates: Requirements 23.3**

### Property 2: Journal Entry Balance Invariant

*For any* jurnal umum yang berhasil disimpan ke database, jumlah total debit harus selalu sama dengan jumlah total kredit (selisih = 0).

**Validates: Requirements 10.2**

### Property 3: Stock Consistency Invariant

*For any* urutan operasi stok (penerimaan, pengeluaran, transfer, penyesuaian) pada sebuah produk di sebuah gudang, stok akhir harus sama dengan stok awal ditambah semua penerimaan dikurangi semua pengeluaran.

**Validates: Requirements 11.1**

### Property 4: ENUM Validation Rejection

*For any* nilai yang tidak ada dalam daftar ENUM yang valid untuk sebuah kolom status, sistem harus menolak penyimpanan nilai tersebut dan mengembalikan error validasi, sementara data yang sudah ada tidak berubah.

**Validates: Requirements 1.2, 1.3**

### Property 5: Notification Preference Round-Trip

*For any* kombinasi tipe notifikasi dan channel (in_app, email, push), menyimpan preferensi kemudian membacanya kembali harus menghasilkan nilai yang identik dengan yang disimpan.

**Validates: Requirements 7.3**

---

## Error Handling

### Strategi Error Handling Per Layer

**Database Layer:**
- ENUM mismatch → `ValidationException` dengan pesan deskriptif dalam Bahasa Indonesia
- Constraint violation → log ke `error_logs` tabel, tampilkan pesan user-friendly
- Deadlock → retry otomatis maksimal 3 kali dengan exponential backoff

**Controller Layer:**
- `ModelNotFoundException` → redirect ke halaman daftar dengan flash message
- `AuthorizationException` → halaman 403 dengan pesan Bahasa Indonesia
- `ValidationException` → kembali ke form dengan error per field
- Exception tidak terduga → log ke `error_logs`, tampilkan halaman 500 yang informatif

**Service Layer:**
- External API failure → log error, throw `IntegrationException` yang ditangkap controller
- Queue job failure → retry dengan backoff, notifikasi admin setelah max retries

**Frontend Layer:**
- Axios error → tampilkan toast notification dengan pesan error
- Alpine.js error → graceful degradation, jangan crash seluruh halaman

### Halaman Error Standar

```blade
{{-- resources/views/errors/403.blade.php --}}
<x-error-layout title="Akses Ditolak">
    <h1>403 — Akses Ditolak</h1>
    <p>Anda tidak memiliki izin untuk mengakses halaman ini.</p>
    <a href="{{ route('dashboard') }}">Kembali ke Dashboard</a>
</x-error-layout>
```

### Logging Strategy

```php
// Semua error dicatat dengan konteks yang cukup
Log::error('ENUM mismatch pada invoice status', [
    'tenant_id' => auth()->user()?->tenant_id,
    'user_id'   => auth()->id(),
    'value'     => $value,
    'valid'     => Invoice::STATUSES,
    'trace'     => $e->getTraceAsString(),
]);
```

---

## Testing Strategy

### Pendekatan Dual Testing

Audit dan perbaikan ini menggunakan dua pendekatan testing yang saling melengkapi:

**1. Unit & Feature Tests (PHPUnit)**
- Verifikasi perbaikan spesifik per area
- Test alur bisnis dengan contoh konkret
- Test edge case dan kondisi error
- Lokasi: `tests/Feature/Audit/` dan `tests/Unit/Audit/`

**2. Property-Based Tests (Eris)**
- Verifikasi invariant yang harus berlaku untuk semua input
- Minimum 100 iterasi per property test
- Lokasi: `tests/Property/`

### Struktur Test

```
tests/
├── Feature/
│   └── Audit/
│       ├── DatabaseEnumTest.php
│       ├── RouteIntegrityTest.php
│       ├── ModelTenantScopeTest.php
│       ├── DarkModeTest.php
│       ├── NotificationTest.php
│       ├── AccessControlTest.php
│       └── BusinessFlowTest.php
├── Unit/
│   └── Audit/
│       ├── JournalBalanceTest.php
│       ├── StockCalculationTest.php
│       └── EnumValidationTest.php
└── Property/
    ├── TenantIsolationPropertyTest.php
    ├── JournalBalancePropertyTest.php
    ├── StockConsistencyPropertyTest.php
    ├── EnumValidationPropertyTest.php
    └── NotificationPreferencePropertyTest.php
```

### Property Test Configuration (Eris)

```php
// tests/Property/TenantIsolationPropertyTest.php
use Eris\TestTrait;
use Eris\Generator;

class TenantIsolationPropertyTest extends TestCase {
    use TestTrait;

    /**
     * Feature: erp-comprehensive-audit-fix
     * Property 1: Tenant Data Isolation
     */
    public function test_tenant_isolation_property(): void {
        $this
            ->limitTo(100)
            ->forAll(
                Generator\choose(1, 1000), // tenant_id A
                Generator\choose(1, 1000)  // tenant_id B (berbeda)
            )
            ->when(fn($a, $b) => $a !== $b)
            ->then(function($tenantIdA, $tenantIdB) {
                // Query sebagai tenant A tidak boleh mengembalikan data tenant B
                $results = Invoice::forTenant($tenantIdA)->get();
                $this->assertTrue(
                    $results->every(fn($r) => $r->tenant_id === $tenantIdA)
                );
            });
    }
}
```

```php
// tests/Property/JournalBalancePropertyTest.php
/**
 * Feature: erp-comprehensive-audit-fix
 * Property 2: Journal Entry Balance Invariant
 */
public function test_journal_balance_invariant(): void {
    $this
        ->limitTo(100)
        ->forAll(
            Generator\seq(Generator\tuple(
                Generator\pos(),  // debit amount
                Generator\pos()   // credit amount
            ))
        )
        ->then(function($entries) {
            // Buat jurnal dengan debit = kredit
            $balanced = $this->makeBalancedJournal($entries);
            $journal = JournalEntry::create($balanced);

            $this->assertEquals(
                $journal->lines->sum('debit'),
                $journal->lines->sum('credit')
            );
        });
}
```

### Smoke Tests (Audit Konfigurasi)

```php
// tests/Feature/Audit/ModelTenantScopeTest.php
public function test_all_tenant_models_use_belongs_to_tenant_trait(): void {
    $tenantModels = $this->getTenantScopedModels();

    foreach ($tenantModels as $modelClass) {
        $this->assertContains(
            BelongsToTenant::class,
            class_uses_recursive($modelClass),
            "Model {$modelClass} harus menggunakan trait BelongsToTenant"
        );
    }
}
```

### Integration Tests (Business Flow)

```php
// tests/Feature/Audit/BusinessFlowTest.php
public function test_complete_sales_flow(): void {
    // Quotation → SO → DO → Invoice → Payment → Journal
    $quotation = Quotation::factory()->create(['status' => 'draft']);
    $so = $quotation->convertToSalesOrder();
    $do = $so->createDeliveryOrder();
    $invoice = $do->createInvoice();
    $payment = $invoice->recordPayment(['amount' => $invoice->total]);

    $this->assertEquals('paid', $invoice->fresh()->status);
    $this->assertNotNull($payment->journalEntry);
    $this->assertEquals(0, $payment->journalEntry->balance); // debit = kredit
}
```

### Kriteria Penerimaan Testing

- Semua property test lulus dengan minimum 100 iterasi
- Semua smoke test lulus (konfigurasi benar)
- Semua integration test lulus (alur bisnis benar)
- Code coverage minimal 70% untuk area yang diaudit
- Zero PHP errors/warnings di log setelah semua perbaikan diterapkan
