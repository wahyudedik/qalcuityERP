<?php

namespace Tests\Feature;

use App\Enums\AiUseCase;
use App\Models\AiUseCaseRoute;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Feature test untuk TenantAiRoutingController.
 *
 * Memverifikasi:
 * - Tenant dapat melihat routing rules yang berlaku
 * - Tenant dapat membuat override routing rule
 * - Tenant dapat menghapus override routing rule
 * - Validasi plan dan provider
 *
 * Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.7, 5.8
 */
class TenantAiRoutingControllerTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create tenant and user
        $this->tenant = Tenant::factory()->create([
            'subscription_plan' => 'professional', // Professional plan untuk akses Anthropic
        ]);

        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'admin',
        ]);

        // Create global routing rules
        AiUseCaseRoute::create([
            'tenant_id' => null, // Global rule
            'use_case' => AiUseCase::CHATBOT->value,
            'provider' => 'gemini',
            'model' => 'gemini-2.5-flash',
            'min_plan' => null,
            'is_active' => true,
        ]);

        AiUseCaseRoute::create([
            'tenant_id' => null, // Global rule
            'use_case' => AiUseCase::FINANCIAL_REPORT->value,
            'provider' => 'anthropic',
            'model' => 'claude-3-5-sonnet-20241022',
            'min_plan' => 'professional',
            'is_active' => true,
        ]);
    }

    /**
     * Test: Tenant dapat melihat halaman AI Routing.
     *
     * Requirements: 5.1, 5.4
     */
    public function test_tenant_can_view_ai_routing_page(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('settings.ai-routing.index'));

        $response->assertStatus(200);
        $response->assertViewIs('settings.ai-routing');
        $response->assertViewHas('routingRules');
        $response->assertViewHas('costByUseCase');
        $response->assertViewHas('availableProviders');
        $response->assertViewHas('tenantPlan', 'professional');
    }

    /**
     * Test: Tenant dapat membuat override routing rule.
     *
     * Requirements: 5.2, 5.3
     */
    public function test_tenant_can_create_override_routing_rule(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('settings.ai-routing.store'), [
            'use_case' => AiUseCase::CHATBOT->value,
            'provider' => 'anthropic',
            'model' => 'claude-3-haiku-20240307',
            'description' => 'Override untuk chatbot menggunakan Claude Haiku',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verifikasi override dibuat
        $this->assertDatabaseHas('ai_use_case_routes', [
            'tenant_id' => $this->tenant->id,
            'use_case' => AiUseCase::CHATBOT->value,
            'provider' => 'anthropic',
            'model' => 'claude-3-haiku-20240307',
            'is_active' => true,
        ]);

        // Verifikasi cache di-invalidate
        $this->assertNull(Cache::get("ai_routing_rules:{$this->tenant->id}"));
    }

    /**
     * Test: Tenant dapat menghapus override routing rule.
     *
     * Requirements: 5.5
     */
    public function test_tenant_can_delete_override_routing_rule(): void
    {
        $this->actingAs($this->user);

        // Buat override terlebih dahulu
        $override = AiUseCaseRoute::create([
            'tenant_id' => $this->tenant->id,
            'use_case' => AiUseCase::CHATBOT->value,
            'provider' => 'anthropic',
            'model' => 'claude-3-haiku-20240307',
            'is_active' => true,
        ]);

        $response = $this->delete(route('settings.ai-routing.destroy', $override->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verifikasi override dihapus
        $this->assertDatabaseMissing('ai_use_case_routes', [
            'id' => $override->id,
        ]);
    }

    /**
     * Test: Tenant dengan plan rendah tidak dapat memilih provider Anthropic.
     *
     * Requirements: 5.6, 5.7
     */
    public function test_tenant_with_low_plan_cannot_use_anthropic(): void
    {
        // Update tenant plan ke starter
        $this->tenant->update(['subscription_plan' => 'starter']);
        $this->actingAs($this->user);

        $response = $this->post(route('settings.ai-routing.store'), [
            'use_case' => AiUseCase::CHATBOT->value,
            'provider' => 'anthropic', // Tidak tersedia untuk plan starter
            'model' => 'claude-3-haiku-20240307',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('provider');
    }

    /**
     * Test: Tenant tidak dapat meng-override use case yang memerlukan plan lebih tinggi.
     *
     * Requirements: 5.7
     */
    public function test_tenant_cannot_override_use_case_requiring_higher_plan(): void
    {
        // Update tenant plan ke starter
        $this->tenant->update(['subscription_plan' => 'starter']);
        $this->actingAs($this->user);

        $response = $this->post(route('settings.ai-routing.store'), [
            'use_case' => AiUseCase::FINANCIAL_REPORT->value, // Memerlukan professional plan
            'provider' => 'gemini',
            'model' => 'gemini-2.5-flash',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('use_case');
    }

    /**
     * Test: Tenant tidak dapat menghapus override milik tenant lain.
     *
     * Requirements: 5.5
     */
    public function test_tenant_cannot_delete_other_tenant_override(): void
    {
        $this->actingAs($this->user);

        // Buat override untuk tenant lain
        $otherTenant = Tenant::factory()->create();
        $otherOverride = AiUseCaseRoute::create([
            'tenant_id' => $otherTenant->id,
            'use_case' => AiUseCase::CHATBOT->value,
            'provider' => 'gemini',
            'model' => 'gemini-2.5-flash',
            'is_active' => true,
        ]);

        $response = $this->delete(route('settings.ai-routing.destroy', $otherOverride->id));

        $response->assertStatus(403);
    }

    /**
     * Test: Professional tenant dapat mengakses semua provider.
     *
     * Requirements: 5.6
     */
    public function test_professional_tenant_can_access_all_providers(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('settings.ai-routing.index'));

        $response->assertStatus(200);
        $availableProviders = $response->viewData('availableProviders');

        $this->assertContains('gemini', $availableProviders);
        $this->assertContains('anthropic', $availableProviders);
    }

    /**
     * Test: Starter tenant hanya dapat mengakses Gemini.
     *
     * Requirements: 5.6
     */
    public function test_starter_tenant_can_only_access_gemini(): void
    {
        $this->tenant->update(['subscription_plan' => 'starter']);
        $this->actingAs($this->user);

        $response = $this->get(route('settings.ai-routing.index'));

        $response->assertStatus(200);
        $availableProviders = $response->viewData('availableProviders');

        $this->assertContains('gemini', $availableProviders);
        $this->assertNotContains('anthropic', $availableProviders);
    }
}
