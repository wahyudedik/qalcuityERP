<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\TelemedicineSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelemedicineSettingsController extends Controller
{
    /**
     * Display telemedicine settings page.
     */
    public function index()
    {
        $tenantId = Auth::user()->tenant_id;
        $settings = TelemedicineSetting::getForTenant($tenantId);

        return view('healthcare.telemedicine.settings', compact('settings'));
    }

    /**
     * Update telemedicine settings.
     */
    public function update(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $settings = TelemedicineSetting::getForTenant($tenantId);

        $validated = $request->validate([
            'jitsi_server_url' => 'required|url',
            'jitsi_app_id' => 'nullable|string|max:255',
            'jitsi_secret' => 'nullable|string|max:255',
            'enable_recording' => 'boolean',
            'enable_waiting_room' => 'boolean',
            'enable_chat' => 'boolean',
            'enable_screen_share' => 'boolean',
            'reminder_enabled' => 'boolean',
            'reminder_minutes_before' => 'integer|min:5|max:1440',
            'send_email_reminder' => 'boolean',
            'send_sms_reminder' => 'boolean',
            'enable_feedback' => 'boolean',
            'require_feedback' => 'boolean',
            'consultation_timeout_minutes' => 'integer|min:15|max:240',
            'max_participants' => 'integer|min:2|max:50',
            'allow_group_consultation' => 'boolean',
            'custom_logo_url' => 'nullable|url',
            'welcome_message' => 'nullable|string|max:500',
        ]);

        // Convert checkboxes to boolean
        $validated['enable_recording'] = $request->has('enable_recording');
        $validated['enable_waiting_room'] = $request->has('enable_waiting_room');
        $validated['enable_chat'] = $request->has('enable_chat');
        $validated['enable_screen_share'] = $request->has('enable_screen_share');
        $validated['reminder_enabled'] = $request->has('reminder_enabled');
        $validated['send_email_reminder'] = $request->has('send_email_reminder');
        $validated['send_sms_reminder'] = $request->has('send_sms_reminder');
        $validated['enable_feedback'] = $request->has('enable_feedback');
        $validated['require_feedback'] = $request->has('require_feedback');
        $validated['allow_group_consultation'] = $request->has('allow_group_consultation');

        $settings->update($validated);

        Log::info('Telemedicine settings updated', [
            'tenant_id' => $tenantId,
            'jitsi_server' => $validated['jitsi_server_url'],
        ]);

        return redirect()->route('healthcare.telemedicine.settings.index')
            ->with('success', 'Telemedicine settings updated successfully!');
    }

    /**
     * Test Jitsi server connection.
     */
    public function testConnection(Request $request)
    {
        $validated = $request->validate([
            'jitsi_server_url' => 'required|url',
        ]);

        $url = rtrim($validated['jitsi_server_url'], '/');

        try {
            // Test if Jitsi server is accessible
            $response = Http::timeout(10)->get($url.'/http-bind');

            if ($response->successful() || $response->status() === 405) {
                // 405 is expected for http-bind endpoint
                return response()->json([
                    'success' => true,
                    'message' => 'Jitsi server is accessible!',
                    'server' => $url,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Jitsi server responded with unexpected status: '.$response->status(),
            ], 400);

        } catch (\Exception $e) {
            Log::error('Jitsi connection test failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to connect to Jitsi server: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reset settings to default.
     */
    public function resetToDefault()
    {
        $tenantId = Auth::user()->tenant_id;

        $settings = TelemedicineSetting::where('tenant_id', $tenantId)->first();

        if ($settings) {
            $settings->update([
                'jitsi_server_url' => 'https://meet.jit.si',
                'jitsi_app_id' => null,
                'jitsi_secret' => null,
                'enable_recording' => true,
                'enable_waiting_room' => true,
                'enable_chat' => true,
                'enable_screen_share' => true,
                'reminder_enabled' => true,
                'reminder_minutes_before' => 30,
                'send_email_reminder' => true,
                'send_sms_reminder' => false,
                'enable_feedback' => true,
                'require_feedback' => false,
                'consultation_timeout_minutes' => 60,
                'max_participants' => 10,
                'allow_group_consultation' => false,
                'custom_logo_url' => null,
                'welcome_message' => null,
            ]);
        }

        return redirect()->route('healthcare.telemedicine.settings.index')
            ->with('success', 'Settings reset to default!');
    }

    /**
     * Get default settings (API endpoint).
     */
    public function getDefaultSettings()
    {
        return response()->json([
            'jitsi_server_url' => 'https://meet.jit.si',
            'enable_recording' => true,
            'enable_waiting_room' => true,
            'enable_chat' => true,
            'enable_screen_share' => true,
            'reminder_enabled' => true,
            'reminder_minutes_before' => 30,
            'send_email_reminder' => true,
            'send_sms_reminder' => false,
            'enable_feedback' => true,
            'require_feedback' => false,
            'consultation_timeout_minutes' => 60,
            'max_participants' => 10,
            'allow_group_consultation' => false,
        ]);
    }
}
