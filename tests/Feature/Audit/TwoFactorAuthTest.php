<?php

namespace Tests\Feature\Audit;

use App\Models\Tenant;
use App\Models\User;
use App\Services\Security\TwoFactorAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Feature: erp-comprehensive-audit-fix
 * Task 53.3: Verifikasi TwoFactorController — setup, verifikasi, backup codes
 */
class TwoFactorAuthTest extends TestCase
{
    use RefreshDatabase;

    protected TwoFactorAuthService $twoFactorService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->twoFactorService = app(TwoFactorAuthService::class);
    }

    /**
     * 53.3 Verifikasi setup 2FA — generate secret dan QR code
     */
    public function test_2fa_setup_generates_secret_and_qr(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'two_factor_enabled' => false,
        ]);

        $this->actingAs($user);

        // Access 2FA setup page
        $response = $this->get(route('two-factor.setup'));
        $response->assertStatus(200);
        $response->assertViewIs('auth.two-factor.setup');

        // Verify secret is in session
        $this->assertNotNull(session('2fa_setup_secret'));
    }

    /**
     * 53.3 Verifikasi konfirmasi 2FA dengan kode OTP yang valid
     */
    public function test_2fa_confirm_with_valid_code(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'two_factor_enabled' => false,
        ]);

        $this->actingAs($user);

        // Generate secret
        $secret = $this->twoFactorService->generateSecretKey();
        session(['2fa_setup_secret' => $secret]);

        // Generate valid OTP code
        $google2fa = new \PragmaRX\Google2FA\Google2FA();
        $validCode = $google2fa->getCurrentOtp($secret);

        // Confirm 2FA
        $response = $this->post(route('two-factor.confirm'), [
            'code' => $validCode,
        ]);

        $response->assertRedirect();
        $response->assertViewIs('auth.two-factor.recovery-codes');

        // Verify 2FA is enabled
        $user->refresh();
        $this->assertTrue($user->two_factor_enabled);
    }

    /**
     * 53.3 Verifikasi konfirmasi 2FA dengan kode OTP yang invalid
     */
    public function test_2fa_confirm_with_invalid_code(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'two_factor_enabled' => false,
        ]);

        $this->actingAs($user);

        // Generate secret
        $secret = $this->twoFactorService->generateSecretKey();
        session(['2fa_setup_secret' => $secret]);

        // Try with invalid code
        $response = $this->post(route('two-factor.confirm'), [
            'code' => '000000',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('code');

        // Verify 2FA is NOT enabled
        $user->refresh();
        $this->assertFalse($user->two_factor_enabled);
    }

    /**
     * 53.3 Verifikasi disable 2FA dengan password yang benar
     */
    public function test_2fa_disable_with_correct_password(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'password' => Hash::make('Password123!'),
            'two_factor_enabled' => true,
        ]);

        $this->actingAs($user);

        // Disable 2FA
        $response = $this->post(route('two-factor.disable'), [
            'password' => 'Password123!',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify 2FA is disabled
        $user->refresh();
        $this->assertFalse($user->two_factor_enabled);
    }

    /**
     * 53.3 Verifikasi disable 2FA dengan password yang salah
     */
    public function test_2fa_disable_with_wrong_password(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'password' => Hash::make('Password123!'),
            'two_factor_enabled' => true,
        ]);

        $this->actingAs($user);

        // Try to disable with wrong password
        $response = $this->post(route('two-factor.disable'), [
            'password' => 'WrongPassword',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors();

        // Verify 2FA is still enabled
        $user->refresh();
        $this->assertTrue($user->two_factor_enabled);
    }

    /**
     * 53.3 Verifikasi recovery codes dapat di-regenerate
     */
    public function test_2fa_recovery_codes_regenerate(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'password' => Hash::make('Password123!'),
            'two_factor_enabled' => true,
        ]);

        $this->actingAs($user);

        // Get old recovery codes
        $oldCodes = $user->two_factor_recovery_codes;

        // Regenerate recovery codes
        $response = $this->post(route('two-factor.regenerate-codes'), [
            'password' => 'Password123!',
        ]);

        $response->assertStatus(200);
        $response->assertViewIs('auth.two-factor.recovery-codes');

        // Verify recovery codes are different
        $user->refresh();
        $newCodes = $user->two_factor_recovery_codes;

        $this->assertNotEquals($oldCodes, $newCodes);
    }

    /**
     * 53.3 Verifikasi recovery code dapat digunakan untuk login
     */
    public function test_2fa_recovery_code_login(): void
    {
        $tenant = Tenant::factory()->create(['is_active' => true]);
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => 'user@example.com',
            'password' => Hash::make('Password123!'),
            'email_verified_at' => now(),
            'two_factor_enabled' => true,
        ]);

        // Get recovery code
        $recoveryCodes = $user->two_factor_recovery_codes;
        $recoveryCode = $recoveryCodes[0] ?? null;

        $this->assertNotNull($recoveryCode);

        // Login with email/password
        $this->post(route('login'), [
            'email' => 'user@example.com',
            'password' => 'Password123!',
        ]);

        // Verify 2FA challenge is shown
        $this->assertNotNull(session('2fa_user_id'));

        // Verify with recovery code
        $response = $this->post(route('two-factor.verify'), [
            'code' => $recoveryCode,
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        // Verify recovery code is consumed
        $user->refresh();
        $newCodes = $user->two_factor_recovery_codes;
        $this->assertNotContains($recoveryCode, $newCodes);
    }

    /**
     * 53.3 Verifikasi OTP code dapat digunakan untuk login
     */
    public function test_2fa_otp_code_login(): void
    {
        $tenant = Tenant::factory()->create(['is_active' => true]);
        $secret = $this->twoFactorService->generateSecretKey();

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => 'user@example.com',
            'password' => Hash::make('Password123!'),
            'email_verified_at' => now(),
            'two_factor_enabled' => true,
            'two_factor_secret' => $secret,
        ]);

        // Generate valid OTP
        $google2fa = new \PragmaRX\Google2FA\Google2FA();
        $validCode = $google2fa->getCurrentOtp($secret);

        // Login with email/password
        $this->post(route('login'), [
            'email' => 'user@example.com',
            'password' => 'Password123!',
        ]);

        // Verify with OTP code
        $response = $this->post(route('two-factor.verify'), [
            'code' => $validCode,
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    /**
     * 53.3 Verifikasi invalid 2FA code ditolak
     */
    public function test_2fa_invalid_code_rejected(): void
    {
        $tenant = Tenant::factory()->create(['is_active' => true]);
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => 'user@example.com',
            'password' => Hash::make('Password123!'),
            'email_verified_at' => now(),
            'two_factor_enabled' => true,
        ]);

        // Login with email/password
        $this->post(route('login'), [
            'email' => 'user@example.com',
            'password' => 'Password123!',
        ]);

        // Try with invalid code
        $response = $this->post(route('two-factor.verify'), [
            'code' => '000000',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('code');
        $this->assertGuest();
    }
}
