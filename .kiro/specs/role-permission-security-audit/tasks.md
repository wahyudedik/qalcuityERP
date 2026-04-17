# Implementation Plan

- [x] 1. Tulis bug condition exploration tests (sebelum fix)
  - **Property 1: Bug Condition** - 6 Bug Security Audit
  - **CRITICAL**: Test-test ini HARUS GAGAL pada kode unfixed — kegagalan mengkonfirmasi bug ada
  - **DO NOT attempt to fix the test or the code when it fails**
  - **GOAL**: Surface counterexample yang mendemonstrasikan setiap bug
  - **Scoped PBT Approach**: Scope setiap property ke kondisi konkret yang memicu bug

  - **Bug 1 — isAdmin() selalu false**:
    - Buat user dengan `role = 'admin'`, panggil `App\Services\Security\PermissionService::isAdmin($user)`
    - Property: untuk semua user dengan `role IN ['admin', 'super_admin']`, `isAdmin()` HARUS mengembalikan `true`
    - Jalankan pada kode unfixed → EXPECTED: GAGAL karena `role_name` tidak ada di model User
    - Dokumentasikan counterexample: `isAdmin(user{role='admin'})` mengembalikan `false`

  - **Bug 2 — Superadmin bypass gagal**:
    - Buat user dengan `role = 'super_admin'`, simulasikan request ke healthcare route via `RBACMiddleware`
    - Property: untuk semua user dengan `role = 'super_admin'`, `hasRole('superadmin')` HARUS mengembalikan `true`
    - Jalankan pada kode unfixed → EXPECTED: GAGAL karena `'superadmin' != 'super_admin'`
    - Dokumentasikan counterexample: `user{role='super_admin'}->hasRole('superadmin')` mengembalikan `false`

  - **Bug 3 — Route sensitif tanpa tenant isolation**:
    - Buat dua user dari tenant berbeda (tenant_id=1 dan tenant_id=2)
    - Property: untuk semua request ke `/barcode/*`, `/inventory/movements/*`, `/bulk-actions/*`, `/api/quick-search`, `/api/saved-searches/*`, `/transaction-chain/*`, middleware `tenant.isolation` HARUS ada di stack
    - Jalankan pada kode unfixed → EXPECTED: GAGAL karena route tidak memiliki `tenant.isolation`
    - Dokumentasikan counterexample: request ke `/barcode/print` tidak memiliki `tenant.isolation` di middleware stack

  - **Bug 4 — Customer portal tanpa tenant isolation**:
    - Property: untuk semua request ke `/portal/*`, middleware `tenant.isolation` HARUS ada di stack
    - Jalankan pada kode unfixed → EXPECTED: GAGAL
    - Dokumentasikan counterexample: request ke `/portal/dashboard` tidak memiliki `tenant.isolation`

  - **Bug 5 — Staff akses supplier scorecard**:
    - Buat user dengan `role = 'staff'`, kirim request ke `/supplier-scorecards/`
    - Property: untuk semua user dengan `role IN ['staff', 'kasir', 'gudang', 'housekeeping', 'maintenance', 'affiliate']`, response HARUS 403
    - Jalankan pada kode unfixed → EXPECTED: GAGAL karena tidak ada role check (response 200)
    - Dokumentasikan counterexample: `user{role='staff'}` mendapat 200 pada `/supplier-scorecards/`

  - **Bug 6 — Model hilang dari EnforceTenantIsolation**:
    - Buat `CustomField` dengan `tenant_id=1`, akses dengan user `tenant_id=2` via route model binding
    - Property: untuk semua model `IN [ErpNotification, UserPermission, CustomField, DocumentTemplate, Workflow, AiTourSession]` dengan `tenant_id != user.tenant_id`, response HARUS 403
    - Jalankan pada kode unfixed → EXPECTED: GAGAL karena model tidak ada di `$tenantModels`
    - Dokumentasikan counterexample: akses `CustomField{tenant_id=1}` dengan `user{tenant_id=2}` tidak diblokir

  - Tandai task selesai setelah semua test ditulis, dijalankan, dan kegagalan didokumentasikan
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7, 1.8, 1.9, 1.10, 1.11_

- [x] 2. Tulis preservation property tests (sebelum fix)
  - **Property 2: Preservation** - Akses Role yang Sudah Benar
  - **IMPORTANT**: Ikuti observation-first methodology — observasi perilaku pada kode unfixed untuk input non-buggy
  - **Scoped PBT**: Generate random user dengan berbagai role valid, verifikasi akses tidak berubah

  - **Observasi pada kode unfixed (non-buggy inputs)**:
    - `user{role='admin'}` mengakses route via `PermissionMiddleware` (bukan `CheckPermissionMiddleware`) → 200
    - `user{role='manager'}` mengakses modul yang diizinkan di `ROLE_DEFAULTS` → 200
    - `user{role='kasir'}` mengakses `/pos/*` → 200
    - `user{role='gudang'}` mengakses `/inventory/*` (bukan movements) → 200
    - `user{role='doctor'}` mengakses healthcare routes → 200
    - `user{role='admin'}` mengakses `/supplier-scorecards/` → 200 (admin tetap boleh)
    - Akses model tanpa `tenant_id` via route binding → tidak diblokir `EnforceTenantIsolation`
    - Per-user override di `UserPermission` → override diprioritaskan di atas role default

  - **Property-based tests**:
    - Property: untuk semua user dengan `role IN ['admin', 'super_admin']`, akses ke semua modul dalam tenant mereka HARUS tetap diizinkan setelah fix
    - Property: untuk semua user dengan `role = 'manager'`, akses ke modul dalam `ROLE_DEFAULTS['manager']` HARUS tetap diizinkan
    - Property: untuk semua user dengan `role = 'kasir'`, akses ke `pos.view` dan `pos.create` HARUS tetap diizinkan
    - Property: untuk semua user dengan `role = 'gudang'`, akses ke inventory dan warehouse HARUS tetap diizinkan
    - Property: untuk semua model tanpa `tenant_id`, `EnforceTenantIsolation` HARUS tidak memblokir akses
    - Property: untuk semua healthcare role (`doctor`, `nurse`, `receptionist`, `pharmacist`, `lab_technician`), akses ke healthcare routes HARUS tetap diizinkan

  - Jalankan semua preservation tests pada kode unfixed → EXPECTED: SEMUA PASS (konfirmasi baseline)
  - Tandai task selesai setelah tests ditulis, dijalankan, dan passing pada kode unfixed
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.8, 3.9, 3.10_

- [x] 3. Fix Bug 1 — Perbaiki field `role_name` → `role` di `Security\PermissionService`

  - [x] 3.1 Implementasi fix
    - File: `app/Services/Security/PermissionService.php`
    - Ganti `$user->role_name ?? ''` → `$user->role ?? ''`
    - Ganti `'superadmin'` → `'super_admin'` dalam array check `isAdmin()`
    - _Bug_Condition: isBugCondition_Bug1(user, 'CheckPermissionMiddleware') — user.role IN ['admin','super_admin'] AND Security\PermissionService.isAdmin(user) = false_
    - _Expected_Behavior: isAdmin(user) mengembalikan true untuk user.role IN ['admin', 'super_admin']_
    - _Preservation: PermissionMiddleware yang menggunakan App\Services\PermissionService tidak terpengaruh_
    - _Requirements: 2.1, 2.2_

  - [x] 3.2 Verifikasi bug condition exploration test sekarang pass
    - **Property 1: Expected Behavior** - isAdmin() Konsisten
    - **IMPORTANT**: Jalankan ulang test yang SAMA dari task 1 (Bug 1) — jangan tulis test baru
    - EXPECTED OUTCOME: Test PASS (konfirmasi bug 1 sudah diperbaiki)
    - _Requirements: 2.1, 2.2_

  - [x] 3.3 Verifikasi preservation tests masih pass
    - **Property 2: Preservation** - Admin dan PermissionMiddleware
    - **IMPORTANT**: Jalankan ulang test yang SAMA dari task 2 — jangan tulis test baru
    - EXPECTED OUTCOME: Tests PASS (tidak ada regresi)

- [x] 4. Fix Bug 2 — Ganti `hasRole('superadmin')` → `isSuperAdmin()` di middleware dan policy

  - [x] 4.1 Perbaiki `RBACMiddleware`
    - File: `app/Http/Middleware/RBACMiddleware.php`
    - Ganti `$user->hasRole('superadmin')` → `$user->isSuperAdmin()`
    - Hapus `|| $user->is_superadmin` jika ada (duplikasi logika)
    - _Bug_Condition: isBugCondition_Bug2(user, 'RBACMiddleware') — user.role='super_admin' AND hasRole('superadmin')=false_
    - _Expected_Behavior: isSuperAdmin() mengembalikan true, bypass check aktif_
    - _Requirements: 2.3, 2.4_

  - [x] 4.2 Perbaiki `HealthcareAccessMiddleware`
    - File: `app/Http/Middleware/HealthcareAccessMiddleware.php`
    - Ganti `$user->hasRole('superadmin')` → `$user->isSuperAdmin()`
    - _Bug_Condition: isBugCondition_Bug2(user, 'HealthcareAccessMiddleware')_
    - _Requirements: 2.3, 2.4_

  - [x] 4.3 Perbaiki `MedicalRecordPolicy` dan `PatientDataPolicy`
    - File: `app/Policies/MedicalRecordPolicy.php`
    - File: `app/Policies/PatientDataPolicy.php`
    - Cari semua `hasRole('superadmin')` dan ganti dengan `isSuperAdmin()`
    - _Bug_Condition: isBugCondition_Bug2(user, 'MedicalRecordPolicy') dan isBugCondition_Bug2(user, 'PatientDataPolicy')_
    - _Requirements: 2.3, 2.4_

  - [x] 4.4 Verifikasi bug condition exploration test sekarang pass
    - **Property 1: Expected Behavior** - Superadmin Bypass Konsisten
    - **IMPORTANT**: Jalankan ulang test yang SAMA dari task 1 (Bug 2) — jangan tulis test baru
    - EXPECTED OUTCOME: Test PASS (super_admin bypass aktif di semua middleware dan policy)
    - _Requirements: 2.3, 2.4_

  - [x] 4.5 Verifikasi preservation tests masih pass
    - **Property 2: Preservation** - Healthcare Role Access
    - **IMPORTANT**: Jalankan ulang test yang SAMA dari task 2 — jangan tulis test baru
    - EXPECTED OUTCOME: Tests PASS — `doctor`, `nurse`, `receptionist`, `pharmacist`, `lab_technician` tetap dapat akses healthcare

- [x] 5. Fix Bug 3 — Tambahkan `tenant.isolation` ke route sensitif

  - [x] 5.1 Tambahkan `tenant.isolation` ke barcode routes
    - File: `routes/web.php`
    - Tambahkan `->middleware(['tenant.isolation'])` ke group `Route::prefix('barcode')`
    - _Bug_Condition: request.path MATCHES '/barcode/*' AND 'tenant.isolation' NOT IN middleware_
    - _Expected_Behavior: tenant.isolation memvalidasi produk milik tenant yang sama_
    - _Requirements: 2.5_

  - [x] 5.2 Tambahkan `tenant.isolation` ke inventory movements routes
    - File: `routes/web.php`
    - Tambahkan `->middleware(['tenant.isolation'])` ke group `Route::prefix('inventory/movements')`
    - _Requirements: 2.5_

  - [x] 5.3 Tambahkan `tenant.isolation` dan permission check ke bulk-actions routes
    - File: `routes/web.php`
    - Tambahkan `->middleware(['tenant.isolation', 'permission:inventory,edit'])` ke `POST /bulk-actions/execute`
    - Tambahkan `->middleware(['tenant.isolation', 'permission:inventory,view'])` ke `GET /bulk-actions/export-download`
    - _Bug_Condition: request.path IN ['/bulk-actions/execute', '/bulk-actions/export-download'] AND 'tenant.isolation' NOT IN middleware_
    - _Requirements: 2.6_

  - [x] 5.4 Tambahkan `tenant.isolation` ke quick-search dan saved-searches routes
    - File: `routes/web.php`
    - Tambahkan `->middleware(['tenant.isolation'])` ke `GET /api/quick-search`
    - Tambahkan `->middleware(['tenant.isolation'])` ke group `Route::prefix('api/saved-searches')`
    - _Requirements: 2.7_

  - [x] 5.5 Tambahkan `tenant.isolation` ke transaction-chain routes
    - File: `routes/web.php`
    - Ubah middleware dari `middleware('auth')` → `middleware(['auth', 'tenant.isolation'])` pada group `Route::prefix('transaction-chain')`
    - _Bug_Condition: request.path MATCHES '/transaction-chain/*' AND 'tenant.isolation' NOT IN middleware_
    - _Requirements: 2.8_

  - [x] 5.6 Verifikasi bug condition exploration test sekarang pass
    - **Property 1: Expected Behavior** - Tenant Isolation pada Route Sensitif
    - **IMPORTANT**: Jalankan ulang test yang SAMA dari task 1 (Bug 3) — jangan tulis test baru
    - EXPECTED OUTCOME: Test PASS (semua route sensitif memiliki `tenant.isolation` di middleware stack)
    - _Requirements: 2.5, 2.6, 2.7, 2.8_

  - [x] 5.7 Verifikasi preservation tests masih pass
    - **Property 2: Preservation** - Akses Inventory dan Barcode untuk Role yang Diizinkan
    - **IMPORTANT**: Jalankan ulang test yang SAMA dari task 2 — jangan tulis test baru
    - EXPECTED OUTCOME: Tests PASS — user dengan tenant yang benar tetap dapat akses

- [x] 6. Fix Bug 4 — Tambahkan `tenant.isolation` ke customer portal routes

  - [x] 6.1 Implementasi fix
    - File: `routes/web.php`
    - Ubah middleware group `/portal/*` dari `middleware('auth')` → `middleware(['auth', 'tenant.isolation'])`
    - _Bug_Condition: isBugCondition_Bug4(request) — request.path STARTS_WITH '/portal/' AND 'tenant.isolation' NOT IN middleware_
    - _Expected_Behavior: user hanya melihat data portal tenant mereka sendiri_
    - _Preservation: user yang sudah login dengan tenant yang benar tetap dapat akses portal_
    - _Requirements: 2.9_

  - [x] 6.2 Verifikasi bug condition exploration test sekarang pass
    - **Property 1: Expected Behavior** - Tenant Isolation pada Customer Portal
    - **IMPORTANT**: Jalankan ulang test yang SAMA dari task 1 (Bug 4) — jangan tulis test baru
    - EXPECTED OUTCOME: Test PASS
    - _Requirements: 2.9_

  - [x] 6.3 Verifikasi preservation tests masih pass
    - **Property 2: Preservation** - Customer Portal Access
    - **IMPORTANT**: Jalankan ulang test yang SAMA dari task 2 — jangan tulis test baru
    - EXPECTED OUTCOME: Tests PASS

- [x] 7. Fix Bug 5 — Tambahkan `permission:suppliers,view` ke supplier scorecard routes

  - [x] 7.1 Implementasi fix
    - File: `routes/web.php`
    - Tambahkan `'permission:suppliers,view'` ke middleware array pada group `Route::prefix('supplier-scorecards')`
    - Tambahkan `'permission:suppliers,view'` ke middleware array pada group `Route::prefix('supplier-performance')`
    - _Bug_Condition: isBugCondition_Bug5(user, request) — user.role IN ['staff','kasir','gudang','housekeeping','maintenance','affiliate'] AND request.path STARTS_WITH '/supplier-scorecards/' OR '/supplier-performance/'_
    - _Expected_Behavior: response 403 untuk role yang tidak memiliki permission suppliers,view_
    - _Preservation: admin dan manager tetap dapat akses karena memiliki permission suppliers,view di ROLE_DEFAULTS_
    - _Requirements: 2.10_

  - [x] 7.2 Verifikasi bug condition exploration test sekarang pass
    - **Property 1: Expected Behavior** - Role Check pada Supplier Scorecard
    - **IMPORTANT**: Jalankan ulang test yang SAMA dari task 1 (Bug 5) — jangan tulis test baru
    - EXPECTED OUTCOME: Test PASS — `staff`, `kasir`, `gudang` mendapat 403
    - _Requirements: 2.10_

  - [x] 7.3 Verifikasi preservation tests masih pass
    - **Property 2: Preservation** - Admin dan Manager Akses Supplier Scorecard
    - **IMPORTANT**: Jalankan ulang test yang SAMA dari task 2 — jangan tulis test baru
    - EXPECTED OUTCOME: Tests PASS — admin dan manager tetap dapat akses

- [x] 8. Fix Bug 6 — Tambahkan model yang hilang ke `$tenantModels` di `EnforceTenantIsolation`

  - [x] 8.1 Implementasi fix
    - File: `app/Http/Middleware/EnforceTenantIsolation.php`
    - Tambahkan ke array `$tenantModels`:
      - `\App\Models\ErpNotification::class`
      - `\App\Models\UserPermission::class`
      - `\App\Models\CustomField::class`
      - `\App\Models\DocumentTemplate::class`
      - `\App\Models\Workflow::class`
      - `\App\Models\AiTourSession::class`
    - Verifikasi `WebhookSubscription` sudah ada di daftar (tidak perlu ditambahkan lagi)
    - _Bug_Condition: isBugCondition_Bug6(model, request) — model.class IN missingModels AND model.tenant_id != user.tenant_id AND model NOT IN $tenantModels_
    - _Expected_Behavior: EnforceTenantIsolation mengembalikan 403 untuk akses lintas tenant_
    - _Preservation: model tanpa tenant_id (model global/shared) tetap tidak divalidasi_
    - _Requirements: 2.11_

  - [x] 8.2 Verifikasi bug condition exploration test sekarang pass
    - **Property 1: Expected Behavior** - EnforceTenantIsolation Mencakup Semua Model
    - **IMPORTANT**: Jalankan ulang test yang SAMA dari task 1 (Bug 6) — jangan tulis test baru
    - EXPECTED OUTCOME: Test PASS — akses lintas tenant untuk semua model yang ditambahkan diblokir dengan 403
    - _Requirements: 2.11_

  - [x] 8.3 Verifikasi preservation tests masih pass
    - **Property 2: Preservation** - Model Global Tidak Diblokir
    - **IMPORTANT**: Jalankan ulang test yang SAMA dari task 2 — jangan tulis test baru
    - EXPECTED OUTCOME: Tests PASS — model tanpa `tenant_id` tetap tidak diblokir

- [x] 9. Checkpoint — Pastikan semua tests pass
  - Jalankan seluruh test suite: `php artisan test`
  - Pastikan semua 6 bug condition exploration tests PASS (bug sudah diperbaiki)
  - Pastikan semua preservation property tests PASS (tidak ada regresi)
  - Pastikan tidak ada test yang sebelumnya passing menjadi failing
  - Tanyakan kepada user jika ada pertanyaan atau ambiguitas yang muncul
