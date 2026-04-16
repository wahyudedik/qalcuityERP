<?php

namespace App\Services;

use App\Models\CertificateVerifyLog;
use App\Models\Product;
use App\Models\ProductCertificate;
use App\Models\User;
use Carbon\Carbon;

class CertificateService
{
    public function __construct(private ProductQrService $qrService) {}

    /**
     * Terbitkan sertifikat baru untuk produk.
     *
     * Revoke sertifikat aktif sebelumnya (jika ada), buat ProductCertificate baru,
     * hitung hash, dan trigger regenerasi QR Code.
     */
    public function issue(Product $product, User $issuedBy, ?Carbon $expiresAt = null): ProductCertificate
    {
        // Revoke sertifikat aktif sebelumnya jika ada
        $existing = $product->certificates()->where('status', 'active')->first();
        if ($existing !== null) {
            $existing->update([
                'status'        => 'revoked',
                'revoked_at'    => now(),
                'revoked_by'    => $issuedBy->id,
                'revoke_reason' => 'Superseded by new certificate',
            ]);
        }

        $certificateNumber = $this->generateCertificateNumber($product->tenant_id);
        $issuedAt          = now();
        $hash              = $this->computeHash($product, $certificateNumber, $issuedAt);

        $certificate = ProductCertificate::create([
            'tenant_id'          => $product->tenant_id,
            'product_id'         => $product->id,
            'certificate_number' => $certificateNumber,
            'certificate_hash'   => $hash,
            'status'             => 'active',
            'issued_by'          => $issuedBy->id,
            'issued_at'          => $issuedAt,
            'expires_at'         => $expiresAt,
        ]);

        // Refresh product so activeCertificate relation picks up the new cert
        $product->refresh();
        $this->qrService->generate($product, true);

        return $certificate;
    }

    /**
     * Hitung HMAC-SHA256 dari data sertifikat.
     *
     * Hash dihitung dari: product_id|tenant_id|sku|certificate_number|issuedAt (ISO 8601)
     * menggunakan APP_KEY sebagai secret key.
     */
    public function computeHash(Product $product, string $certificateNumber, Carbon $issuedAt): string
    {
        return hash_hmac('sha256', implode('|', [
            $product->id,
            $product->tenant_id,
            $product->sku,
            $certificateNumber,
            $issuedAt->toIso8601String(),
        ]), config('app.key'));
    }

    /**
     * Verifikasi sertifikat berdasarkan certificate_number.
     *
     * Mencari sertifikat, menangani not found / revoked, menghitung ulang hash,
     * dan mencatat log ke certificate_verify_logs setiap kali dipanggil.
     *
     * @return array{status: string, certificate: ProductCertificate|null, product: \App\Models\Product|null}
     */
    public function verify(string $certificateNumber, string $ipAddress): array
    {
        $certificate = ProductCertificate::where('certificate_number', $certificateNumber)
            ->with('product')
            ->first();

        if ($certificate === null) {
            CertificateVerifyLog::create([
                'certificate_number' => $certificateNumber,
                'ip_address'         => $ipAddress,
                'result'             => 'not_found',
                'verified_at'        => now(),
            ]);

            return ['status' => 'TIDAK DITEMUKAN', 'certificate' => null, 'product' => null];
        }

        if ($certificate->isRevoked()) {
            CertificateVerifyLog::create([
                'certificate_number' => $certificateNumber,
                'ip_address'         => $ipAddress,
                'result'             => 'revoked',
                'verified_at'        => now(),
            ]);

            return ['status' => 'DICABUT', 'certificate' => $certificate, 'product' => $certificate->product];
        }

        $computedHash = $this->computeHash($certificate->product, $certificateNumber, $certificate->issued_at);
        $isValid      = hash_equals($certificate->certificate_hash, $computedHash);

        CertificateVerifyLog::create([
            'certificate_number' => $certificateNumber,
            'ip_address'         => $ipAddress,
            'result'             => $isValid ? 'valid' : 'invalid',
            'verified_at'        => now(),
        ]);

        return [
            'status'      => $isValid ? 'VALID' : 'TIDAK VALID',
            'certificate' => $certificate,
            'product'     => $certificate->product,
        ];
    }

    /**
     * Revoke sertifikat aktif.
     *
     * Melempar InvalidArgumentException jika sertifikat sudah berstatus revoked.
     */
    public function revoke(ProductCertificate $certificate, User $revokedBy, string $reason): void
    {
        if ($certificate->isRevoked()) {
            throw new \InvalidArgumentException('Certificate is already revoked.');
        }

        $certificate->update([
            'status'        => 'revoked',
            'revoked_by'    => $revokedBy->id,
            'revoked_at'    => now(),
            'revoke_reason' => $reason,
        ]);
    }

    /**
     * Generate PDF sertifikat untuk dicetak.
     *
     * Memuat relasi product.tenant, embed QR Code sebagai base64 PNG,
     * dan render view certificates.pdf via DomPDF.
     */
    public function generatePdf(ProductCertificate $certificate): \Barryvdh\DomPDF\PDF
    {
        $certificate->loadMissing(['product.tenant']);

        $qrBase64 = null;
        if ($certificate->product->qr_code_path) {
            $qrData = \Illuminate\Support\Facades\Storage::disk('public')->get($certificate->product->qr_code_path);
            if ($qrData) {
                $qrBase64 = 'data:image/png;base64,' . base64_encode($qrData);
            }
        }

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('certificates.pdf', [
            'certificate' => $certificate,
            'product'     => $certificate->product,
            'tenant'      => $certificate->product->tenant,
            'qrBase64'    => $qrBase64,
        ]);
    }

    /**
     * Generate nomor sertifikat unik: CERT-{TENANT_ID}-{YYYYMMDD}-{SEQUENCE}
     *
     * SEQUENCE adalah angka 4 digit zero-padded berdasarkan jumlah sertifikat
     * yang sudah diterbitkan untuk tenant tersebut hari ini.
     */
    public function generateCertificateNumber(int $tenantId): string
    {
        $date = now()->format('Ymd');

        $sequence = ProductCertificate::where('tenant_id', $tenantId)
            ->whereDate('issued_at', today())
            ->count() + 1;

        $paddedSequence = str_pad($sequence, 4, '0', STR_PAD_LEFT);

        return "CERT-{$tenantId}-{$date}-{$paddedSequence}";
    }
}
