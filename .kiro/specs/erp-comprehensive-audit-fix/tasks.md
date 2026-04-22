# Tasks — Audit & Perbaikan Komprehensif Qalcuity ERP

## Fase 1: Database & Backend Core

- [x] 1. Audit & Perbaikan Database — ENUM dan Schema
  - [x] 1.1 Scan semua migration untuk kolom ENUM dan identifikasi nilai yang tidak lengkap
  - [x] 1.2 Perbaiki `invoices.status` — tambahkan nilai `voided`, `partial_paid`, `cancelled`
  - [x] 1.3 Perbaiki `sales_orders.status` — sinkronkan dengan semua nilai yang digunakan di controller
  - [x] 1.4 Perbaiki `purchase_orders.status` — sinkronkan dengan semua nilai yang digunakan di controller
  - [x] 1.5 Perbaiki kolom ENUM di tabel Hotel: `rooms.status`, `guests.vip_level`, `housekeeping_tasks` 
  - [x] 1.6 Perbaiki kolom ENUM di tabel Healthcare, Telecom, Manufacturing, Construction, Agriculture
  - [x] 1.7 Tambahkan konstanta status di setiap Model (contoh: `Invoice::STATUS_VOIDED`) untuk type safety
  - [x] 1.8 Update semua Form Request dengan aturan validasi `Rule::in()` menggunakan konstanta model
  - [x] 1.9 Buat migration `alter table` untuk semua perbaikan ENUM (non-destructive)
  - [x] 1.10 Verifikasi seeder demo data berjalan tanpa error truncation setelah perbaikan

- [x] 2. Audit & Perbaikan Route dan Controller
  - [x] 2.1 Scan `routes/web.php` — verifikasi semua controller class dan method ada di filesystem
  - [x] 2.2 Scan `routes/api.php` — verifikasi semua controller class dan method ada di filesystem
  - [x] 2.3 Scan `routes/healthcare.php` — verifikasi semua controller class dan method ada di filesystem
  - [x] 2.4 Perbaiki semua route yang mereferensikan controller atau method yang tidak ada
  - [x] 2.5 Verifikasi semua middleware yang digunakan di route terdaftar di `bootstrap/app.php`
  - [x] 2.6 Buat atau perbaiki halaman error: `errors/403.blade.php`, `errors/404.blade.php`, `errors/500.blade.php` dalam Bahasa Indonesia
  - [x] 2.7 Audit semua named route yang digunakan di Blade view — perbaiki yang tidak terdaftar
  - [x] 2.8 Pastikan semua route yang memerlukan akses modul dilindungi middleware `CheckModulePlanAccess`

- [x] 3. Audit & Perbaikan Model dan Service
  - [x] 3.1 Scan semua Model di `app/Models/` — pastikan model tenant-scoped menggunakan trait `BelongsToTenant`
  - [x] 3.2 Audit semua relasi Eloquent (`hasMany`, `belongsTo`, `hasOne`, `belongsToMany`) — perbaiki yang mereferensikan model atau foreign key yang salah
  - [x] 3.3 Audit semua `$fillable` dan `$guarded` — pastikan tidak ada kolom yang hilang atau salah
  - [x] 3.4 Audit semua `$casts` — pastikan tipe cast sesuai dengan tipe kolom di database
  - [x] 3.5 Audit semua `use` statement di setiap file PHP — perbaiki yang mereferensikan class yang tidak ada
  - [x] 3.6 Audit semua Service class di `app/Services/` — perbaiki method yang memanggil model atau relasi yang tidak ada
  - [x] 3.7 Verifikasi semua Observer di `app/Observers/` terdaftar dengan benar di Service Provider
  - [x] 3.8 Audit semua Form Request di `app/Http/Requests/` — pastikan aturan validasi sesuai kolom database

## Fase 2: View, UI/UX, dan Dark Mode

- [x] 4. Audit & Perbaikan View Blade
  - [x] 4.1 Scan semua file Blade — identifikasi dan perbaiki `Undefined variable` dan null pointer errors
  - [x] 4.2 Tambahkan null-safe operator (`?->`) dan `optional()` di semua view yang mengakses relasi yang mungkin null
  - [x] 4.3 Verifikasi semua komponen Blade (`<x-component-name>`) ada di `resources/views/components/`
  - [x] 4.4 Verifikasi semua `@include`, `@extends`, `@component` mereferensikan file yang ada
  - [x] 4.5 Pastikan semua form memiliki `@csrf` dan `@method()` yang benar
  - [x] 4.6 Audit semua view pagination — pastikan `->links()` dan `->appends()` digunakan dengan benar
  - [x] 4.7 Perbaiki semua view yang menggunakan `route()` helper dengan named route yang tidak terdaftar

- [x] 5. Perbaikan Dark Mode dan Light Mode
  - [x] 5.1 Perbaiki script deteksi tema di `layouts/app.blade.php` — jalankan sebelum render konten untuk mencegah FOUC
  - [x] 5.2 Perbaiki Alpine.js theme store di `app.js` — tambahkan dukungan mode `system` dengan listener `prefers-color-scheme`
  - [x] 5.3 Audit dan perbaiki dark mode pada komponen tabel di semua modul inti (Accounting, Inventory, Sales, Purchasing, HRM, POS)
  - [x] 5.4 Audit dan perbaiki dark mode pada komponen form di semua modul inti
  - [x] 5.5 Audit dan perbaiki dark mode pada komponen card, modal, dropdown, alert, badge, button di semua modul
  - [x] 5.6 Audit dan perbaiki dark mode pada sidebar dan navbar
  - [x] 5.7 Audit dan perbaiki dark mode pada modul industri spesifik (Healthcare, Hotel, F&B, Telecom, Manufacturing, Construction, Agriculture)
  - [x] 5.8 Pastikan semua ikon SVG menggunakan `dark:invert` atau variasi warna yang tepat di dark mode
  - [x] 5.9 Verifikasi kontras warna teks di dark mode memenuhi standar keterbacaan (minimal 4.5:1)

- [x] 6. Audit & Perbaikan UI/UX — Responsivitas dan Komponen
  - [x] 6.1 Audit dan perbaiki responsivitas sidebar — rail 56px di desktop, hamburger/bottom nav di mobile
  - [x] 6.2 Audit dan perbaiki semua tombol — tambahkan state hover, disabled, loading, dan touch target 44x44px
  - [x] 6.3 Audit dan perbaiki semua form — label jelas, pesan error per field, placeholder informatif
  - [x] 6.4 Audit dan perbaiki semua tabel — header jelas, alternating rows, kolom aksi konsisten, pagination berfungsi
  - [x] 6.5 Audit dan perbaiki semua alert dan toast notification — posisi konsisten, warna sesuai, auto-dismiss 5 detik
  - [x] 6.6 Audit dan perbaiki semua modal dialog — tutup dengan X/backdrop/Escape, tidak overflow di mobile
  - [x] 6.7 Audit dan perbaiki semua dropdown menu — posisi benar, tidak terpotong di tepi layar
  - [x] 6.8 Pastikan semua halaman responsif di 320px (mobile), 768px (tablet), 1280px+ (desktop)
  - [x] 6.9 Perbaiki format angka Indonesia (titik ribuan, koma desimal) di semua card statistik dan tabel

## Fase 3: Notifikasi dan Kontrol Akses

- [x] 7. Audit & Perbaikan Sistem Notifikasi
  - [x] 7.1 Audit semua class di `app/Notifications/` — identifikasi modul yang belum memiliki notifikasi
  - [x] 7.2 Buat notifikasi Purchasing: `PurchaseOrderApprovedNotification`, `GoodsReceivedNotification`
  - [x] 7.3 Buat notifikasi HRM: `LeaveApprovedNotification`, `LeaveRejectedNotification`, `ContractExpiryNotification`
  - [x] 7.4 Buat notifikasi Payroll: `PayslipAvailableNotification` (verifikasi `PayrollProcessedNotification`)
  - [x] 7.5 Buat notifikasi POS: `CashierSessionOpenedNotification`, `CashierSessionClosedNotification`
  - [x] 7.6 Buat notifikasi Project: `TaskAssignedNotification`, `DeadlineApproachingNotification`
  - [x] 7.7 Buat notifikasi Manufacturing: `WorkOrderCompletedNotification`, `MaterialShortageNotification`
  - [x] 7.8 Buat notifikasi Construction: `ProjectMilestoneNotification`
  - [x] 7.9 Buat notifikasi Agriculture: `HarvestReminderNotification`, `PlantingScheduleNotification`
  - [x] 7.10 Buat notifikasi Hotel: `ReservationCreatedNotification`, `CheckInReminderNotification`
  - [x] 7.11 Buat notifikasi Telecom: `PackageExpiryNotification`, `InvoiceDueNotification`
  - [x] 7.12 Pastikan semua notifikasi mendukung tiga channel: in-app, email, push browser
  - [x] 7.13 Implementasi sistem preferensi notifikasi per user per channel per tipe
  - [x] 7.14 Pastikan bell notifikasi di navbar menampilkan jumlah unread secara real-time
  - [x] 7.15 Pastikan halaman `/notifications` menampilkan semua notifikasi dengan filter modul, status, dan tanggal
  - [x] 7.16 Pastikan notifikasi tidak dikirim dari modul yang dinonaktifkan untuk tenant

- [x] 8. Audit & Perbaikan Kontrol Akses
  - [x] 8.1 Audit `PlanModuleMap` — pastikan semua modul terdaftar dengan benar per paket langganan
  - [x] 8.2 Pastikan sidebar hanya menampilkan menu sesuai paket langganan, role, dan pengaturan modul tenant
  - [x] 8.3 Pastikan semua tombol aksi (tambah, edit, hapus, approve, export) hanya tampil jika user memiliki permission
  - [x] 8.4 Buat halaman upgrade yang informatif untuk modul yang tidak termasuk paket langganan
  - [x] 8.5 Verifikasi middleware `EnforceTenantIsolation` berfungsi di semua route data tenant
  - [x] 8.6 Verifikasi role `kasir` hanya akses POS, role `gudang` hanya akses Inventory, dll.
  - [x] 8.7 Pastikan perubahan pengaturan modul aktif langsung berlaku tanpa logout/login ulang
  - [x] 8.8 Pastikan SuperAdmin dapat akses semua tenant dan fitur tanpa batasan
  - [x] 8.9 Pastikan tenant yang masa trial berakhir mendapat notifikasi upgrade yang jelas

## Fase 4: Alur Bisnis Per Modul

- [x] 9. Audit & Perbaikan Alur Sales dan Purchasing
  - [x] 9.1 Audit alur Sales: Quotation → Sales Order → Delivery Order → Invoice → Payment → Journal Entry
  - [x] 9.2 Perbaiki semua transisi status yang tidak valid di alur Sales
  - [x] 9.3 Pastikan void/cancel invoice membuat jurnal pembalik yang benar dan memperbarui stok/piutang
  - [x] 9.4 Audit alur Purchasing: Purchase Request → PO → Goods Receipt → Supplier Invoice → Payment → Journal
  - [x] 9.5 Perbaiki semua transisi status yang tidak valid di alur Purchasing
  - [x] 9.6 Audit dan perbaiki alur Down Payment — pengurangan saldo invoice dan pelunasan sisa
  - [x] 9.7 Audit dan perbaiki alur Sales Return dan Purchase Return — pembaruan stok dan jurnal yang tepat
  - [x] 9.8 Audit dan perbaiki alur Approval Workflow — notifikasi approver, approve/reject, notifikasi pemohon

- [x] 10. Audit & Perbaikan Modul Akuntansi
  - [x] 10.1 Verifikasi Chart of Accounts (CoA) — CRUD berfungsi, tipe akun valid
  - [x] 10.2 Pastikan jurnal umum menolak input jika debit ≠ kredit
  - [x] 10.3 Verifikasi laporan Neraca, Laba Rugi, dan Arus Kas menghasilkan angka yang konsisten
  - [x] 10.4 Audit dan perbaiki rekonsiliasi bank — import mutasi, matching, identifikasi selisih
  - [x] 10.5 Verifikasi fitur multi-currency — konversi kurs, revaluasi, laporan dalam mata uang dasar
  - [x] 10.6 Verifikasi perhitungan PPN (11%) dan PPh — laporan pajak akurat
  - [x] 10.7 Verifikasi period lock — tidak ada jurnal baru ke periode yang dikunci
  - [x] 10.8 Verifikasi recurring journal — jurnal berulang dibuat otomatis sesuai jadwal

- [x] 11. Audit & Perbaikan Modul Inventory
  - [x] 11.1 Pastikan stok diperbarui real-time saat penerimaan, pengeluaran, transfer, penyesuaian
  - [x] 11.2 Verifikasi metode costing FIFO dan Average Cost menghasilkan HPP yang benar
  - [x] 11.3 Verifikasi fitur multi-gudang — stok per gudang, transfer antar gudang
  - [x] 11.4 Verifikasi fitur batch/lot tracking — nomor unik, tanggal kadaluarsa, traceability
  - [x] 11.5 Verifikasi fitur barcode dan QR code — generate, print, scan
  - [x] 11.6 Verifikasi fitur landed cost — alokasi biaya ke produk yang diterima
  - [x] 11.7 Verifikasi alert stok minimum — notifikasi dikirim saat stok di bawah minimum
  - [x] 11.8 Verifikasi fitur WMS — lokasi rak, bin, zona gudang untuk picking/putaway

- [x] 12. Audit & Perbaikan Modul HRM dan Payroll
  - [x] 12.1 Verifikasi fitur absensi — input manual, integrasi fingerprint, kalkulasi keterlambatan/lembur
  - [x] 12.2 Verifikasi kalkulasi payroll — gaji bersih benar (komponen, potongan, BPJS, PPh 21)
  - [x] 12.3 Verifikasi generate slip gaji PDF dan pengiriman ke email karyawan
  - [x] 12.4 Verifikasi Employee Self Service (ESS) — pengajuan cuti, lembur, reimbursement, lihat slip gaji
  - [x] 12.5 Verifikasi fitur rekrutmen — posting lowongan, lamaran, seleksi, onboarding
  - [x] 12.6 Verifikasi manajemen shift — jadwal shift, penugasan karyawan, laporan kehadiran
  - [x] 12.7 Verifikasi jurnal akuntansi payroll diposting ke CoA yang benar saat payroll diproses

- [x] 13. Audit & Perbaikan Modul POS
  - [x] 13.1 Verifikasi buka/tutup sesi kasir dengan rekap transaksi yang akurat
  - [x] 13.2 Verifikasi pencarian produk cepat (< 500ms) via barcode scan dan teks
  - [x] 13.3 Verifikasi semua metode pembayaran — tunai (kembalian otomatis), kartu, QRIS, split payment
  - [x] 13.4 Verifikasi integrasi payment gateway (Midtrans, Xendit, Duitku) dan callback handling
  - [x] 13.5 Verifikasi cetak struk ke printer thermal ESC/POS dan kirim via email/WhatsApp
  - [x] 13.6 Verifikasi mode offline POS — transaksi saat offline, sinkronisasi saat online kembali
  - [x] 13.7 Verifikasi loyalty point di POS — poin diberikan saat transaksi, dapat ditukarkan
  - [x] 13.8 Pastikan tutup sesi kasir otomatis posting jurnal penjualan dan kas ke Accounting

- [x] 14. Audit & Perbaikan Modul Industri Spesifik
  - [x] 14.1 Audit modul Healthcare — pendaftaran pasien, EMR, rawat inap, rawat jalan, farmasi, lab, penagihan
  - [x] 14.2 Audit modul Hotel — reservasi, check-in/out, housekeeping, front office, night audit
  - [x] 14.3 Audit modul F&B — manajemen menu, KOT, reservasi meja, manajemen bahan baku
  - [x] 14.4 Audit modul Telecom/ISP — manajemen pelanggan, paket, hotspot, tracking, penagihan otomatis
  - [x] 14.5 Audit modul Manufacturing — BOM, Work Order, MRP, kapasitas, biaya produksi
  - [x] 14.6 Audit modul Construction — RAB, progress proyek, laporan harian, subkontraktor
  - [x] 14.7 Audit modul Agriculture — manajemen lahan, siklus tanam, log panen
  - [x] 14.8 Pastikan semua modul industri terintegrasi dengan Accounting untuk posting jurnal otomatis
  - [x] 14.9 Pastikan semua modul industri menggunakan layout konsisten dan mendukung dark/light mode

## Fase 5: Performa, Keamanan, dan Integrasi

- [x] 15. Audit & Perbaikan Performa dan Keamanan
  - [x] 15.1 Audit semua query database — tambahkan index yang tepat untuk tabel besar (tenant_id, status, date)
  - [x] 15.2 Audit semua controller dan view — perbaiki N+1 query dengan eager loading (`with()`)
  - [x] 15.3 Verifikasi cache strategy — semua cache key menyertakan `tenant_id`, invalidasi cache benar
  - [x] 15.4 Verifikasi semua input divalidasi dan di-sanitasi (SQL injection, XSS, CSRF protection)
  - [x] 15.5 Verifikasi semua file upload — validasi tipe dan ukuran, simpan di lokasi aman
  - [x] 15.6 Verifikasi fitur 2FA (Two-Factor Authentication) berfungsi dengan Google Authenticator
  - [x] 15.7 Verifikasi rate limiting di semua endpoint API dan AI
  - [x] 15.8 Verifikasi security headers di middleware `AddSecurityHeaders` (CSP, HSTS, X-Frame-Options, dll.)
  - [x] 15.9 Verifikasi audit trail mencatat semua perubahan data sensitif dengan user, timestamp, before/after
  - [x] 15.10 Verifikasi account lockout setelah percobaan login gagal berulang

- [x] 16. Audit & Perbaikan Integrasi Eksternal
  - [x] 16.1 Verifikasi integrasi marketplace (Shopee, Tokopedia, Lazada) — sinkronisasi produk, stok, harga, pesanan
  - [x] 16.2 Verifikasi integrasi payment gateway (Midtrans, Xendit, Duitku) — pembayaran, callback, status update
  - [x] 16.3 Verifikasi integrasi shipping (RajaOngkir, JNE, J&T) — kalkulasi ongkir, resi, tracking
  - [x] 16.4 Verifikasi integrasi messaging (WhatsApp, Telegram) — notifikasi dan pesan terkirim
  - [x] 16.5 Verifikasi webhook signature verification untuk semua layanan eksternal
  - [x] 16.6 Pastikan semua integrasi menangani error dengan graceful (log, pesan informatif, tidak crash)
  - [x] 16.7 Verifikasi retry mechanism dengan exponential backoff untuk webhook dan job yang gagal

- [x] 17. Audit & Perbaikan AI Assistant
  - [x] 17.1 Verifikasi AI Chat — pesan terkirim, respons Gemini API ditampilkan dengan format Markdown
  - [x] 17.2 Verifikasi AI Agent — eksekusi operasi ERP dengan konfirmasi user sebelum operasi write
  - [x] 17.3 Verifikasi quota AI per tenant — penggunaan dicatat, akses dibatasi saat quota habis
  - [x] 17.4 Verifikasi AI Memory — konteks percakapan sebelumnya digunakan untuk respons yang relevan
  - [x] 17.5 Verifikasi Proactive Insights — AI menganalisis data bisnis dan memberikan rekomendasi otomatis
  - [x] 17.6 Pastikan semua AI controller (AccountingAi, SalesAi, HrmAi, InventoryAi, dll.) berfungsi benar
  - [x] 17.7 Pastikan Gemini API unavailable ditangani dengan pesan error informatif tanpa crash halaman

## Fase 6: Laporan, Pengaturan, dan Fitur Baru

- [x] 18. Audit & Perbaikan Laporan dan Analytics
  - [x] 18.1 Verifikasi semua laporan keuangan (Neraca, Laba Rugi, Arus Kas, Buku Besar, Neraca Saldo) — angka akurat dan konsisten
  - [x] 18.2 Verifikasi semua laporan operasional dapat difilter berdasarkan periode, cabang, dan parameter relevan
  - [x] 18.3 Verifikasi semua laporan dapat diekspor ke Excel dan PDF dengan layout rapi
  - [x] 18.4 Verifikasi dashboard analytics — grafik Chart.js akurat dengan data real-time
  - [x] 18.5 Verifikasi fitur scheduled reports — laporan digenerate dan dikirim via email sesuai jadwal
  - [x] 18.6 Verifikasi fitur shared reports — laporan dapat dibagikan dengan link aman
  - [x] 18.7 Verifikasi semua widget dashboard dapat dikustomisasi (tambah, hapus, resize, reorder)

- [x] 19. Audit & Perbaikan Pengaturan Sistem
  - [x] 19.1 Verifikasi pengaturan perusahaan — logo, nama, alamat, NPWP tampil di semua dokumen
  - [x] 19.2 Verifikasi pengaturan modul aktif — perubahan langsung tercermin di sidebar dan akses user
  - [x] 19.3 Verifikasi pengaturan akuntansi — mata uang default, format tanggal, metode costing, CoA default
  - [x] 19.4 Verifikasi pengaturan notifikasi — template email, nomor WhatsApp, preferensi default
  - [x] 19.5 Verifikasi pengaturan API keys tersimpan terenkripsi dan digunakan dengan benar
  - [x] 19.6 Verifikasi pengaturan SuperAdmin — Gemini API key, SMTP, pengaturan keamanan
  - [x] 19.7 Pastikan perubahan pengaturan membersihkan cache yang relevan secara otomatis
  - [x] 19.8 Verifikasi onboarding wizard berfungsi untuk tenant baru

- [x] 20. Audit Konsistensi Bahasa Indonesia
  - [x] 20.1 Audit semua label form, judul halaman, pesan error, placeholder — pastikan Bahasa Indonesia yang benar
  - [x] 20.2 Pastikan format tanggal Indonesia (DD/MM/YYYY) di semua tampilan
  - [x] 20.3 Pastikan format angka Indonesia (titik ribuan, koma desimal) di semua tampilan
  - [x] 20.4 Pastikan semua pesan konfirmasi hapus/void menggunakan Bahasa Indonesia yang jelas
  - [x] 20.5 Pastikan semua halaman error (403, 404, 500) dalam Bahasa Indonesia yang informatif
  - [x] 20.6 Audit semua template email notifikasi — pastikan Bahasa Indonesia yang profesional

- [x] 21. Audit Multi-Tenancy dan Isolasi Data
  - [x] 21.1 Verifikasi semua model tenant-scoped menggunakan `BelongsToTenant` trait
  - [x] 21.2 Verifikasi middleware `EnforceTenantIsolation` memvalidasi semua route model binding
  - [x] 21.3 Verifikasi tidak ada query yang dapat mengakses data tenant lain via manipulasi parameter
  - [x] 21.4 Verifikasi semua cache key menyertakan `tenant_id`
  - [x] 21.5 Verifikasi semua background job menggunakan `tenant_id` yang benar
  - [x] 21.6 Verifikasi `CheckTenantActive` middleware — tenant nonaktif tidak dapat login

- [x] 22. Audit Subscription, Billing, dan Fitur Platform
  - [x] 22.1 Verifikasi alur subscription — pilih paket → bayar via Midtrans → aktivasi → akses modul
  - [x] 22.2 Verifikasi notifikasi trial expiry (7 hari, 3 hari, 1 hari sebelum berakhir)
  - [x] 22.3 Verifikasi halaman billing tenant — riwayat pembayaran, invoice, status langganan
  - [x] 22.4 Verifikasi SuperAdmin dapat ubah paket langganan tenant secara manual
  - [x] 22.5 Verifikasi fitur affiliate — referral tracking, kalkulasi komisi, proses payout
  - [x] 22.6 Verifikasi fitur gamifikasi — poin, badge, leaderboard di dark/light mode
  - [x] 22.7 Verifikasi fitur KPI tracking — target, progress otomatis, laporan KPI
  - [x] 22.8 Verifikasi program loyalitas pelanggan — poin transaksi, penukaran, riwayat poin

## Fase 7: Testing dan Verifikasi

- [x] 23. Implementasi Property-Based Tests
  - [x] 23.1 Buat `tests/Property/TenantIsolationPropertyTest.php` — verifikasi isolasi data antar tenant (min. 100 iterasi)
  - [x] 23.2 Buat `tests/Property/JournalBalancePropertyTest.php` — verifikasi debit = kredit untuk semua jurnal (min. 100 iterasi)
  - [x] 23.3 Buat `tests/Property/StockConsistencyPropertyTest.php` — verifikasi konsistensi stok setelah operasi (min. 100 iterasi)
  - [x] 23.4 Buat `tests/Property/EnumValidationPropertyTest.php` — verifikasi penolakan nilai ENUM tidak valid (min. 100 iterasi)
  - [x] 23.5 Buat `tests/Property/NotificationPreferencePropertyTest.php` — verifikasi round-trip preferensi notifikasi (min. 100 iterasi)

- [x] 24. Implementasi Feature dan Unit Tests
  - [x] 24.1 Buat `tests/Feature/Audit/DatabaseEnumTest.php` — test semua kolom ENUM dengan nilai valid dan invalid
  - [x] 24.2 Buat `tests/Feature/Audit/RouteIntegrityTest.php` — smoke test semua route utama mengembalikan HTTP 200/302
  - [x] 24.3 Buat `tests/Feature/Audit/ModelTenantScopeTest.php` — verifikasi semua model tenant menggunakan `BelongsToTenant`
  - [x] 24.4 Buat `tests/Feature/Audit/BusinessFlowTest.php` — integration test alur Sales, Purchasing, Payroll, POS end-to-end
  - [x] 24.5 Buat `tests/Feature/Audit/NotificationTest.php` — verifikasi semua notifikasi terkirim ke channel yang benar
  - [x] 24.6 Buat `tests/Feature/Audit/AccessControlTest.php` — verifikasi RBAC dan module access control
  - [x] 24.7 Buat `tests/Unit/Audit/JournalBalanceTest.php` — unit test kalkulasi balance jurnal
  - [x] 24.8 Buat `tests/Unit/Audit/StockCalculationTest.php` — unit test kalkulasi stok FIFO dan Average Cost

- [ ] 25. Verifikasi Final dan Dokumentasi
  - [x] 25.1 Jalankan semua property-based tests — pastikan semua lulus dengan minimum 100 iterasi
  - [x] 25.2 Jalankan semua feature dan unit tests — pastikan zero failures
  - [x] 25.3 Verifikasi zero PHP errors/warnings di log setelah semua perbaikan diterapkan
  - [x] 25.4 Verifikasi aplikasi berjalan normal di semua modul utama (manual smoke test)
  - [x] 25.5 Verifikasi dark mode dan light mode konsisten di semua halaman utama
  - [x] 25.6 Verifikasi responsivitas di mobile (320px), tablet (768px), dan desktop (1280px+)
  - [x] 25.7 Dokumentasikan semua perbaikan yang dilakukan dan rekomendasi pengembangan selanjutnya

## Fase 8: Modul Industri Tambahan

- [x] 26. Audit & Perbaikan Modul Cosmetic
  - [x] 26.1 Audit modul Cosmetic — formula builder, batch produksi, QC, packaging, distribusi
  - [x] 26.2 Verifikasi fitur BPOM registration — pendaftaran produk kosmetik ke BPOM
  - [x] 26.3 Verifikasi fitur expiry tracking — produk mendekati kadaluarsa mendapat alert
  - [x] 26.4 Verifikasi fitur variant management — varian produk kosmetik (warna, ukuran, dll.)
  - [x] 26.5 Verifikasi integrasi Cosmetic dengan modul Inventory dan Accounting
  - [x] 26.6 Pastikan semua view Cosmetic mendukung dark/light mode dan responsif

- [x] 27. Audit & Perbaikan Modul Fisheries
  - [x] 27.1 Audit modul Fisheries — manajemen kolam/tambak, siklus budidaya, panen, penjualan
  - [x] 27.2 Verifikasi FisheriesController dan FisheriesViewController berfungsi tanpa error
  - [x] 27.3 Verifikasi integrasi Fisheries dengan modul Inventory dan Accounting
  - [x] 27.4 Pastikan semua view Fisheries mendukung dark/light mode dan responsif

- [ ] 28. Audit & Perbaikan Modul Livestock
  - [ ] 28.1 Audit modul Livestock — manajemen ternak, breeding, dairy, poultry, kesehatan hewan
  - [ ] 28.2 Verifikasi BreedingController, DairyController, PoultryController, HealthController berfungsi
  - [ ] 28.3 Verifikasi WasteManagementController untuk pengelolaan limbah peternakan
  - [ ] 28.4 Verifikasi integrasi Livestock dengan modul Inventory dan Accounting
  - [ ] 28.5 Pastikan semua view Livestock mendukung dark/light mode dan responsif

- [ ] 29. Audit & Perbaikan Modul Tour & Travel
  - [ ] 29.1 Audit modul Tour & Travel — paket wisata, booking, manajemen tamu, laporan
  - [ ] 29.2 Verifikasi TourBookingController, TourPackageController, TourTravelAnalyticsController
  - [ ] 29.3 Verifikasi integrasi Tour & Travel dengan modul Accounting dan CRM
  - [ ] 29.4 Pastikan semua view Tour & Travel mendukung dark/light mode dan responsif

- [ ] 30. Audit & Perbaikan Modul Printing
  - [ ] 30.1 Audit modul Printing — manajemen job cetak, estimasi biaya, tracking order
  - [ ] 30.2 Verifikasi PrintJobController dan PrintController berfungsi tanpa error
  - [ ] 30.3 Verifikasi integrasi Printing dengan modul Sales dan Accounting
  - [ ] 30.4 Pastikan semua view Printing mendukung dark/light mode dan responsif

## Fase 9: Fitur Platform Lanjutan

- [ ] 31. Audit & Perbaikan Modul CRM
  - [ ] 31.1 Verifikasi CrmController — manajemen lead, pipeline, kontak, aktivitas
  - [ ] 31.2 Verifikasi CrmAiController dan CrmAiService — rekomendasi AI untuk sales pipeline
  - [ ] 31.3 Verifikasi LeadConversionService — konversi lead ke customer dan sales order
  - [ ] 31.4 Verifikasi integrasi CRM dengan modul Sales dan Accounting
  - [ ] 31.5 Pastikan semua view CRM mendukung dark/light mode dan responsif

- [ ] 32. Audit & Perbaikan Modul Fleet Management
  - [ ] 32.1 Verifikasi FleetController — manajemen kendaraan, jadwal servis, tracking
  - [ ] 32.2 Verifikasi integrasi Fleet dengan modul Asset dan Accounting
  - [ ] 32.3 Pastikan semua view Fleet mendukung dark/light mode dan responsif

- [ ] 33. Audit & Perbaikan Modul Telemedicine
  - [ ] 33.1 Verifikasi TelemedicineController dan TelemedicineService — konsultasi online, video call
  - [ ] 33.2 Verifikasi TelemedicineReminderService — notifikasi jadwal konsultasi
  - [ ] 33.3 Verifikasi TelemedicineFeedbackService — rating dan ulasan dokter
  - [ ] 33.4 Verifikasi TelemedicineSettingsController — konfigurasi layanan telemedicine
  - [ ] 33.5 Pastikan semua view Telemedicine mendukung dark/light mode dan responsif

- [ ] 34. Audit & Perbaikan Modul Security & CCTV
  - [ ] 34.1 Verifikasi SecurityController dan CctvController — integrasi CCTV, access log
  - [ ] 34.2 Verifikasi CctvIntegrationService berfungsi tanpa error
  - [ ] 34.3 Pastikan semua view Security mendukung dark/light mode dan responsif

- [ ] 35. Audit & Perbaikan Automation & Workflow Engine
  - [ ] 35.1 Verifikasi WorkflowController dan WorkflowEngine — pembuatan dan eksekusi workflow otomatis
  - [ ] 35.2 Verifikasi trigger workflow berfungsi untuk semua event yang dikonfigurasi
  - [ ] 35.3 Verifikasi kondisi dan aksi workflow (kirim notifikasi, update status, buat dokumen)
  - [ ] 35.4 Pastikan semua view Automation mendukung dark/light mode dan responsif

- [ ] 36. Audit & Perbaikan Customer Portal
  - [ ] 36.1 Verifikasi CustomerPortalController — portal self-service untuk pelanggan
  - [ ] 36.2 Verifikasi pelanggan dapat melihat invoice, status pesanan, dan riwayat transaksi
  - [ ] 36.3 Verifikasi pelanggan dapat melakukan pembayaran melalui portal
  - [ ] 36.4 Pastikan semua view Customer Portal mendukung dark/light mode dan responsif

- [ ] 37. Audit & Perbaikan Modul Helpdesk
  - [ ] 37.1 Verifikasi HelpdeskController — tiket support, kategori, prioritas, assignment
  - [ ] 37.2 Verifikasi alur tiket: buat → assign → proses → resolve → close
  - [ ] 37.3 Verifikasi notifikasi helpdesk — tiket baru, update status, tiket selesai
  - [ ] 37.4 Pastikan semua view Helpdesk mendukung dark/light mode dan responsif

- [ ] 38. Audit & Perbaikan Modul Document Management
  - [ ] 38.1 Verifikasi DocumentController — upload, versioning, approval, expiry tracking
  - [ ] 38.2 Verifikasi DocumentVersioningService — riwayat versi dokumen tersimpan dengan benar
  - [ ] 38.3 Verifikasi DocumentApprovalService — alur approval dokumen berfungsi
  - [ ] 38.4 Verifikasi DocumentOcrService — OCR untuk ekstraksi teks dari dokumen
  - [ ] 38.5 Verifikasi DocumentSignatureService — tanda tangan digital berfungsi
  - [ ] 38.6 Pastikan semua view Document Management mendukung dark/light mode dan responsif

- [x] 39. Audit & Perbaikan Modul Multi-Company & Konsolidasi
  - [x] 39.1 Verifikasi MultiCompanyController — manajemen entitas bisnis dalam satu platform
  - [x] 39.2 Verifikasi ConsolidationService — konsolidasi laporan keuangan antar entitas
  - [x] 39.3 Verifikasi CompanyGroupController — pengelompokan perusahaan untuk konsolidasi (Fixed HasMany::attach() error)
  - [ ] 39.4 Pastikan semua view Multi-Company mendukung dark/light mode dan responsif

- [ ] 40. Audit & Perbaikan Modul IoT & Smart Devices
  - [ ] 40.1 Verifikasi IotDeviceController — manajemen perangkat IoT yang terhubung
  - [ ] 40.2 Verifikasi SmartScaleController dan SmartScaleService — integrasi timbangan digital
  - [ ] 40.3 Verifikasi RfidController — integrasi RFID untuk tracking inventory
  - [ ] 40.4 Verifikasi FingerprintDeviceController — integrasi fingerprint untuk absensi
  - [ ] 40.5 Verifikasi IotWebhookController — penerimaan data dari perangkat IoT
  - [ ] 40.6 Pastikan semua view IoT mendukung dark/light mode dan responsif

- [ ] 41. Audit & Perbaikan Modul Anomaly Detection & AI Insights
  - [ ] 41.1 Verifikasi AnomalyController dan AnomalyDetectionService — deteksi anomali transaksi
  - [ ] 41.2 Verifikasi AiInsightService — generate insight otomatis dari data bisnis
  - [ ] 41.3 Verifikasi GenerateProactiveInsightsJob berjalan sesuai jadwal
  - [ ] 41.4 Verifikasi AiFinancialAdvisorService — rekomendasi keuangan berbasis AI
  - [ ] 41.5 Pastikan semua view Anomaly & AI Insights mendukung dark/light mode dan responsif

- [ ] 42. Audit & Perbaikan Modul Supplier Management
  - [ ] 42.1 Verifikasi SupplierController dan SupplierScorecardController — evaluasi performa supplier
  - [ ] 42.2 Verifikasi SupplierScorecardService — kalkulasi skor supplier berdasarkan KPI
  - [ ] 42.3 Verifikasi StrategicSourcingService — analisis dan rekomendasi sumber pengadaan
  - [ ] 42.4 Verifikasi SupplierPerformanceController — laporan performa supplier
  - [ ] 42.5 Pastikan semua view Supplier Management mendukung dark/light mode dan responsif

- [ ] 43. Audit & Perbaikan Modul Consignment & Deferred Items
  - [ ] 43.1 Verifikasi ConsignmentController — manajemen barang konsinyasi masuk dan keluar
  - [ ] 43.2 Verifikasi DeferredItemController dan DeferredItemService — pendapatan/beban ditangguhkan
  - [ ] 43.3 Verifikasi integrasi Consignment dan Deferred Items dengan modul Accounting
  - [ ] 43.4 Pastikan semua view Consignment dan Deferred mendukung dark/light mode dan responsif

- [ ] 44. Audit & Perbaikan Modul Compliance & GDPR
  - [ ] 44.1 Verifikasi GdprController — manajemen data pribadi, hak akses, penghapusan data
  - [ ] 44.2 Verifikasi RegulatoryComplianceService — kepatuhan regulasi bisnis
  - [ ] 44.3 Verifikasi fitur data export untuk GDPR (right to portability)
  - [ ] 44.4 Pastikan semua view Compliance mendukung dark/light mode dan responsif

- [ ] 45. Audit & Perbaikan Modul Simulation & Forecast
  - [ ] 45.1 Verifikasi SimulationController dan SimulationService — simulasi skenario bisnis
  - [ ] 45.2 Verifikasi ForecastController dan ForecastService — proyeksi penjualan dan keuangan
  - [ ] 45.3 Verifikasi CashFlowProjectionService — proyeksi arus kas jangka pendek dan panjang
  - [ ] 45.4 Pastikan semua view Simulation & Forecast mendukung dark/light mode dan responsif

- [ ] 46. Audit & Perbaikan Modul Ecommerce & Marketplace
  - [ ] 46.1 Verifikasi EcommerceController — manajemen toko online terintegrasi
  - [ ] 46.2 Verifikasi MarketplaceController dan MarketplaceSyncService — sinkronisasi multi-marketplace
  - [ ] 46.3 Verifikasi MarketplaceWebhookController — penerimaan webhook dari marketplace
  - [ ] 46.4 Verifikasi RetryFailedMarketplaceSyncs job berjalan dengan benar
  - [ ] 46.5 Pastikan semua view Ecommerce & Marketplace mendukung dark/light mode dan responsif

- [ ] 47. Audit & Perbaikan Modul Receivables & Bulk Payment
  - [ ] 47.1 Verifikasi ReceivablesController — aging report, follow-up piutang, rekonsiliasi
  - [ ] 47.2 Verifikasi BulkPaymentController — pembayaran massal untuk banyak invoice sekaligus
  - [ ] 47.3 Verifikasi BulkActionsController — bulk approve, bulk print, bulk export
  - [ ] 47.4 Verifikasi integrasi Receivables dengan modul Accounting
  - [ ] 47.5 Pastikan semua view Receivables mendukung dark/light mode dan responsif

- [ ] 48. Audit & Perbaikan Modul Cost Center & Budget
  - [ ] 48.1 Verifikasi CostCenterController — manajemen pusat biaya, alokasi, laporan
  - [ ] 48.2 Verifikasi BudgetController dan BudgetAiController — anggaran, realisasi, analisis AI
  - [ ] 48.3 Verifikasi BudgetExceededNotification dikirim saat anggaran terlampaui
  - [ ] 48.4 Verifikasi integrasi Cost Center dan Budget dengan modul Accounting
  - [ ] 48.5 Pastikan semua view Cost Center & Budget mendukung dark/light mode dan responsif

- [ ] 49. Audit & Perbaikan Modul Project & Project Billing
  - [ ] 49.1 Verifikasi ProjectController — manajemen proyek, tugas, milestone, tim
  - [ ] 49.2 Verifikasi ProjectBillingController — penagihan berbasis proyek (time & material, fixed price)
  - [ ] 49.3 Verifikasi GanttChartService — tampilan Gantt chart proyek berfungsi
  - [ ] 49.4 Verifikasi ProjectTaskAssignedNotification dikirim saat tugas ditugaskan
  - [ ] 49.5 Verifikasi integrasi Project dengan modul Accounting dan HRM
  - [ ] 49.6 Pastikan semua view Project mendukung dark/light mode dan responsif

- [ ] 50. Audit & Perbaikan Modul Asset Management
  - [ ] 50.1 Verifikasi AssetController — manajemen aset tetap, depresiasi, pemeliharaan
  - [ ] 50.2 Verifikasi RunAssetDepreciation job berjalan sesuai jadwal
  - [ ] 50.3 Verifikasi AssetMaintenanceDueNotification dikirim sebelum jadwal pemeliharaan
  - [ ] 50.4 Verifikasi integrasi Asset dengan modul Accounting (jurnal depresiasi)
  - [ ] 50.5 Pastikan semua view Asset mendukung dark/light mode dan responsif

## Fase 10: SuperAdmin, API, dan Infrastruktur

- [ ] 51. Audit & Perbaikan Panel SuperAdmin
  - [ ] 51.1 Verifikasi TenantController — CRUD tenant, aktivasi/nonaktivasi, impersonasi
  - [ ] 51.2 Verifikasi PlanController — manajemen paket langganan dan fitur per paket
  - [ ] 51.3 Verifikasi SystemSettingsController — konfigurasi platform-wide (SMTP, Gemini, keamanan)
  - [ ] 51.4 Verifikasi MonitoringController — monitoring kesehatan sistem, queue, error log
  - [ ] 51.5 Verifikasi AiModelController — manajemen model AI yang digunakan
  - [ ] 51.6 Verifikasi AffiliateManagementController — manajemen program afiliasi
  - [ ] 51.7 Verifikasi PopupAdController — manajemen iklan popup di platform
  - [ ] 51.8 Pastikan semua view SuperAdmin mendukung dark/light mode dan responsif

- [ ] 52. Audit & Perbaikan Public API
  - [ ] 52.1 Verifikasi semua API controller di `app/Http/Controllers/Api/` berfungsi tanpa error
  - [ ] 52.2 Verifikasi ApiBaseController — autentikasi API token, rate limiting, response format
  - [ ] 52.3 Verifikasi ApiInvoiceController, ApiOrderController, ApiProductController, ApiCustomerController
  - [ ] 52.4 Verifikasi ApiStatsController — endpoint statistik bisnis untuk integrasi eksternal
  - [ ] 52.5 Verifikasi semua API industri spesifik (Healthcare, Hotel, HRM, Inventory, dll.)
  - [ ] 52.6 Pastikan semua API mengembalikan response JSON yang konsisten dengan format standar
  - [ ] 52.7 Verifikasi dokumentasi API tersedia dan akurat di halaman `/documentation`

- [ ] 53. Audit & Perbaikan Auth & Security
  - [ ] 53.1 Verifikasi alur registrasi tenant baru — register → verifikasi email → onboarding
  - [ ] 53.2 Verifikasi alur login — email/password, Google OAuth, 2FA
  - [ ] 53.3 Verifikasi TwoFactorController — setup, verifikasi, backup codes
  - [ ] 53.4 Verifikasi GoogleController — OAuth flow berfungsi tanpa error
  - [ ] 53.5 Verifikasi alur reset password — request → email → reset → konfirmasi
  - [ ] 53.6 Verifikasi AccountLockoutService — lockout setelah gagal login berulang
  - [ ] 53.7 Verifikasi semua view Auth mendukung dark/light mode dan responsif

- [ ] 54. Audit & Perbaikan Background Jobs & Queue
  - [ ] 54.1 Verifikasi semua Job di `app/Jobs/` terdaftar dan dapat dieksekusi tanpa error
  - [ ] 54.2 Verifikasi CheckTrialExpiry job — notifikasi trial expiry dikirim tepat waktu
  - [ ] 54.3 Verifikasi ExpireLoyaltyPoints job — poin kadaluarsa diproses dengan benar
  - [ ] 54.4 Verifikasi UpdateCurrencyRates job — kurs mata uang diperbarui secara berkala
  - [ ] 54.5 Verifikasi ProcessRecurringJournals job — jurnal berulang dibuat sesuai jadwal
  - [ ] 54.6 Verifikasi GenerateTelecomInvoicesJob — invoice Telecom digenerate otomatis
  - [ ] 54.7 Verifikasi semua job menggunakan `tenant_id` yang benar dan tidak mencampur data antar tenant
  - [ ] 54.8 Verifikasi failed job handling — retry dengan backoff, notifikasi admin setelah max retries

- [ ] 55. Audit & Perbaikan Cloud Storage & Backup
  - [ ] 55.1 Verifikasi CloudStorageController — upload, download, delete file ke AWS S3 / Google Cloud
  - [ ] 55.2 Verifikasi AutomatedBackupService — backup database terjadwal berfungsi
  - [ ] 55.3 Verifikasi RestorePointService — restore dari backup berfungsi
  - [ ] 55.4 Verifikasi DataArchivalService — arsip data lama ke cold storage
  - [ ] 55.5 Pastikan semua file upload menggunakan cloud storage (bukan local disk) di production

- [ ] 56. Audit & Perbaikan Zero-Input & Smart Features
  - [ ] 56.1 Verifikasi ZeroInputController dan ZeroInputService — fitur input otomatis berbasis AI
  - [ ] 56.2 Verifikasi QuickSearchController — pencarian global di semua modul berfungsi cepat
  - [ ] 56.3 Verifikasi SavedSearchController — pencarian tersimpan dapat digunakan kembali
  - [ ] 56.4 Verifikasi ReminderController — pengingat manual dan otomatis berfungsi
  - [ ] 56.5 Verifikasi CustomFieldController — field kustom dapat ditambahkan ke semua modul utama
  - [ ] 56.6 Verifikasi SignatureController — tanda tangan digital di dokumen berfungsi
  - [ ] 56.7 Verifikasi CertificateController — generate sertifikat (pelatihan, dll.) berfungsi

- [ ] 57. Audit & Perbaikan Mobile & Offline
  - [ ] 57.1 Verifikasi MobileController dan MobileOptimizationService — halaman mobile-optimized
  - [ ] 57.2 Verifikasi OfflineSyncController — sinkronisasi data offline ke online
  - [ ] 57.3 Verifikasi service worker (`sw.js`) — caching offline berfungsi dengan benar
  - [ ] 57.4 Verifikasi PushSubscriptionController — subscribe/unsubscribe push notification
  - [ ] 57.5 Verifikasi WebPushService — push notification terkirim ke browser
  - [ ] 57.6 Pastikan semua halaman utama dapat digunakan di mobile tanpa horizontal scroll

- [ ] 58. Audit & Perbaikan Laporan Keuangan Lanjutan
  - [ ] 58.1 Verifikasi WriteoffController — penghapusan piutang tak tertagih dengan jurnal yang benar
  - [ ] 58.2 Verifikasi TransactionChainController — lacak rantai transaksi dari awal hingga akhir
  - [ ] 58.3 Verifikasi ConsolidationController — laporan konsolidasi multi-entitas
  - [ ] 58.4 Verifikasi AdvancedAnalyticsDashboardController — dashboard analytics lanjutan
  - [ ] 58.5 Verifikasi SharedReportController — laporan yang dibagikan via link aman
  - [ ] 58.6 Verifikasi UndoRollbackService — pembatalan transaksi dengan rollback yang benar
  - [ ] 58.7 Verifikasi TransactionSagaService — saga pattern untuk transaksi multi-step

- [ ] 59. Audit & Perbaikan Integrasi Akuntansi Eksternal
  - [ ] 59.1 Verifikasi integrasi Jurnal.id — sinkronisasi jurnal dan laporan keuangan
  - [ ] 59.2 Verifikasi integrasi Accurate Online — sinkronisasi data akuntansi
  - [ ] 59.3 Verifikasi AccountingIntegration model dan AccountingSyncLog — log sinkronisasi tersimpan
  - [ ] 59.4 Verifikasi TenantIntegrationSettingsController — konfigurasi integrasi per tenant
  - [ ] 59.5 Verifikasi OAuthController — OAuth flow untuk integrasi akuntansi eksternal

- [ ] 60. Audit & Perbaikan Fitur Tambahan yang Teridentifikasi
  - [ ] 60.1 Verifikasi BotController dan BotService — chatbot otomatis untuk customer service
  - [ ] 60.2 Verifikasi CommissionController — manajemen komisi sales dan afiliasi
  - [ ] 60.3 Verifikasi DisciplinaryController — manajemen pelanggaran dan sanksi karyawan
  - [ ] 60.4 Verifikasi TimesheetController — pencatatan jam kerja per proyek/tugas
  - [ ] 60.5 Verifikasi OvertimeController dan OvertimeApprovalService — pengajuan dan approval lembur
  - [ ] 60.6 Verifikasi ReimbursementController — pengajuan dan approval reimbursement karyawan
  - [ ] 60.7 Verifikasi ContractController — manajemen kontrak karyawan dan vendor
  - [ ] 60.8 Verifikasi PriceListController dan PriceListService — daftar harga per pelanggan/segmen
  - [ ] 60.9 Verifikasi TaxController dan TaxCalculationService — konfigurasi dan kalkulasi pajak
  - [ ] 60.10 Verifikasi BankAccountController — manajemen rekening bank perusahaan
  - [ ] 60.11 Verifikasi ExpenseController — pencatatan dan approval pengeluaran operasional
  - [ ] 60.12 Verifikasi ShippingController dan ShippingService — integrasi pengiriman dan tracking
  - [ ] 60.13 Verifikasi ImportController — import data massal dari Excel untuk semua modul
  - [ ] 60.14 Verifikasi AuditController — tampilan audit trail dan riwayat perubahan data
  - [ ] 60.15 Verifikasi HealthCheckController — endpoint health check untuk monitoring sistem

## Fase 11: Verifikasi Final

- [ ] 61. Verifikasi Final dan Dokumentasi
  - [ ] 61.1 Jalankan semua property-based tests — pastikan semua lulus dengan minimum 100 iterasi
  - [ ] 61.2 Jalankan semua feature dan unit tests — pastikan zero failures
  - [ ] 61.3 Verifikasi zero PHP errors/warnings di log setelah semua perbaikan diterapkan
  - [ ] 61.4 Verifikasi aplikasi berjalan normal di semua modul utama (manual smoke test)
  - [ ] 61.5 Verifikasi dark mode dan light mode konsisten di semua halaman utama
  - [ ] 61.6 Verifikasi responsivitas di mobile (320px), tablet (768px), dan desktop (1280px+)
  - [ ] 61.7 Dokumentasikan semua perbaikan yang dilakukan dan rekomendasi pengembangan selanjutnya
