# Requirements Document

## Introduction

Fitur ini menambahkan kemampuan generate QR Code produk dan Sertifikat Digital Keaslian Produk pada aplikasi ERP multi-tenant berbasis Laravel. Setiap produk dapat memiliki QR Code unik yang dapat dipindai untuk memverifikasi keaslian produk. Sertifikat digital diterbitkan per produk (atau per batch/unit) dengan tanda tangan kriptografis yang tidak dapat dipalsukan, sehingga konsumen dan mitra bisnis dapat memverifikasi bahwa produk tersebut orisinil.

Fitur ini memanfaatkan library `bacon/bacon-qr-code` yang sudah tersedia di codebase, serta pola `BarcodeService` dan `BarcodeController` yang sudah ada untuk konsistensi arsitektur.

---

## Glossary

- **QR_Generator**: Komponen sistem yang bertanggung jawab membuat gambar QR Code untuk produk.
- **Certificate_Service**: Komponen sistem yang bertanggung jawab menerbitkan, menyimpan, dan memvalidasi sertifikat digital keaslian produk.
- **Product_Certificate**: Dokumen digital yang membuktikan keaslian suatu produk, berisi metadata produk, tanda tangan kriptografis, dan nomor sertifikat unik.
- **Certificate_Hash**: Nilai hash SHA-256 yang dihitung dari data produk + tenant_id + timestamp penerbitan + secret key, digunakan sebagai tanda tangan kriptografis.
- **Verification_Page**: Halaman publik (tidak memerlukan login) yang menampilkan status keaslian produk berdasarkan QR Code yang dipindai.
- **Tenant**: Perusahaan/organisasi yang menggunakan aplikasi ERP ini dalam konteks multi-tenant.
- **Certificate_Number**: Nomor unik sertifikat dengan format `CERT-{TENANT_ID}-{YYYYMMDD}-{SEQUENCE}`.
- **QR_Payload**: Data yang dikodekan dalam QR Code, berisi URL verifikasi beserta certificate_number dan hash singkat.

---

## Requirements

### Requirement 1: Generate QR Code Produk

**User Story:** Sebagai admin atau manager, saya ingin men-generate QR Code untuk setiap produk, sehingga QR Code tersebut dapat dicetak pada kemasan atau label produk.

#### Acceptance Criteria

1. WHEN admin atau manager membuka halaman detail atau daftar produk, THE QR_Generator SHALL menampilkan opsi untuk men-generate QR Code per produk.
2. WHEN permintaan generate QR Code dikirim untuk suatu produk, THE QR_Generator SHALL menghasilkan gambar QR Code dalam format PNG yang mengandung URL verifikasi produk tersebut.
3. THE QR_Generator SHALL memastikan setiap QR Code yang dihasilkan bersifat unik per produk per tenant, sehingga dua produk berbeda tidak menghasilkan QR Code yang identik.
4. WHEN QR Code berhasil di-generate, THE QR_Generator SHALL menyimpan path gambar QR Code pada record produk di database.
5. WHEN QR Code sudah pernah di-generate untuk suatu produk, THE QR_Generator SHALL mengembalikan QR Code yang sudah ada tanpa membuat duplikat, kecuali diminta regenerasi secara eksplisit.
6. WHEN admin atau manager meminta regenerasi QR Code, THE QR_Generator SHALL membuat QR Code baru dan memperbarui record produk serta sertifikat terkait.
7. THE QR_Generator SHALL menghasilkan QR Code dengan ukuran minimal 200x200 piksel agar dapat dipindai dengan andal oleh perangkat mobile standar.
8. WHEN QR Code berhasil di-generate, THE QR_Generator SHALL menyediakan endpoint download sehingga gambar QR Code dapat diunduh dalam format PNG.

---

### Requirement 2: Penerbitan Sertifikat Digital Keaslian Produk

**User Story:** Sebagai admin atau manager, saya ingin menerbitkan sertifikat digital keaslian untuk setiap produk, sehingga produk dapat dibuktikan keasliannya secara kriptografis.

#### Acceptance Criteria

1. WHEN admin atau manager menerbitkan sertifikat untuk suatu produk, THE Certificate_Service SHALL membuat record Product_Certificate baru dengan Certificate_Number yang unik.
2. THE Certificate_Service SHALL menghitung Certificate_Hash menggunakan HMAC-SHA256 dari kombinasi: `product_id`, `tenant_id`, `sku`, `certificate_number`, dan `issued_at` timestamp.
3. THE Certificate_Service SHALL menyimpan Certificate_Hash pada record Product_Certificate di database sehingga dapat diverifikasi ulang kapan saja.
4. WHEN sertifikat diterbitkan, THE Certificate_Service SHALL mencatat `issued_by` (user_id penerbit), `issued_at` (timestamp penerbitan), dan `expires_at` (opsional, dapat null untuk sertifikat permanen).
5. THE Certificate_Service SHALL memastikan satu produk hanya memiliki satu sertifikat aktif pada satu waktu; sertifikat lama SHALL dinonaktifkan (status `revoked`) ketika sertifikat baru diterbitkan.
6. WHEN sertifikat berhasil diterbitkan, THE Certificate_Service SHALL secara otomatis men-trigger generate atau regenerasi QR Code produk yang memuat URL verifikasi sertifikat tersebut.
7. THE Certificate_Service SHALL menyediakan fungsi untuk men-generate PDF sertifikat yang dapat dicetak, berisi: nama produk, SKU, nama tenant, Certificate_Number, tanggal terbit, dan QR Code verifikasi.
8. WHEN permintaan penerbitan sertifikat diterima untuk produk yang tidak ditemukan dalam tenant yang sama, THEN THE Certificate_Service SHALL mengembalikan error 404 dengan pesan deskriptif.

---

### Requirement 3: Verifikasi Keaslian Produk via QR Code

**User Story:** Sebagai konsumen atau mitra bisnis, saya ingin memindai QR Code pada produk dan melihat status keasliannya, sehingga saya dapat memastikan produk yang saya terima adalah produk orisinil.

#### Acceptance Criteria

1. THE Verification_Page SHALL dapat diakses secara publik tanpa memerlukan autentikasi, melalui URL dengan format `/verify/{certificate_number}`.
2. WHEN QR Code dipindai dan Verification_Page dibuka dengan certificate_number yang valid, THE Verification_Page SHALL menampilkan informasi: nama produk, SKU, nama tenant/brand, tanggal penerbitan sertifikat, dan status keaslian (ASLI / TIDAK VALID).
3. WHEN Verification_Page menerima certificate_number, THE Certificate_Service SHALL menghitung ulang Certificate_Hash dari data produk yang tersimpan dan membandingkannya dengan hash yang tersimpan di database.
4. IF Certificate_Hash yang dihitung ulang tidak cocok dengan hash yang tersimpan, THEN THE Verification_Page SHALL menampilkan status "TIDAK VALID" beserta pesan bahwa sertifikat tidak dapat diverifikasi.
5. IF certificate_number tidak ditemukan di database, THEN THE Verification_Page SHALL menampilkan status "TIDAK DITEMUKAN" dengan pesan bahwa produk tidak terdaftar.
6. IF sertifikat berstatus `revoked`, THEN THE Verification_Page SHALL menampilkan status "DICABUT" beserta tanggal pencabutan.
7. WHEN Verification_Page berhasil memverifikasi sertifikat, THE Certificate_Service SHALL mencatat log verifikasi yang berisi: timestamp, IP address pemindai, dan certificate_number, untuk keperluan audit.
8. THE Verification_Page SHALL menampilkan hasil verifikasi dalam waktu tidak lebih dari 3 detik sejak request diterima.

---

### Requirement 4: Manajemen Sertifikat (Revoke & Riwayat)

**User Story:** Sebagai admin, saya ingin dapat mencabut (revoke) sertifikat yang sudah tidak valid dan melihat riwayat sertifikat suatu produk, sehingga saya dapat mengelola integritas data keaslian produk.

#### Acceptance Criteria

1. WHEN admin melakukan revoke pada sertifikat aktif, THE Certificate_Service SHALL mengubah status sertifikat menjadi `revoked` dan mencatat `revoked_by`, `revoked_at`, dan `revoke_reason`.
2. THE Certificate_Service SHALL menyimpan seluruh riwayat sertifikat suatu produk (aktif maupun revoked) sehingga audit trail tetap terjaga.
3. WHEN admin membuka halaman detail produk, THE Certificate_Service SHALL menampilkan daftar semua sertifikat yang pernah diterbitkan untuk produk tersebut beserta statusnya.
4. IF admin mencoba merevoke sertifikat yang sudah berstatus `revoked`, THEN THE Certificate_Service SHALL mengembalikan error dengan pesan bahwa sertifikat sudah dalam status revoked.
5. WHILE sertifikat berstatus `revoked`, THE Verification_Page SHALL menampilkan status "DICABUT" dan tidak menampilkan informasi produk secara lengkap.

---

### Requirement 5: Cetak Label QR Code Produk

**User Story:** Sebagai admin atau manager, saya ingin mencetak label QR Code produk dalam format PDF, sehingga label tersebut dapat ditempelkan pada kemasan fisik produk.

#### Acceptance Criteria

1. WHEN admin atau manager memilih satu atau lebih produk dan meminta cetak label QR Code, THE QR_Generator SHALL menghasilkan PDF yang berisi label QR Code untuk setiap produk yang dipilih.
2. THE QR_Generator SHALL mendukung dua format label: format thermal (50mm x 25mm) dan format A4 (multiple labels per halaman), konsisten dengan format yang sudah ada pada BarcodeController.
3. WHEN produk yang dipilih belum memiliki QR Code atau sertifikat aktif, THE QR_Generator SHALL secara otomatis menerbitkan sertifikat dan men-generate QR Code sebelum mencetak label.
4. THE QR_Generator SHALL menyertakan informasi berikut pada setiap label: gambar QR Code, nama produk, SKU, dan Certificate_Number.

---

### Requirement 6: Round-Trip Integritas Sertifikat

**User Story:** Sebagai sistem, saya ingin memastikan bahwa proses penerbitan dan verifikasi sertifikat bersifat konsisten dan tidak dapat dimanipulasi, sehingga integritas keaslian produk terjamin.

#### Acceptance Criteria

1. FOR ALL sertifikat yang diterbitkan oleh Certificate_Service, proses verifikasi ulang dengan data produk yang sama SHALL menghasilkan status VALID (round-trip property).
2. IF data produk (SKU, nama, atau tenant_id) diubah setelah sertifikat diterbitkan, THEN THE Certificate_Service SHALL mendeteksi ketidakcocokan hash pada saat verifikasi dan menampilkan status TIDAK VALID.
3. THE Certificate_Service SHALL menggunakan nilai `APP_KEY` Laravel sebagai secret key untuk HMAC, sehingga hash tidak dapat direproduksi tanpa akses ke konfigurasi server.
4. WHEN Certificate_Number yang sama digunakan untuk verifikasi lebih dari satu kali, THE Verification_Page SHALL menghasilkan hasil yang konsisten (idempotent) selama data produk tidak berubah.
