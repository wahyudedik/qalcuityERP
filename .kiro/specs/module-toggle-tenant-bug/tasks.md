# Implementation Plan

- [x] 1. Write bug condition exploration test
  - **Property 1: Bug Condition** - Modul Onboarding Tidak Tersimpan ke Tenant & Industry Key Mismatch
  - **CRITICAL**: This test MUST FAIL on unfixed code - failure confirms the bug exists
  - **DO NOT attempt to fix the test or the code when it fails**
  - **NOTE**: This test encodes the expected behavior - it will validate the fix when it passes after implementation
  - **GOAL**: Surface counterexamples that demonstrate both bugs exist
  - **Scoped PBT Approach**: Scope to concrete failing cases for reproducibility
  - Test Bug 1 — panggil `complete()` dengan `selected_modules = ['pos', 'hrm']`, assert `tenant.enabled_modules` sama dengan `['pos', 'hrm']` (pada kode unfixed: tetap `null`)
  - Test Bug 2a — panggil `recommend('restaurant')`, assert hasilnya bukan modul default (pada kode unfixed: mengembalikan default)
  - Test Bug 2b — panggil `recommend('manufacturing')`, assert hasilnya bukan modul default
  - Test Bug 2c — panggil `recommend('services')`, assert hasilnya bukan modul default
  - Run test on UNFIXED code
  - **EXPECTED OUTCOME**: Test FAILS (ini membuktikan bug ada)
  - Document counterexamples: `tenant.enabled_modules` tetap `null` setelah `complete()`, dan `recommend('restaurant')` mengembalikan modul default
  - Mark task complete when test is written, run, and failure is documented
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5_

- [x] 2. Write preservation property tests (BEFORE implementing fix)
  - **Property 2: Preservation** - Perilaku di Luar Onboarding Tidak Berubah
  - **IMPORTANT**: Follow observation-first methodology
  - Observe: Settings toggle menyimpan ke `enabled_modules` dengan benar pada kode unfixed
  - Observe: `isModuleEnabled()` mengembalikan `true` untuk tenant dengan `enabled_modules = null`
  - Observe: `skip()` tidak mengubah `enabled_modules`
  - Observe: `recommend('fnb')`, `recommend('retail')`, `recommend('manufacture')` mengembalikan hasil yang benar pada kode unfixed
  - Write property-based test: untuk semua input yang TIDAK melalui `OnboardingController::complete()`, side effect harus identik dengan kode asli
  - Test case 1 — Settings toggle: ubah modul via Settings controller, assert `enabled_modules` tersimpan benar
  - Test case 2 — Null backward compat: tenant dengan `enabled_modules = null`, assert `isModuleEnabled()` mengembalikan `true` untuk semua modul
  - Test case 3 — Skip onboarding: panggil `skip()`, assert `enabled_modules` tidak berubah (tetap `null`)
  - Test case 4 — Direct recommend() call: panggil `recommend('fnb')`, `recommend('retail')`, `recommend('manufacture')`, assert hasilnya sama sebelum dan sesudah fix
  - Verify tests PASS on UNFIXED code
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_

- [x] 3. Fix OnboardingController — simpan modul ke tenant dan mapping industry key

  - [x] 3.1 Implement the fix di `app/Http/Controllers/OnboardingController.php`
    - Tambahkan industry key mapping di `complete()` sebelum memanggil `recommend()`:
      ```php
      $industryMap = [
          'restaurant'    => 'fnb',
          'manufacturing' => 'manufacture',
          'services'      => 'service',
      ];
      $recommendKey = $industryMap[$request->industry] ?? $request->industry;
      ```
    - Tentukan modul yang akan diterapkan: jika `selected_modules` tidak kosong gunakan itu, jika kosong panggil `recommend($recommendKey)['modules']`
    - Simpan modul ke tenant: `$tenant->update(['enabled_modules' => $modulesToApply])`
    - Pastikan `tenants.onboarding_completed` di-set `true`
    - Tidak ada perubahan pada `ModuleRecommendationService`
    - _Bug_Condition: isBugCondition(input) — `selected_modules` tidak kosong DAN `tenant.enabled_modules` tetap `null` setelah `complete()`; ATAU `industry` IN `['restaurant', 'manufacturing', 'services']` dan `recommend()` mengembalikan default_
    - _Expected_Behavior: `tenant.enabled_modules` terisi dengan `selected_modules` jika diisi, atau dengan modul hasil `recommend(mappedKey)` jika tidak diisi_
    - _Preservation: Semua input yang tidak melalui `complete()` tidak terpengaruh; `skip()` tidak menyentuh `enabled_modules`; `recommend()` dari endpoint AJAX tetap menerima key asli_
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5_

  - [x] 3.2 Verify bug condition exploration test now passes
    - **Property 1: Expected Behavior** - Modul Onboarding Tersimpan ke Tenant & Industry Key Mapping Benar
    - **IMPORTANT**: Re-run the SAME test from task 1 - do NOT write a new test
    - Run bug condition exploration test from step 1
    - **EXPECTED OUTCOME**: Test PASSES (confirms both bugs are fixed)
    - Assert `tenant.enabled_modules = ['pos', 'hrm']` setelah `complete()` dengan `selected_modules = ['pos', 'hrm']`
    - Assert `recommend('restaurant')` via `complete()` menghasilkan modul F&B, bukan default
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5_

  - [x] 3.3 Verify preservation tests still pass
    - **Property 2: Preservation** - Perilaku di Luar Onboarding Tidak Berubah
    - **IMPORTANT**: Re-run the SAME tests from task 2 - do NOT write new tests
    - Run preservation property tests from step 2
    - **EXPECTED OUTCOME**: Tests PASS (confirms no regressions)
    - Confirm Settings toggle, null backward compat, skip onboarding, dan direct recommend() call semua masih bekerja benar

- [x] 4. Checkpoint - Ensure all tests pass
  - Pastikan semua test dari task 1, 3.2, dan 3.3 pass
  - Tanya user jika ada pertanyaan atau edge case yang perlu dikonfirmasi
