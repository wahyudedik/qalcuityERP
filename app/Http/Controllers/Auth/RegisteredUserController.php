<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ErpNotification;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'company_name'  => ['required', 'string', 'max:255'],
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'phone'         => ['nullable', 'string', 'max:20'],
            'business_type' => ['nullable', 'string', 'max:50'],
            'password'      => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Buat tenant + user dalam satu transaction agar atomic
        [$tenant, $user] = \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            // Buat slug unik dari nama perusahaan (dengan lock untuk hindari race condition)
            $slug = Str::slug($request->company_name) ?: 'bisnis';
            $originalSlug = $slug;
            $i = 1;
            while (Tenant::where('slug', $slug)->lockForUpdate()->exists()) {
                $slug = $originalSlug . '-' . $i++;
            }

            // Buat tenant baru
            $tenant = Tenant::create([
                'name'          => $request->company_name,
                'slug'          => $slug,
                'email'         => $request->email,
                'phone'         => $request->phone,
                'business_type' => $request->business_type,
                'plan'          => 'trial',
                'is_active'     => true,
                'trial_ends_at' => now()->addDays(14),
            ]);

            // Buat user admin untuk tenant ini
            $user = User::create([
                'tenant_id' => $tenant->id,
                'name'      => $request->name,
                'email'     => $request->email,
                'password'  => Hash::make($request->password),
                'role'      => 'admin',
                'is_active' => true,
            ]);

            return [$tenant, $user];
        });

        event(new Registered($user));

        // Track affiliate referral
        if ($ref = $request->input('ref') ?? $request->cookie('affiliate_ref')) {
            try {
                app(\App\Services\AffiliateService::class)->trackReferral($tenant, $ref);
            } catch (\Throwable) {}
        }

        // Kirim welcome notification (queued)
        try {
            $user->load('tenant');
            $user->notify(new WelcomeNotification($user));
        } catch (\Throwable) {
            // Gagal kirim email tidak boleh gagalkan register
        }

        // In-app notification: selamat datang
        try {
            ErpNotification::create([
                'tenant_id' => $tenant->id,
                'user_id'   => $user->id,
                'type'      => 'welcome',
                'title'     => '🎉 Selamat datang di Qalcuity ERP!',
                'body'      => "Akun trial 14 hari Anda aktif. Mulai dengan mengatur profil perusahaan dan tambahkan produk pertama Anda.",
                'data'      => ['tenant_id' => $tenant->id],
            ]);
        } catch (\Throwable) {
            // Non-critical
        }

        Auth::login($user);

        // Arahkan ke verifikasi email dulu, setelah verified baru ke onboarding
        return redirect()->route('verification.notice');
    }
}
