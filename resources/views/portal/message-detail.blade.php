<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-envelope text-blue-600"></i> Message Detail
            </h1>
            <p class="text-gray-500">View message conversation</p>
        </div>
        <div>
            <a href="{{ route('portal.messages.inbox') }}" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-xl text-sm font-medium transition">
                <i class="fas fa-argrid grid-cols-1 md:grid-cols-2 gap-6-left"></i> Back to Inbox
            </a>
            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition" data-bs-toggle="modal" data-bs-target="#replyModal">
                <i class="fas fa-reply"></i> Reply
            </button>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="w-full md:w-2/3 mx-auto">
            <!-- Message Header -->
            <div class="bg-white rounded-2xl border border-gray-200 mb-3">
                <div class="p-5">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h4 class="mb-1">{{ $message->subject ?? 'No Subject' }}</h4>
                            <div class="flex items-center">
                                <div class="rounded-full bg-primary text-white flex items-center justify-center mr-2"
                                    style="width: 40px; height: 40px;">
                                    <i class="fas fa-user-md"></i>
                                </div>
                                <div>
                                    <strong>From:</strong> {{ $message->sender_name ?? 'Unknown' }}
                                    <br><small
                                        class="text-gray-500">{{ $message->created_at->format('d/m/Y H:i') ?? '-' }}</small>
                                </div>
                            </div>
                        </div>
                        <div>
                            @if ($message->category == 'prescription')
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Prescription</span>
                            @elseif($message->category == 'test_results')
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-sky-100 text-sky-700">Test Results</span>
                            @elseif($message->category == 'appointment')
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Appointment</span>
                            @elseif($message->category == 'symptoms')
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Symptoms</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">{{ ucfirst($message->category ?? 'General') }}</span>
                            @endif

                            @if ($message->priority == 'urgent')
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Urgent</span>
                            @elseif($message->priority == 'high')
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">High</span>
                            @endif
                        </div>
                    </div>

                    @if ($message->status == 'read')
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                            <i class="fas fa-check-double"></i> Read
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                            <i class="fas fa-envelope"></i> Unread
                        </span>
                    @endif

                    @if ($message->visit_id)
                        <div class="mt-2">
                            <small class="text-gray-500">
                                <i class="fas fa-calendar"></i>
                                Related to visit on {{ $message->visit_date ?? '-' }}
                            </small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Message Thread -->
            <div class="bg-white rounded-2xl border border-gray-200 mb-3">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h6 class="mb-0">
                        <i class="fas fa-comments"></i> Conversation Thread
                    </h6>
                </div>
                <div class="p-5">
                    @forelse($message->thread ?? [$message] as $msg)
                        <div class="mb-4 {{ $msg->is_from_patient ? 'ms-5' : 'me-5' }}">
                            <div class="flex items-center mb-2">
                                @if ($msg->is_from_patient)
                                    <div class="rounded-full bg-success text-white flex items-center justify-center mr-2"
                                        style="width: 35px; height: 35px;">
                                        <i class="fas fa-user fa-sm"></i>
                                    </div>
                                    <strong>You</strong>
                                @else
                                    <div class="rounded-full bg-primary text-white flex items-center justify-center mr-2"
                                        style="width: 35px; height: 35px;">
                                        <i class="fas fa-user-md fa-sm"></i>
                                    </div>
                                    <strong>{{ $msg->sender_name ?? 'Doctor' }}</strong>
                                @endif
                                <small class="text-gray-500 ml-2">{{ $msg->created_at->diffForHumans() ?? '-' }}</small>
                            </div>
                            <div class="p-3 rounded {{ $msg->is_from_patient ? 'bg-gray-50' : 'bg-primary text-white' }}">
                                <p class="mb-0" style="white-space: pre-wrap;">{{ $msg->message ?? 'N/A' }}</p>
                            </div>
                            @if ($msg->attachments)
                                <div class="mt-2">
                                    <strong>Attachments:</strong>
                                    <div class="flex flex-wrap gap-2 mt-1">
                                        @foreach ($msg->attachments as $attachment)
                                            <a href="{{ $attachment['url'] ?? '#' }}"
                                                class="px-3 py-1.5 border border-blue-500 text-blue-600 hover:bg-blue-50 rounded-lg text-xs transition" download>
                                                <i class="fas fa-paperclip"></i> {{ $attachment['name'] ?? 'File' }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-gray-500 text-center">No messages in thread</p>
                    @endforelse
                </div>
            </div>

            <!-- Quick Reply -->
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h6 class="mb-0">
                        <i class="fas fa-reply"></i> Quick Reply
                    </h6>
                </div>
                <div class="p-5">
                    <form action="{{ route('portal.messages.reply', $message->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <textarea name="message" class="form-control" rows="5" required placeholder="Type your reply..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Attachments (Optional)</label>
                            <input type="file" name="attachments[]" class="form-control" multiple
                                accept="image/*,.pdf,.doc,.docx">
                        </div>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">
                            <i class="fas fa-paper-plane"></i> Send Reply
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Reply Modal -->
    <div class="modal fade" id="replyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reply to Message</h5>
                    <button type="button" class="text-gray-400 hover:text-gray-600" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('portal.messages.reply', $message->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Original Message</label>
                            <div class="bg-gray-50 p-3 rounded">
                                <p class="mb-0">{{ Str::limit($message->message ?? '', 200) }}</p>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Your Reply <span class="text-red-600">*</span></label>
                            <textarea name="message" class="form-control" rows="6" required placeholder="Type your reply..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-xl text-sm font-medium transition" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">
                            <i class="fas fa-paper-plane"></i> Send Reply
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
