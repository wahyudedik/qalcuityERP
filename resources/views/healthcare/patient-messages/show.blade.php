<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Message Details') }}</h2>
            <a href="{{ route('healthcare.patient-messages.inbox') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"><i
                    class="fas fa-arrow-left mr-2"></i>Back to Inbox</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $message->subject }}</h3>
                        <div class="flex items-center space-x-4 text-sm text-gray-600">
                            <span><i class="fas fa-user mr-1"></i>From:
                                <strong>{{ $message->sender->name ?? 'Unknown' }}</strong></span>
                            <span><i class="fas fa-user mr-1"></i>To:
                                <strong>{{ $message->recipient->name ?? 'Unknown' }}</strong></span>
                            <span><i
                                    class="fas fa-calendar mr-1"></i>{{ $message->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span
                            class="px-3 py-1 text-sm font-semibold rounded-full {{ $message->priority === 'urgent' ? 'bg-red-100 text-red-800' : ($message->priority === 'high' ? 'bg-orange-100 text-orange-800' : ($message->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800')) }}">{{ ucfirst($message->priority) }}</span>
                        <span
                            class="px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">{{ ucfirst(str_replace('_', ' ', $message->category)) }}</span>
                        @if ($message->is_read)
                            <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800"><i
                                    class="fas fa-check mr-1"></i>Read</span>
                        @else
                            <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800"><i
                                    class="fas fa-envelope mr-1"></i>Unread</span>
                        @endif
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-6">
                    <div class="prose max-w-none">
                        <p class="text-gray-700 whitespace-pre-line">{{ $message->message }}</p>
                    </div>
                </div>
            </div>

            @if ($message->replies && $message->replies->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-comments mr-2 text-blue-600"></i>Replies ({{ $message->replies->count() }})
                    </h3>
                    <div class="space-y-4">
                        @foreach ($message->replies as $reply)
                            <div
                                class="border-l-4 border-blue-500 pl-4 py-3 {{ $reply->sender_id === auth()->id() ? 'bg-blue-50' : 'bg-gray-50' }}">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center space-x-2">
                                        <span
                                            class="font-semibold text-gray-900">{{ $reply->sender->name ?? 'Unknown' }}</span>
                                        @if ($reply->sender_id === auth()->id())
                                            <span class="text-xs text-gray-500">(You)</span>
                                        @endif
                                    </div>
                                    <span
                                        class="text-sm text-gray-500">{{ $reply->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <p class="text-gray-700 whitespace-pre-line">{{ $reply->message }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                        class="fas fa-reply mr-2 text-green-600"></i>Reply to Message</h3>
                <form id="replyForm">
                    @csrf
                    <div class="mb-4">
                        <textarea name="message" id="replyMessage" required rows="5"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Type your reply..."></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"><i
                                class="fas fa-reply mr-2"></i>Send Reply</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.getElementById('replyForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const replyMessage = document.getElementById('replyMessage').value;

                fetch('{{ route('healthcare.patient-messages.reply', $message->id) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            message: replyMessage
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Reply sent successfully!');
                            window.location.reload();
                        } else {
                            alert('Failed to send reply. Please try again.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to send reply. Please try again.');
                    });
            });
        </script>
    @endpush
</x-app-layout>
