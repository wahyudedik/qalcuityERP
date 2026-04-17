# Bugfix Requirements Document

## Introduction

Fitur module toggle memungkinkan tenant untuk mengaktifkan atau menonaktifkan modul ERP sesuai kebutuhan bisnis mereka. Fitur ini juga ditampilkan selama proses onboarding, di mana user memilih industri dan sistem merekomendasikan modul yang relevan.

Bug yang ditemukan terdapat pada dua area:

1. **Onboarding tidak menerapkan pilihan modul ke tenant** — Saat user menyelesaikan onboarding dan memilih modul (`selected_modules`), data tersebut hanya disimpan ke `OnboardingProfile` tapi **tidak** disimpan ke `tenant.enabled_modules`. Akibatnya, semua modul tetap aktif (karena `enabled_modules = null` berarti semua aktif), bukan hanya modul yang dipilih user.

2. **Industri onboarding tidak dipetakan ke rekomendasi modul** — `OnboardingController::saveIndustry()` menerima nilai industri seperti `'restaurant'`, `'manufacturing'`, `'services'`, namun `ModuleRecommendationService::recommend()` menggunakan key berbeda seperti `'fnb'`, `'manufacture'`, `'service'`. Pemetaan ini tidak konsisten sehingga rekomendasi modul tidak pernah cocok dengan industri yang dipilih user saat onboarding.

---

## Bug Analysis

### Current Behavior (Defect)

1.1 WHEN user menyelesaikan onboarding dan memilih modul tertentu THEN sistem menyimpan pilihan modul hanya ke `onboarding_profiles.selected_modules` dan TIDAK menyimpannya ke `tenants.enabled_modules`

1.2 WHEN user menyelesaikan onboarding dengan industri yang dipilih THEN sistem tidak menerapkan rekomendasi modul berbasis industri ke `tenants.enabled_modules`, sehingga `enabled_modules` tetap `null` (semua modul aktif)

1.3 WHEN user memilih industri `'restaurant'` saat onboarding dan sistem memanggil `ModuleRecommendationService::recommend()` THEN sistem mengembalikan rekomendasi default (bukan rekomendasi F&B) karena key `'restaurant'` tidak cocok dengan key `'fnb'` yang digunakan service

1.4 WHEN user memilih industri `'manufacturing'` saat onboarding dan sistem memanggil `ModuleRecommendationService::recommend()` THEN sistem mengembalikan rekomendasi default karena key `'manufacturing'` tidak cocok dengan key `'manufacture'`

1.5 WHEN user memilih industri `'services'` saat onboarding dan sistem memanggil `ModuleRecommendationService::recommend()` THEN sistem mengembalikan rekomendasi default karena key `'services'` tidak cocok dengan key `'service'`

### Expected Behavior (Correct)

2.1 WHEN user menyelesaikan onboarding dan memilih modul tertentu THEN sistem SHALL menyimpan pilihan modul tersebut ke `tenants.enabled_modules` sehingga hanya modul yang dipilih yang aktif

2.2 WHEN user menyelesaikan onboarding tanpa memilih modul secara manual THEN sistem SHALL menerapkan rekomendasi modul berbasis industri yang dipilih ke `tenants.enabled_modules`

2.3 WHEN user memilih industri `'restaurant'` saat onboarding THEN sistem SHALL memetakan key tersebut ke `'fnb'` sebelum memanggil `ModuleRecommendationService::recommend()` sehingga rekomendasi modul F&B yang tepat diterapkan

2.4 WHEN user memilih industri `'manufacturing'` saat onboarding THEN sistem SHALL memetakan key tersebut ke `'manufacture'` sebelum memanggil `ModuleRecommendationService::recommend()`

2.5 WHEN user memilih industri `'services'` saat onboarding THEN sistem SHALL memetakan key tersebut ke `'service'` sebelum memanggil `ModuleRecommendationService::recommend()`

### Unchanged Behavior (Regression Prevention)

3.1 WHEN admin tenant mengubah modul melalui halaman Settings > Modul THEN sistem SHALL CONTINUE TO menyimpan perubahan ke `tenants.enabled_modules` dengan benar

3.2 WHEN tenant memiliki `enabled_modules = null` (tenant lama sebelum fitur ini ada) THEN sistem SHALL CONTINUE TO menganggap semua modul aktif (backward compatibility)

3.3 WHEN `isModuleEnabled()` dipanggil pada tenant dengan `enabled_modules` yang sudah diset THEN sistem SHALL CONTINUE TO mengembalikan `true` hanya untuk modul yang ada dalam array tersebut

3.4 WHEN user melewati (skip) proses onboarding THEN sistem SHALL CONTINUE TO tidak mengubah `tenants.enabled_modules` (tetap `null`, semua modul aktif)

3.5 WHEN `ModuleRecommendationService::recommend()` dipanggil langsung dari endpoint AJAX `/settings/modules/recommend` THEN sistem SHALL CONTINUE TO menerima industry key yang sudah ada (`fnb`, `retail`, `manufacture`, dll) tanpa perubahan
