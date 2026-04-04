<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * VerifyCsrfForUploads - Enforce CSRF token verification for file upload requests.
 * 
 * This middleware provides an additional layer of CSRF protection specifically
 * for file upload endpoints, ensuring that all file uploads include a valid
 * CSRF token.
 */
class VerifyCsrfForUploads
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to POST/PUT/PATCH requests with files
        if ($this->isFileUploadRequest($request)) {
            // Laravel's built-in CSRF verification should already catch this,
            // but we add an explicit check for file uploads as defense in depth
            if (!$request->hasValidSignature() && !$this->hasValidCsrfToken($request)) {
                return response()->json([
                    'error' => 'csrf_token_missing',
                    'message' => 'Token CSRF tidak valid atau hilang. Harap refresh halaman dan coba lagi.',
                ], 403);
            }
        }

        return $next($request);
    }

    /**
     * Check if the request contains file uploads.
     */
    protected function isFileUploadRequest(Request $request): bool
    {
        // Check if request method can contain files
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            return false;
        }

        // Check if request has files
        if ($request->files->count() > 0) {
            return true;
        }

        // Check for file fields in specific patterns
        $fileFields = ['file', 'files', 'attachment', 'attachments', 'document', 'image', 'images'];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verify CSRF token using Laravel's built-in mechanism.
     */
    protected function hasValidCsrfToken(Request $request): bool
    {
        $token = $request->input('_token')
            ?? $request->header('X-CSRF-TOKEN')
            ?? $request->header('X-XSRF-TOKEN');

        if (!$token) {
            return false;
        }

        return hash_equals(session()->token(), $token);
    }
}
