<?php

namespace App\Services;

use App\Models\ErrorLogEnhanced;
use Illuminate\Support\Facades\Log;

class ActionableErrorService
{
    /**
     * Log error with actionable solutions
     */
    public function logError(string $errorType, string $message, array $context = [], array $solutions = [], string $severity = 'error'): ErrorLogEnhanced
    {
        $errorCode = $this->generateErrorCode($errorType);

        return ErrorLogEnhanced::create([
            'tenant_id' => auth()->user()?->tenant_id,
            'user_id' => auth()->id(),
            'error_type' => $errorType,
            'error_code' => $errorCode,
            'error_message' => $message,
            'context' => $context,
            'suggested_solutions' => $solutions,
            'severity' => $severity,
        ]);
    }

    /**
     * Get user-friendly error message with solutions
     */
    public function getUserFriendlyError(string $errorType, array $context = []): array
    {
        $errorConfig = $this->getErrorConfiguration($errorType);

        return [
            'error_code' => $errorConfig['code'],
            'title' => $errorConfig['title'],
            'message' => $this->formatMessage($errorConfig['message_template'], $context),
            'solutions' => $errorConfig['solutions'],
            'severity' => $errorConfig['severity'],
            'action_required' => $errorConfig['action_required'] ?? false,
        ];
    }

    /**
     * Get recent errors for dashboard
     */
    public function getRecentErrors(int $limit = 20): array
    {
        $tenantId = auth()->user()->tenant_id;

        return ErrorLogEnhanced::where('tenant_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Mark error as resolved
     */
    public function resolveError(int $errorId, string $notes = ''): bool
    {
        $error = ErrorLogEnhanced::find($errorId);

        if (! $error) {
            return false;
        }

        $error->markAsResolved($notes);

        return true;
    }

    /**
     * Get error statistics
     */
    public function getErrorStats(): array
    {
        $tenantId = auth()->user()->tenant_id;

        $total = ErrorLogEnhanced::where('tenant_id', $tenantId)->count();
        $resolved = ErrorLogEnhanced::where('tenant_id', $tenantId)->where('resolved', true)->count();
        $unresolved = $total - $resolved;

        $bySeverity = ErrorLogEnhanced::where('tenant_id', $tenantId)
            ->selectRaw('severity, COUNT(*) as count')
            ->groupBy('severity')
            ->pluck('count', 'severity')
            ->toArray();

        $byType = ErrorLogEnhanced::where('tenant_id', $tenantId)
            ->selectRaw('error_type, COUNT(*) as count')
            ->groupBy('error_type')
            ->orderByDesc('count')
            ->limit(10)
            ->pluck('count', 'error_type')
            ->toArray();

        return [
            'total' => $total,
            'resolved' => $resolved,
            'unresolved' => $unresolved,
            'resolution_rate' => $total > 0 ? round(($resolved / $total) * 100, 2) : 0,
            'by_severity' => $bySeverity,
            'by_type' => $byType,
        ];
    }

    /**
     * Generate unique error code
     */
    protected function generateErrorCode(string $errorType): string
    {
        $prefix = match ($errorType) {
            'validation' => 'VAL',
            'database' => 'DB',
            'api' => 'API',
            'permission' => 'PERM',
            'payment' => 'PAY',
            default => 'ERR'
        };

        return "{$prefix}-".strtoupper(substr(md5($errorType.time()), 0, 8));
    }

    /**
     * Format message with context
     */
    protected function formatMessage(string $template, array $context): string
    {
        foreach ($context as $key => $value) {
            $template = str_replace("{{$key}}", $value, $template);
        }

        return $template;
    }

    /**
     * Get error configuration
     */
    protected function getErrorConfiguration(string $errorType): array
    {
        return match ($errorType) {
            'insufficient_stock' => [
                'code' => 'VAL-STOCK-001',
                'title' => 'Stok Tidak Mencukupi',
                'message_template' => 'Stok produk "{product}" tidak cukup. Dibutuhkan: {required}, Tersedia: {available}',
                'solutions' => [
                    'Tambah stok produk terlebih dahulu',
                    'Kurangi quantity pada transaksi',
                    'Pilih produk alternatif',
                ],
                'severity' => 'error',
                'action_required' => true,
            ],

            'payment_failed' => [
                'code' => 'PAY-FAIL-001',
                'title' => 'Pembayaran Gagal',
                'message_template' => 'Pembayaran sebesar Rp {amount} gagal diproses',
                'solutions' => [
                    'Periksa koneksi internet Anda',
                    'Coba metode pembayaran lain',
                    'Hubungi bank jika masalah berlanjut',
                ],
                'severity' => 'error',
                'action_required' => true,
            ],

            'permission_denied' => [
                'code' => 'PERM-DENY-001',
                'title' => 'Akses Ditolak',
                'message_template' => 'Anda tidak memiliki izin untuk {action}',
                'solutions' => [
                    'Hubungi administrator untuk meminta akses',
                    'Gunakan akun dengan role yang sesuai',
                ],
                'severity' => 'warning',
                'action_required' => false,
            ],

            'database_error' => [
                'code' => 'DB-ERR-001',
                'title' => 'Database Error',
                'message_template' => 'Terjadi kesalahan saat mengakses database',
                'solutions' => [
                    'Coba lagi dalam beberapa saat',
                    'Hubungi technical support jika masalah berlanjut',
                ],
                'severity' => 'critical',
                'action_required' => true,
            ],

            default => [
                'code' => 'ERR-UNK-001',
                'title' => 'Error Tidak Dikenal',
                'message_template' => $errorType,
                'solutions' => ['Hubungi support team'],
                'severity' => 'error',
                'action_required' => true,
            ]
        };
    }
}
