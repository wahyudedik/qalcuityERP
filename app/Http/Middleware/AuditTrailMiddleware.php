<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuditTrailMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $resourceType = 'medical_record'): Response
    {
        $user = $request->user();

        // Log access before processing request
        if ($user) {
            $logData = [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->roles->pluck('name')->join(', '),
                'tenant_id' => $user->tenant_id,
                'action' => $request->method(),
                'resource_type' => $resourceType,
                'resource_id' => $request->route('patient') ?? $request->route('record') ?? $request->route('id'),
                'url' => $request->fullUrl(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toDateTimeString(),
                'is_after_hours' => $this->isAfterHours(),
            ];

            // Log to database if AuditLog model exists
            if (class_exists(\App\Models\AuditLog::class)) {
                try {
                    \App\Models\AuditLog::create([
                        'user_id' => $user->id,
                        'tenant_id' => $user->tenant_id,
                        'action' => $request->method(),
                        'resource_type' => $resourceType,
                        'resource_id' => $logData['resource_id'],
                        'description' => "Accessed {$resourceType} via {$request->method()} request",
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'metadata' => json_encode([
                            'route' => $request->route()->getName(),
                            'is_after_hours' => $this->isAfterHours(),
                            'session_id' => $request->session()->getId(),
                        ]),
                    ]);
                } catch (\Exception $e) {
                    // Fallback to log file if database insert fails
                    Log::warning('Failed to create audit log entry: ' . $e->getMessage());
                }
            }

            // Log to file for compliance
            Log::channel('healthcare_audit')->info('Medical Record Access', $logData);

            // Alert if after hours access
            if ($this->isAfterHours()) {
                Log::channel('healthcare_audit')->warning('After Hours Access Detected', [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'resource_type' => $resourceType,
                    'timestamp' => now()->toDateTimeString(),
                ]);

                // Add flag to request for controllers to handle
                $request->merge(['after_hours_access' => true]);
            }

            // Alert if accessing patient outside assigned department
            if ($request->route('patient')) {
                $patient = \App\Models\Patient::find($request->route('patient'));

                if ($patient && $user->department_id && $patient->assigned_department_id) {
                    if ($user->department_id !== $patient->assigned_department_id) {
                        Log::channel('healthcare_audit')->warning('Cross-Department Patient Access', [
                            'user_id' => $user->id,
                            'user_department' => $user->department_id,
                            'patient_id' => $patient->id,
                            'patient_department' => $patient->assigned_department_id,
                        ]);
                    }
                }
            }
        }

        $response = $next($request);

        // Log response status
        if ($user && config('app.log_response_status', false)) {
            Log::channel('healthcare_audit')->info('Request Completed', [
                'user_id' => $user->id,
                'status_code' => $response->getStatusCode(),
                'url' => $request->fullUrl(),
            ]);
        }

        return $response;
    }

    /**
     * Check if current time is outside business hours
     */
    protected function isAfterHours(): bool
    {
        $now = now();
        $dayOfWeek = $now->dayOfWeek; // 0 = Sunday, 6 = Saturday
        $hour = $now->hour;

        // Weekend check
        if ($dayOfWeek === 0 || $dayOfWeek === 6) {
            return true;
        }

        // Business hours: 8 AM - 6 PM (configurable)
        $businessStart = config('healthcare.business_hours.start', 8);
        $businessEnd = config('healthcare.business_hours.end', 18);

        return $hour < $businessStart || $hour >= $businessEnd;
    }
}
