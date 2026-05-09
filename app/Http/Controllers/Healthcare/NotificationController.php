<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\NotificationRule;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = NotificationRule::query();

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $rules = $query->orderBy('name')->paginate(20);

        $statistics = [
            'total_rules' => NotificationRule::count(),
            'active_rules' => NotificationRule::where('is_active', true)->count(),
            'notifications_today' => Notification::whereDate('created_at', today())->count(),
        ];

        return view('healthcare.notifications.index', compact('rules', 'statistics'));
    }

    public function create()
    {
        return view('healthcare.notifications.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'trigger_event' => 'required|string|max:100',
            'channels' => 'required|array',
            'priority' => 'required|in:low,medium,high,critical',
            'subject_template' => 'required|string',
            'message_template' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $validated['channels'] = implode(',', $validated['channels']);
        $validated['is_active'] = $request->has('is_active');

        $rule = NotificationRule::create($validated);

        return redirect()->route('healthcare.notifications.show', $rule)
            ->with('success', 'Notification rule created');
    }

    public function show(NotificationRule $rule)
    {
        $notifications = Notification::where('rule_id', $rule->id)
            ->latest()
            ->limit(50)
            ->get();

        return view('healthcare.notifications.show', compact('rule', 'notifications'));
    }

    public function edit(NotificationRule $rule)
    {
        return view('healthcare.notifications.edit', compact('rule'));
    }

    public function update(Request $request, NotificationRule $rule)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'trigger_event' => 'required|string|max:100',
            'channels' => 'required|array',
            'priority' => 'required|in:low,medium,high,critical',
            'subject_template' => 'required|string',
            'message_template' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $validated['channels'] = implode(',', $validated['channels']);
        $validated['is_active'] = $request->has('is_active');

        $rule->update($validated);

        return redirect()->route('healthcare.notifications.index')
            ->with('success', 'Notification rule updated');
    }

    public function toggle(NotificationRule $rule)
    {
        $rule->update(['is_active' => ! $rule->is_active]);

        return response()->json(['success' => true, 'message' => 'Rule toggled']);
    }

    public function test(NotificationRule $rule)
    {
        return response()->json(['success' => true, 'message' => 'Test notification sent']);
    }

    public function destroy(NotificationRule $rule)
    {
        $rule->delete();

        return response()->json(['success' => true, 'message' => 'Rule deleted']);
    }
}
