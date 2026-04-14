# Dokumen Requirements Bugfix — Audit Menyeluruh AI ERP (Qalcuity ERP)

## Pendahuluan

Qalcuity ERP adalah sistem ERP berbasis Laravel 13 + Alpine.js + Tailwind CSS dengan arsitektur multi-tenant, 100+ modul, dan fitur AI generatif (Gemini). Audit menyeluruh ini mencakup seluruh lapisan sistem: sidebar & navigasi, tema dark/light mode, layout, backend, frontend, API, database, keamanan, dan logika bisnis per modul.

Bug yang diidentifikasi mencakup:
- **Active state sidebar** yang tidak konsisten (double active, sinkronisasi rail ↔ panel)
- **Dark/Light mode** yang tidak diterapkan secara konsisten di seluruh komponen
- **Layout & breadcrumb** yang tidak optimal
- **Bug per modul ERP** (logika, kalkulasi, relasi data, edge case)
- **Keamanan** (potensi kebocoran data, query scoping, CSRF, XSS)
- **Performa** (N+1 query, index database, efisiensi proses)

---

## Analisis Bug

### Current Behavior (Defect)

**Sidebar & Navigasi**

1.1 WHEN pengguna mengakses halaman yang route-nya cocok dengan lebih dari satu grup di `$activeGroup` match expression THEN sistem menampilkan dua rail button dalam kondisi `rail-active` secara bersamaan (double active)

1.2 WHEN pengguna mengklik rail button untuk membuka panel submenu THEN sistem tidak selalu menyinkronkan warna aksen (`--group-color`) antara rail button yang aktif dan panel header, sehingga warna indikator glow dot tidak konsisten dengan warna panel

1.3 WHEN pengguna berada di halaman submenu tertentu (misal `invoices.index`) THEN sistem menandai rail button grup sebagai aktif via PHP (`rail-active`) tetapi tidak menandai item submenu yang sesuai di panel sebagai `active` secara otomatis saat panel pertama kali dibuka

1.4 WHEN pengguna mengakses route yang tidak terdaftar di `$activeGroup` match expression (misal route modul baru atau route legacy) THEN sistem mengembalikan `$activeGroup = ''` sehingga tidak ada rail button yang aktif meskipun pengguna sedang berada di halaman tersebut

1.5 WHEN pengguna menggunakan mobile view dan membuka sidebar THEN sistem menampilkan overlay sidebar (`#sidebar-overlay`) dan panel (`#sidebar-panel`) secara bersamaan tanpa menutup salah satunya terlebih dahulu, menyebabkan konflik z-index dan tampilan tumpang tindih

**Dark/Light Mode**

1.6 WHEN pengguna beralih ke light mode THEN beberapa komponen seperti modal Alpine.js, dropdown, card widget dashboard, dan tabel di modul tertentu tetap menggunakan background putih hardcoded (`bg-white`) tanpa class `dark:bg-slate-800` atau equivalent, sehingga tidak berubah saat dark mode aktif

1.7 WHEN pengguna beralih ke dark mode THEN elemen dengan inline style `style="background: #fff"` atau `style="color: #000"` tidak terpengaruh oleh class `dark:` Tailwind karena inline style memiliki specificity lebih tinggi

1.8 WHEN halaman pertama kali dimuat THEN script inisialisasi tema di `<head>` hanya menghapus class `dark` jika `localStorage.getItem('theme') === 'light'`, tetapi tidak menangani kasus `theme === 'system'` dengan benar sebelum ThemeManager diinisialisasi, menyebabkan flash of unstyled content (FOUC) pada beberapa browser

1.9 WHEN pengguna menggunakan komponen pihak ketiga (chart.js, select2, flatpickr, atau library serupa) THEN komponen tersebut tidak mengikuti perubahan tema karena tidak ada listener `theme-changed` event yang diimplementasikan di komponen tersebut

1.10 WHEN pengguna berada di halaman dengan komponen `<select>`, `<input>`, atau `<textarea>` yang menggunakan class Tailwind standar tanpa prefix `dark:` THEN elemen form tersebut tetap berwarna putih di dark mode

**Layout & Breadcrumb**

1.11 WHEN halaman memiliki `$topbarActions` slot yang berisi banyak tombol aksi THEN tombol-tombol tersebut ditampilkan di navbar/topbar yang menyebabkan topbar menjadi penuh dan tidak responsif di layar kecil, padahal tombol aksi lebih tepat berada di dalam konten halaman

1.12 WHEN pengguna mengakses sistem di perangkat mobile (lebar < 640px) THEN breadcrumb di topbar tidak tampil (`hidden sm:block`) sehingga pengguna kehilangan konteks navigasi halaman saat ini

1.13 WHEN teks breadcrumb terlalu panjang THEN teks terpotong (`truncate`) tanpa tooltip atau cara lain untuk melihat teks lengkap

**Bug Per Modul ERP**

1.14 WHEN modul Keuangan memproses jurnal otomatis dari payroll (`seedPayroll`) THEN sistem tidak memvalidasi apakah `accounting_period` dalam status `open` sebelum membuat jurnal entry, sehingga jurnal bisa masuk ke periode yang sudah dikunci (`locked`)

1.15 WHEN modul Inventori melakukan pengurangan stok dari sales order THEN sistem tidak menggunakan database transaction yang konsisten untuk operasi multi-warehouse, berpotensi menyebabkan race condition pada stok negatif saat concurrent requests

1.16 WHEN modul CRM mengkonversi lead menjadi customer (`converted_to_customer_id`) THEN sistem tidak memvalidasi apakah customer dengan data yang sama sudah ada, berpotensi membuat duplikat customer

1.17 WHEN modul Payroll menghitung komponen gaji dengan formula kustom THEN sistem tidak menangani edge case nilai `null` pada field komponen opsional, menyebabkan error kalkulasi

1.18 WHEN modul Proyek menghitung progress berdasarkan volume task (`volume_tracking`) THEN sistem tidak memvalidasi bahwa total volume aktual tidak melebihi volume rencana, sehingga progress bisa melebihi 100%

1.19 WHEN modul Manufacturing membuat Work Order dengan BOM yang memiliki sub-assembly THEN sistem tidak melakukan recursive BOM explosion dengan benar untuk komponen bertingkat lebih dari 2 level

1.20 WHEN modul Hotel melakukan Night Audit THEN sistem tidak memvalidasi bahwa semua reservasi aktif sudah memiliki room rate yang valid sebelum memproses posting, berpotensi menghasilkan posting dengan nilai 0

1.21 WHEN modul Telecom melakukan sinkronisasi data dari MikroTik router THEN sistem tidak menangani timeout koneksi dengan graceful fallback, menyebabkan queue job gagal tanpa retry yang tepat

1.22 WHEN modul Akuntansi membuat laporan neraca (balance sheet) THEN sistem tidak memastikan persamaan akuntansi (Aset = Liabilitas + Ekuitas) seimbang sebelum menampilkan laporan, sehingga laporan bisa menampilkan data yang tidak balance

1.23 WHEN modul E-Commerce melakukan sinkronisasi stok ke marketplace THEN sistem tidak menangani rate limit API marketplace dengan exponential backoff, menyebabkan sync job gagal secara berulang

**Keamanan & Multi-Tenant**

1.24 WHEN query di beberapa controller menggunakan `where('tenant_id', ...)` secara manual tanpa Global Scope THEN ada risiko developer lupa menambahkan filter tenant_id pada query baru, berpotensi kebocoran data antar tenant

1.25 WHEN AI Chat menerima input dari pengguna dan meneruskannya ke Gemini API THEN sistem tidak melakukan sanitasi input secara konsisten untuk mencegah prompt injection yang bisa mengekspos data tenant lain

1.26 WHEN export data (Excel/PDF) diproses melalui queue job THEN sistem tidak memvalidasi bahwa file export hanya bisa diakses oleh tenant yang membuatnya, berpotensi kebocoran data jika URL file dapat ditebak

**Performa**

1.27 WHEN dashboard memuat widget dengan data agregat dari banyak modul THEN sistem melakukan multiple query terpisah tanpa eager loading yang optimal, menyebabkan N+1 query problem pada tenant dengan data besar

1.28 WHEN AI ERP Chat digunakan secara bersamaan oleh banyak pengguna dalam satu tenant THEN sistem tidak membatasi concurrent AI requests per tenant secara efektif, berpotensi menghabiskan quota Gemini API

---

### Expected Behavior (Correct)

**Sidebar & Navigasi**

2.1 WHEN pengguna mengakses halaman manapun THEN sistem SHALL menampilkan tepat satu rail button dalam kondisi `rail-active` sesuai dengan grup route yang aktif, tanpa double active

2.2 WHEN pengguna mengklik rail button THEN sistem SHALL menyinkronkan warna `--group-color` antara rail button, glow dot indicator, panel accent line, dan panel header secara konsisten

2.3 WHEN panel submenu dibuka THEN sistem SHALL secara otomatis menandai item submenu yang sesuai dengan route aktif saat ini dengan class `active` tanpa memerlukan interaksi tambahan dari pengguna

2.4 WHEN pengguna mengakses route apapun yang valid dalam sistem THEN sistem SHALL mendeteksi grup yang sesuai dan menampilkan rail button yang tepat dalam kondisi aktif

2.5 WHEN pengguna menggunakan mobile view dan membuka sidebar THEN sistem SHALL menampilkan hanya satu layer navigasi pada satu waktu dengan transisi yang smooth dan z-index yang benar

**Dark/Light Mode**

2.6 WHEN pengguna beralih antara dark dan light mode THEN sistem SHALL mengubah tampilan semua komponen (modal, dropdown, card, tabel, form, widget) secara konsisten menggunakan class `dark:` Tailwind yang diterapkan di seluruh komponen

2.7 WHEN komponen memiliki kebutuhan styling khusus THEN sistem SHALL menggunakan CSS custom properties atau class Tailwind `dark:` alih-alih inline style hardcoded untuk memastikan tema berfungsi

2.8 WHEN halaman pertama kali dimuat THEN sistem SHALL menerapkan tema yang benar (termasuk mode `system`) sebelum render pertama untuk mencegah FOUC, dengan script inisialisasi yang lengkap di `<head>`

2.9 WHEN tema berubah THEN sistem SHALL mengirimkan event `theme-changed` dan semua komponen pihak ketiga (chart, datepicker, select) SHALL merespons event tersebut untuk menyesuaikan tampilannya

2.10 WHEN pengguna mengisi form di dark mode THEN sistem SHALL menampilkan semua elemen form (`input`, `select`, `textarea`) dengan background dan text color yang sesuai dark mode

**Layout & Breadcrumb**

2.11 WHEN halaman memiliki tombol aksi utama THEN sistem SHALL menempatkan tombol aksi tersebut di dalam area konten halaman (page header section), bukan di topbar/navbar global

2.12 WHEN pengguna mengakses sistem di perangkat mobile THEN sistem SHALL menampilkan breadcrumb atau indikator halaman aktif yang ringkas dan terbaca di semua ukuran layar

2.13 WHEN teks breadcrumb panjang THEN sistem SHALL menampilkan tooltip atau mekanisme lain agar pengguna dapat melihat teks lengkap

**Bug Per Modul ERP**

2.14 WHEN modul Keuangan memproses jurnal otomatis THEN sistem SHALL memvalidasi status `accounting_period` dan menolak pembuatan jurnal jika periode dalam status `locked` atau `closed`

2.15 WHEN modul Inventori melakukan pengurangan stok multi-warehouse THEN sistem SHALL menggunakan database transaction dengan pessimistic locking (`lockForUpdate()`) untuk mencegah race condition

2.16 WHEN modul CRM mengkonversi lead menjadi customer THEN sistem SHALL memeriksa duplikat berdasarkan email/telepon dan menampilkan konfirmasi jika customer serupa sudah ada

2.17 WHEN modul Payroll menghitung komponen gaji THEN sistem SHALL menangani nilai `null` dengan default value yang sesuai dan menampilkan pesan error yang jelas jika formula tidak valid

2.18 WHEN modul Proyek memperbarui progress task THEN sistem SHALL memvalidasi bahwa volume aktual tidak melebihi volume rencana dan membatasi progress maksimum di 100%

2.19 WHEN modul Manufacturing membuat Work Order THEN sistem SHALL melakukan recursive BOM explosion untuk semua level sub-assembly tanpa batasan kedalaman yang tidak perlu

2.20 WHEN modul Hotel menjalankan Night Audit THEN sistem SHALL memvalidasi kelengkapan data semua reservasi aktif sebelum memulai proses posting dan menampilkan daftar item yang perlu diperbaiki

2.21 WHEN modul Telecom gagal terhubung ke router THEN sistem SHALL menangani timeout dengan graceful fallback, mencatat error ke log, dan menjadwalkan retry dengan exponential backoff

2.22 WHEN modul Akuntansi menghasilkan laporan neraca THEN sistem SHALL memvalidasi keseimbangan persamaan akuntansi dan menampilkan peringatan jika laporan tidak balance

2.23 WHEN modul E-Commerce melakukan sinkronisasi ke marketplace THEN sistem SHALL mengimplementasikan rate limiting dengan exponential backoff dan antrian retry yang tepat

**Keamanan & Multi-Tenant**

2.24 WHEN model Eloquent dibuat atau diquery THEN sistem SHALL menggunakan Global Scope `TenantScope` secara konsisten di semua model yang memiliki `tenant_id` untuk memastikan isolasi data otomatis

2.25 WHEN AI Chat menerima input pengguna THEN sistem SHALL melakukan sanitasi dan validasi input sebelum diteruskan ke Gemini API untuk mencegah prompt injection

2.26 WHEN file export selesai dibuat THEN sistem SHALL menyimpan referensi file dengan `tenant_id` dan memvalidasi kepemilikan sebelum mengizinkan download

**Performa**

2.27 WHEN dashboard memuat data THEN sistem SHALL menggunakan eager loading, query caching, dan agregasi yang efisien untuk menghindari N+1 query

2.28 WHEN AI Chat menerima request THEN sistem SHALL membatasi concurrent AI requests per tenant menggunakan rate limiter yang dikonfigurasi di middleware `RateLimitAiRequests`

---

### Unchanged Behavior (Regression Prevention)

**Sidebar & Navigasi**

3.1 WHEN pengguna SuperAdmin mengakses panel admin THEN sistem SHALL CONTINUE TO menampilkan hanya menu Dashboard dan Admin di rail sidebar tanpa menu tenant

3.2 WHEN pengguna dengan role Kasir atau Gudang login THEN sistem SHALL CONTINUE TO menyembunyikan menu Operasional, Keuangan, dan Pengaturan sesuai logika role yang ada

3.3 WHEN pengguna mengklik logo di rail sidebar THEN sistem SHALL CONTINUE TO mengarahkan ke halaman dashboard

3.4 WHEN pengguna menggunakan keyboard shortcut atau quick search THEN sistem SHALL CONTINUE TO berfungsi normal tanpa terpengaruh perubahan sidebar

**Dark/Light Mode**

3.5 WHEN pengguna menyimpan preferensi tema ke localStorage THEN sistem SHALL CONTINUE TO mempertahankan preferensi tersebut setelah refresh halaman

3.6 WHEN sistem mendeteksi preferensi `prefers-color-scheme` dari OS THEN sistem SHALL CONTINUE TO menerapkan tema yang sesuai jika pengguna memilih mode `system`

3.7 WHEN tema berubah THEN sistem SHALL CONTINUE TO mengirimkan event `theme-changed` ke semua listener yang sudah terdaftar

**Fungsionalitas Inti**

3.8 WHEN pengguna melakukan transaksi penjualan (sales order → invoice → payment) THEN sistem SHALL CONTINUE TO memproses alur end-to-end dengan benar termasuk pembuatan jurnal otomatis

3.9 WHEN sistem multi-tenant berjalan THEN sistem SHALL CONTINUE TO mengisolasi data antar tenant sehingga tenant A tidak dapat mengakses data tenant B

3.10 WHEN pengguna menggunakan AI Chat THEN sistem SHALL CONTINUE TO merespons pertanyaan tentang data ERP dengan konteks tenant yang benar

3.11 WHEN queue worker memproses job THEN sistem SHALL CONTINUE TO menjalankan job secara asinkron tanpa memblokir request HTTP utama

3.12 WHEN pengguna mengakses API dengan token THEN sistem SHALL CONTINUE TO memvalidasi token dan menerapkan rate limiting sesuai konfigurasi

3.13 WHEN pengguna melakukan export data THEN sistem SHALL CONTINUE TO menghasilkan file Excel/PDF yang valid dengan data yang benar

3.14 WHEN sistem menjalankan scheduled commands (cron) THEN sistem SHALL CONTINUE TO mengeksekusi semua task terjadwal sesuai jadwal yang dikonfigurasi

3.15 WHEN pengguna menggunakan fitur offline (PWA/Service Worker) THEN sistem SHALL CONTINUE TO menyimpan data lokal dan melakukan sinkronisasi saat koneksi kembali

---

## Derivasi Bug Condition

### Bug Condition Function

```pascal
FUNCTION isBugCondition(X)
  INPUT: X of type SystemInteraction
  OUTPUT: boolean

  RETURN (
    // Sidebar: double active atau tidak ada active
    (X.type = 'navigation' AND countActiveRailButtons(X.route) != 1)
    OR
    // Theme: komponen tidak merespons perubahan tema
    (X.type = 'theme_toggle' AND existsHardcodedStyle(X.component))
    OR
    // Module logic: validasi bisnis tidak dijalankan
    (X.type = 'module_operation' AND NOT validationExecuted(X.operation))
    OR
    // Security: query tanpa tenant scope
    (X.type = 'data_query' AND NOT hasTenantScope(X.query))
    OR
    // Performance: N+1 query pada dashboard
    (X.type = 'dashboard_load' AND queryCount(X.request) > threshold)
  )
END FUNCTION
```

### Property: Fix Checking

```pascal
// Property: Fix Checking — Semua kondisi bug harus diperbaiki
FOR ALL X WHERE isBugCondition(X) DO
  result ← F'(X)
  ASSERT (
    (X.type = 'navigation' IMPLIES countActiveRailButtons(result) = 1)
    AND (X.type = 'theme_toggle' IMPLIES allComponentsRespond(result))
    AND (X.type = 'module_operation' IMPLIES validationExecuted(result))
    AND (X.type = 'data_query' IMPLIES hasTenantScope(result))
    AND (X.type = 'dashboard_load' IMPLIES queryCount(result) <= threshold)
  )
END FOR
```

### Property: Preservation Checking

```pascal
// Property: Preservation — Behavior yang sudah benar tidak boleh berubah
FOR ALL X WHERE NOT isBugCondition(X) DO
  ASSERT F(X) = F'(X)
END FOR
```
