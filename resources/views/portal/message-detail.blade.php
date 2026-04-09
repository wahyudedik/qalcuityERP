@extends('layouts.app')

@section('title', 'Message Detail')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-envelope text-primary"></i> Message Detail
            </h1>
            <p class="text-muted mb-0">View message conversation</p>
        </div>
        <div>
            <a href="{{ route('portal.messages.inbox') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Inbox
            </a>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#replyModal">
                <i class="fas fa-reply"></i> Reply
            </button>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <!-- Message Header -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h4 class="mb-1">{{ $message->subject ?? 'No Subject' }}</h4>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2"
                                    style="width: 40px; height: 40px;">
                                    <i class="fas fa-user-md"></i>
                                </div>
                                <div>
                                    <strong>From:</strong> {{ $message->sender_name ?? 'Unknown' }}
                                    <br><small
                                        class="text-muted">{{ $message->created_at->format('d/m/Y H:i') ?? '-' }}</small>
                                </div>
                            </div>
                        </div>
                        <div>
                            @if ($message->category == 'prescription')
                                <span class="badge bg-primary">Prescription</span>
                            @elseif($message->category == 'test_results')
                                <span class="badge bg-info">Test Results</span>
                            @elseif($message->category == 'appointment')
                                <span class="badge bg-success">Appointment</span>
                            @elseif($message->category == 'symptoms')
                                <span class="badge bg-warning">Symptoms</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($message->category ?? 'General') }}</span>
                            @endif

                            @if ($message->priority == 'urgent')
                                <span class="badge bg-danger">Urgent</span>
                            @elseif($message->priority == 'high')
                                <span class="badge bg-warning">High</span>
                            @endif
                        </div>
                    </div>

                    @if ($message->status == 'read')
                        <span class="badge bg-success">
                            <i class="fas fa-check-double"></i> Read
                        </span>
                    @else
                        <span class="badge bg-primary">
                            <i class="fas fa-envelope"></i> Unread
                        </span>
                    @endif

                    @if ($message->visit_id)
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="fas fa-calendar"></i>
                                Related to visit on {{ $message->visit_date ?? '-' }}
                            </small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Message Thread -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-comments"></i> Conversation Thread
                    </h6>
                </div>
                <div class="card-body">
                    @forelse($message->thread ?? [$message] as $msg)
                        <div class="mb-4 {{ $msg->is_from_patient ? 'ms-5' : 'me-5' }}">
                            <div class="d-flex align-items-center mb-2">
                                @if ($msg->is_from_patient)
                                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-2"
                                        style="width: 35px; height: 35px;">
                                        <i class="fas fa-user fa-sm"></i>
                                    </div>
                                    <strong>You</strong>
                                @else
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2"
                                        style="width: 35px; height: 35px;">
                                        <i class="fas fa-user-md fa-sm"></i>
                                    </div>
                                    <strong>{{ $msg->sender_name ?? 'Doctor' }}</strong>
                                @endif
                                <small class="text-muted ms-2">{{ $msg->created_at->diffForHumans() ?? '-' }}</small>
                            </div>
                            <div class="p-3 rounded {{ $msg->is_from_patient ? 'bg-light' : 'bg-primary text-white' }}">
                                <p class="mb-0" style="white-space: pre-wrap;">{{ $msg->message ?? 'N/A' }}</p>
                            </div>
                            @if ($msg->attachments)
                                <div class="mt-2">
                                    <strong>Attachments:</strong>
                                    <div class="d-flex flex-wrap gap-2 mt-1">
                                        @foreach ($msg->attachments as $attachment)
                                            <a href="{{ $attachment['url'] ?? '#' }}"
                                                class="btn btn-sm btn-outline-primary" download>
                                                <i class="fas fa-paperclip"></i> {{ $attachment['name'] ?? 'File' }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted text-center">No messages in thread</p>
                    @endforelse
                </div>
            </div>

            <!-- Quick Reply -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-reply"></i> Quick Reply
                    </h6>
                </div>
                <div class="card-body">
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
                        <button type="submit" class="btn btn-primary">
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('portal.messages.reply', $message->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Original Message</label>
                            <div class="bg-light p-3 rounded">
                                <p class="mb-0">{{ Str::limit($message->message ?? '', 200) }}</p>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Your Reply <span class="text-danger">*</span></label>
                            <textarea name="message" class="form-control" rows="6" required placeholder="Type your reply..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Send Reply
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
