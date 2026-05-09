<?php

namespace App\Http\Controllers;

use App\Models\PopupAd;
use App\Models\PopupAdView;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PopupAdDismissController extends Controller
{
    /**
     * Record that the authenticated user has dismissed/viewed a popup ad.
     */
    public function store(Request $request, PopupAd $ad): JsonResponse
    {
        PopupAdView::updateOrCreate(
            [
                'popup_ad_id' => $ad->id,
                'user_id' => $request->user()->id,
            ],
            ['viewed_at' => now()]
        );

        return response()->json(['ok' => true]);
    }
}
