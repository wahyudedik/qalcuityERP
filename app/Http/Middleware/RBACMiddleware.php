<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RBACMiddleware
{
    /**
     * Role-permission mapping for healthcare module
     */
    protected array $rolePermissions = [
        'superadmin' => ['*'], // All permissions
        'admin' => [
            'healthcare.*',
            'healthcare.patients.*',
            'healthcare.doctors.*',
            'healthcare.staff.*',
            'healthcare.reports.*',
            'healthcare.settings.*',
        ],
        'doctor' => [
            'healthcare.access',
            'healthcare.patients.view',
            'healthcare.patients.edit',
            'healthcare.emr.*',
            'healthcare.prescriptions.*',
            'healthcare.lab-orders.*',
            'healthcare.appointments.*',
            'healthcare.surgery.*',
        ],
        'nurse' => [
            'healthcare.access',
            'healthcare.patients.view',
            'healthcare.emr.view',
            'healthcare.vitals.*',
            'healthcare.appointments.view',
            'healthcare.queue.*',
        ],
        'receptionist' => [
            'healthcare.access',
            'healthcare.patients.view',
            'healthcare.patients.create',
            'healthcare.appointments.*',
            'healthcare.queue.*',
            'healthcare.billing.view',
        ],
        'pharmacist' => [
            'healthcare.access',
            'healthcare.pharmacy.*',
            'healthcare.prescriptions.view',
            'healthcare.prescriptions.fulfill',
        ],
        'lab_technician' => [
            'healthcare.access',
            'healthcare.laboratory.*',
            'healthcare.lab-orders.view',
            'healthcare.lab-results.*',
        ],
        'radiologist' => [
            'healthcare.access',
            'healthcare.radiology.*',
            'healthcare.radiology-reports.*',
        ],
        'billing_staff' => [
            'healthcare.access',
            'healthcare.billing.*',
            'healthcare.insurance.*',
            'healthcare.patients.view',
        ],
        'patient' => [
            'healthcare.portal.*',
            'healthcare.portal.records.view',
            'healthcare.portal.appointments.*',
            'healthcare.portal.billing.view',
        ],
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $requiredPermission): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Authentication required.');
        }

        // Superadmin bypass
        if ($user->hasRole('superadmin') || $user->is_superadmin) {
            return $next($request);
        }

        // Check if user has the required permission
        if (!$this->hasPermission($user, $requiredPermission)) {
            // Log unauthorized access attempt
            \Illuminate\Support\Facades\Log::warning('Unauthorized healthcare access attempt', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'required_permission' => $requiredPermission,
                'url' => $request->fullUrl(),
                'ip_address' => $request->ip(),
            ]);

            abort(403, 'Insufficient permissions. You do not have access to this resource.');
        }

        return $next($request);
    }

    /**
     * Check if user has the required permission
     */
    protected function hasPermission($user, string $permission): bool
    {
        // Check if user has permission directly
        if ($user->can($permission)) {
            return true;
        }

        // Check role-based permissions
        $userRoles = $user->roles->pluck('name')->toArray();

        foreach ($userRoles as $role) {
            if (isset($this->rolePermissions[$role])) {
                $permissions = $this->rolePermissions[$role];

                // Check for wildcard permission
                if (in_array('*', $permissions)) {
                    return true;
                }

                // Check exact match
                if (in_array($permission, $permissions)) {
                    return true;
                }

                // Check wildcard patterns (e.g., healthcare.patients.*)
                foreach ($permissions as $perm) {
                    if (str_ends_with($perm, '.*')) {
                        $pattern = substr($perm, 0, -1); // Remove the '*'
                        if (str_starts_with($permission, $pattern)) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Get permissions for a specific role
     */
    public static function getRolePermissions(string $role): array
    {
        $instance = new self();
        return $instance->rolePermissions[$role] ?? [];
    }

    /**
     * Get all available roles
     */
    public static function getAvailableRoles(): array
    {
        $instance = new self();
        return array_keys($instance->rolePermissions);
    }
}
