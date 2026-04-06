<?php

namespace App\Services\Security;

use App\Models\TwoFactorAuth;
use App\Models\UserSession;
use Illuminate\Support\Facades\Crypt;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthService
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Enable 2FA for user
     */
    public function enable2FA(int $userId): array
    {
        try {
            // Generate secret key
            $secretKey = $this->google2fa->generateSecretKey();

            // Generate recovery codes
            $recoveryCodes = $this->generateRecoveryCodes(10);

            // Save to database (encrypted)
            TwoFactorAuth::updateOrCreate(
                ['user_id' => $userId],
                [
                    'secret_key' => Crypt::encrypt($secretKey),
                    'recovery_codes' => Crypt::encrypt(json_encode($recoveryCodes)),
                    'enabled' => false, // Not enabled until verified
                    'method' => 'totp',
                ]
            );

            // Generate QR code URL
            $user = \App\Models\User::find($userId);
            $qrCodeUrl = $this->google2fa->getQRCodeUrl(
                config('app.name'),
                $user->email,
                $secretKey
            );

            return [
                'success' => true,
                'secret_key' => $secretKey, // Return unencrypted for QR code generation
                'qr_code_url' => $qrCodeUrl,
                'recovery_codes' => $recoveryCodes, // Show once only
            ];
        } catch (\Exception $e) {
            \Log::error('Enable 2FA failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => 'Failed to enable 2FA'];
        }
    }

    /**
     * Verify and activate 2FA
     */
    public function verifyAndActivate(int $userId, string $code): bool
    {
        try {
            $twoFactor = TwoFactorAuth::where('user_id', $userId)->first();

            if (!$twoFactor) {
                return false;
            }

            $secretKey = Crypt::decrypt($twoFactor->secret_key);
            $valid = $this->google2fa->verifyKey($secretKey, $code);

            if ($valid) {
                $twoFactor->update([
                    'enabled' => true,
                    'enabled_at' => now(),
                ]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            \Log::error('Verify 2FA failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Verify 2FA code during login
     */
    public function verifyCode(int $userId, string $code): bool
    {
        try {
            $twoFactor = TwoFactorAuth::where('user_id', $userId)
                ->where('enabled', true)
                ->first();

            if (!$twoFactor) {
                return false;
            }

            $secretKey = Crypt::decrypt($twoFactor->secret_key);
            $valid = $this->google2fa->verifyKey($secretKey, $code);

            if ($valid) {
                $twoFactor->update(['last_used_at' => now()]);
            }

            return $valid;
        } catch (\Exception $e) {
            \Log::error('Verify 2FA code failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Use recovery code
     */
    public function useRecoveryCode(int $userId, string $recoveryCode): bool
    {
        try {
            $twoFactor = TwoFactorAuth::where('user_id', $userId)->first();

            if (!$twoFactor || !$twoFactor->enabled) {
                return false;
            }

            $recoveryCodes = json_decode(Crypt::decrypt($twoFactor->recovery_codes), true);

            $index = array_search($recoveryCode, $recoveryCodes);

            if ($index !== false) {
                // Remove used code
                unset($recoveryCodes[$index]);
                $twoFactor->update([
                    'recovery_codes' => Crypt::encrypt(json_encode(array_values($recoveryCodes))),
                    'last_used_at' => now(),
                ]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            \Log::error('Use recovery code failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Disable 2FA
     */
    public function disable2FA(int $userId): bool
    {
        try {
            $twoFactor = TwoFactorAuth::where('user_id', $userId)->first();

            if ($twoFactor) {
                $twoFactor->update([
                    'enabled' => false,
                    'secret_key' => null,
                    'recovery_codes' => null,
                ]);
            }

            return true;
        } catch (\Exception $e) {
            \Log::error('Disable 2FA failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Check if user has 2FA enabled
     */
    public function isEnabled(int $userId): bool
    {
        $twoFactor = TwoFactorAuth::where('user_id', $userId)
            ->where('enabled', true)
            ->first();

        return $twoFactor !== null;
    }

    /**
     * Get 2FA status
     */
    public function getStatus(int $userId): array
    {
        $twoFactor = TwoFactorAuth::where('user_id', $userId)->first();

        return [
            'enabled' => $twoFactor ? $twoFactor->enabled : false,
            'method' => $twoFactor ? $twoFactor->method : null,
            'enabled_at' => $twoFactor ? $twoFactor->enabled_at : null,
            'last_used_at' => $twoFactor ? $twoFactor->last_used_at : null,
        ];
    }

    /**
     * Generate recovery codes
     */
    protected function generateRecoveryCodes(int $count = 10): array
    {
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(4))); // 8 character codes
        }

        return $codes;
    }
}
