<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-exchange-alt text-blue-600"></i> HL7 Messages
            </h1>
            <p class="text-gray-500">Health Level 7 message monitoring and tracking</p>
        </div>
        <div>
            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition" data-bs-toggle="modal" data-bs-target="#sendMessageModal">
                <i class="fas fa-paper-plane"></i> Send Message
            </button>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-emerald-300">
                <div class="p-5 text-center">
                    <h3 class="text-emerald-600">{{ $stats['sent'] ?? 0 }}</h3>
                    <small class="text-gray-500">Messages Sent</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-blue-300">
                <div class="p-5 text-center">
                    <h3 class="text-sky-600">{{ $stats['received'] ?? 0 }}</h3>
                    <small class="text-gray-500">Messages Received</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-amber-300">
                <div class="p-5 text-center">
                    <h3 class="text-amber-600">{{ $stats['pending'] ?? 0 }}</h3>
                    <small class="text-gray-500">Pending</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-red-300">
                <div class="p-5 text-center">
                    <h3 class="text-red-600">{{ $stats['errors'] ?? 0 }}</h3>
                    <small class="text-gray-500">Errors</small>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h5 class="mb-0">Message Log</h5>
                </div>
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>Direction</th>
                                    <th>Message Type</th>
                                    <th>Event</th>
                                    <th>Sender</th>
                                    <th>Receiver</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($messages as $message)
                                    <tr>
                                        <td>
                                            <small>{{ $message->created_at->format('d/m/Y H:i:s') }}</small>
                                            <br><small
                                                class="text-gray-500">{{ $message->created_at->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            @if ($message->direction == 'outbound')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                                    <i class="fas fa-argrid grid-cols-1 md:grid-cols-2 gap-6-up"></i> Out
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-sky-100 text-sky-700">
                                                    <i class="fas fa-argrid grid-cols-1 md:grid-cols-2 gap-6-down"></i> In
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $message->message_type ?? '-' }}</strong>
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-gray-50 text-dark">{{ $message->trigger_event ?? '-' }}</span>
                                        </td>
                                        <td>
                                            <small>{{ $message->sending_app ?? '-' }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $message->receiving_app ?? '-' }}</small>
                                        </td>
                                        <td>
                                            @if ($message->status == 'sent' || $message->status == 'received')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                                    <i class="fas fa-check-circle"></i> {{ ucfirst($message->status) }}
                                                </span>
                                            @elseif($message->status == 'pending')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                                    <i class="fas fa-clock"></i> Pending
                                                </span>
                                            @elseif($message->status == 'error')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                                    <i class="fas fa-times-circle"></i> Error
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">{{ ucfirst($message->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="px-3 py-1.5 bg-sky-500 hover:bg-sky-600 text-white rounded-lg text-xs transition" data-bs-toggle="modal"
                                                data-bs-target="#viewMessageModal{{ $message->id }}">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            @if ($message->status == 'error')
                                                <button class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs transition" title="Retry">
                                                    <i class="fas fa-redo"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>

                                    <!-- View Message Modal -->
                                    <div class="modal fade" id="viewMessageModal{{ $message->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-xl">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">HL7 Message Details</h5>
                                                    <button type="button" class="text-gray-400 hover:text-gray-600"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                                        <div class="w-full md:w-1/2">
                                                            <strong>Message Type:</strong>
                                                            <p>{{ $message->message_type ?? '-' }}</p>
                                                        </div>
                                                        <div class="w-full md:w-1/2">
                                                            <strong>Trigger Event:</strong>
                                                            <p>{{ $message->trigger_event ?? '-' }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                                        <div class="w-full md:w-1/2">
                                                            <strong>Sending Application:</strong>
                                                            <p>{{ $message->sending_app ?? '-' }}</p>
                                                        </div>
                                                        <div class="w-full md:w-1/2">
                                                            <strong>Receiving Application:</strong>
                                                            <p>{{ $message->receiving_app ?? '-' }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <strong>Message Content:</strong>
                                                        <pre class="bg-gray-50 p-3 rounded" style="max-height: 400px; overflow-y: auto; font-size: 0.85rem;"><code>{{ $message->message_content ?? 'N/A' }}</code></pre>
                                                    </div>
                                                    @if ($message->acknowledgment)
                                                        <div class="mb-3">
                                                            <strong>Acknowledgment (ACK):</strong>
                                                            <pre class="bg-gray-50 p-3 rounded" style="max-height: 200px; overflow-y: auto; font-size: 0.85rem;"><code>{{ $message->acknowledgment }}</code></pre>
                                                        </div>
                                                    @endif
                                                    @if ($message->error_message)
                                                        <div class="alert alert-danger">
                                                            <strong>Error:</strong> {{ $message->error_message }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-xl text-sm font-medium transition"
                                                        data-bs-dismiss="modal">Close</button>
                                                    <button class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium transition" onclick="window.print()">
                                                        <i class="fas fa-print"></i> Print
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-10">
                                            <i class="fas fa-exchange-alt fa-3x text-gray-500 mb-3"></i>
                                            <p class="text-gray-500">No HL7 messages found</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if (isset($messages) && $messages->hasPages())
                        <div class="mt-3">
                            {{ $messages->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Send Message Modal -->
    <div class="modal fade" id="sendMessageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Send HL7 Message</h5>
                    <button type="button" class="text-gray-400 hover:text-gray-600" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('integration.hl7-messages.send') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Message Type <span class="text-red-600">*</span></label>
                            <select name="message_type" class="form-select" required>
                                <option value="">Select Type</option>
                                <option value="ADT">ADT - Admit, Discharge, Transfer</option>
                                <option value="ORM">ORM - Order Message</option>
                                <option value="ORU">ORU - Observation Result</option>
                                <option value="SIU">SIU - Scheduling Information</option>
                                <option value="DFT">DFT - Detailed Financial Transaction</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Receiving Application <span class="text-red-600">*</span></label>
                            <input type="text" name="receiving_app" class="form-control" required
                                placeholder="e.g., LAB_SYSTEM, RIS_SYSTEM">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message Content <span class="text-red-600">*</span></label>
                            <textarea name="message_content" class="form-control" rows="10" required
                                placeholder="Enter HL7 message segments (MSH, PID, PV1, etc.)..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-xl text-sm font-medium transition" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
