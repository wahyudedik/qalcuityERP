<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $query = Reminder::where('tenant_id', $tenantId)
            ->where('user_id', auth()->id())
            ->latest('remind_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'pending');
        }

        $reminders = $query->paginate(20)->withQueryString();

        $overdueCount = Reminder::where('tenant_id', $tenantId)
            ->where('user_id', auth()->id())
            ->pending()->due()->count();

        return view('reminders.index', compact('reminders', 'overdueCount'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'     => 'required|string|max:255',
            'notes'     => 'nullable|string|max:1000',
            'remind_at' => 'required|date|after:now',
            'channel'   => 'nullable|in:app,email',
        ]);

        Reminder::create([
            'tenant_id' => auth()->user()->tenant_id,
            'user_id'   => auth()->id(),
            'title'     => $data['title'],
            'notes'     => $data['notes'] ?? null,
            'remind_at' => $data['remind_at'],
            'channel'   => $data['channel'] ?? 'app',
            'status'    => 'pending',
        ]);

        return back()->with('success', 'Pengingat berhasil dibuat.');
    }

    public function markDone(Reminder $reminder)
    {
        abort_if($reminder->tenant_id !== auth()->user()->tenant_id, 403);
        abort_if($reminder->user_id !== auth()->id(), 403);

        $reminder->update(['status' => 'done']);

        return back()->with('success', 'Pengingat ditandai selesai.');
    }

    public function destroy(Reminder $reminder)
    {
        abort_if($reminder->tenant_id !== auth()->user()->tenant_id, 403);
        abort_if($reminder->user_id !== auth()->id(), 403);

        $reminder->delete();

        return back()->with('success', 'Pengingat dihapus.');
    }
}
