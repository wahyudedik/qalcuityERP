<?php

namespace Database\Factories;

use App\Models\AiUsageCostLog;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory untuk AiUsageCostLog model.
 */
class AiUsageCostLogFactory extends Factory
{
    protected $model = AiUsageCostLog::class;

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
        $models = [
            'gemini' => ['gemini-2.5-flash', 'gemini-2.5-flash-lite', 'gemini-1.5-flash'],
            'anthropic' => ['claude-3-5-sonnet-20241022', 'claude-3-haiku-20240307'],
        ];

        $provider = $this->faker->randomElement($providers);
        $model = $this->faker->randomElement($models[$provider]);

        $inputTokens = $this->faker->numberBetween(100, 5000);
        $outputTokens = $this->faker->numberBetween(100, 3000);

        $costPer1k = $provider === 'gemini' ? 0.15 : 2.50;
        $estimatedCost = (($inputTokens + $outputTokens) / 1000) * $costPer1k;

        return [
            'tenant_id' => Tenant::factory(),
            'user_id' => $this->faker->boolean(70) ? User::factory() : null,
            'use_case' => $this->faker->randomElement($useCases),
            'provider' => $provider,
            'model' => $model,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'estimated_cost_idr' => round($estimatedCost, 4),
            'response_time_ms' => $this->faker->numberBetween(500, 15000),
            'fallback_degraded' => $this->faker->boolean(10),
            'created_at' => now(),
        ];
    }
}
