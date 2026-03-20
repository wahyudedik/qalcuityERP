<?php

namespace App\Services;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\User;

class ChatSessionManager
{
    // Batas history yang dikirim ke Gemini (hemat token)
    const MAX_HISTORY_MESSAGES = 20;
    // Estimasi token per karakter (rough estimate)
    const CHARS_PER_TOKEN = 4;

    public function getOrCreateSession(User $user, ?int $sessionId = null): ChatSession
    {
        if ($sessionId) {
            $session = ChatSession::where('id', $sessionId)
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->first();

            if ($session) return $session;
        }

        return ChatSession::create([
            'tenant_id' => $user->tenant_id,  // null untuk super_admin, itu OK
            'user_id'   => $user->id,
            'title'     => null,
            'is_active' => true,
        ]);
    }

    /**
     * Ambil history yang sudah dipangkas untuk efisiensi token.
     * Strategi: ambil N pesan terakhir, selalu sertakan pesan pertama sebagai konteks awal.
     */
    public function getHistory(ChatSession $session): array
    {
        $messages = $session->messages()
            ->orderBy('id')
            ->get(['role', 'content']);

        if ($messages->count() <= self::MAX_HISTORY_MESSAGES) {
            return $messages->map(fn($m) => ['role' => $m->role, 'text' => $m->content])->toArray();
        }

        // Ambil pesan pertama (konteks awal) + N pesan terakhir
        $first  = $messages->first();
        $recent = $messages->slice(-self::MAX_HISTORY_MESSAGES + 1);

        return collect([$first])
            ->merge($recent)
            ->unique('id')
            ->map(fn($m) => ['role' => $m->role, 'text' => $m->content])
            ->values()
            ->toArray();
    }

    public function saveUserMessage(ChatSession $session, string $content): ChatMessage
    {
        return $session->messages()->create([
            'role'        => 'user',
            'content'     => $content,
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
            'role'           => 'model',
            'content'        => $content,
            'model_used'     => $model,
            'token_count'    => $tokens,
            'function_calls' => !empty($functionCalls) ? $functionCalls : null,
        ]);

        // Update total token di session
        $session->increment('total_tokens', $tokens);
        $session->update(['last_model' => $model]);

        // Auto-set judul session dari pesan pertama user jika belum ada
        if (!$session->title) {
            $firstUserMsg = $session->messages()->where('role', 'user')->first();
            if ($firstUserMsg) {
                $session->update(['title' => substr($firstUserMsg->content, 0, 60)]);
            }
        }

        return $message;
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

    protected function estimateTokens(string $text): int
    {
        return (int) ceil(strlen($text) / self::CHARS_PER_TOKEN);
    }
}
