<x-app-layout>
    <x-slot name="header">{{ __('HL7 Message Details') }} -
                #{{ $message->id }}</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('healthcare.hl7.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"><i
                    class="fas fa-arrow-left mr-2"></i>Back</a>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-envelope mr-2 text-blue-600"></i>Message Information</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Message ID</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900 font-mono">#{{ $message->id }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Direction</dt>
                            <dd class="mt-1">
                                @if ($message->direction === 'inbound')
                                    <span
                                        class="px-2 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800"><i
                                            class="fas fa-arrow-down mr-1"></i>Inbound</span>
                                @else
                                    <span
                                        class="px-2 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800"><i
                                            class="fas fa-arrow-up mr-1"></i>Outbound</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Message Type</dt>
                            <dd class="mt-1"><span
                                    class="px-2 py-1 text-sm font-semibold rounded-full bg-indigo-100 text-indigo-800">{{ $message->message_type }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                @if ($message->status === 'sent' || $message->status === 'received')
                                    <span
                                        class="px-2 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800"><i
                                            class="fas fa-check mr-1"></i>{{ ucfirst($message->status) }}</span>
                                @elseif($message->status === 'pending')
                                    <span
                                        class="px-2 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800"><i
                                            class="fas fa-clock mr-1"></i>Pending</span>
                                @else
                                    <span
                                        class="px-2 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800"><i
                                            class="fas fa-times mr-1"></i>Error</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-server mr-2 text-purple-600"></i>Connection Details</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Receiving Application</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $message->receiving_app ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created At</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $message->created_at->format('d/m/Y H:i:s') }}
                            </dd>
                        </div>
                        @if ($message->sent_at)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Sent At</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $message->sent_at->format('d/m/Y H:i:s') }}
                                </dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Retry Count</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $message->retry_count }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900"><i class="fas fa-code mr-2 text-green-600"></i>HL7
                        Message Content</h3>
                    <button onclick="copyMessage()"
                        class="px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-sm"><i
                            class="fas fa-copy mr-1"></i>Copy</button>
                </div>
                <pre id="messageContent" class="text-xs bg-gray-50 p-4 rounded overflow-x-auto font-mono whitespace-pre-wrap">{{ $message->message_content }}</pre>
            </div>

            @if ($message->error_message)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6 border-2 border-red-200">
                    <h3 class="text-lg font-semibold text-red-900 mb-4"><i
                            class="fas fa-exclamation-triangle mr-2 text-red-600"></i>Error Message</h3>
                    <pre class="text-sm bg-red-50 p-4 rounded overflow-x-auto text-red-800">{{ $message->error_message }}</pre>
                </div>
            @endif

            @if ($message->status === 'error')
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-tasks mr-2 text-indigo-600"></i>Actions</h3>
                    <button onclick="retryMessage()"
                        class="px-6 py-3 bg-yellow-600 text-white rounded-md hover:bg-yellow-700"><i
                            class="fas fa-redo mr-2"></i>Retry Message</button>
                </div>
            @endif
        </div>
    </div>

    <script>
        function copyMessage() {
            const content = document.getElementById('messageContent').textContent;
            navigator.clipboard.writeText(content).then(() => {
                alert('Message content copied to clipboard');
            }).catch(error => alert('Copy failed'));
        }

        function retryMessage() {
            if (confirm('Retry sending this message?')) {
                fetch('{{ route('healthcare.hl7.retry', $message) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        location.reload();
                    })
                    .catch(error => alert('Retry failed'));
            }
        }
    </script>
</x-app-layout>
