<?php

namespace App\Http\Controllers;

use App\Models\AgentAuditLog;
use App\Models\ChatSession;
use App\Models\ProactiveInsight;
use App\Services\Agent\AgentExecutor;
use App\Services\Agent\AgentOrchestrator;
use App\Services\Agent\ProactiveInsightEngine;
use App\Services\AiMemoryService;
use App\Services\ERP\ToolRegistry;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * AgentController — Task 12
 *
 * HTTP layer untuk ERP AI Agent. Menangani semua request agent:
 * streaming SSE, non-streaming, konfirmasi, pembatalan, undo,
 * proactive insights, dan manajemen memori user.
 *
 * Requirements: 7.1, 7.2, 7.6, 5.6, 4.4, 6.6
 */
class AgentController extends Controller
{
    public function __construct(
        private readonly AgentOrchestrator    $orchestrator,
        private readonly AgentExecutor        $executor,
        private readonly ProactiveInsightEngine $insightEngine,
        private readonly AiMemoryService      $memoryService,
    ) {}

    /**
     * POST /agent/send
     * Kirim pesan ke agent (non-streaming). Mengembalikan JsonResponse.
     *
     * Requirements: 7.1, 7.6
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'message'    => 'required|string|max:4000',
            'session_id' => 'nullable|integer|exists:chat_sessions,id',
            'confirmed'  => 'nullable|boolean',
        ]);

        $user      = $request->user();
        $session   = $this->resolveSession($user, $request->session_id);
        $confirmed = (bool) $request->input('confirmed', false);

        try {
            $generator = $this->orchestrator->handle(
                message: $request->message,
                user: $user,
                session: $session,
                confirmed: $confirmed,
            );

            // Kumpulkan semua events dari generator
            $events = [];
            foreach ($generator as $event) {
                $events[] = $event;
            }

            // Ambil task_summary sebagai respons utama
            $summary = collect($events)->firstWhere('event', 'task_summary');

            return response()->json([
                'session_id' => $session->id,
                'events'     => $events,
                'summary'    => $summary['data'] ?? null,
            ]);

        } catch (\Throwable $e) {
            Log::error('AgentController::send error', [
                'error'      => $e->getMessage(),
                'session_id' => $session->id,
                'user_id'    => $user->id,
            ]);

            return response()->json([
                'error'   => true,
                'code'    => 'AGENT_ERROR',
                'message' => $e->getMessage() ?: 'Terjadi kesalahan pada agent.',
            ], 500);
        }
    }

    /**
     * POST /agent/stream
     * Kirim pesan ke agent dengan SSE streaming.
     * Mendelegasikan ke AgentOrchestrator::handle() sebagai Generator.
     *
     * Format SSE: "data: {json}\n\n"
     *
     * Requirements: 7.1, 7.2, 7.6
     */
    public function stream(Request $request): StreamedResponse
    {
        $request->validate([
            'message'    => 'required|string|max:4000',
            'session_id' => 'nullable|integer|exists:chat_sessions,id',
            'confirmed'  => 'nullable|boolean',
        ]);

        $user      = $request->user();
        $session   = $this->resolveSession($user, $request->session_id);
        $confirmed = (bool) $request->input('confirmed', false);
        $message   = $request->message;

        return response()->stream(function () use ($user, $session, $confirmed, $message) {
            // Kirim acknowledgment segera (< 2 detik) — Requirement 7.6
            $this->sseEvent('acknowledgment', ['message' => 'Memproses permintaan...']);

            try {
                $generator = $this->orchestrator->handle(
                    message: $message,
                    user: $user,
                    session: $session,
                    confirmed: $confirmed,
                );

                foreach ($generator as $event) {
                    $this->sseEvent($event['event'], $event['data']);
                }

            } catch (\Throwable $e) {
                Log::error('AgentController::stream error', [
                    'error'      => $e->getMessage(),
                    'session_id' => $session->id,
                    'user_id'    => $user->id,
                ]);

                $this->sseEvent('error', [
                    'code'    => 'STREAM_ERROR',
                    'message' => $e->getMessage() ?: 'Terjadi kesalahan pada streaming agent.',
                ]);
            }
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Connection'        => 'keep-alive',
        ]);
    }

    /**
     * POST /agent/confirm
     * Teruskan konfirmasi user ke session aktif dan lanjutkan eksekusi.
     * Digunakan setelah Approval Gate ditampilkan.
     *
     * Requirements: 1.5, 7.1
     */
    public function confirm(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|integer|exists:chat_sessions,id',
            'message'    => 'nullable|string|max:4000',
        ]);

        $user    = $request->user();
        $session = $this->findUserSession($user, $request->session_id);

        if (!$session) {
            return response()->json([
                'error'   => true,
                'code'    => 'SESSION_NOT_FOUND',
                'message' => 'Session tidak ditemukan atau tidak memiliki akses.',
            ], 404);
        }

        // Ambil pesan dari active_plan jika tidak ada pesan baru
        $activePlan = $session->active_plan;
        $message    = $request->input('message', $activePlan['goal'] ?? 'Lanjutkan eksekusi.');

        try {
            $generator = $this->orchestrator->handle(
                message: $message,
                user: $user,
                session: $session,
                confirmed: true,
            );

            $events = [];
            foreach ($generator as $event) {
                $events[] = $event;
            }

            $summary = collect($events)->firstWhere('event', 'task_summary');

            return response()->json([
                'session_id' => $session->id,
                'events'     => $events,
                'summary'    => $summary['data'] ?? null,
            ]);

        } catch (\Throwable $e) {
            Log::error('AgentController::confirm error', [
                'error'      => $e->getMessage(),
                'session_id' => $session->id,
            ]);

            return response()->json([
                'error'   => true,
                'code'    => 'CONFIRM_ERROR',
                'message' => $e->getMessage() ?: 'Gagal melanjutkan eksekusi.',
            ], 500);
        }
    }

    /**
     * POST /agent/cancel
     * Batalkan eksekusi yang sedang berjalan.
     * Memanggil AgentOrchestrator::cancel().
     *
     * Requirements: 7.4, 7.5
     */
    public function cancel(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|integer|exists:chat_sessions,id',
        ]);

        $user    = $request->user();
        $session = $this->findUserSession($user, $request->session_id);

        if (!$session) {
            return response()->json([
                'error'   => true,
                'code'    => 'SESSION_NOT_FOUND',
                'message' => 'Session tidak ditemukan atau tidak memiliki akses.',
            ], 404);
        }

        $this->orchestrator->cancel($session);

        return response()->json([
            'success'    => true,
            'session_id' => $session->id,
            'message'    => 'Eksekusi berhasil dibatalkan.',
        ]);
    }

    /**
     * POST /agent/undo
     * Undo aksi write terakhir dalam window 5 menit.
     * Memanggil AgentExecutor::undo() untuk audit log terakhir.
     *
     * Requirements: 6.6
     */
    public function undo(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'nullable|integer|exists:chat_sessions,id',
            'log_id'     => 'nullable|integer|exists:agent_audit_logs,id',
        ]);

        $user     = $request->user();
        $tenantId = $user->tenant_id;

        // Cari audit log yang akan di-undo
        $auditLog = null;

        if ($request->filled('log_id')) {
            // Undo log spesifik
            $auditLog = AgentAuditLog::where('id', $request->log_id)
                ->where('tenant_id', $tenantId)
                ->where('user_id', $user->id)
                ->first();
        } else {
            // Undo log write terakhir dalam 5 menit
            $fiveMinutesAgo = Carbon::now()->subMinutes(5);

            $query = AgentAuditLog::where('tenant_id', $tenantId)
                ->where('user_id', $user->id)
                ->where('action_type', 'write')
                ->where('status', 'success')
                ->where('is_undoable', true)
                ->where('undoable_until', '>=', Carbon::now())
                ->where('created_at', '>=', $fiveMinutesAgo)
                ->latest();

            if ($request->filled('session_id')) {
                $query->where('session_id', $request->session_id);
            }

            $auditLog = $query->first();
        }

        if (!$auditLog) {
            return response()->json([
                'error'   => true,
                'code'    => 'NO_UNDOABLE_ACTION',
                'message' => 'Tidak ada aksi yang dapat di-undo dalam 5 menit terakhir.',
            ], 404);
        }

        $registry  = new ToolRegistry($tenantId, $user->id);
        $undoResult = $this->executor->undo($auditLog, $registry);

        if (!$undoResult->success) {
            return response()->json([
                'error'   => true,
                'code'    => 'UNDO_FAILED',
                'message' => $undoResult->message,
            ], 422);
        }

        return response()->json([
            'success'       => true,
            'message'       => $undoResult->message,
            'restored_data' => $undoResult->restoredData,
        ]);
    }

    /**
     * GET /agent/insights
     * Ambil proactive insights yang belum dibaca untuk user.
     * Memanggil ProactiveInsightEngine::getPendingInsights().
     *
     * Requirements: 4.4
     */
    public function insights(Request $request): JsonResponse
    {
        $user     = $request->user();
        $tenantId = $user->tenant_id;

        if (!$tenantId) {
            return response()->json(['insights' => []]);
        }

        $insights = $this->insightEngine->getPendingInsights($tenantId, $user->id);

        return response()->json([
            'insights' => array_map(fn($insight) => [
                'id'              => $insight->id,
                'condition_type'  => $insight->condition_type,
                'urgency'         => $insight->urgency,
                'title'           => $insight->title,
                'description'     => $insight->description,
                'business_impact' => $insight->business_impact,
                'recommendations' => $insight->recommendations,
                'created_at'      => $insight->created_at?->toIso8601String(),
            ], $insights),
            'count' => count($insights),
        ]);
    }

    /**
     * POST /agent/insights/{id}/dismiss
     * Dismiss sebuah proactive insight.
     * Suppress insight serupa selama 24 jam.
     *
     * Requirements: 4.5
     */
    public function dismissInsight(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'reason' => 'nullable|string|in:dismissed,handled',
        ]);

        $user     = $request->user();
        $tenantId = $user->tenant_id;

        $insight = ProactiveInsight::where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$insight) {
            return response()->json([
                'error'   => true,
                'code'    => 'INSIGHT_NOT_FOUND',
                'message' => 'Insight tidak ditemukan.',
            ], 404);
        }

        $reason = $request->input('reason', 'dismissed');
        $this->insightEngine->dismiss($insight, $reason);

        return response()->json([
            'success' => true,
            'message' => 'Insight berhasil di-dismiss.',
        ]);
    }

    /**
     * GET /agent/memory
     * Lihat data memori AI user saat ini.
     * Mengekspos AiMemoryService untuk user.
     *
     * Requirements: 5.6
     */
    public function memory(Request $request): JsonResponse
    {
        $user     = $request->user();
        $tenantId = $user->tenant_id;

        if (!$tenantId) {
            return response()->json(['preferences' => [], 'suggestions' => []]);
        }

        $preferences = $this->memoryService->getPreferences($tenantId, $user->id);
        $suggestions = $this->memoryService->getSuggestions($tenantId, $user->id);

        return response()->json([
            'preferences' => $preferences,
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * DELETE /agent/memory
     * Hapus semua data memori AI user.
     * Mengekspos AiMemoryService untuk user.
     *
     * Requirements: 5.6
     */
    public function clearMemory(Request $request): JsonResponse
    {
        $user     = $request->user();
        $tenantId = $user->tenant_id;

        if (!$tenantId) {
            return response()->json([
                'success' => true,
                'deleted' => 0,
                'message' => 'Tidak ada data memori untuk dihapus.',
            ]);
        }

        $deleted = $this->memoryService->resetMemory($tenantId, $user->id);

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
            'message' => "Berhasil menghapus {$deleted} data memori.",
        ]);
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    /**
     * Resolve atau buat session baru untuk user.
     */
    private function resolveSession(\App\Models\User $user, ?int $sessionId): ChatSession
    {
        if ($sessionId) {
            $session = ChatSession::where('id', $sessionId)
                ->where('user_id', $user->id)
                ->first();

            if ($session) {
                return $session;
            }
        }

        // Buat session baru dengan tipe agent
        return ChatSession::create([
            'user_id'          => $user->id,
            'tenant_id'        => $user->tenant_id,
            'title'            => 'Agent Session',
            'session_type'     => 'agent',
            'execution_status' => null,
            'is_cancelled'     => false,
        ]);
    }

    /**
     * Cari session milik user (tanpa membuat baru).
     */
    private function findUserSession(\App\Models\User $user, int $sessionId): ?ChatSession
    {
        return ChatSession::where('id', $sessionId)
            ->where('user_id', $user->id)
            ->first();
    }

    /**
     * Kirim SSE event ke output buffer.
     * Format: "data: {json}\n\n"
     */
    private function sseEvent(string $event, array $data): void
    {
        echo "event: {$event}\n";
        echo 'data: ' . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n\n";

        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
    }
}
