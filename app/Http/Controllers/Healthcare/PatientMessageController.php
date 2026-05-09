<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientMessageController extends Controller
{
    public function inbox(Request $request)
    {
        $query = PatientMessage::where('recipient_id', Auth::id())
            ->with(['sender', 'replyTo']);

        if ($request->filled('is_read')) {
            $query->where('is_read', $request->boolean('is_read'));
        }

        $messages = $query->orderBy('created_at', 'desc')->paginate(20);

        $statistics = [
            'total' => PatientMessage::where('recipient_id', Auth::id())->count(),
            'unread' => PatientMessage::where('recipient_id', Auth::id())->where('is_read', false)->count(),
        ];

        return view('healthcare.messages.inbox', compact('messages', 'statistics'));
    }

    public function sent(Request $request)
    {
        $messages = PatientMessage::where('sender_id', Auth::id())
            ->with(['recipient'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('healthcare.messages.sent', compact('messages'));
    }

    public function create()
    {
        $patients = Patient::where('is_active', true)->get();

        return view('healthcare.messages.create', compact('patients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'category' => 'required|in:general,prescription,test_results,appointment,billing,symptoms,follow_up',
        ]);

        $validated['sender_id'] = Auth::id();

        $message = PatientMessage::create($validated);

        return redirect()->route('healthcare.messages.show', $message)
            ->with('success', 'Message sent');
    }

    public function show(PatientMessage $message)
    {
        if ($message->recipient_id === Auth::id() && ! $message->is_read) {
            $message->update(['is_read' => true]);
        }

        $message->load(['sender', 'recipient', 'replies.sender']);

        return view('healthcare.messages.show', compact('message'));
    }

    public function reply(Request $request, PatientMessage $message)
    {
        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $reply = PatientMessage::create([
            'sender_id' => Auth::id(),
            'recipient_id' => $message->sender_id === Auth::id() ? $message->recipient_id : $message->sender_id,
            'subject' => 'Re: '.$message->subject,
            'message' => $validated['message'],
            'priority' => $message->priority,
            'category' => $message->category,
            'parent_id' => $message->id,
        ]);

        return response()->json(['success' => true, 'message' => 'Reply sent']);
    }

    public function destroy(PatientMessage $message)
    {
        $message->delete();

        return response()->json(['success' => true, 'message' => 'Message deleted']);
    }

    /**
     * Index.
     * Route: healthcare/patient-messages
     */
    public function index(Request $request)
    {
        // TODO: Add authorization
        // $this->authorize('ACTION', MODEL::class);

        $validated = $request->validate([
            // TODO: Add validation rules
        ]);

        // TODO: Implement Index logic

        return back()->with('success', 'Index completed successfully.');
    }

    /**
     * Show the form for editing.
     * Route: healthcare/patient-messages/{patient_message}/edit
     */
    public function edit($model)
    {
        $this->authorize('update', $model);

        return view('healthcare.patient-message.edit', compact('model'));
    }

    /**
     * Update the specified resource.
     * Route: healthcare/patient-messages/{patient_message}
     */
    public function update(Request $request, $model)
    {
        $this->authorize('update', $model);

        $validated = $request->validate([
            // TODO: Add validation rules
        ]);

        $model->update($validated);

        return redirect()->route('healthcare.patient-messages.update')
            ->with('success', 'Updated successfully.');
    }
}
