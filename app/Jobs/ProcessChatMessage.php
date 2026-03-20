<?php

namespace App\Jobs;

use App\Models\AiUsageLog;
use App\Models\ChatSession;
use App\Models\User;
use App\Services\ChatSessionManager;
use App\Services\ERP\ToolRegistry;
use App\Services\GeminiService;
use App\Services\GeminiWriteValidator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProcessChatMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 60;

    public function __construct(
        public readonly int    $userId,
        public readonly int    $sessionId,
        public readonly string $message,
        public readonly string $cacheKey,  // untuk push hasil ke polling
    ) {}

    public function handle(
        GeminiService      $gemini,
        ChatSessionManager $sessionManager,
        GeminiWriteValidator $validator,
    ): void {
        $user    = User::find($this->userId);
        $session = ChatSession::find($this->sessionId);

        if (!$user || !$session) return;

        $tenantId = $user->tenant_id;
        $history  = $sessionManager->getHistory($session);

        try {
            if (!$tenantId) {
                $response = $gemini->chat($this->message, $history);
                $text     = $response['text'] ?: 'Maaf, tidak ada respons.';
                $sessionManager->saveModelMessage($session, $text, $response['model']);
                $this->pushResult($text, $response['model'], [], $session);
                return;
            }

            $registry = new ToolRegistry($tenantId, $user->id);
            $context  = $this->buildContext($user);

            $response      = $gemini->chatWithTools($context, $history, $registry->getDeclarations());
            $functionCalls = $response['function_calls'] ?? [];

            if (empty($functionCalls)) {
                $text = $response['text'] ?: 'Maaf, saya tidak dapat memproses permintaan tersebut.';
                $sessionManager->saveModelMessage($session, $text, $response['model']);
                AiUsageLog::track($tenantId, $user->id, strlen($text));
                $this->pushResult($text, $response['model'], [], $session);
                return;
            }

            $functionResults = [];
            $executedActions = [];

            foreach ($functionCalls as $call) {
                if ($registry->isWriteOperation($call['name'])) {
                    if (!$validator->validate($call['name'], $call['args'])) continue;
                }
                $result            = $registry->execute($call['name'], $call['args']);
                $functionResults[] = ['name' => $call['name'], 'data' => $result];
                $executedActions[] = ['tool' => $call['name'], 'args' => $call['args'], 'result' => $result];
            }

            $finalResponse = $gemini->sendFunctionResults(
                $this->message, $history, $registry->getDeclarations(), $functionResults
            );

            $finalText = $finalResponse['text'] ?: 'Permintaan telah diproses.';
            $sessionManager->saveModelMessage($session, $finalText, $finalResponse['model'], $executedActions);
            AiUsageLog::track($tenantId, $user->id, strlen($finalText));
            $this->pushResult($finalText, $finalResponse['model'], $executedActions, $session);

        } catch (\Throwable $e) {
            Log::error("ProcessChatMessage failed: " . $e->getMessage());
            Cache::put($this->cacheKey, ['error' => 'Terjadi kesalahan pada sistem AI.'], 120);
        }
    }

    private function buildContext(User $user): string
    {
        $tenant = $user->tenant;
        $ctx    = "[KONTEKS: Pengguna \"{$user->name}\" (role: {$user->role}), perusahaan \"{$tenant?->name}\"]\n\n";
        return $ctx . $this->message;
    }

    private function pushResult(string $text, string $model, array $actions, ChatSession $session): void
    {
        Cache::put($this->cacheKey, [
            'message'       => $text,
            'model'         => $model,
            'actions'       => $actions,
            'session_id'    => $session->id,
            'session_title' => $session->fresh()->title,
        ], 120); // TTL 2 menit
    }

    public function failed(\Throwable $e): void
    {
        Cache::put($this->cacheKey, ['error' => 'Terjadi kesalahan pada sistem AI.'], 120);
        Log::error("ProcessChatMessage job failed permanently: " . $e->getMessage());
    }
}
