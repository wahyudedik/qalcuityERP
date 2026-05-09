<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * SEC-004: File Upload Validation Middleware
 *
 * Validates file uploads to prevent malicious file uploads (PHP files, etc.)
 * Ensures consistent MIME type validation across all upload endpoints.
 */
class ValidateFileUpload
{
    /**
     * Allowed MIME types mapping
     */
    const ALLOWED_MIME_TYPES = [
        'image' => [
            'mimes' => 'jpg,jpeg,png,gif,webp,svg',
            'mimetypes' => 'image/jpeg,image/png,image/gif,image/webp,image/svg+xml',
            'max' => 10240, // 10MB
        ],
        'document' => [
            'mimes' => 'pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv',
            'mimetypes' => 'application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,text/plain,text/csv',
            'max' => 20480, // 20MB
        ],
        'spreadsheet' => [
            'mimes' => 'csv,txt,xlsx,xls',
            'mimetypes' => 'text/csv,text/plain,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'max' => 10240, // 10MB
        ],
    ];

    /**
     * Dangerous file extensions that should NEVER be allowed
     */
    const DANGEROUS_EXTENSIONS = [
        'php',
        'php3',
        'php4',
        'php5',
        'php7',
        'php8',
        'phtml',
        'exe',
        'bat',
        'cmd',
        'com',
        'scr',
        'sh',
        'bash',
        'js',
        'jsx',
        'ts',
        'tsx', // Could contain XSS
        'html',
        'htm', // Could contain scripts
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $fileType = 'document'): Response
    {
        // Check if request has files
        if (! $request->hasFile('file') && ! $request->hasFile('files') && ! $request->hasFile('image')) {
            return $next($request);
        }

        // Get validation rules based on file type
        $rules = $this->getValidationRules($fileType);

        // Validate file(s)
        try {
            $request->validate($rules);
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'File validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }

            return back()->withErrors($e->errors())->withInput();
        }

        // Additional security check: Verify no dangerous extensions
        $this->checkDangerousFiles($request);

        return $next($request);
    }

    /**
     * Get validation rules based on file type
     */
    protected function getValidationRules(string $fileType): array
    {
        $config = self::ALLOWED_MIME_TYPES[$fileType] ?? self::ALLOWED_MIME_TYPES['document'];

        return [
            'file' => "required|file|mimes:{$config['mimes']}|mimetypes:{$config['mimetypes']}|max:{$config['max']}",
            'files.*' => "required|file|mimes:{$config['mimes']}|mimetypes:{$config['mimetypes']}|max:{$config['max']}",
            'image' => "required|image|mimes:{$config['mimes']}|mimetypes:{$config['mimetypes']}|max:{$config['max']}",
        ];
    }

    /**
     * Check for dangerous file extensions
     */
    protected function checkDangerousFiles(Request $request): void
    {
        $files = array_merge(
            $request->file('file') ? [$request->file('file')] : [],
            $request->file('files') ?: [],
            $request->file('image') ? [$request->file('image')] : [],
        );

        foreach ($files as $file) {
            if (! $file) {
                continue;
            }

            $extension = strtolower($file->getClientOriginalExtension());

            if (in_array($extension, self::DANGEROUS_EXTENSIONS)) {
                abort(403, "File type .{$extension} is not allowed for security reasons.");
            }

            // Double-check MIME type matches extension
            $detectedMimeType = $file->getMimeType();
            $allowedMimeTypes = $this->getAllAllowedMimeTypes();

            if (! in_array($detectedMimeType, $allowedMimeTypes)) {
                abort(403, "File MIME type '{$detectedMimeType}' is not allowed.");
            }
        }
    }

    /**
     * Get all allowed MIME types
     */
    protected function getAllAllowedMimeTypes(): array
    {
        $mimeTypes = [];
        foreach (self::ALLOWED_MIME_TYPES as $config) {
            $mimeTypes = array_merge($mimeTypes, explode(',', $config['mimetypes']));
        }

        return array_unique($mimeTypes);
    }
}
