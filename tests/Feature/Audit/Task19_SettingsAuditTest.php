<?php

namespace Tests\Feature\Audit;

use App\Events\SettingsUpdated;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\NotificationPreference;
use App\Models\OnboardingProfile;
use App\Models\SystemSetting;
use App\Models\Tenant;
use App\Models\TenantApiSetting;
use App\Models\User;
use App\Services\SettingsCacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Task 19: Audit & Perbaikan Pengaturan Sistem
 *
 * Validates all settings functionality:
 * - Company profile settings (logo, name, address, NPWP)
 * - Module activation/deactivation
 * - Accounting settings (currency, date format, costing method)
 * - Notification preferences
 * - API key encryption and usage
 * - SuperAdmin system settings
 * - Cache invalidation on settings changes
 * - Onboarding wizard functionality
 */
class Task19_SettingsAuditTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $admin;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create tenant manually
        $this->tenant = Tenant::create([
            'name' => 'Test Company',
            'slug' => 'test-company',
            'email' => 'test@company.com',
            'phone' => '081234567890',
            'address' => 'Jl. Test No. 123',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'npwp' => '01.234.567.8-901.000',
            'plan' => 'professional',
            'is_active' => true,
            'enabled_modules' => ['accounting', 'inventory', 'sales'],
        ]);

        $this->admin = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->superAdmin = User::create([
            'tenant_id' => null,
            'name' => 'Super Admin',
            'email' => 'superadmin@qalcuity.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
        ]);

        Storage::fake('public');
    }

    /** @test - 19.1 Company profile settings appear in documents */
    public function test_company_profile_settings_appear_in_documents(): void
    {
        $this->actingAs($this->admin);

        // Update company profile
        $response = $this->put(route('company-profile.update'), [
            'name' => 'PT Test Indonesia',
            'email' => 'info@test.co.id',
            'phone' => '021-12345678',
            'address' => 'Jl. Sudirman No. 100',
            'city' => 'Jakarta Selatan',
            'province' => 'DKI Jakarta',
            'npwp' => '01.234.567.8-901.000',
            'website' => 'https://test.co.id',
            'tagline' => 'Your Trusted Partner',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify data saved
        $this->tenant->refresh();
        $this->assertEquals('PT Test Indonesia', $this->tenant->name);
        $this->assertEquals('01.234.567.8-901.000', $this->tenant->npwp);
        $this->assertEquals('Jl. Sudirman No. 100', $this->tenant->address);

        // Verify company info appears in invoice PDF view
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
        ]);

        $invoice = Invoice::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-001',
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => 1000000,
            'tax' => 110000,
            'total' => 1110000,
            'status' => 'unpaid',
        ]);

        $pdfResponse = $this->get(route('invoices.pdf', $invoice));
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertSee('PT Test Indonesia');
        $pdfResponse->assertSee('01.234.567.8-901.000');
        $pdfResponse->assertSee('Jl. Sudirman No. 100');
    }

    /** @test - 19.2 Module activation changes reflect in sidebar and access */
    public function test_module_activation_reflects_in_sidebar_and_access(): void
    {
        Event::fake([SettingsUpdated::class]);

        $this->actingAs($this->admin);

        // Initially has accounting, inventory, sales
        $this->assertTrue($this->tenant->isModuleEnabled('accounting'));
        $this->assertTrue($this->tenant->isModuleEnabled('inventory'));
        $this->assertFalse($this->tenant->isModuleEnabled('hrm'));

        // Update modules - add HRM, remove inventory
        $response = $this->put(route('settings.modules.update'), [
            'modules' => ['accounting', 'sales', 'hrm'],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify SettingsUpdated event was dispatched
        Event::assertDispatched(SettingsUpdated::class, function ($event) {
            return $event->type === 'module' && $event->tenantId === $this->tenant->id;
        });

        // Verify changes persisted
        $this->tenant->refresh();
        $this->assertTrue($this->tenant->isModuleEnabled('accounting'));
        $this->assertTrue($this->tenant->isModuleEnabled('hrm'));
        $this->assertFalse($this->tenant->isModuleEnabled('inventory'));

        // Verify sidebar reflects changes
        $dashboardResponse = $this->get(route('dashboard'));
        $dashboardResponse->assertStatus(200);
        // HRM should now be visible in sidebar
        $dashboardResponse->assertSee('HRM');
    }

    /** @test - 19.3 Accounting settings can be configured */
    public function test_accounting_settings_can_be_configured(): void
    {
        $this->actingAs($this->admin);

        // Create currency
        $response = $this->post(route('accounting.currencies.store'), [
            'code' => 'USD',
            'name' => 'US Dollar',
            'symbol' => '$',
            'rate_to_idr' => 15000,
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify currency created
        $currency = Currency::where('tenant_id', $this->tenant->id)
            ->where('code', 'USD')
            ->first();

        $this->assertNotNull($currency);
        $this->assertEquals('US Dollar', $currency->name);
        $this->assertEquals(15000, $currency->rate_to_idr);
    }

    /** @test - 19.4 Notification preferences can be configured */
    public function test_notification_preferences_can_be_configured(): void
    {
        $this->actingAs($this->admin);

        // Update notification preferences
        $response = $this->post(route('notifications.preferences.update'), [
            'preferences' => [
                'low_stock' => [
                    'in_app' => true,
                    'email' => true,
                    'push' => false,
                ],
                'invoice_overdue' => [
                    'in_app' => true,
                    'email' => false,
                    'push' => false,
                ],
            ],
            'digest_frequency' => 'daily',
            'digest_day' => 'monday',
            'digest_time' => '09:00',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify preferences saved
        $this->admin->refresh();
        $this->assertEquals('daily', $this->admin->digest_frequency);

        $lowStockPref = NotificationPreference::where('user_id', $this->admin->id)
            ->where('notification_type', 'low_stock')
            ->first();

        $this->assertNotNull($lowStockPref);
        $this->assertTrue($lowStockPref->in_app);
        $this->assertTrue($lowStockPref->email);
        $this->assertFalse($lowStockPref->push);
    }

    /** @test - 19.5 API keys are stored encrypted and used correctly */
    public function test_api_keys_stored_encrypted_and_used_correctly(): void
    {
        $this->actingAs($this->admin);

        $apiKey = 'test_api_key_12345';

        // Store API setting with encryption
        TenantApiSetting::set(
            $this->tenant->id,
            'midtrans_server_key',
            $apiKey,
            true, // encrypt
            'payment',
            'Midtrans Server Key'
        );

        // Verify stored value is encrypted (not plain text)
        $record = TenantApiSetting::where('tenant_id', $this->tenant->id)
            ->where('key', 'midtrans_server_key')
            ->first();

        $this->assertNotNull($record);
        $this->assertTrue($record->is_encrypted);
        $this->assertNotEquals($apiKey, $record->value); // Should be encrypted

        // Verify can retrieve decrypted value
        $retrieved = TenantApiSetting::get($this->tenant->id, 'midtrans_server_key');
        $this->assertEquals($apiKey, $retrieved);
    }

    /** @test - 19.6 SuperAdmin system settings function correctly */
    public function test_superadmin_system_settings_function_correctly(): void
    {
        $this->actingAs($this->superAdmin);

        // Update system settings
        $response = $this->put(route('super-admin.settings.update'), [
            'gemini_model' => 'gemini-2.0-flash',
            'gemini_timeout' => 60,
            'mail_host' => 'smtp.gmail.com',
            'mail_port' => 587,
            'mail_encryption' => 'tls',
            'app_name' => 'Qalcuity ERP',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify settings saved
        $this->assertEquals('gemini-2.0-flash', SystemSetting::get('gemini_model'));
        $this->assertEquals(60, SystemSetting::get('gemini_timeout'));
        $this->assertEquals('smtp.gmail.com', SystemSetting::get('mail_host'));
    }

    /** @test - 19.7 Settings changes clear cache automatically */
    public function test_settings_changes_clear_cache_automatically(): void
    {
        Event::fake([SettingsUpdated::class]);

        $cacheService = app(SettingsCacheService::class);

        // Set initial cache
        $cacheKey = "tenant_api_settings_{$this->tenant->id}";
        Cache::put($cacheKey, ['test' => 'data'], 3600);
        $this->assertTrue(Cache::has($cacheKey));

        // Update API setting
        TenantApiSetting::set(
            $this->tenant->id,
            'test_key',
            'test_value',
            false,
            'general'
        );

        // Dispatch event manually (in real app, controller does this)
        event(new SettingsUpdated(
            type: 'api',
            tenantId: $this->tenant->id
        ));

        // Verify cache was cleared
        $this->assertFalse(Cache::has($cacheKey));
    }

    /** @test - 19.8 Onboarding wizard functions for new tenants */
    public function test_onboarding_wizard_functions_for_new_tenants(): void
    {
        // Create new tenant without onboarding completed
        $newTenant = Tenant::create([
            'name' => 'New Company',
            'slug' => 'new-company',
            'email' => 'new@company.com',
            'plan' => 'trial',
            'is_active' => true,
            'onboarding_completed' => false,
            'enabled_modules' => null, // Not yet configured
        ]);

        $newUser = User::create([
            'tenant_id' => $newTenant->id,
            'name' => 'New Admin',
            'email' => 'newadmin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->actingAs($newUser);

        // Access onboarding wizard
        $response = $this->get(route('onboarding.wizard'));
        $response->assertStatus(200);
        $response->assertSee('Selamat datang'); // Welcome message

        // Save industry selection
        $industryResponse = $this->postJson(route('onboarding.save-industry'), [
            'industry' => 'retail',
            'business_size' => 'small',
        ]);

        $industryResponse->assertStatus(200);
        $industryResponse->assertJson(['success' => true]);

        // Verify onboarding profile created
        $profile = OnboardingProfile::where('tenant_id', $newTenant->id)
            ->where('user_id', $newUser->id)
            ->first();

        $this->assertNotNull($profile);
        $this->assertEquals('retail', $profile->industry);
        $this->assertEquals('small', $profile->business_size);

        // Complete onboarding
        $completeResponse = $this->post(route('onboarding.complete'), [
            'modules' => ['accounting', 'inventory', 'sales', 'pos'],
        ]);

        $completeResponse->assertRedirect(route('dashboard'));

        // Verify tenant marked as completed
        $newTenant->refresh();
        $this->assertTrue($newTenant->onboarding_completed);
        $this->assertNotNull($newTenant->enabled_modules);
        $this->assertContains('accounting', $newTenant->enabled_modules);
    }

    /** @test - Company logo upload and display */
    public function test_company_logo_upload_and_display(): void
    {
        $this->actingAs($this->admin);

        $file = UploadedFile::fake()->image('logo.png', 200, 200);

        $response = $this->put(route('company-profile.update'), [
            'name' => $this->tenant->name,
            'logo' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify logo path saved
        $this->tenant->refresh();
        $this->assertNotNull($this->tenant->logo);
        $this->assertStringContainsString('tenants/', $this->tenant->logo);

        // Verify file exists in storage
        Storage::disk('public')->assertExists($this->tenant->logo);
    }

    /** @test - Module settings respect plan limitations */
    public function test_module_settings_respect_plan_limitations(): void
    {
        // Set tenant to starter plan
        $this->tenant->update(['plan' => 'starter']);
        $this->actingAs($this->admin);

        // Try to enable enterprise-only module
        $response = $this->put(route('settings.modules.update'), [
            'modules' => ['accounting', 'manufacturing'], // manufacturing not in starter
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $response->assertSessionHas('upgrade_required', true);

        // Verify module not enabled
        $this->tenant->refresh();
        $this->assertFalse($this->tenant->isModuleEnabled('manufacturing'));
    }

    /** @test - Settings cache service clears correctly */
    public function test_settings_cache_service_clears_correctly(): void
    {
        $cacheService = app(SettingsCacheService::class);

        // Test tenant cache clearing
        $tenantCacheKey = "tenant_modules_{$this->tenant->id}";
        Cache::put($tenantCacheKey, ['test' => 'data'], 3600);

        $cacheService->clearTenantCache($this->tenant->id);
        $this->assertFalse(Cache::has($tenantCacheKey));

        // Test system cache clearing
        $systemCacheKey = 'system_settings';
        Cache::put($systemCacheKey, ['test' => 'data'], 3600);

        $cacheService->clearSystemCache();
        $this->assertFalse(Cache::has($systemCacheKey));

        // Test module cache clearing
        $moduleCacheKey = 'module_settings_accounting';
        Cache::put($moduleCacheKey, ['test' => 'data'], 3600);

        $cacheService->clearModuleCache('accounting');
        $this->assertFalse(Cache::has($moduleCacheKey));
    }
}
