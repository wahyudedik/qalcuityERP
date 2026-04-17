# Bugfix Requirements Document

## Introduction

Setelah registrasi (manual maupun Google/Gmail OAuth), user diarahkan ke onboarding wizard. Pada step pertama (Select Industry), saat user memilih industri dan menekan "Next", request POST ke `/onboarding/save-industry` mengembalikan HTTP 500. Karena server mengembalikan HTML error page alih-alih JSON, frontend Alpine.js melempar `SyntaxError: Unexpected token '<', "<!DOCTYPE "... is not valid JSON`. Akibatnya user tidak bisa melanjutkan onboarding sama sekali.

Selain itu, halaman `/onboarding/sample-data` selalu menampilkan "No templates available for your industry yet." untuk semua industri termasuk "Manufacturing", karena tabel `sample_data_templates` tidak pernah di-seed.

Bug ini terjadi pada kedua jalur registrasi (manual dan Google OAuth) dan memblokir seluruh alur onboarding baru.

---

## Bug Analysis

### Current Behavior (Defect)

1.1 WHEN user baru (registrasi manual atau Google OAuth) memilih industri di wizard onboarding dan menekan "Next" THEN sistem mengembalikan HTTP 500 dan HTML error page alih-alih JSON response

1.2 WHEN `OnboardingProfile::updateOrCreate()` dieksekusi di `saveIndustry()` THEN sistem melempar SQL error karena kolom `skipped` ada di `$fillable` model `OnboardingProfile` namun tidak ada di skema tabel `onboarding_profiles` di migration

1.3 WHEN frontend Alpine.js menerima response dari `/onboarding/save-industry` THEN sistem melempar `SyntaxError: Unexpected token '<', "<!DOCTYPE "...` karena mencoba mem-parse HTML error page sebagai JSON

1.4 WHEN user berhasil melewati step industry selection dan membuka halaman `/onboarding/sample-data` THEN sistem menampilkan "No templates available for your industry yet." untuk semua industri karena tabel `sample_data_templates` kosong (tidak ada seeder)

1.5 WHEN `SampleDataGeneratorService::getTemplates($industry)` dipanggil dengan industri apapun THEN sistem mengembalikan array kosong karena tidak ada data di tabel `sample_data_templates`

### Expected Behavior (Correct)

2.1 WHEN user baru memilih industri di wizard onboarding dan menekan "Next" THEN sistem SHALL menyimpan pilihan industri ke `onboarding_profiles` dan mengembalikan JSON `{"success": true, "next_step": "..."}` dengan HTTP 200

2.2 WHEN `OnboardingProfile::updateOrCreate()` dieksekusi THEN sistem SHALL berhasil menyimpan data tanpa SQL error karena kolom `skipped` telah ditambahkan ke migration atau dihapus dari `$fillable`

2.3 WHEN frontend Alpine.js menerima response dari `/onboarding/save-industry` THEN sistem SHALL mem-parse JSON dengan benar dan mengarahkan user ke halaman `/onboarding/sample-data`

2.4 WHEN user membuka halaman `/onboarding/sample-data` dengan industri yang valid THEN sistem SHALL menampilkan minimal satu template yang tersedia untuk industri tersebut

2.5 WHEN `SampleDataGeneratorService::getTemplates($industry)` dipanggil dengan industri yang valid (retail, restaurant, hotel, construction, agriculture, manufacturing, services) THEN sistem SHALL mengembalikan array berisi minimal satu template aktif

### Unchanged Behavior (Regression Prevention)

3.1 WHEN user yang sudah menyelesaikan onboarding login kembali THEN sistem SHALL CONTINUE TO mengarahkan user langsung ke dashboard tanpa melewati wizard onboarding

3.2 WHEN user registrasi manual dengan data valid THEN sistem SHALL CONTINUE TO membuat tenant baru dan user admin dalam satu transaction atomik

3.3 WHEN user registrasi via Google OAuth THEN sistem SHALL CONTINUE TO membuat tenant baru dan user admin dengan `email_verified_at` terisi otomatis

3.4 WHEN `saveIndustry()` menerima industri yang tidak valid (di luar enum yang diizinkan) THEN sistem SHALL CONTINUE TO mengembalikan validation error 422

3.5 WHEN user membuka `/onboarding/wizard` tanpa autentikasi THEN sistem SHALL CONTINUE TO mengarahkan ke halaman login (middleware `auth` tetap aktif)

3.6 WHEN `generateSampleData()` dipanggil untuk industri yang didukung (retail, restaurant, hotel, construction, agriculture) THEN sistem SHALL CONTINUE TO menghasilkan data demo sesuai industri masing-masing

---

## Bug Condition Pseudocode

```pascal
FUNCTION isBugCondition(X)
  INPUT: X = { user: AuthUser, industry: string, business_size: string }
  OUTPUT: boolean

  // Bug terpicu ketika user baru mencoba menyimpan pilihan industri
  // dan kolom 'skipped' belum ada di tabel onboarding_profiles
  RETURN X.user.tenant_id IS NOT NULL
    AND OnboardingProfile.columnExists('skipped') = FALSE
END FUNCTION

// Property: Fix Checking
FOR ALL X WHERE isBugCondition(X) DO
  result ← saveIndustry'(X)
  ASSERT result.status = 200
    AND result.body.success = TRUE
    AND result.body IS valid JSON
END FOR

// Property: Preservation Checking
FOR ALL X WHERE NOT isBugCondition(X) DO
  ASSERT saveIndustry(X) = saveIndustry'(X)
END FOR
```
