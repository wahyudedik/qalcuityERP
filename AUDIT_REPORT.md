# AUDIT MENYELURUH — QALCUITY AI ERP
> Tanggal: 13 April 2026 | Stack: Laravel 13 · PHP 8.3 · MySQL · Alpine.js · Tailwind CSS

---

## RINGKASAN KONDISI SISTEM

Qalcuity ERP adalah sistem ERP multi-tenant berbasis Laravel 13 yang sangat komprehensif dengan 20+ modul industri, integrasi AI Gemini, dan arsitektur keamanan berlapis. Secara keseluruhan sistem ini **dalam kondisi baik** dengan beberapa bug kritis dan area improvement yang perlu ditangani.

**Skor Kondisi per Area:**
- Keamanan & Multi-Tenant: 8/10 ✅
- Business Logic: 7.5/10 ✅
- Database & Migrasi: 8/10 ✅
- Frontend & UI/UX: 6.5/10 ⚠️
- AI ERP Chat: 8/10 ✅
- Performa: 7/10 ⚠️
- API & Integrasi: 8/10 ✅

---

## BAGIAN 1: BUG TERIDENTIFIKASI + SOLUSI + PRIORITAS


### 🔴 KRITIS (P0) — Harus diperbaiki segera

#### BUG-001: Duplikasi Middleware Security Headers
**File:** `app/Http/Middleware/AddSecurityHeaders.php` vs `app/Http/Middleware/SecurityHeaders.php`
**Masalah:** Dua middleware dengan fungsi identik terdaftar. `AddSecurityHeaders` set `X-Frame-Options: SAMEORIGIN`, sedangkan `SecurityHeaders` set `X-Frame-Options: DENY`. Konflik ini menyebabkan header yang dikirim tidak konsisten tergantung urutan middleware.
**Solusi:** Hapus `SecurityHeaders.php`, pertahankan `AddSecurityHeaders.php` dan update nilai `X-Frame-Options` ke `DENY` (lebih aman). Pastikan hanya satu yang terdaftar di `bootstrap/app.php`.
**Prioritas:** P0 — Keamanan

#### BUG-002: CSP `unsafe-eval` di Production
**File:** `app/Http/Middleware/AddSecurityHeaders.php` (line buildCspPolicy)
**Masalah:** `script-src` mengizinkan `'unsafe-eval'` di semua environment termasuk production. Ini membuka celah XSS via `eval()`.
**Solusi:** Hapus `'unsafe-eval'` dari CSP production. Hanya izinkan di development jika benar-benar diperlukan Alpine.js.
**Prioritas:** P0 — Keamanan XSS

#### BUG-003: API Token Dikirim via Query String
**File:** `app/Http/Middleware/ApiTokenAuth.php` (line 14)
**Masalah:** `$request->query('api_token')` memungkinkan token dikirim via URL query string. Token akan tercatat di server logs, browser history, dan proxy logs — kebocoran kredensial.
**Solusi:** Hapus fallback `$request->query('api_token')`. Hanya izinkan Bearer token atau `X-API-Token` header.
**Prioritas:** P0 — Keamanan API

#### BUG-004: Warehouse ID Tidak Divalidasi Tenant saat Tambah Produk
**File:** `app/Http/Controllers/InventoryController.php` (store, line ~100)
**Masalah:** `warehouse_id` divalidasi dengan `exists:warehouses,id` tanpa memfilter `tenant_id`. User tenant A bisa memasukkan `warehouse_id` milik tenant B.
**Solusi:** Ubah validasi menjadi Rule::exists dengan scope tenant:
```php
'warehouse_id' => ['nullable', Rule::exists('warehouses', 'id')->where('tenant_id', $tid)],
```
**Prioritas:** P0 — Isolasi Data Multi-Tenant

#### BUG-005: GeminiWriteValidator Tidak Mencakup Semua Tool Write
**File:** `app/Services/GeminiWriteValidator.php`
**Masalah:** Validator hanya mencakup `add_stock`, `create_purchase_order`, `auto_reorder`, `add_transaction`. Tool write lain seperti `create_customer`, `create_employee`, `update_product` tidak divalidasi — AI bisa membuat data dengan nilai tidak valid.
**Solusi:** Tambahkan validator untuk semua tool write yang ada di ToolRegistry.
**Prioritas:** P0 — Integritas Data AI

---

### 🟠 TINGGI (P1) — Perbaiki dalam 1 minggu

#### BUG-006: N+1 Query di AuditLogService.getLogs
**File:** `app/Services/Security/AuditLogService.php` (getLogs)
**Masalah:** Method `getLogs` mengembalikan paginator tapi `exportToCsv` memanggil `getLogs` dengan `per_page: 10000` — ini load 10.000 record sekaligus ke memory.
**Solusi:** Gunakan `cursor()` atau `chunk()` untuk export CSV besar.
**Prioritas:** P1 — Performa/Memory

#### BUG-007: Dashboard Cache Key Tidak Include User ID untuk Custom Widgets
**File:** `app/Http/Controllers/DashboardController.php` (index)
**Masalah:** Cache key `dashboard_{tenantId}_{role}` tidak include `user_id`. Jika dua user dengan role sama di tenant yang sama memiliki widget berbeda, mereka bisa mendapat data widget yang salah.
**Solusi:** Tambahkan `user_id` ke cache key untuk data yang user-specific (custom widgets, gamification).
**Prioritas:** P1 — Data Integrity

#### BUG-008: `two_factor_secret` Ada di `$fillable` User Model
**File:** `app/Models/User.php` (line ~30)
**Masalah:** `two_factor_secret` dan `two_factor_recovery_codes` ada di `$fillable`. Jika ada endpoint yang melakukan mass assignment dari request user, secret 2FA bisa ditimpa.
**Solusi:** Pindahkan ke `$guarded` atau gunakan explicit assignment di TwoFactorService saja.
**Prioritas:** P1 — Keamanan 2FA

#### BUG-009: Tidak Ada Rate Limiting di Endpoint Login
**File:** `routes/auth.php` / `app/Http/Controllers/Auth/`
**Masalah:** Meskipun ada `AccountLockoutService`, tidak terlihat rate limiting di level middleware untuk endpoint `/login`. Brute force masih mungkin jika lockout service tidak berjalan.
**Solusi:** Tambahkan `throttle:10,1` middleware ke route login, atau gunakan Laravel's built-in `RateLimiter`.
**Prioritas:** P1 — Keamanan Brute Force

#### BUG-010: `sendMedia` Menyimpan File ke Storage Tanpa Validasi MIME Ketat
**File:** `app/Http/Controllers/ChatController.php` (sendMedia, line ~370)
**Masalah:** File diupload ke `storage/products/` dengan nama `chat_` + uniqid. Tidak ada pengecekan ekstensi berbahaya di sini (berbeda dengan `ValidateFileUpload` middleware yang tidak dipakai di endpoint ini).
**Solusi:** Tambahkan validasi ekstensi dan MIME type sebelum `storeAs()`, atau terapkan `ValidateFileUpload` middleware ke route chat media.
**Prioritas:** P1 — Keamanan Upload

#### BUG-011: `BelongsToTenant` Trait Tidak Aktif di Semua Model Kritis
**File:** `app/Traits/BelongsToTenant.php`
**Masalah:** Trait ini hanya digunakan di `User` model (terlihat dari import). Model-model lain seperti `Product`, `SalesOrder`, `Invoice` menggunakan manual `where('tenant_id', ...)` di controller — tidak konsisten dan rawan lupa.
**Solusi:** Audit semua model dan tambahkan `use BelongsToTenant` ke model yang memiliki kolom `tenant_id`. Ini akan otomatis enforce global scope.
**Prioritas:** P1 — Konsistensi Isolasi Tenant

#### BUG-012: Health Check Endpoint `/api/health/detailed` Tidak Diproteksi
**File:** `app/Http/Controllers/HealthCheckController.php`
**Masalah:** Endpoint `/detailed` menampilkan informasi sensitif (versi database, status Redis, konfigurasi queue) tanpa autentikasi.
**Solusi:** Tambahkan middleware `auth:sanctum` atau IP whitelist untuk endpoint `/detailed` dan `/ready`.
**Prioritas:** P1 — Information Disclosure

---

### 🟡 SEDANG (P2) — Perbaiki dalam 2 minggu

#### BUG-013: `PermissionService.roleDefault` Tidak Handle Role Baru
**File:** `app/Services/PermissionService.php`
**Masalah:** Role baru seperti `housekeeping`, `maintenance` (ditambahkan di migration 2026_04_05) tidak ada di `ROLE_DEFAULTS`. Method `roleDefault` akan return `false` untuk semua permission — user dengan role baru tidak bisa akses apapun.
**Solusi:** Tambahkan default permissions untuk semua role yang ada di database.
**Prioritas:** P2 — Fungsionalitas

#### BUG-014: `RBACMiddleware` Hanya Untuk Healthcare
**File:** `app/Http/Middleware/RBACMiddleware.php`
**Masalah:** `$rolePermissions` hanya mendefinisikan role healthcare (doctor, nurse, pharmacist, dll). Middleware ini tidak bisa digunakan untuk modul lain.
**Solusi:** Pindahkan logika ke `PermissionService` yang sudah ada, atau buat RBAC yang lebih generik.
**Prioritas:** P2 — Arsitektur

#### BUG-015: Tidak Ada Validasi `parent_id` Tenant saat Buat COA
**File:** `app/Http/Controllers/AccountingController.php` (storeCoa)
**Masalah:** `parent_id` divalidasi dengan `exists:chart_of_accounts,id` tanpa filter `tenant_id`. User bisa set parent COA dari tenant lain.
**Solusi:** Tambahkan Rule::exists dengan where tenant_id.
**Prioritas:** P2 — Isolasi Data

#### BUG-016: `AuditLogService.getLogs` Return Type Salah
**File:** `app/Services/Security/AuditLogService.php` (getLogs, line ~100)
**Masalah:** Method signature mengembalikan `array` tapi implementasi mengembalikan `LengthAwarePaginator`. Ini akan menyebabkan error jika ada kode yang mengiterasi result sebagai array biasa.
**Solusi:** Ubah return type ke `LengthAwarePaginator` atau `mixed`.
**Prioritas:** P2 — Type Safety

#### BUG-017: `OutputEscaper.sanitizeHtml` Menggunakan `strip_tags` yang Deprecated
**File:** `app/Services/OutputEscaper.php` (sanitizeHtml)
**Masalah:** `strip_tags($value, $allowedTagString)` dengan string parameter deprecated di PHP 8.x. Harus menggunakan array.
**Solusi:** Ubah ke `strip_tags($value, ['p', 'br', 'strong', 'em', ...])`.
**Prioritas:** P2 — Kompatibilitas PHP 8.3

#### BUG-018: Tidak Ada Validasi Tanggal di `AccountingController.storePeriod`
**File:** `app/Http/Controllers/AccountingController.php` (storePeriod)
**Masalah:** Tidak ada pengecekan apakah periode baru overlap dengan periode yang sudah ada. Dua periode bisa memiliki tanggal yang tumpang tindih.
**Solusi:** Tambahkan validasi overlap sebelum create:
```php
$overlap = AccountingPeriod::where('tenant_id', $tid)
    ->where('start_date', '<=', $data['end_date'])
    ->where('end_date', '>=', $data['start_date'])
    ->exists();
```
**Prioritas:** P2 — Business Logic Keuangan

---

### 🟢 RENDAH (P3) — Backlog

#### BUG-019: `app.js` Mengekspos `window.moduleLoader` dan `window.logger`
**File:** `resources/js/app.js` (line ~120)
**Masalah:** `window.moduleLoader = moduleLoader` dan `window.logger = logger` mengekspos internal module ke global scope — bisa dieksploitasi via browser console.
**Solusi:** Hapus assignment ke `window` atau gunakan WeakRef/Symbol untuk internal access.
**Prioritas:** P3 — Minor Security

#### BUG-020: `ValidateFileUpload` Tidak Menangani `audio/*` dan `video/*`
**File:** `app/Http/Middleware/ValidateFileUpload.php`
**Masalah:** Modul telemedicine membutuhkan upload video/audio tapi middleware tidak memiliki tipe `media`. Akan selalu gagal validasi.
**Solusi:** Tambahkan tipe `media` ke `ALLOWED_MIME_TYPES`.
**Prioritas:** P3 — Fungsionalitas Telemedicine


---

## BAGIAN 2: DAFTAR IMPROVEMENT & FITUR BARU

### Improvement Arsitektur

**IMP-001: Standardisasi BelongsToTenant di Semua Model**
Saat ini hanya `User` yang menggunakan trait `BelongsToTenant`. Semua model dengan `tenant_id` harus menggunakan trait ini untuk konsistensi dan mencegah data leak antar tenant.

**IMP-002: Centralized Permission Registry**
`RBACMiddleware` mendefinisikan permissions sendiri, `PermissionService` mendefinisikan sendiri, `User.allowedAiTools()` mendefinisikan sendiri. Perlu satu sumber kebenaran untuk semua permission mapping.

**IMP-003: API Versioning yang Lebih Ketat**
Saat ini hanya ada `/api/v1`. Perlu mekanisme deprecation dan versioning yang jelas untuk backward compatibility saat ada breaking changes.

**IMP-004: Event Sourcing untuk Transaksi Keuangan**
Transaksi keuangan kritis (journal entries, invoices) sebaiknya menggunakan event sourcing pattern untuk audit trail yang tidak bisa dimanipulasi.

**IMP-005: Database Connection Pooling**
Untuk production dengan banyak tenant, perlu konfigurasi connection pooling yang optimal. Pertimbangkan PgBouncer atau ProxySQL.

### Improvement Performa

**IMP-006: Eager Loading di Dashboard Stats**
Method `salesStats`, `inventoryStats`, dll di DashboardController masih berpotensi N+1. Perlu audit query dengan Laravel Debugbar dan tambahkan eager loading.

**IMP-007: Queue Priority untuk AI Jobs**
Semua AI jobs masuk ke queue `default`. Perlu priority queue: `critical` untuk notifikasi, `high` untuk AI chat, `default` untuk reports, `low` untuk analytics.

**IMP-008: Redis untuk Session & Cache di Production**
Saat ini menggunakan database driver untuk session dan cache. Untuk production dengan banyak concurrent user, Redis sangat direkomendasikan.

**IMP-009: CDN untuk Static Assets**
Logo, gambar produk, dan dokumen sebaiknya disajikan via CDN (CloudFront/Cloudflare) untuk mengurangi beban server.

**IMP-010: Lazy Loading Images di Views**
Tambahkan `loading="lazy"` ke semua `<img>` tag di views untuk mempercepat initial page load.

### Fitur Baru yang Direkomendasikan

**FEAT-001: AI Anomaly Auto-Resolution**
Saat ini anomali hanya dideteksi dan ditampilkan. Tambahkan kemampuan AI untuk menyarankan dan mengeksekusi resolusi otomatis (dengan konfirmasi user).

**FEAT-002: Multi-Language Support (i18n)**
Sistem sudah menggunakan `APP_LOCALE=id` tapi belum ada file terjemahan lengkap. Tambahkan dukungan Bahasa Inggris untuk tenant internasional.

**FEAT-003: Audit Trail Visual Timeline**
Tampilkan audit trail sebagai timeline visual per record (mirip GitHub commit history) untuk memudahkan investigasi perubahan data.

**FEAT-004: Bulk AI Operations**
Endpoint `/chat/batch` sudah ada tapi belum diekspos di UI. Tambahkan fitur "AI Batch Processing" di dashboard untuk operasi massal.

**FEAT-005: Tenant Data Export (GDPR)**
Meskipun ada `GdprController`, belum ada UI yang jelas untuk tenant mengekspor semua data mereka. Tambahkan wizard export data lengkap.

**FEAT-006: Mobile App (PWA Enhancement)**
Service worker sudah ada. Tingkatkan PWA dengan: push notifications yang lebih kaya, background sync yang lebih robust, dan offline-first untuk modul POS.

**FEAT-007: AI-Powered Onboarding**
Ganti onboarding wizard statis dengan AI yang bisa mendeteksi jenis bisnis dari deskripsi dan otomatis mengkonfigurasi modul yang relevan.

**FEAT-008: Inter-Tenant Marketplace**
Untuk tenant yang ingin berbagi produk/layanan antar bisnis dalam ekosistem yang sama (B2B marketplace internal).

---

## BAGIAN 3: EVALUASI PERFORMA AI ERP CHAT

### Arsitektur AI Chat

Sistem AI ERP chat menggunakan arsitektur yang solid:
- **Gemini 2.5 Flash** sebagai model utama dengan fallback
- **Intent Detection** untuk memfilter tools (60-70% pengurangan tools yang dikirim)
- **Rule-based Handler** untuk pertanyaan sederhana (tanpa API call)
- **Response Cache** untuk query repetitif
- **Quota Management** dengan fail-safe DB fallback
- **Tool Registry** dengan 100+ tools terorganisir per modul

### Kekuatan

1. **Intent-based Tool Filtering** — Sangat efektif. Dari 100+ tools, hanya 10-30 yang dikirim ke Gemini berdasarkan intent. Ini mengurangi latency 60-70%.
2. **Multi-layer Caching** — Rule-based → Cache → Gemini API. Pertanyaan umum tidak pernah mencapai API.
3. **Quota Fail-safe** — Jika cache down, fallback ke DB. Jika DB down, deny access (conservative approach). Ini benar.
4. **Tenant Context Injection** — Business context otomatis diinjeksi ke system prompt berdasarkan `business_type`.
5. **Streaming Support** — Endpoint `/chat/stream` untuk UX yang lebih responsif.

### Kelemahan & Risiko

1. **Tool Registry Terlalu Besar** — 100+ tools dalam satu registry. Meskipun ada filtering, maintenance akan sulit. Rekomendasikan modularisasi per domain.
2. **Tidak Ada Tool Versioning** — Jika tool signature berubah, semua history chat yang menggunakan tool lama akan error.
3. **System Prompt Terlalu Panjang** — `buildSystemPrompt()` menghasilkan prompt yang sangat panjang. Ini mengonsumsi banyak token dan meningkatkan biaya.
4. **Tidak Ada Circuit Breaker** — Jika Gemini API down, semua request akan timeout. Perlu circuit breaker pattern.
5. **Media Upload ke `products/` Folder** — File yang diupload via chat disimpan di folder products. Ini tidak semantik dan bisa menyebabkan konflik.

### Rekomendasi AI Chat

- Implementasi circuit breaker untuk Gemini API calls
- Pisahkan tool registry per domain (inventory tools, finance tools, dll)
- Tambahkan tool result caching yang lebih granular
- Implementasi conversation summarization yang lebih agresif untuk hemat token
- Tambahkan feedback mechanism (thumbs up/down) untuk improve AI responses

---

## BAGIAN 4: AUDIT DATABASE & MIGRASI

### Temuan Positif

- **200+ migrasi** terorganisir dengan baik menggunakan timestamp
- **Composite indexes** sudah ditambahkan di migration khusus (`add_composite_indexes_for_performance.php`)
- **Soft deletes** ditambahkan ke model kritis
- **Foreign key constraints** ada di migration `add_missing_foreign_key_constraints.php`
- **utf8mb4** collation untuk support emoji dan karakter Unicode penuh

### Masalah Database

**DB-001: Migration `2026_04_09_000001_skip_inpatient_tables_migration.php`**
Ada migration dengan nama "skip" — ini mengindikasikan ada migration yang sengaja di-skip. Perlu investigasi apakah tabel inpatient benar-benar tidak diperlukan atau ada dependency yang hilang.

**DB-002: Duplikasi Tabel Integration**
Ada dua migration untuk integration tables:
- `2026_04_06_000011_create_integration_tables.php`
- `2026_04_08_000036_create_integration_tables.php`
Ini berpotensi konflik atau duplikasi kolom.

**DB-003: Tidak Ada Index di `tenant_id` + `created_at` untuk Tabel Besar**
Tabel seperti `activity_logs`, `audit_trail`, `ai_usage_logs` yang diquery dengan filter `tenant_id` + date range membutuhkan composite index `(tenant_id, created_at)`.

**DB-004: `users.role` Menggunakan ENUM**
Beberapa migration menambahkan nilai ke ENUM `users.role` (kasir, gudang, affiliate, housekeeping, maintenance, dll). ALTER TABLE untuk ENUM di MySQL bisa menyebabkan table lock pada tabel besar. Pertimbangkan migrasi ke VARCHAR dengan constraint.

**DB-005: Tidak Ada Partitioning untuk Tabel Log**
Tabel `activity_logs`, `audit_trail`, `ai_usage_logs` akan tumbuh sangat besar. Perlu table partitioning by month atau archival strategy yang lebih agresif.

---

## BAGIAN 5: AUDIT KEAMANAN

### Keamanan yang Sudah Baik ✅

1. **Multi-tenant isolation** via `BelongsToTenant` trait + `EnforceTenantIsolation` middleware
2. **CSRF protection** aktif di semua form
3. **2FA support** dengan Google Authenticator
4. **Account lockout** setelah 5 percobaan gagal
5. **Audit trail** untuk semua operasi sensitif
6. **Webhook signature verification** via HMAC
7. **API rate limiting** per plan
8. **Password hashing** dengan bcrypt
9. **Sensitive data** tidak di-log (password, token)
10. **GDPR compliance** tools tersedia

### Kerentanan yang Ditemukan

**SEC-001 (KRITIS): CSP `unsafe-eval`** — Lihat BUG-002
**SEC-002 (KRITIS): API Token via Query String** — Lihat BUG-003
**SEC-003 (TINGGI): Health Check Endpoint Terbuka** — Lihat BUG-012
**SEC-004 (TINGGI): `two_factor_secret` di `$fillable`** — Lihat BUG-008
**SEC-005 (SEDANG): Tidak Ada HSTS di Development** — Acceptable, tapi pastikan production selalu HTTPS
**SEC-006 (SEDANG): `connect-src 'self' https: ws: wss:'` di SecurityHeaders terlalu permisif** — Izinkan semua HTTPS connections. Perlu dibatasi ke domain spesifik.

---

## BAGIAN 6: TASK LIST DETAIL PER MODUL


### MODUL CORE & INFRASTRUKTUR

**TASK-CORE-001** [P0] Hapus `SecurityHeaders.php`, update `AddSecurityHeaders.php` dengan nilai yang benar
**TASK-CORE-002** [P0] Hapus fallback `api_token` query string dari `ApiTokenAuth.php`
**TASK-CORE-003** [P0] Hapus `'unsafe-eval'` dari CSP production
**TASK-CORE-004** [P1] Tambahkan `use BelongsToTenant` ke semua model dengan `tenant_id`
**TASK-CORE-005** [P1] Proteksi health check endpoint `/api/health/detailed`
**TASK-CORE-006** [P1] Tambahkan rate limiting ke route login
**TASK-CORE-007** [P2] Fix return type `AuditLogService.getLogs` ke `LengthAwarePaginator`
**TASK-CORE-008** [P2] Fix `OutputEscaper.sanitizeHtml` untuk PHP 8.3 compatibility
**TASK-CORE-009** [P2] Implementasi circuit breaker untuk Gemini API calls
**TASK-CORE-010** [P3] Hapus `window.moduleLoader` dan `window.logger` dari global scope

### MODUL DASHBOARD & ANALYTICS

**TASK-DASH-001** [P1] Fix cache key dashboard untuk include `user_id` pada data user-specific
**TASK-DASH-002** [P2] Audit N+1 queries di semua `*Stats()` methods
**TASK-DASH-003** [P2] Tambahkan skeleton loading state untuk semua widget
**TASK-DASH-004** [P2] Tambahkan error boundary per widget (jika satu widget gagal, yang lain tetap tampil)
**TASK-DASH-005** [P3] Tambahkan "Last Updated" timestamp di setiap widget
**TASK-DASH-006** [P3] Tambahkan tombol refresh manual per widget

### MODUL AI CHAT (ERP Assistant)

**TASK-AI-001** [P0] Tambahkan GeminiWriteValidator untuk semua tool write
**TASK-AI-002** [P1] Tambahkan validasi MIME type di `sendMedia` sebelum `storeAs()`
**TASK-AI-003** [P1] Pisahkan media upload chat ke folder `chat-uploads/` bukan `products/`
**TASK-AI-004** [P2] Implementasi circuit breaker untuk Gemini API
**TASK-AI-005** [P2] Modularisasi ToolRegistry per domain
**TASK-AI-006** [P2] Tambahkan feedback mechanism (thumbs up/down) di UI chat
**TASK-AI-007** [P2] Optimasi system prompt — kurangi panjang dengan template yang lebih ringkas
**TASK-AI-008** [P3] Tambahkan "AI is typing..." indicator yang lebih informatif
**TASK-AI-009** [P3] Tambahkan history export (download percakapan sebagai PDF/TXT)

### MODUL AKUNTANSI & KEUANGAN

**TASK-ACC-001** [P0] Fix validasi `parent_id` COA untuk filter tenant_id
**TASK-ACC-002** [P2] Tambahkan validasi overlap periode akuntansi
**TASK-ACC-003** [P2] Tambahkan konfirmasi sebelum lock/close periode
**TASK-ACC-004** [P2] Tambahkan validasi debit = kredit sebelum post journal entry
**TASK-ACC-005** [P3] Tambahkan fitur "Jurnal Balik" (reversing journal) otomatis
**TASK-ACC-006** [P3] Export laporan keuangan ke Excel (selain PDF yang sudah ada)

### MODUL INVENTORI & GUDANG

**TASK-INV-001** [P0] Fix validasi `warehouse_id` untuk filter tenant_id
**TASK-INV-002** [P1] Tambahkan validasi stok negatif sebelum deduction
**TASK-INV-003** [P2] Tambahkan konfirmasi untuk stock adjustment yang besar (>1000 unit)
**TASK-INV-004** [P2] Tambahkan barcode scanner support di mobile (via camera)
**TASK-INV-005** [P3] Tambahkan fitur "Stok Opname" dengan QR code scanning
**TASK-INV-006** [P3] Notifikasi real-time saat stok mendekati minimum

### MODUL PENJUALAN & CRM

**TASK-SALES-001** [P1] Validasi customer_id filter tenant_id di semua endpoint sales
**TASK-SALES-002** [P2] Tambahkan validasi harga jual tidak boleh di bawah harga beli
**TASK-SALES-003** [P2] Tambahkan fitur "Duplicate Order" untuk repeat order
**TASK-SALES-004** [P3] Tambahkan tracking status pengiriman real-time
**TASK-SALES-005** [P3] Tambahkan fitur "Customer Statement" (rekap transaksi per customer)

### MODUL PEMBELIAN & PROCUREMENT

**TASK-PUR-001** [P1] Validasi supplier_id filter tenant_id
**TASK-PUR-002** [P2] Tambahkan approval workflow untuk PO di atas threshold tertentu
**TASK-PUR-003** [P2] Tambahkan fitur "3-way matching" (PO vs GR vs Invoice)
**TASK-PUR-004** [P3] Tambahkan supplier rating otomatis berdasarkan delivery performance

### MODUL HRM & PAYROLL

**TASK-HRM-001** [P1] Tambahkan default permissions untuk role `housekeeping` dan `maintenance`
**TASK-HRM-002** [P2] Validasi tidak ada overlap shift untuk employee yang sama
**TASK-HRM-003** [P2] Tambahkan kalkulasi lembur otomatis berdasarkan shift
**TASK-HRM-004** [P3] Tambahkan fitur "Slip Gaji Digital" yang bisa diakses employee via self-service
**TASK-HRM-005** [P3] Integrasi BPJS Ketenagakerjaan untuk kalkulasi iuran otomatis

### MODUL HEALTHCARE

**TASK-HC-001** [P1] Audit semua endpoint healthcare untuk HIPAA compliance
**TASK-HC-002** [P2] Tambahkan enkripsi untuk field PHI (nama pasien, diagnosa, resep)
**TASK-HC-003** [P2] Tambahkan "Break Glass" access log yang lebih detail
**TASK-HC-004** [P3] Tambahkan integrasi BPJS Kesehatan untuk klaim otomatis
**TASK-HC-005** [P3] Tambahkan fitur telemedicine recording dengan enkripsi

### MODUL HOTEL

**TASK-HTL-001** [P2] Validasi tidak ada double booking untuk kamar yang sama
**TASK-HTL-002** [P2] Tambahkan channel manager sync yang lebih robust
**TASK-HTL-003** [P3] Tambahkan fitur "Early Check-in / Late Check-out" dengan biaya tambahan
**TASK-HTL-004** [P3] Tambahkan loyalty program terintegrasi dengan modul Loyalty

### MODUL TELECOM/ISP

**TASK-TEL-001** [P2] Tambahkan monitoring bandwidth real-time di dashboard
**TASK-TEL-002** [P2] Tambahkan auto-suspend untuk pelanggan yang melewati quota
**TASK-TEL-003** [P3] Tambahkan integrasi dengan RADIUS server untuk autentikasi
**TASK-TEL-004** [P3] Tambahkan laporan penggunaan bandwidth per pelanggan

### MODUL MANUFACTURING

**TASK-MFG-001** [P2] Validasi BOM tidak circular (A → B → A)
**TASK-MFG-002** [P2] Tambahkan kalkulasi HPP otomatis saat work order selesai
**TASK-MFG-003** [P3] Tambahkan integrasi dengan IoT sensor untuk monitoring produksi
**TASK-MFG-004** [P3] Tambahkan fitur "Production Scheduling" dengan Gantt chart

### MODUL POS

**TASK-POS-001** [P2] Tambahkan offline mode yang lebih robust (sync saat online kembali)
**TASK-POS-002** [P2] Tambahkan split payment (cash + transfer + kartu)
**TASK-POS-003** [P3] Tambahkan customer display (layar kedua untuk pelanggan)
**TASK-POS-004** [P3] Tambahkan integrasi dengan timbangan digital

### MODUL SUPER ADMIN

**TASK-SA-001** [P1] Tambahkan 2FA wajib untuk akun super admin
**TASK-SA-002** [P2] Tambahkan fitur "Impersonate Tenant" dengan audit trail lengkap
**TASK-SA-003** [P2] Tambahkan monitoring real-time penggunaan resource per tenant
**TASK-SA-004** [P3] Tambahkan fitur "Tenant Migration" untuk pindah data antar server

### SIDEBAR & NAVIGASI

**TASK-NAV-001** [P2] Tambahkan "Recently Visited" di panel sidebar (sudah ada tracker, perlu UI)
**TASK-NAV-002** [P2] Tambahkan keyboard shortcut untuk navigasi antar modul
**TASK-NAV-003** [P2] Perbaiki mobile navigation — bottom rail terlalu kecil untuk modul banyak
**TASK-NAV-004** [P3] Tambahkan "Favorites" untuk pin menu yang sering digunakan
**TASK-NAV-005** [P3] Tambahkan breadcrumb yang lebih informatif di semua halaman

---

## BAGIAN 7: PERBAIKAN ERROR JAVASCRIPT

### JS-001: `window.Alpine` Assignment Sebelum Plugin Registration
**File:** `resources/js/app.js` (line 8-12)
**Masalah:** `window.Alpine = Alpine` dilakukan sebelum `Alpine.plugin(collapse)`. Jika ada script eksternal yang mengakses `window.Alpine` sebelum plugin terdaftar, plugin tidak akan tersedia.
**Solusi:** Pindahkan `window.Alpine = Alpine` ke setelah semua plugin terdaftar, sebelum `Alpine.start()`.

### JS-002: `requestIdleCallback` Fallback Tidak Ada
**File:** `resources/js/app.js` (line ~85)
**Masalah:** `if ('requestIdleCallback' in window)` — jika browser tidak support (Safari < 16), background preloading tidak pernah berjalan.
**Solusi:** Tambahkan fallback:
```js
const idleCallback = window.requestIdleCallback || ((fn) => setTimeout(fn, 1));
idleCallback(async () => { ... });
```

### JS-003: Service Worker Error Tidak Ditangani dengan Baik
**File:** `resources/js/app.js` (line ~100)
**Masalah:** Jika service worker registration gagal, hanya di-log. Tidak ada fallback atau user notification.
**Solusi:** Tambahkan graceful degradation — tampilkan banner "Offline mode tidak tersedia" jika SW gagal.

### JS-004: `moduleLoader.load('chat')` Tanpa Timeout
**File:** `resources/js/app.js` (line ~45)
**Masalah:** Jika chat module gagal load (network error), tidak ada timeout atau retry logic.
**Solusi:** Tambahkan timeout dan retry:
```js
moduleLoader.load('chat', { timeout: 10000, retries: 2 })
```

### JS-005: Alpine.js `errorBoundary` Plugin Custom
**File:** `resources/js/app.js` (line 10)
**Masalah:** `errorBoundary` adalah plugin custom yang diimport dari `./error-boundary`. Jika file ini tidak ada atau error, Alpine tidak akan start sama sekali.
**Solusi:** Wrap dalam try-catch:
```js
try { Alpine.plugin(errorBoundary); } catch(e) { console.warn('Error boundary plugin failed', e); }
```

---

## BAGIAN 8: REKOMENDASI UI/UX

### Prinsip Desain untuk Pengguna Indonesia Pemula

Sistem ini ditujukan untuk UMKM dan bisnis Indonesia yang mungkin baru pertama kali menggunakan ERP. Berikut rekomendasi UI/UX:

### UX-001: Simplifikasi Onboarding
**Masalah:** Terlalu banyak modul yang langsung terlihat saat pertama login.
**Solusi:**
- Tampilkan hanya 3-5 modul paling relevan berdasarkan `business_type`
- Sembunyikan modul advanced sampai user siap
- Tambahkan "Mode Pemula" vs "Mode Lengkap" toggle

### UX-002: Bahasa yang Lebih Ramah
**Masalah:** Beberapa label masih menggunakan istilah teknis akuntansi (COA, GL, Debit/Kredit).
**Solusi:**
- Tambahkan tooltip penjelasan untuk istilah teknis
- Gunakan bahasa sehari-hari: "Daftar Akun" bukan "Chart of Accounts"
- Tambahkan contoh penggunaan di setiap form

### UX-003: Mobile-First untuk POS dan Inventory
**Masalah:** Sidebar rail 56px terlalu kecil di mobile. Bottom navigation tidak cukup untuk 20+ modul.
**Solusi:**
- Buat dedicated mobile layout untuk POS (full screen, touch-optimized)
- Gunakan bottom sheet navigation untuk mobile
- Tambahkan gesture support (swipe untuk navigasi)

### UX-004: Loading States yang Lebih Informatif
**Masalah:** Beberapa halaman menampilkan blank screen saat loading data.
**Solusi:**
- Tambahkan skeleton screens untuk semua list/table
- Tambahkan progress indicator untuk operasi panjang (export, import)
- Tambahkan "Estimated time" untuk operasi AI

### UX-005: Error Messages yang Lebih Helpful
**Masalah:** Error messages terlalu teknis ("Validation failed", "500 Internal Server Error").
**Solusi:**
- Tulis error message dalam Bahasa Indonesia yang jelas
- Tambahkan "Apa yang harus dilakukan" di setiap error
- Tambahkan tombol "Laporkan Masalah" di error page

### UX-006: Konfirmasi untuk Aksi Destruktif
**Masalah:** Beberapa aksi delete/void tidak memiliki konfirmasi yang cukup jelas.
**Solusi:**
- Tambahkan modal konfirmasi dengan teks yang menjelaskan konsekuensi
- Untuk aksi kritis (hapus data keuangan), minta user ketik "HAPUS" untuk konfirmasi
- Tambahkan "Undo" untuk aksi yang bisa dibatalkan

### UX-007: Dashboard yang Lebih Personal
**Masalah:** Dashboard menampilkan semua widget sekaligus — overwhelming untuk pemula.
**Solusi:**
- Tampilkan "Daily Briefing" — 3 hal terpenting hari ini
- Tambahkan "Quick Actions" yang kontekstual (berdasarkan waktu dan aktivitas terakhir)
- Tambahkan "Tips & Tricks" untuk fitur yang belum pernah digunakan

### UX-008: Responsif di Semua Ukuran Layar
**Masalah:** Beberapa tabel dan form tidak responsif di layar kecil (< 375px).
**Solusi:**
- Audit semua tabel — tambahkan horizontal scroll atau card view di mobile
- Semua form harus single-column di mobile
- Tombol aksi harus full-width di mobile

### UX-009: Dark Mode yang Konsisten
**Masalah:** Beberapa komponen tidak memiliki dark mode yang proper (warna hardcoded).
**Solusi:**
- Audit semua komponen untuk dark mode consistency
- Gunakan CSS variables untuk semua warna
- Tambahkan "System" option di theme selector (ikuti OS preference)

### UX-010: Aksesibilitas Dasar
**Masalah:** Beberapa elemen interaktif tidak memiliki label yang proper untuk screen reader.
**Solusi:**
- Tambahkan `aria-label` ke semua icon buttons
- Pastikan semua form input memiliki `<label>` yang terhubung
- Tambahkan `role` attribute yang tepat untuk custom components
- Pastikan color contrast ratio minimal 4.5:1 untuk teks

---

## RINGKASAN EKSEKUTIF

Qalcuity ERP adalah sistem yang **ambisius dan komprehensif** dengan arsitektur yang solid. Fondasi keamanan multi-tenant sudah baik dengan `BelongsToTenant` trait, `EnforceTenantIsolation` middleware, dan audit trail yang lengkap.

**5 Hal yang Harus Diperbaiki Segera:**
1. Hapus duplikasi middleware security headers (BUG-001)
2. Hapus `unsafe-eval` dari CSP production (BUG-002)
3. Hapus API token via query string (BUG-003)
4. Fix validasi warehouse_id untuk tenant isolation (BUG-004)
5. Tambahkan GeminiWriteValidator untuk semua tool write (BUG-005)

**3 Improvement Terpenting:**
1. Standardisasi `BelongsToTenant` di semua model
2. Centralized permission registry
3. Redis untuk session & cache di production

**Kondisi AI ERP Chat:** Baik. Intent detection dan caching sudah efektif. Perlu circuit breaker dan modularisasi tool registry.

**Kondisi UI/UX:** Perlu perhatian khusus untuk mobile experience dan simplifikasi untuk pengguna pemula Indonesia.

---
*Laporan ini dibuat berdasarkan audit kode statis. Disarankan untuk melakukan penetration testing dan load testing sebelum go-live production.*
