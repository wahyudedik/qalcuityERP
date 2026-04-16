# Implementation Plan: Product QR Certificate

## Overview

Implementasi fitur QR Code produk dan Sertifikat Digital Keaslian berbasis Laravel. Mengikuti arsitektur layered yang sudah ada: migration → model → service → controller → view/route. Memanfaatkan `bacon/bacon-qr-code` dan pola `BelongsToTenant` yang sudah tersedia.

## Tasks

- [x] 1. Database migrations dan model
  - [x] 1.1 Buat migration `add_qr_code_path_to_products_table`
    - Tambahkan kolom `qr_code_path` (nullable string) ke tabel `products` setelah kolom `barcode`
    - _Requirements: 1.4_

  - [x] 1.2 Buat migration `create_product_certificates_table`
    - Buat tabel `product_certificates` sesuai skema di design (id, tenant_id, product_id, certificate_number, certificate_hash, status, issued_by, issued_at, expires_at, revoked_by, revoked_at, revoke_reason, timestamps)
    - Tambahkan index pada `(product_id, status)` dan `(tenant_id, certificate_number)`
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 4.1_

  - [x] 1.3 Buat migration `create_certificate_verify_logs_table`
    - Buat tabel `certificate_verify_logs` (id, certificate_number, ip_address, result enum, verified_at)
    - _Requirements: 3.7_

  - [x] 1.4 Buat model `ProductCertificate`
    - Gunakan trait `BelongsToTenant`, definisikan `$fillable`, `casts()`, relasi `product()`, `issuer()`, `revoker()`
    - Tambahkan helper methods `isActive()` dan `isRevoked()`
    - _Requirements: 2.1, 2.4, 4.1_

  - [x] 1.5 Update model `Product`
    - Tambahkan `qr_code_path` ke `$fillable`
    - Tambahkan relasi `certificates()` dan `activeCertificate()`
    - _Requirements: 1.4, 4.3_

- [x] 2. ProductQrService
  - [x] 2.1 Buat `app/Services/ProductQrService.php`
    - Implementasikan `buildPayload(string $certificateNumber): string` — return URL `/verify/{certificateNumber}`
    - Implementasikan `generate(Product $product, bool $force = false): string` — gunakan `BaconQrCode\Writer` dengan `PngImageBackEnd`, ukuran 300x300, simpan ke `storage/app/public/qr-codes/{tenant_id}/{product_id}.png`, update `product->qr_code_path`
    - Implementasikan `delete(Product $product): void` — hapus file lama dari storage
    - _Requirements: 1.2, 1.3, 1.4, 1.5, 1.7_

  - [x] 2.2 Tulis property test untuk `ProductQrService` — Property 1: QR Payload Uniqueness
    - **Property 1: QR Payload Uniqueness**
    - **Validates: Requirements 1.3**
    - Gunakan `eris/eris`, minimum 100 iterasi, generate random (product_id, tenant_id) pairs, assert payload berbeda

  - [x] 2.3 Tulis property test untuk `ProductQrService` — Property 2: QR Minimum Size
    - **Property 2: QR Code Minimum Size**
    - **Validates: Requirements 1.7**
    - Generate QR untuk random produk, assert dimensi PNG ≥ 200x200 piksel

  - [x] 2.4 Tulis property test untuk `ProductQrService` — Property 3: QR Idempotence
    - **Property 3: QR Generation Idempotence**
    - **Validates: Requirements 1.5**
    - Panggil `generate()` dua kali tanpa `$force`, assert path yang dikembalikan sama

- [x] 3. CertificateService — core
  - [x] 3.1 Buat `app/Services/CertificateService.php` dengan method `computeHash()` dan `generateCertificateNumber()`
    - `computeHash(Product $product, string $certificateNumber, Carbon $issuedAt): string` — HMAC-SHA256 dari `product_id|tenant_id|sku|certificate_number|issuedAt->toIso8601String()` dengan `config('app.key')`
    - `generateCertificateNumber(int $tenantId): string` — format `CERT-{TENANT_ID}-{YYYYMMDD}-{SEQUENCE}`
    - _Requirements: 2.1, 2.2, 2.3, 6.3_

  - [x] 3.2 Tulis property test untuk `CertificateService` — Property 5: HMAC Determinism
    - **Property 5: HMAC Hash Determinism**
    - **Validates: Requirements 2.2, 2.3**
    - Hitung hash dua kali dengan input yang sama, assert hasilnya identik

  - [x] 3.3 Tulis property test untuk `CertificateService` — Property 4: Certificate Number Uniqueness
    - **Property 4: Certificate Number Uniqueness**
    - **Validates: Requirements 2.1**
    - Issue N sertifikat untuk produk/tenant berbeda, assert semua `certificate_number` unik

- [x] 4. CertificateService — issue, revoke, verify
  - [x] 4.1 Implementasikan `issue(Product $product, User $issuedBy, ?Carbon $expiresAt = null): ProductCertificate`
    - Revoke sertifikat aktif sebelumnya (jika ada), buat `ProductCertificate` baru, hitung hash, trigger `ProductQrService::generate()`
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6_

  - [x] 4.2 Tulis property test — Property 6: Certificate Issuance Completeness
    - **Property 6: Certificate Issuance Completeness**
    - **Validates: Requirements 2.4, 1.4, 2.6**
    - Issue sertifikat, assert `certificate_hash`, `issued_by`, `issued_at` tidak null dan `product->qr_code_path` tidak null

  - [x] 4.3 Tulis property test — Property 7: Single Active Certificate Invariant
    - **Property 7: Single Active Certificate Invariant**
    - **Validates: Requirements 2.5, 4.2**
    - Issue beberapa sertifikat berturut-turut, assert count active = 1 dan total sertifikat bertambah

  - [x] 4.4 Implementasikan `revoke(ProductCertificate $certificate, User $revokedBy, string $reason): void`
    - Throw `InvalidArgumentException` jika sudah revoked, update status, catat `revoked_by`, `revoked_at`, `revoke_reason`
    - _Requirements: 4.1, 4.4_

  - [x] 4.5 Tulis property test — Property 11: Double-Revoke Error
    - **Property 11: Double-Revoke Error**
    - **Validates: Requirements 4.4**
    - Issue → revoke → revoke lagi, assert exception dilempar

  - [x] 4.6 Implementasikan `verify(string $certificateNumber, string $ipAddress): array`
    - Cari sertifikat, handle not found → `TIDAK DITEMUKAN`, revoked → `DICABUT`, hitung ulang hash → cocok `VALID` / tidak cocok `TIDAK VALID`
    - Catat log ke `certificate_verify_logs` setiap kali dipanggil
    - _Requirements: 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 6.1, 6.2, 6.4_

  - [x] 4.7 Tulis property test — Property 8: Verification Round-Trip
    - **Property 8: Verification Round-Trip**
    - **Validates: Requirements 6.1, 6.4, 3.2, 3.3**
    - Issue → verify, assert VALID; verify ulang, assert hasil sama

  - [x] 4.8 Tulis property test — Property 9: Tamper Detection
    - **Property 9: Tamper Detection**
    - **Validates: Requirements 6.2, 3.4**
    - Issue → ubah SKU produk → verify, assert TIDAK VALID

  - [x] 4.9 Tulis property test — Property 10: Revoked Certificate Verification
    - **Property 10: Revoked Certificate Verification**
    - **Validates: Requirements 3.6, 4.5**
    - Issue → revoke → verify, assert DICABUT

  - [x] 4.10 Tulis property test — Property 12: Verification Log Recorded
    - **Property 12: Verification Log Recorded**
    - **Validates: Requirements 3.7**
    - Verify sertifikat, assert record baru muncul di `certificate_verify_logs`

  - [x] 4.11 Tulis property test — Property 13: Not Found for Unknown Certificate
    - **Property 13: Not Found for Unknown Certificate**
    - **Validates: Requirements 3.5**
    - Random string sebagai certificate_number, assert status TIDAK DITEMUKAN

  - [x] 4.12 Tulis property test — Property 15: 404 Cross-Tenant
    - **Property 15: 404 for Cross-Tenant Product**
    - **Validates: Requirements 2.8**
    - Random product_id dari tenant lain, assert `ModelNotFoundException` dilempar

- [x] 5. Checkpoint — pastikan semua tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [x] 6. CertificateService — PDF generation
  - [x] 6.1 Implementasikan `generatePdf(ProductCertificate $certificate): \Barryvdh\DomPDF\PDF`
    - Buat Blade view `resources/views/certificates/pdf.blade.php` dengan: nama produk, SKU, nama tenant, Certificate_Number, tanggal terbit, dan QR Code (embed base64 PNG)
    - _Requirements: 2.7_

  - [x] 6.2 Tulis property test — Property 14: Label PDF Contains Required Fields
    - **Property 14: Label PDF Contains Required Fields**
    - **Validates: Requirements 5.4, 5.1**
    - Generate label untuk random produk, assert konten PDF mengandung nama produk, SKU, dan certificate_number

- [x] 7. Controllers dan Routes
  - [x] 7.1 Buat `app/Http/Controllers/ProductQrController.php`
    - `POST /products/{product}/qr/generate` → panggil `ProductQrService::generate($product, force: true)`
    - `GET /products/{product}/qr/download` → stream file PNG sebagai download
    - `POST /products/qr/print-labels` → generate PDF label (thermal/A4) untuk produk yang dipilih, auto-issue sertifikat jika belum ada
    - _Requirements: 1.1, 1.6, 1.8, 5.1, 5.2, 5.3, 5.4_

  - [x] 7.2 Buat `app/Http/Controllers/CertificateController.php`
    - `POST /products/{product}/certificates` → `CertificateService::issue()`
    - `GET /products/{product}/certificates` → list riwayat sertifikat produk
    - `DELETE /certificates/{certificate}/revoke` → `CertificateService::revoke()`
    - `GET /certificates/{certificate}/pdf` → `CertificateService::generatePdf()`
    - Pastikan semua endpoint menggunakan tenant isolation (produk harus milik tenant yang sama)
    - _Requirements: 2.1, 2.4, 2.7, 2.8, 4.1, 4.3_

  - [x] 7.3 Buat `app/Http/Controllers/VerifyController.php` (public, tanpa auth middleware)
    - `GET /verify/{certificateNumber}` → panggil `CertificateService::verify()`, render Blade view
    - _Requirements: 3.1, 3.2, 3.5, 3.6, 3.8_

  - [x] 7.4 Daftarkan routes di `routes/web.php`
    - Route authenticated untuk `ProductQrController` dan `CertificateController` (dalam group middleware `auth` + `tenant`)
    - Route publik untuk `VerifyController` tanpa middleware auth
    - _Requirements: 3.1_

- [x] 8. Blade Views
  - [x] 8.1 Buat view `resources/views/verify/show.blade.php`
    - Tampilkan status verifikasi (VALID / TIDAK VALID / DICABUT / TIDAK DITEMUKAN), nama produk, SKU, nama tenant, tanggal terbit
    - Tampilkan pesan yang sesuai untuk setiap status
    - _Requirements: 3.1, 3.2, 3.4, 3.5, 3.6_

  - [x] 8.2 Buat view `resources/views/certificates/label-thermal.blade.php` dan `label-a4.blade.php`
    - Thermal: 50mm x 25mm per label, berisi QR Code, nama produk, SKU, Certificate_Number
    - A4: multiple labels per halaman
    - _Requirements: 5.2, 5.4_

- [x] 9. Checkpoint akhir — pastikan semua tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks bertanda `*` bersifat opsional dan dapat dilewati untuk MVP yang lebih cepat
- Setiap task mereferensikan requirements spesifik untuk traceability
- Property tests menggunakan library `eris/eris` dengan minimum 100 iterasi per test
- Checkpoint memastikan validasi inkremental sebelum melanjutkan ke fase berikutnya
