# Bugfix Requirements Document

## Introduction

Audit keamanan pada sistem role & permission aplikasi ERP Laravel multi-tenant ini menemukan sejumlah celah yang berpotensi menyebabkan kebocoran data antar tenant, privilege escalation, dan bypass authorization. Investigasi mendalam pada codebase mengidentifikasi 6 kategori bug utama yang perlu diperbaiki untuk memastikan isolasi data tenant yang ketat dan kontrol akses yang konsisten di seluruh endpoint.

---

## Bug Analysis

### Current Behavior (Defect)

**Bug 1 — Dual PermissionService: Inkonsistensi Implementasi**

1.1 WHEN `CheckPermissionMiddleware` digunakan pada suatu route THEN sistem menggunakan `App\Services\Security\PermissionService` yang memeriksa `$user->role_name` (field yang tidak ada di model User), sehingga method `isAdmin()` selalu mengembalikan `false` dan semua user dianggap bukan admin

1.2 WHEN `PermissionMiddleware` digunakan pada route lain THEN sistem menggunakan `App\Services\PermissionService` yang memeriksa `$user->role` (field yang benar), sehingga dua middleware permission bekerja dengan logika berbeda untuk endpoint yang berbeda

**Bug 2 — Role Name Mismatch: `superadmin` vs `super_admin`**

1.3 WHEN `RBACMiddleware`, `HealthcareAccessMiddleware`, `MedicalRecordPolicy`, dan `PatientDataPolicy` melakukan bypass check untuk superadmin THEN sistem memeriksa `$user->hasRole('superadmin')` (tanpa underscore), namun model User mendefinisikan `isSuperAdmin()` dengan `$this->role === 'super_admin'` (dengan underscore), sehingga bypass superadmin pada modul healthcare tidak pernah aktif

1.4 WHEN superadmin mengakses endpoint healthcare THEN sistem menolak akses karena role check `hasRole('superadmin')` tidak cocok dengan nilai role `'super_admin'` yang tersimpan di database

**Bug 3 — Missing Tenant Isolation pada Route Sensitif**

1.5 WHEN user dengan role apapun mengakses `/barcode/print`, `/barcode/auto-generate`, atau `/inventory/movements/*` THEN sistem hanya memeriksa autentikasi (`auth` middleware) tanpa memvalidasi tenant_id, sehingga user dari tenant A dapat mencetak barcode atau memanipulasi stok produk milik tenant B

1.6 WHEN user mengakses `/bulk-actions/execute` atau `/bulk-actions/export-download` THEN sistem hanya memeriksa autentikasi tanpa role atau permission check, sehingga user dengan role `staff` atau `kasir` dapat mengeksekusi bulk action pada data yang seharusnya tidak dapat mereka akses

1.7 WHEN user mengakses `/api/quick-search` atau `/api/saved-searches/*` THEN sistem hanya memeriksa autentikasi tanpa tenant isolation middleware, sehingga hasil pencarian berpotensi menampilkan data dari tenant lain jika query tidak difilter di level controller

1.8 WHEN user mengakses `/transaction-chain/{type}/{id}` THEN sistem hanya memeriksa autentikasi (`middleware('auth')`) tanpa tenant isolation, sehingga user dapat melihat transaction chain milik tenant lain dengan menebak ID

**Bug 4 — Customer Portal Tanpa Tenant Isolation**

1.9 WHEN user yang sudah login mengakses `/portal/*` (customer portal) THEN sistem hanya memeriksa autentikasi tanpa `tenant.isolation` middleware, sehingga user dari tenant A berpotensi mengakses data portal customer dari tenant B

**Bug 5 — Supplier Scorecard Hanya Menggunakan `auth` Tanpa Role Check**

1.10 WHEN user dengan role `staff`, `kasir`, atau `gudang` mengakses `/supplier-scorecards/*` atau `/supplier-performance/*` THEN sistem mengizinkan akses karena middleware hanya `['auth', 'tenant.isolation']` tanpa pembatasan role, padahal data scorecard supplier bersifat sensitif untuk operasional bisnis

**Bug 6 — EnforceTenantIsolation Tidak Mencakup Semua Model**

1.11 WHEN user mengakses resource yang menggunakan route model binding untuk model seperti `Notification`, `UserPermission`, `CustomField`, `DocumentTemplate`, `Workflow`, `WebhookSubscription`, atau `AiTourSession` THEN middleware `EnforceTenantIsolation` tidak memvalidasi kepemilikan tenant karena model-model tersebut tidak ada dalam daftar `$tenantModels`, sehingga user dapat mengakses atau memodifikasi resource milik tenant lain

---

### Expected Behavior (Correct)

**Bug 1 — Dual PermissionService**

2.1 WHEN `CheckPermissionMiddleware` memeriksa status admin THEN sistem SHALL menggunakan field `$user->role` (bukan `$user->role_name`) sehingga method `isAdmin()` pada `App\Services\Security\PermissionService` mengembalikan nilai yang benar

2.2 WHEN sistem membutuhkan pengecekan permission THEN sistem SHALL menggunakan satu implementasi `PermissionService` yang konsisten, atau kedua implementasi SHALL menggunakan field dan logika yang identik untuk menentukan status admin/superadmin

**Bug 2 — Role Name Mismatch**

2.3 WHEN `RBACMiddleware`, `HealthcareAccessMiddleware`, `MedicalRecordPolicy`, dan `PatientDataPolicy` melakukan bypass check untuk superadmin THEN sistem SHALL memeriksa `$user->hasRole('super_admin')` (dengan underscore) yang konsisten dengan nilai yang tersimpan di database dan definisi `isSuperAdmin()` di model User

2.4 WHEN superadmin mengakses endpoint healthcare THEN sistem SHALL mengizinkan akses penuh tanpa pemeriksaan permission tambahan

**Bug 3 — Missing Tenant Isolation pada Route Sensitif**

2.5 WHEN user mengakses `/barcode/*` atau `/inventory/movements/*` THEN sistem SHALL memvalidasi bahwa produk dan data stok yang diakses milik tenant yang sama dengan user yang sedang login

2.6 WHEN user mengakses `/bulk-actions/execute` THEN sistem SHALL memeriksa permission yang sesuai berdasarkan tipe aksi yang dieksekusi sebelum memproses request

2.7 WHEN user mengakses `/api/quick-search` atau `/api/saved-searches/*` THEN sistem SHALL memfilter semua hasil berdasarkan `tenant_id` user yang sedang login

2.8 WHEN user mengakses `/transaction-chain/{type}/{id}` THEN sistem SHALL memvalidasi bahwa transaksi yang diminta milik tenant yang sama dengan user yang sedang login

**Bug 4 — Customer Portal**

2.9 WHEN user mengakses `/portal/*` THEN sistem SHALL memvalidasi tenant isolation sehingga user hanya dapat melihat data portal yang terkait dengan tenant mereka sendiri

**Bug 5 — Supplier Scorecard**

2.10 WHEN user dengan role `staff`, `kasir`, atau `gudang` mencoba mengakses `/supplier-scorecards/*` atau `/supplier-performance/*` THEN sistem SHALL menolak akses dengan HTTP 403 karena role tersebut tidak memiliki izin untuk melihat data scorecard supplier

**Bug 6 — EnforceTenantIsolation**

2.11 WHEN user mengakses resource dengan route model binding untuk model yang memiliki `tenant_id` THEN middleware `EnforceTenantIsolation` SHALL memvalidasi kepemilikan tenant untuk semua model tersebut, termasuk `Notification`, `UserPermission`, `CustomField`, `DocumentTemplate`, `Workflow`, `WebhookSubscription`, dan `AiTourSession`

---

### Unchanged Behavior (Regression Prevention)

3.1 WHEN user dengan role `admin` mengakses modul apapun dalam tenant mereka THEN sistem SHALL CONTINUE TO mengizinkan akses penuh ke semua resource dalam tenant tersebut

3.2 WHEN user dengan role `super_admin` mengakses data tenant manapun THEN sistem SHALL CONTINUE TO mengizinkan akses dengan audit trail yang dicatat di `AuditLogService`

3.3 WHEN user dengan role `manager` mengakses modul yang diizinkan (sales, hrm, purchasing, dll.) THEN sistem SHALL CONTINUE TO mengizinkan akses sesuai dengan `ROLE_DEFAULTS` yang sudah didefinisikan di `App\Services\PermissionService`

3.4 WHEN user dengan role `kasir` mengakses modul POS THEN sistem SHALL CONTINUE TO mengizinkan akses ke `pos.view` dan `pos.create` sesuai konfigurasi yang ada

3.5 WHEN user dengan role `gudang` mengakses modul inventory dan warehouse THEN sistem SHALL CONTINUE TO mengizinkan akses sesuai dengan `ROLE_DEFAULTS['gudang']` yang sudah didefinisikan

3.6 WHEN admin tenant mengelola user dalam tenant mereka melalui `/users/*` THEN sistem SHALL CONTINUE TO memvalidasi bahwa user yang dikelola memiliki `tenant_id` yang sama (perilaku `abort_if` yang sudah ada di `TenantUserController`)

3.7 WHEN `EnforceTenantIsolation` memvalidasi route model binding THEN sistem SHALL CONTINUE TO mengizinkan akses untuk model yang tidak memiliki `tenant_id` (model global/shared)

3.8 WHEN user mengakses halaman publik (`/`, `/about/*`, `/legal/*`, `/resources/*`) THEN sistem SHALL CONTINUE TO melayani halaman tersebut tanpa autentikasi

3.9 WHEN per-user permission override disimpan di `UserPermission` table THEN sistem SHALL CONTINUE TO mengutamakan override tersebut di atas role default sesuai logika di `App\Services\PermissionService::check()`

3.10 WHEN healthcare module diakses oleh role `doctor`, `nurse`, `receptionist`, `pharmacist`, atau `lab_technician` THEN sistem SHALL CONTINUE TO mengizinkan akses sesuai dengan permission mapping di `RBACMiddleware::$rolePermissions`
