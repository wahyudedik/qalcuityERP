# Missing Migrations Audit Fix — Bugfix Design

## Overview

Beberapa test di `tests/Feature/DemoData/SampleDataGeneratorPropertyTest.php` di-skip atau gagal karena tabel/kolom yang dibutuhkan oleh industry generator tidak ada atau tidak konsisten dengan yang diharapkan. Root cause utama adalah tabel `doctors` dan `patients` tidak memiliki kolom `tenant_id`, sehingga `HealthcareGenerator` gagal saat insert/query. Selain itu, ditemukan beberapa mismatch kolom antara generator dan skema migrasi yang menyebabkan kegagalan silent (exception di-catch sebagai warning).

Fix approach: tambahkan kolom `tenant_id` ke tabel `doctors` dan `patients` via new migration files, dan perbaiki mismatch kolom lainnya yang ditemukan dari audit mendalam.

---

## Glossary

- **Bug_Condition (C)**: Kondisi yang memicu bug — ketika generator mencoba insert/query dengan `tenant_id` pada tabel yang tidak memiliki kolom tersebut, atau menggunakan kolom yang tidak ada di skema.
- **Property (P)**: Perilaku yang diharapkan — semua generator berhasil membuat data tanpa exception, dan test property 11 berjalan tanpa `markTestSkipped()`.
- **Preservation**: Struktur tabel yang sudah ada dan berfungsi tidak boleh berubah; test property 1-10 tidak boleh regresi.
- **HealthcareGenerator**: `app/Services/DemoData/Generators/HealthcareGenerator.php` — generator data demo untuk industri healthcare.
- **HotelGenerator**: `app/Services/DemoData/Generators/HotelGenerator.php` — generator data demo untuk industri hotel.
- **tenant_id**: Kolom foreign key ke tabel `tenants` yang digunakan untuk isolasi data multi-tenant.
- **markTestSkipped()**: Metode PHPUnit yang menyebabkan test di-skip (bukan fail), dipanggil ketika tabel tidak ditemukan.

---

## Bug Details

### Bug Condition

Bug termanifestasi ketika `SampleDataGeneratorService::generateForIndustry()` dipanggil untuk industri tertentu, dan generator mencoba melakukan operasi DB pada tabel yang tidak memiliki kolom yang diharapkan. Akibatnya exception di-catch sebagai non-fatal warning, data tidak terbuat, dan test property 11 memanggil `markTestSkipped()`.

**Formal Specification:**
```
FUNCTION isBugCondition(input)
  INPUT: input berupa pasangan (industry: string, table: string, column: string)
  OUTPUT: boolean

  RETURN table EXISTS in database
         AND column NOT IN Schema::getColumnListing(table)
         AND generator untuk industry mencoba INSERT/SELECT dengan column tersebut
END FUNCTION
```

### Examples

**Issue 1 — `doctors` table: missing `tenant_id`**
- Generator: `HealthcareGenerator::seedDoctors()` melakukan `DB::table('doctors')->where('tenant_id', $tenantId)` dan `insertGetId(['tenant_id' => $tenantId, ...])`
- Migrasi `2026_04_08_000026_create_doctors_table.php`: tabel dibuat dengan `user_id` (FK ke users) tapi **tidak ada `tenant_id`**
- Akibat: `SQLSTATE[42S22]: Column not found: 1054 Unknown column 'tenant_id'` — exception di-catch — `generatedData['doctors'] = 0`
- Test: `assertHealthcareMinimums()` memanggil `Schema::hasTable('doctors')` — true, tapi `where('tenant_id', ...)` gagal — test fail

**Issue 2 — `patients` table: missing `tenant_id`**
- Generator: `HealthcareGenerator::seedPatients()` melakukan `DB::table('patients')->where('tenant_id', $tenantId)` dan `insertGetId(['tenant_id' => $tenantId, ...])`
- Migrasi `2026_04_08_000001_create_patients_table.php`: tabel dibuat **tanpa `tenant_id`** (hanya `registered_by`, `primary_doctor_id` sebagai FK ke users)
- Akibat: sama seperti Issue 1 — exception di-catch — `generatedData['patients'] = 0`

**Issue 3 — `appointments` table: `tenant_id` ditambahkan via separate migration**
- Migrasi `2026_04_08_000007_create_appointments_table.php`: tabel dibuat **tanpa `tenant_id`**
- Migrasi `2026_04_10_000001_add_tenant_id_to_appointments_table.php`: menambahkan `tenant_id` dengan guard `if (!Schema::hasColumn(...))`
- Status: **Partially fixed** — perlu verifikasi bahwa guard berfungsi benar dan migrasi dijalankan berurutan

**Issue 4 — `housekeeping_tasks` table: type enum mismatch**
- Generator: `HotelGenerator::seedHousekeepingTasks()` menggunakan type values: `'regular_cleaning'`, `'turndown_service'`, `'deep_cleaning'`, `'inspection'`
- Migrasi final `2026_04_05_000012_fix_housekeeping_tasks_table_structure.php`: enum `type` = `['checkout_clean', 'stay_clean', 'deep_clean', 'inspection']`
- Mismatch: `'regular_cleaning'` tidak ada di enum (seharusnya `'stay_clean'`), `'turndown_service'` tidak ada di enum
- Akibat: enum violation — exception di-catch — `generatedData['housekeeping_tasks'] = 0`

**Issue 5 — `housekeeping_tasks` table: missing `actual_duration` column**
- Generator: `HotelGenerator::seedHousekeepingTasks()` menggunakan kolom `'actual_duration'`
- Migrasi final `2026_04_05_000012`: tidak ada kolom `actual_duration` (hanya `estimated_duration`)
- Akibat: `SQLSTATE[42S22]: Column not found: 1054 Unknown column 'actual_duration'`

**Issue 6 — `quality_checks` table: required `inspector_id` FK**
- Generator: `ManufacturingGenerator::seedQualityChecks()` melakukan insert **tanpa** kolom `inspector_id`
- Migrasi `2026_04_09_000003_create_quality_control_tables.php`: `inspector_id` adalah `foreignId('inspector_id')->constrained('users')->onDelete('restrict')` — NOT NULL, no default
- Akibat: `SQLSTATE[HY000]: Field 'inspector_id' doesn't have a default value` — exception di-catch — `generatedData['quality_checks'] = 0`

**Issue 7 — `crop_cycles` table: schema mismatch (dua migrasi berbeda)**
- Ada **dua** migrasi yang membuat `crop_cycles`:
  1. `2026_03_31_000008_create_crop_cycles_table.php`: skema kompleks dengan `farm_plot_id`, `number`, `crop_variety`, `phase`, dll.
  2. `2026_04_06_000010_create_agriculture_tables.php`: skema sederhana dengan `crop_name`, `variety`, `area_hectares`, `growth_stage`, dll. — menggunakan `if (!Schema::hasTable('crop_cycles'))` guard
- Generator `AgricultureGenerator::seedCropCycles()` menggunakan kolom dari skema **kedua** (`crop_name`, `variety`, `area_hectares`, `growth_stage`, `estimated_yield_tons`, `actual_yield_tons`, `status`)
- Jika migrasi pertama dijalankan lebih dulu, tabel sudah ada dengan skema berbeda, guard di migrasi kedua skip. Generator kemudian gagal karena kolom tidak cocok.
- Akibat: `SQLSTATE[42S22]: Column not found` untuk kolom seperti `area_hectares`, `growth_stage`

**Issue 8 — `doctors` table: `doctor_number` unique constraint tanpa `tenant_id` scope**
- Migrasi: `$table->string('doctor_number')->unique()` — unique global, bukan per-tenant
- Generator: menggunakan `doctor_number` seperti `'DR-HC-001'` yang akan conflict jika dua tenant berbeda membuat data demo
- Akibat: `SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'DR-HC-001'` pada test multi-tenant (Property 6)

**Issue 9 — `patients` table: `medical_record_number` dan `nik` unique constraint tanpa `tenant_id` scope**
- Migrasi: `$table->string('medical_record_number')->unique()` dan `$table->string('nik', 16)->nullable()->unique()` — unique global
- Generator: menggunakan fixed values seperti `'MR-HC-0001'` dan `'3201010101800001'`
- Akibat: sama seperti Issue 8 — conflict pada multi-tenant test

---

## Expected Behavior

### Preservation Requirements

**Unchanged Behaviors:**
- Semua test property 1-10 harus tetap lulus tanpa regresi
- Struktur tabel yang sudah ada dan berfungsi (hotel, restaurant, manufacturing, retail, services, agriculture, construction) tidak boleh berubah
- Generator untuk industri yang sudah berfungsi harus tetap menghasilkan data dengan jumlah yang sama
- Migrasi yang sudah ada tidak boleh dimodifikasi — hanya tambah migrasi baru

**Scope:**
Semua industri selain `healthcare` yang sudah berfungsi harus tetap berfungsi. Fix hanya boleh menambah kolom/constraint yang missing, tidak mengubah yang sudah ada.

---

## Hypothesized Root Cause

Berdasarkan audit mendalam terhadap semua migration files dan generator files:

1. **`doctors` table dibuat tanpa `tenant_id`**: Migrasi `2026_04_08_000026_create_doctors_table.php` menggunakan `user_id` sebagai identifier utama (model dokter sebagai user), bukan sebagai entitas multi-tenant. Ini inkonsisten dengan semua tabel lain yang menggunakan `tenant_id`.

2. **`patients` table dibuat tanpa `tenant_id`**: Migrasi `2026_04_08_000001_create_patients_table.php` juga tidak memiliki `tenant_id`. Kemungkinan dibuat sebelum keputusan arsitektur multi-tenant diterapkan secara konsisten.

3. **`appointments` table: `tenant_id` ditambahkan via patch migration**: Migrasi awal tidak memiliki `tenant_id`, kemudian ditambahkan via `2026_04_10_000001`. Ini adalah pola yang benar tapi perlu dipastikan guard-nya berfungsi.

4. **`housekeeping_tasks` type enum mismatch**: Generator menggunakan nilai enum yang berbeda dari yang didefinisikan di migrasi final. Kemungkinan generator ditulis berdasarkan versi lama skema sebelum migrasi fix `2026_04_05_000012` mengubah enum values.

5. **`housekeeping_tasks` missing `actual_duration`**: Kolom ini ada di versi awal tabel (dari `2026_04_03_000002`) tapi dihapus saat recreate di `2026_04_05_000012`. Generator tidak diupdate.

6. **`quality_checks` missing `inspector_id`**: Generator `ManufacturingGenerator` tidak menyertakan `inspector_id` saat insert, padahal kolom ini NOT NULL tanpa default. Generator perlu resolve user_id untuk inspector.

7. **`crop_cycles` dual migration conflict**: Ada dua migrasi yang membuat tabel `crop_cycles` dengan skema berbeda. Migrasi pertama (`2026_03_31_000008`) dibuat untuk use case farm management yang kompleks, sedangkan migrasi kedua (`2026_04_06_000010`) dibuat untuk agriculture analytics dengan skema yang lebih sederhana. Generator menggunakan kolom dari skema kedua.

8. **Unique constraints tanpa `tenant_id` scope**: `doctors.doctor_number`, `patients.medical_record_number`, dan `patients.nik` memiliki unique constraint global, bukan per-tenant. Ini menyebabkan conflict pada test yang membuat multiple tenants.

---

## Correctness Properties

Property 1: Bug Condition — Generator Berhasil Insert Data dengan tenant_id

_For any_ industry generator yang mencoba insert ke tabel dengan kolom `tenant_id`, setelah fix diterapkan, generator SHALL berhasil melakukan insert tanpa exception, dan data yang dibuat SHALL memiliki `tenant_id` yang sesuai dengan tenant yang diberikan.

**Validates: Requirements 2.1, 2.2, 2.3, 2.4**

Property 2: Preservation — Generator Industri Lain Tidak Terpengaruh

_For any_ industry selain `healthcare` (retail, manufacturing, hotel, restaurant, services, agriculture, construction), setelah fix diterapkan, generator SHALL menghasilkan jumlah record yang sama seperti sebelumnya, dan semua test property 1-10 SHALL tetap lulus.

**Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5, 3.6**

---

## Fix Implementation

### Perubahan yang Diperlukan

Semua fix dilakukan via **new migration files** — tidak ada modifikasi pada migrasi yang sudah ada.

---

### Fix 1: Tambah `tenant_id` ke tabel `doctors`

**File baru**: `database/migrations/2026_04_15_000001_add_tenant_id_to_doctors_table.php`

**Perubahan**:
- Tambah kolom `tenant_id` (foreignId, constrained ke `tenants`, cascadeOnDelete)
- Tambah index `tenant_id`
- Drop global unique constraint `doctor_number` dan `license_number`
- Tambah per-tenant unique constraint `['tenant_id', 'doctor_number']` dan `['tenant_id', 'license_number']`

```php
if (!Schema::hasColumn('doctors', 'tenant_id')) {
    Schema::table('doctors', function (Blueprint $table) {
        $table->foreignId('tenant_id')->after('id')->constrained('tenants')->onDelete('cascade');
        $table->index('tenant_id');
        $table->dropUnique(['doctor_number']);
        $table->dropUnique(['license_number']);
        $table->unique(['tenant_id', 'doctor_number']);
        $table->unique(['tenant_id', 'license_number']);
    });
}
```

---

### Fix 2: Tambah `tenant_id` ke tabel `patients`

**File baru**: `database/migrations/2026_04_15_000002_add_tenant_id_to_patients_table.php`

**Perubahan**:
- Tambah kolom `tenant_id` (foreignId, constrained ke `tenants`, cascadeOnDelete)
- Tambah index `tenant_id`
- Drop global unique constraint `medical_record_number` dan `nik`
- Tambah per-tenant unique constraint `['tenant_id', 'medical_record_number']` dan `['tenant_id', 'nik']`

```php
if (!Schema::hasColumn('patients', 'tenant_id')) {
    Schema::table('patients', function (Blueprint $table) {
        $table->foreignId('tenant_id')->after('id')->constrained('tenants')->onDelete('cascade');
        $table->index('tenant_id');
        $table->dropUnique(['medical_record_number']);
        $table->dropUnique(['nik']);
        $table->unique(['tenant_id', 'medical_record_number']);
        $table->unique(['tenant_id', 'nik']);
    });
}
```

---

### Fix 3: Perbaiki `housekeeping_tasks` — tambah `actual_duration` dan perluas type enum

**File baru**: `database/migrations/2026_04_15_000003_fix_housekeeping_tasks_for_generator.php`

**Perubahan**:
- Tambah kolom `actual_duration` (integer, nullable) — digunakan oleh `HotelGenerator`
- Perluas enum `type` untuk menambahkan nilai yang digunakan generator: `'regular_cleaning'`, `'turndown_service'`

```php
// Tambah actual_duration
if (!Schema::hasColumn('housekeeping_tasks', 'actual_duration')) {
    Schema::table('housekeeping_tasks', function (Blueprint $table) {
        $table->integer('actual_duration')->nullable()->after('estimated_duration');
    });
}

// Perluas enum type via raw SQL (MySQL tidak support ALTER COLUMN enum via Blueprint)
DB::statement("ALTER TABLE housekeeping_tasks MODIFY COLUMN type 
    ENUM('checkout_clean','stay_clean','deep_clean','inspection',
         'regular_cleaning','turndown_service') NOT NULL");
```

---

### Fix 4: Buat `quality_checks.inspector_id` nullable

**File baru**: `database/migrations/2026_04_15_000004_make_quality_checks_inspector_nullable.php`

**Perubahan**:
- Ubah `inspector_id` dari NOT NULL menjadi nullable
- Ini memungkinkan `ManufacturingGenerator` insert tanpa harus menyediakan inspector

```php
DB::statement("ALTER TABLE quality_checks MODIFY COLUMN inspector_id 
    BIGINT UNSIGNED NULL");
```

---

### Fix 5: Tambah kolom yang missing ke `crop_cycles`

**File baru**: `database/migrations/2026_04_15_000005_fix_crop_cycles_schema_for_generator.php`

**Analisis**: Migrasi `2026_03_31_000008` membuat `crop_cycles` dengan skema kompleks. Generator `AgricultureGenerator` menggunakan kolom dari skema yang berbeda (dari `2026_04_06_000010` yang di-guard). Solusi: tambah kolom yang missing ke tabel yang sudah ada.

**Kolom yang digunakan generator tapi tidak ada di skema pertama**:
- `variety` (ada sebagai `crop_variety` di skema pertama — perlu alias)
- `area_hectares` (tidak ada)
- `field_location` (tidak ada)
- `growth_stage` (tidak ada, ada `phase` dengan enum berbeda)
- `estimated_yield_tons` (ada sebagai `target_yield_qty` — perlu alias)
- `actual_yield_tons` (ada sebagai `actual_yield_qty` — perlu alias)
- `status` (tidak ada, ada `phase`)
- `planting_date` (ada sebagai `actual_plant_date` — perlu alias)
- `expected_harvest_date` (ada sebagai `plan_harvest_date` — perlu alias)

```php
Schema::table('crop_cycles', function (Blueprint $table) {
    if (!Schema::hasColumn('crop_cycles', 'variety')) {
        $table->string('variety')->nullable()->after('crop_name');
    }
    if (!Schema::hasColumn('crop_cycles', 'area_hectares')) {
        $table->decimal('area_hectares', 10, 2)->nullable();
    }
    if (!Schema::hasColumn('crop_cycles', 'field_location')) {
        $table->string('field_location')->nullable();
    }
    if (!Schema::hasColumn('crop_cycles', 'growth_stage')) {
        $table->string('growth_stage')->nullable();
    }
    if (!Schema::hasColumn('crop_cycles', 'estimated_yield_tons')) {
        $table->float('estimated_yield_tons')->nullable();
    }
    if (!Schema::hasColumn('crop_cycles', 'actual_yield_tons')) {
        $table->float('actual_yield_tons')->nullable();
    }
    if (!Schema::hasColumn('crop_cycles', 'status')) {
        $table->string('status')->default('active');
    }
    if (!Schema::hasColumn('crop_cycles', 'planting_date')) {
        $table->date('planting_date')->nullable();
    }
    if (!Schema::hasColumn('crop_cycles', 'expected_harvest_date')) {
        $table->date('expected_harvest_date')->nullable();
    }
    if (!Schema::hasColumn('crop_cycles', 'actual_harvest_date')) {
        $table->date('actual_harvest_date')->nullable();
    }
});
```

---

### Fix 6: Verifikasi `appointments.tenant_id` (sudah ada via patch migration)

Migrasi `2026_04_10_000001_add_tenant_id_to_appointments_table.php` sudah menambahkan `tenant_id` dengan guard yang benar. **Tidak perlu fix tambahan**.

---

### Ringkasan Semua Issues dan Fix

| # | Issue | Tabel | Root Cause | Fix |
|---|---|---|---|---|
| 1 | Missing `tenant_id` | `doctors` | Migrasi dibuat tanpa tenant scope | New migration: tambah `tenant_id` + ubah unique constraints |
| 2 | Missing `tenant_id` | `patients` | Migrasi dibuat tanpa tenant scope | New migration: tambah `tenant_id` + ubah unique constraints |
| 3 | `tenant_id` via patch | `appointments` | Migrasi awal tanpa `tenant_id` | Sudah ada patch migration — verifikasi saja |
| 4 | Enum mismatch `type` | `housekeeping_tasks` | Generator menggunakan nilai enum lama | New migration: perluas enum |
| 5 | Missing `actual_duration` | `housekeeping_tasks` | Kolom dihapus saat recreate | New migration: tambah kolom |
| 6 | `inspector_id` NOT NULL | `quality_checks` | Generator tidak pass `inspector_id` | New migration: buat nullable |
| 7 | Dual migration conflict | `crop_cycles` | Dua migrasi berbeda untuk tabel sama | New migration: tambah kolom yang missing |
| 8 | Global unique constraint | `doctors` | `doctor_number` unique tanpa tenant scope | Ditangani di Fix 1 |
| 9 | Global unique constraint | `patients` | `medical_record_number`, `nik` unique tanpa tenant scope | Ditangani di Fix 2 |

---

## Testing Strategy

### Validation Approach

Strategi testing mengikuti dua fase: pertama, jalankan test pada kode yang belum di-fix untuk mengkonfirmasi bug (exploratory), kemudian verifikasi fix bekerja dan tidak ada regresi (fix checking + preservation checking).

### Exploratory Bug Condition Checking

**Goal**: Konfirmasi root cause sebelum implementasi fix. Jalankan test pada kode unfixed untuk melihat error yang sebenarnya.

**Test Plan**: Jalankan `test_property_11_industry_data_completeness` dengan industry `healthcare` pada database tanpa fix. Observasi apakah test di-skip atau fail, dan pesan error apa yang muncul.

**Test Cases**:
1. **Healthcare Doctor Insert Test**: Panggil `HealthcareGenerator::seedDoctors()` langsung — akan fail dengan `Column not found: tenant_id` (akan fail pada unfixed code)
2. **Healthcare Patient Insert Test**: Panggil `HealthcareGenerator::seedPatients()` langsung — akan fail dengan `Column not found: tenant_id` (akan fail pada unfixed code)
3. **Hotel Housekeeping Task Test**: Panggil `HotelGenerator::seedHousekeepingTasks()` — akan fail dengan enum violation atau missing column (akan fail pada unfixed code)
4. **Manufacturing QC Test**: Panggil `ManufacturingGenerator::seedQualityChecks()` — akan fail dengan `Field 'inspector_id' doesn't have a default value` (akan fail pada unfixed code)
5. **Agriculture Crop Cycle Test**: Panggil `AgricultureGenerator::seedCropCycles()` — akan fail dengan `Column not found` jika skema pertama yang aktif (mungkin fail pada unfixed code)

**Expected Counterexamples**:
- `SQLSTATE[42S22]: Column not found: 1054 Unknown column 'tenant_id' in 'field list'` untuk doctors dan patients
- Enum violation untuk housekeeping_tasks type values `'regular_cleaning'` dan `'turndown_service'`
- `SQLSTATE[HY000]: Field 'inspector_id' doesn't have a default value` untuk quality_checks
- `SQLSTATE[42S22]: Column not found` untuk crop_cycles kolom `area_hectares`, `growth_stage`, dll.

### Fix Checking

**Goal**: Verifikasi bahwa setelah fix diterapkan, semua generator berhasil membuat data.

**Pseudocode:**
```
FOR ALL industry IN ['healthcare', 'hotel', 'manufacturing', 'agriculture'] DO
  result := generateForIndustry(industry, tenantId, userId)
  ASSERT result['success'] == true
  ASSERT result['records_created'] > 0
  ASSERT expectedMinimums(industry, tenantId)
END FOR
```

### Preservation Checking

**Goal**: Verifikasi bahwa fix tidak merusak industri yang sudah berfungsi.

**Pseudocode:**
```
FOR ALL industry IN ['retail', 'restaurant', 'services', 'construction'] DO
  result := generateForIndustry(industry, tenantId, userId)
  ASSERT result['success'] == true
  ASSERT recordCounts(industry, tenantId) >= expectedMinimums(industry)
END FOR
```

**Testing Approach**: Property-based testing direkomendasikan untuk preservation checking karena menghasilkan banyak test case secara otomatis, menangkap edge case yang mungkin terlewat, dan memberikan jaminan kuat bahwa behavior tidak berubah untuk semua input non-buggy.

**Test Cases**:
1. **Retail Preservation**: Verifikasi `RetailGenerator` masih menghasilkan >=10 sales orders setelah fix
2. **Restaurant Preservation**: Verifikasi `RestaurantGenerator` masih menghasilkan >=8 tables dan >=10 fb_orders
3. **Services Preservation**: Verifikasi `ServicesGenerator` masih menghasilkan >=5 projects dan >=5 project_invoices
4. **Construction Preservation**: Verifikasi `ConstructionGenerator` masih menghasilkan >=3 projects dan >=5 purchase_orders

### Unit Tests

- Test bahwa `doctors` table memiliki kolom `tenant_id` setelah migrasi
- Test bahwa `patients` table memiliki kolom `tenant_id` setelah migrasi
- Test bahwa `housekeeping_tasks` memiliki kolom `actual_duration` setelah migrasi
- Test bahwa `quality_checks.inspector_id` adalah nullable setelah migrasi
- Test bahwa `crop_cycles` memiliki semua kolom yang dibutuhkan `AgricultureGenerator`

### Property-Based Tests

- Generate random tenant IDs dan verifikasi bahwa `HealthcareGenerator` berhasil membuat data dengan `tenant_id` yang benar (Property 1)
- Generate random pairs of tenants dan verifikasi tidak ada overlap data antara tenant A dan tenant B (Property 6 — data isolation)
- Generate random industries dan verifikasi bahwa semua generator menghasilkan data dengan `tenant_id` yang benar (Property 5)

### Integration Tests

- Test full flow: `generateForIndustry('healthcare', ...)` — verifikasi doctors, patients, appointments terbuat dengan `tenant_id` yang benar
- Test multi-tenant: generate data untuk 2 tenant berbeda dengan industry yang sama — verifikasi tidak ada data overlap
- Test idempotency: panggil `generateForIndustry` dua kali untuk tenant yang sama — verifikasi tidak ada duplikasi (Property 7)
