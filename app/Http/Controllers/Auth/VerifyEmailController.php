<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            // Jika admin dan belum onboarding, arahkan ke sana
            $user = $request->user();
            if ($user->isAdmin() && $user->tenant && ! $user->tenant->onboarding_completed) {
                return redirect()->route('onboarding.index');
            }

            return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        $user = $request->user();
        if ($user->isAdmin() && $user->tenant && ! $user->tenant->onboarding_completed) {
            return redirect()->route('onboarding.index');
        }

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }
}
