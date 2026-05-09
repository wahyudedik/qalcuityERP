<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Product;
use App\Services\AI\ChatbotTrainingService;
use App\Services\AI\DynamicPricingService;
use App\Services\AI\ImageRecognitionService;
use App\Services\AI\PredictiveMaintenanceService;
use App\Services\AI\SentimentAnalysisService;
use App\Services\AI\VoiceCommandService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AIEnhancementController extends Controller
{
    protected $voiceService;

    protected $imageService;

    protected $maintenanceService;

    protected $pricingService;

    protected $sentimentService;

    protected $chatbotService;

    public function __construct(
        VoiceCommandService $voiceService,
        ImageRecognitionService $imageService,
        PredictiveMaintenanceService $maintenanceService,
        DynamicPricingService $pricingService,
        SentimentAnalysisService $sentimentService,
        ChatbotTrainingService $chatbotService
    ) {
        $this->voiceService = $voiceService;
        $this->imageService = $imageService;
        $this->maintenanceService = $maintenanceService;
        $this->pricingService = $pricingService;
        $this->sentimentService = $sentimentService;
        $this->chatbotService = $chatbotService;
    }

    // ==================== VOICE COMMANDS ====================

    public function processVoiceCommand(Request $request)
    {
        $request->validate([
            'audio' => 'required|string',
        ]);

        $result = $this->voiceService->processVoiceCommand(
            $request->audio,
            auth()->user()->tenant_id,
            auth()->id()
        );

        return response()->json($result);
    }

    public function getVoiceCommandHistory()
    {
        $history = $this->voiceService->getCommandHistory(auth()->user()->tenant_id);

        return response()->json(['success' => true, 'history' => $history]);
    }

    public function getVoiceCommandStats()
    {
        $stats = $this->voiceService->getCommandStats(auth()->user()->tenant_id);

        return response()->json(['success' => true, 'stats' => $stats]);
    }

    // ==================== IMAGE RECOGNITION ====================

    public function detectProducts(Request $request)
    {
        $request->validate([
            'image' => 'required|file|image|max:5120',
        ]);

        $path = $request->file('image')->store('ai-images');

        $result = $this->imageService->detectProducts(
            $path,
            auth()->user()->tenant_id,
            auth()->id()
        );

        return response()->json($result);
    }

    public function assessDamage(Request $request)
    {
        $request->validate([
            'image' => 'required|file|image|max:5120',
        ]);

        $path = $request->file('image')->store('ai-images');

        $result = $this->imageService->assessDamage(
            $path,
            auth()->user()->tenant_id,
            auth()->id()
        );

        return response()->json($result);
    }

    public function extractText(Request $request)
    {
        $request->validate([
            'image' => 'required|file|image|max:5120',
        ]);

        $path = $request->file('image')->store('ai-images');

        $result = $this->imageService->extractText(
            $path,
            auth()->user()->tenant_id,
            auth()->id()
        );

        return response()->json($result);
    }

    public function getImageRecognitionHistory(Request $request)
    {
        $type = $request->query('type');
        $history = $this->imageService->getRecognitionHistory(
            auth()->user()->tenant_id,
            $type
        );

        return response()->json(['success' => true, 'history' => $history]);
    }

    public function verifyImageResult(int $resultId)
    {
        $success = $this->imageService->verifyResult($resultId);

        return response()->json(['success' => $success]);
    }

    public function getImageRecognitionStats()
    {
        $stats = $this->imageService->getRecognitionStats(auth()->user()->tenant_id);

        return response()->json(['success' => true, 'stats' => $stats]);
    }

    // ==================== PREDICTIVE MAINTENANCE ====================

    public function predictAllAssets()
    {
        $result = $this->maintenanceService->predictForAllAssets(auth()->user()->tenant_id);

        return response()->json($result);
    }

    public function predictAsset(int $assetId)
    {
        $asset = Asset::findOrFail($assetId);
        $result = $this->maintenanceService->predictForAsset($asset);

        return response()->json($result ? ['success' => true, 'prediction' => $result] : ['success' => false]);
    }

    public function scheduleMaintenance(Request $request, int $predictionId)
    {
        $request->validate([
            'scheduled_date' => 'required|date|after:today',
        ]);

        $success = $this->maintenanceService->scheduleMaintenance(
            $predictionId,
            $request->scheduled_date,
            auth()->id()
        );

        return response()->json(['success' => $success]);
    }

    public function markMaintenanceCompleted(Request $request, int $predictionId)
    {
        $success = $this->maintenanceService->markCompleted(
            $predictionId,
            $request->notes ?? ''
        );

        return response()->json(['success' => $success]);
    }

    public function getPendingPredictions(Request $request)
    {
        $severity = $request->query('severity');
        $predictions = $this->maintenanceService->getPendingPredictions(
            auth()->user()->tenant_id,
            $severity
        );

        return response()->json(['success' => true, 'predictions' => $predictions]);
    }

    public function getMaintenanceStats()
    {
        $stats = $this->maintenanceService->getMaintenanceStats(auth()->user()->tenant_id);

        return response()->json(['success' => true, 'stats' => $stats]);
    }

    public function dismissPrediction(Request $request, int $predictionId)
    {
        $success = $this->maintenanceService->dismissPrediction(
            $predictionId,
            $request->reason ?? ''
        );

        return response()->json(['success' => $success]);
    }

    // ==================== DYNAMIC PRICING ====================

    public function calculatePrice(int $productId)
    {
        $product = Product::findOrFail($productId);
        $result = $this->pricingService->calculatePrice($product);

        return response()->json($result);
    }

    public function applyPricingRule(Request $request, int $productId)
    {
        $request->validate([
            'rule_id' => 'required|exists:dynamic_pricing_rules,id',
        ]);

        $result = $this->pricingService->applyRule(
            $productId,
            $request->rule_id,
            auth()->id()
        );

        return response()->json($result);
    }

    public function getPricingRecommendations()
    {
        $result = $this->pricingService->getRecommendations(auth()->user()->tenant_id);

        return response()->json($result);
    }

    public function createPricingRule(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'conditions' => 'sometimes|array',
            'formula' => 'sometimes|array',
        ]);

        $success = $this->pricingService->createRule(
            auth()->user()->tenant_id,
            $request->name,
            $request->conditions ?? [],
            $request->formula ?? []
        );

        return response()->json(['success' => $success]);
    }

    public function getPricingHistory(int $productId)
    {
        $history = $this->pricingService->getPricingHistory($productId);

        return response()->json(['success' => true, 'history' => $history]);
    }

    // ==================== SENTIMENT ANALYSIS ====================

    public function analyzeSentiment(Request $request)
    {
        $request->validate([
            'text' => 'required|string',
            'source_type' => 'required|string|in:review,feedback,survey,social_media',
            'source_id' => 'sometimes|integer',
        ]);

        $result = $this->sentimentService->analyzeSentiment(
            $request->text,
            $request->source_type,
            $request->source_id,
            auth()->user()->tenant_id
        );

        return response()->json($result);
    }

    public function getPendingAnalyses()
    {
        $analyses = $this->sentimentService->getPendingAnalyses(auth()->user()->tenant_id);

        return response()->json(['success' => true, 'analyses' => $analyses]);
    }

    public function markReviewed(int $analysisId)
    {
        $success = $this->sentimentService->markReviewed($analysisId, auth()->id());

        return response()->json(['success' => $success]);
    }

    public function getSentimentStats(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $stats = $this->sentimentService->getSentimentStats(
            auth()->user()->tenant_id,
            $startDate ? Carbon::parse($startDate) : null,
            $endDate ? Carbon::parse($endDate) : null
        );

        return response()->json(['success' => true, 'stats' => $stats]);
    }

    public function getSentimentTrends(Request $request)
    {
        $period = $request->query('period', 'daily');
        $trends = $this->sentimentService->getSentimentTrends(
            auth()->user()->tenant_id,
            $period
        );

        return response()->json($trends);
    }

    // ==================== CHATBOT TRAINING ====================

    public function trainFromHistory()
    {
        $result = $this->chatbotService->trainFromHistory(auth()->user()->tenant_id);

        return response()->json($result);
    }

    public function addTrainingData(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'question' => 'required|string',
            'answer' => 'required|string',
        ]);

        $success = $this->chatbotService->addTrainingData(
            auth()->user()->tenant_id,
            $request->category,
            $request->question,
            $request->answer,
            $request->context ?? []
        );

        return response()->json(['success' => $success]);
    }

    public function findBotResponse(Request $request)
    {
        $request->validate([
            'question' => 'required|string',
        ]);

        $result = $this->chatbotService->findBestResponse(
            $request->question,
            auth()->user()->tenant_id
        );

        return response()->json($result);
    }

    public function logConversation(Request $request)
    {
        $request->validate([
            'user_message' => 'required|string',
            'bot_response' => 'required|string',
        ]);

        $conversationId = $this->chatbotService->logConversation(
            auth()->user()->tenant_id,
            auth()->id(),
            $request->user_message,
            $request->bot_response,
            $request->context ?? []
        );

        return response()->json(['success' => true, 'conversation_id' => $conversationId]);
    }

    public function recordFeedback(Request $request, int $conversationId)
    {
        $request->validate([
            'was_helpful' => 'required|boolean',
            'notes' => 'sometimes|string',
        ]);

        $success = $this->chatbotService->recordFeedback(
            $conversationId,
            $request->was_helpful,
            $request->notes ?? ''
        );

        return response()->json(['success' => $success]);
    }

    public function getTrainingStats()
    {
        $stats = $this->chatbotService->getTrainingStats(auth()->user()->tenant_id);

        return response()->json(['success' => true, 'stats' => $stats]);
    }

    public function getLowConfidenceQuestions()
    {
        $questions = $this->chatbotService->getLowConfidenceQuestions(auth()->user()->tenant_id);

        return response()->json(['success' => true, 'questions' => $questions]);
    }

    public function bulkImportTrainingData(Request $request)
    {
        $request->validate([
            'data' => 'required|array',
        ]);

        $result = $this->chatbotService->bulkImport(
            auth()->user()->tenant_id,
            $request->data
        );

        return response()->json($result);
    }

    // ==================== DASHBOARD ====================

    public function dashboard()
    {
        $tenantId = auth()->user()->tenant_id;

        $overview = [
            'voice_commands' => $this->voiceService->getCommandStats($tenantId),
            'image_recognition' => $this->imageService->getRecognitionStats($tenantId),
            'predictive_maintenance' => $this->maintenanceService->getMaintenanceStats($tenantId),
            'sentiment_analysis' => $this->sentimentService->getSentimentStats($tenantId),
            'chatbot_training' => $this->chatbotService->getTrainingStats($tenantId),
        ];

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'overview' => $overview]);
        }

        return view('ai.enhancements-dashboard', compact('overview'));
    }
}
