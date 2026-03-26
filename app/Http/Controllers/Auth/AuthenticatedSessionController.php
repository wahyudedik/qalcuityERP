<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // Cek apakah user memerlukan 2FA
        $user = Auth::user();
        if ($user->two_factor_enabled) {
            // Simpan user ID di session, logout sementara
            $remember = $request->boolean('remember');
            Auth::logout();
            $request->session()->put('2fa_user_id', $user->id);
            $request->session()->put('2fa_remember', $remember);
            return redirect()->route('two-factor.challenge');
        }

        $request->session()->regenerate();

        // Admin wajib 2FA — paksa setup sebelum bisa akses apapun
        if ($user->isAdmin() && !$user->two_factor_enabled) {
            return redirect()->route('two-factor.setup')
                ->with('warning', 'Sebagai Admin, Anda wajib mengaktifkan Two-Factor Authentication sebelum melanjutkan.');
        }

        // Affiliate users go to affiliate dashboard
        if ($user->isAffiliate()) {
            return redirect()->route('affiliate.dashboard');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
