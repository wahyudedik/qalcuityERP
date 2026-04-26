<?php

namespace Tests\Feature\Audit;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

/**
 * Feature: erp-comprehensive-audit-fix
 * Task 53.4: Verifikasi GoogleController — OAuth flow berfungsi tanpa error
 */
class GoogleOAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 53.4 Verifikasi Google OAuth redirect
     */
    public function test_google_oauth_redirect(): void
    {
        $response = $this->get(route('auth.google'));

        // Should redirect to Google OAuth consent screen
        $response->assertStatus(302);
        $this->assertStringContainsString('accounts.google.com', $response->headers->get('Location'));
    }

    /**
     * 53.4 Verifikasi Google OAuth callback dengan new user
     */
    public function test_google_oauth_callback_new_user(): void
    {
        // Mock Google user
        $googleUser = Mockery::mock('Laravel\Socialite\Two\User');
        $googleUser->shouldReceive('getId')->andReturn('google-123');
        $googleUser->shouldReceive('getName')->andReturn('John Doe');
        $googleUser->shouldReceive('getEmail')->andReturn('john@gmail.com');
        $googleUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');

        // Mock Socialite
        Socialite::shouldReceive('driver->user')->andReturn($googleUser);

        // Call callback
        $response = $this->get(route('auth.google.callback'));

        // Should redirect to onboarding
        $response->assertRedirect(route('onboarding.index'));

        // Verify tenant was created
        $tenant = Tenant::where('email', 'john@gmail.com')->first();
        $this->assertNotNull($tenant);
        $this->assertEquals('trial', $tenant->plan);
        $this->assertTrue($tenant->is_active);

        // Verify user was created
        $user = User::where('email', 'john@gmail.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('google-123', $user->google_id);
        $this->assertEquals('admin', $user->role);
        $this->assertNotNull($user->email_verified_at);

        // Verify user is authenticated
        $this->assertAuthenticatedAs($user);
    }

    /**
     * 53.4 Verifikasi Google OAuth callback dengan existing user (link account)
     */
    public function test_google_oauth_callback_existing_user_link(): void
    {
        // Create existing user
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => 'john@gmail.com',
            'google_id' => null,
        ]);

        // Mock Google user
        $googleUser = Mockery::mock('Laravel\Socialite\Two\User');
        $googleUser->shouldReceive('getId')->andReturn('google-123');
        $googleUser->shouldReceive('getName')->andReturn('John Doe');
        $googleUser->shouldReceive('getEmail')->andReturn('john@gmail.com');
        $googleUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');

        // Mock Socialite
        Socialite::shouldReceive('driver->user')->andReturn($googleUser);

        // Call callback
        $response = $this->get(route('auth.google.callback'));

        // Should redirect to dashboard
        $response->assertRedirect(route('dashboard'));

        // Verify Google ID was linked
        $user->refresh();
        $this->assertEquals('google-123', $user->google_id);

        // Verify user is authenticated
        $this->assertAuthenticatedAs($user);
    }

    /**
     * 53.4 Verifikasi Google OAuth callback dengan existing google_id
     */
    public function test_google_oauth_callback_existing_google_id(): void
    {
        // Create existing user with Google ID
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => 'john@gmail.com',
            'google_id' => 'google-123',
        ]);

        // Mock Google user
        $googleUser = Mockery::mock('Laravel\Socialite\Two\User');
        $googleUser->shouldReceive('getId')->andReturn('google-123');
        $googleUser->shouldReceive('getName')->andReturn('John Doe');
        $googleUser->shouldReceive('getEmail')->andReturn('john@gmail.com');
        $googleUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');

        // Mock Socialite
        Socialite::shouldReceive('driver->user')->andReturn($googleUser);

        // Call callback
        $response = $this->get(route('auth.google.callback'));

        // Should redirect to dashboard
        $response->assertRedirect(route('dashboard'));

        // Verify user is authenticated
        $this->assertAuthenticatedAs($user);
    }

    /**
     * 53.4 Verifikasi Google OAuth dengan inactive user
     */
    public function test_google_oauth_callback_inactive_user(): void
    {
        // Create inactive user
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => 'john@gmail.com',
            'google_id' => 'google-123',
            'is_active' => false,
        ]);

        // Mock Google user
        $googleUser = Mockery::mock('Laravel\Socialite\Two\User');
        $googleUser->shouldReceive('getId')->andReturn('google-123');
        $googleUser->shouldReceive('getName')->andReturn('John Doe');
        $googleUser->shouldReceive('getEmail')->andReturn('john@gmail.com');
        $googleUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');

        // Mock Socialite
        Socialite::shouldReceive('driver->user')->andReturn($googleUser);

        // Call callback
        $response = $this->get(route('auth.google.callback'));

        // Should redirect to login with error
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');

        // Verify user is NOT authenticated
        $this->assertGuest();
    }

    /**
     * 53.4 Verifikasi Google OAuth dengan inactive tenant
     */
    public function test_google_oauth_callback_inactive_tenant(): void
    {
        // Create inactive tenant
        $tenant = Tenant::factory()->create(['is_active' => false]);
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => 'john@gmail.com',
            'google_id' => 'google-123',
            'is_active' => true,
        ]);

        // Mock Google user
        $googleUser = Mockery::mock('Laravel\Socialite\Two\User');
        $googleUser->shouldReceive('getId')->andReturn('google-123');
        $googleUser->shouldReceive('getName')->andReturn('John Doe');
        $googleUser->shouldReceive('getEmail')->andReturn('john@gmail.com');
        $googleUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');

        // Mock Socialite
        Socialite::shouldReceive('driver->user')->andReturn($googleUser);

        // Call callback
        $response = $this->get(route('auth.google.callback'));

        // Should redirect to subscription expired
        $response->assertRedirect(route('subscription.expired'));

        // Verify user is NOT authenticated
        $this->assertGuest();
    }

    /**
     * 53.4 Verifikasi Google OAuth error handling
     */
    public function test_google_oauth_callback_error(): void
    {
        // Mock Socialite to throw exception
        Socialite::shouldReceive('driver->user')->andThrow(new \Exception('OAuth error'));

        // Call callback
        $response = $this->get(route('auth.google.callback'));

        // Should redirect to login with error
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');

        // Verify user is NOT authenticated
        $this->assertGuest();
    }
}
