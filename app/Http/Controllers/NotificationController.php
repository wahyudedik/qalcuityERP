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
        $module = $request->get('module');

        if ($user->isSuperAdmin()) {
            $query = ErpNotification::where('user_id', $user->id)->latest();
            if ($module && $module !== 'all') {
                $query->byModule($module);
            }
            $notifications = $query->paginate(30)->withQueryString();

            $moduleCounts = ErpNotification::where('user_id', $user->id)
                ->whereNull('read_at')
                ->selectRaw('module, count(*) as count')
                ->groupBy('module')
                ->pluck('count', 'module');
        } else {
            $tenantId = $user->tenant_id;
            $query = ErpNotification::where('tenant_id', $tenantId)->latest();
            if ($module && $module !== 'all') {
                $query->byModule($module);
            }
            $notifications = $query->paginate(30)->withQueryString();

            $moduleCounts = ErpNotification::where('tenant_id', $tenantId)
                ->whereNull('read_at')
                ->selectRaw('module, count(*) as count')
                ->groupBy('module')
                ->pluck('count', 'module');
        }

        $activeModule = $module ?: 'all';

        return view('notifications.index', compact('notifications', 'moduleCounts', 'activeModule'));
    }

    public function markRead(Request $request, ErpNotification $notification): RedirectResponse
    {
        $user = $request->user();

        // Pastikan user berhak
        abort_if(
            ! $user || (! $user->isSuperAdmin() && $notification->tenant_id !== $user->tenant_id),
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

    /**
     * API: Get notifications for notification bell (JSON).
     */
    public function apiIndex(Request $request): JsonResponse
    {
        $user = $request->user();
        $limit = $request->get('limit', 10);

        $query = $user->isSuperAdmin()
            ? ErpNotification::where('user_id', $user->id)
            : ErpNotification::where('tenant_id', $user->tenant_id);

        $notifications = $query->latest()
            ->limit($limit)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'type' => $n->type,
                'module' => $n->module,
                'title' => $n->title,
                'body' => $n->body,
                'data' => $n->data,
                'read_at' => $n->read_at?->toISOString(),
                'created_at' => $n->created_at->toISOString(),
            ]);

        $unreadCount = $user->isSuperAdmin()
            ? ErpNotification::where('user_id', $user->id)->whereNull('read_at')->count()
            : ErpNotification::where('tenant_id', $user->tenant_id)->whereNull('read_at')->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * API: Get unread notification count (JSON).
     */
    public function apiUnreadCount(Request $request): JsonResponse
    {
        $user = $request->user();

        $count = $user->isSuperAdmin()
            ? ErpNotification::where('user_id', $user->id)->whereNull('read_at')->count()
            : ErpNotification::where('tenant_id', $user->tenant_id)->whereNull('read_at')->count();

        return response()->json([
            'unread_count' => $count,
        ]);
    }
}
