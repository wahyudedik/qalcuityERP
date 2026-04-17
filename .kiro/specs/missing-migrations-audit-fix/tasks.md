# Implementation Plan

- [x] 1. Write bug condition exploration test
  - **Property 1: Bug Condition** - Generator Fails on Tables with Missing Columns
  - **CRITICAL**: This test MUST FAIL on unfixed code - failure confirms the bug exists
  - **DO NOT attempt to fix the test or the code when it fails**
  - **NOTE**: This test encodes the expected behavior - it will validate the fix when it passes after implementation
  - **GOAL**: Surface counterexamples that demonstrate the bug exists across all affected generators
  - **Scoped PBT Approach**: Scope the property to concrete failing cases per generator:
    - `HealthcareGenerator::seedDoctors()` → expects `SQLSTATE[42S22]: Column not found: tenant_id` on `doctors`
    - `HealthcareGenerator::seedPatients()` → expects `SQLSTATE[42S22]: Column not found: tenant_id` on `patients`
    - `HotelGenerator::seedHousekeepingTasks()` → expects enum violation for `'regular_cleaning'`/`'turndown_service'` OR `Column not found: actual_duration`
    - `ManufacturingGenerator::seedQualityChecks()` → expects `SQLSTATE[HY000]: Field 'inspector_id' doesn't have a default value`
    - `AgricultureGenerator::seedCropCycles()` → expects `Column not found` for `area_hectares`, `growth_stage`, etc.
  - Test assertions: for each generator call, assert that `generatedData[table] > 0` (will fail on unfixed code)
  - Run test on UNFIXED code
  - **EXPECTED OUTCOME**: Test FAILS (this is correct - it proves the bug exists)
  - Document counterexamples found:
    - `HealthcareGenerator::seedDoctors()` → `SQLSTATE[42S22]: Column not found: 1054 Unknown column 'tenant_id' in 'field list'`
    - `HealthcareGenerator::seedPatients()` → same as above
    - `HotelGenerator::seedHousekeepingTasks()` → enum violation or missing `actual_duration`
    - `ManufacturingGenerator::seedQualityChecks()` → `Field 'inspector_id' doesn't have a default value`
    - `AgricultureGenerator::seedCropCycles()` → `Column not found: area_hectares`
  - Mark task complete when test is written, run, and failure is documented
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5_

- [x] 2. Write preservation property tests (BEFORE implementing fix)
  - **Property 2: Preservation** - Non-Healthcare Generators Unaffected
  - **IMPORTANT**: Follow observation-first methodology
  - Observe behavior on UNFIXED code for non-buggy inputs (industries that already work):
    - Observe: `RetailGenerator` produces >=10 sales_orders on unfixed code
    - Observe: `RestaurantGenerator` produces >=8 tables and >=10 fb_orders on unfixed code
    - Observe: `ServicesGenerator` produces >=5 projects and >=5 project_invoices on unfixed code
    - Observe: `ConstructionGenerator` produces >=3 projects and >=5 purchase_orders on unfixed code
  - Write property-based tests: for all industries in `['retail', 'restaurant', 'services', 'construction']`, assert record counts >= expected minimums (from Preservation Requirements in design)
  - Also verify test property 1-10 still pass on unfixed code as baseline
  - Run tests on UNFIXED code
  - **EXPECTED OUTCOME**: Tests PASS (this confirms baseline behavior to preserve)
  - Mark task complete when tests are written, run, and passing on unfixed code
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6_

- [x] 3. Fix 1: Tambah `tenant_id` ke tabel `doctors`

  - [x] 3.1 Buat migration `2026_04_15_000001_add_tenant_id_to_doctors_table.php`
    - Tambah kolom `tenant_id` (foreignId, constrained ke `tenants`, cascadeOnDelete) dengan guard `if (!Schema::hasColumn(...))`
    - Tambah index `tenant_id`
    - Drop global unique constraint `doctor_number` dan `license_number`
    - Tambah per-tenant unique constraint `['tenant_id', 'doctor_number']` dan `['tenant_id', 'license_number']`
    - Implementasi `down()` untuk rollback: drop per-tenant unique, restore global unique, drop `tenant_id`
    - _Bug_Condition: isBugCondition('healthcare', 'doctors', 'tenant_id') == true (kolom tidak ada)_
    - _Expected_Behavior: setelah migrasi, `Schema::hasColumn('doctors', 'tenant_id')` == true_
    - _Preservation: migrasi yang sudah ada tidak dimodifikasi, hanya tambah migrasi baru_
    - _Requirements: 2.1, 2.4, 2.5, 2.6_

- [x] 4. Fix 2: Tambah `tenant_id` ke tabel `patients`

  - [x] 4.1 Buat migration `2026_04_15_000002_add_tenant_id_to_patients_table.php`
    - Tambah kolom `tenant_id` (foreignId, constrained ke `tenants`, cascadeOnDelete) dengan guard `if (!Schema::hasColumn(...))`
    - Tambah index `tenant_id`
    - Drop global unique constraint `medical_record_number` dan `nik`
    - Tambah per-tenant unique constraint `['tenant_id', 'medical_record_number']` dan `['tenant_id', 'nik']`
    - Implementasi `down()` untuk rollback: drop per-tenant unique, restore global unique, drop `tenant_id`
    - _Bug_Condition: isBugCondition('healthcare', 'patients', 'tenant_id') == true (kolom tidak ada)_
    - _Expected_Behavior: setelah migrasi, `Schema::hasColumn('patients', 'tenant_id')` == true_
    - _Preservation: migrasi yang sudah ada tidak dimodifikasi, hanya tambah migrasi baru_
    - _Requirements: 2.2, 2.4, 2.5, 2.6_

- [x] 5. Fix 3: Perbaiki `housekeeping_tasks` — tambah `actual_duration` dan perluas type enum

  - [x] 5.1 Buat migration `2026_04_15_000003_fix_housekeeping_tasks_for_generator.php`
    - Tambah kolom `actual_duration` (integer, nullable, after `estimated_duration`) dengan guard `if (!Schema::hasColumn(...))`
    - Perluas enum `type` via raw SQL: tambah nilai `'regular_cleaning'` dan `'turndown_service'` ke enum yang sudah ada (`checkout_clean`, `stay_clean`, `deep_clean`, `inspection`)
    - Gunakan `DB::statement("ALTER TABLE housekeeping_tasks MODIFY COLUMN type ENUM(...) NOT NULL")`
    - Implementasi `down()` untuk rollback: drop `actual_duration`, kembalikan enum ke nilai semula
    - _Bug_Condition: isBugCondition('hotel', 'housekeeping_tasks', 'actual_duration') == true AND enum mismatch untuk 'regular_cleaning'/'turndown_service'_
    - _Expected_Behavior: `HotelGenerator::seedHousekeepingTasks()` berhasil insert tanpa exception_
    - _Preservation: nilai enum yang sudah ada (`checkout_clean`, `stay_clean`, `deep_clean`, `inspection`) tetap valid_
    - _Requirements: 2.5, 2.6, 3.3_

- [x] 6. Fix 4: Buat `quality_checks.inspector_id` nullable

  - [x] 6.1 Buat migration `2026_04_15_000004_make_quality_checks_inspector_nullable.php`
    - Ubah `inspector_id` dari NOT NULL menjadi nullable via raw SQL: `DB::statement("ALTER TABLE quality_checks MODIFY COLUMN inspector_id BIGINT UNSIGNED NULL")`
    - Implementasi `down()` untuk rollback: kembalikan ke NOT NULL (hati-hati jika ada data null)
    - _Bug_Condition: isBugCondition('manufacturing', 'quality_checks', 'inspector_id') == true (NOT NULL tanpa default, generator tidak pass nilai)_
    - _Expected_Behavior: `ManufacturingGenerator::seedQualityChecks()` berhasil insert tanpa `inspector_id`_
    - _Preservation: foreign key constraint ke tabel `users` tetap ada, hanya nullability yang berubah_
    - _Requirements: 2.5, 2.6, 3.3_

- [x] 7. Fix 5: Tambah kolom yang missing ke `crop_cycles`

  - [x] 7.1 Buat migration `2026_04_15_000005_fix_crop_cycles_schema_for_generator.php`
    - Tambah kolom berikut dengan guard `if (!Schema::hasColumn(...))` untuk setiap kolom:
      - `variety` (string, nullable)
      - `area_hectares` (decimal 10,2, nullable)
      - `field_location` (string, nullable)
      - `growth_stage` (string, nullable)
      - `estimated_yield_tons` (float, nullable)
      - `actual_yield_tons` (float, nullable)
      - `status` (string, default 'active')
      - `planting_date` (date, nullable)
      - `expected_harvest_date` (date, nullable)
      - `actual_harvest_date` (date, nullable)
    - Implementasi `down()` untuk rollback: drop semua kolom yang ditambahkan
    - _Bug_Condition: isBugCondition('agriculture', 'crop_cycles', 'area_hectares') == true (kolom dari skema kedua tidak ada di skema pertama yang aktif)_
    - _Expected_Behavior: `AgricultureGenerator::seedCropCycles()` berhasil insert tanpa `Column not found` exception_
    - _Preservation: kolom yang sudah ada di skema pertama (`farm_plot_id`, `number`, `crop_variety`, `phase`, dll.) tidak diubah_
    - _Requirements: 2.5, 2.6, 3.3_

- [x] 8. Verify bug condition exploration test now passes

  - [x] 8.1 Jalankan ulang test dari task 1 setelah semua fix diterapkan
    - **Property 1: Expected Behavior** - All Generators Successfully Insert Data with tenant_id
    - **IMPORTANT**: Re-run the SAME test from task 1 - do NOT write a new test
    - Jalankan `php artisan migrate --env=testing` untuk apply semua migrasi baru
    - Re-run bug condition exploration test dari step 1
    - **EXPECTED OUTCOME**: Test PASSES (confirms all bugs are fixed):
      - `HealthcareGenerator::seedDoctors()` berhasil → `generatedData['doctors'] > 0`
      - `HealthcareGenerator::seedPatients()` berhasil → `generatedData['patients'] > 0`
      - `HotelGenerator::seedHousekeepingTasks()` berhasil → `generatedData['housekeeping_tasks'] > 0`
      - `ManufacturingGenerator::seedQualityChecks()` berhasil → `generatedData['quality_checks'] > 0`
      - `AgricultureGenerator::seedCropCycles()` berhasil → `generatedData['crop_cycles'] > 0`
    - Verifikasi `test_property_11_industry_data_completeness` untuk `healthcare` berjalan tanpa `markTestSkipped()`
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6_

  - [x] 8.2 Verify preservation tests still pass
    - **Property 2: Preservation** - Non-Healthcare Generators Unaffected
    - **IMPORTANT**: Re-run the SAME tests from task 2 - do NOT write new tests
    - Run preservation property tests dari step 2
    - **EXPECTED OUTCOME**: Tests PASS (confirms no regressions):
      - `RetailGenerator` masih menghasilkan >=10 sales_orders
      - `RestaurantGenerator` masih menghasilkan >=8 tables dan >=10 fb_orders
      - `ServicesGenerator` masih menghasilkan >=5 projects dan >=5 project_invoices
      - `ConstructionGenerator` masih menghasilkan >=3 projects dan >=5 purchase_orders
    - Confirm test property 1-10 masih lulus semua tanpa regresi

- [x] 9. Checkpoint - Ensure all tests pass
  - Jalankan full test suite: `php artisan test tests/Feature/DemoData/SampleDataGeneratorPropertyTest.php`
  - Pastikan tidak ada test yang di-skip untuk `healthcare`, `hotel`, `manufacturing`, `agriculture`
  - Pastikan semua test property 1-11 lulus
  - Pastikan `php artisan migrate --env=testing` berjalan tanpa error
  - Tanyakan ke user jika ada pertanyaan atau ambiguitas yang muncul
