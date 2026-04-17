# Registration Onboarding Save Industry Bug - Bugfix Design

## Overview

Bug ini memblokir seluruh alur onboarding baru. Saat user baru (registrasi manual maupun Google OAuth) memilih industri di wizard dan menekan "Next", request POST ke `/onboarding/save-industry` mengembalikan HTTP 500 karena kolom `skipped` ada di `$fillable` dan `$casts` model `OnboardingProfile` namun tidak ada di migration tabel `onboarding_profiles`. Server mengembalikan HTML error page, bukan JSON, sehingga Alpine.js melempar `SyntaxError: Unexpected token '<'`.

Selain itu, halaman `/onboarding/sample-data` selalu menampilkan "No templates available for your industry yet." karena tabel `sample_data_templates` tidak pernah di-seed. Dan route `save-industry` terduplikasi tiga kali di `routes/web.php`.

Strategi fix: (1) tambah kolom `skipped` ke migration via migration baru, (2) buat seeder `SampleDataTemplateSeeder` untuk semua 7 industri, (3) hapus dua baris duplikat route.

## Glossary

- **Bug_Condition (C)**: Kondisi yang memicu bug — `OnboardingProfile::updateOrCreate()` dieksekusi saat kolom `skipped` belum ada di tabel `onboarding_profiles`
- **Property (P)**: Perilaku yang diharapkan — `saveIndustry()` mengembalikan JSON `{"success": true}` dengan HTTP 200
- **Preservation**: Perilaku yang tidak boleh berubah — registrasi, login, validasi input, dan alur onboarding yang sudah selesai
- **OnboardingProfile**: Model di `app/Models/OnboardingProfile.php` yang menyimpan pilihan industri user; memiliki `skipped` di `$fillable` dan `$casts` tapi tidak di migration
- **SampleDataTemplate**: Model di `app/Models/SampleDataTemplate.php` yang menyimpan template data demo per industri; tabelnya kosong karena tidak ada seeder
- **saveIndustry()**: Method di `app/Http/Controllers/OnboardingController.php` yang menangani POST `/onboarding/save-industry`
- **SampleDataGeneratorService::getTemplates()**: Method yang query `sample_data_templates` — mengembalikan array kosong karena tabel tidak di-seed

## Bug Details

### Bug Condition

Bug terpicu ketika user baru mencoba menyimpan pilihan industri dan `OnboardingProfile::updateOrCreate()` dieksekusi. Eloquent mencoba menulis kolom `skipped` (karena ada di `$fillable`) ke tabel yang tidak memiliki kolom tersebut, menyebabkan SQL error → HTTP 500 → HTML response → JSON parse error di frontend.

**Formal Specification:**
```
FUNCTION isBugCondition(X)
  INPUT: X = { user: AuthUser, industry: string, business_size: string }
  OUTPUT: boolean

  RETURN X.user.tenant_id IS NOT NULL
    AND OnboardingProfile.columnExists('skipped') = FALSE
END FUNCTION
```

### Examples

- User baru registrasi manual → pilih "Retail" → klik "Next" → HTTP 500, Alpine.js: `SyntaxError: Unexpected token '<', "<!DOCTYPE "... is not valid JSON`
- User baru registrasi Google OAuth → pilih "Manufacturing" → klik "Next" → HTTP 500, error yang sama
- User yang sudah selesai onboarding (kolom `skipped` sudah ada) → tidak terdampak
- Request dengan industri tidak valid (misal "mining") → HTTP 422 validation error (tidak terdampak bug ini)

## Expected Behavior

### Preservation Requirements

**Unchanged Behaviors:**
- Registrasi manual dengan data valid harus tetap membuat tenant baru dan user admin dalam satu transaction atomik
- Registrasi via Google OAuth harus tetap membuat tenant baru dengan `email_verified_at` terisi otomatis
- User yang sudah menyelesaikan onboarding harus tetap diarahkan langsung ke dashboard
- `saveIndustry()` dengan industri tidak valid harus tetap mengembalikan HTTP 422
- Middleware `auth` pada route `/onboarding/*` harus tetap aktif
- `generateSampleData()` untuk industri yang didukung harus tetap menghasilkan data demo sesuai industri

**Scope:**
Semua input yang TIDAK melibatkan `OnboardingProfile::updateOrCreate()` dengan kolom `skipped` tidak terpengaruh fix ini. Ini mencakup:
- Semua route di luar prefix `onboarding`
- Proses registrasi itu sendiri (sebelum redirect ke wizard)
- Login dan autentikasi
- Semua modul ERP lainnya

## Hypothesized Root Cause

1. **Kolom `skipped` tidak ada di migration**: `OnboardingProfile` mendefinisikan `skipped` di `$fillable` dan `$casts` (sebagai `boolean`), tapi migration `2026_04_06_000012_create_onboarding_tables.php` tidak menyertakan kolom ini di tabel `onboarding_profiles`. Saat `skip()` atau `updateOrCreate()` dieksekusi, Eloquent mencoba INSERT/UPDATE kolom yang tidak ada → SQL error.

2. **Tabel `sample_data_templates` tidak di-seed**: Migration sudah membuat tabel dengan struktur yang benar, tapi tidak ada seeder yang mengisi data. `SampleDataGeneratorService::getTemplates($industry)` selalu mengembalikan array kosong, sehingga view menampilkan "No templates available for your industry yet." untuk semua industri.

3. **Duplikasi route `save-industry`**: Di `routes/web.php` baris 2893, route `POST /onboarding/save-industry` didefinisikan tiga kali berturut-turut. Laravel akan menggunakan definisi terakhir, tapi ini menyebabkan warning dan potensi konflik nama route.

## Correctness Properties

Property 1: Bug Condition - Save Industry Mengembalikan JSON Valid

_For any_ request POST ke `/onboarding/save-industry` dengan user terautentikasi dan payload industri valid, di mana kolom `skipped` telah ditambahkan ke tabel `onboarding_profiles`, fungsi `saveIndustry()` yang sudah diperbaiki SHALL mengembalikan HTTP 200 dengan JSON body `{"success": true, "next_step": "..."}` yang dapat di-parse oleh Alpine.js.

**Validates: Requirements 2.1, 2.2, 2.3**

Property 2: Preservation - Non-Buggy Input Behavior

_For any_ input yang TIDAK melibatkan eksekusi `OnboardingProfile::updateOrCreate()` dengan kolom `skipped` pada tabel yang belum memiliki kolom tersebut (misalnya: registrasi, login, validasi error, route lain), kode yang sudah diperbaiki SHALL menghasilkan perilaku yang identik dengan kode asli, mempertahankan semua fungsionalitas yang sudah berjalan.

**Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5, 3.6**

## Fix Implementation

### Changes Required

**File 1**: Buat migration baru `database/migrations/2026_04_06_000013_add_skipped_to_onboarding_profiles.php`

**Specific Changes**:
1. **Tambah kolom `skipped`**: `$table->boolean('skipped')->default(false)->after('completed_at');`
2. **Down method**: `$table->dropColumn('skipped');`

---

**File 2**: Buat seeder baru `database/seeders/SampleDataTemplateSeeder.php`

**Specific Changes**:
1. **Seed 7 industri**: retail, restaurant, hotel, construction, agriculture, manufacturing, services
2. **Setiap industri minimal 1 template aktif** dengan `is_active = true`
3. **Data config** berisi deskripsi modul yang akan di-populate
4. **Daftarkan di `DatabaseSeeder`**: tambah `SampleDataTemplateSeeder::class` ke array `$this->call()`

---

**File 3**: `routes/web.php`

**Specific Changes**:
1. **Hapus 2 baris duplikat** route `POST /save-industry` (baris 2893 duplikat ke-2 dan ke-3), sisakan hanya satu definisi

## Testing Strategy

### Validation Approach

Strategi testing mengikuti dua fase: pertama, surface counterexample yang mendemonstrasikan bug pada kode yang belum diperbaiki, kemudian verifikasi fix bekerja dengan benar dan tidak merusak perilaku yang sudah ada.

### Exploratory Bug Condition Checking

**Goal**: Surface counterexample yang mendemonstrasikan bug SEBELUM mengimplementasikan fix. Konfirmasi atau refutasi analisis root cause.

**Test Plan**: Tulis test yang mensimulasikan POST request ke `saveIndustry()` dengan user terautentikasi dan payload valid, lalu assert response adalah JSON valid dengan HTTP 200. Jalankan test ini pada kode yang BELUM diperbaiki untuk mengobservasi kegagalan.

**Test Cases**:
1. **Save Industry - Retail**: POST `/onboarding/save-industry` dengan `industry=retail, business_size=small` → akan gagal dengan HTTP 500 pada kode unfixed
2. **Save Industry - Manufacturing**: POST dengan `industry=manufacturing, business_size=medium` → akan gagal dengan HTTP 500 pada kode unfixed
3. **Skip Onboarding**: POST `/onboarding/skip` → akan gagal dengan HTTP 500 karena `skip()` juga menulis kolom `skipped`
4. **Get Templates - Empty**: `SampleDataGeneratorService::getTemplates('retail')` → mengembalikan array kosong pada kode unfixed

**Expected Counterexamples**:
- `saveIndustry()` mengembalikan HTTP 500 dengan HTML body alih-alih JSON
- SQL error: `SQLSTATE[42S22]: Column not found: 1054 Unknown column 'skipped' in 'field list'`
- `getTemplates()` mengembalikan `[]` untuk semua industri

### Fix Checking

**Goal**: Verifikasi bahwa untuk semua input di mana bug condition berlaku, fungsi yang sudah diperbaiki menghasilkan perilaku yang diharapkan.

**Pseudocode:**
```
FOR ALL X WHERE isBugCondition(X) DO
  result := saveIndustry_fixed(X)
  ASSERT result.status = 200
    AND result.body IS valid JSON
    AND result.body.success = TRUE
    AND result.body.next_step IS NOT NULL
END FOR
```

### Preservation Checking

**Goal**: Verifikasi bahwa untuk semua input di mana bug condition TIDAK berlaku, fungsi yang sudah diperbaiki menghasilkan hasil yang sama dengan fungsi asli.

**Pseudocode:**
```
FOR ALL X WHERE NOT isBugCondition(X) DO
  ASSERT saveIndustry_original(X) = saveIndustry_fixed(X)
END FOR
```

**Testing Approach**: Property-based testing direkomendasikan untuk preservation checking karena:
- Menghasilkan banyak test case secara otomatis di seluruh domain input
- Menangkap edge case yang mungkin terlewat oleh unit test manual
- Memberikan jaminan kuat bahwa perilaku tidak berubah untuk semua input non-buggy

**Test Plan**: Observasi perilaku pada kode UNFIXED untuk input non-bug (validasi error, route lain), kemudian tulis property-based test yang menangkap perilaku tersebut.

**Test Cases**:
1. **Validation Error Preservation**: POST dengan `industry=invalid_value` → harus tetap mengembalikan HTTP 422 setelah fix
2. **Auth Middleware Preservation**: POST tanpa autentikasi → harus tetap redirect ke login setelah fix
3. **Get Templates After Seed**: `getTemplates('retail')` → harus mengembalikan array non-empty setelah seeder dijalankan

### Unit Tests

- Test `saveIndustry()` dengan payload valid untuk setiap industri yang didukung (7 industri)
- Test `saveIndustry()` dengan industri tidak valid → assert HTTP 422
- Test `skip()` → assert redirect ke dashboard tanpa SQL error
- Test `SampleDataGeneratorService::getTemplates($industry)` setelah seeder → assert non-empty array untuk setiap industri

### Property-Based Tests

- Generate random valid industry values dari enum `[retail, restaurant, hotel, construction, agriculture, manufacturing, services]` dan verifikasi `saveIndustry()` selalu mengembalikan HTTP 200 dengan JSON valid
- Generate random invalid industry strings dan verifikasi selalu mengembalikan HTTP 422
- Verifikasi `getTemplates($industry)` mengembalikan array dengan `is_active = true` untuk semua industri valid

### Integration Tests

- Test full onboarding flow: registrasi → wizard → pilih industri → sample data page → tampilkan templates
- Test bahwa user yang sudah selesai onboarding tidak diarahkan ke wizard lagi
- Test route `/onboarding/save-industry` hanya terdaftar sekali (tidak duplikat) dengan `php artisan route:list`
