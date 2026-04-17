# Tasks

## Task List

- [x] 1. Setup infrastruktur base classes untuk industry generators
  - [x] 1.1 Buat `app/Services/DemoData/CoreDataContext.php` (value object untuk membawa IDs hasil core generation)
  - [x] 1.2 Buat `app/Services/DemoData/BaseIndustryGenerator.php` (abstract class dengan method `generate()`, `bulkInsert()`, `logWarning()`)
  - [x] 1.3 Buat `app/Services/DemoData/CoreModulesGenerator.php` dengan method untuk generate CoA, periode, warehouse, tax rates, cost centers, produk, pelanggan, supplier, karyawan — dalam urutan dependensi yang benar
  - [x] 1.4 Tambahkan `SampleDataTemplateSeeder` untuk industri `healthcare` yang belum ada di seeder

- [x] 2. Refactor SampleDataGeneratorService sebagai orchestrator
  - [x] 2.1 Tambahkan method `validateTenant(int $tenantId): void` yang throw exception jika tenant tidak ditemukan
  - [x] 2.2 Tambahkan method `isAlreadyGenerated(int $tenantId, int $userId): bool` yang cek `OnboardingProfile.sample_data_generated`
  - [x] 2.3 Tambahkan method `resolveGenerator(string $industry): BaseIndustryGenerator` yang return generator yang tepat per industri
  - [x] 2.4 Refactor `generateForIndustry()` agar: (1) validasi tenant, (2) cek idempotency, (3) buat Demo_Log, (4) wrap dalam DB transaction, (5) panggil CoreModulesGenerator, (6) panggil IndustryGenerator, (7) update Demo_Log dan OnboardingProfile
  - [x] 2.5 Pastikan kegagalan Core_Modules men-trigger rollback dan return `{success: false}`
  - [x] 2.6 Pastikan kegagalan Industry_Modules hanya log warning dan lanjut, dengan info `failed_modules` di response

- [x] 3. Implementasi industry generators
  - [x] 3.1 Buat `app/Services/DemoData/Generators/RetailGenerator.php` — 20 produk fashion/consumer goods, 10 pelanggan, 10 transaksi sales, 1 loyalty program, 1 price list, purchase orders
  - [x] 3.2 Buat `app/Services/DemoData/Generators/RestaurantGenerator.php` — 15 menu item (3 kategori), 8 meja, 10 order completed, inventory bahan baku, 3 karyawan (kasir/pelayan/koki)
  - [x] 3.3 Buat `app/Services/DemoData/Generators/HotelGenerator.php` — 3 tipe kamar, 15 kamar (2+ lantai), 10 reservasi (3 status), 10 tamu, housekeeping tasks
  - [x] 3.4 Buat `app/Services/DemoData/Generators/ManufacturingGenerator.php` — 5 raw material, 3 finished good, 2 BOM, 3 work order (3 status), 2 QC records, PO bahan baku + SO produk jadi
  - [x] 3.5 Buat `app/Services/DemoData/Generators/HealthcareGenerator.php` — 10 pasien, 3 dokter (spesialisasi berbeda), 10 appointment (3 status), 5 rekam medis, inventory obat
  - [x] 3.6 Buat `app/Services/DemoData/Generators/ServicesGenerator.php` — 10 klien, 5 proyek (3 status), 10 timesheet entries, 5 invoice, CRM leads
  - [x] 3.7 Buat `app/Services/DemoData/Generators/AgricultureGenerator.php` — 3 farm plot, 3 crop cycle (tahap berbeda), 2 harvest log, inventory pupuk/pestisida, 3 karyawan lapangan
  - [x] 3.8 Buat `app/Services/DemoData/Generators/ConstructionGenerator.php` — 3 proyek (status berbeda), 1 RAB per proyek, 20 material konstruksi, 5 PO material, 5 karyawan (mandor/tukang/pengawas)

- [x] 4. Property-based tests
  - [x] 4.1 [PBT] Tulis property test untuk Property 1: Template tersedia untuk semua industri valid — `for any` industri dalam daftar yang didukung, `getTemplates()` mengembalikan non-empty array
  - [x] 4.2 [PBT] Tulis property test untuk Property 2: Template tidak tersedia untuk industri tidak valid — `for any` string bukan industri valid, `getTemplates()` mengembalikan array kosong
  - [x] 4.3 [PBT] Tulis property test untuk Property 3: Template memiliki semua field wajib — `for any` template yang dikembalikan, semua field wajib harus ada
  - [x] 4.4 [PBT] Tulis property test untuk Property 4: Core data completeness — `for any` industri valid, setelah generate ada minimal 1 warehouse, 5 CoA (semua tipe), 1 periode open, 3 karyawan, 3 pelanggan, 2 supplier, 5 produk dengan stok > 0
  - [x] 4.5 [PBT] Tulis property test untuk Property 5: Semua record memiliki tenant_id yang benar — `for any` tenant ID, semua record yang dibuat memiliki tenant_id yang sesuai
  - [x] 4.6 [PBT] Tulis property test untuk Property 6: Isolasi data antar tenant — `for any` dua tenant berbeda, data tidak overlap
  - [x] 4.7 [PBT] Tulis property test untuk Property 7: Idempotency — `for any` industri dan tenant, generate dua kali menghasilkan jumlah record yang sama
  - [x] 4.8 [PBT] Tulis property test untuk Property 8: Demo_Log mencerminkan hasil generate — `for any` generate yang berhasil, Demo_Log memiliki status completed dan records_created > 0
  - [x] 4.9 [PBT] Tulis property test untuk Property 9: OnboardingProfile diperbarui — `for any` generate yang berhasil, sample_data_generated = true
  - [x] 4.10 [PBT] Tulis property test untuk Property 10: Response JSON memiliki struktur yang benar — `for any` pemanggilan generate, response selalu memiliki field yang diperlukan
  - [x] 4.11 [PBT] Tulis property test untuk Property 11: Industry data completeness — `for any` industri valid, jumlah record spesifik industri memenuhi minimum requirements

- [x] 5. Unit tests untuk error handling dan edge cases
  - [x] 5.1 Tulis unit test: tenant tidak valid mengembalikan `{success: false}` dengan pesan error deskriptif
  - [x] 5.2 Tulis unit test: kegagalan satu modul industri tidak menghentikan modul lain (inject exception pada satu generator)
  - [x] 5.3 Tulis unit test: kegagalan Core_Modules men-trigger rollback (tidak ada data parsial di DB)
  - [x] 5.4 Tulis unit test: partial failure mengembalikan `success: true` dengan `failed_modules` terisi
  - [x] 5.5 Tulis unit test: Demo_Log dibuat dengan status `processing` saat generate dimulai
  - [x] 5.6 Tulis unit test: Demo_Log diperbarui ke `failed` dengan `error_message` saat exception tidak tertangani
  - [x] 5.7 Tulis integration test: proses generate selesai dalam < 60 detik untuk semua industri
  - [x] 5.8 Tulis HTTP test: halaman `onboarding.sample-data` menampilkan template (bukan pesan "No templates available")
