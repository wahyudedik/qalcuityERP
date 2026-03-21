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
        $user = $request->user();

        // Super admin tidak punya tenant_id, tampilkan notifikasi global
        if ($user->isSuperAdmin()) {
            $notifications = ErpNotification::whereNull('tenant_id')
                ->orWhere('user_id', $user->id)
                ->latest()
                ->paginate(30);
        } else {
            $notifications = ErpNotification::where('tenant_id', $user->tenant_id)
                ->latest()
                ->paginate(30);
        }

        return view('notifications.index', compact('notifications'));
    }

    public function markRead(Request $request, ErpNotification $notification): RedirectResponse
    {
        // Pastikan user berhak
        abort_if(
            !auth()->user()->isSuperAdmin() && $notification->tenant_id !== auth()->user()->tenant_id,
            403
        );
        $notification->markRead();
        return back();
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->isSuperAdmin()) {
            ErpNotification::where('user_id', $user->id)->whereNull('read_at')->update(['read_at' => now()]);
        } else {
            ErpNotification::where('tenant_id', $user->tenant_id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        return back()->with('success', 'Semua notifikasi ditandai dibaca.');
    }
}
