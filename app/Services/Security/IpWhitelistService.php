<?php

namespace App\Services\Security;

use App\Models\IpWhitelist;
use Illuminate\Support\Facades\Request;

class IpWhitelistService
{
    /**
     * Check if IP is whitelisted
     */
    public function isIpAllowed(int $tenantId, string $scope = 'admin'): bool
    {
        $ipAddress = Request::ip();

        // Check if IP is in whitelist
        $whitelistEntry = IpWhitelist::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where(function ($query) use ($scope) {
                $query->where('scope', $scope)
                    ->orWhere('scope', 'all');
            })
            ->where(function ($query) use ($ipAddress) {
                $query->where('ip_address', $ipAddress)
                    ->orWhereRaw('? BETWEEN INET_ATON(SUBSTRING_INDEX(ip_address, "/", 1)) AND INET_ATON(SUBSTRING_INDEX(ip_address, "/", 1)) + POW(2, 32 - SUBSTRING_INDEX(ip_address, "/", -1)) - 1', [$ipAddress]);
            })
            ->first();

        return $whitelistEntry !== null;
    }

    /**
     * Add IP to whitelist
     */
    public function addIp(int $tenantId, string $ipAddress, int $userId, array $options = []): bool
    {
        try {
            IpWhitelist::create([
                'tenant_id' => $tenantId,
                'ip_address' => $ipAddress,
                'description' => $options['description'] ?? '',
                'scope' => $options['scope'] ?? 'admin',
                'is_active' => true,
                'created_by_user_id' => $userId,
                'expires_at' => $options['expires_at'] ?? null,
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Add IP to whitelist failed', [
                'tenant_id' => $tenantId,
                'ip_address' => $ipAddress,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Remove IP from whitelist
     */
    public function removeIp(int $tenantId, string $ipAddress): bool
    {
        try {
            IpWhitelist::where('tenant_id', $tenantId)
                ->where('ip_address', $ipAddress)
                ->delete();

            return true;
        } catch (\Exception $e) {
            \Log::error('Remove IP from whitelist failed', [
                'tenant_id' => $tenantId,
                'ip_address' => $ipAddress,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Deactivate IP
     */
    public function deactivateIp(int $whitelistId): bool
    {
        try {
            $whitelist = IpWhitelist::find($whitelistId);

            if ($whitelist) {
                $whitelist->update(['is_active' => false]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            \Log::error('Deactivate IP failed', [
                'whitelist_id' => $whitelistId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get all whitelisted IPs
     */
    public function getWhitelistedIps(int $tenantId, ?string $scope = null): array
    {
        $query = IpWhitelist::where('tenant_id', $tenantId);

        if ($scope) {
            $query->where('scope', $scope);
        }

        return $query->orderBy('created_at', 'desc')->get()->map(function ($ip) {
            return [
                'id' => $ip->id,
                'ip_address' => $ip->ip_address,
                'description' => $ip->description,
                'scope' => $ip->scope,
                'is_active' => $ip->is_active,
                'created_by' => $ip->createdBy ? $ip->createdBy->name : 'Unknown',
                'created_at' => $ip->created_at,
                'expires_at' => $ip->expires_at,
            ];
        })->toArray();
    }

    /**
     * Clean expired IPs
     */
    public function cleanExpiredIps(): int
    {
        return IpWhitelist::where('is_active', true)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->update(['is_active' => false]);
    }

    /**
     * Validate IP format
     */
    public function isValidIp(string $ipAddress): bool
    {
        // Support single IP or CIDR notation
        if (strpos($ipAddress, '/') !== false) {
            // CIDR notation
            [$ip, $prefix] = explode('/', $ipAddress);

            return filter_var($ip, FILTER_VALIDATE_IP) &&
                is_numeric($prefix) &&
                $prefix >= 0 &&
                $prefix <= 32;
        }

        return filter_var($ipAddress, FILTER_VALIDATE_IP) !== false;
    }
}
