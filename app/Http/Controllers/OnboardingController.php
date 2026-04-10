<?php

namespace App\Http\Controllers;

use App\Models\OnboardingProfile;
use App\Models\OnboardingProgress;
use App\Models\AiTourSession;
use App\Models\UserTip;
use App\Services\SampleDataGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnboardingController extends Controller
{
    public function __construct(
        protected SampleDataGeneratorService $sampleDataService
    ) {
    }

    /**
     * Get authenticated user's tenant ID with null safety
     */
    protected function getTenantId(): int
    {
        $user = Auth::user();
        return $user?->tenant_id ?? abort(401, 'Unauthenticated.');
    }

    /**
     * Get authenticated user's ID with null safety
     */
    protected function getUserId(): int
    {
        $user = Auth::user();
        return $user?->id ?? abort(401, 'Unauthenticated.');
    }

    /**
     * Main onboarding dashboard
     */
    public function index()
    {
        $tenantId = $this->getTenantId();
        $userId = $this->getUserId();

        // Check if profile exists
        $profile = OnboardingProfile::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->first();

        if (!$profile) {
            return redirect()->route('onboarding.wizard');
        }

        // Get progress
        $progress = $this->getProgress($tenantId, $userId);

        // Get pending tips
        $tips = UserTip::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('dismissed', false)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('onboarding.dashboard', compact('profile', 'progress', 'tips'));
    }

    /**
     * Industry Selection Wizard
     */
    public function wizard()
    {
        return view('onboarding.wizard');
    }

    /**
     * Save industry selection
     */
    public function saveIndustry(Request $request)
    {
        $request->validate([
            'industry' => 'required|in:retail,restaurant,hotel,construction,agriculture,manufacturing,services',
            'business_size' => 'required|in:micro,small,medium,large',
            'employee_count' => 'nullable|integer|min:1',
            'selected_modules' => 'nullable|array',
        ]);

        $tenantId = $this->getTenantId();
        $userId = $this->getUserId();

        $profile = OnboardingProfile::updateOrCreate(
            ['tenant_id' => $tenantId, 'user_id' => $userId],
            [
                'industry' => $request->industry,
                'business_size' => $request->business_size,
                'employee_count' => $request->employee_count,
                'selected_modules' => $request->selected_modules ?? [],
            ]
        );

        // Initialize progress steps based on industry
        $this->initializeProgressSteps($tenantId, $userId, $request->industry);

        return response()->json([
            'success' => true,
            'profile' => $profile,
            'next_step' => route('onboarding.sample-data'),
        ]);
    }

    /**
     * Sample Data Generation page
     */
    public function sampleDataPage()
    {
        $profile = OnboardingProfile::where('tenant_id', $this->getTenantId())
            ->where('user_id', $this->getUserId())
            ->first();

        if (!$profile) {
            return redirect()->route('onboarding.wizard');
        }

        $templates = $this->sampleDataService->getTemplates($profile->industry);

        return view('onboarding.sample-data', compact('profile', 'templates'));
    }

    /**
     * Generate sample data
     */
    public function generateSampleData(Request $request)
    {
        $tenantId = $this->getTenantId();
        $userId = $this->getUserId();

        $profile = OnboardingProfile::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->first();

        if (!$profile) {
            return response()->json(['error' => 'Profile not found'], 404);
        }

        $result = $this->sampleDataService->generateForIndustry(
            $profile->industry,
            $tenantId,
            $userId
        );

        if ($result['success']) {
            // Mark sample data step as completed
            $this->markStepCompleted($tenantId, $userId, 'generate_sample_data');
        }

        return response()->json($result);
    }

    /**
     * Get onboarding progress
     */
    public function getProgressData()
    {
        $tenantId = $this->getTenantId();
        $userId = $this->getUserId();

        $progress = $this->getProgress($tenantId, $userId);

        return response()->json($progress);
    }

    /**
     * Mark step as completed
     */
    public function completeStep(Request $request, string $stepKey)
    {
        $tenantId = $this->getTenantId();
        $userId = $this->getUserId();

        $this->markStepCompleted($tenantId, $userId, $stepKey);

        return response()->json(['success' => true]);
    }

    /**
     * Start AI Tour
     */
    public function startTour(Request $request)
    {
        $request->validate([
            'tour_type' => 'required|in:general,module_specific,feature_highlight',
        ]);

        $tour = AiTourSession::create([
            'tenant_id' => $this->getTenantId(),
            'user_id' => $this->getUserId(),
            'tour_type' => $request->tour_type,
            'started_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'tour_id' => $tour->id,
            'steps' => $this->getTourSteps($request->tour_type),
        ]);
    }

    /**
     * Complete tour step
     */
    public function completeTourStep(Request $request, int $tourId)
    {
        $request->validate(['step' => 'required|string']);

        $tour = AiTourSession::findOrFail($tourId);
        $tour->completeStep($request->step);

        return response()->json(['success' => true]);
    }

    /**
     * Get available tips
     */
    public function getTips()
    {
        $tips = UserTip::where('tenant_id', $this->getTenantId())
            ->where('user_id', $this->getUserId())
            ->where('dismissed', false)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['tips' => $tips]);
    }

    /**
     * Dismiss tip
     */
    public function dismissTip(int $tipId)
    {
        $tip = UserTip::findOrFail($tipId);
        $tip->dismiss();

        return response()->json(['success' => true]);
    }

    /**
     * Complete onboarding
     */
    public function complete(Request $request)
    {
        $tenantId = $this->getTenantId();
        $userId = $this->getUserId();

        $request->validate([
            'industry' => 'required|in:retail,restaurant,hotel,construction,agriculture,manufacturing,services',
            'business_size' => 'required|in:micro,small,medium,large',
            'employee_count' => 'nullable|integer|min:1',
            'selected_modules' => 'nullable|array',
        ]);

        // Create or update profile
        $profile = OnboardingProfile::updateOrCreate(
            ['tenant_id' => $tenantId, 'user_id' => $userId],
            [
                'industry' => $request->industry,
                'business_size' => $request->business_size,
                'employee_count' => $request->employee_count,
                'selected_modules' => $request->selected_modules ?? [],
                'completed_at' => now(),
            ]
        );

        // Initialize progress steps
        $this->initializeProgressSteps($tenantId, $userId, $request->industry);

        // Mark onboarding as completed
        OnboardingProgress::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->update(['completed' => true]);

        return redirect()->route('dashboard')
            ->with('success', 'Onboarding completed successfully! Welcome to Qalcuity ERP.');
    }

    /**
     * Skip onboarding
     */
    public function skip()
    {
        $tenantId = $this->getTenantId();
        $userId = $this->getUserId();

        // Create minimal profile
        OnboardingProfile::updateOrCreate(
            ['tenant_id' => $tenantId, 'user_id' => $userId],
            [
                'industry' => 'services',
                'business_size' => 'small',
                'skipped' => true,
                'completed_at' => now(),
            ]
        );

        return redirect()->route('dashboard')
            ->with('info', 'Onboarding skipped. You can always configure settings later.');
    }

    /**
     * AI Chat assistant for onboarding
     */
    public function aiChat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'context' => 'nullable|string',
        ]);

        $tenantId = $this->getTenantId();
        $userId = $this->getUserId();

        // Get user's onboarding profile
        $profile = OnboardingProfile::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->first();

        // Here you would integrate with your AI service
        // For now, return a simple response
        $response = $this->generateOnboardingChatResponse(
            $request->message,
            $profile,
            $request->context
        );

        return response()->json([
            'success' => true,
            'response' => $response,
        ]);
    }

    /**
     * Generate AI chat response for onboarding
     */
    protected function generateOnboardingChatResponse(string $message, ?OnboardingProfile $profile, ?string $context): string
    {
        // Simple keyword-based response system
        // In production, this would call your AI service (Gemini, OpenAI, etc.)

        $message = strtolower($message);

        if (str_contains($message, 'product') || str_contains($message, 'inventory')) {
            return 'To add products, go to Inventory > Products > Add Product. You can also import products in bulk using CSV files.';
        }

        if (str_contains($message, 'customer') || str_contains($message, 'client')) {
            return 'You can manage customers in the CRM module. Go to CRM > Customers to add or import your customer list.';
        }

        if (str_contains($message, 'sale') || str_contains($message, 'pos')) {
            return 'To process sales, use the POS module. Go to POS, select products, and complete the checkout process.';
        }

        if (str_contains($message, 'report')) {
            return 'Reports are available in the Reports section. You can generate sales, inventory, finance, and HRM reports.';
        }

        if (str_contains($message, 'help') || str_contains($message, 'support')) {
            return 'I\'m here to help! You can ask me about any feature in Qalcuity ERP. What would you like to know?';
        }

        return 'Thank you for your question! I can help you with products, customers, sales, reports, and other features. What specific area would you like to explore?';
    }

    /**
     * Reset onboarding (for testing)
     */
    public function reset()
    {
        $tenantId = $this->getTenantId();
        $userId = $this->getUserId();

        OnboardingProfile::where('tenant_id', $tenantId)->where('user_id', $userId)->delete();
        OnboardingProgress::where('tenant_id', $tenantId)->where('user_id', $userId)->delete();
        AiTourSession::where('tenant_id', $tenantId)->where('user_id', $userId)->delete();
        UserTip::where('tenant_id', $tenantId)->where('user_id', $userId)->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Helper: Get progress summary
     */
    protected function getProgress(int $tenantId, int $userId): array
    {
        $totalSteps = OnboardingProgress::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->count();

        $completedSteps = OnboardingProgress::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('completed', true)
            ->count();

        $steps = OnboardingProgress::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->orderBy('order')
            ->get();

        return [
            'total_steps' => $totalSteps,
            'completed_steps' => $completedSteps,
            'completion_percentage' => $totalSteps > 0 ? round(($completedSteps / $totalSteps) * 100, 2) : 0,
            'steps' => $steps,
        ];
    }

    /**
     * Helper: Initialize progress steps based on industry
     */
    protected function initializeProgressSteps(int $tenantId, int $userId, string $industry): void
    {
        $commonSteps = [
            ['key' => 'complete_profile', 'name' => 'Complete Your Profile', 'category' => 'setup', 'order' => 1],
            ['key' => 'generate_sample_data', 'name' => 'Generate Sample Data', 'category' => 'setup', 'order' => 2],
            ['key' => 'explore_dashboard', 'name' => 'Explore Dashboard', 'category' => 'first_action', 'order' => 3],
            ['key' => 'create_first_record', 'name' => 'Create Your First Record', 'category' => 'first_action', 'order' => 4],
            ['key' => 'invite_team_member', 'name' => 'Invite Team Member', 'category' => 'collaboration', 'order' => 5],
        ];

        $industrySpecificSteps = match ($industry) {
            'retail' => [
                ['key' => 'add_first_product', 'name' => 'Add Your First Product', 'category' => 'module', 'order' => 6],
                ['key' => 'process_first_sale', 'name' => 'Process First Sale', 'category' => 'module', 'order' => 7],
            ],
            'restaurant' => [
                ['key' => 'create_menu', 'name' => 'Create Menu Items', 'category' => 'module', 'order' => 6],
                ['key' => 'setup_tables', 'name' => 'Setup Tables', 'category' => 'module', 'order' => 7],
                ['key' => 'take_first_order', 'name' => 'Take First Order', 'category' => 'module', 'order' => 8],
            ],
            'hotel' => [
                ['key' => 'setup_rooms', 'name' => 'Setup Rooms', 'category' => 'module', 'order' => 6],
                ['key' => 'create_booking', 'name' => 'Create First Booking', 'category' => 'module', 'order' => 7],
                ['key' => 'check_in_guest', 'name' => 'Check-in First Guest', 'category' => 'module', 'order' => 8],
            ],
            'construction' => [
                ['key' => 'create_project', 'name' => 'Create First Project', 'category' => 'module', 'order' => 6],
                ['key' => 'add_materials', 'name' => 'Add Materials', 'category' => 'module', 'order' => 7],
            ],
            'agriculture' => [
                ['key' => 'add_crop_cycle', 'name' => 'Add Crop Cycle', 'category' => 'module', 'order' => 6],
                ['key' => 'setup_irrigation', 'name' => 'Setup Irrigation Schedule', 'category' => 'module', 'order' => 7],
            ],
            default => []
        };

        $allSteps = array_merge($commonSteps, $industrySpecificSteps);

        foreach ($allSteps as $step) {
            OnboardingProgress::create([
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'step_key' => $step['key'],
                'step_name' => $step['name'],
                'category' => $step['category'],
                'order' => $step['order'],
                'description' => "Complete this step to continue your onboarding journey",
            ]);
        }
    }

    /**
     * Helper: Mark step as completed
     */
    protected function markStepCompleted(int $tenantId, int $userId, string $stepKey): void
    {
        $step = OnboardingProgress::where('tenant_id', $tenantId)
            ->where('user_id', $userId)
            ->where('step_key', $stepKey)
            ->first();

        if ($step) {
            $step->markAsCompleted();
        }
    }

    /**
     * Helper: Get tour steps
     */
    protected function getTourSteps(string $tourType): array
    {
        return match ($tourType) {
            'general' => [
                ['step' => 'welcome', 'title' => 'Welcome to Qalcuity ERP', 'content' => 'Let me show you around...'],
                ['step' => 'dashboard', 'title' => 'Dashboard Overview', 'content' => 'This is your command center...'],
                ['step' => 'modules', 'title' => 'Modules Navigation', 'content' => 'Access all features here...'],
                ['step' => 'reports', 'title' => 'Reports & Analytics', 'content' => 'View insights and reports...'],
                ['step' => 'settings', 'title' => 'Settings', 'content' => 'Customize your experience...'],
            ],
            'module_specific' => [
                ['step' => 'module_intro', 'title' => 'Module Introduction', 'content' => 'Learn about this module...'],
                ['step' => 'key_features', 'title' => 'Key Features', 'content' => 'Here are the main features...'],
                ['step' => 'common_tasks', 'title' => 'Common Tasks', 'content' => 'How to perform common tasks...'],
            ],
            default => []
        };
    }
}
