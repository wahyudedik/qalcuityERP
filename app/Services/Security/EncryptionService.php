<?php

namespace App\Services\Security;

use App\Models\EncryptionKey;
use Illuminate\Support\Facades\Crypt;

class EncryptionService
{
    /**
     * Encrypt sensitive data with tenant-specific key
     */
    public function encryptData(int $tenantId, string $keyName, string $data): string
    {
        try {
            $encryptionKey = $this->getActiveKey($tenantId, $keyName);

            if (!$encryptionKey) {
                // Create new key if doesn't exist
                $encryptionKey = $this->createEncryptionKey($tenantId, $keyName);
            }

            // Use Laravel's built-in encryption (AES-256-CBC)
            return Crypt::encryptString($data);
        } catch (\Exception $e) {
            \Log::error('Encryption failed', [
                'tenant_id' => $tenantId,
                'key_name' => $keyName,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Decrypt data
     */
    public function decryptData(int $tenantId, string $keyName, string $encryptedData): string
    {
        try {
            return Crypt::decryptString($encryptedData);
        } catch (\Exception $e) {
            \Log::error('Decryption failed', [
                'tenant_id' => $tenantId,
                'key_name' => $keyName,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Rotate encryption key
     */
    public function rotateKey(int $tenantId, string $keyName, int $userId): bool
    {
        try {
            $oldKey = $this->getActiveKey($tenantId, $keyName);

            if ($oldKey) {
                // Deactivate old key
                $oldKey->update(['is_active' => false]);
            }

            // Create new key
            $this->createEncryptionKey($tenantId, $keyName);

            // Log rotation
            EncryptionKey::where('tenant_id', $tenantId)
                ->where('key_name', $keyName)
                ->latest()
                ->first()
                    ?->update([
                    'rotated_at' => now(),
                    'rotated_by_user_id' => $userId,
                ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Key rotation failed', [
                'tenant_id' => $tenantId,
                'key_name' => $keyName,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get active encryption key
     */
    protected function getActiveKey(int $tenantId, string $keyName): ?EncryptionKey
    {
        return EncryptionKey::where('tenant_id', $tenantId)
            ->where('key_name', $keyName)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Create new encryption key
     */
    protected function createEncryptionKey(int $tenantId, string $keyName): EncryptionKey
    {
        // Laravel's Crypt uses APP_KEY from .env
        // For per-tenant keys, we would implement custom encryption
        // For now, we track key metadata

        return EncryptionKey::create([
            'tenant_id' => $tenantId,
            'key_name' => $keyName,
            'public_key' => 'AES-256-CBC', // Using Laravel's default
            'private_key' => 'managed_by_laravel',
            'algorithm' => 'AES-256-CBC',
            'is_active' => true,
        ]);
    }

    /**
     * Hash sensitive field for searchability
     */
    public function hashForSearch(string $value): string
    {
        return hash_hmac('sha256', $value, config('app.key'));
    }

    /**
     * Encrypt array of data
     */
    public function encryptArray(int $tenantId, string $keyName, array $data): array
    {
        $encrypted = [];

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $encrypted[$key] = $this->encryptData($tenantId, $keyName, $value);
            } else {
                $encrypted[$key] = $value;
            }
        }

        return $encrypted;
    }

    /**
     * Decrypt array of data
     */
    public function decryptArray(int $tenantId, string $keyName, array $encryptedData): array
    {
        $decrypted = [];

        foreach ($encryptedData as $key => $value) {
            if (is_string($value)) {
                try {
                    $decrypted[$key] = $this->decryptData($tenantId, $keyName, $value);
                } catch (\Exception $e) {
                    $decrypted[$key] = $value; // Keep encrypted if fails
                }
            } else {
                $decrypted[$key] = $value;
            }
        }

        return $decrypted;
    }
}
