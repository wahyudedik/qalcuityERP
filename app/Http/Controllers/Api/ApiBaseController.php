<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ApiBaseController extends Controller
{
    protected function ok(mixed $data, string $message = 'OK', int $status = 200): JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $data], $status);
    }

    protected function created(mixed $data, string $message = 'Dibuat.'): JsonResponse
    {
        return $this->ok($data, $message, 201);
    }

    protected function error(string $message, int $status = 400): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message], $status);
    }

    protected function tenantId(): int
    {
        return (int) request()->get('_api_tenant_id');
    }
}
