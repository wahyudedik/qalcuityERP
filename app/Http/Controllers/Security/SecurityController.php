<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Services\Security\TwoFactorAuthService;
use App\Services\Security\EncryptionService;
use App\Services\Security\SessionManagementService;
use App\Services\Security\IpWhitelistService;
use App\Services\Security\AuditLogService;
use App\Services\Security\GdprComplianceService;
use App\Services\Security\PermissionService;
use Illuminate\Http\Request;

class SecurityController extends Controller
{
    protected $twoFactorService;
    protected $encryptionService;
    protected $sessionService;
    protected $ipWhitelistService;
    protected $auditLogService;
    protected $gdprService;
    protected $permissionService;

    public function __construct(
        TwoFactorAuthService $twoFactorService,
        EncryptionService $encryptionService,
        SessionManagementService $sessionService,
        IpWhitelistService $ipWhitelistService,
        AuditLogService $auditLogService,
        GdprComplianceService $gdprService,
        PermissionService $permissionService
    ) {
        $this->twoFactorService = $twoFactorService;
        $this->encryptionService = $encryptionService;
        $this->sessionService = $sessionService;
        $this->ipWhitelistService = $ipWhitelistService;
        $this->auditLogService = $auditLogService;
        $this->gdprService = $gdprService;
        $this->permissionService = $permissionService;
    }

    // ==================== TWO-FACTOR AUTHENTICATION ====================

    public function enable2FA()
    {
        $result = $this->twoFactorService->enable2FA(auth()->id());
        return response()->json($result);
    }

    public function verify2FA(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $success = $this->twoFactorService->verifyAndActivate(auth()->id(), $request->code);

        return response()->json([
            'success' => $success,
            'message' => $success ? '2FA enabled successfully' : 'Invalid code',
        ]);
    }

    public function disable2FA()
    {
        $success = $this->twoFactorService->disable2FA(auth()->id());

        return response()->json([
            'success' => $success,
            'message' => $success ? '2FA disabled' : 'Failed to disable 2FA',
        ]);
    }

    public function get2FAStatus()
    {
        $status = $this->twoFactorService->getStatus(auth()->id());
        return response()->json(['success' => true, 'status' => $status]);
    }

    // ==================== SESSION MANAGEMENT ====================

    public function getActiveSessions()
    {
        $sessions = $this->sessionService->getActiveSessions(auth()->id());
        return response()->json(['success' => true, 'sessions' => $sessions]);
    }

    public function terminateSession(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
        ]);

        $success = $this->sessionService->terminateSession($request->session_id);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Session terminated' : 'Failed to terminate session',
        ]);
    }

    public function terminateAllOtherSessions()
    {
        $currentSessionId = session()->getId();
        $count = $this->sessionService->terminateAllSessions(auth()->id(), $currentSessionId);

        return response()->json([
            'success' => true,
            'terminated_count' => $count,
            'message' => "Terminated {$count} other sessions",
        ]);
    }

    // ==================== IP WHITELISTING ====================

    public function getWhitelistedIps(Request $request)
    {
        $scope = $request->query('scope');
        $ips = $this->ipWhitelistService->getWhitelistedIps(auth()->user()->tenant_id, $scope);

        return response()->json(['success' => true, 'ips' => $ips]);
    }

    public function addIpToWhitelist(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|string',
            'description' => 'sometimes|string',
            'scope' => 'sometimes|in:admin,api,all',
            'expires_at' => 'sometimes|date|after:now',
        ]);

        if (!$this->ipWhitelistService->isValidIp($request->ip_address)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid IP address format',
            ], 422);
        }

        $success = $this->ipWhitelistService->addIp(
            auth()->user()->tenant_id,
            $request->ip_address,
            auth()->id(),
            [
                'description' => $request->description ?? '',
                'scope' => $request->scope ?? 'admin',
                'expires_at' => $request->expires_at ?? null,
            ]
        );

        return response()->json([
            'success' => $success,
            'message' => $success ? 'IP added to whitelist' : 'Failed to add IP',
        ]);
    }

    public function removeIpFromWhitelist(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|string',
        ]);

        $success = $this->ipWhitelistService->removeIp(
            auth()->user()->tenant_id,
            $request->ip_address
        );

        return response()->json([
            'success' => $success,
            'message' => $success ? 'IP removed from whitelist' : 'Failed to remove IP',
        ]);
    }

    public function deactivateIp(int $whitelistId)
    {
        $success = $this->ipWhitelistService->deactivateIp($whitelistId);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'IP deactivated' : 'Failed to deactivate IP',
        ]);
    }

    // ==================== AUDIT LOGS ====================

    public function getAuditLogs(Request $request)
    {
        $filters = $request->only([
            'event_type',
            'user_id',
            'start_date',
            'end_date',
            'model_type',
            'model_id',
            'success',
            'per_page',
        ]);

        $logs = $this->auditLogService->getLogs(auth()->user()->tenant_id, $filters);

        return response()->json(['success' => true, 'logs' => $logs]);
    }

    public function getUserActivitySummary(Request $request)
    {
        $userId = $request->query('user_id', auth()->id());
        $period = $request->query('period', '7 days');

        $summary = $this->auditLogService->getUserActivitySummary(
            auth()->user()->tenant_id,
            $userId,
            $period
        );

        return response()->json(['success' => true, 'summary' => $summary]);
    }

    public function exportAuditLogs(Request $request)
    {
        $filters = $request->only(['event_type', 'start_date', 'end_date']);
        $csv = $this->auditLogService->exportToCsv(auth()->user()->tenant_id, $filters);

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="audit_logs_' . date('Y-m-d') . '.csv"');
    }

    // ==================== GDPR/PDP COMPLIANCE ====================

    public function getConsents()
    {
        $consents = $this->gdprService->getUserConsents(
            auth()->user()->tenant_id,
            auth()->id()
        );

        return response()->json(['success' => true, 'consents' => $consents]);
    }

    public function grantConsent(Request $request)
    {
        $request->validate([
            'consent_type' => 'required|string',
            'consent_text' => 'required|string',
        ]);

        $success = $this->gdprService->recordConsent(
            auth()->user()->tenant_id,
            auth()->id(),
            $request->consent_type,
            $request->consent_text
        );

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Consent recorded' : 'Failed to record consent',
        ]);
    }

    public function withdrawConsent(Request $request)
    {
        $request->validate([
            'consent_type' => 'required|string',
        ]);

        $success = $this->gdprService->withdrawConsent(
            auth()->user()->tenant_id,
            auth()->id(),
            $request->consent_type
        );

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Consent withdrawn' : 'Failed to withdraw consent',
        ]);
    }

    public function createDataRequest(Request $request)
    {
        $request->validate([
            'request_type' => 'required|in:access,erasure,rectification,portability',
            'details' => 'sometimes|string',
        ]);

        $requestId = match ($request->request_type) {
            'access' => $this->gdprService->createAccessRequest(
                auth()->user()->tenant_id,
                auth()->id(),
                $request->details ?? null
            ),
            'erasure' => $this->gdprService->createErasureRequest(
                auth()->user()->tenant_id,
                auth()->id(),
                $request->details ?? null
            ),
            'rectification' => $this->gdprService->createRectificationRequest(
                auth()->user()->tenant_id,
                auth()->id(),
                $request->details
            ),
            default => throw new \InvalidArgumentException('Invalid request type'),
        };

        return response()->json([
            'success' => true,
            'request_id' => $requestId,
            'message' => 'Data request created successfully',
        ]);
    }

    public function getPendingDataRequests()
    {
        $requests = $this->gdprService->getPendingRequests(auth()->user()->tenant_id);
        return response()->json(['success' => true, 'requests' => $requests]);
    }

    public function processDataRequest(Request $request, int $dataRequestId)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'rejection_reason' => 'sometimes|required_if:action,reject|string',
        ]);

        $dataRequest = \App\Models\DataRequest::findOrFail($dataRequestId);

        if ($request->action === 'approve') {
            if ($dataRequest->request_type === 'access') {
                $result = $this->gdprService->processAccessRequest($dataRequestId, auth()->id());
            } elseif ($dataRequest->request_type === 'erasure') {
                $success = $this->gdprService->processErasureRequest($dataRequestId, auth()->id());
                $result = ['success' => $success];
            }
        } else {
            $dataRequest->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'processed_by_user_id' => auth()->id(),
                'processed_at' => now(),
            ]);

            $result = ['success' => true];
        }

        return response()->json($result);
    }

    // ==================== PERMISSIONS & RBAC ====================

    public function getPermissions()
    {
        $permissions = $this->permissionService->getGroupedPermissions();
        return response()->json(['success' => true, 'permissions' => $permissions]);
    }

    public function getRolePermissions(int $roleId)
    {
        $permissions = $this->permissionService->getRolePermissions($roleId);
        return response()->json(['success' => true, 'permissions' => $permissions]);
    }

    public function assignPermission(Request $request)
    {
        $request->validate([
            'role_id' => 'required|integer',
            'permission_id' => 'required|integer',
        ]);

        $success = $this->permissionService->assignPermissionToRole(
            $request->role_id,
            $request->permission_id
        );

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Permission assigned' : 'Failed to assign permission',
        ]);
    }

    public function syncRolePermissions(Request $request, int $roleId)
    {
        $request->validate([
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'integer',
        ]);

        $success = $this->permissionService->syncRolePermissions(
            $roleId,
            $request->permission_ids
        );

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Permissions synced' : 'Failed to sync permissions',
        ]);
    }

    public function checkPermission(Request $request)
    {
        $request->validate([
            'permission' => 'required|string',
        ]);

        $hasPermission = $this->permissionService->hasPermission(
            auth()->user(),
            $request->permission
        );

        return response()->json([
            'has_permission' => $hasPermission,
        ]);
    }

    // ==================== ENCRYPTION ====================

    public function rotateEncryptionKey(Request $request)
    {
        $request->validate([
            'key_name' => 'required|string',
        ]);

        $success = $this->encryptionService->rotateKey(
            auth()->user()->tenant_id,
            $request->key_name,
            auth()->id()
        );

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Encryption key rotated' : 'Failed to rotate key',
        ]);
    }

    // ==================== SECURITY DASHBOARD ====================

    public function dashboard()
    {
        $tenantId = auth()->user()->tenant_id;

        $overview = [
            'two_factor_enabled_users' => \App\Models\TwoFactorAuth::where('enabled', true)->count(),
            'active_sessions' => \App\Models\UserSession::where('is_active', true)->count(),
            'whitelisted_ips' => \App\Models\IpWhitelist::where('tenant_id', $tenantId)
                ->where('is_active', true)->count(),
            'pending_data_requests' => \App\Models\DataRequest::where('tenant_id', $tenantId)
                ->where('status', 'pending')->count(),
            'recent_security_events' => \App\Models\AuditLogEnhanced::where('tenant_id', $tenantId)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
        ];

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'overview' => $overview]);
        }

        return view('security.dashboard', compact('overview'));
    }
}
