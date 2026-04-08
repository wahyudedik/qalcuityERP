<?php

namespace App\Services;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\User;
use App\Services\AiMemoryService;

class ChatSessionManager
{
    // Batas history yang dikirim ke Gemini (hemat token)
    const MAX_HISTORY_MESSAGES = 20;
    // Batas maksimum messages yang disimpan per session (hard limit)
    const MAX_MESSAGES_PER_SESSION = 100;
    // Estimasi token per karakter (rough estimate)
    const CHARS_PER_TOKEN = 4;

    public function getOrCreateSession(User $user, ?int $sessionId = null): ChatSession
    {
        if ($sessionId) {
            $session = ChatSession::where('id', $sessionId)
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->first();

            if ($session)
                return $session;
        }

        return ChatSession::create([
            'tenant_id' => $user->tenant_id,  // null untuk super_admin, itu OK
            'user_id' => $user->id,
            'title' => null,
            'is_active' => true,
        ]);
    }

    /**
     * Ambil history yang sudah dipangkas untuk efisiensi token.
     * BUG-AI-001 FIX: Database-level limit to prevent memory exhaustion
     * Strategi: ambil N pesan terakhir, selalu sertakan pesan pertama sebagai konteks awal.
     */
    public function getHistory(ChatSession $session): array
    {
        // BUG-AI-001 FIX: Get total count first (cheap query)
        $totalMessages = $session->messages()->count();

        // If session has too many messages, warn about truncation
        if ($totalMessages > self::MAX_MESSAGES_PER_SESSION) {
            \Log::warning('Chat session exceeds max messages, truncating history', [
                'session_id' => $session->id,
                'total_messages' => $totalMessages,
                'max_allowed' => self::MAX_MESSAGES_PER_SESSION,
            ]);
        }

        // BUG-AI-001 FIX: Only load messages we actually need
        if ($totalMessages <= self::MAX_HISTORY_MESSAGES) {
            // Small session: load all messages
            $messages = $session->messages()
                ->orderBy('id')
                ->get(['id', 'role', 'content']);
        } else {
            // Large session: load first message + last N messages only
            $firstMessage = $session->messages()
                ->orderBy('id')
                ->first(['id', 'role', 'content']);

            $recentMessages = $session->messages()
                ->orderByDesc('id')
                ->limit(self::MAX_HISTORY_MESSAGES - 1) // -1 because we include first
                ->get(['id', 'role', 'content'])
                ->sortBy('id'); // Re-sort by ascending ID

            // Merge first + recent, avoiding duplicates
            $messages = collect([$firstMessage])
                ->merge($recentMessages)
                ->unique('id')
                ->values();
        }

        return $messages->map(fn($m) => ['role' => $m->role, 'text' => $m->content])->toArray();
    }

    public function saveUserMessage(ChatSession $session, string $content): ChatMessage
    {
        return $session->messages()->create([
            'role' => 'user',
            'content' => $content,
            'token_count' => $this->estimateTokens($content),
        ]);
    }

    public function saveModelMessage(
        ChatSession $session,
        string $content,
        string $model,
        array $functionCalls = []
    ): ChatMessage {
        $tokens = $this->estimateTokens($content);

        $message = $session->messages()->create([
            'role' => 'model',
            'content' => $content,
            'model_used' => $model,
            'token_count' => $tokens,
            'function_calls' => !empty($functionCalls) ? $functionCalls : null,
        ]);

        // Update total token di session (single query)
        $session->increment('total_tokens', $tokens);
        $session->last_model = $model;
        $session->save();

        // Auto-set judul session dari pesan pertama user jika belum ada
        if (!$session->title) {
            $firstUserMsg = $session->messages()->where('role', 'user')->value('content');
            if ($firstUserMsg) {
                // Bersihkan context prefix [KONTEKS SISTEM: ...] sebelum dijadikan judul
                $clean = preg_replace('/^\[KONTEKS SISTEM:.*?\]\n\n/s', '', $firstUserMsg);
                $title = mb_substr(trim($clean), 0, 60);
                $session->title = $title;
                $session->save();
            }
        }

        // BUG-AI-001 FIX: Auto-purge old messages if session exceeds limit
        $this->purgeOldMessagesIfNeeded($session);

        // Rekam pola aksi ke AI memory (Task 52)
        if (!empty($functionCalls) && $session->user_id && $session->tenant_id) {
            $this->recordActionsToMemory($session->tenant_id, $session->user_id, $functionCalls);
        }

        return $message;
    }

    /**
     * Rekam pola tool calls ke AI memory untuk pembelajaran preferensi.
     */
    private function recordActionsToMemory(int $tenantId, int $userId, array $functionCalls): void
    {
        try {
            $memoryService = app(AiMemoryService::class);
            foreach ($functionCalls as $call) {
                $toolName = $call['tool'] ?? '';
                $args = $call['args'] ?? [];

                // Rekam metode pembayaran
                if (!empty($args['payment_method'])) {
                    $memoryService->recordAction($tenantId, $userId, 'payment_method', ['value' => $args['payment_method']]);
                }
                // Rekam gudang default
                if (!empty($args['warehouse_name'])) {
                    $memoryService->recordAction($tenantId, $userId, 'warehouse', ['value' => $args['warehouse_name']]);
                }
                // Rekam customer yang sering digunakan
                if (!empty($args['customer_name'])) {
                    $memoryService->recordAction($tenantId, $userId, 'customer', ['value' => $args['customer_name']]);
                }
                // Rekam produk yang sering digunakan
                if (!empty($args['product_name'])) {
                    $memoryService->recordAction($tenantId, $userId, 'product', ['value' => $args['product_name']]);
                }
                // Rekam supplier yang sering digunakan
                if (!empty($args['supplier_name'])) {
                    $memoryService->recordAction($tenantId, $userId, 'frequent_suppliers', [
                        'value' => $args['supplier_name'],
                        'product_name' => $args['product_name'] ?? null,
                    ]);
                }
                // Rekam kuantitas order yang umum digunakan
                $qty = $args['quantity'] ?? $args['qty'] ?? null;
                if ($qty !== null) {
                    $memoryService->recordAction($tenantId, $userId, 'typical_order_quantity', ['value' => $qty]);
                }
                // Rekam diskon yang digunakan
                $discount = $args['discount'] ?? $args['discount_percent'] ?? null;
                if ($discount !== null) {
                    $memoryService->recordAction($tenantId, $userId, 'preferred_discount', ['value' => $discount]);
                }
                // Rekam syarat pembayaran yang digunakan
                $paymentTerms = $args['payment_terms'] ?? $args['terms_days'] ?? null;
                if ($paymentTerms !== null) {
                    $memoryService->recordAction($tenantId, $userId, 'preferred_payment_terms', ['value' => $paymentTerms]);
                }
                // Rekam alamat pengiriman yang digunakan
                $address = $args['delivery_address'] ?? $args['address'] ?? null;
                if ($address !== null) {
                    $memoryService->recordAction($tenantId, $userId, 'preferred_delivery_address', ['value' => $address]);
                }
                // Rekam preferensi pajak
                $taxPref = $args['tax_included'] ?? $args['include_tax'] ?? null;
                if ($taxPref !== null) {
                    $memoryService->recordAction($tenantId, $userId, 'tax_preference', ['value' => $taxPref]);
                }
            }
        } catch (\Throwable) {
            // Jangan biarkan error memory mengganggu flow utama
        }
    }

    public function getUserSessions(User $user, int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        return ChatSession::where('user_id', $user->id)
            ->where('is_active', true)
            ->withCount('messages')
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function deleteSession(ChatSession $session): void
    {
        $session->update(['is_active' => false]);
    }

    /**
     * BUG-AI-001 FIX: Auto-purge old messages when session exceeds limit.
     * Keeps first message (for context) + latest N messages.
     */
    protected function purgeOldMessagesIfNeeded(ChatSession $session): void
    {
        $totalMessages = $session->messages()->count();

        if ($totalMessages <= self::MAX_MESSAGES_PER_SESSION) {
            return; // No purge needed
        }

        try {
            // Keep: first message + latest (MAX_MESSAGES_PER_SESSION - 1) messages
            $messagesToKeep = self::MAX_MESSAGES_PER_SESSION - 1;

            // Get IDs of messages to delete (exclude first and latest N)
            $firstMessageId = $session->messages()->orderBy('id')->value('id');

            $messageIdsToDelete = $session->messages()
                ->where('id', '!=', $firstMessageId)
                ->orderByDesc('id')
                ->offset($messagesToKeep) // Skip latest N messages
                ->pluck('id');

            if ($messageIdsToDelete->isNotEmpty()) {
                $deletedCount = $session->messages()
                    ->whereIn('id', $messageIdsToDelete)
                    ->delete();

                \Log::info('Auto-purged old chat messages', [
                    'session_id' => $session->id,
                    'deleted_count' => $deletedCount,
                    'remaining_count' => $session->messages()->count(),
                ]);
            }
        } catch (\Throwable $e) {
            // Don't let purge errors break the chat flow
            \Log::error('Failed to purge old chat messages', [
                'session_id' => $session->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function estimateTokens(string $text): int
    {
        return (int) ceil(strlen($text) / self::CHARS_PER_TOKEN);
    }
}
