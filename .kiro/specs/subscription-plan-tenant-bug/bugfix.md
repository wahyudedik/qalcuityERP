# Bugfix Requirements Document

## Introduction

Sistem subscription plan pada aplikasi Laravel ERP memiliki beberapa bug kritis yang menyebabkan tenant baru tidak dapat memilih paket langganan, data paket tidak tersedia secara otomatis, dan inkonsistensi dalam pengecekan plan. Bug ini berdampak pada user experience saat registrasi, halaman subscription yang kosong, dan potensi data integrity issues karena tidak adanya foreign key constraint.

## Bug Analysis

### Current Behavior (Defect)

1.1 WHEN aplikasi di-setup dengan `php artisan migrate:fresh --seed` THEN tabel `subscription_plans` tetap kosong karena `DatabaseSeeder` tidak memanggil seeder untuk subscription plans

1.2 WHEN tenant baru dibuat melalui `RegisteredUserController` atau `GoogleController` THEN tenant hanya memiliki field `plan => 'trial'` tanpa `subscription_plan_id`, menyebabkan relasi `subscriptionPlan` null

1.3 WHEN user mengakses halaman `/subscription` THEN halaman menampilkan list kosong karena tidak ada data di tabel `subscription_plans`

1.4 WHEN admin menambahkan kolom `subscription_plan_id` ke tabel `tenants` THEN tidak ada foreign key constraint yang memastikan referential integrity dengan tabel `subscription_plans`

1.5 WHEN kode mengecek plan tenant di `ConsolidationController` THEN menggunakan string comparison `$tenant->plan !== 'enterprise'` yang tidak konsisten dengan method `maxUsers()` dan `maxAiMessages()` yang mengecek `subscriptionPlan` relation terlebih dahulu

### Expected Behavior (Correct)

2.1 WHEN aplikasi di-setup dengan `php artisan migrate:fresh --seed` THEN tabel `subscription_plans` SHALL terisi otomatis dengan 4 paket default (Starter, Business, Professional, Enterprise) dari `SubscriptionPlan::defaultPlans()`

2.2 WHEN tenant baru dibuat melalui `RegisteredUserController` atau `GoogleController` THEN tenant SHALL memiliki `subscription_plan_id` yang merujuk ke paket trial/starter yang sesuai, bukan hanya string `plan => 'trial'`

2.3 WHEN user mengakses halaman `/subscription` THEN halaman SHALL menampilkan list paket yang tersedia dari tabel `subscription_plans`

2.4 WHEN migration menambahkan kolom `subscription_plan_id` ke tabel `tenants` THEN migration SHALL menambahkan foreign key constraint `->constrained('subscription_plans')->onDelete('set null')` untuk memastikan referential integrity

2.5 WHEN kode mengecek plan tenant di seluruh aplikasi THEN SHALL menggunakan method konsisten yang mengecek `subscriptionPlan` relation terlebih dahulu, dengan fallback ke field `plan` string untuk backward compatibility

### Unchanged Behavior (Regression Prevention)

3.1 WHEN tenant existing dengan field `plan` string (trial/starter/business/professional/enterprise) tanpa `subscription_plan_id` THEN method `maxUsers()` dan `maxAiMessages()` SHALL CONTINUE TO berfungsi dengan fallback ke `match($this->plan)` untuk backward compatibility

3.2 WHEN command `php artisan plans:sync` dijalankan manual THEN command SHALL CONTINUE TO berfungsi untuk sync/update subscription plans dari `defaultPlans()` ke database

3.3 WHEN tenant memiliki `subscription_plan_id` yang valid THEN method `maxUsers()` dan `maxAiMessages()` SHALL CONTINUE TO menggunakan nilai dari `subscriptionPlan` relation sebagai prioritas utama

3.4 WHEN `SuperAdminSeeder` dan `SampleDataTemplateSeeder` dijalankan THEN seeder-seeder tersebut SHALL CONTINUE TO berfungsi tanpa perubahan behavior

3.5 WHEN tenant dibuat dengan `plan => 'trial'` dan `trial_ends_at` THEN logic trial expiry di method `isTrialExpired()`, `isPlanExpired()`, dan `canAccess()` SHALL CONTINUE TO berfungsi dengan benar
