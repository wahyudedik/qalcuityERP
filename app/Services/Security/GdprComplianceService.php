<?php

namespace App\Services\Security;

use App\Models\DataConsent;
use App\Models\DataRequest;

class GdprComplianceService
{
    /**
     * Record user consent
     */
    public function recordConsent(int $tenantId, int $userId, string $consentType, string $consentText): bool
    {
        try {
            DataConsent::create([
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'consent_type' => $consentType,
                'granted' => true,
                'consent_text' => $consentText,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'granted_at' => now(),
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Record consent failed', [
                'user_id' => $userId,
                'consent_type' => $consentType,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Withdraw consent
     */
    public function withdrawConsent(int $tenantId, int $userId, string $consentType): bool
    {
        try {
            DataConsent::where('tenant_id', $tenantId)
                ->where('user_id', $userId)
                ->where('consent_type', $consentType)
                ->where('granted', true)
                ->update([
                    'granted' => false,
                    'withdrawn_at' => now(),
                ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Withdraw consent failed', [
                'user_id' => $userId,
                'consent_type' => $consentType,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Check if user has given consent
     */
    public function hasConsent(int $tenantId, int $userId, string $consentType): bool
    {
        $consent = DataConsent::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('consent_type', $consentType)
            ->where('granted', true)
            ->latest()
            ->first();

        return $consent !== null;
    }

    /**
     * Create data access request (Right to Access)
     */
    public function createAccessRequest(int $tenantId, int $userId, ?string $details = null): int
    {
        $request = DataRequest::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'request_type' => 'access',
            'details' => $details,
            'status' => 'pending',
        ]);

        return $request->id;
    }

    /**
     * Create data erasure request (Right to be Forgotten)
     */
    public function createErasureRequest(int $tenantId, int $userId, ?string $details = null): int
    {
        $request = DataRequest::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'request_type' => 'erasure',
            'details' => $details,
            'status' => 'pending',
        ]);

        return $request->id;
    }

    /**
     * Create data rectification request
     */
    public function createRectificationRequest(int $tenantId, int $userId, string $details): int
    {
        $request = DataRequest::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'request_type' => 'rectification',
            'details' => $details,
            'status' => 'pending',
        ]);

        return $request->id;
    }

    /**
     * Process data access request - export user data
     */
    public function processAccessRequest(int $requestId, int $processedByUserId): array
    {
        try {
            $dataRequest = DataRequest::findOrFail($requestId);

            // Collect all user data
            $userData = $this->collectUserData($dataRequest->user_id, $dataRequest->tenant_id);

            $dataRequest->update([
                'status' => 'completed',
                'response_data' => json_encode($userData),
                'processed_by_user_id' => $processedByUserId,
                'processed_at' => now(),
                'completed_at' => now(),
            ]);

            return [
                'success' => true,
                'data' => $userData,
                'export_url' => $this->generateExportFile($userData),
            ];
        } catch (\Exception $e) {
            \Log::error('Process access request failed', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Process data erasure request
     */
    public function processErasureRequest(int $requestId, int $processedByUserId): bool
    {
        try {
            $dataRequest = DataRequest::findOrFail($requestId);

            // Anonymize user data (don't delete - keep for audit)
            $this->anonymizeUserData($dataRequest->user_id, $dataRequest->tenant_id);

            $dataRequest->update([
                'status' => 'completed',
                'processed_by_user_id' => $processedByUserId,
                'processed_at' => now(),
                'completed_at' => now(),
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Process erasure request failed', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get pending data requests
     */
    public function getPendingRequests(int $tenantId): array
    {
        return DataRequest::where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->id,
                    'user_name' => $request->user->name ?? 'Unknown',
                    'request_type' => $request->request_type,
                    'details' => $request->details,
                    'created_at' => $request->created_at,
                ];
            })
            ->toArray();
    }

    /**
     * Get user's consent history
     */
    public function getUserConsents(int $tenantId, int $userId): array
    {
        return DataConsent::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->orderBy('granted_at', 'desc')
            ->get()
            ->map(function ($consent) {
                return [
                    'consent_type' => $consent->consent_type,
                    'granted' => $consent->granted,
                    'granted_at' => $consent->granted_at,
                    'withdrawn_at' => $consent->withdrawn_at,
                    'consent_text' => $consent->consent_text,
                ];
            })
            ->toArray();
    }

    /**
     * Collect all user data for export
     */
    protected function collectUserData(int $userId, int $tenantId): array
    {
        $user = \App\Models\User::find($userId);

        return [
            'profile' => $user ? $user->only(['name', 'email', 'phone']) : [],
            'consents' => $this->getUserConsents($tenantId, $userId),
            // Add more data collections as needed
        ];
    }

    /**
     * Anonymize user data
     */
    protected function anonymizeUserData(int $userId, int $tenantId): void
    {
        $user = \App\Models\User::find($userId);

        if ($user) {
            $user->update([
                'name' => 'Deleted User #' . $userId,
                'email' => "deleted_{$userId}@anonymous.com",
                'phone' => null,
            ]);
        }
    }

    /**
     * Generate export file
     */
    protected function generateExportFile(array $userData): string
    {
        $filename = 'data_export_' . date('Y-m-d_H-i-s') . '.json';
        $path = storage_path('app/exports/' . $filename);

        file_put_contents($path, json_encode($userData, JSON_PRETTY_PRINT));

        return url('storage/exports/' . $filename);
    }
}
