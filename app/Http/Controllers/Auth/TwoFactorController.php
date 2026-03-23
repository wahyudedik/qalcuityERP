<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorService;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactorController extends Controller
{
    public function __construct(protected TwoFactorService $twoFactor) {}

    // ─── Setup (dari halaman profil) ──────────────────────────────

    /**
     * Tampilkan halaman setup 2FA.
     */
    public function setup(Request $request)
    {
        $user   = $request->user();
        $secret = $request->session()->get('2fa_setup_secret') ?? $this->twoFactor->generateSecret();
        $request->session()->put('2fa_setup_secret', $secret);

        $qrUrl  = $this->twoFactor->getQrCodeUrl($user, $secret);
        $qrSvg  = $this->generateQrSvg($qrUrl);

        return view('auth.two-factor.setup', compact('secret', 'qrSvg', 'user'));
    }

    /**
     * Konfirmasi kode OTP dan aktifkan 2FA.
     */
    public function confirm(Request $request)
    {
        $request->validate(['code' => 'required|string|digits:6']);

        $secret = $request->session()->get('2fa_setup_secret');
        if (!$secret) {
            return back()->with('error', 'Sesi setup 2FA tidak valid. Mulai ulang.');
        }

        if (!$this->twoFactor->verify($secret, $request->code)) {
            return back()->withErrors(['code' => 'Kode OTP tidak valid. Pastikan waktu perangkat Anda sinkron.']);
        }

        $this->twoFactor->enable($request->user(), $secret);
        $request->session()->forget('2fa_setup_secret');

        $recoveryCodes = $request->user()->two_factor_recovery_codes;

        return view('auth.two-factor.recovery-codes', compact('recoveryCodes'))
            ->with('success', '2FA berhasil diaktifkan.');
    }

    /**
     * Nonaktifkan 2FA.
     */
    public function disable(Request $request)
    {
        $request->validate(['password' => 'required|current_password']);
        $this->twoFactor->disable($request->user());
        return back()->with('success', '2FA berhasil dinonaktifkan.');
    }

    /**
     * Regenerate recovery codes.
     */
    public function regenerateCodes(Request $request)
    {
        $request->validate(['password' => 'required|current_password']);
        $codes = $this->twoFactor->regenerateRecoveryCodes($request->user());
        return view('auth.two-factor.recovery-codes', ['recoveryCodes' => $codes]);
    }

    // ─── Challenge (saat login) ───────────────────────────────────

    /**
     * Tampilkan form verifikasi OTP setelah login berhasil.
     */
    public function challenge()
    {
        if (!session('2fa_user_id')) {
            return redirect()->route('login');
        }
        return view('auth.two-factor.challenge');
    }

    /**
     * Verifikasi OTP dari challenge.
     */
    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $userId = $request->session()->get('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user   = \App\Models\User::findOrFail($userId);
        $code   = trim($request->code);
        $valid  = false;

        // Coba OTP dulu, lalu recovery code
        $secret = $this->twoFactor->getSecret($user);
        if ($secret && strlen($code) === 6 && ctype_digit($code)) {
            $valid = $this->twoFactor->verify($secret, $code);
        }

        if (!$valid && strlen($code) === 10) {
            $valid = $this->twoFactor->verifyRecoveryCode($user, $code);
        }

        if (!$valid) {
            return back()->withErrors(['code' => 'Kode tidak valid. Coba lagi atau gunakan recovery code.']);
        }

        // Login user
        Auth::login($user, $request->session()->get('2fa_remember', false));
        $request->session()->forget(['2fa_user_id', '2fa_remember']);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    // ─── Helper ───────────────────────────────────────────────────

    private function generateQrSvg(string $url): string
    {
        try {
            $renderer = new ImageRenderer(
                new RendererStyle(200),
                new SvgImageBackEnd()
            );
            $writer = new Writer($renderer);
            return $writer->writeString($url);
        } catch (\Throwable) {
            // Fallback: return URL saja jika library tidak tersedia
            return '';
        }
    }
}
