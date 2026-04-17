# Module Toggle Tenant Bug — Bugfix Design

## Overview

Dua bug ditemukan pada alur onboarding yang menyebabkan pilihan modul user tidak pernah diterapkan ke tenant:

**Bug 1** — `OnboardingController::complete()` menyimpan `selected_modules` ke `OnboardingProfile` tetapi tidak ke `tenants.enabled_modules`. Akibatnya, setelah onboarding selesai, semua modul tetap aktif karena `enabled_modules = null`.

**Bug 2** — `OnboardingController::saveIndustry()` dan `complete()` menerima industry key seperti `'restaurant'`, `'manufacturing'`, `'services'`, sedangkan `ModuleRecommendationService::recommend()` menggunakan key berbeda: `'fnb'`, `'manufacture'`, `'service'`. Pemetaan tidak ada, sehingga rekomendasi modul berbasis industri selalu jatuh ke `default`.

Strategi fix: tambahkan satu langkah di `complete()` untuk menyimpan modul ke tenant, dan tambahkan mapping industri sebelum memanggil `recommend()`.

---

## Glossary

- **Bug_Condition (C)**: Kondisi yang memicu bug — saat `complete()` dipanggil dengan `selected_modules` dan/atau `industry` yang membutuhkan pemetaan
- **Property (P)**: Perilaku yang diharapkan — `tenants.enabled_modules` terisi dengan modul yang benar setelah onboarding selesai
- **Preservation**: Perilaku yang tidak boleh berubah — toggle modul via Settings, backward compat `null`, `isModuleEnabled()`, skip onboarding, dan endpoint AJAX recommend
- **OnboardingController::complete()**: Method di `app/Http/Controllers/OnboardingController.php` yang memproses penyelesaian onboarding
- **ModuleRecommendationService::recommend()**: Method di `app/Services/ModuleRecommendationService.php` yang mengembalikan daftar modul berdasarkan industry key
- **enabled_modules**: Kolom JSON di tabel `tenants`; jika `null` berarti semua modul aktif (backward compat)
- **selected_modules**: Kolom JSON di tabel `onboarding_profiles`; menyimpan pilihan modul user saat onboarding

---

## Bug Details

### Bug Condition

Bug termanifestasi dalam dua skenario yang saling berkaitan:

1. Saat `complete()` dipanggil dengan `selected_modules` yang tidak kosong, data tersebut hanya disimpan ke `OnboardingProfile` dan tidak diteruskan ke `Tenant`.
2. Saat `complete()` atau `saveIndustry()` dipanggil dengan industry key dari onboarding form (`restaurant`, `manufacturing`, `services`), key tersebut tidak dipetakan ke key yang dikenali `ModuleRecommendationService` (`fnb`, `manufacture`, `service`).

**Formal Specification:**

```
FUNCTION isBugCondition(input)
  INPUT: input berupa HTTP request ke complete() atau saveIndustry()
  OUTPUT: boolean

  // Bug 1: selected_modules tidak disimpan ke tenant
  bug1 := input.selected_modules IS NOT EMPTY
          AND tenant.enabled_modules REMAINS NULL after complete()

  // Bug 2: industry key mismatch
  INDUSTRY_MISMATCH_MAP := {
    'restaurant'    -> 'fnb',
    'manufacturing' -> 'manufacture',
    'services'      -> 'service'
  }
  bug2 := input.industry IN KEYS(INDUSTRY_MISMATCH_MAP)
          AND recommend(input.industry) RETURNS default (bukan rekomendasi industri)

  RETURN bug1 OR bug2
END FUNCTION
```

### Examples

- User memilih modul `['pos', 'inventory', 'hrm']` saat onboarding → setelah `complete()`, `tenant.enabled_modules` tetap `null` (semua modul aktif) — **Bug 1**
- User memilih industri `'restaurant'` → `recommend('restaurant')` mengembalikan modul default, bukan modul F&B — **Bug 2**
- User memilih industri `'manufacturing'` → `recommend('manufacturing')` mengembalikan modul default, bukan modul manufaktur — **Bug 2**
- User memilih industri `'services'` → `recommend('services')` mengembalikan modul default, bukan modul jasa — **Bug 2**
- User memilih industri `'retail'` → tidak terdampak Bug 2 karena key sudah cocok; tapi Bug 1 tetap berlaku jika `selected_modules` diisi

---

## Expected Behavior

### Preservation Requirements

**Unchanged Behaviors:**
- Admin tenant mengubah modul via halaman Settings > Modul harus tetap menyimpan ke `tenants.enabled_modules` dengan benar
- Tenant dengan `enabled_modules = null` (tenant lama) harus tetap dianggap semua modul aktif
- `isModuleEnabled()` harus tetap mengembalikan `true` hanya untuk modul yang ada dalam array `enabled_modules`
- User yang skip onboarding tidak boleh mengubah `tenants.enabled_modules` (tetap `null`)
- Endpoint AJAX `/settings/modules/recommend` yang memanggil `recommend()` langsung harus tetap menerima key `fnb`, `retail`, `manufacture`, dll tanpa perubahan

**Scope:**
Semua input yang TIDAK melalui `OnboardingController::complete()` tidak boleh terpengaruh oleh fix ini. Ini mencakup:
- Toggle modul via Settings controller
- Pemanggilan `ModuleRecommendationService::recommend()` dari luar onboarding
- `OnboardingController::skip()` — tidak boleh menyentuh `enabled_modules`

---

## Hypothesized Root Cause

1. **Missing write ke Tenant di complete()**: `complete()` hanya memanggil `OnboardingProfile::updateOrCreate()` dan tidak ada satu baris pun yang menyentuh model `Tenant`. Developer mungkin berasumsi ada observer atau event yang meneruskan data, tapi tidak ada.

2. **Tidak ada industry key mapping**: Onboarding form menggunakan label yang lebih deskriptif (`restaurant`, `manufacturing`, `services`) sedangkan `ModuleRecommendationService` menggunakan key yang lebih singkat (`fnb`, `manufacture`, `service`). Tidak ada layer translasi di antara keduanya — kemungkinan dua bagian ini dikembangkan secara terpisah tanpa koordinasi.

3. **Tidak ada fallback atau warning**: Karena `recommend()` menggunakan `match` dengan `default`, mismatch key tidak menghasilkan error — hanya mengembalikan modul generik secara diam-diam, sehingga bug tidak terdeteksi saat development.

---

## Correctness Properties

Property 1: Bug Condition — Modul Onboarding Tersimpan ke Tenant

_For any_ request ke `complete()` di mana `selected_modules` tidak kosong, fungsi yang sudah diperbaiki SHALL menyimpan array `selected_modules` tersebut ke `tenants.enabled_modules` sehingga hanya modul yang dipilih yang aktif untuk tenant tersebut.

**Validates: Requirements 2.1**

Property 2: Bug Condition — Industry Key Mapping Menghasilkan Rekomendasi yang Tepat

_For any_ request ke `complete()` atau `saveIndustry()` di mana `industry` adalah `'restaurant'`, `'manufacturing'`, atau `'services'`, fungsi yang sudah diperbaiki SHALL memetakan key tersebut ke `'fnb'`, `'manufacture'`, atau `'service'` sebelum memanggil `recommend()`, sehingga rekomendasi modul yang dikembalikan sesuai dengan industri yang dipilih.

**Validates: Requirements 2.2, 2.3, 2.4, 2.5**

Property 3: Preservation — Perilaku di Luar Onboarding Tidak Berubah

_For any_ input yang TIDAK melalui `OnboardingController::complete()` (termasuk Settings toggle, skip onboarding, dan pemanggilan langsung `recommend()` dengan key yang sudah benar), fungsi yang sudah diperbaiki SHALL menghasilkan perilaku yang identik dengan kode asli.

**Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5**

---

## Fix Implementation

### Changes Required

**File**: `app/Http/Controllers/OnboardingController.php`

**Function**: `complete()`

**Specific Changes**:

1. **Resolve modules to apply**: Setelah `OnboardingProfile::updateOrCreate()`, tentukan modul yang akan diterapkan:
   - Jika `selected_modules` tidak kosong → gunakan `selected_modules`
   - Jika `selected_modules` kosong → panggil `ModuleRecommendationService::recommend()` dengan industry key yang sudah dipetakan, ambil `['modules']`

2. **Industry key mapping**: Tambahkan mapping sebelum memanggil `recommend()`:
   ```php
   $industryMap = [
       'restaurant'    => 'fnb',
       'manufacturing' => 'manufacture',
       'services'      => 'service',
   ];
   $recommendKey = $industryMap[$request->industry] ?? $request->industry;
   ```

3. **Write ke Tenant**: Setelah menentukan modul, simpan ke tenant:
   ```php
   $tenant = Tenant::find($tenantId);
   $tenant->update(['enabled_modules' => $modulesToApply]);
   ```

4. **Tandai onboarding_completed**: Pastikan `tenants.onboarding_completed` juga di-set `true` (sudah ada atau perlu ditambahkan).

**Catatan**: `ModuleRecommendationService` tidak perlu diubah — fix dilakukan sepenuhnya di layer controller dengan mapping key, sehingga endpoint AJAX yang memanggil `recommend()` langsung tidak terpengaruh (Requirement 3.5).

---

## Testing Strategy

### Validation Approach

Strategi dua fase: pertama jalankan tes pada kode yang belum diperbaiki untuk membuktikan bug ada (exploratory), lalu verifikasi fix bekerja dan tidak merusak perilaku yang sudah ada (fix + preservation checking).

### Exploratory Bug Condition Checking

**Goal**: Buktikan bug ada pada kode unfixed. Konfirmasi root cause analysis.

**Test Plan**: Panggil `complete()` dengan berbagai kombinasi `industry` dan `selected_modules`, lalu periksa state `tenant.enabled_modules` dan output `recommend()`.

**Test Cases**:
1. **Bug 1 — selected_modules tidak tersimpan**: Panggil `complete()` dengan `selected_modules = ['pos', 'hrm']`, periksa `tenant.enabled_modules` → akan tetap `null` pada kode unfixed
2. **Bug 2 — restaurant → default**: Panggil `recommend('restaurant')` → akan mengembalikan modul default, bukan F&B
3. **Bug 2 — manufacturing → default**: Panggil `recommend('manufacturing')` → akan mengembalikan modul default, bukan manufaktur
4. **Bug 2 — services → default**: Panggil `recommend('services')` → akan mengembalikan modul default, bukan jasa
5. **Edge case — industry valid tanpa mismatch**: Panggil `complete()` dengan `industry = 'retail'` → Bug 2 tidak terjadi, tapi Bug 1 tetap ada

**Expected Counterexamples**:
- `tenant.enabled_modules` tetap `null` setelah `complete()` meskipun `selected_modules` diisi
- `recommend('restaurant')` mengembalikan `['pos', 'inventory', 'purchasing', 'sales', 'invoicing', 'accounting', 'hrm', 'reports']` (default), bukan modul F&B

### Fix Checking

**Goal**: Verifikasi bahwa untuk semua input di mana bug condition berlaku, fungsi yang sudah diperbaiki menghasilkan perilaku yang benar.

**Pseudocode:**
```
FOR ALL input WHERE isBugCondition(input) DO
  result := complete_fixed(input)
  
  // Bug 1 check
  IF input.selected_modules IS NOT EMPTY THEN
    ASSERT tenant.enabled_modules = input.selected_modules
  END IF
  
  // Bug 2 check
  IF input.industry IN ['restaurant', 'manufacturing', 'services'] THEN
    mappedKey := INDUSTRY_MAP[input.industry]
    ASSERT tenant.enabled_modules = recommend(mappedKey).modules
  END IF
END FOR
```

### Preservation Checking

**Goal**: Verifikasi bahwa untuk semua input di mana bug condition TIDAK berlaku, fungsi yang sudah diperbaiki menghasilkan hasil yang sama dengan fungsi asli.

**Pseudocode:**
```
FOR ALL input WHERE NOT isBugCondition(input) DO
  ASSERT complete_original(input) SIDE_EFFECTS = complete_fixed(input) SIDE_EFFECTS
END FOR
```

**Testing Approach**: Property-based testing direkomendasikan untuk preservation checking karena:
- Menghasilkan banyak test case otomatis di seluruh domain input
- Menangkap edge case yang mungkin terlewat oleh unit test manual
- Memberikan jaminan kuat bahwa perilaku tidak berubah untuk semua input non-buggy

**Test Cases**:
1. **Settings toggle preservation**: Verifikasi bahwa mengubah modul via Settings controller tetap menyimpan ke `enabled_modules` dengan benar
2. **Null backward compat**: Verifikasi `isModuleEnabled()` tetap mengembalikan `true` untuk tenant dengan `enabled_modules = null`
3. **Skip onboarding**: Verifikasi `skip()` tidak mengubah `enabled_modules`
4. **Direct recommend() call**: Verifikasi `recommend('fnb')`, `recommend('retail')`, dll tetap mengembalikan hasil yang sama

### Unit Tests

- Test `complete()` dengan `selected_modules` terisi → `tenant.enabled_modules` harus terisi
- Test `complete()` dengan `selected_modules` kosong dan `industry = 'restaurant'` → `tenant.enabled_modules` harus berisi modul F&B
- Test mapping untuk ketiga industry key yang bermasalah: `restaurant→fnb`, `manufacturing→manufacture`, `services→service`
- Test `complete()` dengan industry yang tidak perlu mapping (`retail`, `hotel`, dll) → tetap bekerja benar
- Test edge case: `selected_modules = []` (array kosong) → fallback ke rekomendasi industri

### Property-Based Tests

- Generate random `selected_modules` (subset dari `ALL_MODULES`) → setelah `complete()`, `tenant.enabled_modules` selalu sama dengan input
- Generate random industry dari semua nilai valid → `recommend()` tidak pernah mengembalikan modul default kecuali untuk industry yang memang tidak punya mapping spesifik
- Generate random non-onboarding actions → `enabled_modules` tidak berubah

### Integration Tests

- Full onboarding flow: pilih industri `restaurant` → selesaikan onboarding → verifikasi hanya modul F&B yang aktif di dashboard
- Full onboarding flow: pilih modul manual → selesaikan onboarding → verifikasi hanya modul yang dipilih yang aktif
- Verifikasi tenant lama (tanpa onboarding) tetap bisa akses semua modul (`enabled_modules = null`)
- Verifikasi Settings > Modul masih bisa mengubah `enabled_modules` setelah onboarding selesai
