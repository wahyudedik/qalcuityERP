<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\HL7Message;
use Illuminate\Http\Request;

class HL7Controller extends Controller
{
    public function index(Request $request)
    {
        $query = HL7Message::query();

        if ($request->filled('direction')) {
            $query->where('direction', $request->direction);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('message_type')) {
            $query->where('message_type', $request->message_type);
        }

        $messages = $query->orderBy('created_at', 'desc')->paginate(50);

        $statistics = [
            'total' => HL7Message::count(),
            'sent' => HL7Message::where('direction', 'outbound')->where('status', 'sent')->count(),
            'received' => HL7Message::where('direction', 'inbound')->where('status', 'received')->count(),
            'pending' => HL7Message::where('status', 'pending')->count(),
            'errors' => HL7Message::where('status', 'error')->count(),
        ];

        return view('healthcare.hl7.index', compact('messages', 'statistics'));
    }

    public function show(HL7Message $message)
    {
        return view('healthcare.hl7.show', compact('message'));
    }

    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'message_type' => 'required|in:ADT,ORM,ORU,SIU,DFT',
            'receiving_app' => 'required|string|max:255',
            'message_content' => 'required|string',
        ]);

        $message = HL7Message::create([
            'direction' => 'outbound',
            'message_type' => $validated['message_type'],
            'receiving_app' => $validated['receiving_app'],
            'message_content' => $validated['message_content'],
            'status' => 'pending',
        ]);

        return response()->json(['success' => true, 'message' => 'HL7 message queued for sending']);
    }

    public function retry(HL7Message $message)
    {
        if ($message->status !== 'error') {
            return response()->json(['success' => false, 'message' => 'Only error messages can be retried'], 400);
        }

        $message->update(['status' => 'pending', 'retry_count' => $message->retry_count + 1]);

        return response()->json(['success' => true, 'message' => 'Message retry queued']);
    }

    public function destroy(HL7Message $message)
    {
        $message->delete();

        return response()->json(['success' => true, 'message' => 'Message deleted']);
    }
}
