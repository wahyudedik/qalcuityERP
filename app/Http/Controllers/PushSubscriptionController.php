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
        $validated = $request->validate([
            'subscription.endpoint' => 'required|url|max:500',
            'subscription.keys.p256dh' => 'required|string',
            'subscription.keys.auth' => 'required|string',
        ]);

        $user = $request->user();
        $subscription = $validated['subscription'];

        // Upsert — don't create duplicate for same endpoint
        PushSubscription::updateOrCreate(
            ['user_id' => $user->id, 'endpoint' => $subscription['endpoint']],
            [
                'tenant_id' => $user->tenant_id,
                'p256dh' => $subscription['keys']['p256dh'],
                'auth' => $subscription['keys']['auth'],
            ]
        );

        return response()->json(['ok' => true, 'message' => 'Push subscription saved']);
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
