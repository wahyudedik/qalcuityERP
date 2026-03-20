<?php

namespace App\Http\Controllers;

use App\Models\AiUsageLog;
use App\Models\ChatSession;
use App\Services\ChatSessionManager;
use App\Services\ERP\ToolRegistry;
use App\Services\GeminiService;
use App\Services\GeminiWriteValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function __construct(
        protected GeminiService      $gemini,
        protected ChatSessionManager $sessionManager,
        protected GeminiWriteValidator $validator,
    ) {}

    /**
     * Tampilkan halaman chat.
     */
    public function index(Request $request)
    {
        $sessions = $this->sessionManager->getUserSessions($request->user());
        return view('chat.index', compact('sessions'));
    }

    /**
     * Kirim pesan dan dapatkan respons Gemini.
     * POST /chat/send
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'message'    => 'required|string|max:4000',
            'session_id' => 'nullable|integer',
        ]);

        $user    = $request->user();
        $tenantId = $user->tenant_id;

        // Super admin tanpa tenant aktif tidak bisa pakai ERP tools
        // Tetap bisa chat biasa dengan Gemini
        $session  = $this->sessionManager->getOrCreateSession($user, $request->session_id);
        $history  = $this->sessionManager->getHistory($session);

        // Simpan pesan user
        $this->sessionManager->saveUserMessage($session, $request->message);

        // Jika tidak ada tenant, fallback ke chat biasa tanpa tools
        if (!$tenantId) {
            try {
                $response = $this->gemini->chat($request->message, $history);
                $text = $response['text'] ?: 'Maaf, tidak ada respons.';
                $this->sessionManager->saveModelMessage($session, $text, $response['model']);
                return response()->json([
                    'session_id'    => $session->id,
                    'session_title' => $session->fresh()->title,
                    'message'       => $text,
                    'model'         => $response['model'],
                    'actions'       => [],
                ]);
            } catch (\Throwable $e) {
                Log::error('ChatController (no-tenant) error: ' . $e->getMessage());
                return response()->json(['session_id' => $session->id, 'message' => 'Terjadi kesalahan pada sistem AI.'], 500);
            }
        }

        $registry = new ToolRegistry($tenantId, $user->id);

        // Cek limit AI messages per bulan berdasarkan plan tenant
        $tenant = $user->tenant;
        $maxAi  = $tenant?->maxAiMessages() ?? 20;

        // Inject konteks bisnis tenant ke Gemini
        if ($tenant && $tenant->business_type) {
            $this->gemini->withTenantContext($tenant->aiBusinessContext());
        }
        if ($maxAi !== -1) {
            $usedThisMonth = AiUsageLog::tenantMonthlyCount($tenantId);
            if ($usedThisMonth >= $maxAi) {
                return response()->json([
                    'session_id' => $session->id,
                    'message'    => "Kuota pesan AI bulan ini sudah habis ({$usedThisMonth}/{$maxAi}). "
                        . "Upgrade paket untuk mendapatkan lebih banyak pesan AI.",
                    'quota_exceeded' => true,
                ], 429);
            }
        }

        try {
            // Step 1: Kirim ke Gemini dengan function calling tools
            $response = $this->gemini->chatWithTools(
                message: $this->buildSystemPrompt($request->message, $user),
                history: $history,
                toolDeclarations: $registry->getDeclarations(),
            );

            $functionCalls = $response['function_calls'] ?? [];

            // Step 2: Jika Gemini tidak memanggil function, langsung return teks
            if (empty($functionCalls)) {
                $text = $response['text'] ?: 'Maaf, saya tidak dapat memproses permintaan tersebut.';
                $this->sessionManager->saveModelMessage($session, $text, $response['model']);
                AiUsageLog::track($tenantId, $user->id, strlen($text));

                return response()->json([
                    'session_id' => $session->id,
                    'message'    => $text,
                    'model'      => $response['model'],
                    'actions'    => [],
                ]);
            }

            // Step 3: Validasi dan eksekusi setiap function call
            $functionResults = [];
            $executedActions = [];
            $validationErrors = [];

            foreach ($functionCalls as $call) {
                $toolName = $call['name'];
                $args     = $call['args'];

                // Validasi write operations sebelum eksekusi
                if ($registry->isWriteOperation($toolName)) {
                    if (!$this->validator->validate($toolName, $args)) {
                        $validationErrors[] = [
                            'tool'   => $toolName,
                            'errors' => $this->validator->getErrors(),
                        ];
                        continue;
                    }
                }

                // Eksekusi tool
                $result = $registry->execute($toolName, $args);
                Log::info("ChatController: executed tool [{$toolName}]", ['result' => $result]);

                // Simpan args di _args agar bisa dipakai untuk reconstruct model turn di sendFunctionResults
                $result['_args'] = $args;
                $functionResults[] = ['name' => $toolName, 'data' => $result];
                $executedActions[] = ['tool' => $toolName, 'args' => $args, 'result' => $result];
            }

            // Jika ada validation error, kembalikan pesan error tanpa eksekusi
            if (!empty($validationErrors)) {
                $errorMsg = $this->buildValidationErrorMessage($validationErrors);
                $this->sessionManager->saveModelMessage($session, $errorMsg, $response['model']);
                AiUsageLog::track($tenantId, $user->id, strlen($errorMsg));

                return response()->json([
                    'session_id'        => $session->id,
                    'message'           => $errorMsg,
                    'model'             => $response['model'],
                    'validation_errors' => $validationErrors,
                    'actions'           => [],
                ]);
            }

            // Step 4: Kirim hasil function kembali ke Gemini untuk dirangkai jadi jawaban natural
            $finalResponse = $this->gemini->sendFunctionResults(
                originalMessage: $request->message,
                history: $history,
                toolDeclarations: $registry->getDeclarations(),
                functionResults: $functionResults,
            );

            $finalText = $finalResponse['text'] ?: $this->buildFallbackText($functionResults);

            $this->sessionManager->saveModelMessage(
                $session,
                $finalText,
                $finalResponse['model'],
                $executedActions
            );
            AiUsageLog::track($tenantId, $user->id, strlen($finalText));

            return response()->json([
                'session_id'    => $session->id,
                'session_title' => $session->fresh()->title,
                'message'       => $finalText,
                'model'         => $finalResponse['model'],
                'actions'       => $executedActions,
            ]);

        } catch (\Throwable $e) {
            Log::error('ChatController error: ' . $e->getMessage());

            return response()->json([
                'session_id' => $session->id,
                'message'    => 'Terjadi kesalahan pada sistem AI. Silakan coba lagi.',
                'error'      => app()->isLocal() ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Kirim pesan dengan file/gambar (multimodal).
     * POST /chat/send-media
     */
    public function sendMedia(Request $request): JsonResponse
    {
        $request->validate([
            'message'    => 'nullable|string|max:4000',
            'session_id' => 'nullable|integer',
            'files'      => 'required|array|min:1|max:5',
            'files.*'    => 'required|file|max:20480', // 20MB per file
        ]);

        $user     = $request->user();
        $tenantId = $user->tenant_id;
        $session  = $this->sessionManager->getOrCreateSession($user, $request->session_id);
        $history  = $this->sessionManager->getHistory($session);
        $message  = $request->message ?? 'Tolong analisis file/gambar ini.';

        // Encode files to base64 for Gemini
        $files = [];
        $fileLabels = [];
        foreach ($request->file('files') as $file) {
            $mimeType = $file->getMimeType();
            $data     = base64_encode(file_get_contents($file->getRealPath()));
            $files[]  = ['mime_type' => $mimeType, 'data' => $data];
            $fileLabels[] = $file->getClientOriginalName() . ' (' . $this->humanFileSize($file->getSize()) . ')';
        }

        // Save user message with file info
        $userMsgText = $message . "\n\n📎 File: " . implode(', ', $fileLabels);
        $this->sessionManager->saveUserMessage($session, $userMsgText);

        // Inject tenant context
        $tenant = $user->tenant;
        if ($tenant && $tenant->business_type) {
            $this->gemini->withTenantContext($tenant->aiBusinessContext());
        }

        // Check AI quota
        if ($tenantId) {
            $maxAi = $tenant?->maxAiMessages() ?? 20;
            if ($maxAi !== -1) {
                $usedThisMonth = AiUsageLog::tenantMonthlyCount($tenantId);
                if ($usedThisMonth >= $maxAi) {
                    return response()->json([
                        'session_id'     => $session->id,
                        'message'        => "Kuota pesan AI bulan ini sudah habis ({$usedThisMonth}/{$maxAi}).",
                        'quota_exceeded' => true,
                    ], 429);
                }
            }
        }

        try {
            $registry = $tenantId ? new ToolRegistry($tenantId, $user->id) : null;
            $contextMessage = $this->buildSystemPrompt($message, $user);

            $response = $this->gemini->chatWithMedia(
                message: $contextMessage,
                files: $files,
                history: $history,
                toolDeclarations: $registry ? $registry->getDeclarations() : [],
            );

            $functionCalls = $response['function_calls'] ?? [];
            $text = $response['text'] ?? '';

            // If AI called functions, execute them
            if (!empty($functionCalls) && $registry) {
                $functionResults = [];
                foreach ($functionCalls as $call) {
                    $result = $registry->execute($call['name'], $call['args']);
                    $result['_args'] = $call['args']; // needed to reconstruct model turn
                    $functionResults[] = ['name' => $call['name'], 'data' => $result];
                }

                $finalResponse = $this->gemini->sendFunctionResults(
                    originalMessage: $message,
                    history: $history,
                    toolDeclarations: $registry->getDeclarations(),
                    functionResults: $functionResults,
                );
                $text = $finalResponse['text'] ?? $text;
            }

            $text = $text ?: 'Maaf, tidak dapat memproses file tersebut.';
            $this->sessionManager->saveModelMessage($session, $text, $response['model']);

            if ($tenantId) {
                AiUsageLog::track($tenantId, $user->id, strlen($text));
            }

            return response()->json([
                'session_id'    => $session->id,
                'session_title' => $session->fresh()->title,
                'message'       => $text,
                'model'         => $response['model'],
                'actions'       => [],
            ]);

        } catch (\Throwable $e) {
            Log::error('ChatController sendMedia error: ' . $e->getMessage());
            return response()->json([
                'session_id' => $session->id,
                'message'    => 'Gagal memproses file. Pastikan format file didukung (JPG, PNG, PDF, TXT).',
                'error'      => app()->isLocal() ? $e->getMessage() : null,
            ], 500);
        }
    }
    public function messages(Request $request, ChatSession $session): JsonResponse
    {
        abort_if($session->user_id !== $request->user()->id, 403);

        $messages = $session->messages()
            ->orderBy('id')
            ->get(['id', 'role', 'content', 'model_used', 'created_at']);

        return response()->json(['messages' => $messages]);
    }

    /**
     * Hapus (soft) session.
     * DELETE /chat/{session}
     */
    public function destroy(Request $request, ChatSession $session): JsonResponse
    {
        abort_if($session->user_id !== $request->user()->id, 403);
        $this->sessionManager->deleteSession($session);
        return response()->json(['success' => true]);
    }

    // ─── Helpers ──────────────────────────────────────────────────

    /**
     * Inject tenant & user context ke pesan agar Gemini tahu konteks bisnis.
     * Data ini tidak bocor ke tenant lain karena setiap request baru ToolRegistry
     * dibuat dengan tenantId spesifik, dan history diambil dari session milik user tsb.
     */
    protected function buildSystemPrompt(string $message, \App\Models\User $user): string
    {
        $tenant = $user->tenant;
        if (!$tenant) return $message;

        $context = "[KONTEKS SISTEM: Kamu sedang melayani pengguna bernama \"{$user->name}\" "
            . "(role: {$user->role}) dari perusahaan \"{$tenant->name}\". "
            . "Semua data yang kamu akses melalui tools adalah milik perusahaan ini saja. "
            . "Jangan pernah menyebut atau mengasumsikan data dari perusahaan lain.]\n\n";

        return $context . $message;
    }

    protected function buildValidationErrorMessage(array $errors): string
    {
        $lines = ['Maaf, ada data yang perlu diperbaiki sebelum saya bisa melanjutkan:'];
        foreach ($errors as $err) {
            foreach ($err['errors'] as $msg) {
                $lines[] = "• {$msg}";
            }
        }
        return implode("\n", $lines);
    }

    protected function buildFallbackText(array $functionResults): string
    {
        $lines = [];
        foreach ($functionResults as $result) {
            $data = $result['data'];
            if (isset($data['message'])) {
                $lines[] = $data['message'];
            } elseif (isset($data['status']) && $data['status'] === 'success') {
                $lines[] = 'Berhasil diproses.';
            }
        }
        return implode("\n", $lines) ?: 'Permintaan telah diproses.';
    }

    protected function humanFileSize(int $bytes): string
    {
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }
}
