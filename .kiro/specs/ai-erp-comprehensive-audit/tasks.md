# Rencana Implementasi — Audit Menyeluruh AI ERP (Qalcuity ERP)

- [x] 1. Tulis test eksplorasi bug condition (sebelum implementasi fix)
  - **Property 1: Bug Condition** — Sidebar, Dark Mode, Modul ERP, Keamanan, Performa
  - **PENTING**: Tulis semua test ini SEBELUM mengimplementasikan perbaikan apapun
  - **TUJUAN**: Membuktikan bug ada dan memahami root cause melalui counterexample
  - **Pendekatan PBT**: Scope property ke kasus konkret yang gagal untuk reprodusibilitas
  - Sidebar 1.1: Test `countActiveRailButtons(route)` untuk route yang overlap dua grup — assert `=== 1`, akan mengembalikan 2
  - Sidebar 1.2: Test sinkronisasi `--group-color` setelah klik rail button — assert warna tersinkronisasi, akan gagal
  - Sidebar 1.3: Test item submenu ter-highlight saat panel dibuka — assert `active` class ada, akan gagal
  - Sidebar 1.4: Test route modul baru (hotel, telecom) di `$activeGroup` — assert rail aktif, akan return `''`
  - Sidebar 1.5: Test mobile sidebar mutual exclusion — assert hanya satu layer terlihat, akan overlap
  - Dark Mode 1.6: Test komponen modal/card di dark mode — assert tidak ada `bg-white` tanpa `dark:`, akan gagal
  - Dark Mode 1.7: Test elemen dengan inline style di dark mode — assert inline style tidak override tema, akan gagal
  - Dark Mode 1.8: Test FOUC dengan `theme=system` — assert tidak ada flash putih, akan terdeteksi flash
  - Dark Mode 1.9: Test Chart.js merespons event `theme-changed` — assert warna chart berubah, akan gagal
  - Dark Mode 1.10: Test form input di dark mode — assert background gelap, akan tetap putih
  - Layout 1.11: Test topbar dengan banyak aksi — assert tombol di page header bukan topbar, akan overflow
  - Layout 1.12: Test breadcrumb di mobile view — assert breadcrumb terlihat, akan hidden
  - Layout 1.13: Test breadcrumb panjang — assert tooltip muncul saat hover, akan tidak ada tooltip
  - Keuangan 1.14: Buat `AccountingPeriod` status `locked`, panggil `createJournalEntry()` — assert exception, akan berhasil tanpa exception
  - Inventori 1.15: Simulasikan 2 concurrent request pengurangan stok — assert stok tidak negatif, akan bisa negatif
  - CRM 1.16: Konversi lead dengan email duplikat — assert customer existing digunakan, akan buat duplikat
  - Payroll 1.17: Hitung formula dengan komponen `null` — assert default 0, akan error
  - Proyek 1.18: Update progress dengan `actualVolume > plannedVolume` — assert progress ≤ 100, akan melebihi 100
  - Manufacturing 1.19: BOM dengan 3 level sub-assembly — assert semua komponen level 3 ada, akan tidak muncul
  - Hotel 1.20: Night audit dengan reservasi tanpa room rate — assert exception dengan daftar error, akan posting nilai 0
  - Telecom 1.21: Simulasikan MikroTik timeout — assert job di-release dengan backoff, akan fail tanpa retry
  - Akuntansi 1.22: Generate balance sheet tidak balance — assert warning ditampilkan, akan tidak ada warning
  - E-Commerce 1.23: Simulasikan HTTP 429 dari marketplace — assert job di-release dengan backoff, akan fail
  - Keamanan 1.24: Query model tanpa manual `where tenant_id` — assert TenantScope otomatis, akan bocor
  - Keamanan 1.25: Kirim prompt injection ke AI Chat — assert input disanitasi, akan diteruskan langsung
  - Keamanan 1.26: Download export milik tenant lain — assert 404, akan berhasil download
  - Performa 1.27: Load dashboard, hitung query count — assert ≤ threshold, akan melebihi
  - Performa 1.28: Kirim 61 AI request/menit per tenant — assert request ke-61 mendapat 429, akan berhasil
  - Jalankan semua test pada kode UNFIXED — **EXPECTED OUTCOME: SEMUA GAGAL** (membuktikan bug ada)
  - Dokumentasikan counterexample yang ditemukan untuk setiap bug
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7, 1.8, 1.9, 1.10, 1.11, 1.12, 1.13, 1.14, 1.15, 1.16, 1.17, 1.18, 1.19, 1.20, 1.21, 1.22, 1.23, 1.24, 1.25, 1.26, 1.27, 1.28_


- [x] 2. Tulis preservation property tests (SEBELUM implementasi fix)
  - **Property 2: Preservation** — Behavior yang sudah benar tidak boleh berubah
  - **PENTING**: Ikuti metodologi observation-first — observasi kode UNFIXED untuk input non-buggy
  - Observasi: SuperAdmin login → hanya menu Dashboard dan Admin terlihat di rail sidebar
  - Observasi: Kasir login → menu Keuangan, Operasional, Pengaturan tersembunyi
  - Observasi: Klik logo sidebar → diarahkan ke halaman dashboard
  - Observasi: Set tema, refresh → tema sama setelah refresh (localStorage persisten)
  - Observasi: Mode `system` dengan OS dark → tema dark diterapkan
  - Observasi: Event `theme-changed` dikirim ke semua listener yang terdaftar
  - Observasi: Sales order → invoice → payment → jurnal otomatis terbuat (alur end-to-end)
  - Observasi: Query dari tenant A tidak mengembalikan data tenant B
  - Observasi: AI Chat merespons dengan konteks tenant yang benar
  - Observasi: Queue job diproses asinkron tanpa memblokir HTTP request
  - Observasi: API token validation dan rate limiting berfungsi
  - Observasi: Export Excel/PDF menghasilkan file valid
  - Observasi: Scheduled commands (cron) berjalan sesuai jadwal
  - Observasi: Jurnal berhasil dibuat untuk periode `open` (non-buggy case)
  - Observasi: Pengurangan stok single-warehouse tanpa concurrent tetap berfungsi
  - Tulis property-based test: untuk semua input yang TIDAK memenuhi isBugCondition, output F = output F'
  - Jalankan test pada kode UNFIXED — **EXPECTED OUTCOME: SEMUA LULUS** (konfirmasi baseline)
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.8, 3.9, 3.10, 3.11, 3.12, 3.13, 3.14, 3.15_


- [x] 3. Fix Sidebar & Navigasi (Bug 1.1–1.5)

  - [x] 3.1 Perbaiki double active rail button (Bug 1.1)
    - Refactor `$activeGroup` di `resources/views/layouts/sidebar.blade.php`
    - Ganti `match` expression yang overlap dengan fungsi `resolveActiveGroup()` berbasis array prioritas
    - Definisikan `$groupMap` dengan semua modul: keuangan, inventori, crm, payroll, proyek, manufacturing, hotel, telecom, akuntansi, ecommerce
    - Gunakan `foreach` dengan early return — grup pertama yang cocok menang
    - Pastikan setiap route hanya bisa cocok dengan satu grup
    - _Bug_Condition: `countActiveRailButtons(route) != 1` — route cocok dengan lebih dari satu pola_
    - _Expected_Behavior: `resolveActiveGroup()` mengembalikan tepat satu string grup atau `''`_
    - _Preservation: SuperAdmin hanya melihat Dashboard dan Admin; role-based menu tetap berfungsi_
    - _Requirements: 2.1, 3.1, 3.2_

  - [x] 3.2 Perbaiki sinkronisasi warna aksen rail ↔ panel (Bug 1.2)
    - Update Alpine.js handler di `resources/js/sidebar.js` atau komponen Alpine sidebar
    - Saat rail button diklik, set CSS custom property `--group-color` di `document.documentElement`
    - Update juga `--group-color` di `#sidebar-panel-header` untuk sinkronisasi glow dot dan panel accent line
    - Pastikan warna di-set saat transisi, bukan hanya saat inisialisasi
    - _Bug_Condition: `NOT colorsSynced(railButton, panel)` setelah klik_
    - _Expected_Behavior: `--group-color` tersinkronisasi antara rail button, glow dot, dan panel header_
    - _Requirements: 2.2_

  - [x] 3.3 Perbaiki auto-active item submenu (Bug 1.3)
    - Update `resources/views/layouts/sidebar.blade.php` — tambahkan `Route::is()` check di setiap item submenu
    - Gunakan `{{ Route::is('invoices.*') ? 'active' : '' }}` atau Alpine.js `x-bind:class`
    - Untuk Alpine.js: tambahkan Alpine store yang menyimpan route aktif saat ini
    - Pastikan item submenu ter-highlight saat panel pertama kali dibuka tanpa interaksi tambahan
    - _Bug_Condition: `NOT submenuItemMarked(route, panel)` saat panel dibuka_
    - _Expected_Behavior: item submenu yang sesuai route aktif memiliki class `active`_
    - _Requirements: 2.3_

  - [x] 3.4 Tambahkan semua route modul ke activeGroup (Bug 1.4)
    - Pastikan `resolveActiveGroup()` dari task 3.1 mencakup semua route modul: hotel, telecom, manufacturing, dll.
    - Tambahkan unit test untuk setiap route baru yang ditambahkan ke sistem
    - Verifikasi route `hotel.night-audit`, `telecom.mikrotik.*`, dan route legacy terdaftar
    - _Bug_Condition: `NOT routeCovered(route, activeGroupMap)` — route tidak ada di map_
    - _Expected_Behavior: setiap route valid mengembalikan grup yang sesuai_
    - _Requirements: 2.4_

  - [x] 3.5 Perbaiki z-index conflict mobile sidebar (Bug 1.5)
    - Tambahkan Alpine.js store `sidebar` dengan state `overlayOpen` dan `panelOpen`
    - Implementasikan mutual exclusion: `openOverlay()` menutup panel, `openPanel()` menutup overlay
    - Set z-index hierarchy di Tailwind: overlay `z-40`, panel `z-50`, rail `z-60`
    - Pastikan `closeAll()` menutup keduanya saat backdrop diklik
    - _Bug_Condition: `isMobile AND layersOverlapping(input)` — overlay dan panel terbuka bersamaan_
    - _Expected_Behavior: hanya satu layer navigasi terlihat pada satu waktu di mobile_
    - _Requirements: 2.5_


- [x] 4. Fix Dark/Light Mode (Bug 1.6–1.10)

  - [x] 4.1 Tambahkan dark class ke semua komponen (Bug 1.6)
    - Audit semua komponen Blade: modal, dropdown, card widget, tabel di semua modul
    - Ganti `bg-white` dengan `bg-white dark:bg-slate-800` di setiap komponen
    - Buat Blade component base (`x-card`, `x-modal`, `x-table`) yang sudah include dark mode class secara default
    - Gunakan komponen base tersebut di seluruh halaman untuk konsistensi
    - _Bug_Condition: `existsHardcodedBgWhite(component)` — `bg-white` tanpa `dark:` equivalent_
    - _Expected_Behavior: semua komponen merespons perubahan tema secara konsisten_
    - _Requirements: 2.6_

  - [x] 4.2 Ganti inline style hardcoded dengan Tailwind class (Bug 1.7)
    - Cari semua `style="background: #fff"` dan `style="color: #000"` di seluruh view
    - Ganti dengan class Tailwind `bg-white dark:bg-slate-800 text-gray-900 dark:text-gray-100`
    - Untuk library pihak ketiga yang tidak bisa dihindari, gunakan CSS variable: `:root { --widget-bg: #fff }` dan `.dark { --widget-bg: #1e293b }`
    - _Bug_Condition: `existsInlineStyle(component)` — inline style memiliki specificity lebih tinggi dari `dark:`_
    - _Expected_Behavior: tidak ada inline style yang mengoverride tema_
    - _Requirements: 2.7_

  - [x] 4.3 Perbaiki script inisialisasi tema untuk mencegah FOUC (Bug 1.8)
    - Update `resources/views/layouts/app.blade.php` bagian `<head>`
    - Ganti script inisialisasi dengan IIFE yang menangani `theme === 'dark'`, `theme === 'light'`, dan `theme === 'system'`
    - Untuk `system`: gunakan `window.matchMedia('(prefers-color-scheme: dark)').matches`
    - Script harus dijalankan SEBELUM render pertama (inline di `<head>`, bukan defer/async)
    - _Bug_Condition: `event = 'page_load' AND FOUC_detected()` — flash putih sebelum dark mode diterapkan_
    - _Expected_Behavior: tema diterapkan sebelum render pertama, tidak ada FOUC_
    - _Requirements: 2.8_

  - [x] 4.4 Tambahkan event listener tema ke komponen pihak ketiga (Bug 1.9)
    - Update `resources/js/theme-manager.js` — tambahkan `window.dispatchEvent(new CustomEvent('theme-changed', ...))`
    - Tambahkan listener `theme-changed` di inisialisasi Chart.js untuk update warna legend, ticks, dan grid
    - Tambahkan listener `theme-changed` di inisialisasi Flatpickr untuk update theme
    - Tambahkan listener `theme-changed` di inisialisasi Select2/Choices.js jika digunakan
    - _Bug_Condition: `NOT thirdPartyResponds(component)` — chart/select tidak update saat tema berubah_
    - _Expected_Behavior: semua komponen pihak ketiga merespons event `theme-changed`_
    - _Requirements: 2.9, 3.7_

  - [x] 4.5 Tambahkan dark style ke semua elemen form (Bug 1.10)
    - Tambahkan ke `resources/css/app.css` di `@layer base`: style untuk `input`, `select`, `textarea`
    - Gunakan `@apply bg-white dark:bg-slate-700 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-slate-600`
    - Pastikan focus ring juga dark-aware: `focus:ring-blue-500 dark:focus:ring-blue-400`
    - _Bug_Condition: `NOT formElementDarkStyled(formElement)` — form tetap putih di dark mode_
    - _Expected_Behavior: semua elemen form menampilkan background dan text color yang sesuai dark mode_
    - _Requirements: 2.10_


- [x] 5. Fix Layout & Breadcrumb (Bug 1.11–1.13)

  - [x] 5.1 Pindahkan topbar actions ke page header section (Bug 1.11)
    - Update `resources/views/layouts/app.blade.php` — hapus slot `$topbarActions` dari navbar
    - Tambahkan `@if(isset($pageHeader))` section di dalam area konten dengan layout flex justify-between
    - Update semua halaman yang menggunakan `@slot('topbarActions')` untuk menggunakan `@slot('pageHeader')`
    - Pastikan page header section responsif di semua ukuran layar
    - _Bug_Condition: `hasTopbarActions AND topbarOverflows(input)` — topbar penuh di layar kecil_
    - _Expected_Behavior: tombol aksi berada di page header section dalam konten, bukan di topbar global_
    - _Requirements: 2.11_

  - [x] 5.2 Tampilkan breadcrumb ringkas di mobile (Bug 1.12)
    - Update `resources/views/components/breadcrumb.blade.php`
    - Ganti `hidden sm:block` dengan dua versi: desktop (breadcrumb penuh) dan mobile (hanya halaman aktif)
    - Mobile: tampilkan `<nav class="flex sm:hidden">` dengan nama halaman aktif saja
    - Desktop: tampilkan `<nav class="hidden sm:flex">` dengan breadcrumb lengkap
    - _Bug_Condition: `isMobile AND NOT breadcrumbVisible()` — breadcrumb hidden di mobile_
    - _Expected_Behavior: breadcrumb atau indikator halaman aktif terlihat di semua ukuran layar_
    - _Requirements: 2.12_

  - [x] 5.3 Tambahkan tooltip untuk breadcrumb panjang (Bug 1.13)
    - Update `resources/views/components/breadcrumb.blade.php`
    - Tambahkan Alpine.js tooltip pada item breadcrumb dengan `x-data="{ show: false }"`
    - Gunakan `@mouseenter` dan `@mouseleave` untuk toggle tooltip
    - Tampilkan tooltip hanya jika panjang teks > 20 karakter
    - Tambahkan atribut `title` sebagai fallback untuk aksesibilitas
    - _Bug_Condition: `breadcrumbText.length > 40 AND NOT hasTooltip()` — teks terpotong tanpa tooltip_
    - _Expected_Behavior: tooltip muncul saat hover pada breadcrumb yang terpotong_
    - _Requirements: 2.13_


- [x] 6. Fix Modul ERP — Keuangan & Inventori (Bug 1.14–1.15)

  - [x] 6.1 Validasi status accounting period sebelum buat jurnal (Bug 1.14)
    - Update `app/Services/JournalService.php` — tambahkan guard clause di `createJournalEntry()`
    - Query `AccountingPeriod` berdasarkan `tenant_id`, `year`, dan `month`
    - Jika status `locked` atau `closed`, lempar `\DomainException` dengan pesan yang jelas
    - Update `app/Jobs/ProcessRecurringJournals.php` untuk menangkap exception ini
    - _Bug_Condition: `module = 'keuangan' AND NOT periodValidated(input)` — jurnal masuk ke periode locked_
    - _Expected_Behavior: `DomainException` dilempar dengan pesan "periode dalam status {status}"_
    - _Preservation: jurnal berhasil dibuat untuk periode `open`_
    - _Requirements: 2.14, 3.8_

  - [x] 6.2 Tambahkan database transaction dan pessimistic locking untuk stok (Bug 1.15)
    - Update `app/Services/InventoryService.php` — wrap `deductStockMultiWarehouse()` dengan `DB::transaction()`
    - Tambahkan `->lockForUpdate()` pada query `WarehouseStock` di dalam transaction
    - Tambahkan validasi stok cukup sebelum decrement, lempar `DomainException` jika tidak cukup
    - _Bug_Condition: `module = 'inventori' AND NOT usesTransaction(input)` — race condition stok negatif_
    - _Expected_Behavior: stok tidak pernah negatif meski ada concurrent requests_
    - _Preservation: pengurangan stok single-warehouse tanpa concurrent tetap berfungsi_
    - _Requirements: 2.15, 3.8_


- [x] 7. Fix Modul ERP — CRM, Payroll, Proyek (Bug 1.16–1.18)

  - [x] 7.1 Tambahkan dedup check saat konversi lead ke customer (Bug 1.16)
    - Update `app/Services/CrmService.php` — tambahkan query cek duplikat di `convertLeadToCustomer()`
    - Cek `Customer::where('email', $lead->email)->orWhere('phone', $lead->phone)` dalam scope tenant
    - Jika customer sudah ada, gunakan customer existing dan update lead dengan `converted_to_customer_id`
    - Jika belum ada, buat customer baru seperti biasa
    - _Bug_Condition: `module = 'crm' AND NOT duplicateChecked(input)` — duplikat customer dibuat_
    - _Expected_Behavior: customer existing digunakan, tidak ada duplikat_
    - _Preservation: konversi lead dengan email baru tetap membuat customer baru_
    - _Requirements: 2.16_

  - [x] 7.2 Tambahkan null-safe handling di formula payroll (Bug 1.17)
    - Update `app/Services/PayrollCalculationService.php` — tambahkan null coalescing di `evaluateFormula()`
    - Gunakan `array_map(fn($v) => $v ?? 0.0, $components)` sebelum evaluasi formula
    - Wrap evaluasi dalam `try-catch \Throwable` dan lempar `DomainException` dengan pesan yang jelas
    - _Bug_Condition: `module = 'payroll' AND nullComponentUnhandled(input)` — error kalkulasi saat null_
    - _Expected_Behavior: nilai null diganti 0, formula dievaluasi tanpa error_
    - _Requirements: 2.17_

  - [x] 7.3 Batasi progress task maksimum 100% (Bug 1.18)
    - Update `app/Services/ProjectService.php` — tambahkan `min(100.0, ...)` di `updateTaskProgress()`
    - Tambahkan validasi `planned_volume > 0` sebelum kalkulasi, lempar `DomainException` jika tidak valid
    - Simpan `progress = min(100.0, (actualVolume / plannedVolume) * 100)` ke database
    - _Bug_Condition: `module = 'proyek' AND progressExceeds100(input)` — progress > 100%_
    - _Expected_Behavior: progress selalu dalam range 0–100_
    - _Requirements: 2.18_


- [x] 8. Fix Modul ERP — Manufacturing, Hotel, Telecom (Bug 1.19–1.21)

  - [x] 8.1 Implementasikan recursive BOM explosion (Bug 1.19)
    - Update `app/Services/ManufacturingService.php` — refactor `explodeBom()` menjadi rekursif
    - Tambahkan parameter `$depth = 0` dan guard clause `if ($depth > 10)` untuk mencegah circular reference
    - Untuk setiap komponen yang `is_sub_assembly`, panggil `explodeBom()` secara rekursif dengan `$depth + 1`
    - Merge hasil rekursi ke array result utama
    - _Bug_Condition: `module = 'manufacturing' AND bomDepth > 2 AND NOT recursiveExplosion(input)`_
    - _Expected_Behavior: semua komponen semua level (termasuk level 3+) ada di hasil explosion_
    - _Requirements: 2.19_

  - [x] 8.2 Tambahkan pre-validation sebelum night audit (Bug 1.20)
    - Update `app/Services/HotelNightAuditService.php` — tambahkan fase validasi di `runNightAudit()`
    - Iterasi semua reservasi `checked_in` dengan eager load `room.rateType`
    - Kumpulkan semua error (room rate tidak valid atau 0) ke array `$errors`
    - Jika `$errors` tidak kosong, lempar `DomainException` dengan daftar item bermasalah
    - Lanjutkan posting hanya jika validasi berhasil
    - _Bug_Condition: `module = 'hotel' AND NOT rateValidated(input)` — posting dengan nilai 0_
    - _Expected_Behavior: exception dengan daftar reservasi bermasalah sebelum posting dimulai_
    - _Requirements: 2.20_

  - [x] 8.3 Tambahkan graceful timeout dan exponential backoff untuk MikroTik sync (Bug 1.21)
    - Update `app/Jobs/Telecom/SyncMikrotikJob.php` (atau file serupa)
    - Set `public int $tries = 5` dan `public array $backoff = [30, 60, 120, 300, 600]`
    - Wrap koneksi dalam `try-catch ConnectionException` — log warning dan `$this->release(backoff)`
    - Set timeout koneksi 10 detik di `MikrotikClient`
    - Re-throw exception non-connection untuk error yang tidak terduga
    - _Bug_Condition: `module = 'telecom' AND NOT gracefulTimeout(input)` — job fail tanpa retry_
    - _Expected_Behavior: job di-release dengan backoff, tidak fail permanen karena timeout_
    - _Requirements: 2.21_


- [x] 9. Fix Modul ERP — Akuntansi & E-Commerce (Bug 1.22–1.23)

  - [x] 9.1 Tambahkan validasi keseimbangan balance sheet (Bug 1.22)
    - Update `app/Services/AccountingReportService.php` — tambahkan assertion di `generateBalanceSheet()`
    - Hitung `$difference = abs($totalAssets - ($totalLiabilities + $totalEquity))`
    - Jika `$difference > 0.01` (toleransi pembulatan), tambahkan `balance_warning` ke report dengan pesan dan selisih
    - Tampilkan warning di view laporan neraca jika `balance_warning.is_balanced === false`
    - _Bug_Condition: `module = 'akuntansi' AND NOT balanceValidated(input)` — laporan tidak balance tanpa warning_
    - _Expected_Behavior: warning ditampilkan dengan selisih jika persamaan akuntansi tidak terpenuhi_
    - _Requirements: 2.22_

  - [x] 9.2 Implementasikan exponential backoff untuk marketplace sync (Bug 1.23)
    - Update `app/Jobs/SyncMarketplaceStock.php`
    - Set `public int $tries = 10`
    - Tambahkan `try-catch RateLimitException` — hitung delay `min(600, pow(2, $this->attempts()) * 10)`
    - Log info rate limit dan panggil `$this->release($delay)`
    - Re-throw `MarketplaceApiException` untuk error non-rate-limit
    - _Bug_Condition: `module = 'ecommerce' AND NOT rateLimitHandled(input)` — job fail saat HTTP 429_
    - _Expected_Behavior: job di-release dengan exponential backoff, tidak fail permanen_
    - _Requirements: 2.23_


- [x] 10. Fix Keamanan & Multi-Tenant (Bug 1.24–1.26)

  - [x] 10.1 Implementasikan Global Scope TenantScope (Bug 1.24)
    - Buat `app/Models/Scopes/TenantScope.php` — implementasikan `Scope` interface dengan method `apply()`
    - Resolve `$tenantId` dari `auth()->user()->tenant_id`, header `X-Tenant-ID`, atau session
    - Buat trait `app/Models/Concerns/BelongsToTenant.php` dengan `bootBelongsToTenant()` yang mendaftarkan scope
    - Tambahkan `use BelongsToTenant;` ke semua model yang memiliki kolom `tenant_id`
    - Hapus semua `where('tenant_id', ...)` manual yang redundan setelah scope ditambahkan
    - _Bug_Condition: `type = 'data_query' AND NOT hasTenantScope(query)` — data bocor antar tenant_
    - _Expected_Behavior: TenantScope otomatis menambahkan filter tenant di semua query_
    - _Preservation: isolasi data multi-tenant tetap terjaga_
    - _Requirements: 2.24, 3.9_

  - [x] 10.2 Implementasikan sanitasi input AI untuk mencegah prompt injection (Bug 1.25)
    - Update `app/Services/AiChatService.php` — tambahkan method `sanitizeUserInput()`
    - Batasi panjang input maksimum 2000 karakter dengan `mb_substr()`
    - Hapus pola prompt injection dengan regex: "ignore previous instructions", "forget everything", "you are now a", dll.
    - Ganti pola yang cocok dengan `[FILTERED]`
    - Panggil `strip_tags()` untuk menghapus HTML
    - Update `buildPrompt()` untuk selalu memanggil `sanitizeUserInput()` sebelum menyusun prompt
    - _Bug_Condition: `type = 'ai_chat' AND NOT inputSanitized(prompt)` — prompt injection berhasil_
    - _Expected_Behavior: pola injection dihapus, konteks tenant tidak bisa dimanipulasi_
    - _Preservation: AI Chat tetap merespons dengan konteks tenant yang benar_
    - _Requirements: 2.25, 3.10_

  - [x] 10.3 Validasi kepemilikan file export sebelum download (Bug 1.26)
    - Update `app/Http/Controllers/ExportController.php` — tambahkan validasi `tenant_id` di `download()`
    - Query `ExportJob::where('token', $exportToken)->where('tenant_id', auth()->user()->tenant_id)`
    - Gunakan `firstOrFail()` — otomatis 404 jika token tidak ditemukan atau beda tenant
    - Pastikan token export menggunakan UUID (`Str::uuid()`) bukan path yang bisa ditebak
    - Simpan file di path `exports/{tenantId}/{uuid}.xlsx` untuk isolasi storage
    - _Bug_Condition: `type = 'export' AND NOT ownershipValidated(file)` — download file tenant lain berhasil_
    - _Expected_Behavior: 404 jika `tenant_id` file tidak cocok dengan tenant pengguna_
    - _Preservation: export Excel/PDF tetap menghasilkan file valid untuk tenant yang benar_
    - _Requirements: 2.26, 3.13_


- [x] 11. Fix Performa (Bug 1.27–1.28)

  - [x] 11.1 Optimasi dashboard dengan eager loading dan query caching (Bug 1.27)
    - Update `app/Http/Controllers/DashboardController.php`
    - Bungkus semua query dashboard dalam `Cache::remember($cacheKey, 3600, fn() => [...])`
    - Gunakan cache key berbasis `tenant_id` dan jam: `"dashboard:{$tenantId}:" . now()->format('Y-m-d-H')`
    - Tambahkan eager loading: `with(['category', 'warehouseStocks'])` untuk inventory
    - Gunakan selective eager loading: `with(['customer:id,name', 'items.product:id,name'])` untuk transaksi
    - Gunakan `selectRaw` dengan `groupBy` untuk agregasi revenue, bukan query per-record
    - _Bug_Condition: `type = 'dashboard_load' AND queryCount > N1_threshold` — N+1 query_
    - _Expected_Behavior: query count ≤ threshold, cache hit setelah load pertama_
    - _Requirements: 2.27_

  - [x] 11.2 Implementasikan rate limiting AI per tenant (Bug 1.28)
    - Buat `app/Http/Middleware/RateLimitAiRequests.php`
    - Resolve `$tenantId` dari `auth()->user()->tenant_id`
    - Gunakan `RateLimiter::tooManyAttempts("ai_requests:{$tenantId}", 60)` dengan window 60 detik
    - Jika melebihi limit, return JSON 429 dengan `error` dan `retry_after`
    - Jika belum melebihi, panggil `$this->limiter->hit($key, 60)` dan lanjutkan request
    - Daftarkan middleware ke route AI Chat di `bootstrap/app.php` atau `RouteServiceProvider`
    - _Bug_Condition: `type = 'ai_request' AND NOT rateLimited(tenant)` — satu tenant menghabiskan quota_
    - _Expected_Behavior: HTTP 429 dengan `retry_after` setelah 60 request/menit per tenant_
    - _Preservation: AI Chat tetap merespons normal dalam batas rate limit_
    - _Requirements: 2.28, 3.12_


- [x] 12. Verifikasi fix checking — jalankan ulang test eksplorasi

  - [x] 12.1 Verifikasi fix sidebar & navigasi (Bug 1.1–1.5)
    - **Property 1: Expected Behavior** — Sidebar Active State Eksklusif
    - **PENTING**: Jalankan ulang test YANG SAMA dari task 1 — JANGAN tulis test baru
    - Jalankan test sidebar dari task 1 pada kode yang sudah diperbaiki
    - Assert: `countActiveRailButtons(route) === 1` untuk semua route — harus LULUS
    - Assert: `--group-color` tersinkronisasi setelah klik rail button — harus LULUS
    - Assert: item submenu ter-highlight saat panel dibuka — harus LULUS
    - Assert: route modul baru mengembalikan rail aktif yang benar — harus LULUS
    - Assert: mobile sidebar hanya menampilkan satu layer — harus LULUS
    - **EXPECTED OUTCOME: SEMUA LULUS** (konfirmasi bug 1.1–1.5 teratasi)
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5_

  - [x] 12.2 Verifikasi fix dark/light mode (Bug 1.6–1.10)
    - **Property 1: Expected Behavior** — Dark Mode Konsistensi Komponen
    - Jalankan ulang test dark mode dari task 1 pada kode yang sudah diperbaiki
    - Assert: tidak ada komponen dengan `bg-white` tanpa `dark:` — harus LULUS
    - Assert: inline style tidak mengoverride tema — harus LULUS
    - Assert: tidak ada FOUC saat load dengan `theme=system` — harus LULUS
    - Assert: Chart.js memperbarui warna setelah `theme-changed` — harus LULUS
    - Assert: form input background gelap di dark mode — harus LULUS
    - **EXPECTED OUTCOME: SEMUA LULUS** (konfirmasi bug 1.6–1.10 teratasi)
    - _Requirements: 2.6, 2.7, 2.8, 2.9, 2.10_

  - [x] 12.3 Verifikasi fix layout & breadcrumb (Bug 1.11–1.13)
    - **Property 1: Expected Behavior** — Layout Topbar dan Breadcrumb
    - Jalankan ulang test layout dari task 1 pada kode yang sudah diperbaiki
    - Assert: tombol aksi berada di page header section, bukan topbar — harus LULUS
    - Assert: breadcrumb terlihat di mobile view — harus LULUS
    - Assert: tooltip muncul saat hover pada breadcrumb panjang — harus LULUS
    - **EXPECTED OUTCOME: SEMUA LULUS** (konfirmasi bug 1.11–1.13 teratasi)
    - _Requirements: 2.11, 2.12, 2.13_

  - [x] 12.4 Verifikasi fix modul ERP (Bug 1.14–1.23)
    - **Property 1: Expected Behavior** — Validasi Bisnis Modul ERP
    - Jalankan ulang test modul ERP dari task 1 pada kode yang sudah diperbaiki
    - Assert: `DomainException` dilempar untuk jurnal ke periode locked — harus LULUS
    - Assert: stok tidak negatif saat concurrent requests — harus LULUS
    - Assert: customer existing digunakan saat konversi lead duplikat — harus LULUS
    - Assert: formula payroll dengan null component menghasilkan 0, tidak error — harus LULUS
    - Assert: progress task dibatasi di 100% — harus LULUS
    - Assert: BOM explosion level 3+ menghasilkan semua komponen — harus LULUS
    - Assert: night audit melempar exception untuk reservasi tanpa room rate — harus LULUS
    - Assert: MikroTik job di-release dengan backoff saat timeout — harus LULUS
    - Assert: balance sheet menampilkan warning jika tidak balance — harus LULUS
    - Assert: marketplace sync job di-release dengan exponential backoff saat 429 — harus LULUS
    - **EXPECTED OUTCOME: SEMUA LULUS** (konfirmasi bug 1.14–1.23 teratasi)
    - _Requirements: 2.14, 2.15, 2.16, 2.17, 2.18, 2.19, 2.20, 2.21, 2.22, 2.23_

  - [x] 12.5 Verifikasi fix keamanan & performa (Bug 1.24–1.28)
    - **Property 1: Expected Behavior** — Keamanan Multi-Tenant dan Performa
    - Jalankan ulang test keamanan dan performa dari task 1 pada kode yang sudah diperbaiki
    - Assert: TenantScope otomatis menambahkan filter tenant di semua query — harus LULUS
    - Assert: pola prompt injection dihapus dari input AI — harus LULUS
    - Assert: download export milik tenant lain mengembalikan 404 — harus LULUS
    - Assert: query count dashboard ≤ threshold, cache hit setelah load pertama — harus LULUS
    - Assert: request AI ke-61 dalam 1 menit mendapat HTTP 429 — harus LULUS
    - **EXPECTED OUTCOME: SEMUA LULUS** (konfirmasi bug 1.24–1.28 teratasi)
    - _Requirements: 2.24, 2.25, 2.26, 2.27, 2.28_


- [x] 13. Verifikasi preservation checking — pastikan tidak ada regresi

  - [x] 13.1 Verifikasi preservation sidebar & tema
    - **Property 2: Preservation** — Behavior Sidebar dan Tema yang Sudah Benar
    - **PENTING**: Jalankan ulang test YANG SAMA dari task 2 — JANGAN tulis test baru
    - Assert: SuperAdmin hanya melihat menu Dashboard dan Admin — harus LULUS
    - Assert: Kasir tidak melihat menu Keuangan, Operasional, Pengaturan — harus LULUS
    - Assert: klik logo sidebar mengarahkan ke dashboard — harus LULUS
    - Assert: preferensi tema tersimpan di localStorage dan bertahan setelah refresh — harus LULUS
    - Assert: mode `system` mendeteksi `prefers-color-scheme` OS dengan benar — harus LULUS
    - Assert: event `theme-changed` tetap dikirim ke semua listener — harus LULUS
    - **EXPECTED OUTCOME: SEMUA LULUS** (tidak ada regresi pada sidebar dan tema)
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7_

  - [x] 13.2 Verifikasi preservation fungsionalitas inti
    - **Property 2: Preservation** — Fungsionalitas Inti ERP
    - Jalankan ulang test preservation dari task 2 pada kode yang sudah diperbaiki
    - Assert: alur sales order → invoice → payment → jurnal otomatis tetap berfungsi — harus LULUS
    - Assert: isolasi data multi-tenant tetap terjaga (tenant A tidak bisa akses data tenant B) — harus LULUS
    - Assert: AI Chat merespons dengan konteks tenant yang benar — harus LULUS
    - Assert: queue job diproses asinkron tanpa memblokir HTTP request — harus LULUS
    - Assert: API token validation dan rate limiting tetap berfungsi — harus LULUS
    - Assert: export Excel/PDF menghasilkan file valid dengan data yang benar — harus LULUS
    - Assert: scheduled commands (cron) berjalan sesuai jadwal — harus LULUS
    - Assert: jurnal berhasil dibuat untuk periode `open` (non-buggy case) — harus LULUS
    - Assert: pengurangan stok single-warehouse tanpa concurrent tetap berfungsi — harus LULUS
    - **EXPECTED OUTCOME: SEMUA LULUS** (tidak ada regresi pada fungsionalitas inti)
    - _Requirements: 3.8, 3.9, 3.10, 3.11, 3.12, 3.13, 3.14, 3.15_


- [x] 14. Integration Testing — Verifikasi end-to-end

  - [x] 14.1 Integration test navigasi dan tema
    - Full Navigation Flow: navigasi ke setiap route dalam sistem — verifikasi tepat satu rail button aktif dan submenu yang benar ter-highlight
    - Theme Persistence Flow: toggle tema → refresh → verifikasi tema sama, semua komponen (termasuk chart) menggunakan warna yang benar
    - Mobile Navigation Flow: buka sidebar di mobile → verifikasi hanya satu layer terlihat, transisi smooth
    - Pastikan semua test lulus sebelum melanjutkan

  - [x] 14.2 Integration test modul ERP end-to-end
    - Payroll End-to-End: buat payroll dengan komponen null → hitung → verifikasi tidak error, jurnal terbuat ke periode open
    - Manufacturing Work Order: buat WO dengan BOM 3 level → verifikasi semua komponen di-reserve dari inventory
    - Hotel Night Audit: buat reservasi dengan rate valid dan invalid → jalankan night audit → verifikasi error ditampilkan untuk yang invalid
    - Pastikan semua test lulus sebelum melanjutkan

  - [x] 14.3 Integration test keamanan multi-tenant
    - Multi-Tenant Isolation: login sebagai dua tenant berbeda → verifikasi data tidak bocor antar tenant
    - Export Security: buat export sebagai tenant A → coba download sebagai tenant B → verifikasi 404
    - AI Prompt Injection: kirim berbagai pola injection ke AI Chat → verifikasi semua disanitasi
    - Pastikan semua test lulus sebelum melanjutkan

  - [x] 14.4 Integration test performa
    - AI Chat Rate Limit: kirim 61 request AI dalam 1 menit → verifikasi request ke-61 mendapat 429 dengan `retry_after`
    - Dashboard Performance: load dashboard dengan data besar → verifikasi query count ≤ threshold dan response time < 2 detik
    - Marketplace Sync Retry: simulasikan rate limit API marketplace → verifikasi job di-retry dengan exponential backoff
    - Pastikan semua test lulus sebelum melanjutkan

- [x] 15. Checkpoint — Pastikan semua test lulus
  - Jalankan seluruh test suite (unit, property-based, integration)
  - Verifikasi tidak ada test yang gagal
  - Verifikasi tidak ada regresi pada fungsionalitas yang sudah benar
  - Tanyakan kepada pengguna jika ada pertanyaan atau ambiguitas sebelum menutup spec ini
