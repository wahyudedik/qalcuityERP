# Requirements Document

## Introduction

Fitur ini memungkinkan tenant baru yang baru selesai registrasi untuk men-generate demo data yang relevan dengan industri mereka selama proses onboarding. Saat ini, halaman "Load Sample Data" menampilkan pesan "No templates available for your industry yet." karena `SampleDataGeneratorService` hanya menghasilkan data minimal (beberapa produk dan pelanggan) dan tidak mencakup semua modul ERP yang tersedia.

Fitur ini akan memperluas `SampleDataGeneratorService` agar menghasilkan demo data yang komprehensif dan realistis per industri — mencakup semua modul utama (akuntansi, HRM, inventory, penjualan, pembelian, manufaktur, dll.) — sehingga tenant baru dapat langsung melihat dan mengeksplorasi sistem ERP dengan data yang bermakna sesuai konteks bisnis mereka.

## Glossary

- **Demo_Generator**: `SampleDataGeneratorService` — service yang bertanggung jawab menghasilkan demo data per industri
- **Industry_Profile**: Record `OnboardingProfile` yang menyimpan pilihan industri dan ukuran bisnis tenant
- **Demo_Template**: Record `SampleDataTemplate` yang mendefinisikan konfigurasi data demo per industri
- **Demo_Log**: Record `SampleDataLog` yang mencatat status dan hasil proses generasi demo data
- **Tenant**: Entitas bisnis multi-tenant yang menggunakan Qalcuity ERP
- **Onboarding_Wizard**: Alur wizard registrasi dengan langkah: Select Industry → Sample Data → Ready!
- **Core_Modules**: Modul-modul dasar yang wajib ada di semua industri: akuntansi (CoA, periode, pajak), warehouse, produk, pelanggan, supplier, karyawan
- **Industry_Modules**: Modul-modul spesifik per industri, misalnya Manufacturing (BOM, work order, QC), Healthcare (pasien, appointment, rekam medis), Hotel (kamar, reservasi, housekeeping)
- **Seeder_Orchestrator**: Komponen yang mengkoordinasikan urutan pemanggilan seeder per modul agar dependensi data terpenuhi

---

## Requirements

### Requirement 1: Ketersediaan Template Demo Data per Industri

**User Story:** Sebagai tenant baru yang memilih industri saat onboarding, saya ingin melihat template demo data yang tersedia untuk industri saya, sehingga saya dapat memahami data apa yang akan di-generate sebelum memulai proses.

#### Acceptance Criteria

1. THE Demo_Generator SHALL menyediakan minimal satu Demo_Template aktif untuk setiap industri yang didukung: `retail`, `restaurant`, `hotel`, `construction`, `agriculture`, `manufacturing`, `services`, `healthcare`.
2. WHEN `SampleDataGeneratorService::getTemplates($industry)` dipanggil dengan nilai industri yang valid, THE Demo_Generator SHALL mengembalikan array non-kosong berisi minimal satu Demo_Template.
3. IF `SampleDataGeneratorService::getTemplates($industry)` dipanggil dengan industri yang tidak didukung, THEN THE Demo_Generator SHALL mengembalikan array kosong.
4. THE Demo_Template SHALL menyertakan field: `industry`, `template_name`, `description`, `modules_included` (array nama modul), `data_config` (konfigurasi jumlah record per entitas), dan `is_active`.
5. WHEN halaman `onboarding.sample-data` dimuat untuk tenant dengan Industry_Profile yang valid, THE Onboarding_Wizard SHALL menampilkan daftar Demo_Template yang tersedia, bukan pesan "No templates available".

---

### Requirement 2: Generasi Demo Data Core Modules (Semua Industri)

**User Story:** Sebagai tenant baru di industri apapun, saya ingin sistem men-generate data dasar yang dibutuhkan semua modul ERP, sehingga saya dapat langsung menggunakan fitur akuntansi, inventory, dan HRM tanpa setup manual.

#### Acceptance Criteria

1. WHEN `Demo_Generator::generateForIndustry($industry, $tenantId, $userId)` dipanggil, THE Demo_Generator SHALL men-generate Core_Modules data dalam urutan yang memenuhi dependensi foreign key: CoA → Periode Akuntansi → Warehouse → Tax Rates → Cost Centers → Produk → Pelanggan → Supplier → Karyawan.
2. THE Demo_Generator SHALL membuat minimal 1 warehouse aktif untuk setiap tenant demo.
3. THE Demo_Generator SHALL membuat minimal 5 akun Chart of Accounts (CoA) yang mencakup tipe: `asset`, `liability`, `equity`, `revenue`, `expense`.
4. THE Demo_Generator SHALL membuat minimal 1 periode akuntansi dengan status `open`.
5. THE Demo_Generator SHALL membuat minimal 3 karyawan dengan role berbeda (admin, manager, staff).
6. THE Demo_Generator SHALL membuat minimal 3 pelanggan aktif.
7. THE Demo_Generator SHALL membuat minimal 2 supplier aktif.
8. THE Demo_Generator SHALL membuat minimal 5 produk aktif dengan stok awal > 0.
9. IF proses generasi Core_Modules gagal pada satu entitas, THEN THE Demo_Generator SHALL mencatat warning ke log dan melanjutkan generasi entitas berikutnya tanpa menghentikan seluruh proses.
10. WHEN generasi Core_Modules selesai, THE Demo_Generator SHALL memperbarui Demo_Log dengan jumlah total record yang berhasil dibuat.

---

### Requirement 3: Generasi Demo Data Industri Manufacturing

**User Story:** Sebagai tenant baru di industri manufaktur, saya ingin demo data yang mencakup modul produksi, BOM, work order, dan quality control, sehingga saya dapat langsung mengeksplorasi alur produksi end-to-end.

#### Acceptance Criteria

1. WHEN `Demo_Generator::generateForIndustry('manufacturing', $tenantId, $userId)` dipanggil, THE Demo_Generator SHALL men-generate data spesifik manufaktur yang mencakup: produk bahan baku, produk jadi, Bill of Materials (BOM), work order, dan quality control records.
2. THE Demo_Generator SHALL membuat minimal 5 produk bahan baku dengan kategori `raw_material`.
3. THE Demo_Generator SHALL membuat minimal 3 produk jadi dengan kategori `finished_good`.
4. THE Demo_Generator SHALL membuat minimal 2 BOM yang menghubungkan produk jadi dengan bahan baku.
5. THE Demo_Generator SHALL membuat minimal 3 work order dengan status berbeda (`draft`, `in_progress`, `completed`).
6. THE Demo_Generator SHALL membuat minimal 2 quality control records yang terhubung dengan work order.
7. THE Demo_Generator SHALL membuat data purchase order untuk bahan baku dan sales order untuk produk jadi.

---

### Requirement 4: Generasi Demo Data Industri Retail

**User Story:** Sebagai tenant baru di industri retail, saya ingin demo data yang mencakup produk fashion/consumer goods, transaksi POS, dan program loyalitas, sehingga saya dapat langsung mengeksplorasi alur penjualan retail.

#### Acceptance Criteria

1. WHEN `Demo_Generator::generateForIndustry('retail', $tenantId, $userId)` dipanggil, THE Demo_Generator SHALL men-generate minimal 20 produk dengan kategori yang relevan untuk retail (fashion, elektronik, atau consumer goods).
2. THE Demo_Generator SHALL membuat minimal 10 pelanggan retail dengan data lengkap.
3. THE Demo_Generator SHALL membuat minimal 10 transaksi penjualan (sales order atau POS transaction) dengan status `completed`.
4. THE Demo_Generator SHALL membuat minimal 1 program loyalitas aktif.
5. THE Demo_Generator SHALL membuat minimal 1 price list dengan minimal 3 item.
6. THE Demo_Generator SHALL membuat data purchase order untuk pengisian stok produk.

---

### Requirement 5: Generasi Demo Data Industri Restaurant / F&B

**User Story:** Sebagai tenant baru di industri restoran, saya ingin demo data yang mencakup menu, meja, dan transaksi order, sehingga saya dapat langsung mengeksplorasi modul F&B dan POS.

#### Acceptance Criteria

1. WHEN `Demo_Generator::generateForIndustry('restaurant', $tenantId, $userId)` dipanggil, THE Demo_Generator SHALL men-generate minimal 15 menu item dengan kategori berbeda (makanan utama, minuman, dessert).
2. THE Demo_Generator SHALL membuat minimal 8 meja (table) dengan kapasitas dan status yang bervariasi.
3. THE Demo_Generator SHALL membuat minimal 10 transaksi order dengan status `completed`.
4. THE Demo_Generator SHALL membuat data inventory untuk bahan baku dapur.
5. THE Demo_Generator SHALL membuat minimal 3 karyawan dengan role: kasir, pelayan, koki.

---

### Requirement 6: Generasi Demo Data Industri Hotel

**User Story:** Sebagai tenant baru di industri perhotelan, saya ingin demo data yang mencakup tipe kamar, kamar, reservasi, dan tamu, sehingga saya dapat langsung mengeksplorasi modul hotel management.

#### Acceptance Criteria

1. WHEN `Demo_Generator::generateForIndustry('hotel', $tenantId, $userId)` dipanggil, THE Demo_Generator SHALL men-generate minimal 3 tipe kamar (Standard, Deluxe, Suite) dengan harga berbeda.
2. THE Demo_Generator SHALL membuat minimal 15 kamar yang terdistribusi di minimal 2 lantai.
3. THE Demo_Generator SHALL membuat minimal 10 reservasi dengan status berbeda (`confirmed`, `checked_in`, `checked_out`).
4. THE Demo_Generator SHALL membuat minimal 10 data tamu (guest) dengan informasi lengkap.
5. THE Demo_Generator SHALL membuat data housekeeping tasks untuk kamar yang ada.

---

### Requirement 7: Generasi Demo Data Industri Healthcare

**User Story:** Sebagai tenant baru di industri kesehatan, saya ingin demo data yang mencakup pasien, dokter, appointment, dan rekam medis, sehingga saya dapat langsung mengeksplorasi modul healthcare.

#### Acceptance Criteria

1. WHEN `Demo_Generator::generateForIndustry('healthcare', $tenantId, $userId)` dipanggil, THE Demo_Generator SHALL men-generate minimal 10 data pasien dengan informasi demografis.
2. THE Demo_Generator SHALL membuat minimal 3 data dokter dengan spesialisasi berbeda.
3. THE Demo_Generator SHALL membuat minimal 10 appointment dengan status berbeda (`scheduled`, `completed`, `cancelled`).
4. THE Demo_Generator SHALL membuat minimal 5 rekam medis (medical records) yang terhubung dengan pasien dan dokter.
5. THE Demo_Generator SHALL membuat data inventory untuk obat-obatan dan alat medis.

---

### Requirement 8: Generasi Demo Data Industri Services (Jasa)

**User Story:** Sebagai tenant baru di industri jasa, saya ingin demo data yang mencakup klien, proyek, timesheet, dan invoice, sehingga saya dapat langsung mengeksplorasi alur project-based billing.

#### Acceptance Criteria

1. WHEN `Demo_Generator::generateForIndustry('services', $tenantId, $userId)` dipanggil, THE Demo_Generator SHALL men-generate minimal 10 klien (customer) dengan tipe bisnis.
2. THE Demo_Generator SHALL membuat minimal 5 proyek dengan status berbeda (`planning`, `in_progress`, `completed`).
3. THE Demo_Generator SHALL membuat minimal 10 timesheet entries yang terhubung dengan proyek.
4. THE Demo_Generator SHALL membuat minimal 5 invoice yang terhubung dengan proyek dan klien.
5. THE Demo_Generator SHALL membuat data CRM leads untuk pipeline penjualan jasa.

---

### Requirement 9: Generasi Demo Data Industri Agriculture

**User Story:** Sebagai tenant baru di industri pertanian, saya ingin demo data yang mencakup lahan, siklus tanam, dan panen, sehingga saya dapat langsung mengeksplorasi modul agrikultur.

#### Acceptance Criteria

1. WHEN `Demo_Generator::generateForIndustry('agriculture', $tenantId, $userId)` dipanggil, THE Demo_Generator SHALL men-generate minimal 3 farm plot (lahan) dengan luas dan jenis tanaman berbeda.
2. THE Demo_Generator SHALL membuat minimal 3 crop cycle aktif dengan tahap pertumbuhan berbeda.
3. THE Demo_Generator SHALL membuat minimal 2 harvest log yang terhubung dengan crop cycle.
4. THE Demo_Generator SHALL membuat data inventory untuk pupuk, pestisida, dan alat pertanian.
5. THE Demo_Generator SHALL membuat minimal 3 karyawan lapangan.

---

### Requirement 10: Generasi Demo Data Industri Construction

**User Story:** Sebagai tenant baru di industri konstruksi, saya ingin demo data yang mencakup proyek konstruksi, RAB, dan material, sehingga saya dapat langsung mengeksplorasi modul project management konstruksi.

#### Acceptance Criteria

1. WHEN `Demo_Generator::generateForIndustry('construction', $tenantId, $userId)` dipanggil, THE Demo_Generator SHALL men-generate minimal 3 proyek konstruksi dengan status berbeda.
2. THE Demo_Generator SHALL membuat minimal 1 RAB (Rencana Anggaran Biaya) per proyek.
3. THE Demo_Generator SHALL membuat minimal 20 material/produk yang relevan untuk konstruksi (semen, besi, pasir, dll.).
4. THE Demo_Generator SHALL membuat minimal 5 purchase order untuk material konstruksi.
5. THE Demo_Generator SHALL membuat minimal 5 karyawan dengan role: mandor, tukang, pengawas.

---

### Requirement 11: Isolasi Data Demo antar Tenant

**User Story:** Sebagai operator sistem, saya ingin memastikan demo data yang di-generate untuk satu tenant tidak bercampur dengan data tenant lain, sehingga integritas data multi-tenant tetap terjaga.

#### Acceptance Criteria

1. THE Demo_Generator SHALL menyertakan `tenant_id` yang benar pada setiap record yang dibuat selama proses generasi demo data.
2. WHEN `Demo_Generator::generateForIndustry($industry, $tenantId, $userId)` dipanggil untuk dua tenant berbeda secara bersamaan, THE Demo_Generator SHALL menghasilkan data yang sepenuhnya terisolasi per tenant tanpa cross-contamination.
3. IF `tenant_id` tidak valid atau tidak ditemukan di database, THEN THE Demo_Generator SHALL menghentikan proses dan mengembalikan error dengan pesan deskriptif.
4. THE Demo_Generator SHALL memastikan semua operasi database dalam satu proses generasi dieksekusi dalam satu database transaction, sehingga jika terjadi kegagalan fatal, seluruh data yang sudah dibuat untuk tenant tersebut di-rollback.

---

### Requirement 12: Idempotency — Generasi Demo Data Tidak Duplikat

**User Story:** Sebagai tenant yang mencoba men-generate demo data lebih dari sekali (misalnya karena error atau refresh), saya ingin sistem tidak membuat data duplikat, sehingga database tetap bersih.

#### Acceptance Criteria

1. WHEN `Demo_Generator::generateForIndustry($industry, $tenantId, $userId)` dipanggil dua kali berturut-turut untuk tenant yang sama, THE Demo_Generator SHALL menghasilkan jumlah record yang sama seperti pemanggilan pertama (tidak ada duplikasi).
2. THE Demo_Generator SHALL menggunakan mekanisme `updateOrCreate` atau `insertOrIgnore` untuk semua entitas yang memiliki unique constraint (misalnya produk berdasarkan SKU, pelanggan berdasarkan email).
3. WHEN demo data sudah pernah di-generate untuk tenant (field `sample_data_generated = true` pada Industry_Profile), THE Demo_Generator SHALL mengembalikan response sukses tanpa membuat record baru.

---

### Requirement 13: Pelacakan Status dan Progress Generasi Demo Data

**User Story:** Sebagai tenant yang sedang menunggu proses generasi demo data, saya ingin melihat status progress secara real-time, sehingga saya tahu apakah proses sedang berjalan, selesai, atau gagal.

#### Acceptance Criteria

1. WHEN proses generasi demo data dimulai, THE Demo_Generator SHALL membuat Demo_Log dengan status `processing` dan timestamp `started_at`.
2. WHEN proses generasi demo data selesai dengan sukses, THE Demo_Generator SHALL memperbarui Demo_Log dengan status `completed`, `records_created` (jumlah total record), dan timestamp `completed_at`.
3. IF proses generasi demo data gagal karena exception yang tidak tertangani, THEN THE Demo_Generator SHALL memperbarui Demo_Log dengan status `failed` dan `error_message` yang deskriptif.
4. WHEN generasi demo data berhasil, THE Demo_Generator SHALL memperbarui field `sample_data_generated = true` pada Industry_Profile tenant yang bersangkutan.
5. THE Demo_Generator SHALL mengembalikan response JSON dengan struktur: `{ success: boolean, records_created: integer, generated_data: object, error?: string }`.

---

### Requirement 14: Performa Generasi Demo Data

**User Story:** Sebagai tenant baru yang menunggu demo data selesai di-generate, saya ingin proses selesai dalam waktu yang wajar, sehingga pengalaman onboarding tidak terasa lambat.

#### Acceptance Criteria

1. WHEN `Demo_Generator::generateForIndustry($industry, $tenantId, $userId)` dipanggil untuk industri apapun, THE Demo_Generator SHALL menyelesaikan proses generasi dalam waktu kurang dari 60 detik pada kondisi database normal.
2. THE Demo_Generator SHALL menggunakan bulk insert (`DB::table()->insert([...])`) untuk entitas yang memiliki banyak record (lebih dari 10 record sejenis), bukan insert satu per satu dalam loop.
3. THE Demo_Generator SHALL menghindari N+1 query dengan tidak melakukan query database di dalam loop yang sudah melakukan query.

---

### Requirement 15: Penanganan Error dan Partial Failure

**User Story:** Sebagai operator sistem, saya ingin proses generasi demo data yang robust terhadap error, sehingga kegagalan pada satu modul tidak menghentikan generasi data modul lainnya.

#### Acceptance Criteria

1. IF generasi data untuk satu modul spesifik industri gagal (misalnya model tidak ditemukan), THEN THE Demo_Generator SHALL mencatat error ke application log dengan konteks yang cukup (tenant_id, modul, pesan error) dan melanjutkan ke modul berikutnya.
2. THE Demo_Generator SHALL mengembalikan response sukses parsial jika Core_Modules berhasil di-generate meskipun beberapa Industry_Modules gagal, dengan menyertakan informasi modul mana yang gagal dalam field `generated_data`.
3. IF seluruh proses generasi gagal (Core_Modules tidak berhasil dibuat), THEN THE Demo_Generator SHALL mengembalikan `{ success: false, error: "..." }` dan memastikan tidak ada data parsial yang tersisa di database (rollback).
