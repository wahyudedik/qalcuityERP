# Package Module Validation Bugfix Design

## Overview

Sistem ERP multi-tenant tidak memvalidasi kesesuaian antara paket langganan (`SubscriptionPlan`) dan modul yang diaktifkan (`enabled_modules`) pada setiap tenant. Akibatnya, tenant dengan paket Starter bisa mengaktifkan modul advanced seperti `manufacturing`, `fleet`, atau `wms` secara bebas melalui Settings > Modules.

Bug ini terjadi di empat titik:
1. `ModuleSettingsController::update()` — tidak memfilter modul berdasarkan plan
2. `TenantController::updatePlan()` — tidak menyesuaikan `enabled_modules` saat plan berubah
3. `SubscriptionPlan::features` — disimpan sebagai string bebas, tidak ter-mapping ke module key
4. Tidak ada middleware yang memblokir akses route modul berdasarkan plan

Strategi fix: tambahkan `PlanModuleMap` sebagai sumber kebenaran mapping plan→modul, validasi di controller, sync saat plan berubah, dan middleware `CheckModulePlanAccess` untuk route-level gating.

## Glossary

- **Bug_Condition (C)**: Kondisi di mana tenant mengaktifkan atau mengakses modul yang tidak diizinkan oleh plannya
- **Property (P)**: Perilaku yang diharapkan — sistem menolak aktivasi/akses modul di luar izin plan dengan error yang jelas
- **Preservation**: Perilaku yang tidak boleh berubah — tenant lama (null), super_admin, dan tenant dengan plan yang memang mengizinkan modul tersebut tetap berjalan normal
- **PlanModuleMap**: Kelas baru di `app/Services/PlanModuleMap.php` yang menjadi sumber kebenaran mapping `plan_slug → []module_keys`
- **isBugCondition**: Fungsi yang menentukan apakah suatu request/akses melanggar batas plan
- **enabled_modules**: Kolom JSON di tabel `tenants` yang menyimpan daftar module key yang aktif
- **ALL_MODULES**: Konstanta di `ModuleRecommendationService` berisi 35 module key yang valid
- **plan_slug**: Nilai slug plan seperti `starter`, `business`, `professional`, `enterprise`

## Bug Details

### Bug Condition

Bug termanifestasi ketika tenant mengirim request update modul yang menyertakan module key di luar yang diizinkan plannya, atau ketika plan berubah tanpa menyesuaikan `enabled_modules`, atau ketika tenant mengakses route modul yang tidak diizinkan plannya.

**Formal Specification:**
```
FUNCTION isBugCondition(input)
  INPUT: input berupa { tenant, requestedModules[], routeModule? }
  OUTPUT: boolean

  IF tenant.plan_slug IS NULL THEN
    RETURN false  // tenant lama tanpa plan, backward compat
  END IF

  allowedModules := PlanModuleMap::getAllowedModules(tenant.plan_slug)

  IF input.requestedModules IS NOT NULL THEN
    RETURN EXISTS module IN input.requestedModules
           WHERE module NOT IN allowedModules
  END IF

  IF input.routeModule IS NOT NULL THEN
    RETURN input.routeModule NOT IN allowedModules
           AND tenant.enabled_modules IS NOT NULL
  END IF

  RETURN false
END FUNCTION
```

### Examples

- Tenant plan `starter` mengirim `modules = ['pos', 'inventory', 'manufacturing']` → **bug**: `manufacturing` tidak diizinkan di starter
- Super-admin mengubah plan tenant dari `professional` ke `starter` tanpa menyesuaikan `enabled_modules` → **bug**: `enabled_modules` masih berisi `fleet`, `wms`
- Tenant plan `starter` mengakses route `/fleet/vehicles` → **bug**: tidak ada middleware yang memblokir
- Tenant plan `business` mengaktifkan `crm`, `helpdesk`, `subscription_billing` → **tidak bug**: semua diizinkan di business
- Tenant dengan `enabled_modules = null` mengakses modul apapun → **tidak bug**: backward compat, semua diizinkan

## Expected Behavior

### Preservation Requirements

**Unchanged Behaviors:**
- Tenant dengan `enabled_modules = null` (tenant lama) tetap dapat mengakses semua modul tanpa pembatasan
- Tenant yang mengaktifkan modul yang memang diizinkan plannya tetap tersimpan dengan benar ke `enabled_modules`
- Super-admin (`role = super_admin`) tetap memiliki akses penuh ke semua route tanpa pembatasan plan
- Tenant dengan plan `professional` atau `enterprise` tetap dapat mengaktifkan modul advanced (`manufacturing`, `fleet`, `wms`)
- `isModuleEnabled()` tetap mengembalikan `true` hanya untuk modul yang ada di array `enabled_modules`
- Tenant dengan status `expired` atau `suspended` tetap diarahkan ke halaman subscription expired via `CheckTenantActive`

**Scope:**
Semua input yang TIDAK melibatkan aktivasi modul di luar batas plan atau akses route modul yang tidak diizinkan tidak boleh terpengaruh oleh fix ini. Ini mencakup:
- Semua operasi non-modul (profil, pengaturan umum, laporan yang diizinkan)
- Akses super-admin ke semua halaman
- Tenant lama dengan `enabled_modules = null`

## Hypothesized Root Cause

Berdasarkan analisis kode:

1. **Tidak ada PlanModuleMap**: Tidak ada mapping terstruktur antara `plan_slug` dan module key yang diizinkan. `SubscriptionPlan::features` hanya menyimpan string display seperti `"Manufaktur (BOM & MRP)"` yang tidak bisa digunakan untuk validasi programatik terhadap `ALL_MODULES`.

2. **ModuleSettingsController::update() tidak memvalidasi plan**: Validasi di baris `'modules.*' => ['string', 'in:' . implode(',', ModuleRecommendationService::ALL_MODULES)]` hanya mengecek apakah module key ada di `ALL_MODULES`, tidak mengecek apakah module key tersebut diizinkan oleh plan tenant yang sedang login.

3. **TenantController::updatePlan() tidak sync enabled_modules**: Setelah `$tenant->update($data)`, tidak ada logika yang menyesuaikan `enabled_modules` dengan modul yang diizinkan plan baru. Selain itu, validasi `'plan' => 'required|in:trial,basic,pro,enterprise'` menggunakan slug legacy yang tidak konsisten dengan `SubscriptionPlan::slug` (`starter`, `business`, `professional`, `enterprise`).

4. **Tidak ada middleware plan gating**: `HealthcareAccessMiddleware` adalah satu-satunya contoh middleware module check, tapi hanya untuk modul `healthcare`. Tidak ada middleware generik yang bisa digunakan untuk modul lain berdasarkan plan.

## Correctness Properties

Property 1: Bug Condition - Plan Gating pada Aktivasi Modul

_For any_ request update modul di mana `isBugCondition` bernilai true (tenant meminta modul di luar izin plannya), fungsi `ModuleSettingsController::update()` yang sudah diperbaiki SHALL menolak request tersebut dengan HTTP 422 dan mengembalikan pesan error yang menyebutkan modul mana yang tidak diizinkan.

**Validates: Requirements 2.1**

Property 2: Bug Condition - Sync enabled_modules saat Plan Berubah

_For any_ operasi `updatePlan` di mana plan baru memiliki allowed modules yang lebih sedikit dari plan lama, fungsi `TenantController::updatePlan()` yang sudah diperbaiki SHALL secara otomatis menghapus modul yang tidak diizinkan dari `enabled_modules` tenant.

**Validates: Requirements 2.2**

Property 3: Bug Condition - Route-Level Plan Gating

_For any_ request ke route modul yang tidak diizinkan oleh plan tenant (dan `enabled_modules` bukan null), middleware `CheckModulePlanAccess` SHALL mengembalikan HTTP 403 dengan pesan yang menjelaskan bahwa modul tersebut memerlukan upgrade paket.

**Validates: Requirements 2.4**

Property 4: Preservation - Backward Compatibility null enabled_modules

_For any_ tenant dengan `enabled_modules = null`, semua pengecekan plan gating SHALL melewati tenant tersebut tanpa pembatasan, mempertahankan perilaku backward compat yang sudah ada.

**Validates: Requirements 3.1, 3.3**

Property 5: Preservation - Aktivasi Modul yang Diizinkan

_For any_ request update modul di mana semua modul yang diminta memang diizinkan oleh plan tenant, sistem SHALL menyimpan pilihan tersebut ke `enabled_modules` dengan benar, sama seperti perilaku sebelum fix.

**Validates: Requirements 3.2, 3.4**

## Fix Implementation

### Changes Required

Asumsi root cause analysis di atas benar:

**File baru: `app/Services/PlanModuleMap.php`**

Buat kelas statis yang menjadi sumber kebenaran mapping plan slug ke module key yang diizinkan:

```
CLASS PlanModuleMap
  CONST PLAN_MODULES = [
    'starter'      => ['pos', 'inventory', 'sales', 'invoicing', 'reports'],
    'business'     => ['pos', 'inventory', 'purchasing', 'sales', 'invoicing',
                       'crm', 'accounting', 'budget', 'helpdesk', 'commission',
                       'consignment', 'subscription_billing', 'reimbursement', 'reports'],
    'professional' => ALL_MODULES minus ['telecom', 'hotel', 'fnb', 'spa'],
    'enterprise'   => ALL_MODULES,
    'trial'        => ['pos', 'inventory', 'sales', 'invoicing', 'reports'],
  ]

  FUNCTION getAllowedModules(plan_slug): array
    RETURN PLAN_MODULES[plan_slug] ?? PLAN_MODULES['starter']
  END FUNCTION

  FUNCTION isModuleAllowedForPlan(module_key, plan_slug): bool
    RETURN module_key IN getAllowedModules(plan_slug)
  END FUNCTION

  FUNCTION filterAllowedModules(modules[], plan_slug): array
    RETURN modules WHERE module IN getAllowedModules(plan_slug)
  END FUNCTION
END CLASS
```

**File: `app/Http/Controllers/ModuleSettingsController.php`**

Tambahkan validasi plan gating di method `update()`:

```
FUNCTION update(request)
  // ... validasi existing ...

  tenant := auth().user().tenant
  plan_slug := tenant.subscriptionPlan?.slug ?? tenant.plan

  // NEW: filter modul berdasarkan plan
  allowedModules := PlanModuleMap::getAllowedModules(plan_slug)
  disallowedModules := newModules WHERE module NOT IN allowedModules

  IF disallowedModules IS NOT EMPTY THEN
    RETURN back().withErrors([
      'modules' => 'Modul berikut tidak diizinkan untuk paket ' + plan_slug + ': ' + join(disallowedModules)
    ])
  END IF

  // ... sisa logika existing ...
END FUNCTION
```

**File: `app/Http/Controllers/SuperAdmin/TenantController.php`**

Perbaiki `updatePlan()` untuk sync `enabled_modules` dan perbaiki validasi slug:

```
FUNCTION updatePlan(request, tenant)
  data := request.validate([
    'plan'                 => 'required|in:trial,starter,business,professional,enterprise',
    'subscription_plan_id' => 'nullable|exists:subscription_plans,id',
    'plan_expires_at'      => 'nullable|date|after:today',
    'trial_ends_at'        => 'nullable|date',
  ])

  tenant.update(data)

  // NEW: sync enabled_modules dengan plan baru
  IF tenant.enabled_modules IS NOT NULL THEN
    newPlanSlug := data['plan']
    allowedModules := PlanModuleMap::getAllowedModules(newPlanSlug)
    syncedModules := tenant.enabled_modules WHERE module IN allowedModules
    tenant.update(['enabled_modules' => syncedModules])
  END IF

  // ... bust cache existing ...
END FUNCTION
```

**File baru: `app/Http/Middleware/CheckModulePlanAccess.php`**

Middleware generik untuk route-level plan gating:

```
CLASS CheckModulePlanAccess
  FUNCTION handle(request, next, moduleKey)
    user := request.user()

    IF NOT user THEN RETURN redirect login END IF
    IF user.isSuperAdmin() THEN RETURN next(request) END IF

    tenant := user.tenant
    IF tenant.enabled_modules IS NULL THEN RETURN next(request) END IF

    plan_slug := tenant.subscriptionPlan?.slug ?? tenant.plan
    IF NOT PlanModuleMap::isModuleAllowedForPlan(moduleKey, plan_slug) THEN
      IF request.expectsJson() THEN
        RETURN response.json(['message' => 'Modul ini memerlukan upgrade paket'], 403)
      END IF
      RETURN redirect.route('subscription.upgrade').with('module', moduleKey)
    END IF

    RETURN next(request)
  END FUNCTION
END CLASS
```

**File: `bootstrap/app.php` atau `app/Http/Kernel.php`**

Daftarkan middleware alias `check.module.plan` → `CheckModulePlanAccess`.

## Testing Strategy

### Validation Approach

Strategi testing mengikuti dua fase: pertama, surface counterexample yang mendemonstrasikan bug pada kode yang belum diperbaiki, kemudian verifikasi fix bekerja benar dan tidak merusak perilaku yang sudah ada.

### Exploratory Bug Condition Checking

**Goal**: Surface counterexample yang mendemonstrasikan bug SEBELUM implementasi fix. Konfirmasi atau refutasi root cause analysis.

**Test Plan**: Tulis test yang mensimulasikan request dari tenant dengan plan terbatas yang mencoba mengaktifkan modul advanced. Jalankan pada kode UNFIXED untuk mengamati kegagalan.

**Test Cases**:
1. **Starter Activates Manufacturing**: Tenant plan `starter` mengirim `modules = ['pos', 'manufacturing']` → seharusnya ditolak, tapi pada kode unfixed akan tersimpan (akan gagal pada unfixed code)
2. **Plan Downgrade No Sync**: Super-admin mengubah plan dari `professional` ke `starter`, `enabled_modules` masih berisi `fleet` → seharusnya di-strip, tapi pada kode unfixed tidak berubah (akan gagal pada unfixed code)
3. **Route Access Without Middleware**: Tenant plan `starter` mengakses route fleet → seharusnya 403, tapi pada kode unfixed tidak ada middleware yang memblokir (akan gagal pada unfixed code)
4. **Legacy Slug Validation**: `updatePlan` dengan `plan = 'starter'` → pada kode unfixed akan gagal validasi karena hanya menerima `trial,basic,pro,enterprise`

**Expected Counterexamples**:
- `enabled_modules` tersimpan dengan modul yang tidak diizinkan plan
- `enabled_modules` tidak berubah setelah plan downgrade
- Route modul advanced dapat diakses oleh tenant plan starter

### Fix Checking

**Goal**: Verifikasi bahwa untuk semua input di mana bug condition berlaku, fungsi yang sudah diperbaiki menghasilkan perilaku yang diharapkan.

**Pseudocode:**
```
FOR ALL input WHERE isBugCondition(input) DO
  result := fixedFunction(input)
  ASSERT expectedBehavior(result)
END FOR
```

### Preservation Checking

**Goal**: Verifikasi bahwa untuk semua input di mana bug condition TIDAK berlaku, fungsi yang sudah diperbaiki menghasilkan hasil yang sama dengan fungsi original.

**Pseudocode:**
```
FOR ALL input WHERE NOT isBugCondition(input) DO
  ASSERT originalFunction(input) = fixedFunction(input)
END FOR
```

**Testing Approach**: Property-based testing direkomendasikan untuk preservation checking karena:
- Menghasilkan banyak test case secara otomatis di seluruh domain input
- Menangkap edge case yang mungkin terlewat oleh unit test manual
- Memberikan jaminan kuat bahwa perilaku tidak berubah untuk semua input non-buggy

**Test Cases**:
1. **Null enabled_modules Preservation**: Tenant dengan `enabled_modules = null` tetap bisa mengakses semua modul setelah fix
2. **Allowed Module Activation Preservation**: Tenant plan `business` mengaktifkan `crm` tetap tersimpan dengan benar
3. **Super Admin Preservation**: Super-admin tetap bisa mengakses semua route tanpa pembatasan
4. **Professional/Enterprise Advanced Modules**: Tenant plan `professional` tetap bisa mengaktifkan `manufacturing`, `fleet`, `wms`

### Unit Tests

- Test `PlanModuleMap::getAllowedModules()` untuk setiap plan slug
- Test `ModuleSettingsController::update()` menolak modul di luar plan dengan HTTP 422
- Test `TenantController::updatePlan()` menyesuaikan `enabled_modules` saat plan downgrade
- Test `CheckModulePlanAccess` middleware mengembalikan 403 untuk modul yang tidak diizinkan
- Test edge case: plan slug tidak dikenal, `enabled_modules` kosong array vs null

### Property-Based Tests

- Generate random kombinasi plan slug + module list, verifikasi bahwa hanya modul yang diizinkan plan tersebut yang bisa disimpan
- Generate random tenant state dengan berbagai plan, verifikasi preservation: tenant dengan `enabled_modules = null` selalu lolos
- Generate random plan downgrade scenarios, verifikasi `enabled_modules` selalu subset dari allowed modules plan baru

### Integration Tests

- Test full flow: tenant starter mencoba aktifkan manufacturing → ditolak → aktifkan pos → berhasil
- Test plan upgrade flow: starter → professional → modul advanced bisa diaktifkan
- Test plan downgrade flow: professional → starter → `enabled_modules` otomatis di-strip
- Test super-admin mengubah plan tenant dan verifikasi `enabled_modules` tersync
