<?php

namespace App\Http\Controllers\Api\Telecom;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Base controller for Telecom API endpoints.
 */
abstract class TelecomApiController extends Controller
{
    /**
     * Return success response.
     */
    protected function success($data = [], string $message = 'Success', int $statusCode = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Return error response.
     */
    protected function error(string $message, int $statusCode = 400, array $errors = [])
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (! empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Log API request.
     */
    protected function logApiRequest(Request $request, string $endpoint, mixed $result = null): void
    {
        Log::channel('daily')->info('Telecom API Request', [
            'endpoint' => $endpoint,
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_id' => auth()->id(),
            'tenant_id' => auth()->user()?->tenant_id,
            'result' => $result,
        ]);
    }
}
