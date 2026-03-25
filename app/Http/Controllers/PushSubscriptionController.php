<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    /**
     * Save browser push subscription.
     * Called from JS after user grants notification permission.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => 'required|url|max:500',
            'keys.p256dh' => 'required|string',
            'keys.auth'   => 'required|string',
        ]);

        $user = $request->user();

        // Upsert — don't create duplicate for same endpoint
        PushSubscription::updateOrCreate(
            ['user_id' => $user->id, 'endpoint' => $data['endpoint']],
            [
                'tenant_id' => $user->tenant_id,
                'p256dh'    => $data['keys']['p256dh'],
                'auth'      => $data['keys']['auth'],
            ]
        );

        return response()->json(['ok' => true]);
    }

    /**
     * Remove push subscription (user opts out).
     */
    public function destroy(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => 'required|url',
        ]);

        PushSubscription::where('user_id', $request->user()->id)
            ->where('endpoint', $data['endpoint'])
            ->delete();

        return response()->json(['ok' => true]);
    }
}
