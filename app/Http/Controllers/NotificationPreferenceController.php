<?php

namespace App\Http\Controllers;

use App\Models\NotificationPreference;
use Illuminate\Http\Request;

class NotificationPreferenceController extends Controller
{
    public function index(Request $request)
    {
        $user           = $request->user();
        $availableTypes = NotificationPreference::availableTypes();

        $userPrefs = NotificationPreference::where('user_id', $user->id)->get()
            ->keyBy('notification_type');

        return view('notifications.preferences', compact('availableTypes', 'userPrefs', 'user'));
    }

    public function update(Request $request)
    {
        $user  = $request->user();
        $prefs = $request->input('preferences', []);

        foreach (NotificationPreference::availableTypes() as $module => $types) {
            foreach ($types as $type => $label) {
                NotificationPreference::updateOrCreate(
                    ['user_id' => $user->id, 'notification_type' => $type],
                    [
                        'in_app' => isset($prefs[$type]['in_app']),
                        'email'  => isset($prefs[$type]['email']),
                        'push'   => isset($prefs[$type]['push']),
                    ]
                );
            }
        }

        // Update digest preferences on user
        $user->update([
            'digest_frequency' => $request->input('digest_frequency', 'weekly'),
            'digest_day'       => $request->input('digest_day', 'friday'),
            'digest_time'      => $request->input('digest_time', '17:00'),
        ]);

        return back()->with('success', 'Preferensi notifikasi berhasil disimpan.');
    }
}
