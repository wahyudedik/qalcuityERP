<?php

namespace App\Services;

use App\Models\User;
use PragmaRX\Google2FA\Google2FA;

/**
 * TwoFactorService — Task 53
 * Kelola setup, verifikasi, dan recovery 2FA per user.
 */
class TwoFactorService
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Generate secret baru untuk user (belum disimpan, perlu konfirmasi dulu).
     */
    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    /**
     * Buat QR code URL untuk ditampilkan ke user.
     */
    public function getQrCodeUrl(User $user, string $secret): string
    {
        return $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );
    }

    /**
     * Verifikasi kode OTP dari user.
     */
    public function verify(string $secret, string $code): bool
    {
        return (bool) $this->google2fa->verifyKey($secret, $code, 1); // window=1 (±30 detik)
    }

    /**
     * Aktifkan 2FA setelah user konfirmasi kode pertama kali.
     */
    public function enable(User $user, string $secret): void
    {
        $user->update([
            'two_factor_secret'       => encrypt($secret),
            'two_factor_enabled'      => true,
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => $this->generateRecoveryCodes(),
        ]);
    }

    /**
     * Nonaktifkan 2FA.
     */
    public function disable(User $user): void
    {
        $user->update([
            'two_factor_secret'         => null,
            'two_factor_enabled'        => false,
            'two_factor_confirmed_at'   => null,
            'two_factor_recovery_codes' => null,
        ]);
    }

    /**
     * Verifikasi recovery code dan hapus dari daftar jika valid.
     */
    public function verifyRecoveryCode(User $user, string $code): bool
    {
        $codes = $user->two_factor_recovery_codes ?? [];
        $index = array_search(trim($code), $codes);

        if ($index === false) return false;

        // Hapus kode yang sudah dipakai
        unset($codes[$index]);
        $user->update(['two_factor_recovery_codes' => array_values($codes)]);

        return true;
    }

    /**
     * Ambil secret yang sudah di-decrypt.
     */
    public function getSecret(User $user): ?string
    {
        if (!$user->two_factor_secret) return null;
        try {
            return decrypt($user->two_factor_secret);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Generate 8 recovery codes acak.
     */
    public function generateRecoveryCodes(): array
    {
        return array_map(
            fn() => strtoupper(substr(str_replace(['+', '/', '='], '', base64_encode(random_bytes(10))), 0, 10)),
            range(1, 8)
        );
    }

    /**
     * Regenerate recovery codes.
     */
    public function regenerateRecoveryCodes(User $user): array
    {
        $codes = $this->generateRecoveryCodes();
        $user->update(['two_factor_recovery_codes' => $codes]);
        return $codes;
    }
}
