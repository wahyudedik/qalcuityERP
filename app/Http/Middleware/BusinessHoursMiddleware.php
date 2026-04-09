<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class BusinessHoursMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $mode = 'warn'): Response
    {
        $user = $request->user();

        // Skip if not authenticated
        if (!$user) {
            return $next($request);
        }

        // Check if access is outside business hours
        if ($this->isOutsideBusinessHours()) {
            $businessHours = $this->getBusinessHours();

            // Log after-hours access
            Log::channel('healthcare_audit')->info('After-Hours Access', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->roles->pluck('name')->join(', '),
                'access_time' => now()->toDateTimeString(),
                'business_hours' => $businessHours,
                'url' => $request->fullUrl(),
                'ip_address' => $request->ip(),
            ]);

            // Add metadata to request
            $request->merge([
                'after_hours_access' => true,
                'business_hours_config' => $businessHours,
            ]);

            // Handle based on mode
            switch ($mode) {
                case 'block':
                    // Block access outside business hours (except emergency)
                    if (!$this->isEmergencyAccess($request)) {
                        return response()->view('errors.healthcare-after-hours', [
                            'business_hours' => $businessHours,
                            'current_time' => now(),
                            'message' => 'Access to this resource is restricted to business hours only.',
                        ], 403);
                    }
                    break;

                case 'warn':
                    // Allow but add warning to session
                    session()->flash(
                        'warning',
                        'You are accessing the system outside of business hours (' .
                        $businessHours['display'] . '). This access has been logged.'
                    );
                    break;

                case 'log':
                default:
                    // Only log, no warning or block
                    break;
            }

            // Alert security team for sensitive operations
            if ($this->isSensitiveOperation($request)) {
                $this->alertSecurityTeam($user, $request);
            }
        }

        return $next($request);
    }

    /**
     * Check if current time is outside business hours
     */
    protected function isOutsideBusinessHours(): bool
    {
        $now = now();
        $dayOfWeek = $now->dayOfWeek; // 0 = Sunday, 6 = Saturday

        // Check if weekends are restricted
        $allowWeekends = config('healthcare.business_hours.allow_weekends', false);
        if (!$allowWeekends && ($dayOfWeek === 0 || $dayOfWeek === 6)) {
            return true;
        }

        // Get business hours from config
        $startHour = config('healthcare.business_hours.start', 8);
        $endHour = config('healthcare.business_hours.end', 18);
        $currentHour = $now->hour;

        return $currentHour < $startHour || $currentHour >= $endHour;
    }

    /**
     * Get business hours configuration
     */
    protected function getBusinessHours(): array
    {
        $startHour = config('healthcare.business_hours.start', 8);
        $endHour = config('healthcare.business_hours.end', 18);
        $allowWeekends = config('healthcare.business_hours.allow_weekends', false);

        return [
            'start' => $startHour,
            'end' => $endHour,
            'allow_weekends' => $allowWeekends,
            'display' => sprintf(
                '%s - %s%s',
                str_pad($startHour, 2, '0', STR_PAD_LEFT) . ':00',
                str_pad($endHour, 2, '0', STR_PAD_LEFT) . ':00',
                $allowWeekends ? ' (Mon-Sun)' : ' (Mon-Fri)'
            ),
        ];
    }

    /**
     * Check if this is emergency access (ER, critical patients, etc.)
     */
    protected function isEmergencyAccess(Request $request): bool
    {
        // Check if accessing ER module
        if (
            str_contains($request->path(), 'emergency') ||
            str_contains($request->path(), 'er')
        ) {
            return true;
        }

        // Check if accessing critical patient
        $patientId = $request->route('patient') ?? $request->route('id');
        if ($patientId) {
            $patient = \App\Models\Patient::find($patientId);
            if ($patient && $patient->is_critical) {
                return true;
            }
        }

        // Check if user has emergency access role
        $user = $request->user();
        if ($user && ($user->hasRole('doctor') || $user->hasRole('emergency_staff'))) {
            return $request->has('emergency_access') || $request->has('override_reason');
        }

        return false;
    }

    /**
     * Check if operation is sensitive (requires extra monitoring)
     */
    protected function isSensitiveOperation(Request $request): bool
    {
        $sensitiveOperations = [
            'DELETE',
            'PUT',
            'PATCH',
        ];

        $sensitiveRoutes = [
            'healthcare.emr.delete',
            'healthcare.prescriptions.delete',
            'healthcare.lab-results.delete',
            'healthcare.patients.delete',
            'healthcare.billing.void',
        ];

        $routeName = $request->route()->getName();

        return in_array($request->method(), $sensitiveOperations) ||
            in_array($routeName, $sensitiveRoutes) ||
            str_contains($request->path(), 'delete') ||
            str_contains($request->path(), 'void');
    }

    /**
     * Alert security team about sensitive after-hours access
     */
    protected function alertSecurityTeam($user, Request $request): void
    {
        Log::channel('healthcare_security')->critical('Sensitive After-Hours Operation', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_role' => $user->roles->pluck('name')->join(', '),
            'operation' => $request->method(),
            'route' => $request->route()->getName(),
            'url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'timestamp' => now()->toDateTimeString(),
        ]);

        // Send notification if configured
        if (config('healthcare.security.notify_after_hours', false)) {
            try {
                // Notification logic here (email, SMS, webhook, etc.)
                // Notification::route('mail', config('healthcare.security.alert_email'))
                //     ->notify(new AfterHoursAccessAlert($user, $request));
            } catch (\Exception $e) {
                Log::error('Failed to send after-hours alert: ' . $e->getMessage());
            }
        }
    }
}
