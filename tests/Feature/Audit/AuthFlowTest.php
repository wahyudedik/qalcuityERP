<?php

namespace Tests\Feature\Audit;

use App\Models\Tenant;
use App\Models\User;
use App\Services\AccountLockoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Feature: erp-comprehensive-audit-fix
 * Task 53: Audit & Perbaikan Auth & Security
 *
 * Tests for authentication flows:
 * - 53.1 Tenant registration flow
 * - 53.2 Login flow (email/password, Google OAuth, 2FA)
 * - 53.5 Password reset flow
 * - 53.6 Account lockout after failed attempts
 */
class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 53.1 Verifikasi alur registrasi tenant baru — register → verifikasi email → onboarding
     */
    public function test_tenant_registration_flow(): void
    {
        // Step 1: Register new tenant
        $response = $this->post(route('register'), [
            'company_name' => 'PT. Test Company',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+62812345678',
            'business_type' => 'toko_retail',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        // Should redirect to email verification
        $response->assertRedirect(route('verification.notice'));

        // Verify tenant was created
        $tenant = Tenant::where('email', 'john@example.com')->first();
        $this->assertNotNull($tenant);
        $this->assertEquals('PT. Test Company', $tenant->name);
        $this->assertEquals('trial', $tenant->plan);
        $this->assertTrue($tenant->is_active);
        $this->assertNotNull($tenant->trial_ends_at);

        // Verify user was created as admin
        $user = User::where('email', 'john@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('admin', $user->role);
        $this->assertEquals($tenant->id, $user->tenant_id);
        $this->assertTrue($user->is_active);
        $this->assertNull($user->email_verified_at);

        // Step 2: Verify email
        $this->actingAs($user);
        $verifyResponse = $this->post(route('verification.send'));
        $verifyResponse->assertSessionHasNoErrors();

        // Simulate email verification (in real scenario, user clicks link in email)
        $user->update(['email_verified_at' => now()]);

        // Step 3: Access onboarding
        $onboardingResponse = $this->get(route('onboarding.index'));
        $onboardingResponse->assertStatus(200);
    }

    /**
     * 53.2 Verifikasi alur login — email/password
     */
    public function test_login_flow_with_email_password(): void
    {
        // Create tenant and user
        $tenant = Tenant::factory()->create(['is_active' => true]);
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => 'user@example.com',
            'password' => Hash::make('Password123!'),
            'email_verified_at' => now(),
            'two_factor_enabled' => false,
        ]);

        // Step 1: Access login page
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');

        // Step 2: Submit login form
        $loginResponse = $this->post(route('login'), [
            'email' => 'user@example.com',
            'password' => 'Password123!',
        ]);

        // Should redirect to dashboard
        $loginResponse->assertRedirect(route('dashboard'));

        // Verify user is authenticated
        $this->assertAuthenticatedAs($user);
    }

    /**
     * 53.2 Verifikasi alur login dengan 2FA
     */
    public function test_login_flow_with_2fa(): void
    {
        // Create tenant and user with 2FA enabled
        $tenant = Tenant::factory()->create(['is_active' => true]);
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => 'user@example.com',
            'password' => Hash::make('Password123!'),
            'email_verified_at' => now(),
            'two_factor_enabled' => true,
            'two_factor_secret' => 'JBSWY3DPEBLW64TMMQ======',
        ]);

        // Step 1: Submit login form
        $loginResponse = $this->post(route('login'), [
            'email' => 'user@example.com',
            'password' => 'Password123!',
        ]);

        // Should redirect to 2FA challenge
        $loginResponse->assertRedirect(route('two-factor.challenge'));

        // Verify user is NOT authenticated yet
        $this->assertGuest();

        // Verify 2FA user ID is in session
        $this->assertNotNull(session('2fa_user_id'));
    }

    /**
     * 53.5 Verifikasi alur reset password — request → email → reset → konfirmasi
     */
    public function test_password_reset_flow(): void
    {
        // Create user
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('OldPassword123!'),
        ]);

        // Step 1: Request password reset
        $response = $this->post(route('password.email'), [
            'email' => 'user@example.com',
        ]);

        $response->assertSessionHasNoErrors();

        // Step 2: Get reset token from database
        $resetToken = \DB::table('password_reset_tokens')
            ->where('email', 'user@example.com')
            ->first();

        $this->assertNotNull($resetToken);

        // Step 3: Access reset password form
        $resetFormResponse = $this->get(route('password.reset', ['token' => $resetToken->token]));
        $resetFormResponse->assertStatus(200);

        // Step 4: Submit new password
        $resetResponse = $this->post(route('password.store'), [
            'token' => $resetToken->token,
            'email' => 'user@example.com',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $resetResponse->assertRedirect(route('login'));

        // Step 5: Verify new password works
        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $user->password));

        // Verify old password doesn't work
        $this->assertFalse(Hash::check('OldPassword123!', $user->password));
    }

    /**
     * 53.6 Verifikasi AccountLockoutService — lockout setelah gagal login berulang
     */
    public function test_account_lockout_after_failed_attempts(): void
    {
        $tenant = Tenant::factory()->create(['is_active' => true]);
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => 'user@example.com',
            'password' => Hash::make('CorrectPassword123!'),
            'email_verified_at' => now(),
        ]);

        $lockoutService = app(AccountLockoutService::class);

        // Simulate 5 failed login attempts
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post(route('login'), [
                'email' => 'user@example.com',
                'password' => 'WrongPassword',
            ]);

            $response->assertRedirect(route('login'));
        }

        // Verify account is locked
        $user->refresh();
        $this->assertTrue($lockoutService->isLocked($user));
        $this->assertEquals(5, $user->failed_login_attempts);
        $this->assertNotNull($user->locked_until);

        // Attempt to login with correct password should fail
        $response = $this->post(route('login'), [
            'email' => 'user@example.com',
            'password' => 'CorrectPassword123!',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    /**
     * 53.6 Verifikasi account unlock setelah lockout period
     */
    public function test_account_unlock_after_lockout_period(): void
    {
        $tenant = Tenant::factory()->create(['is_active' => true]);
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => 'user@example.com',
            'password' => Hash::make('CorrectPassword123!'),
            'email_verified_at' => now(),
            'locked_until' => now()->subMinutes(1), // Already expired
        ]);

        $lockoutService = app(AccountLockoutService::class);

        // Verify account is not locked (lockout period expired)
        $this->assertFalse($lockoutService->isLocked($user));

        // Should be able to login with correct password
        $response = $this->post(route('login'), [
            'email' => 'user@example.com',
            'password' => 'CorrectPassword123!',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    /**
     * 53.2 Verifikasi admin wajib 2FA
     */
    public function test_admin_must_enable_2fa(): void
    {
        $tenant = Tenant::factory()->create(['is_active' => true]);
        $admin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => 'admin@example.com',
            'password' => Hash::make('Password123!'),
            'email_verified_at' => now(),
            'role' => 'admin',
            'two_factor_enabled' => false,
        ]);

        // Login as admin
        $response = $this->post(route('login'), [
            'email' => 'admin@example.com',
            'password' => 'Password123!',
        ]);

        // Should redirect to 2FA setup
        $response->assertRedirect(route('two-factor.setup'));
        $response->assertSessionHas('warning');
    }

    /**
     * 53.2 Verifikasi login dengan inactive user
     */
    public function test_login_with_inactive_user(): void
    {
        $tenant = Tenant::factory()->create(['is_active' => true]);
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => 'user@example.com',
            'password' => Hash::make('Password123!'),
            'email_verified_at' => now(),
            'is_active' => false,
        ]);

        $response = $this->post(route('login'), [
            'email' => 'user@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    /**
     * 53.2 Verifikasi login dengan inactive tenant
     */
    public function test_login_with_inactive_tenant(): void
    {
        $tenant = Tenant::factory()->create(['is_active' => false]);
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => 'user@example.com',
            'password' => Hash::make('Password123!'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $response = $this->post(route('login'), [
            'email' => 'user@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
