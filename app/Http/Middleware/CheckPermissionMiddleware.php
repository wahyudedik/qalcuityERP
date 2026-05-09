<?php

namespace App\Http\Middleware;

use App\Services\Security\PermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermissionMiddleware
{
    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        if (! $this->permissionService->hasPermission(auth()->user(), $permission)) {
            // Log unauthorized permission attempt
            \Log::warning('Unauthorized permission attempt', [
                'user_id' => auth()->id(),
                'permission' => $permission,
                'url' => $request->fullUrl(),
            ]);

            abort(403, 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
