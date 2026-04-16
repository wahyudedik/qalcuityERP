# Design Document: Product QR Certificate

## Overview

Fitur ini menambahkan kemampuan generate QR Code produk dan penerbitan Sertifikat Digital Keaslian Produk pada aplikasi ERP multi-tenant berbasis Laravel. Setiap produk dapat memiliki QR Code unik yang dikodekan dengan URL verifikasi publik. Sertifikat digital diterbitkan dengan tanda tangan kriptografis HMAC-SHA256 yang tidak dapat dipalsukan tanpa akses ke `APP_KEY` server.

Desain ini memanfaatkan library `bacon/bacon-qr-code` yang sudah tersedia, mengikuti pola `BarcodeService` dan `BarcodeController` yang sudah ada, serta menggunakan trait `BelongsToTenant` untuk isolasi data multi-tenant.

---

## Architecture

Fitur ini mengikuti arsitektur layered yang sudah ada di codebase:

```
┌─────────────────────────────────────────────────────────────┐
│  HTTP Layer                                                  │
│  ProductQrController  │  CertificateController  │  VerifyController (public) │
└──────────────┬────────────────┬────────────────────┬────────┘
               │                │                    │
┌──────────────▼────────────────▼────────────────────▼────────┐
│  Service Layer                                               │
│  ProductQrService             │  CertificateService          │
└──────────────┬────────────────┴────────────────────┬────────┘
               │                                     │
┌──────────────▼─────────────────────────────────────▼────────┐
│  Data Layer                                                  │
│  Product (existing)  │  ProductCertificate  │  CertVerifyLog │
└─────────────────────────────────────────────────────────────┘
```

**Alur utama:**
1. Admin menerbitkan sertifikat → `CertificateService::issue()` → hitung HMAC-SHA256 → simpan `ProductCertificate` → trigger `ProductQrService::generate()`
2. `ProductQrService::generate()` → buat QR Code PNG via `BaconQrCode\Writer` → simpan ke `storage/app/public/qr-codes/` → update `products.qr_code_path`
3. Konsumen scan QR → buka `/verify/{certificate_number}` (public) → `CertificateService::verify()` → hitung ulang hash → bandingkan → tampilkan status

---

## Components and Interfaces

### ProductQrService

Bertanggung jawab untuk generate dan manage gambar QR Code produk.

```php
namespace App\Services;

class ProductQrService
{
    // Generate QR Code PNG untuk produk, simpan ke storage, update product record.
    // Jika QR sudah ada dan $force=false, kembalikan path yang sudah ada.
    public function generate(Product $product, bool $force = false): string;

    // Hapus file QR Code lama dari storage
    public function delete(Product $product): void;

    // Buat QR payload URL: /verify/{certificate_number}
    public function buildPayload(string $certificateNumber): string;
}
```

**Implementasi `generate()`:**
- Gunakan `BaconQrCode\Writer` dengan `PngImageBackEnd` (konsisten dengan `TwoFactorController`)
- Ukuran: 300x300 piksel (memenuhi minimum 200x200)
- Simpan ke `storage/app/public/qr-codes/{tenant_id}/{product_id}.png`
- Return path relatif untuk disimpan di `products.qr_code_path`

### CertificateService

Bertanggung jawab untuk penerbitan, verifikasi, dan revoke sertifikat.

```php
namespace App\Services;

class CertificateService
{
    // Terbitkan sertifikat baru untuk produk. Revoke sertifikat aktif sebelumnya.
    // Trigger generate QR Code setelah sertifikat diterbitkan.
    public function issue(Product $product, User $issuedBy, ?Carbon $expiresAt = null): ProductCertificate;

    // Verifikasi sertifikat berdasarkan certificate_number.
    // Return array ['status' => 'VALID'|'TIDAK VALID'|'TIDAK DITEMUKAN'|'DICABUT', 'certificate' => ..., 'product' => ...]
    public function verify(string $certificateNumber, string $ipAddress): array;

    // Revoke sertifikat aktif. Throw exception jika sudah revoked.
    public function revoke(ProductCertificate $certificate, User $revokedBy, string $reason): void;

    // Hitung HMAC-SHA256 dari data sertifikat
    public function computeHash(Product $product, string $certificateNumber, Carbon $issuedAt): string;

    // Generate PDF sertifikat untuk dicetak
    public function generatePdf(ProductCertificate $certificate): \Barryvdh\DomPDF\PDF;

    // Generate nomor sertifikat unik: CERT-{TENANT_ID}-{YYYYMMDD}-{SEQUENCE}
    private function generateCertificateNumber(int $tenantId): string;
}
```

**Komputasi hash:**
```php
hash_hmac('sha256', implode('|', [
    $product->id,
    $product->tenant_id,
    $product->sku,
    $certificateNumber,
    $issuedAt->toIso8601String(),
]), config('app.key'));
```

### ProductQrController

Controller untuk operasi QR Code yang memerlukan autentikasi.

```php
// POST /products/{product}/qr/generate   → generate atau regenerate QR
// GET  /products/{product}/qr/download   → download PNG
// POST /products/qr/print-labels         → cetak label PDF (thermal/A4)
```

### CertificateController

Controller untuk manajemen sertifikat (authenticated).

```php
// POST   /products/{product}/certificates          → issue sertifikat
// GET    /products/{product}/certificates          → list riwayat sertifikat
// DELETE /certificates/{certificate}/revoke        → revoke sertifikat
// GET    /certificates/{certificate}/pdf           → download PDF sertifikat
```

### VerifyController (Public)

Controller publik tanpa middleware auth.

```php
// GET /verify/{certificateNumber}  → halaman verifikasi publik
```

---

## Data Models

### Tabel: `product_certificates`

```php
Schema::create('product_certificates', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->string('certificate_number', 50)->unique();
    $table->string('certificate_hash', 64);          // HMAC-SHA256 hex (64 chars)
    $table->enum('status', ['active', 'revoked'])->default('active');
    $table->foreignId('issued_by')->constrained('users');
    $table->timestamp('issued_at');
    $table->timestamp('expires_at')->nullable();
    $table->foreignId('revoked_by')->nullable()->constrained('users');
    $table->timestamp('revoked_at')->nullable();
    $table->string('revoke_reason')->nullable();
    $table->timestamps();

    $table->index(['product_id', 'status']);
    $table->index(['tenant_id', 'certificate_number']);
});
```

### Tabel: `certificate_verify_logs`

```php
Schema::create('certificate_verify_logs', function (Blueprint $table) {
    $table->id();
    $table->string('certificate_number', 50)->index();
    $table->string('ip_address', 45);
    $table->enum('result', ['valid', 'invalid', 'not_found', 'revoked']);
    $table->timestamp('verified_at');
});
```

### Modifikasi tabel `products`

```php
Schema::table('products', function (Blueprint $table) {
    $table->string('qr_code_path')->nullable()->after('barcode');
});
```

### Model: `ProductCertificate`

```php
namespace App\Models;

class ProductCertificate extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'product_id', 'certificate_number', 'certificate_hash',
        'status', 'issued_by', 'issued_at', 'expires_at',
        'revoked_by', 'revoked_at', 'revoke_reason',
    ];

    protected function casts(): array
    {
        return [
            'issued_at'  => 'datetime',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function issuer(): BelongsTo  { return $this->belongsTo(User::class, 'issued_by'); }
    public function revoker(): BelongsTo { return $this->belongsTo(User::class, 'revoked_by'); }

    public function isActive(): bool   { return $this->status === 'active'; }
    public function isRevoked(): bool  { return $this->status === 'revoked'; }
}
```

---

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system — essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: QR Payload Uniqueness

*For any* two products with different (product_id, tenant_id) combinations, the QR payload URL yang dihasilkan oleh `buildPayload()` harus berbeda.

**Validates: Requirements 1.3**

---

### Property 2: QR Code Minimum Size

*For any* produk yang di-generate QR Code-nya, dimensi gambar PNG yang dihasilkan harus minimal 200x200 piksel.

**Validates: Requirements 1.7**

---

### Property 3: QR Generation Idempotence

*For any* produk yang sudah memiliki QR Code, memanggil `generate()` tanpa flag `$force=true` harus mengembalikan path yang sama tanpa membuat file baru.

**Validates: Requirements 1.5**

---

### Property 4: Certificate Number Uniqueness

*For any* jumlah sertifikat yang diterbitkan (untuk produk dan tenant yang berbeda-beda), tidak boleh ada dua sertifikat dengan `certificate_number` yang sama di seluruh database.

**Validates: Requirements 2.1**

---

### Property 5: HMAC Hash Determinism

*For any* kombinasi (product_id, tenant_id, sku, certificate_number, issued_at), fungsi `computeHash()` harus selalu menghasilkan nilai yang sama ketika dipanggil berulang kali dengan input yang sama.

**Validates: Requirements 2.2, 2.3**

---

### Property 6: Certificate Issuance Completeness

*For any* produk yang diterbitkan sertifikatnya, record `ProductCertificate` yang dihasilkan harus memiliki: `certificate_hash` tidak null, `issued_by` tidak null, `issued_at` tidak null, dan produk terkait harus memiliki `qr_code_path` yang tidak null setelah proses selesai.

**Validates: Requirements 2.4, 1.4, 2.6**

---

### Property 7: Single Active Certificate Invariant

*For any* produk, setelah menerbitkan sertifikat baru, jumlah sertifikat dengan status `active` untuk produk tersebut harus tepat 1, dan total jumlah sertifikat (aktif + revoked) harus bertambah (riwayat tidak dihapus).

**Validates: Requirements 2.5, 4.2**

---

### Property 8: Verification Round-Trip

*For any* sertifikat yang diterbitkan oleh `CertificateService::issue()` dengan data produk yang tidak diubah, memanggil `CertificateService::verify()` dengan `certificate_number` yang sama harus menghasilkan status `VALID`. Memanggil `verify()` berkali-kali dengan input yang sama harus menghasilkan hasil yang konsisten (idempotent).

**Validates: Requirements 6.1, 6.4, 3.2, 3.3**

---

### Property 9: Tamper Detection

*For any* sertifikat yang sudah diterbitkan, jika salah satu dari field `sku`, `tenant_id`, atau `product_id` pada produk diubah setelah penerbitan, maka `verify()` harus menghasilkan status `TIDAK VALID` (bukan `VALID`).

**Validates: Requirements 6.2, 3.4**

---

### Property 10: Revoked Certificate Verification

*For any* sertifikat yang di-revoke, memanggil `verify()` dengan `certificate_number`-nya harus menghasilkan status `DICABUT`, bukan `VALID`.

**Validates: Requirements 3.6, 4.5**

---

### Property 11: Double-Revoke Error

*For any* sertifikat yang sudah berstatus `revoked`, memanggil `revoke()` kembali harus melempar exception (bukan diam-diam berhasil).

**Validates: Requirements 4.4**

---

### Property 12: Verification Log Recorded

*For any* pemanggilan `verify()` dengan `certificate_number` yang ada di database, harus ada record baru di tabel `certificate_verify_logs` setelah pemanggilan selesai.

**Validates: Requirements 3.7**

---

### Property 13: Not Found for Unknown Certificate

*For any* string yang tidak ada sebagai `certificate_number` di database, `verify()` harus menghasilkan status `TIDAK DITEMUKAN`.

**Validates: Requirements 3.5**

---

### Property 14: Label PDF Contains Required Fields

*For any* subset produk yang dipilih untuk cetak label, PDF yang dihasilkan harus mengandung: nama produk, SKU, dan `certificate_number` untuk setiap produk dalam subset tersebut.

**Validates: Requirements 5.4, 5.1**

---

### Property 15: 404 for Cross-Tenant Product

*For any* `product_id` yang tidak ditemukan dalam `tenant_id` user yang sedang login, `CertificateService::issue()` harus melempar `ModelNotFoundException` (yang akan dirender sebagai HTTP 404).

**Validates: Requirements 2.8**

---

## Error Handling

| Kondisi | Penanganan |
|---|---|
| Produk tidak ditemukan di tenant | `ModelNotFoundException` → HTTP 404 |
| Revoke sertifikat yang sudah revoked | `InvalidArgumentException` dengan pesan deskriptif |
| Gagal generate QR Code (BaconQrCode error) | Log error, lempar `RuntimeException` |
| Gagal tulis file ke storage | Log error, lempar `RuntimeException` |
| `certificate_number` tidak ditemukan saat verifikasi | Return `['status' => 'TIDAK DITEMUKAN']` (bukan exception) |
| Hash tidak cocok saat verifikasi | Return `['status' => 'TIDAK VALID']` (bukan exception) |
| Sertifikat revoked saat verifikasi | Return `['status' => 'DICABUT']` (bukan exception) |

Verifikasi publik tidak boleh melempar exception ke user — semua kondisi error dikembalikan sebagai status yang dapat ditampilkan di halaman.

---

## Testing Strategy

### Unit Tests (PHPUnit)

Fokus pada contoh spesifik, edge case, dan integrasi antar komponen:

- `CertificateServiceTest`: test `issue()`, `verify()`, `revoke()`, `computeHash()` dengan contoh konkret
- `ProductQrServiceTest`: test `generate()`, `buildPayload()`, idempotence
- `VerifyControllerTest`: test HTTP response untuk sertifikat valid, revoked, tidak ditemukan
- `CertificateControllerTest`: test authorization (tenant isolation), revoke flow
- Edge cases: produk tanpa SKU, `APP_KEY` berbeda menghasilkan hash berbeda, PDF generation

### Property-Based Tests (Pest + `pestphp/pest-plugin-faker` atau `eris/eris`)

Library yang digunakan: **`eris/eris`** (PHP property-based testing library).

Setiap property test dikonfigurasi minimum **100 iterasi**.

Setiap test diberi tag komentar dengan format:
`// Feature: product-qr-certificate, Property {N}: {property_text}`

Mapping property ke test:

| Property | Test | Pola PBT |
|---|---|---|
| P1: QR Payload Uniqueness | Generate random (product_id, tenant_id) pairs, assert payload berbeda | Metamorphic |
| P2: QR Minimum Size | Generate random produk, assert dimensi PNG ≥ 200x200 | Invariant |
| P3: QR Idempotence | Generate QR dua kali, assert path sama | Idempotence |
| P4: Certificate Number Uniqueness | Issue N sertifikat, assert semua cert_number unik | Invariant |
| P5: HMAC Determinism | Hitung hash dua kali dengan input sama, assert sama | Round-trip |
| P6: Issuance Completeness | Issue sertifikat, assert semua field wajib tidak null | Invariant |
| P7: Single Active Invariant | Issue beberapa sertifikat berturut-turut, assert count active = 1 | Invariant |
| P8: Verification Round-Trip | Issue → verify, assert VALID; verify ulang, assert sama | Round-trip + Idempotence |
| P9: Tamper Detection | Issue → ubah SKU → verify, assert TIDAK VALID | Metamorphic |
| P10: Revoked Shows DICABUT | Issue → revoke → verify, assert DICABUT | Round-trip |
| P11: Double-Revoke Error | Issue → revoke → revoke lagi, assert exception | Error condition |
| P12: Verify Log Recorded | Verify sertifikat, assert log count bertambah 1 | Invariant |
| P13: Not Found | Random string sebagai cert_number, assert TIDAK DITEMUKAN | Error condition |
| P14: Label PDF Fields | Generate label untuk random produk, assert konten PDF | Invariant |
| P15: 404 Cross-Tenant | Random product_id dari tenant lain, assert ModelNotFoundException | Error condition |

### Dual Testing Rationale

Unit tests menangkap bug konkret dan memvalidasi integrasi antar komponen. Property tests memverifikasi kebenaran universal dengan input yang beragam — terutama penting untuk kriptografi (P5, P8, P9) dan isolasi multi-tenant (P15). Keduanya saling melengkapi dan keduanya diperlukan.
