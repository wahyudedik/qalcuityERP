# Bugfix Requirements Document

## Introduction

Sistem ERP multi-tenant tidak memiliki validasi antara paket langganan (SubscriptionPlan) dan modul yang diaktifkan (enabled_modules) untuk setiap tenant. Akibatnya, tenant dengan paket Starter dapat mengaktifkan modul advanced seperti `manufacturing`, `fleet`, atau `wms` secara manual melalui halaman Settings > Modules, tanpa ada pembatasan dari sistem. Selain itu, fitur yang tercantum di paket (string bebas seperti "Manufaktur (BOM & MRP)") tidak ter-mapping ke module key terstruktur, sehingga tidak bisa digunakan sebagai dasar validasi akses.

---

## Bug Analysis

### Current Behavior (Defect)

1.1 WHEN tenant dengan paket Starter mengirim request update modul yang menyertakan module key advanced (seperti `manufacturing`, `fleet`, `wms`) THEN sistem menyimpan modul tersebut ke `enabled_modules` tanpa validasi apapun terhadap paket

1.2 WHEN super-admin mengubah plan tenant melalui `TenantController::updatePlan()` THEN sistem hanya memvalidasi nilai `in:trial,basic,pro,enterprise` (slug legacy) dan tidak menyesuaikan `enabled_modules` dengan modul yang diizinkan oleh plan baru

1.3 WHEN `SubscriptionPlan` dibuat atau diperbarui melalui `PlanController` THEN sistem hanya menyimpan fitur sebagai array string bebas (contoh: "Manufaktur (BOM & MRP)") tanpa mapping ke module key terstruktur yang digunakan oleh `ModuleRecommendationService::ALL_MODULES`

1.4 WHEN tenant mengakses route modul tertentu (selain healthcare) THEN sistem tidak mengecek apakah paket tenant mengizinkan akses ke modul tersebut — hanya `enabled_modules` yang dicek, bukan plan

### Expected Behavior (Correct)

2.1 WHEN tenant dengan paket Starter mengirim request update modul yang menyertakan module key di luar yang diizinkan oleh plannya THEN sistem SHALL menolak request tersebut dan mengembalikan error validasi yang menjelaskan modul mana yang tidak diizinkan

2.2 WHEN super-admin mengubah plan tenant THEN sistem SHALL menyesuaikan `enabled_modules` tenant agar hanya berisi modul yang diizinkan oleh plan baru (modul yang tidak diizinkan SHALL dinonaktifkan secara otomatis)

2.3 WHEN `SubscriptionPlan` menyimpan fitur THEN sistem SHALL memiliki mekanisme mapping antara fitur plan dan module key terstruktur, sehingga dapat ditentukan modul mana yang diizinkan untuk setiap plan

2.4 WHEN tenant mengakses route modul yang tidak diizinkan oleh plannya THEN sistem SHALL mengembalikan response 403 dengan pesan yang menjelaskan bahwa modul tersebut memerlukan upgrade paket

### Unchanged Behavior (Regression Prevention)

3.1 WHEN tenant dengan `enabled_modules = null` (tenant lama / backward compat) mengakses modul apapun THEN sistem SHALL CONTINUE TO mengizinkan akses ke semua modul (null = semua aktif)

3.2 WHEN tenant mengaktifkan modul yang memang diizinkan oleh plannya melalui Settings > Modules THEN sistem SHALL CONTINUE TO menyimpan pilihan tersebut ke `enabled_modules` dengan benar

3.3 WHEN super-admin (role `super_admin`) mengakses route apapun THEN sistem SHALL CONTINUE TO mengizinkan akses penuh tanpa pembatasan plan atau modul

3.4 WHEN tenant dengan paket Professional atau Enterprise mengaktifkan modul advanced (`manufacturing`, `fleet`, `wms`) THEN sistem SHALL CONTINUE TO mengizinkan aktivasi modul tersebut

3.5 WHEN `isModuleEnabled()` dipanggil pada tenant dengan `enabled_modules` berisi array THEN sistem SHALL CONTINUE TO mengembalikan `true` hanya untuk modul yang ada di array tersebut

3.6 WHEN tenant dengan status langganan `expired` atau `suspended` mengakses sistem THEN sistem SHALL CONTINUE TO mengarahkan ke halaman subscription expired melalui `CheckTenantActive` middleware
