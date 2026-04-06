<?php

namespace App\Http\Controllers;

use App\Models\OnboardingProfile;
use App\Models\OnboardingProgress;
use App\Models\AiTourSession;
use App\Models\UserTip;
use App\Services\SampleDataGeneratorService;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function __construct(
        protected SampleDataGeneratorService $sampleDataService
    ) {
    }

    /**
     * Main onboarding dashboard
     */
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;
        $userId = auth()->id();

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

        $tenantId = auth()->user()->tenant_id;
        $userId = auth()->id();

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
        $profile = OnboardingProfile::where('tenant_id', auth()->user()->tenant_id)
            ->where('user_id', auth()->id())
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
        $tenantId = auth()->user()->tenant_id;
        $userId = auth()->id();

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
        $tenantId = auth()->user()->tenant_id;
        $userId = auth()->id();

        $progress = $this->getProgress($tenantId, $userId);

        return response()->json($progress);
    }

    /**
     * Mark step as completed
     */
    public function completeStep(Request $request, string $stepKey)
    {
        $tenantId = auth()->user()->tenant_id;
        $userId = auth()->id();

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
            'tenant_id' => auth()->user()->tenant_id,
            'user_id' => auth()->id(),
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
        $tips = UserTip::where('tenant_id', auth()->user()->tenant_id)
            ->where('user_id', auth()->id())
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
     * Reset onboarding (for testing)
     */
    public function reset()
    {
        $tenantId = auth()->user()->tenant_id;
        $userId = auth()->id();

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
