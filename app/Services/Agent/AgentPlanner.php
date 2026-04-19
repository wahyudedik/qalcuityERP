<?php

namespace App\Services\Agent;

use App\DTOs\Agent\AgentPlan;
use App\DTOs\Agent\AgentStep;
use App\DTOs\Agent\ErpContext;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Log;

/**
 * AgentPlanner — Task 4
 *
 * Memecah instruksi kompleks menjadi rencana langkah-langkah yang dapat
 * dieksekusi oleh AgentExecutor. Menggunakan GeminiService dengan planning
 * prompt khusus dan menghasilkan maksimal 10 langkah.
 *
 * Requirements: 1.1, 1.2, 1.6
 */
class AgentPlanner
{
    /** Maksimal langkah dalam satu plan */
    private const MAX_STEPS = 10;

    /**
     * Kata kunci yang mengindikasikan instruksi multi-step (Bahasa Indonesia & Inggris).
     */
    private const MULTI_STEP_KEYWORDS = [
        // Bahasa Indonesia
        'kemudian', 'lalu', 'setelah itu', 'selanjutnya', 'dan kemudian',
        'pertama', 'kedua', 'ketiga', 'langkah', 'tahap', 'proses',
        'buat dan', 'cek dan', 'analisis dan', 'laporan dan',
        'sekaligus', 'bersamaan', 'juga', 'serta',
        'bandingkan', 'korelasikan', 'gabungkan',
        // English
        'then', 'after that', 'next', 'first', 'second', 'third',
        'step', 'process', 'create and', 'check and', 'analyze and',
        'compare', 'correlate', 'combine', 'also', 'as well as',
        'followed by', 'and then',
    ];

    /**
     * Kata kunci yang mengindikasikan operasi write.
     */
    private const WRITE_KEYWORDS = [
        // Bahasa Indonesia
        'buat', 'tambah', 'update', 'ubah', 'hapus', 'simpan', 'catat',
        'input', 'masukkan', 'daftarkan', 'sesuaikan', 'koreksi',
        'posting', 'jurnal', 'invoice', 'purchase order', 'po',
        // English
        'create', 'add', 'update', 'change', 'delete', 'save', 'record',
        'insert', 'register', 'adjust', 'correct', 'post',
    ];

    public function __construct(
        private readonly GeminiService $gemini,
    ) {}

    /**
     * Buat rencana eksekusi dari instruksi user.
     * Menggunakan GeminiService dengan planning prompt khusus.
     * Retry 1x jika Gemini gagal, lalu fallback ke single-turn.
     *
     * @param  string     $instruction    Instruksi dari user
     * @param  ErpContext $context        Konteks ERP tenant
     * @param  array      $availableTools Daftar tool yang tersedia
     * @param  string     $language       Bahasa ('id' atau 'en')
     * @return AgentPlan dengan maksimal 10 langkah
     */
    public function plan(
        string $instruction,
        ErpContext $context,
        array $availableTools,
        string $language = 'id',
    ): AgentPlan {
        // Instruksi kosong → single-turn fallback
        if (trim($instruction) === '') {
            return $this->buildFallbackPlan($instruction, $language);
        }

        $prompt = $this->buildPlanningPrompt($instruction, $context, $availableTools, $language);

        // Coba generate plan, retry 1x jika gagal
        $rawResponse = $this->callGeminiWithRetry($prompt);

        if ($rawResponse === null) {
            Log::warning('AgentPlanner: Gemini gagal setelah retry, fallback ke single-turn', [
                'instruction' => substr($instruction, 0, 100),
            ]);
            return $this->buildFallbackPlan($instruction, $language);
        }

        // Parse response menjadi AgentPlan
        $plan = $this->parseResponse($rawResponse, $instruction, $language);

        if ($plan === null) {
            Log::warning('AgentPlanner: gagal parse response Gemini, fallback ke single-turn', [
                'instruction' => substr($instruction, 0, 100),
                'response'    => substr($rawResponse, 0, 200),
            ]);
            return $this->buildFallbackPlan($instruction, $language);
        }

        return $plan;
    }

    /**
     * Deteksi apakah instruksi memerlukan multi-step planning
     * atau bisa langsung dijawab sebagai single-turn.
     *
     * @param  string $instruction
     * @return bool   true jika perlu planning
     */
    public function requiresPlanning(string $instruction): bool
    {
        if (trim($instruction) === '') {
            return false;
        }

        $lower = mb_strtolower($instruction);

        foreach (self::MULTI_STEP_KEYWORDS as $keyword) {
            if (str_contains($lower, $keyword)) {
                return true;
            }
        }

        // Heuristik: instruksi panjang (> 100 karakter) cenderung multi-step
        if (mb_strlen($instruction) > 100) {
            return true;
        }

        // Heuristik: mengandung tanda koma yang memisahkan beberapa aksi
        if (substr_count($lower, ',') >= 2) {
            return true;
        }

        return false;
    }

    // ─── Private: Prompt Builder ──────────────────────────────────────────────

    /**
     * Bangun planning prompt untuk Gemini.
     */
    private function buildPlanningPrompt(
        string $instruction,
        ErpContext $context,
        array $availableTools,
        string $language,
    ): string {
        $toolList = $this->formatToolList($availableTools);
        $erpContext = $context->toSystemPrompt();
        $langInstruction = $language === 'id'
            ? 'Balas dalam Bahasa Indonesia.'
            : 'Reply in English.';

        return <<<PROMPT
        You are an ERP AI Agent planner. Your task is to break down a user instruction into an ordered execution plan.

        {$erpContext}

        Available Tools:
        {$toolList}

        User Instruction: {$instruction}

        {$langInstruction}

        Create an execution plan with AT MOST 10 steps. Each step must call one of the available tools.
        Respond ONLY with a valid JSON object in this exact format (no markdown, no explanation):
        {
          "goal": "brief description of the overall goal",
          "summary": "what will be accomplished",
          "steps": [
            {
              "order": 1,
              "name": "step name",
              "toolName": "tool_name_from_available_tools",
              "args": {"key": "value"},
              "isWriteOp": false,
              "dependsOnStep": null
            }
          ]
        }

        Rules:
        - Maximum 10 steps
        - Each step must have: order (integer), name (non-empty string), toolName (non-empty string), args (object), isWriteOp (boolean)
        - dependsOnStep is optional (null or step name string)
        - isWriteOp must be true for create/update/delete operations
        - Steps must be ordered (order starts at 1)
        PROMPT;
    }

    /**
     * Format daftar tool menjadi string untuk prompt.
     */
    private function formatToolList(array $availableTools): string
    {
        if (empty($availableTools)) {
            return '(no tools available — use "answer" as toolName for direct responses)';
        }

        $lines = [];
        foreach ($availableTools as $tool) {
            if (is_array($tool)) {
                $name = $tool['name'] ?? $tool['toolName'] ?? 'unknown';
                $desc = $tool['description'] ?? '';
                $lines[] = "- {$name}: {$desc}";
            } elseif (is_string($tool)) {
                $lines[] = "- {$tool}";
            }
        }

        return implode("\n", $lines);
    }

    // ─── Private: Gemini Call ─────────────────────────────────────────────────

    /**
     * Panggil Gemini dengan retry 1x jika gagal.
     * Return null jika kedua percobaan gagal.
     */
    private function callGeminiWithRetry(string $prompt): ?string
    {
        // Percobaan pertama
        try {
            $response = $this->gemini->generate($prompt);
            $text = $response['text'] ?? '';
            if (!empty(trim($text))) {
                return $text;
            }
        } catch (\Throwable $e) {
            Log::warning('AgentPlanner: percobaan pertama Gemini gagal', [
                'error' => $e->getMessage(),
            ]);
        }

        // Retry sekali
        try {
            $response = $this->gemini->generate($prompt);
            $text = $response['text'] ?? '';
            if (!empty(trim($text))) {
                return $text;
            }
        } catch (\Throwable $e) {
            Log::warning('AgentPlanner: retry Gemini juga gagal', [
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    // ─── Private: Response Parser ─────────────────────────────────────────────

    /**
     * Parse raw Gemini response menjadi AgentPlan.
     * Return null jika response tidak valid.
     */
    private function parseResponse(string $rawResponse, string $instruction, string $language): ?AgentPlan
    {
        // Bersihkan markdown code blocks jika ada
        $json = $this->extractJson($rawResponse);

        if ($json === null) {
            return null;
        }

        $data = json_decode($json, true);

        if (!is_array($data)) {
            return null;
        }

        // Validasi struktur dasar
        if (empty($data['steps']) || !is_array($data['steps'])) {
            return null;
        }

        $steps = $this->parseSteps($data['steps']);

        if (empty($steps)) {
            return null;
        }

        // Batasi maksimal 10 langkah
        $steps = array_slice($steps, 0, self::MAX_STEPS);

        $hasWriteOps = collect($steps)->contains(fn(AgentStep $s) => $s->isWriteOp);

        return new AgentPlan(
            goal: $data['goal'] ?? $instruction,
            steps: $steps,
            summary: $data['summary'] ?? '',
            hasWriteOps: $hasWriteOps,
            language: $language,
        );
    }

    /**
     * Ekstrak JSON dari response yang mungkin mengandung markdown.
     */
    private function extractJson(string $text): ?string
    {
        $text = trim($text);

        // Hapus markdown code blocks
        $text = preg_replace('/^```(?:json)?\s*/m', '', $text);
        $text = preg_replace('/\s*```$/m', '', $text);
        $text = trim($text);

        // Cari JSON object
        $start = strpos($text, '{');
        $end   = strrpos($text, '}');

        if ($start === false || $end === false || $end <= $start) {
            return null;
        }

        return substr($text, $start, $end - $start + 1);
    }

    /**
     * Parse array steps dari data JSON menjadi array AgentStep.
     * Validasi setiap step dan skip yang tidak valid.
     */
    private function parseSteps(array $rawSteps): array
    {
        $steps = [];

        foreach ($rawSteps as $index => $raw) {
            if (!is_array($raw)) {
                continue;
            }

            $name     = trim($raw['name'] ?? '');
            $toolName = trim($raw['toolName'] ?? $raw['tool_name'] ?? '');

            // Validasi field wajib
            if ($name === '' || $toolName === '') {
                continue;
            }

            $order      = isset($raw['order']) ? (int) $raw['order'] : ($index + 1);
            $args       = is_array($raw['args'] ?? null) ? $raw['args'] : [];
            $isWriteOp  = (bool) ($raw['isWriteOp'] ?? $raw['is_write_op'] ?? false);
            $dependsOn  = isset($raw['dependsOnStep']) && $raw['dependsOnStep'] !== null
                ? (string) $raw['dependsOnStep']
                : null;

            $steps[] = new AgentStep(
                order: $order,
                name: $name,
                toolName: $toolName,
                args: $args,
                isWriteOp: $isWriteOp,
                dependsOnStep: $dependsOn,
            );
        }

        // Urutkan berdasarkan order
        usort($steps, fn(AgentStep $a, AgentStep $b) => $a->order <=> $b->order);

        return $steps;
    }

    // ─── Private: Fallback ────────────────────────────────────────────────────

    /**
     * Buat single-turn fallback plan ketika Gemini gagal atau instruksi sederhana.
     */
    private function buildFallbackPlan(string $instruction, string $language): AgentPlan
    {
        $goal = trim($instruction) !== ''
            ? $instruction
            : ($language === 'id' ? 'Jawab pertanyaan user' : 'Answer user question');

        $stepName = $language === 'id' ? 'Jawab langsung' : 'Direct answer';

        return new AgentPlan(
            goal: $goal,
            steps: [
                new AgentStep(
                    order: 1,
                    name: $stepName,
                    toolName: 'answer',
                    args: ['instruction' => $instruction],
                    isWriteOp: false,
                    dependsOnStep: null,
                ),
            ],
            summary: $goal,
            hasWriteOps: false,
            language: $language,
        );
    }
}
