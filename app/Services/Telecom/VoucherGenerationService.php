<?php

namespace App\Services\Telecom;

use App\Models\InternetPackage;
use App\Models\VoucherCode;
use Illuminate\Support\Str;

/**
 * Service for generating and managing voucher codes.
 */
class VoucherGenerationService
{
    /**
     * Generate single voucher code.
     * 
     * @param InternetPackage $package
     * @param array $options Generation options
     * @return VoucherCode
     */
    public function generateSingle(InternetPackage $package, array $options = []): VoucherCode
    {
        $code = $this->generateCode($options['code_length'] ?? 8, $options['code_pattern'] ?? 'alphanumeric');

        return VoucherCode::create([
            'tenant_id' => $package->tenant_id,
            'package_id' => $package->id,
            'generated_by' => $options['generated_by'] ?? null,
            'code' => $code,
            'batch_number' => $options['batch_number'] ?? null,
            'valid_from' => $options['valid_from'] ?? null,
            'valid_until' => $options['valid_until'] ?? null,
            'validity_hours' => $options['validity_hours'] ?? 24,
            'max_usage' => $options['max_usage'] ?? 1,
            'download_speed_mbps' => $options['download_speed_mbps'] ?? $package->download_speed_mbps,
            'upload_speed_mbps' => $options['upload_speed_mbps'] ?? $package->upload_speed_mbps,
            'quota_bytes' => $options['quota_bytes'] ?? $package->quota_bytes,
            'sale_price' => $options['sale_price'] ?? null,
        ]);
    }

    /**
     * Generate multiple voucher codes in batch.
     * 
     * @param InternetPackage $package
     * @param int $quantity Number of vouchers to generate
     * @param array $options Generation options
     * @return array Generated vouchers
     */
    public function generateBatch(InternetPackage $package, int $quantity, array $options = []): array
    {
        $batchNumber = $options['batch_number'] ?? 'BATCH-' . now()->format('YmdHis') . '-' . Str::random(4);
        $vouchers = [];

        for ($i = 0; $i < $quantity; $i++) {
            $vouchers[] = $this->generateSingle($package, array_merge($options, [
                'batch_number' => $batchNumber,
            ]));
        }

        return $vouchers;
    }

    /**
     * Redeem/use a voucher code.
     * 
     * @param string $code
     * @param \App\Models\Customer|null $customer
     * @param string|null $username
     * @return array Result
     */
    public function redeemVoucher(string $code, $customer = null, ?string $username = null): array
    {
        $voucher = VoucherCode::where('code', $code)->first();

        if (!$voucher) {
            return [
                'success' => false,
                'error' => 'Kode voucher tidak ditemukan',
            ];
        }

        if (!$voucher->canBeUsed()) {
            return [
                'success' => false,
                'error' => $this->getVoucherErrorMessage($voucher),
            ];
        }

        // Mark as used
        $voucher->markAsUsed($customer, $username);

        // If sold, mark as sold
        if ($voucher->sale_price && !$voucher->sold_at) {
            $voucher->update([
                'sold_at' => now(),
                'sold_to_customer_id' => $customer?->id,
            ]);
        }

        return [
            'success' => true,
            'voucher' => $voucher,
            'package' => $voucher->package,
            'message' => 'Voucher berhasil digunakan',
        ];
    }

    /**
     * Get voucher statistics.
     * 
     * @param int $tenantId
     * @param string|null $batchNumber
     * @return array Statistics
     */
    public function getVoucherStats(int $tenantId, ?string $batchNumber = null): array
    {
        $query = VoucherCode::where('tenant_id', $tenantId);

        if ($batchNumber) {
            $query->where('batch_number', $batchNumber);
        }

        $total = $query->count();
        $unused = (clone $query)->where('status', 'unused')->count();
        $used = (clone $query)->where('status', 'used')->count();
        $expired = (clone $query)->where('status', 'expired')->count();
        $revoked = (clone $query)->where('status', 'revoked')->count();

        $totalRevenue = (clone $query)
            ->whereNotNull('sale_price')
            ->whereNotNull('sold_at')
            ->sum('sale_price');

        return [
            'total' => $total,
            'unused' => $unused,
            'used' => $used,
            'expired' => $expired,
            'revoked' => $revoked,
            'usage_rate' => $total > 0 ? round(($used / $total) * 100, 2) : 0,
            'total_revenue' => $totalRevenue,
            'total_revenue_formatted' => 'Rp ' . number_format($totalRevenue, 0, ',', '.'),
        ];
    }

    /**
     * Generate voucher code string.
     */
    protected function generateCode(int $length = 8, string $pattern = 'alphanumeric'): string
    {
        switch ($pattern) {
            case 'numeric':
                $characters = '0123456789';
                break;
            case 'alphabetic':
                $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'alphanumeric':
            default:
                $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                break;
        }

        $code = '';
        $maxIndex = strlen($characters) - 1;

        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[rand(0, $maxIndex)];
        }

        // Ensure uniqueness
        if (VoucherCode::where('code', $code)->exists()) {
            return $this->generateCode($length, $pattern);
        }

        return $code;
    }

    /**
     * Get error message for voucher status.
     */
    protected function getVoucherErrorMessage(VoucherCode $voucher): string
    {
        if ($voucher->status === 'used') {
            return 'Voucher sudah digunakan';
        }

        if ($voucher->status === 'expired') {
            return 'Voucher sudah kadaluarsa';
        }

        if ($voucher->status === 'revoked') {
            return 'Voucher telah dibatalkan';
        }

        if ($voucher->usage_count >= $voucher->max_usage) {
            return 'Voucher sudah mencapai batas penggunaan';
        }

        if ($voucher->isExpired()) {
            return 'Voucher sudah kadaluarsa';
        }

        return 'Voucher tidak dapat digunakan';
    }
}
