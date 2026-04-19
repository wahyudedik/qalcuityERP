<?php

namespace App\Services\Agent;

use App\DTOs\Agent\AgentStep;
use App\DTOs\Agent\ExecutionContext;
use App\DTOs\Agent\StepResult;
use App\DTOs\Agent\UndoResult;
use App\Models\AgentAuditLog;
use App\Models\User;
use App\Services\ERP\ToolRegistry;
use App\Services\PermissionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * AgentExecutor - Task 5
 *
 * Mengeksekusi setiap langkah dalam AgentPlan, mengelola context propagation
 * antar langkah, validasi permission, penolakan operasi destruktif, dan undo.
 *
 * Requirements: 1.3, 1.4, 6.1, 6.2, 6.3, 6.5, 6.6, 9.3
 */
class AgentExecutor
{
    /** Timeout eksekusi tool dalam detik */
    private const TOOL_TIMEOUT_SECONDS = 10;

    /** Window undo dalam detik (5 menit) */
    private const UNDO_WINDOW_SECONDS = 300;

    /**
     * Pola nama tool yang dianggap destruktif dan harus ditolak.
     */
    private const DESTRUCTIVE_TOOL_PATTERNS = [
        'bulk_delete',
        'mass_delete',
        'delete_all',
        'purge',
        'truncate',
        'wipe',
    ];

    /** User yang sedang mengeksekusi (untuk permission check dan audit log). */
    private ?User $user = null;

    /** Tenant ID untuk audit log. */
    private ?int $tenantId = null;

    /** Session ID untuk audit log (opsional). */
    private ?int $sessionId = null;

    /**
     * Set user context untuk permission checking dan audit logging.
     */
    public function setUser(User $user, int $tenantId, ?int $sessionId = null): void
    {
        $this->user      = $user;
        $this->tenantId  = $tenantId;
        $this->sessionId = $sessionId;
    }

    /**
     * Eksekusi satu langkah dari plan.
     *
     * Urutan:
     * 1. Tolak operasi destruktif
     * 2. Validasi permission user
     * 3. Resolve placeholder args dari ExecutionContext
     * 4. Eksekusi tool via ToolRegistry dengan timeout 10 detik
     * 5. Catat hasil ke AgentAuditLog (untuk write ops)
     * 6. Simpan output ke ExecutionContext
     */
    public function executeStep(
        AgentStep $step,
        ExecutionContext $context,
        ToolRegistry $registry,
    ): StepResult {
        // 1. Tolak operasi destruktif sebelum apapun
        $destructiveCheck = $this->checkDestructiveOperation($step);
        if ($destructiveCheck !== null) {
            return new StepResult(
                stepOrder: $step->order,
                status: 'failed',
                output: null,
                errorMessage: $destructiveCheck,
            );
        }

        // 2. Validasi permission user
        $permissionCheck = $this->checkPermission($step);
        if ($permissionCheck !== null) {
            return new StepResult(
                stepOrder: $step->order,
                status: 'failed',
                output: null,
                errorMessage: $permissionCheck,
            );
        }

        // 3. Resolve placeholder args
        $resolvedArgs = $this->resolveArgs($step->args, $context);

        // 4. Eksekusi tool dengan timeout
        $startTime = microtime(true);
        $result    = null;
        $error     = null;

        try {
            $result = $this->executeWithTimeout(
                fn() => $registry->execute($step->toolName, $resolvedArgs),
                self::TOOL_TIMEOUT_SECONDS
            );
        } catch (TimeoutException $e) {
            $error = "Eksekusi tool '{$step->toolName}' melebihi batas waktu " . self::TOOL_TIMEOUT_SECONDS . " detik.";
            Log::warning('AgentExecutor: tool timeout', [
                'tool'      => $step->toolName,
                'step'      => $step->order,
                'elapsed_s' => round(microtime(true) - $startTime, 2),
            ]);
        } catch (\Throwable $e) {
            $error = "Eksekusi tool '{$step->toolName}' gagal: " . $e->getMessage();
            Log::error('AgentExecutor: tool execution error', [
                'tool'  => $step->toolName,
                'step'  => $step->order,
                'error' => $e->getMessage(),
            ]);
        }

        // Tentukan status berdasarkan hasil
        $isSuccess = $error === null
            && $result !== null
            && ($result['status'] ?? '') === 'success';

        $status = $isSuccess ? 'success' : 'failed';

        if ($error === null && $result !== null && ($result['status'] ?? '') !== 'success') {
            $error = $result['message'] ?? "Tool '{$step->toolName}' mengembalikan status gagal.";
        }

        // 5. Catat ke AgentAuditLog untuk write operations
        if ($step->isWriteOp) {
            $this->writeAuditLog(
                step: $step,
                args: $resolvedArgs,
                result: $result ?? [],
                status: $status,
                errorMessage: $error,
            );
        }

        // 6. Simpan output ke ExecutionContext jika berhasil
        if ($isSuccess) {
            $context->set($step->order, $result);
        }

        return new StepResult(
            stepOrder: $step->order,
            status: $status,
            output: $isSuccess ? $result : null,
            errorMessage: $error,
        );
    }

    /**
     * Resolve args dengan mengganti placeholder {{step_N.field}} dengan nilai
     * aktual dari ExecutionContext.
     * Contoh: {{step_1.product_id}} -> nilai product_id dari output langkah 1.
     */
    public function resolveArgs(array $args, ExecutionContext $context): array
    {
        $resolved = [];

        foreach ($args as $key => $value) {
            $resolved[$key] = $this->resolveValue($value, $context);
        }

        return $resolved;
    }

    /**
     * Cek apakah aksi dapat di-undo.
     * True jika is_undoable = true dan undoable_until belum lewat.
     */
    public function canUndo(AgentAuditLog $log): bool
    {
        if (!$log->is_undoable) {
            return false;
        }

        if ($log->undoable_until === null) {
            return false;
        }

        return Carbon::now()->lessThanOrEqualTo($log->undoable_until);
    }

    /**
     * Undo aksi write yang dieksekusi dalam window 5 menit.
     */
    public function undo(AgentAuditLog $log, ToolRegistry $registry): UndoResult
    {
        if (!$this->canUndo($log)) {
            $reason = !$log->is_undoable
                ? "Aksi '{$log->action_name}' tidak mendukung undo."
                : "Window undo 5 menit untuk aksi '{$log->action_name}' sudah berakhir.";

            return new UndoResult(
                success: false,
                message: $reason,
            );
        }

        $undoToolName = 'undo_' . $log->action_name;

        try {
            if ($registry->isWriteOperation($log->action_name)) {
                $log->update(['status' => 'undone']);

                $undoResult = null;
                try {
                    $undoResult = $registry->execute($undoToolName, [
                        'original_log_id' => $log->id,
                        'original_args'   => $log->parameters ?? [],
                        'original_result' => $log->result ?? [],
                    ]);
                } catch (\Throwable) {
                    // Undo tool tidak ada - status sudah ditandai undone
                }

                return new UndoResult(
                    success: true,
                    message: "Aksi '{$log->action_name}' berhasil di-undo.",
                    restoredData: $undoResult ?? $log->result,
                );
            }

            return new UndoResult(
                success: false,
                message: "Aksi '{$log->action_name}' bukan operasi write, tidak dapat di-undo.",
            );
        } catch (\Throwable $e) {
            Log::error('AgentExecutor: undo gagal', [
                'log_id' => $log->id,
                'action' => $log->action_name,
                'error'  => $e->getMessage(),
            ]);

            return new UndoResult(
                success: false,
                message: "Undo gagal: " . $e->getMessage(),
            );
        }
    }

    // --- Private: Destructive Check ---

    /**
     * Cek apakah step mengandung operasi destruktif.
     * Return pesan error jika destruktif, null jika aman.
     */
    private function checkDestructiveOperation(AgentStep $step): ?string
    {
        $toolNameLower = strtolower($step->toolName);

        foreach (self::DESTRUCTIVE_TOOL_PATTERNS as $pattern) {
            if (str_contains($toolNameLower, $pattern)) {
                return "Operasi '{$step->toolName}' ditolak karena termasuk operasi destruktif "
                    . "(bulk delete / modifikasi data historis terkunci) yang tidak diizinkan.";
            }
        }

        if ($this->isLockedPeriodModification($step)) {
            return "Operasi '{$step->toolName}' ditolak karena mencoba memodifikasi data "
                . "pada periode akuntansi yang sudah dikunci.";
        }

        return null;
    }

    /**
     * Deteksi apakah step mencoba memodifikasi data pada periode akuntansi terkunci.
     */
    private function isLockedPeriodModification(AgentStep $step): bool
    {
        if (!$step->isWriteOp) {
            return false;
        }

        $args = $step->args;

        if (isset($args['locked_period']) && $args['locked_period'] === true) {
            return true;
        }

        if (isset($args['period_status']) && $args['period_status'] === 'locked') {
            return true;
        }

        return false;
    }

    // --- Private: Permission Check ---

    /**
     * Cek permission user untuk mengeksekusi step.
     * Return pesan error jika tidak berwenang, null jika berwenang.
     */
    private function checkPermission(AgentStep $step): ?string
    {
        if ($this->user === null) {
            return null;
        }

        $requiredPermission = $this->resolveRequiredPermission($step);

        if ($requiredPermission === null) {
            return null;
        }

        $parts  = explode('.', $requiredPermission);
        $action = array_pop($parts);
        $module = implode('.', $parts) ?: $requiredPermission;

        $permissionService = app(PermissionService::class);

        if (! $permissionService->check($this->user, $module, $action)) {
            return "Akses ditolak: Anda memerlukan permission '{$requiredPermission}' "
                . "untuk mengeksekusi aksi '{$step->toolName}'.";
        }

        return null;
    }

    /**
     * Resolve permission yang diperlukan untuk sebuah step.
     * Return null jika tidak ada permission khusus yang diperlukan.
     */
    private function resolveRequiredPermission(AgentStep $step): ?string
    {
        $permissionMap = [
            'create_journal'        => 'accounting.journal.create',
            'post_journal'          => 'accounting.journal.post',
            'create_invoice'        => 'sales.invoice.create',
            'create_purchase_order' => 'purchasing.po.create',
            'run_payroll'           => 'hrm.payroll.run',
            'delete_'               => 'admin.delete',
            'adjust_stock'          => 'inventory.stock.adjust',
            'create_employee'       => 'hrm.employee.create',
            'update_employee'       => 'hrm.employee.update',
        ];

        $toolName = $step->toolName;

        if (isset($permissionMap[$toolName])) {
            return $permissionMap[$toolName];
        }

        foreach ($permissionMap as $prefix => $permission) {
            if (str_starts_with($toolName, $prefix)) {
                return $permission;
            }
        }

        return null;
    }

    // --- Private: Timeout Execution ---

    /**
     * Eksekusi callable dengan timeout.
     * Menggunakan pcntl_alarm jika tersedia, fallback ke time check.
     *
     * @throws TimeoutException jika melebihi timeout
     */
    private function executeWithTimeout(callable $fn, int $timeoutSeconds): mixed
    {
        if (function_exists('pcntl_alarm') && function_exists('pcntl_signal')) {
            return $this->executeWithPcntlTimeout($fn, $timeoutSeconds);
        }

        $start   = microtime(true);
        $result  = $fn();
        $elapsed = microtime(true) - $start;

        if ($elapsed > $timeoutSeconds) {
            throw new TimeoutException(
                "Eksekusi melebihi batas waktu {$timeoutSeconds} detik (aktual: " . round($elapsed, 2) . " detik)."
            );
        }

        return $result;
    }

    /**
     * Eksekusi dengan pcntl_alarm untuk interrupt yang sesungguhnya.
     */
    private function executeWithPcntlTimeout(callable $fn, int $timeoutSeconds): mixed
    {
        $timedOut = false;

        pcntl_signal(SIGALRM, function () use (&$timedOut) {
            $timedOut = true;
        });

        pcntl_alarm($timeoutSeconds);

        try {
            $result = $fn();
        } finally {
            pcntl_alarm(0);
            pcntl_signal(SIGALRM, SIG_DFL);
        }

        if ($timedOut) {
            throw new TimeoutException("Eksekusi melebihi batas waktu {$timeoutSeconds} detik.");
        }

        return $result;
    }

    // --- Private: Audit Log ---

    /**
     * Tulis AgentAuditLog untuk write operation.
     */
    private function writeAuditLog(
        AgentStep $step,
        array $args,
        array $result,
        string $status,
        ?string $errorMessage,
    ): void {
        if ($this->user === null || $this->tenantId === null) {
            Log::warning('AgentExecutor: tidak dapat menulis audit log - user/tenantId tidak di-set', [
                'tool' => $step->toolName,
            ]);
            return;
        }

        try {
            $isUndoable    = $status === 'success';
            $undoableUntil = $isUndoable
                ? Carbon::now()->addSeconds(self::UNDO_WINDOW_SECONDS)
                : null;

            AgentAuditLog::create([
                'tenant_id'      => $this->tenantId,
                'user_id'        => $this->user->id,
                'session_id'     => $this->sessionId,
                'action_name'    => $step->toolName,
                'action_type'    => 'write',
                'parameters'     => $args,
                'result'         => $result,
                'status'         => $status,
                'error_message'  => $errorMessage,
                'is_undoable'    => $isUndoable,
                'undoable_until' => $undoableUntil,
            ]);
        } catch (\Throwable $e) {
            Log::error('AgentExecutor: gagal menulis audit log', [
                'tool'  => $step->toolName,
                'error' => $e->getMessage(),
            ]);
        }
    }

    // --- Private: Arg Resolution ---

    /**
     * Resolve satu nilai (rekursif untuk nested array).
     */
    private function resolveValue(mixed $value, ExecutionContext $context): mixed
    {
        if (is_string($value)) {
            return $this->resolvePlaceholders($value, $context);
        }

        if (is_array($value)) {
            return $this->resolveArgs($value, $context);
        }

        return $value;
    }

    /**
     * Ganti semua placeholder {{step_N.field}} dalam string dengan nilai aktual.
     */
    private function resolvePlaceholders(string $value, ExecutionContext $context): mixed
    {
        if (preg_match('/^\{\{step_(\d+)\.([^}]+)\}\}$/', $value, $matches)) {
            return $this->extractFromContext((int) $matches[1], $matches[2], $context);
        }

        return preg_replace_callback(
            '/\{\{step_(\d+)\.([^}]+)\}\}/',
            function (array $m) use ($context): string {
                $extracted = $this->extractFromContext((int) $m[1], $m[2], $context);
                return is_scalar($extracted) ? (string) $extracted : '';
            },
            $value
        );
    }

    /**
     * Ekstrak nilai dari output langkah tertentu di ExecutionContext.
     * Mendukung dot-notation untuk nested fields (misal: data.product_id).
     */
    private function extractFromContext(int $stepOrder, string $field, ExecutionContext $context): mixed
    {
        $output = $context->get($stepOrder);

        if ($output === null) {
            return null;
        }

        $parts   = explode('.', $field);
        $current = $output;

        foreach ($parts as $part) {
            if (is_array($current) && array_key_exists($part, $current)) {
                $current = $current[$part];
            } elseif (is_object($current) && property_exists($current, $part)) {
                $current = $current->$part;
            } else {
                return null;
            }
        }

        return $current;
    }
}

/**
 * Exception untuk timeout eksekusi tool.
 */
class TimeoutException extends \RuntimeException {}
