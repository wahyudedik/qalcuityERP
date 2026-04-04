<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ErpNotification;
use App\Models\Tenant;
use App\Models\User;
use App\Services\GamificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

/**
 * GoogleController — Handle Google OAuth login/register.
 *
 * Flow:
 *   1. User clicks "Login with Google" → redirect to Google
 *   2. Google redirects back with user info
 *   3a. If google_id exists → login existing user
 *   3b. If email exists but no google_id → link Google account
 *   3c. If new user → create Tenant + User (trial plan, admin role)
 *
 * Google login bypasses:
 *   - Email verification (Google already verified)
 *   - 2FA challenge (Google is already a second factor)
 *
 * Google login respects:
 *   - Tenant active check (inactive tenant = blocked)
 *   - User active check (disabled user = blocked)
 *   - Role & permissions (unchanged)
 */
class GoogleController extends Controller
{
    /**
     * Redirect to Google OAuth consent screen.
     */
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle callback from Google.
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            Log::warning('Google OAuth failed: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Login Google gagal. Silakan coba lagi.');
        }

        // 1. Find by google_id (fastest, already linked)
        $user = User::where('google_id', $googleUser->getId())->first();

        // 2. Find by email (link existing account)
        if (!$user) {
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // Link Google account to existing user
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'avatar_url' => $googleUser->getAvatar(),
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ]);
            }
        }

        // 3. New user — create Tenant + User
        if (!$user) {
            $user = $this->createNewUser($googleUser);
        }

        // Check if user/tenant is active
        if (!$user->is_active) {
            return redirect()->route('login')->with('error', 'Akun Anda dinonaktifkan. Hubungi admin.');
        }

        if ($user->tenant && !$user->tenant->canAccess()) {
            return redirect()->route('subscription.expired');
        }

        // Login — skip 2FA (Google is already a trusted second factor)
        Auth::login($user, true);
        session()->regenerate();

        // Record login activity
        ActivityLog::record('login', 'User ' . $user->name . ' login via Google');
        GamificationService::onLogin($user);

        // Redirect: new user → onboarding, existing → dashboard
        if ($user->wasRecentlyCreated || ($user->tenant && !$user->tenant->onboarding_completed)) {
            return redirect()->route('onboarding.show');
        }

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Create new Tenant + User from Google profile.
     */
    private function createNewUser($googleUser): User
    {
        return DB::transaction(function () use ($googleUser) {
            $name = $googleUser->getName() ?: $googleUser->getNickname() ?: 'User';
            $email = $googleUser->getEmail();

            // Generate unique slug
            $slug = Str::slug(Str::before($email, '@')) ?: 'bisnis';
            $originalSlug = $slug;
            $i = 1;
            while (Tenant::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $i++;
            }

            $tenant = Tenant::create([
                'name' => "Bisnis {$name}",
                'slug' => $slug,
                'email' => $email,
                'plan' => 'trial',
                'is_active' => true,
                'trial_ends_at' => now()->addDays(14),
            ]);

            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $name,
                'email' => $email,
                'google_id' => $googleUser->getId(),
                'avatar_url' => $googleUser->getAvatar(),
                'password' => bcrypt(Str::random(32)), // random password (user uses Google)
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(), // Google email is verified
            ]);

            // Welcome notification
            try {
                ErpNotification::create([
                    'tenant_id' => $tenant->id,
                    'user_id' => $user->id,
                    'type' => 'welcome',
                    'title' => '🎉 Selamat datang di Qalcuity ERP!',
                    'body' => "Akun trial 14 hari Anda aktif via Google. Mulai dengan mengatur profil perusahaan.",
                    'data' => ['tenant_id' => $tenant->id, 'source' => 'google'],
                ]);
            } catch (\Throwable) {
            }

            return $user;
        });
    }
}
