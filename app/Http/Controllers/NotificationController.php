<?php

namespace App\Http\Controllers;

use App\Models\ErpNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = ErpNotification::where('tenant_id', $request->user()->tenant_id)
            ->latest()
            ->paginate(30);

        return view('notifications.index', compact('notifications'));
    }

    public function markRead(Request $request, ErpNotification $notification): RedirectResponse
    {
        $notification->markRead();
        return back();
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        ErpNotification::where('tenant_id', $request->user()->tenant_id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'Semua notifikasi ditandai dibaca.');
    }
}
