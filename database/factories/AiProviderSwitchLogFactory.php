<?php

namespace Database\Factories;

use App\Models\AiProviderSwitchLog;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory untuk AiProviderSwitchLog model.
 */
class AiProviderSwitchLogFactory extends Factory
{
    protected $model = AiProviderSwitchLog::class;

    public function definition(): array
    {
        $useCases = [
            'chatbot',
            'crud_ai',
            'auto_reply',
            'invoice_parsing',
            'document_parsing',
            'notification_ai',
            'product_description',
            'email_draft',
            'financial_report',
            'forecasting',
            'decision_support',
            'audit_analysis',
            'business_recommendation',
            'bank_reconciliation_ai',
            'budget_analysis',
            'anomaly_detection',
        ];

        $providers = ['gemini', 'anthropic'];
        $reasons = ['rate_limit', 'quota_exceeded', 'server_error', 'use_case_fallback'];

        return [
            'tenant_id' => Tenant::factory(),
            'from_provider' => $this->faker->randomElement($providers),
            'to_provider' => $this->faker->randomElement($providers),
            'reason' => $this->faker->randomElement($reasons),
            'use_case' => $this->faker->randomElement($useCases),
            'error_message' => $this->faker->boolean(50) ? $this->faker->sentence() : null,
            'created_at' => now(),
        ];
    }
}
