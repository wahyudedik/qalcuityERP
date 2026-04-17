# Bugfix Requirements Document

## Introduction

Beberapa test di `tests/Feature/DemoData/SampleDataGeneratorPropertyTest.php` di-skip atau gagal karena tabel/kolom yang dibutuhkan tidak ada di database test. Contoh konkret dari output test:

```
- property 11 industry data completeness → doctors table does not exist in test DB — skipping healthcare minimums check
```

Root cause: migrasi untuk beberapa tabel healthcare (dan kemungkinan modul lain) tidak mendefinisikan kolom `tenant_id`, padahal generator service (`HealthcareGenerator`, dll.) mencoba melakukan insert dan query menggunakan kolom tersebut. Akibatnya migrasi gagal dijalankan atau tabel tidak bisa digunakan, sehingga test memanggil `markTestSkipped()`.

Scope audit mencakup seluruh modul: healthcare, hotel, restaurant, manufacturing, retail, services, agriculture, construction, dan modul pendukung lainnya.

---

## Bug Analysis

### Current Behavior (Defect)

1.1 WHEN migration `create_doctors_table` dijalankan THEN tabel `doctors` dibuat tanpa kolom `tenant_id`, sehingga `HealthcareGenerator::seedDoctors()` gagal saat mencoba insert/query dengan `tenant_id`.

1.2 WHEN migration `create_patients_table` dijalankan THEN tabel `patients` dibuat tanpa kolom `tenant_id`, sehingga `HealthcareGenerator::seedPatients()` gagal saat mencoba insert/query dengan `tenant_id`.

1.3 WHEN test `test_property_11_industry_data_completeness` dijalankan dengan industry `healthcare` THEN test di-skip dengan pesan "doctors table does not exist in test DB" karena `Schema::hasTable('doctors')` mengembalikan false atau tabel tidak dapat digunakan.

1.4 WHEN `SampleDataGeneratorService::generateForIndustry('healthcare', ...)` dipanggil THEN industry generator gagal karena kolom `tenant_id` tidak ada di tabel `doctors` dan `patients`, menyebabkan exception yang di-catch sebagai non-fatal warning.

1.5 WHEN audit dilakukan pada seluruh migrasi modul THEN ditemukan tabel-tabel lain yang direferensikan oleh generator service tetapi memiliki kolom yang tidak lengkap atau tidak konsisten dengan yang diharapkan generator.

### Expected Behavior (Correct)

2.1 WHEN migration `create_doctors_table` dijalankan THEN tabel `doctors` SHALL memiliki kolom `tenant_id` sebagai foreign key ke tabel `tenants` dengan cascade delete, sehingga `HealthcareGenerator` dapat melakukan insert dan query berdasarkan `tenant_id`.

2.2 WHEN migration `create_patients_table` dijalankan THEN tabel `patients` SHALL memiliki kolom `tenant_id` sebagai foreign key ke tabel `tenants` dengan cascade delete, sehingga `HealthcareGenerator` dapat melakukan insert dan query berdasarkan `tenant_id`.

2.3 WHEN test `test_property_11_industry_data_completeness` dijalankan dengan industry `healthcare` THEN test SHALL berjalan tanpa skip, memverifikasi bahwa ≥3 doctors dan ≥10 appointments tersedia untuk tenant yang bersangkutan.

2.4 WHEN `SampleDataGeneratorService::generateForIndustry('healthcare', ...)` dipanggil THEN sistem SHALL berhasil membuat data healthcare (doctors, patients, appointments) dengan `tenant_id` yang benar.

2.5 WHEN audit seluruh migrasi selesai dilakukan THEN semua tabel yang direferensikan oleh generator service SHALL memiliki kolom yang lengkap dan konsisten dengan yang diharapkan oleh masing-masing generator.

2.6 WHEN `php artisan migrate --env=testing` dijalankan THEN semua migrasi SHALL berhasil dijalankan tanpa error.

### Unchanged Behavior (Regression Prevention)

3.1 WHEN test dijalankan untuk industry selain `healthcare` (retail, manufacturing, hotel, restaurant, services, agriculture, construction) THEN sistem SHALL CONTINUE TO menjalankan test tersebut tanpa skip dan menghasilkan data yang sesuai minimum requirements.

3.2 WHEN `SampleDataGeneratorService::generateForIndustry()` dipanggil untuk industry yang sudah berfungsi THEN sistem SHALL CONTINUE TO menghasilkan data dengan jumlah record yang sama seperti sebelumnya (idempotency terjaga).

3.3 WHEN migrasi yang sudah ada dan berfungsi dijalankan ulang THEN sistem SHALL CONTINUE TO membuat tabel dengan struktur yang sama seperti sebelumnya tanpa perubahan yang merusak.

3.4 WHEN data tenant lain sudah ada di database THEN penambahan kolom `tenant_id` pada tabel `doctors` dan `patients` SHALL CONTINUE TO menjaga isolasi data antar tenant (tidak ada cross-contamination).

3.5 WHEN `Schema::hasTable('doctors')` dipanggil setelah fix diterapkan THEN sistem SHALL CONTINUE TO mengembalikan `true` (tabel tetap ada, hanya strukturnya yang diperbaiki).

3.6 WHEN test property 1–10 dijalankan THEN sistem SHALL CONTINUE TO lulus semua test tersebut tanpa regresi.
