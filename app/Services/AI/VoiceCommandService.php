<?php

namespace App\Services\AI;

use App\Models\VoiceCommand;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class VoiceCommandService
{
    /**
     * Process voice command (speech-to-text + intent detection)
     */
    public function processVoiceCommand(string $audioPath, int $tenantId, int $userId): array
    {
        try {
            // Step 1: Speech-to-Text
            $transcribedText = $this->speechToText($audioPath);

            if (!$transcribedText) {
                return [
                    'success' => false,
                    'error' => 'Failed to transcribe audio'
                ];
            }

            // Step 2: Intent Detection & Entity Extraction
            $intentData = $this->detectIntent($transcribedText);

            // Step 3: Execute Command
            $executionResult = $this->executeCommand($intentData, $tenantId);

            // Step 4: Log command
            $voiceCommand = VoiceCommand::create([
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'audio_path' => $audioPath,
                'transcribed_text' => $transcribedText,
                'intent' => $intentData['intent'],
                'extracted_entities' => $intentData['entities'],
                'status' => $executionResult['success'] ? 'executed' : 'failed',
                'execution_result' => $executionResult,
                'error_message' => $executionResult['error'] ?? null,
            ]);

            return [
                'success' => true,
                'command_id' => $voiceCommand->id,
                'transcribed_text' => $transcribedText,
                'intent' => $intentData['intent'],
                'entities' => $intentData['entities'],
                'result' => $executionResult,
            ];

        } catch (\Throwable $e) {
            Log::error('Voice command processing failed: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Convert speech to text using Google Cloud Speech-to-Text or similar
     */
    protected function speechToText(string $audioPath): ?string
    {
        try {
            // Option 1: Google Cloud Speech-to-Text API
            $apiKey = config('services.google.cloud_api_key');

            if ($apiKey) {
                return $this->googleSpeechToText($audioPath, $apiKey);
            }

            // Option 2: OpenAI Whisper API (fallback)
            $openAiKey = config('services.openai.api_key');

            if ($openAiKey) {
                return $this->openAIWhisper($audioPath, $openAiKey);
            }

            // Option 3: Mock for development
            return $this->mockSpeechToText($audioPath);

        } catch (\Throwable $e) {
            Log::error('Speech-to-text failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Google Cloud Speech-to-Text
     */
    protected function googleSpeechToText(string $audioPath, string $apiKey): ?string
    {
        $audioContent = base64_encode(Storage::get($audioPath));

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post("https://speech.googleapis.com/v1/speech:recognize?key={$apiKey}", [
                    'config' => [
                        'encoding' => 'LINEAR16',
                        'sampleRateHertz' => 16000,
                        'languageCode' => 'id-ID', // Indonesian
                        'enableAutomaticPunctuation' => true,
                    ],
                    'audio' => [
                        'content' => $audioContent,
                    ],
                ]);

        if ($response->successful()) {
            $results = $response->json('results');
            return $results[0]['alternatives'][0]['transcript'] ?? null;
        }

        return null;
    }

    /**
     * OpenAI Whisper API
     */
    protected function openAIWhisper(string $audioPath, string $apiKey): ?string
    {
        $response = Http::withToken($apiKey)
            ->asMultipart()
            ->post('https://api.openai.com/v1/audio/transcriptions', [
                'file' => fopen(storage_path('app/' . $audioPath), 'r'),
                'model' => 'whisper-1',
                'language' => 'id', // Indonesian
            ]);

        if ($response->successful()) {
            return $response->json('text');
        }

        return null;
    }

    /**
     * Mock speech-to-text for development
     */
    protected function mockSpeechToText(string $audioPath): string
    {
        // For testing without API keys
        return "Buat invoice untuk pelanggan John Doe sebesar 500000 rupiah";
    }

    /**
     * Detect intent and extract entities from text
     */
    protected function detectIntent(string $text): array
    {
        // Use NLP service or regex patterns
        $intents = [
            'create_invoice' => '/buat invoice|invoice baru|faktur/i',
            'check_stock' => '/cek stok|stok produk|inventory/i',
            'create_product' => '/tambah produk|produk baru|buat produk/i',
            'view_report' => '/lihat laporan|report|statistik/i',
            'search_customer' => '/cari pelanggan|customer|kontak/i',
        ];

        $detectedIntent = 'unknown';
        $entities = [];

        foreach ($intents as $intent => $pattern) {
            if (preg_match($pattern, $text)) {
                $detectedIntent = $intent;
                break;
            }
        }

        // Extract entities based on intent
        switch ($detectedIntent) {
            case 'create_invoice':
                // Extract amount
                if (preg_match('/(\d+)\s*(ribu|juta|ratus)/i', $text, $matches)) {
                    $amount = $this->parseAmount($matches[1], $matches[2]);
                    $entities['amount'] = $amount;
                }

                // Extract customer name (simplified)
                if (preg_match('/untuk\s+([A-Z][a-z]+\s+[A-Z][a-z]+)/i', $text, $matches)) {
                    $entities['customer_name'] = $matches[1];
                }
                break;

            case 'check_stock':
                // Extract product name
                if (preg_match('/produk\s+(\w+)/i', $text, $matches)) {
                    $entities['product_name'] = $matches[1];
                }
                break;
        }

        return [
            'intent' => $detectedIntent,
            'entities' => $entities,
            'confidence' => 0.85,
        ];
    }

    /**
     * Execute detected command
     */
    protected function executeCommand(array $intentData, int $tenantId): array
    {
        try {
            switch ($intentData['intent']) {
                case 'create_invoice':
                    return $this->executeCreateInvoice($intentData['entities'], $tenantId);

                case 'check_stock':
                    return $this->executeCheckStock($intentData['entities'], $tenantId);

                case 'create_product':
                    return $this->executeCreateProduct($intentData['entities'], $tenantId);

                default:
                    return [
                        'success' => false,
                        'error' => 'Unknown intent: ' . $intentData['intent']
                    ];
            }
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Execute: Create Invoice
     */
    protected function executeCreateInvoice(array $entities, int $tenantId): array
    {
        // Implementation would create actual invoice
        return [
            'success' => true,
            'message' => "Invoice created for {$entities['customer_name']} with amount Rp " . number_format($entities['amount'] ?? 0),
            'action' => 'invoice_created',
        ];
    }

    /**
     * Execute: Check Stock
     */
    protected function executeCheckStock(array $entities, int $tenantId): array
    {
        // Implementation would query inventory
        return [
            'success' => true,
            'message' => "Stock check for product: {$entities['product_name']}",
            'action' => 'stock_checked',
        ];
    }

    /**
     * Execute: Create Product
     */
    protected function executeCreateProduct(array $entities, int $tenantId): array
    {
        return [
            'success' => true,
            'message' => "Product creation initiated",
            'action' => 'product_creation_started',
        ];
    }

    /**
     * Parse amount from text
     */
    protected function parseAmount(string $number, string $unit): int
    {
        $multiplier = match (strtolower($unit)) {
            'ratus' => 100,
            'ribu' => 1000,
            'juta' => 1000000,
            default => 1
        };

        return intval($number) * $multiplier;
    }

    /**
     * Get voice command history
     */
    public function getCommandHistory(int $tenantId, int $limit = 20): array
    {
        return VoiceCommand::where('tenant_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get command statistics
     */
    public function getCommandStats(int $tenantId): array
    {
        $total = VoiceCommand::where('tenant_id', $tenantId)->count();
        $success = VoiceCommand::where('tenant_id', $tenantId)
            ->where('status', 'executed')
            ->count();

        $byIntent = VoiceCommand::where('tenant_id', $tenantId)
            ->selectRaw('intent, COUNT(*) as count')
            ->groupBy('intent')
            ->pluck('count', 'intent')
            ->toArray();

        return [
            'total_commands' => $total,
            'successful_commands' => $success,
            'success_rate' => $total > 0 ? round(($success / $total) * 100, 2) : 0,
            'by_intent' => $byIntent,
        ];
    }
}
