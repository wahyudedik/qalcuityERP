# Dokumen Requirements — Audit & Perbaikan Komprehensif Qalcuity ERP

## Pendahuluan

Dokumen ini mendefinisikan requirements untuk audit menyeluruh dan perbaikan komprehensif aplikasi Qalcuity ERP — platform manajemen bisnis multi-tenant berbasis Laravel 13 + PHP 8.3 yang melayani UKM Indonesia. Cakupan audit meliputi seluruh lapisan aplikasi: database, backend (model, controller, service, route), frontend (view, komponen Blade, Alpine.js), UI/UX (dark mode, light mode, responsivitas), sistem notifikasi, kontrol akses berbasis modul dan paket langganan, serta peluang pengembangan fitur baru.

Tujuan akhir: menjadikan Qalcuity ERP sebagai aplikasi ERP nomor 1 di Indonesia — bebas error, user-friendly, responsif, dan siap dikembangkan lebih lanjut.

---

## Glosarium

- **System**: Aplikasi Qalcuity ERP secara keseluruhan
- **Tenant**: Perusahaan/bisnis yang berlangganan dan menggunakan platform
- **SuperAdmin**: Administrator platform tingkat tertinggi yang mengelola semua tenant
- **Admin**: Administrator tingkat tenant yang mengelola pengguna dan pengaturan dalam satu tenant
- **User**: Pengguna akhir dalam sebuah tenant (kasir, manajer, staf, dll.)
- **Modul**: Unit fungsional ERP (Accounting, Inventory, Sales, HRM, Payroll, POS, dll.)
- **Paket Langganan**: Tier berlangganan (Starter, Professional, Enterprise, dll.) yang menentukan modul yang dapat diakses
- **Permission**: Hak akses granular per modul (view, create, edit, delete)
- **Role**: Peran pengguna (super_admin, admin, manager, kasir, gudang, staff, dll.)
- **ENUM**: Tipe kolom database dengan nilai terbatas yang harus sesuai antara migration dan penggunaan di kode
- **Dark Mode / Light Mode**: Tema tampilan aplikasi yang dapat dipilih pengguna
- **FOUC**: Flash of Unstyled Content — kilatan tampilan sebelum tema diterapkan
- **Sidebar**: Panel navigasi utama aplikasi (rail 56px + panel 240px)
- **Notifikasi ERP**: Notifikasi in-app, email, dan push browser untuk event bisnis
- **Tenant Isolation**: Pemisahan data antar tenant menggunakan `tenant_id`
- **PlanModuleMap**: Peta konfigurasi modul yang tersedia per paket langganan
- **RBAC**: Role-Based Access Control — sistem kontrol akses berbasis peran
- **Migration**: File PHP yang mendefinisikan skema database
- **Seeder**: File PHP yang mengisi data awal database
- **Blade**: Template engine Laravel untuk view
- **Alpine.js**: Library JavaScript ringan untuk reaktivitas UI
- **Tailwind CSS**: Framework CSS utility-first

---

## Requirements

### Requirement 1: Perbaikan Error Database — Truncation & ENUM Mismatch

**User Story:** Sebagai developer, saya ingin semua kolom database memiliki tipe dan panjang yang sesuai dengan data yang disimpan, sehingga tidak ada error SQLSTATE data truncation atau ENUM mismatch saat operasi CRUD.

#### Acceptance Criteria

1. THE System SHALL mengaudit semua kolom ENUM di seluruh migration dan memastikan nilai yang digunakan di controller, model, dan seeder sesuai persis dengan definisi ENUM di migration.
2. WHEN sebuah nilai status dikirim ke database, THE System SHALL memvalidasi nilai tersebut terhadap daftar ENUM yang valid sebelum eksekusi query.
3. IF nilai yang dikirim tidak ada dalam daftar ENUM yang valid, THEN THE System SHALL mengembalikan pesan error yang deskriptif dan mencatat kejadian tersebut ke error log.
4. THE System SHALL mengaudit semua kolom `VARCHAR` dan `TEXT` untuk memastikan panjang kolom cukup menampung data yang mungkin dimasukkan (contoh: kolom `status` pada tabel `invoices` harus mendukung nilai `voided`).
5. THE System SHALL memperbaiki semua migration yang memiliki definisi ENUM tidak lengkap, termasuk namun tidak terbatas pada: `invoices.status`, `sales_orders.status`, `purchase_orders.status`, `rooms.status`, `guests.vip_level`, `housekeeping_tasks.deep_cleaning`, dan kolom status lainnya.
6. WHEN migration dijalankan ulang atau fresh, THE System SHALL berhasil tanpa error constraint atau truncation pada semua seeder demo data.

### Requirement 2: Perbaikan Error Route & Controller

**User Story:** Sebagai pengguna, saya ingin semua link navigasi dan tombol aksi di aplikasi berfungsi tanpa error 404, 500, atau route not found, sehingga saya dapat menggunakan semua fitur tanpa hambatan.

#### Acceptance Criteria

1. THE System SHALL mengaudit semua route yang terdaftar di `routes/web.php`, `routes/api.php`, `routes/auth.php`, dan `routes/healthcare.php` untuk memastikan setiap route memiliki controller method yang sesuai dan tidak menghasilkan error.
2. WHEN pengguna mengakses URL yang valid, THE System SHALL mengembalikan response HTTP 200 atau redirect yang sesuai, bukan error 404 atau 500.
3. THE System SHALL memastikan semua controller yang direferensikan di route file benar-benar ada di direktori `app/Http/Controllers/` beserta subdirektorinya.
4. THE System SHALL memastikan semua method controller yang dipanggil dari route benar-benar ada dan tidak menghasilkan `MethodNotAllowedException` atau `BadMethodCallException`.
5. IF sebuah route memerlukan middleware tertentu (seperti `auth`, `role`, `permission`, `tenant.isolation`), THEN THE System SHALL memastikan middleware tersebut terdaftar di `bootstrap/app.php` atau `Kernel.php` dan berfungsi dengan benar.
6. THE System SHALL mengaudit semua named route yang digunakan di view Blade (`route('nama.route')`) dan memastikan tidak ada `RouteNotFoundException` saat view dirender.
7. WHEN pengguna dengan role tertentu mengakses route yang dibatasi role lain, THE System SHALL mengembalikan halaman 403 yang informatif, bukan error 500.

### Requirement 3: Perbaikan Kualitas Kode — Model, Controller, Service

**User Story:** Sebagai developer, saya ingin semua kode PHP mengikuti konvensi Laravel yang benar dan bebas dari kesalahan penulisan, sehingga aplikasi berjalan stabil dan mudah dipelihara.

#### Acceptance Criteria

1. THE System SHALL mengaudit semua Eloquent Model untuk memastikan: (a) setiap model tenant-scoped menggunakan trait `BelongsToTenant`, (b) relasi `hasMany`, `belongsTo`, `hasOne`, `belongsToMany` mereferensikan model dan foreign key yang benar, (c) `$fillable` atau `$guarded` didefinisikan dengan benar.
2. THE System SHALL mengaudit semua Controller untuk memastikan: (a) tidak ada `undefined variable` yang dikirim ke view, (b) semua `compact()` hanya menyertakan variabel yang benar-benar ada, (c) semua query menggunakan tenant scope yang benar.
3. THE System SHALL mengaudit semua Service class untuk memastikan tidak ada method yang memanggil model atau relasi yang tidak ada.
4. WHEN sebuah model menggunakan `$casts`, THE System SHALL memastikan tipe cast sesuai dengan tipe kolom di database (contoh: kolom JSON di-cast sebagai `array`, kolom decimal di-cast sebagai `float` atau `decimal`).
5. THE System SHALL memastikan semua `use` statement di setiap file PHP mereferensikan class yang benar-benar ada dan tidak ada `Class not found` error.
6. THE System SHALL mengaudit semua Form Request (`app/Http/Requests/`) untuk memastikan aturan validasi sesuai dengan kolom database dan tidak ada aturan yang mereferensikan kolom yang tidak ada.
7. THE System SHALL memastikan semua Observer (`app/Observers/`) terdaftar dengan benar di Service Provider dan tidak menghasilkan error saat event model dipicu.

### Requirement 4: Perbaikan View & Komponen Blade

**User Story:** Sebagai pengguna, saya ingin semua halaman tampil dengan benar tanpa error Blade, variabel undefined, atau komponen yang hilang, sehingga pengalaman penggunaan aplikasi menjadi lancar.

#### Acceptance Criteria

1. THE System SHALL mengaudit semua file Blade view untuk memastikan tidak ada `Undefined variable`, `Trying to get property of non-object`, atau `Call to a member function on null` error.
2. THE System SHALL memastikan semua komponen Blade (`<x-component-name>`) yang digunakan di view benar-benar ada di direktori `resources/views/components/`.
3. THE System SHALL memastikan semua `@include`, `@extends`, `@component`, dan `@livewire` di view mereferensikan file yang benar-benar ada.
4. WHEN sebuah view menampilkan data dari database, THE System SHALL menggunakan null-safe operator (`?->`) atau pengecekan `isset()` / `optional()` untuk mencegah error pada data yang mungkin null.
5. THE System SHALL mengaudit semua view yang menggunakan `route()` helper untuk memastikan named route yang direferensikan benar-benar terdaftar.
6. THE System SHALL memastikan semua form di view memiliki `@csrf` token dan method yang sesuai (`@method('PUT')`, `@method('DELETE')`) untuk operasi non-GET.
7. THE System SHALL mengaudit semua view pagination untuk memastikan `->links()` atau `->appends()` digunakan dengan benar dan tidak menghasilkan error.

### Requirement 5: Audit & Perbaikan Dark Mode dan Light Mode

**User Story:** Sebagai pengguna, saya ingin tampilan dark mode dan light mode berfungsi secara konsisten di semua halaman dan komponen, sehingga pengalaman visual saya nyaman di semua kondisi pencahayaan.

#### Acceptance Criteria

1. THE System SHALL memastikan semua view Blade menggunakan class Tailwind CSS yang mendukung dark mode (`dark:bg-*`, `dark:text-*`, `dark:border-*`) secara konsisten di seluruh halaman.
2. WHEN pengguna beralih antara dark mode dan light mode, THE System SHALL menerapkan perubahan tema secara instan tanpa reload halaman menggunakan Alpine.js dan `localStorage`.
3. THE System SHALL memastikan tidak ada FOUC (Flash of Unstyled Content) saat halaman pertama kali dimuat dengan menjalankan script deteksi tema sebelum render konten.
4. THE System SHALL mengaudit semua komponen: tabel, form, card, modal, dropdown, alert, badge, button, sidebar, navbar, dan pagination untuk memastikan semua memiliki class dark mode yang lengkap dan benar.
5. THE System SHALL memastikan warna teks di dark mode memiliki kontras yang cukup (minimal 4.5:1 untuk teks normal) agar mudah dibaca.
6. THE System SHALL memastikan semua ikon SVG, ilustrasi, dan gambar memiliki tampilan yang sesuai di kedua mode (menggunakan `dark:invert` atau variasi warna yang tepat).
7. WHILE pengguna menggunakan mode `system`, THE System SHALL secara otomatis mengikuti preferensi sistem operasi pengguna dan memperbarui tema jika preferensi sistem berubah.
8. THE System SHALL memastikan semua halaman industri-spesifik (Healthcare, Hotel, F&B, Telecom, dll.) juga mendukung dark mode dan light mode dengan konsisten.

### Requirement 6: Audit UI/UX — Responsivitas, Sidebar, Form, Tabel, Button, Alert

**User Story:** Sebagai pengguna, saya ingin antarmuka aplikasi responsif, user-friendly, dan konsisten di semua perangkat (desktop, tablet, mobile), sehingga saya dapat bekerja dengan nyaman dari perangkat apapun.

#### Acceptance Criteria

1. THE System SHALL memastikan semua halaman responsif dan dapat digunakan dengan baik pada lebar layar 320px (mobile), 768px (tablet), dan 1280px+ (desktop) menggunakan breakpoint Tailwind CSS.
2. THE System SHALL memastikan sidebar navigasi berfungsi dengan benar: (a) rail 56px selalu terlihat di desktop, (b) panel 240px muncul saat hover/klik, (c) di mobile sidebar collapse menjadi bottom navigation atau hamburger menu.
3. THE System SHALL memastikan semua tombol (button) memiliki: (a) state hover yang jelas, (b) state disabled yang terlihat dan tidak dapat diklik, (c) state loading saat proses sedang berjalan, (d) ukuran touch target minimal 44x44px di mobile.
4. THE System SHALL memastikan semua form memiliki: (a) label yang jelas untuk setiap input, (b) pesan error validasi yang spesifik dan muncul di bawah field yang salah, (c) placeholder yang informatif, (d) urutan tab yang logis.
5. THE System SHALL memastikan semua tabel memiliki: (a) header yang jelas, (b) baris alternating color untuk keterbacaan, (c) kolom aksi yang konsisten (edit, hapus, lihat), (d) pagination yang berfungsi, (e) fitur sort dan filter yang bekerja dengan benar.
6. THE System SHALL memastikan semua alert dan notifikasi toast: (a) muncul di posisi yang konsisten (pojok kanan atas), (b) memiliki warna yang sesuai (hijau=sukses, merah=error, kuning=warning, biru=info), (c) dapat ditutup oleh pengguna, (d) hilang otomatis setelah 5 detik.
7. THE System SHALL memastikan semua modal dialog: (a) dapat ditutup dengan tombol X, klik backdrop, atau tombol Escape, (b) tidak overflow di layar kecil, (c) memiliki z-index yang benar sehingga tidak tertutup elemen lain.
8. THE System SHALL memastikan semua dropdown menu: (a) muncul di posisi yang benar, (b) tidak terpotong di tepi layar, (c) dapat ditutup dengan klik di luar area dropdown.
9. THE System SHALL memastikan semua card dan panel statistik di dashboard menampilkan data yang benar dengan format angka Indonesia (titik sebagai pemisah ribuan, koma sebagai desimal).

### Requirement 7: Audit Sistem Notifikasi — Cakupan Semua Modul

**User Story:** Sebagai pengguna, saya ingin menerima notifikasi yang relevan dari semua modul ERP yang saya gunakan, sehingga saya tidak melewatkan event bisnis penting.

#### Acceptance Criteria

1. THE System SHALL memastikan notifikasi tersedia untuk semua event kritis di setiap modul aktif, mencakup minimal: (a) Accounting: jurnal disetujui, periode ditutup, anggaran terlampaui, (b) Inventory: stok menipis, stok habis, transfer gudang selesai, (c) Sales: invoice jatuh tempo, invoice dibayar, pesanan baru, (d) Purchasing: PO disetujui, barang diterima, (e) HRM: pengajuan cuti disetujui/ditolak, kontrak karyawan hampir berakhir, (f) Payroll: payroll diproses, slip gaji tersedia, (g) POS: sesi kasir dibuka/ditutup, (h) Project: tugas ditugaskan, deadline mendekat, (i) Asset: jadwal pemeliharaan mendekat, (j) Healthcare: jadwal appointment, hasil lab tersedia, (k) Hotel: check-in/check-out, reservasi baru, (l) Telecom: paket hampir habis, tagihan jatuh tempo.
2. THE System SHALL memastikan notifikasi dapat dikirim melalui minimal tiga channel: in-app (bell icon), email, dan push notification browser.
3. WHEN pengguna mengatur preferensi notifikasi, THE System SHALL menyimpan preferensi per channel per tipe notifikasi dan menghormati preferensi tersebut saat mengirim notifikasi.
4. THE System SHALL memastikan bell notifikasi di navbar menampilkan jumlah notifikasi yang belum dibaca secara real-time (polling atau push).
5. THE System SHALL memastikan halaman daftar notifikasi (`/notifications`) menampilkan semua notifikasi dengan filter berdasarkan modul, status baca, dan tanggal.
6. IF sebuah modul dinonaktifkan untuk tenant tertentu, THEN THE System SHALL tidak mengirim notifikasi dari modul tersebut kepada pengguna tenant tersebut.
7. THE System SHALL mengaudit semua class Notification di `app/Notifications/` untuk memastikan tidak ada modul yang belum memiliki notifikasi: Inventory (LowStock sudah ada, perlu tambahan), Sales, Purchasing, HRM, Payroll, POS, Project, Asset, CRM, Manufacturing, Construction, Agriculture, Livestock, Fisheries, Telecom, Hotel, F&B, Healthcare.
8. THE System SHALL memastikan notifikasi escalation berfungsi: jika notifikasi tidak dibaca dalam waktu tertentu, notifikasi dikirim ke level manajemen yang lebih tinggi.

### Requirement 8: Audit Kontrol Akses — Modul, Paket Langganan, Role & Permission

**User Story:** Sebagai admin tenant, saya ingin menu, fitur, dan modul yang tampil di aplikasi sesuai dengan paket langganan yang aktif dan role pengguna, sehingga pengguna hanya melihat dan mengakses fitur yang relevan dan diizinkan.

#### Acceptance Criteria

1. THE System SHALL memastikan semua route yang memerlukan akses modul tertentu dilindungi oleh middleware `CheckModulePlanAccess` dengan parameter modul yang benar.
2. WHEN pengguna mengakses halaman modul yang tidak termasuk dalam paket langganan tenant, THE System SHALL menampilkan halaman upgrade yang informatif, bukan error 403 atau 500.
3. THE System SHALL memastikan sidebar navigasi hanya menampilkan menu modul yang: (a) aktif dalam paket langganan tenant, (b) diizinkan oleh role pengguna, (c) diaktifkan dalam pengaturan modul tenant.
4. THE System SHALL memastikan semua tombol aksi (tambah, edit, hapus, approve, export) hanya tampil jika pengguna memiliki permission yang sesuai (`view`, `create`, `edit`, `delete` per modul).
5. THE System SHALL mengaudit `PlanModuleMap` untuk memastikan semua modul yang ada di aplikasi terdaftar dengan benar di peta modul per paket langganan.
6. WHEN admin mengubah pengaturan modul aktif untuk tenant, THE System SHALL segera memperbarui akses tanpa perlu logout/login ulang.
7. THE System SHALL memastikan middleware `EnforceTenantIsolation` berfungsi di semua route yang mengakses data tenant, sehingga tidak ada kebocoran data antar tenant.
8. THE System SHALL memastikan role `kasir` hanya dapat mengakses modul POS dan fitur yang relevan, role `gudang` hanya dapat mengakses modul Inventory, dan role lainnya sesuai dengan definisi RBAC yang telah ditetapkan.
9. THE System SHALL memastikan SuperAdmin dapat mengakses semua tenant dan semua fitur tanpa batasan modul atau paket langganan.
10. WHEN masa trial tenant berakhir, THE System SHALL membatasi akses ke fitur premium dan menampilkan notifikasi upgrade yang jelas.

### Requirement 9: Audit Alur Bisnis (Business Flow) Per Modul

**User Story:** Sebagai pengguna bisnis, saya ingin semua alur transaksi bisnis berjalan dengan benar dari awal hingga akhir, sehingga data keuangan dan operasional saya akurat dan konsisten.

#### Acceptance Criteria

1. THE System SHALL memastikan alur Sales berjalan lengkap dan benar: Quotation → Sales Order → Delivery Order → Invoice → Payment → Journal Entry, dengan setiap transisi status yang valid dan jurnal akuntansi yang benar.
2. THE System SHALL memastikan alur Purchasing berjalan lengkap dan benar: Purchase Request → Purchase Order → Goods Receipt → Supplier Invoice → Payment → Journal Entry.
3. THE System SHALL memastikan alur Inventory berjalan benar: penerimaan barang menambah stok, pengeluaran barang mengurangi stok, transfer gudang memindahkan stok, dan semua pergerakan tercatat di stock movement log.
4. THE System SHALL memastikan alur Payroll berjalan benar: input kehadiran → kalkulasi gaji → approval → generate slip gaji → posting jurnal akuntansi.
5. THE System SHALL memastikan alur POS berjalan benar: buka sesi → transaksi penjualan → pembayaran (tunai/kartu/QRIS) → tutup sesi → rekonsiliasi kas → posting jurnal.
6. THE System SHALL memastikan alur Accounting berjalan benar: input jurnal → approval → posting → laporan keuangan (Neraca, Laba Rugi, Arus Kas) yang akurat.
7. THE System SHALL memastikan alur Approval Workflow berfungsi: pengajuan → notifikasi approver → approve/reject → notifikasi pemohon → eksekusi atau pembatalan.
8. WHEN sebuah transaksi dibatalkan atau di-void, THE System SHALL membuat jurnal pembalik yang benar dan memperbarui semua data terkait (stok, piutang, hutang, dll.).
9. THE System SHALL memastikan alur Down Payment berjalan benar: pembayaran uang muka → pengurangan saldo invoice → pelunasan sisa.
10. THE System SHALL memastikan alur Return (Sales Return dan Purchase Return) berjalan benar dengan pembaruan stok dan jurnal yang tepat.

### Requirement 10: Audit Modul Akuntansi & Keuangan

**User Story:** Sebagai akuntan, saya ingin semua fitur akuntansi berfungsi dengan benar dan menghasilkan laporan keuangan yang akurat sesuai standar akuntansi Indonesia, sehingga saya dapat membuat keputusan keuangan yang tepat.

#### Acceptance Criteria

1. THE System SHALL memastikan Chart of Accounts (CoA) dapat dibuat, diedit, dan dihapus dengan benar, dan setiap akun memiliki tipe yang valid (Aset, Liabilitas, Ekuitas, Pendapatan, Beban).
2. THE System SHALL memastikan jurnal umum dapat dibuat dengan debit = kredit, dan sistem menolak jurnal yang tidak balance.
3. THE System SHALL memastikan laporan Neraca (Balance Sheet), Laba Rugi (Income Statement), dan Arus Kas (Cash Flow) menghasilkan angka yang akurat dan konsisten satu sama lain.
4. THE System SHALL memastikan rekonsiliasi bank berfungsi: import mutasi bank → matching dengan transaksi → identifikasi selisih.
5. THE System SHALL memastikan fitur multi-currency berfungsi: konversi kurs, revaluasi, dan laporan dalam mata uang dasar.
6. THE System SHALL memastikan perhitungan PPN (11%) dan PPh (berbagai tarif) berfungsi dengan benar dan menghasilkan laporan pajak yang akurat.
7. THE System SHALL memastikan fitur period lock berfungsi: setelah periode dikunci, tidak ada jurnal baru yang dapat diposting ke periode tersebut.
8. THE System SHALL memastikan fitur recurring journal berfungsi: jurnal berulang dibuat otomatis sesuai jadwal yang dikonfigurasi.

### Requirement 11: Audit Modul Inventory & Gudang

**User Story:** Sebagai manajer gudang, saya ingin semua fitur inventory berfungsi dengan benar termasuk multi-gudang, batch tracking, dan perhitungan harga pokok, sehingga data stok saya selalu akurat.

#### Acceptance Criteria

1. THE System SHALL memastikan stok produk diperbarui secara real-time saat terjadi penerimaan, pengeluaran, transfer, atau penyesuaian stok.
2. THE System SHALL memastikan metode costing FIFO dan Average Cost menghasilkan nilai HPP yang benar dan konsisten.
3. THE System SHALL memastikan fitur multi-gudang berfungsi: stok dapat dilihat per gudang, transfer antar gudang menghasilkan stock movement yang benar.
4. THE System SHALL memastikan fitur batch/lot tracking berfungsi: setiap batch memiliki nomor unik, tanggal kadaluarsa, dan dapat dilacak dari penerimaan hingga pengeluaran.
5. THE System SHALL memastikan fitur barcode dan QR code berfungsi: generate, print, dan scan barcode untuk identifikasi produk.
6. THE System SHALL memastikan fitur landed cost berfungsi: biaya pengiriman dan bea cukai dialokasikan ke produk yang diterima.
7. THE System SHALL memastikan alert stok minimum berfungsi: notifikasi dikirim saat stok produk mencapai atau di bawah minimum stok yang dikonfigurasi.
8. THE System SHALL memastikan fitur WMS (Warehouse Management System) berfungsi: lokasi rak, bin, dan zona gudang dapat dikonfigurasi dan digunakan untuk picking/putaway.

### Requirement 12: Audit Modul HRM & Payroll

**User Story:** Sebagai manajer HR, saya ingin semua fitur HRM dan Payroll berfungsi dengan benar termasuk absensi, penggajian, dan laporan HR, sehingga pengelolaan SDM perusahaan berjalan efisien.

#### Acceptance Criteria

1. THE System SHALL memastikan fitur absensi berfungsi: input manual, integrasi fingerprint, dan perhitungan keterlambatan/lembur yang akurat.
2. THE System SHALL memastikan kalkulasi payroll menghasilkan gaji bersih yang benar berdasarkan komponen gaji, potongan, tunjangan, BPJS, dan PPh 21.
3. THE System SHALL memastikan slip gaji dapat digenerate dalam format PDF dan dikirim ke email karyawan.
4. THE System SHALL memastikan fitur Employee Self Service (ESS) berfungsi: karyawan dapat mengajukan cuti, lembur, reimbursement, dan melihat slip gaji sendiri.
5. THE System SHALL memastikan fitur rekrutmen berfungsi: posting lowongan, penerimaan lamaran, proses seleksi, dan onboarding karyawan baru.
6. THE System SHALL memastikan fitur manajemen shift berfungsi: pembuatan jadwal shift, penugasan karyawan, dan laporan kehadiran per shift.
7. THE System SHALL memastikan fitur pelatihan berfungsi: pendaftaran pelatihan, tracking kehadiran, dan sertifikat pelatihan.
8. THE System SHALL memastikan jurnal akuntansi payroll diposting dengan benar ke CoA yang sesuai saat payroll diproses.

### Requirement 13: Audit Modul POS (Point of Sale)

**User Story:** Sebagai kasir, saya ingin aplikasi POS berjalan cepat dan stabil tanpa error, sehingga proses transaksi di kasir tidak terganggu dan pelanggan tidak menunggu lama.

#### Acceptance Criteria

1. THE System SHALL memastikan sesi kasir dapat dibuka dan ditutup dengan benar, dengan rekap transaksi yang akurat.
2. THE System SHALL memastikan pencarian produk di POS berfungsi cepat (hasil muncul dalam 500ms) menggunakan barcode scan atau pencarian teks.
3. THE System SHALL memastikan semua metode pembayaran berfungsi: tunai (dengan kembalian otomatis), kartu debit/kredit, QRIS, dan split payment.
4. THE System SHALL memastikan integrasi payment gateway (Midtrans, Xendit, Duitku) berfungsi dan menangani callback pembayaran dengan benar.
5. THE System SHALL memastikan struk/receipt dapat dicetak ke printer thermal ESC/POS dan dikirim via email atau WhatsApp.
6. THE System SHALL memastikan mode offline POS berfungsi: transaksi dapat dilakukan saat koneksi internet terputus dan disinkronkan saat koneksi kembali.
7. THE System SHALL memastikan fitur loyalty point di POS berfungsi: poin diberikan saat transaksi dan dapat ditukarkan sebagai diskon.
8. WHEN sesi kasir ditutup, THE System SHALL secara otomatis memposting jurnal penjualan dan kas ke modul Accounting.

### Requirement 14: Audit Modul Industri Spesifik

**User Story:** Sebagai pengguna modul industri spesifik (Healthcare, Hotel, F&B, Telecom, Manufacturing, Construction, Agriculture, dll.), saya ingin semua fitur modul industri saya berfungsi dengan benar dan terintegrasi dengan modul inti ERP.

#### Acceptance Criteria

1. THE System SHALL memastikan modul Healthcare berfungsi: pendaftaran pasien, rekam medis elektronik (EMR), rawat inap, rawat jalan, IGD, farmasi, laboratorium, radiologi, dan penagihan medis.
2. THE System SHALL memastikan modul Hotel berfungsi: reservasi kamar, check-in/check-out, housekeeping, front office, revenue management, dan night audit.
3. THE System SHALL memastikan modul F&B berfungsi: manajemen menu, kitchen order ticket (KOT), reservasi meja, manajemen bahan baku, dan laporan penjualan F&B.
4. THE System SHALL memastikan modul Telecom/ISP berfungsi: manajemen pelanggan, paket internet, hotspot, tracking penggunaan, dan penagihan otomatis.
5. THE System SHALL memastikan modul Manufacturing berfungsi: Bill of Materials (BOM), Work Order, MRP, kapasitas produksi, dan perhitungan biaya produksi.
6. THE System SHALL memastikan modul Construction berfungsi: RAB (Rencana Anggaran Biaya), progress proyek, laporan harian lapangan, dan manajemen subkontraktor.
7. THE System SHALL memastikan modul Agriculture berfungsi: manajemen lahan, siklus tanam, log panen, dan laporan pertanian.
8. THE System SHALL memastikan semua modul industri spesifik terintegrasi dengan modul Accounting untuk posting jurnal otomatis dari setiap transaksi.
9. THE System SHALL memastikan semua modul industri spesifik menggunakan layout yang konsisten dengan modul inti dan mendukung dark mode/light mode.

### Requirement 15: Audit Performa & Keamanan

**User Story:** Sebagai admin sistem, saya ingin aplikasi berjalan dengan performa yang baik dan aman dari ancaman keamanan, sehingga data bisnis tenant terlindungi dan pengguna mendapatkan pengalaman yang responsif.

#### Acceptance Criteria

1. THE System SHALL memastikan semua query database yang sering dijalankan menggunakan index yang tepat untuk menghindari full table scan pada tabel besar.
2. THE System SHALL memastikan semua halaman dashboard dan laporan menggunakan cache yang tepat untuk mengurangi beban database, dengan cache invalidation yang benar saat data berubah.
3. THE System SHALL memastikan tidak ada N+1 query problem di controller dan view dengan menggunakan eager loading (`with()`) yang tepat.
4. THE System SHALL memastikan semua input pengguna divalidasi dan di-sanitasi sebelum diproses untuk mencegah SQL injection, XSS, dan CSRF.
5. THE System SHALL memastikan semua file upload divalidasi tipe dan ukurannya, dan disimpan di lokasi yang aman (bukan di direktori publik langsung).
6. THE System SHALL memastikan fitur 2FA (Two-Factor Authentication) berfungsi dengan benar menggunakan Google Authenticator.
7. THE System SHALL memastikan rate limiting berfungsi di semua endpoint API dan AI untuk mencegah abuse.
8. THE System SHALL memastikan semua komunikasi menggunakan HTTPS dan header keamanan yang tepat (CSP, HSTS, X-Frame-Options, dll.) diterapkan oleh middleware `AddSecurityHeaders`.
9. THE System SHALL memastikan audit trail mencatat semua perubahan data sensitif (keuangan, data karyawan, pengaturan sistem) dengan informasi user, timestamp, dan nilai sebelum/sesudah perubahan.
10. THE System SHALL memastikan fitur account lockout berfungsi: akun dikunci setelah sejumlah percobaan login yang gagal.

### Requirement 16: Audit Integrasi Eksternal

**User Story:** Sebagai pengguna, saya ingin semua integrasi dengan layanan eksternal (marketplace, payment gateway, shipping, messaging) berfungsi dengan benar dan menangani error dengan baik, sehingga operasional bisnis saya tidak terganggu.

#### Acceptance Criteria

1. THE System SHALL memastikan integrasi marketplace (Shopee, Tokopedia, Lazada) berfungsi: sinkronisasi produk, stok, harga, dan pesanan berjalan dengan benar.
2. THE System SHALL memastikan integrasi payment gateway (Midtrans, Xendit, Duitku) berfungsi: pembayaran diproses, callback diterima, dan status transaksi diperbarui dengan benar.
3. THE System SHALL memastikan integrasi shipping (RajaOngkir, JNE, J&T) berfungsi: kalkulasi ongkir, pembuatan resi, dan tracking pengiriman berjalan dengan benar.
4. THE System SHALL memastikan integrasi messaging (WhatsApp, Telegram) berfungsi: notifikasi dan pesan dikirim dengan benar ke nomor/channel yang dikonfigurasi.
5. THE System SHALL memastikan integrasi akuntansi (Jurnal.id, Accurate Online) berfungsi: sinkronisasi data jurnal dan laporan keuangan berjalan dengan benar.
6. IF sebuah layanan eksternal tidak tersedia atau mengembalikan error, THEN THE System SHALL mencatat error ke log, menampilkan pesan yang informatif kepada pengguna, dan tidak mengakibatkan crash aplikasi.
7. THE System SHALL memastikan webhook dari layanan eksternal diverifikasi signature-nya sebelum diproses untuk mencegah request palsu.
8. THE System SHALL memastikan retry mechanism berfungsi untuk webhook dan job yang gagal, dengan exponential backoff yang tepat.

### Requirement 17: Audit AI Assistant & Fitur AI

**User Story:** Sebagai pengguna, saya ingin AI Assistant berfungsi dengan benar dan memberikan rekomendasi yang relevan berdasarkan data bisnis saya, sehingga saya dapat membuat keputusan yang lebih baik.

#### Acceptance Criteria

1. THE System SHALL memastikan AI Chat berfungsi: pesan dikirim, diproses oleh Gemini API, dan respons ditampilkan dengan benar termasuk format Markdown.
2. THE System SHALL memastikan AI Agent dapat mengeksekusi operasi ERP (baca data, buat transaksi, generate laporan) dengan konfirmasi pengguna sebelum operasi write.
3. THE System SHALL memastikan quota AI per tenant berfungsi: penggunaan dicatat, dan akses dibatasi saat quota habis dengan pesan yang informatif.
4. THE System SHALL memastikan AI Memory berfungsi: konteks percakapan sebelumnya digunakan untuk memberikan respons yang lebih relevan.
5. THE System SHALL memastikan Proactive Insights berfungsi: AI secara otomatis menganalisis data bisnis dan memberikan rekomendasi yang relevan.
6. THE System SHALL memastikan rate limiting AI berfungsi: request berlebihan dari satu pengguna dibatasi untuk mencegah abuse dan menjaga ketersediaan layanan.
7. THE System SHALL memastikan semua AI controller (AccountingAiController, SalesAiController, HrmAiController, InventoryAiController, dll.) berfungsi dengan benar dan mengembalikan data yang relevan.
8. IF Gemini API tidak tersedia, THEN THE System SHALL menampilkan pesan error yang informatif dan mencatat kejadian tersebut, tanpa mengakibatkan crash halaman.

### Requirement 18: Identifikasi & Pengembangan Fitur yang Kurang

**User Story:** Sebagai product owner, saya ingin mengetahui fitur-fitur yang masih kurang atau belum optimal di Qalcuity ERP, sehingga dapat diprioritaskan untuk pengembangan agar menjadi ERP terbaik di Indonesia.

#### Acceptance Criteria

1. THE System SHALL mengidentifikasi dan mendokumentasikan semua fitur yang ada di modul tetapi belum memiliki view yang lengkap (hanya controller tanpa view, atau view kosong/placeholder).
2. THE System SHALL mengidentifikasi modul yang belum memiliki fitur export (Excel/PDF) padahal data tersebut penting untuk pelaporan.
3. THE System SHALL mengidentifikasi modul yang belum memiliki fitur import data dari Excel untuk memudahkan migrasi data awal.
4. THE System SHALL mengidentifikasi fitur yang ada di roadmap industri ERP Indonesia tetapi belum ada di Qalcuity, seperti: integrasi e-Faktur DJP, integrasi BPJS Ketenagakerjaan/Kesehatan, laporan SPT Tahunan, dan integrasi bank transfer otomatis.
5. THE System SHALL mengidentifikasi modul yang belum memiliki dashboard/widget di halaman utama padahal data modul tersebut penting untuk monitoring harian.
6. THE System SHALL mengidentifikasi fitur mobile yang belum optimal: halaman yang belum responsif, fitur yang tidak dapat digunakan di mobile, dan komponen yang tidak touch-friendly.
7. THE System SHALL mengidentifikasi fitur kolaborasi yang kurang: komentar pada transaksi, mention pengguna, shared workspace, dan real-time collaboration.
8. THE System SHALL mengidentifikasi fitur pelaporan yang kurang: laporan yang belum ada, laporan yang tidak akurat, dan laporan yang tidak dapat dikustomisasi.

### Requirement 19: Audit Pengaturan & Konfigurasi Sistem

**User Story:** Sebagai admin, saya ingin semua halaman pengaturan berfungsi dengan benar dan perubahan pengaturan langsung diterapkan ke seluruh aplikasi, sehingga saya dapat mengkonfigurasi sistem sesuai kebutuhan bisnis.

#### Acceptance Criteria

1. THE System SHALL memastikan halaman pengaturan perusahaan (`/settings/company`) berfungsi: logo, nama perusahaan, alamat, NPWP, dan informasi lainnya dapat disimpan dan ditampilkan di dokumen (invoice, PO, slip gaji, dll.).
2. THE System SHALL memastikan pengaturan modul aktif berfungsi: admin dapat mengaktifkan/menonaktifkan modul dan perubahan langsung tercermin di sidebar dan akses pengguna.
3. THE System SHALL memastikan pengaturan akuntansi berfungsi: mata uang default, format tanggal, metode costing, dan CoA default dapat dikonfigurasi.
4. THE System SHALL memastikan pengaturan notifikasi berfungsi: admin dapat mengkonfigurasi template email, nomor WhatsApp, dan preferensi notifikasi default.
5. THE System SHALL memastikan pengaturan API (API keys untuk integrasi eksternal) dapat disimpan dengan aman (terenkripsi) dan digunakan dengan benar oleh service yang membutuhkannya.
6. THE System SHALL memastikan pengaturan SuperAdmin (system settings) berfungsi: konfigurasi platform-wide seperti Gemini API key, SMTP, dan pengaturan keamanan dapat disimpan dan diterapkan.
7. WHEN pengaturan sistem diubah, THE System SHALL membersihkan cache yang relevan sehingga perubahan langsung berlaku tanpa perlu restart server.
8. THE System SHALL memastikan fitur onboarding wizard berfungsi: tenant baru dipandu melalui langkah-langkah setup awal yang diperlukan.

### Requirement 20: Audit Laporan & Analytics

**User Story:** Sebagai manajer dan pemilik bisnis, saya ingin semua laporan menghasilkan data yang akurat dan dapat diekspor, serta dashboard analytics memberikan insight yang berguna untuk pengambilan keputusan.

#### Acceptance Criteria

1. THE System SHALL memastikan semua laporan keuangan (Neraca, Laba Rugi, Arus Kas, Buku Besar, Neraca Saldo) menghasilkan angka yang akurat dan konsisten.
2. THE System SHALL memastikan semua laporan operasional (laporan penjualan, pembelian, inventory, HRM, payroll) dapat difilter berdasarkan periode, cabang, dan parameter relevan lainnya.
3. THE System SHALL memastikan semua laporan dapat diekspor ke format Excel dan PDF dengan layout yang rapi dan profesional.
4. THE System SHALL memastikan dashboard analytics menampilkan grafik yang akurat menggunakan Chart.js dengan data real-time dari database.
5. THE System SHALL memastikan fitur Advanced Analytics (customer segmentation, product profitability, employee performance, cashflow forecast) menghasilkan analisis yang akurat.
6. THE System SHALL memastikan fitur scheduled reports berfungsi: laporan digenerate dan dikirim via email sesuai jadwal yang dikonfigurasi.
7. THE System SHALL memastikan fitur shared reports berfungsi: laporan dapat dibagikan dengan link yang aman dan dapat diakses tanpa login.
8. THE System SHALL memastikan semua widget dashboard dapat dikustomisasi: ditambah, dihapus, diubah ukuran, dan diatur posisinya oleh pengguna.

### Requirement 21: Konsistensi Bahasa & Teks UI

**User Story:** Sebagai pengguna Indonesia, saya ingin semua teks di antarmuka aplikasi menggunakan Bahasa Indonesia yang benar dan konsisten, sehingga aplikasi terasa natural dan mudah dipahami.

#### Acceptance Criteria

1. THE System SHALL memastikan semua label form, judul halaman, pesan error, pesan sukses, placeholder, dan tooltip menggunakan Bahasa Indonesia yang benar dan konsisten.
2. THE System SHALL memastikan tidak ada teks campuran Bahasa Indonesia dan Bahasa Inggris dalam satu kalimat atau label yang sama (kecuali istilah teknis yang memang lazim dalam Bahasa Inggris seperti "Dashboard", "Invoice", "PO").
3. THE System SHALL memastikan format tanggal menggunakan format Indonesia (DD/MM/YYYY atau "12 Januari 2025") di semua tampilan.
4. THE System SHALL memastikan format angka menggunakan format Indonesia (titik sebagai pemisah ribuan, koma sebagai desimal) di semua tampilan angka dan mata uang.
5. THE System SHALL memastikan semua pesan konfirmasi hapus, void, dan aksi destruktif lainnya menggunakan Bahasa Indonesia yang jelas dan meminta konfirmasi eksplisit dari pengguna.
6. THE System SHALL memastikan semua halaman error (404, 403, 500) menampilkan pesan dalam Bahasa Indonesia yang informatif dan menyediakan link untuk kembali ke halaman yang relevan.
7. THE System SHALL memastikan semua email notifikasi yang dikirim ke pengguna menggunakan template Bahasa Indonesia yang profesional.

### Requirement 22: Pengembangan Fitur Baru — Peningkatan Kompetitif

**User Story:** Sebagai product owner, saya ingin Qalcuity ERP memiliki fitur-fitur unggulan yang membedakannya dari kompetitor dan menjadikannya ERP pilihan utama UKM Indonesia.

#### Acceptance Criteria

1. WHERE fitur integrasi e-Faktur DJP diaktifkan, THE System SHALL menyediakan kemampuan untuk generate, upload, dan sinkronisasi faktur pajak dengan sistem DJP Online.
2. WHERE fitur integrasi BPJS diaktifkan, THE System SHALL menyediakan kalkulasi iuran BPJS Ketenagakerjaan dan Kesehatan yang akurat dan laporan yang dapat diupload ke portal BPJS.
3. THE System SHALL menyediakan fitur mobile app yang optimal: semua halaman utama dapat diakses dan digunakan dengan nyaman di smartphone Android dan iOS melalui browser.
4. THE System SHALL menyediakan fitur bulk operations yang komprehensif: bulk approve, bulk print, bulk export, dan bulk status update untuk semua modul utama.
5. THE System SHALL menyediakan fitur template dokumen yang dapat dikustomisasi: admin dapat mengubah layout invoice, PO, slip gaji, dan dokumen lainnya sesuai branding perusahaan.
6. THE System SHALL menyediakan fitur multi-company yang berfungsi penuh: konsolidasi laporan keuangan dari beberapa entitas bisnis dalam satu platform.
7. THE System SHALL menyediakan fitur API publik yang terdokumentasi dengan baik sehingga developer dapat mengintegrasikan sistem eksternal dengan Qalcuity ERP.
8. THE System SHALL menyediakan fitur backup dan restore data yang dapat dilakukan oleh admin tenant secara mandiri.

### Requirement 23: Audit Multi-Tenancy & Isolasi Data

**User Story:** Sebagai SuperAdmin, saya ingin memastikan data setiap tenant benar-benar terisolasi dan tidak ada kebocoran data antar tenant, sehingga keamanan dan privasi data setiap pelanggan terjamin.

#### Acceptance Criteria

1. THE System SHALL memastikan setiap model yang menyimpan data tenant menggunakan trait `BelongsToTenant` yang secara otomatis menambahkan filter `tenant_id` pada semua query.
2. THE System SHALL memastikan middleware `EnforceTenantIsolation` memvalidasi semua route model binding terhadap `tenant_id` pengguna yang sedang login.
3. THE System SHALL memastikan tidak ada query yang dapat mengakses data tenant lain, bahkan melalui manipulasi parameter URL atau request body.
4. THE System SHALL memastikan SuperAdmin dapat mengakses data semua tenant melalui panel SuperAdmin dengan menggunakan `withoutTenantScope()` yang tepat.
5. THE System SHALL memastikan fitur `CheckTenantActive` middleware berfungsi: tenant yang dinonaktifkan oleh SuperAdmin tidak dapat login atau mengakses aplikasi.
6. THE System SHALL memastikan semua job background (queue) yang memproses data tenant menggunakan `tenant_id` yang benar dan tidak mencampur data antar tenant.
7. THE System SHALL memastikan cache key selalu menyertakan `tenant_id` sehingga cache satu tenant tidak dapat diakses oleh tenant lain.

### Requirement 24: Audit Fitur Subscription & Billing

**User Story:** Sebagai SuperAdmin, saya ingin sistem subscription dan billing berfungsi dengan benar sehingga pendapatan platform dapat dikelola dengan baik dan tenant mendapatkan akses sesuai paket yang dibayar.

#### Acceptance Criteria

1. THE System SHALL memastikan alur subscription berfungsi: tenant memilih paket → pembayaran via Midtrans → aktivasi paket → akses modul sesuai paket.
2. THE System SHALL memastikan notifikasi trial expiry dikirim 7 hari, 3 hari, dan 1 hari sebelum masa trial berakhir.
3. THE System SHALL memastikan notifikasi subscription payment failed dikirim saat pembayaran perpanjangan gagal.
4. THE System SHALL memastikan halaman billing tenant menampilkan riwayat pembayaran, invoice, dan status langganan yang akurat.
5. THE System SHALL memastikan SuperAdmin dapat mengubah paket langganan tenant secara manual dari panel SuperAdmin.
6. WHEN paket langganan tenant berakhir dan tidak diperpanjang, THE System SHALL secara otomatis membatasi akses ke fitur premium sambil tetap mengizinkan akses ke data yang sudah ada.
7. THE System SHALL memastikan fitur affiliate berfungsi: referral tracking, kalkulasi komisi, dan proses payout berjalan dengan benar.

### Requirement 25: Audit Fitur Gamifikasi, KPI & Loyalitas

**User Story:** Sebagai manajer, saya ingin fitur gamifikasi, KPI tracking, dan program loyalitas berfungsi dengan benar untuk meningkatkan motivasi karyawan dan retensi pelanggan.

#### Acceptance Criteria

1. THE System SHALL memastikan fitur gamifikasi berfungsi: poin diberikan saat karyawan menyelesaikan tugas, badge diberikan saat pencapaian tertentu, dan leaderboard menampilkan peringkat yang akurat.
2. THE System SHALL memastikan fitur KPI tracking berfungsi: target KPI dapat dikonfigurasi, progress diperbarui secara otomatis berdasarkan data transaksi, dan laporan KPI dapat digenerate.
3. THE System SHALL memastikan program loyalitas pelanggan berfungsi: poin diberikan saat transaksi, poin dapat ditukarkan, dan riwayat poin dapat dilihat oleh pelanggan dan admin.
4. THE System SHALL memastikan poin loyalitas tidak kadaluarsa secara tidak terduga dan job `ExpireLoyaltyPoints` berjalan sesuai jadwal yang dikonfigurasi.
5. THE System SHALL memastikan halaman gamifikasi (`/gamification`) menampilkan achievements, leaderboard, dan riwayat poin dengan benar di kedua mode (dark/light).

---

## Kriteria Penerimaan Global

Seluruh perbaikan dan pengembangan yang dilakukan harus memenuhi kriteria berikut:

1. **Zero Error**: Tidak ada error PHP, JavaScript, atau database yang muncul di log saat penggunaan normal aplikasi.
2. **Konsistensi UI**: Semua halaman menggunakan komponen dan pola desain yang konsisten (warna, tipografi, spacing, border radius).
3. **Responsivitas**: Semua halaman dapat digunakan dengan baik di mobile (320px), tablet (768px), dan desktop (1280px+).
4. **Dark/Light Mode**: Semua halaman mendukung kedua mode dengan kontras warna yang memadai.
5. **Performa**: Halaman utama (dashboard, daftar transaksi) dimuat dalam waktu kurang dari 3 detik pada koneksi normal.
6. **Keamanan**: Tidak ada celah keamanan kritis (SQL injection, XSS, CSRF, unauthorized access).
7. **Bahasa Indonesia**: Semua teks UI menggunakan Bahasa Indonesia yang benar dan konsisten.
8. **Integrasi Akuntansi**: Setiap transaksi keuangan menghasilkan jurnal akuntansi yang benar secara otomatis.
9. **Isolasi Tenant**: Data setiap tenant benar-benar terisolasi dan tidak dapat diakses oleh tenant lain.
10. **Notifikasi Lengkap**: Semua event bisnis penting dari semua modul aktif menghasilkan notifikasi yang relevan.
