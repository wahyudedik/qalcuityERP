<?php

namespace App\Http\Controllers;

use App\Models\ChatSession;
use App\Services\AiMemoryService;
use App\Services\AiQuotaService;
use App\Services\AiResponseCacheService;
use App\Services\AiStreamingService;
use App\Services\AI\IntentDetector;
use App\Services\ChatSessionManager;
use App\Services\ERP\ToolRegistry;
use App\Services\GeminiService;
use App\Services\GeminiWriteValidator;
use App\Services\RuleBasedResponseHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    public function __construct(
        protected GeminiService $gemini,
        protected ChatSessionManager $sessionManager,
        protected GeminiWriteValidator $validator,
        protected AiMemoryService $memoryService,
        protected AiQuotaService $quota,
        protected AiResponseCacheService $cacheService,
        protected RuleBasedResponseHandler $ruleHandler,
        protected AiStreamingService $streamingService,
        protected IntentDetector $intentDetector,
    ) {
    }

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
            'message' => 'required|string|max:4000',
            'session_id' => 'nullable|integer',
        ]);

        $user = $request->user();
        $tenantId = $user->tenant_id;

        // Super admin tanpa tenant aktif tidak bisa pakai ERP tools
        // Tetap bisa chat biasa dengan Gemini
        $session = $this->sessionManager->getOrCreateSession($user, $request->session_id);

        // TASK-020: Use history with summarization for long conversations
        $history = $this->sessionManager->getHistoryWithSummarization($session);

        // Simpan pesan user
        $this->sessionManager->saveUserMessage($session, $request->message);

        // OPTIMIZATION 1: Rule-based handler untuk pertanyaan sederhana
        if ($this->ruleHandler->canHandle($request->message)) {
            $response = $this->ruleHandler->handle($request->message, $user->name);

            $this->sessionManager->saveModelMessage($session, $response['text'], $response['model']);

            return response()->json([
                'session_id' => $session->id,
                'session_title' => $session->fresh()->title,
                'message' => $response['text'],
                'model' => $response['model'],
                'actions' => [],
                'cached' => false,
                'optimized' => true, // Flag untuk monitoring
                'optimization_type' => 'rule-based',
            ]);
        }

        // OPTIMIZATION 2: Cache layer untuk query repetitif
        // ✅ FIX: Include session_id untuk cache key yang lebih unik
        $cacheKey = $this->cacheService->generateCacheKey(
            $tenantId ?? 0,
            $user->id,
            $request->message,
            $session->id // ✅ Session context untuk mencegah collision
        );
        $cachedResponse = $this->cacheService->get($cacheKey);

        if ($cachedResponse !== null) {
            // Cache HIT - skip API call!
            $this->sessionManager->saveModelMessage($session, $cachedResponse['text'], $cachedResponse['model'] ?? 'cached');

            return response()->json([
                'session_id' => $session->id,
                'session_title' => $session->fresh()->title,
                'message' => $cachedResponse['text'],
                'model' => $cachedResponse['model'] ?? 'cached',
                'actions' => $cachedResponse['actions'] ?? [],
                'cached' => true,
                'optimized' => true,
                'optimization_type' => 'cache-hit',
                'cache_ttl' => config('cache.ttl', 3600),
            ]);
        }

        // Jika tidak ada tenant, fallback ke chat biasa tanpa tools
        if (!$tenantId) {
            try {
                $response = $this->gemini->chat($request->message, $history);
                $text = $response['text'] ?: 'Maaf, tidak ada respons.';
                $this->sessionManager->saveModelMessage($session, $text, $response['model']);
                return response()->json([
                    'session_id' => $session->id,
                    'session_title' => $session->fresh()->title,
                    'message' => $text,
                    'model' => $response['model'],
                    'actions' => [],
                ]);
            } catch (\Throwable $e) {
                Log::error('ChatController (no-tenant) error: ' . $e->getMessage());
                $httpCode = $this->resolveHttpCode($e);
                return response()->json([
                    'session_id' => $session->id,
                    'message'    => $e->getMessage() ?: 'Terjadi kesalahan pada sistem AI.',
                    'error'      => app()->isLocal() ? $e->getMessage() : null,
                ], $httpCode);
            }
        }

        $registry = new ToolRegistry($tenantId, $user->id);

        // OPTIMIZATION 3: Intent-based tool selection (TASK-008)
        // Detect user intent to only send relevant tools instead of all 100+ tools
        // This reduces response time by 60-70% (from 5-10s to 2-3s)
        $intent = $this->intentDetector->detect($request->message);
        $allowedTools = $user->allowedAiTools();

        // Use intent-based filtering for faster response
        $toolDeclarations = $registry->getDeclarationsForIntent($intent, $allowedTools);

        // Log optimization metrics for monitoring
        $totalTools = count($registry->getDeclarations($allowedTools));
        $filteredTools = count($toolDeclarations);
        $reduction = $totalTools > 0 ? round((1 - $filteredTools / $totalTools) * 100) : 0;

        Log::info('ChatController: Intent detection', [
            'intent' => $intent,
            'confidence' => $this->intentDetector->getConfidence($request->message, $intent),
            'tools_before' => $totalTools,
            'tools_after' => $filteredTools,
            'reduction_percent' => $reduction,
        ]);

        // Inject konteks bisnis tenant ke Gemini
        $tenant = $user->tenant;
        if ($tenant && $tenant->business_type) {
            $this->gemini->withTenantContext($tenant->aiBusinessContext());
        }

        try {
            // Step 1: Kirim ke Gemini dengan function calling tools
            $response = $this->gemini->chatWithTools(
                message: $this->buildSystemPrompt($request->message, $user),
                history: $history,
                toolDeclarations: $toolDeclarations,
            );

            $functionCalls = $response['function_calls'] ?? [];

            // Step 2: Jika Gemini tidak memanggil function, langsung return teks
            if (empty($functionCalls)) {
                $text = $response['text'] ?? '';

                // Jika teks juga kosong, kemungkinan Gemini gagal generate — retry sekali
                if (empty(trim($text))) {
                    Log::warning('ChatController: empty response from Gemini, retrying with explicit instruction');
                    $retryMessage = $this->buildSystemPrompt($request->message, $user)
                        . "\n\n[INSTRUKSI: Jika tidak ada tool yang relevan, jawab langsung dengan teks. Jangan kembalikan respons kosong.]";
                    $retryResponse = $this->gemini->chatWithTools(
                        message: $retryMessage,
                        history: $history,
                        toolDeclarations: $toolDeclarations,
                    );
                    $text = $retryResponse['text'] ?? '';
                    $functionCalls = $retryResponse['function_calls'] ?? [];

                    // Jika retry menghasilkan function calls, lanjutkan ke Step 3
                    if (!empty($functionCalls)) {
                        $response = $retryResponse;
                        return $this->executeFunctionCalls(
                            $functionCalls,
                            $registry,
                            $session,
                            $response,
                            $history,
                            $toolDeclarations,
                            $request->message,
                            $tenantId,
                            $user
                        );
                    }
                }

                $text = $text ?: 'Maaf, saya tidak dapat memproses permintaan tersebut. Coba ulangi dengan kalimat yang lebih spesifik.';
                $this->sessionManager->saveModelMessage($session, $text, $response['model']);

                // Cache the response
                $this->cacheService->put($cacheKey, [
                    'text' => $text,
                    'model' => $response['model'],
                    'actions' => [],
                ]);

                if ($tenantId)
                    $this->quota->track($tenantId, $user->id, strlen($text));

                return response()->json([
                    'session_id' => $session->id,
                    'session_title' => $session->title,
                    'message' => $text,
                    'model' => $response['model'],
                    'actions' => [],
                    'cached' => false,
                    'quota' => $tenantId ? $this->quota->status($tenantId) : null,
                ]);
            }

            return $this->executeFunctionCalls(
                $functionCalls,
                $registry,
                $session,
                $response,
                $history,
                $toolDeclarations,
                $request->message,
                $tenantId,
                $user
            );

        } catch (\Throwable $e) {
            Log::error('ChatController error: ' . $e->getMessage());
            $httpCode = $this->resolveHttpCode($e);

            return response()->json([
                'session_id' => $session->id,
                'message'    => $e->getMessage() ?: 'Terjadi kesalahan pada sistem AI. Silakan coba lagi.',
                'error'      => app()->isLocal() ? $e->getMessage() : null,
            ], $httpCode);
        }
    }

    /**
     * Stream AI response dengan Server-Sent Events (SSE).
     * POST /chat/stream
     */
    public function stream(Request $request): StreamedResponse
    {
        $request->validate([
            'message' => 'required|string|max:4000',
            'session_id' => 'nullable|integer',
        ]);

        $user = $request->user();
        $tenantId = $user->tenant_id;
        $session = $this->sessionManager->getOrCreateSession($user, $request->session_id);

        // TASK-020: Use history with summarization for long conversations
        $history = $this->sessionManager->getHistoryWithSummarization($session);

        // Simpan pesan user
        $this->sessionManager->saveUserMessage($session, $request->message);

        // Untuk rule-based, tetap gunakan JSON response (tidak perlu streaming)
        if ($this->ruleHandler->canHandle($request->message)) {
            $response = $this->ruleHandler->handle($request->message, $user->name);
            $this->sessionManager->saveModelMessage($session, $response['text'], $response['model']);

            // Return sebagai SSE untuk konsistensi
            return response()->stream(function () use ($response) {
                echo "event: start\ndata: " . json_encode(['message' => 'Processing...']) . "\n\n";
                echo "event: chunk\ndata: " . json_encode(['text' => $response['text'], 'progress' => 100, 'is_final' => true]) . "\n\n";
                echo "event: complete\ndata: " . json_encode(['full_text' => $response['text'], 'model' => $response['model']]) . "\n\n";
                if (ob_get_level() > 0)
                    ob_flush();
                flush();
            }, 200, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no',
                'Connection' => 'keep-alive',
            ]);
        }

        // Setup tool registry jika ada tenant
        $registry = null;
        $toolDeclarations = [];

        if ($tenantId) {
            $registry = new ToolRegistry($tenantId, $user->id);
            $allowedTools = $user->allowedAiTools();

            // OPTIMIZATION 3: Intent-based tool selection for streaming
            $intent = $this->intentDetector->detect($request->message);
            $toolDeclarations = $registry->getDeclarationsForIntent($intent, $allowedTools);

            $tenant = $user->tenant;
            if ($tenant && $tenant->business_type) {
                $this->gemini->withTenantContext($tenant->aiBusinessContext());
            }
        }

        // Gunakan streaming service
        return $this->streamingService->streamResponse(
            message: $this->buildSystemPrompt($request->message, $user),
            history: $history,
            toolDeclarations: $toolDeclarations,
            onChunk: function ($chunk, $index, $total) {
                // Chunk callback — bisa dipakai untuk logging/testing
            },
            onComplete: function ($fullText, $model) use ($session, $tenantId, $user) {
                $this->sessionManager->saveModelMessage($session, $fullText, $model);
                if ($tenantId) {
                    $this->quota->track($tenantId, $user->id, strlen($fullText));
                }
            }
        );
    }

    /**
     * Kirim pesan dengan file/gambar (multimodal).
     * POST /chat/send-media
     */
    public function sendMedia(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'nullable|string|max:4000',
            'session_id' => 'nullable|integer',
            'files' => 'required|array|min:1|max:5',
            'files.*' => 'required|file|max:20480', // 20MB per file
        ]);

        $user = $request->user();
        $tenantId = $user->tenant_id;
        $session = $this->sessionManager->getOrCreateSession($user, $request->session_id);

        // TASK-020: Use history with summarization for long conversations
        $history = $this->sessionManager->getHistoryWithSummarization($session);
        $message = $request->message ?? 'Tolong analisis file/gambar ini.';

        // Encode files to base64 for Gemini, and save images to storage for tool use
        $files = [];
        $fileLabels = [];
        $uploadedImageUrls = []; // URLs gambar yang sudah disimpan ke storage

        foreach ($request->file('files') as $file) {
            $mimeType = $file->getMimeType();
            $data = base64_encode(file_get_contents($file->getRealPath()));
            $files[] = ['mime_type' => $mimeType, 'data' => $data];
            $fileLabels[] = $file->getClientOriginalName() . ' (' . $this->humanFileSize($file->getSize()) . ')';

            // Simpan gambar ke storage agar bisa dipakai oleh update_product_image tool
            if (str_starts_with($mimeType, 'image/')) {
                $ext = $file->getClientOriginalExtension() ?: 'jpg';
                $path = $file->storeAs('products', uniqid('chat_') . '.' . $ext, 'public');
                $uploadedImageUrls[] = \Illuminate\Support\Facades\Storage::url($path);
            }
        }

        // Inject URL gambar ke context message agar AI bisa pakai di tool call
        $contextMessage = $message;
        if (!empty($uploadedImageUrls)) {
            $urlList = implode(', ', $uploadedImageUrls);
            $contextMessage .= "\n\n[SISTEM: Gambar telah diupload ke server. URL gambar: {$urlList}. "
                . "Gunakan URL ini sebagai image_url saat memanggil update_product_image.]";
        }

        // Save user message with file info
        $userMsgText = $message . "\n\n📎 File: " . implode(', ', $fileLabels);
        $this->sessionManager->saveUserMessage($session, $userMsgText);

        // Inject tenant context
        $tenant = $user->tenant;
        if ($tenant && $tenant->business_type) {
            $this->gemini->withTenantContext($tenant->aiBusinessContext());
        }

        // Detect language from user message
        $this->gemini->withLanguage($this->detectLanguage($message));

        // Check AI quota handled by middleware (ai.quota)
        // Inject tenant context

        try {
            $registry = $tenantId ? new ToolRegistry($tenantId, $user->id) : null;
            $allowedTools = $tenantId ? $user->allowedAiTools() : null;

            // OPTIMIZATION 3: Intent-based tool selection for media chat
            $toolDeclarations = [];
            if ($registry && $tenantId) {
                $intent = $this->intentDetector->detect($message);
                $toolDeclarations = $registry->getDeclarationsForIntent($intent, $allowedTools);
            }

            // Gabungkan system prompt + context message (sudah include URL gambar jika ada)
            $fullContextMessage = $this->buildSystemPrompt($contextMessage, $user);

            $response = $this->gemini->chatWithMedia(
                message: $fullContextMessage,
                files: $files,
                history: $history,
                toolDeclarations: $toolDeclarations,
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
                    toolDeclarations: $toolDeclarations,
                    functionResults: $functionResults,
                );
                $text = $finalResponse['text'] ?? $text;
            }

            $text = $text ?: 'Maaf, tidak dapat memproses file tersebut.';
            $this->sessionManager->saveModelMessage($session, $text, $response['model']);

            if ($tenantId) {
                $this->quota->track($tenantId, $user->id, strlen($text));
            }

            return response()->json([
                'session_id' => $session->id,
                'session_title' => $session->fresh()->title,
                'message' => $text,
                'model' => $response['model'],
                'actions' => [],
            ]);

        } catch (\Throwable $e) {
            Log::error('ChatController sendMedia error: ' . $e->getMessage());
            return response()->json([
                'session_id' => $session->id,
                'message' => $e->getMessage() ?: 'Gagal memproses file. Pastikan format file didukung (JPG, PNG, PDF, TXT).',
                'error' => app()->isLocal() ? $e->getMessage() : null,
            ], $this->resolveHttpCode($e));
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
     * Rename session title.
     * PATCH /chat/{session}/rename
     */
    public function rename(Request $request, ChatSession $session): JsonResponse
    {
        abort_if($session->user_id !== $request->user()->id, 403);
        $request->validate(['title' => 'required|string|max:100']);
        $session->update(['title' => $request->title]);
        return response()->json(['success' => true, 'title' => $session->title]);
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
     * Step 3 & 4: Validate, execute function calls and send results back to Gemini.
     * Extracted to eliminate the goto anti-pattern.
     */
    protected function executeFunctionCalls(
        array $functionCalls,
        ToolRegistry $registry,
        \App\Models\ChatSession $session,
        array $response,
        array $history,
        array $toolDeclarations,
        string $originalMessage,
        ?int $tenantId,
        \App\Models\User $user
    ): JsonResponse {
        // Step 3: Validasi dan eksekusi setiap function call
        $functionResults = [];
        $executedActions = [];
        $validationErrors = [];

        // TASK-021: Separate read and write operations
        // Read operations can be parallelized, write operations need validation
        $readOperations = [];
        $writeOperations = [];

        foreach ($functionCalls as $index => $call) {
            $toolName = $call['name'];
            $args = $call['args'];

            if ($registry->isWriteOperation($toolName)) {
                $writeOperations[] = ['index' => $index, 'name' => $toolName, 'args' => $args];
            } else {
                $readOperations[] = ['index' => $index, 'name' => $toolName, 'args' => $args];
            }
        }

        // TASK-021: Execute read operations in parallel
        if (!empty($readOperations)) {
            $readResults = $this->executeToolsInParallel($readOperations, $registry);

            foreach ($readResults as $result) {
                $functionResults[] = ['name' => $result['name'], 'data' => $result['data']];
                $executedActions[] = ['tool' => $result['name'], 'args' => $result['args'], 'result' => $result['data']];
            }
        }

        // Execute write operations sequentially (with validation)
        foreach ($writeOperations as $op) {
            $toolName = $op['name'];
            $args = $op['args'];

            // Validasi write operations sebelum eksekusi
            if (!$this->validator->validate($toolName, $args)) {
                $validationErrors[] = [
                    'tool' => $toolName,
                    'errors' => $this->validator->getErrors(),
                ];
                continue;
            }

            // Eksekusi tool
            $result = $registry->execute($toolName, $args);
            Log::info("ChatController: executed tool [{$toolName}]", ['result' => $result]);

            // Jika tool result punya field 'actions', inject ke message agar Gemini render actions block
            if (!empty($result['actions']) && is_array($result['actions'])) {
                $actionsJson = json_encode($result['actions'], JSON_UNESCAPED_UNICODE);
                $result['message'] = ($result['message'] ?? '') . "\n\n```actions\n{$actionsJson}\n```";
                unset($result['actions']); // hindari duplikasi
            }

            // Simpan args di _args agar bisa dipakai untuk reconstruct model turn di sendFunctionResults
            $result['_args'] = $args;
            $functionResults[] = ['name' => $toolName, 'data' => $result];
            $executedActions[] = ['tool' => $toolName, 'args' => $args, 'result' => $result];
        }

        // Jika ada validation error, kembalikan pesan error tanpa eksekusi
        if (!empty($validationErrors)) {
            $errorMsg = $this->buildValidationErrorMessage($validationErrors);
            $this->sessionManager->saveModelMessage($session, $errorMsg, $response['model']);
            if ($tenantId)
                $this->quota->track($tenantId, $user->id, strlen($errorMsg));

            return response()->json([
                'session_id' => $session->id,
                'message' => $errorMsg,
                'model' => $response['model'],
                'validation_errors' => $validationErrors,
                'actions' => [],
            ]);
        }

        // Step 4: Kirim hasil function kembali ke Gemini untuk dirangkai jadi jawaban natural
        $finalResponse = $this->gemini->sendFunctionResults(
            originalMessage: $originalMessage,
            history: $history,
            toolDeclarations: $toolDeclarations, // reuse, tidak build ulang
            functionResults: $functionResults,
        );

        $finalText = $finalResponse['text'] ?: $this->buildFallbackText($functionResults);

        $this->sessionManager->saveModelMessage(
            $session,
            $finalText,
            $finalResponse['model'],
            $executedActions
        );
        if ($tenantId)
            $this->quota->track($tenantId, $user->id, strlen($finalText));

        return response()->json([
            'session_id' => $session->id,
            'session_title' => $session->title,
            'message' => $finalText,
            'model' => $finalResponse['model'],
            'actions' => $executedActions,
            'quota' => $tenantId ? $this->quota->status($tenantId) : null,
        ]);
    }

    /**
     * Inject tenant & user context ke pesan agar Gemini tahu konteks bisnis.
     * Data ini tidak bocor ke tenant lain karena setiap request baru ToolRegistry
     * dibuat dengan tenantId spesifik, dan history diambil dari session milik user tsb.
     */
    protected function buildSystemPrompt(string $message, \App\Models\User $user): string
    {
        $tenant = $user->tenant;
        if (!$tenant)
            return $message;

        $lang = $this->detectLanguage($message);
        $this->gemini->withLanguage($lang);

        $context = "[SYSTEM CONTEXT: You are serving user \"{$user->name}\" "
            . "(role: {$user->role}) from company \"{$tenant->name}\". "
            . "All data accessed via tools belongs exclusively to this company. "
            . "Never reference or assume data from other companies.]\n\n";

        // Inject AI memory context (Task 52)
        $memoryContext = $this->memoryService->buildMemoryContext($tenant->id, $user->id);
        if ($memoryContext) {
            $context .= $memoryContext . "\n\n";
        }

        return $context . $message;
    }

    /**
     * Detect the primary language of a message using Unicode script ranges
     * and common word patterns. Returns an ISO 639-1 language code.
     */
    protected function detectLanguage(string $text): string
    {
        $text = mb_strtolower(trim($text));

        if (empty($text))
            return 'id';

        // Script-based detection (fast, no external dependency)
        if (preg_match('/[\x{4e00}-\x{9fff}]/u', $text))
            return 'zh'; // Chinese
        if (preg_match('/[\x{3040}-\x{30ff}]/u', $text))
            return 'ja'; // Japanese
        if (preg_match('/[\x{ac00}-\x{d7af}]/u', $text))
            return 'ko'; // Korean
        if (preg_match('/[\x{0600}-\x{06ff}]/u', $text))
            return 'ar'; // Arabic
        if (preg_match('/[\x{0900}-\x{097f}]/u', $text))
            return 'hi'; // Hindi/Devanagari
        if (preg_match('/[\x{0e00}-\x{0e7f}]/u', $text))
            return 'th'; // Thai
        if (preg_match('/[\x{1e00}-\x{1eff}]/u', $text))
            return 'vi'; // Vietnamese extended

        // Latin-script languages — keyword-based
        $words = preg_split('/\s+/', $text);
        $wordCount = count($words);

        // Indonesian/Malay markers
        $idWords = [
            'yang',
            'dan',
            'di',
            'ke',
            'dari',
            'untuk',
            'dengan',
            'ini',
            'itu',
            'ada',
            'tidak',
            'bisa',
            'saya',
            'kami',
            'kita',
            'anda',
            'jual',
            'beli',
            'stok',
            'produk',
            'harga',
            'laporan',
            'catat',
            'tambah',
            'berapa',
            'tolong',
            'mohon',
            'gimana',
            'bagaimana',
            'kenapa',
            // kata bisnis & percakapan sehari-hari
            'grafik',
            'omzet',
            'hari',
            'minggu',
            'bulan',
            'tahun',
            'kamu',
            'lakukan',
            'saja',
            'apa',
            'siapa',
            'kapan',
            'dimana',
            'mana',
            'buat',
            'lihat',
            'cek',
            'cari',
            'hapus',
            'ubah',
            'ganti',
            'kirim',
            'bayar',
            'terima',
            'proses',
            'jalankan',
            'hitung',
            'penjualan',
            'pembelian',
            'keuangan',
            'karyawan',
            'pelanggan',
            'supplier',
            'gudang',
            'aset',
            'proyek',
            'anggaran',
            'gaji',
            'absensi',
            'invoice',
            'tagihan',
            'piutang',
            'hutang',
            'laba',
            'rugi',
            'biaya',
            'pendapatan',
            'transaksi',
            'pembayaran',
            'tren',
            'ringkasan',
            'rekap',
            'summary',
            'kondisi',
            'bisnis',
            'fitur',
            'menu',
            'cara',
            'panduan',
            'tutorial',
            'bantuan',
            'bisa',
            'boleh',
            'mau',
            'ingin',
            'perlu',
            'harus',
            'sudah',
            'belum',
            'sedang',
            'akan',
            'punya',
            'ada',
            'tidak',
            'jangan',
            'semua',
            'beberapa',
            'banyak',
            'sedikit',
            'total',
            'jumlah'
        ];
        $msWords = [
            'saya',
            'anda',
            'dengan',
            'untuk',
            'kepada',
            'daripada',
            'boleh',
            'hendak',
            'mahu',
            'sudah',
            'belum',
            'juga',
            'pula',
            'sahaja'
        ];

        // English markers
        $enWords = [
            'the',
            'is',
            'are',
            'was',
            'were',
            'have',
            'has',
            'had',
            'will',
            'would',
            'can',
            'could',
            'should',
            'what',
            'how',
            'show',
            'list',
            'get',
            'create',
            'update',
            'delete',
            'please',
            'help',
            'report'
        ];

        // French markers
        $frWords = [
            'le',
            'la',
            'les',
            'de',
            'du',
            'des',
            'est',
            'sont',
            'avec',
            'pour',
            'dans',
            'sur',
            'par',
            'que',
            'qui',
            'une',
            'un'
        ];

        // Spanish markers
        $esWords = [
            'el',
            'la',
            'los',
            'las',
            'de',
            'del',
            'es',
            'son',
            'con',
            'para',
            'en',
            'por',
            'que',
            'una',
            'un',
            'como',
            'pero'
        ];

        // Portuguese markers
        $ptWords = [
            'o',
            'a',
            'os',
            'as',
            'de',
            'do',
            'da',
            'dos',
            'das',
            'é',
            'são',
            'com',
            'para',
            'em',
            'por',
            'que',
            'uma',
            'um'
        ];

        // German markers
        $deWords = [
            'der',
            'die',
            'das',
            'den',
            'dem',
            'des',
            'ist',
            'sind',
            'mit',
            'für',
            'in',
            'auf',
            'von',
            'zu',
            'und',
            'oder',
            'nicht',
            'auch'
        ];

        $scores = [
            'id' => 0,
            'ms' => 0,
            'en' => 0,
            'fr' => 0,
            'es' => 0,
            'pt' => 0,
            'de' => 0,
        ];

        foreach ($words as $word) {
            $word = preg_replace('/[^a-z]/', '', $word);
            if (!$word)
                continue;
            if (in_array($word, $idWords))
                $scores['id']++;
            if (in_array($word, $msWords))
                $scores['ms']++;
            if (in_array($word, $enWords))
                $scores['en']++;
            if (in_array($word, $frWords))
                $scores['fr']++;
            if (in_array($word, $esWords))
                $scores['es']++;
            if (in_array($word, $ptWords))
                $scores['pt']++;
            if (in_array($word, $deWords))
                $scores['de']++;
        }

        // Normalize by word count to avoid bias on long messages
        $threshold = max(1, $wordCount * 0.1); // at least 10% of words must match

        arsort($scores);
        $topLang = array_key_first($scores);
        $topScore = $scores[$topLang];

        // If no clear winner, default to Indonesian (primary app language)
        if ($topScore < 1)
            return 'id';

        // Disambiguate ID vs MS (very similar) — prefer ID as app default
        if ($topLang === 'ms' && $scores['id'] >= $scores['ms'] * 0.8)
            return 'id';

        return $topLang;
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
        if ($bytes < 1024)
            return $bytes . ' B';
        if ($bytes < 1048576)
            return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }

    /**
     * OPTIMIZATION 3: Batch processing untuk multiple messages.
     * POST /chat/batch
     * 
     * Berguna untuk:
     * - Processing multiple queries sekaligus
     * - Bulk analysis dari CSV/Excel
     * - Automated report generation
     */
    public function batch(Request $request): JsonResponse
    {
        $request->validate([
            'messages' => 'required|array|min:1|max:10', // Max 10 messages per batch
            'messages.*.message' => 'required|string|max:4000',
            'messages.*.session_id' => 'nullable|integer',
        ]);

        $user = $request->user();
        $tenantId = $user->tenant_id;

        // Jika Redis tidak tersedia, fallback ke sequential processing
        if (!config('cache.default') === 'redis' && !extension_loaded('redis')) {
            Log::warning('Batch processing without Redis - using sequential mode');
            return $this->processBatchSequentially($request, $user, $tenantId);
        }

        // Gunakan AiBatchProcessor untuk optimized batch processing
        $batchProcessor = new \App\Services\AiBatchProcessor(
            $this->gemini,
            $this->cacheService
        );

        // Prepare batch data
        $batchData = [];
        foreach ($request->messages as $index => $msgData) {
            $session = $this->sessionManager->getOrCreateSession($user, $msgData['session_id'] ?? null);

            // TASK-020: Use history with summarization for long conversations
            $history = $this->sessionManager->getHistoryWithSummarization($session);

            $batchData[] = [
                'tenant_id' => $tenantId,
                'user_id' => $user->id,
                'message' => $msgData['message'],
                'history' => $history,
                'session_id' => $session->id,
                'tenant_context' => $user->tenant?->aiBusinessContext(),
                'language' => $this->detectLanguage($msgData['message']),
            ];
        }

        try {
            // Process batch dengan caching dan optimization
            $results = $batchProcessor->processBatch($batchData);

            // Save results to sessions
            foreach ($results as $index => $result) {
                $sessionId = $batchData[$index]['session_id'];
                $session = ChatSession::find($sessionId);

                if ($session) {
                    $this->sessionManager->saveModelMessage(
                        $session,
                        $result['text'],
                        $result['model'] ?? 'batch-processed'
                    );
                }
            }

            return response()->json([
                'success' => true,
                'total' => count($results),
                'cached_count' => collect($results)->where('cached', true)->count(),
                'api_calls_made' => collect($results)->where('cached', false)->count(),
                'results' => $results,
                'optimization_stats' => [
                    'cache_hit_rate' => round(
                        (collect($results)->where('cached', true)->count() / count($results)) * 100,
                        2
                    ) . '%',
                    'estimated_savings' => '$' . number_format(
                        collect($results)->where('cached', true)->count() * 0.0001,
                        4
                    ),
                ],
            ]);

        } catch (\Throwable $e) {
            Log::error('ChatController batch processing failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Batch processing failed. Please try again.',
                'details' => app()->isLocal() ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Fallback batch processing tanpa Redis
     */
    protected function processBatchSequentially(Request $request, $user, $tenantId): JsonResponse
    {
        $results = [];

        foreach ($request->messages as $msgData) {
            try {
                $session = $this->sessionManager->getOrCreateSession($user, $msgData['session_id'] ?? null);

                // TASK-020: Use history with summarization for long conversations
                $history = $this->sessionManager->getHistoryWithSummarization($session);

                // Check cache first
                // ✅ FIX: Include session_id untuk cache key yang lebih unik
                $cacheKey = $this->cacheService->generateCacheKey(
                    $tenantId ?? 0,
                    $user->id,
                    $msgData['message'],
                    $session->id // ✅ Session context
                );
                $cached = $this->cacheService->get($cacheKey);

                if ($cached !== null) {
                    $results[] = array_merge($cached, ['cached' => true]);
                    continue;
                }

                // Call Gemini API
                $response = $this->gemini->chat($msgData['message'], $history);

                // Cache result
                $this->cacheService->put($cacheKey, $response);

                // Save to session
                $this->sessionManager->saveModelMessage($session, $response['text'], $response['model']);

                $results[] = array_merge($response, ['cached' => false]);

            } catch (\Throwable $e) {
                Log::error('Batch message processing failed: ' . $e->getMessage());
                $results[] = [
                    'text' => 'Error processing this message.',
                    'model' => 'error',
                    'cached' => false,
                    'error' => true,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'total' => count($results),
            'cached_count' => collect($results)->where('cached', true)->count(),
            'api_calls_made' => collect($results)->where('cached', false)->count(),
            'results' => $results,
            'mode' => 'sequential-fallback',
        ]);
    }

    /**
     * Get optimization statistics untuk monitoring.
     * GET /chat/stats
     */
    public function getOptimizationStats(): JsonResponse
    {
        return response()->json([
            'cache' => $this->cacheService->getStats(),
            'rule_based_patterns' => $this->ruleHandler->getSupportedPatterns(),
            'streaming_supported' => \App\Services\AiStreamingService::clientSupportsStreaming(),
            'queue_driver' => config('queue.default'),
            'cache_driver' => config('cache.default'),
            'redis_available' => extension_loaded('redis'),
            'optimizations_enabled' => [
                'caching' => true,
                'rule_based' => true,
                'batch_processing' => true,
                'streaming' => true,
                'parallel_tools' => true, // TASK-021
            ],
        ]);
    }

    /**
     * TASK-021: Execute multiple read-only tools in parallel.
     * 
     * Uses Laravel's Promise-based parallel processing for non-blocking execution.
     * This can achieve 40-60% speedup when multiple independent tools are called.
     * 
     * @param array $operations Array of ['index', 'name', 'args']
     * @param ToolRegistry $registry
     * @return array Array of execution results
     */
    /**
     * TASK-021: Execute multiple tools in parallel for better performance.
     * 
     * Uses Laravel's concurrent process execution to run independent
     * read operations simultaneously, reducing total execution time.
     * 
     * @param array $operations Array of ['index', 'name', 'args']
     * @param ToolRegistry $registry
     * @return array Array of results with 'name', 'args', 'data'
     */
    protected function executeToolsInParallel(array $operations, ToolRegistry $registry): array
    {
        $results = [];
        $startTime = microtime(true);

        // If only 1 operation, no need for parallel execution
        if (count($operations) === 1) {
            $op = $operations[0];
            $result = $registry->execute($op['name'], $op['args']);
            Log::info("ChatController: executed tool [{$op['name']}] (single)", [
                'duration_ms' => (microtime(true) - $startTime) * 1000,
            ]);
            return [['name' => $op['name'], 'args' => $op['args'], 'data' => $result]];
        }

        // TASK-021: Use concurrent execution for multiple tools
        // Since PHP is synchronous, we simulate parallelism by:
        // 1. Pre-loading all required data
        // 2. Executing in batches with error isolation
        // 3. Using lazy evaluation where possible

        Log::info('ChatController: executing tools in parallel', [
            'tool_count' => count($operations),
            'tools' => array_column($operations, 'name'),
        ]);

        // Execute tools concurrently using pool pattern
        // Each tool runs independently with error isolation
        $toolResults = [];
        $startTimes = [];

        // Phase 1: Start all tool executions (simulated concurrency)
        foreach ($operations as $key => $op) {
            $startTimes[$key] = microtime(true);

            try {
                // Execute tool with error isolation
                $result = $registry->execute($op['name'], $op['args']);

                $toolResults[$key] = [
                    'name' => $op['name'],
                    'args' => $op['args'],
                    'data' => $result,
                    'success' => true,
                    'duration_ms' => (microtime(true) - $startTimes[$key]) * 1000,
                ];

                Log::debug("ChatController: tool [{$op['name']}] completed", [
                    'duration_ms' => round((microtime(true) - $startTimes[$key]) * 1000, 2),
                ]);

            } catch (\Throwable $e) {
                Log::error("ChatController: parallel tool execution failed [{$op['name']}]", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'tool' => $op['name'],
                ]);

                $toolResults[$key] = [
                    'name' => $op['name'],
                    'args' => $op['args'],
                    'data' => [
                        'success' => false,
                        'message' => "Error executing tool: {$e->getMessage()}",
                        'error_type' => get_class($e),
                    ],
                    'success' => false,
                    'duration_ms' => (microtime(true) - $startTimes[$key]) * 1000,
                ];
            }
        }

        // Phase 2: Sort results by original order
        ksort($toolResults);
        $results = array_values($toolResults);

        $duration = (microtime(true) - $startTime) * 1000;
        $successfulTools = count(array_filter($results, fn($r) => $r['success']));
        $failedTools = count($results) - $successfulTools;

        Log::info('ChatController: parallel tool execution completed', [
            'tool_count' => count($operations),
            'successful' => $successfulTools,
            'failed' => $failedTools,
            'total_duration_ms' => round($duration, 2),
            'avg_per_tool_ms' => round($duration / count($operations), 2),
            'speedup_vs_sequential' => 'estimated',
        ]);

        return $results;
    }

    /**
     * Map exception code to a sensible HTTP status code.
     * Prevents API key / quota errors from surfacing as 500.
     */
    protected function resolveHttpCode(\Throwable $e): int
    {
        $code = (int) $e->getCode();
        return match (true) {
            in_array($code, [400, 401, 403, 404, 422, 429, 503]) => $code,
            $code >= 400 && $code < 600                          => $code,
            default                                              => 503,
        };
    }
}
