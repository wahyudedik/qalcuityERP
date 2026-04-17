# Role & Permission Security Audit — Bugfix Design

## Overview

Audit keamanan pada sistem role & permission aplikasi ERP Laravel multi-tenant ini menemukan 6 kategori bug yang berpotensi menyebabkan kebocoran data antar tenant, privilege escalation, dan bypass authorization. Dokumen ini merinci analisis teknis, root cause, dan rencana perbaikan untuk setiap bug.

Pendekatan perbaikan bersifat **minimal dan targeted**: setiap fix hanya mengubah komponen yang bermasalah tanpa merombak arsitektur yang sudah berjalan dengan benar.

---

## Glossary

- **Bug_Condition (C)**: Kondisi input yang memicu bug — kombinasi dari role user, route yang diakses, dan state middleware
- **Property (P)**: Perilaku yang diharapkan ketika bug condition terpenuhi — akses ditolak atau diizinkan sesuai aturan
- **Preservation**: Perilaku yang tidak boleh berubah setelah fix — akses role yang sudah benar harus tetap berjalan
- **`App\Services\PermissionService`**: Service utama di `app/Services/PermissionService.php` yang menggunakan `$user->role` (field yang benar) dan `ROLE_DEFAULTS` untuk menentukan permission
- **`App\Services\Security\PermissionService`**: Service sekunder di `app/Services/Security/PermissionService.php` yang digunakan oleh `CheckPermissionMiddleware`, menggunakan `$user->role_name` (field yang **tidak ada** di model User)
- **`EnforceTenantIsolation`**: Middleware di `app/Http/Middleware/EnforceTenantIsolation.php` yang memvalidasi `tenant_id` dari route model binding
- **`BelongsToTenant`**: Trait yang digunakan model-model bertenant untuk auto-scope query berdasarkan `tenant_id`
- **`isSuperAdmin()`**: Method di `App\Models\User` yang memeriksa `$this->role === 'super_admin'` (dengan underscore)
- **`hasRole(string $role)`**: Method di `App\Models\User` yang memeriksa `in_array($this->role, (array) $roles)`

---

## Bug Details

### Bug 1 — Dual PermissionService: Inkonsistensi Field

`CheckPermissionMiddleware` menggunakan `App\Services\Security\PermissionService` yang memanggil `isAdmin()` dengan memeriksa `$user->role_name`. Field `role_name` tidak ada di model `User` — field yang benar adalah `role`. Akibatnya `isAdmin()` selalu mengembalikan `false`, dan semua user dianggap bukan admin oleh middleware ini.

Sementara itu, `PermissionMiddleware` menggunakan `App\Services\PermissionService` yang memeriksa `$user->role` dengan benar. Dua middleware ini bekerja dengan logika berbeda untuk endpoint yang berbeda.

**Formal Specification:**
```
FUNCTION isBugCondition_Bug1(user, middleware)
  INPUT: user: User model, middleware: string
  OUTPUT: boolean

  IF middleware = 'CheckPermissionMiddleware'
    RETURN user.role IN ['admin', 'super_admin']
           AND Security\PermissionService.isAdmin(user) = false
           // karena isAdmin() memeriksa user.role_name yang tidak ada
  END IF

  RETURN false
END FUNCTION
```

**Contoh Manifestasi:**
- User dengan `role = 'admin'` mengakses route yang dilindungi `CheckPermissionMiddleware` → ditolak (seharusnya diizinkan)
- User dengan `role = 'admin'` mengakses route yang dilindungi `PermissionMiddleware` → diizinkan (benar)
- Dua route dengan proteksi berbeda menghasilkan keputusan berbeda untuk user yang sama

---

### Bug 2 — Role Name Mismatch: `superadmin` vs `super_admin`

`RBACMiddleware` dan `HealthcareAccessMiddleware` melakukan bypass check dengan `$user->hasRole('superadmin')` (tanpa underscore). Namun `User::hasRole()` memeriksa `$this->role`, dan nilai yang tersimpan di database adalah `'super_admin'` (dengan underscore) sesuai definisi `isSuperAdmin()`.

**Formal Specification:**
```
FUNCTION isBugCondition_Bug2(user, middleware)
  INPUT: user: User model, middleware: string
  OUTPUT: boolean

  IF middleware IN ['RBACMiddleware', 'HealthcareAccessMiddleware', 'MedicalRecordPolicy', 'PatientDataPolicy']
    RETURN user.role = 'super_admin'
           AND middleware.bypassCheck(user) = false
           // karena hasRole('superadmin') != hasRole('super_admin')
  END IF

  RETURN false
END FUNCTION
```

**Contoh Manifestasi:**
- Super admin mengakses `/healthcare/patients` → ditolak dengan 403 (seharusnya bypass)
- `$user->hasRole('superadmin')` → `false` meskipun `$user->role = 'super_admin'`
- `$user->isSuperAdmin()` → `true` (method yang benar, tidak digunakan di middleware ini)

---

### Bug 3 — Missing Tenant Isolation pada Route Sensitif

Lima kelompok route sensitif hanya menggunakan middleware `auth` tanpa `tenant.isolation`:

| Route | Risiko |
|-------|--------|
| `/barcode/print`, `/barcode/auto-generate` | User tenant A cetak barcode produk tenant B |
| `/inventory/movements/*` | User tenant A manipulasi stok tenant B |
| `/bulk-actions/execute`, `/bulk-actions/export-download` | Staff/kasir eksekusi bulk action tanpa role check |
| `/api/quick-search`, `/api/saved-searches/*` | Hasil pencarian bocor ke tenant lain |
| `/transaction-chain/{type}/{id}` | User tebak ID untuk lihat transaksi tenant lain |

**Formal Specification:**
```
FUNCTION isBugCondition_Bug3(request)
  INPUT: request: HTTP Request
  OUTPUT: boolean

  sensitiveRoutes = [
    '/barcode/print', '/barcode/auto-generate',
    '/inventory/movements/*',
    '/bulk-actions/execute', '/bulk-actions/export-download',
    '/api/quick-search', '/api/saved-searches/*',
    '/transaction-chain/{type}/{id}'
  ]

  RETURN request.path MATCHES sensitiveRoutes
         AND 'tenant.isolation' NOT IN request.middleware
         AND request.user.tenant_id IS NOT NULL
END FUNCTION
```

---

### Bug 4 — Customer Portal Tanpa Tenant Isolation

Route group `/portal/*` hanya menggunakan middleware `auth`. Tidak ada `tenant.isolation` middleware, sehingga user dari tenant A berpotensi mengakses data portal customer dari tenant B.

**Formal Specification:**
```
FUNCTION isBugCondition_Bug4(request)
  INPUT: request: HTTP Request
  OUTPUT: boolean

  RETURN request.path STARTS_WITH '/portal/'
         AND 'tenant.isolation' NOT IN request.middleware
         AND request.user.tenant_id IS NOT NULL
END FUNCTION
```

---

### Bug 5 — Supplier Scorecard Tanpa Role Check

Route `/supplier-scorecards/*` dan `/supplier-performance/*` menggunakan `['auth', 'tenant.isolation']` tanpa pembatasan role. User dengan role `staff`, `kasir`, atau `gudang` dapat mengakses data scorecard supplier yang bersifat sensitif.

**Formal Specification:**
```
FUNCTION isBugCondition_Bug5(user, request)
  INPUT: user: User model, request: HTTP Request
  OUTPUT: boolean

  unauthorizedRoles = ['staff', 'kasir', 'gudang', 'housekeeping', 'maintenance', 'affiliate']

  RETURN request.path STARTS_WITH '/supplier-scorecards/' OR '/supplier-performance/'
         AND user.role IN unauthorizedRoles
END FUNCTION
```

---

### Bug 6 — EnforceTenantIsolation Tidak Mencakup Semua Model

`EnforceTenantIsolation` memiliki daftar `$tenantModels` yang tidak mencakup model-model berikut meskipun semuanya menggunakan trait `BelongsToTenant` dan memiliki `tenant_id`:

- `ErpNotification` (tabel: `erp_notifications`)
- `UserPermission`
- `CustomField`
- `DocumentTemplate`
- `Workflow`
- `WebhookSubscription` (sudah ada di list tapi perlu verifikasi)
- `AiTourSession`

**Formal Specification:**
```
FUNCTION isBugCondition_Bug6(model, request)
  INPUT: model: Eloquent Model instance dari route binding, request: HTTP Request
  OUTPUT: boolean

  missingModels = [
    ErpNotification, UserPermission, CustomField,
    DocumentTemplate, Workflow, AiTourSession
  ]

  RETURN model.class IN missingModels
         AND model.tenant_id IS NOT NULL
         AND model.tenant_id != request.user.tenant_id
         AND EnforceTenantIsolation.tenantModels NOT CONTAINS model.class
END FUNCTION
```

---

## Expected Behavior

### Preservation Requirements

**Perilaku yang tidak boleh berubah setelah fix:**
- User dengan role `admin` tetap mendapat akses penuh ke semua resource dalam tenant mereka
- User dengan role `super_admin` tetap dapat mengakses data tenant manapun dengan audit trail
- User dengan role `manager` tetap mendapat akses sesuai `ROLE_DEFAULTS['manager']`
- User dengan role `kasir` tetap dapat mengakses modul POS (`pos.view`, `pos.create`)
- User dengan role `gudang` tetap dapat mengakses modul inventory dan warehouse
- Per-user permission override di tabel `UserPermission` tetap diprioritaskan di atas role default
- Healthcare module tetap dapat diakses oleh `doctor`, `nurse`, `receptionist`, `pharmacist`, `lab_technician`
- Admin tenant tetap dapat mengelola user dalam tenant mereka melalui `/users/*`
- Model tanpa `tenant_id` (model global/shared) tetap tidak divalidasi oleh `EnforceTenantIsolation`
- Halaman publik tetap dapat diakses tanpa autentikasi

**Scope:**
Semua input yang tidak memenuhi bug condition (user dengan role yang benar, route yang sudah aman, model yang sudah terdaftar) tidak boleh terpengaruh oleh fix ini.

---

## Hypothesized Root Cause

### Bug 1 — Dual PermissionService
**Root Cause**: Terdapat dua implementasi `PermissionService` dengan namespace berbeda yang dibuat secara independen. `App\Services\Security\PermissionService` dibuat untuk sistem permission berbasis database (Role/Permission model), sementara `App\Services\PermissionService` menggunakan pendekatan ROLE_DEFAULTS berbasis konfigurasi. Saat `isAdmin()` ditulis di service sekunder, developer menggunakan `$user->role_name` yang mungkin terinspirasi dari sistem Spatie/Laravel-Permission (yang menggunakan `role_name`), bukan field `role` yang digunakan di model User aplikasi ini.

### Bug 2 — Role Name Mismatch
**Root Cause**: Inkonsistensi konvensi penamaan. `isSuperAdmin()` di model User menggunakan `'super_admin'` (snake_case dengan underscore), tetapi developer yang menulis `RBACMiddleware` dan `HealthcareAccessMiddleware` menggunakan `'superadmin'` (tanpa underscore) yang lebih umum di library pihak ketiga seperti Spatie.

### Bug 3 — Missing Tenant Isolation
**Root Cause**: Route-route ini kemungkinan ditambahkan secara bertahap (feature-by-feature) tanpa security review yang konsisten. Developer fokus pada fungsionalitas dan menambahkan `auth` middleware, tetapi lupa menambahkan `tenant.isolation`. Route `transaction-chain` bahkan berada di luar group middleware utama.

### Bug 4 — Customer Portal
**Root Cause**: Route group `/portal/*` didefinisikan di luar group middleware utama yang sudah menyertakan `tenant.isolation`. Kemungkinan ditambahkan belakangan sebagai fitur terpisah tanpa mengikuti pola yang sudah ada.

### Bug 5 — Supplier Scorecard
**Root Cause**: Developer menganggap `tenant.isolation` sudah cukup untuk proteksi, tanpa mempertimbangkan bahwa data scorecard supplier bersifat sensitif dan perlu pembatasan role tambahan. Tidak ada role check yang didefinisikan untuk modul `suppliers` di level route.

### Bug 6 — EnforceTenantIsolation
**Root Cause**: Daftar `$tenantModels` di `EnforceTenantIsolation` diisi secara manual dan tidak ada mekanisme otomatis untuk mendeteksi model baru yang menggunakan `BelongsToTenant`. Model-model yang hilang kemungkinan ditambahkan ke codebase setelah middleware ditulis, tanpa update pada daftar tersebut.

---

## Correctness Properties

Property 1: Bug Condition — PermissionService Konsisten

_For any_ user dengan role `admin` atau `super_admin`, `CheckPermissionMiddleware` SHALL mengizinkan akses (atau mengevaluasi permission dengan benar) karena `isAdmin()` di `App\Services\Security\PermissionService` menggunakan field `$user->role` yang benar, bukan `$user->role_name` yang tidak ada.

**Validates: Requirements 2.1, 2.2**

---

Property 2: Bug Condition — Superadmin Bypass Konsisten

_For any_ user dengan `role = 'super_admin'`, semua middleware yang melakukan bypass check (`RBACMiddleware`, `HealthcareAccessMiddleware`, `MedicalRecordPolicy`, `PatientDataPolicy`) SHALL mengizinkan akses tanpa pemeriksaan permission tambahan, karena menggunakan `hasRole('super_admin')` yang konsisten dengan nilai di database.

**Validates: Requirements 2.3, 2.4**

---

Property 3: Bug Condition — Tenant Isolation pada Route Sensitif

_For any_ request ke route sensitif (`/barcode/*`, `/inventory/movements/*`, `/bulk-actions/*`, `/api/quick-search`, `/api/saved-searches/*`, `/transaction-chain/*`) dari user dengan `tenant_id`, sistem SHALL memvalidasi bahwa resource yang diakses milik tenant yang sama dengan user yang sedang login.

**Validates: Requirements 2.5, 2.6, 2.7, 2.8**

---

Property 4: Bug Condition — Tenant Isolation pada Customer Portal

_For any_ request ke `/portal/*` dari user yang sudah login, sistem SHALL memvalidasi tenant isolation sehingga user hanya dapat melihat data portal yang terkait dengan tenant mereka sendiri.

**Validates: Requirements 2.9**

---

Property 5: Bug Condition — Role Check pada Supplier Scorecard

_For any_ user dengan role `staff`, `kasir`, `gudang`, `housekeeping`, `maintenance`, atau `affiliate` yang mengakses `/supplier-scorecards/*` atau `/supplier-performance/*`, sistem SHALL menolak akses dengan HTTP 403.

**Validates: Requirements 2.10**

---

Property 6: Bug Condition — EnforceTenantIsolation Mencakup Semua Model

_For any_ request dengan route model binding untuk model `ErpNotification`, `UserPermission`, `CustomField`, `DocumentTemplate`, `Workflow`, atau `AiTourSession` yang memiliki `tenant_id` berbeda dari user yang login, `EnforceTenantIsolation` SHALL menolak akses dengan HTTP 403.

**Validates: Requirements 2.11**

---

Property 7: Preservation — Akses Role yang Sudah Benar

_For any_ input di mana bug condition TIDAK terpenuhi (user dengan role yang benar mengakses route yang sudah aman), sistem SHALL menghasilkan perilaku yang identik dengan sebelum fix — tidak ada perubahan pada keputusan akses untuk skenario yang sudah berjalan dengan benar.

**Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.8, 3.9, 3.10**

---

## Fix Implementation

### Bug 1 — Perbaiki `isAdmin()` di `App\Services\Security\PermissionService`

**File**: `app/Services/Security/PermissionService.php`

**Perubahan**:
```php
// SEBELUM (buggy):
protected function isAdmin($user): bool
{
    return in_array($user->role_name ?? '', ['admin', 'superadmin']);
}

// SESUDAH (fixed):
protected function isAdmin($user): bool
{
    return in_array($user->role ?? '', ['admin', 'super_admin']);
}
```

Perubahan: `role_name` → `role`, dan `'superadmin'` → `'super_admin'` agar konsisten dengan model User.

---

### Bug 2 — Perbaiki Role Name di Middleware Healthcare

**File 1**: `app/Http/Middleware/RBACMiddleware.php`

```php
// SEBELUM (buggy):
if ($user->hasRole('superadmin') || $user->is_superadmin) {

// SESUDAH (fixed):
if ($user->isSuperAdmin()) {
```

Gunakan `isSuperAdmin()` yang sudah terdefinisi dengan benar di model User, hindari duplikasi logika.

**File 2**: `app/Http/Middleware/HealthcareAccessMiddleware.php`

```php
// SEBELUM (buggy):
if ($user->hasRole('superadmin') || $user->is_superadmin) {

// SESUDAH (fixed):
if ($user->isSuperAdmin()) {
```

**File 3**: `app/Policies/MedicalRecordPolicy.php` dan `app/Policies/PatientDataPolicy.php` (jika ada)

Cari semua penggunaan `hasRole('superadmin')` dan ganti dengan `isSuperAdmin()`.

---

### Bug 3 — Tambahkan Tenant Isolation pada Route Sensitif

**File**: `routes/web.php`

**Barcode routes** — tambahkan `tenant.isolation` ke middleware group:
```php
// SEBELUM:
Route::prefix('barcode')->name('barcode.')->group(function () {

// SESUDAH:
Route::prefix('barcode')->name('barcode.')->middleware(['tenant.isolation'])->group(function () {
```

**Inventory movements routes** — tambahkan `tenant.isolation`:
```php
// SEBELUM:
Route::prefix('inventory/movements')->name('inventory.movements.')->group(function () {

// SESUDAH:
Route::prefix('inventory/movements')->name('inventory.movements.')->middleware(['tenant.isolation'])->group(function () {
```

**Bulk actions routes** — tambahkan `tenant.isolation` dan permission check:
```php
// SEBELUM:
Route::post('/bulk-actions/execute', [...])
Route::get('/bulk-actions/export-download', [...])

// SESUDAH:
Route::post('/bulk-actions/execute', [...])
    ->middleware(['tenant.isolation', 'permission:inventory,edit']);
Route::get('/bulk-actions/export-download', [...])
    ->middleware(['tenant.isolation', 'permission:inventory,view']);
```

**Quick search dan saved searches** — tambahkan `tenant.isolation`:
```php
// SEBELUM:
Route::get('/api/quick-search', [...])
Route::prefix('api/saved-searches')->group(function () {

// SESUDAH:
Route::get('/api/quick-search', [...])->middleware(['tenant.isolation']);
Route::prefix('api/saved-searches')->middleware(['tenant.isolation'])->group(function () {
```

**Transaction chain** — pindahkan ke dalam group dengan `tenant.isolation`:
```php
// SEBELUM:
Route::prefix('transaction-chain')->name('transaction-chain.')->middleware('auth')->group(function () {

// SESUDAH:
Route::prefix('transaction-chain')->name('transaction-chain.')->middleware(['auth', 'tenant.isolation'])->group(function () {
```

---

### Bug 4 — Tambahkan Tenant Isolation pada Customer Portal

**File**: `routes/web.php`

```php
// SEBELUM:
Route::prefix('portal')->name('customer-portal.')->middleware('auth')->group(function () {

// SESUDAH:
Route::prefix('portal')->name('customer-portal.')->middleware(['auth', 'tenant.isolation'])->group(function () {
```

---

### Bug 5 — Tambahkan Role Check pada Supplier Scorecard

**File**: `routes/web.php`

```php
// SEBELUM:
Route::prefix('supplier-scorecards')->name('suppliers.')->middleware(['auth', 'tenant.isolation'])->group(function () {
Route::prefix('supplier-performance')->name('supplier-performance.')->middleware(['auth', 'tenant.isolation'])->group(function () {

// SESUDAH:
Route::prefix('supplier-scorecards')->name('suppliers.')->middleware(['auth', 'tenant.isolation', 'permission:suppliers,view'])->group(function () {
Route::prefix('supplier-performance')->name('supplier-performance.')->middleware(['auth', 'tenant.isolation', 'permission:suppliers,view'])->group(function () {
```

Menggunakan `permission:suppliers,view` yang sudah terdefinisi di `ROLE_DEFAULTS` — hanya `admin`, `manager`, dan role dengan akses `suppliers` yang dapat mengakses.

---

### Bug 6 — Tambahkan Model yang Hilang ke EnforceTenantIsolation

**File**: `app/Http/Middleware/EnforceTenantIsolation.php`

Tambahkan model-model berikut ke array `$tenantModels`:

```php
// Tambahkan ke daftar $tenantModels yang sudah ada:
\App\Models\ErpNotification::class,
\App\Models\UserPermission::class,
\App\Models\CustomField::class,
\App\Models\DocumentTemplate::class,
\App\Models\Workflow::class,
\App\Models\AiTourSession::class,
```

Catatan: `WebhookSubscription` sudah ada di daftar. `ErpNotification` menggunakan tabel `erp_notifications` (bukan `notifications`) sehingga tidak konflik dengan model Notification bawaan Laravel.

---

## Testing Strategy

### Validation Approach

Strategi testing mengikuti dua fase: pertama, surface counterexample yang mendemonstrasikan bug pada kode yang belum diperbaiki (exploratory), kemudian verifikasi fix bekerja dengan benar dan tidak merusak perilaku yang sudah ada (preservation).

---

### Exploratory Bug Condition Checking

**Goal**: Konfirmasi root cause sebelum implementasi fix. Jika test tidak gagal seperti yang diharapkan, root cause perlu direvisi.

**Test Cases**:

1. **Bug 1 — isAdmin() selalu false**: Buat user dengan `role = 'admin'`, panggil `Security\PermissionService::isAdmin($user)` → harus mengembalikan `false` pada kode unfixed (membuktikan bug)
2. **Bug 2 — Superadmin bypass gagal**: Buat user dengan `role = 'super_admin'`, simulasikan request ke healthcare route dengan `RBACMiddleware` → harus mendapat 403 pada kode unfixed
3. **Bug 3 — Barcode tanpa tenant isolation**: Buat dua user dari tenant berbeda, user A akses barcode produk tenant B → harus berhasil pada kode unfixed (membuktikan bug)
4. **Bug 4 — Portal tanpa tenant isolation**: User dari tenant A akses `/portal/` → tidak ada validasi tenant pada kode unfixed
5. **Bug 5 — Staff akses scorecard**: User dengan `role = 'staff'` akses `/supplier-scorecards/` → harus berhasil (200) pada kode unfixed
6. **Bug 6 — Model hilang dari isolation check**: Buat `CustomField` dengan `tenant_id = 1`, akses dengan user `tenant_id = 2` → tidak ada 403 pada kode unfixed

**Expected Counterexamples**:
- `isAdmin()` mengembalikan `false` untuk admin karena `role_name` tidak ada di model
- `hasRole('superadmin')` mengembalikan `false` untuk user dengan `role = 'super_admin'`
- Request lintas tenant berhasil pada route yang tidak memiliki `tenant.isolation`

---

### Fix Checking

**Goal**: Verifikasi bahwa untuk semua input di mana bug condition terpenuhi, fungsi yang sudah diperbaiki menghasilkan perilaku yang benar.

**Pseudocode:**
```
FOR ALL user WHERE user.role IN ['admin', 'super_admin'] DO
  result := CheckPermissionMiddleware_fixed(user, anyPermission)
  ASSERT Security\PermissionService_fixed.isAdmin(user) = true
END FOR

FOR ALL user WHERE user.role = 'super_admin' DO
  result := RBACMiddleware_fixed(user, anyHealthcareRoute)
  ASSERT result.status = 200 (bypass)
END FOR

FOR ALL request WHERE request.path MATCHES sensitiveRoutes DO
  ASSERT 'tenant.isolation' IN request.resolvedMiddleware
END FOR

FOR ALL user WHERE user.role IN ['staff', 'kasir', 'gudang'] DO
  result := request('/supplier-scorecards/', user)
  ASSERT result.status = 403
END FOR

FOR ALL model WHERE model IN missingModels AND model.tenant_id != user.tenant_id DO
  result := EnforceTenantIsolation_fixed(model, user)
  ASSERT result.status = 403
END FOR
```

---

### Preservation Checking

**Goal**: Verifikasi bahwa untuk semua input di mana bug condition TIDAK terpenuhi, perilaku tidak berubah.

**Pseudocode:**
```
FOR ALL user WHERE NOT isBugCondition(user, request) DO
  ASSERT original_behavior(user, request) = fixed_behavior(user, request)
END FOR
```

**Testing Approach**: Property-based testing direkomendasikan untuk preservation checking karena:
- Menghasilkan banyak test case otomatis di seluruh domain input
- Menangkap edge case yang mungkin terlewat oleh unit test manual
- Memberikan jaminan kuat bahwa perilaku tidak berubah untuk semua input non-buggy

**Test Cases**:
1. **Admin akses normal**: User `role = 'admin'` mengakses semua modul → tetap diizinkan setelah fix
2. **Manager akses sesuai ROLE_DEFAULTS**: User `role = 'manager'` mengakses modul yang diizinkan → tetap diizinkan
3. **Kasir akses POS**: User `role = 'kasir'` mengakses `/pos/*` → tetap diizinkan
4. **Gudang akses inventory**: User `role = 'gudang'` mengakses `/inventory/*` → tetap diizinkan
5. **Doctor akses healthcare**: User `role = 'doctor'` mengakses healthcare routes → tetap diizinkan
6. **Per-user override tetap berlaku**: User dengan override permission di `UserPermission` → override tetap diprioritaskan
7. **Model global tidak diblokir**: Akses model tanpa `tenant_id` → tetap tidak divalidasi oleh `EnforceTenantIsolation`

---

### Unit Tests

- Test `Security\PermissionService::isAdmin()` dengan user yang memiliki `role = 'admin'` dan `role = 'super_admin'`
- Test `RBACMiddleware::handle()` dengan user `role = 'super_admin'` → harus bypass
- Test `HealthcareAccessMiddleware::handle()` dengan user `role = 'super_admin'` → harus bypass
- Test `EnforceTenantIsolation::handle()` dengan model yang baru ditambahkan ke daftar
- Test route middleware stack untuk setiap route sensitif yang diperbaiki
- Test HTTP 403 untuk role yang tidak diizinkan mengakses supplier scorecard

### Property-Based Tests

- Generate random user dengan berbagai role, verifikasi `isAdmin()` konsisten antara kedua PermissionService
- Generate random pasangan (user, model) dengan tenant berbeda, verifikasi `EnforceTenantIsolation` selalu menolak akses
- Generate random user dengan role non-admin, verifikasi tidak ada akses ke supplier scorecard
- Generate random request ke route sensitif, verifikasi `tenant.isolation` selalu ada di middleware stack

### Integration Tests

- Full flow: super_admin login → akses healthcare module → harus berhasil (end-to-end)
- Full flow: user tenant A → akses barcode produk tenant B → harus 403
- Full flow: user `role = 'staff'` → akses `/supplier-scorecards/` → harus 403
- Full flow: user tenant A → akses `/portal/` → hanya melihat data tenant A
- Full flow: user tenant A → akses `CustomField` milik tenant B via route binding → harus 403
