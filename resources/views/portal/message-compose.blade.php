@extends('layouts.app')

@section('title', 'Compose Message')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-envelope text-primary"></i> Compose Message
            </h1>
            <p class="text-muted mb-0">Send a message to your healthcare provider</p>
        </div>
        <div>
            <a href="{{ route('portal.messages.inbox') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Inbox
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">New Message</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('portal.messages.send') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">To (Doctor/Department) <span class="text-danger">*</span></label>
                            <select name="recipient_id" class="form-select" required>
                                <option value="">Select Recipient</option>
                                @foreach ($doctors ?? [] as $doctor)
                                    <option value="{{ $doctor->id }}">
                                        Dr. {{ $doctor->name }} - {{ $doctor->specialization ?? 'General' }}
                                    </option>
                                @endforeach
                                <option disabled>──────────</option>
                                @foreach ($departments ?? [] as $dept)
                                    <option value="dept_{{ $dept->id }}">
                                        {{ $dept->name }} Department
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" name="subject" class="form-control" required
                                placeholder="e.g., Follow-up on prescription, Question about test results">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Message Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required>
                                <option value="">Select Category</option>
                                <option value="general">General Inquiry</option>
                                <option value="prescription">Prescription Question</option>
                                <option value="test_results">Test Results</option>
                                <option value="appointment">Appointment Related</option>
                                <option value="billing">Billing Question</option>
                                <option value="symptoms">New Symptoms</option>
                                <option value="follow_up">Follow-up</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="low">Low - Non-urgent question</option>
                                <option value="medium" selected>Medium - Regular inquiry</option>
                                <option value="high">High - Needs prompt attention</option>
                                <option value="urgent">Urgent - Medical concern</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Related Visit (Optional)</label>
                            <select name="visit_id" class="form-select">
                                <option value="">None</option>
                                @foreach ($recent_visits ?? [] as $visit)
                                    <option value="{{ $visit->id }}">
                                        {{ $visit->visit_date->format('d/m/Y') }} - {{ $visit->doctor_name ?? '-' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea name="message" class="form-control" rows="10" required
                                placeholder="Please describe your question or concern in detail..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Attachments (Optional)</label>
                            <input type="file" name="attachments[]" class="form-control" multiple
                                accept="image/*,.pdf,.doc,.docx">
                            <small class="text-muted">Accepted formats: Images, PDF, Word documents (Max 10MB per
                                file)</small>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Response Time:</strong> You can expect a response within 24-48 hours during business
                            days.
                            For urgent medical concerns, please call our emergency line or visit the nearest facility.
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" onclick="history.back()">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Templates -->
    <div class="row mt-4">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-lightbulb text-warning"></i> Message Templates
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <button type="button" class="btn btn-outline-primary btn-sm w-100 mb-2 text-start"
                                onclick="fillTemplate('prescription')">
                                <i class="fas fa-pills"></i> Prescription Refill Request
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm w-100 mb-2 text-start"
                                onclick="fillTemplate('results')">
                                <i class="fas fa-flask"></i> Test Results Inquiry
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-outline-primary btn-sm w-100 mb-2 text-start"
                                onclick="fillTemplate('appointment')">
                                <i class="fas fa-calendar"></i> Appointment Follow-up
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm w-100 mb-2 text-start"
                                onclick="fillTemplate('symptoms')">
                                <i class="fas fa-stethoscope"></i> Report New Symptoms
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function fillTemplate(type) {
            const templates = {
                prescription: {
                    subject: 'Prescription Refill Request',
                    category: 'prescription',
                    message: 'Dear Doctor,\n\nI would like to request a refill for my current prescription:\n\nMedication: [Medication Name]\nDosage: [Dosage]\nLast prescribed: [Date]\n\nThank you.'
                },
                results: {
                    subject: 'Test Results Inquiry',
                    category: 'test_results',
                    message: 'Dear Doctor,\n\nI am writing to inquire about my recent test results from [Date].\n\nTest Type: [Test Name]\n\nCould you please provide an update on the results and any recommendations?\n\nThank you.'
                },
                appointment: {
                    subject: 'Follow-up on Recent Appointment',
                    category: 'follow_up',
                    message: 'Dear Doctor,\n\nI had an appointment with you on [Date] and have a few follow-up questions:\n\n[Your questions here]\n\nThank you for your time.'
                },
                symptoms: {
                    subject: 'New Symptoms Report',
                    category: 'symptoms',
                    message: 'Dear Doctor,\n\nI am experiencing new symptoms since my last visit:\n\nSymptoms: [Describe symptoms]\nStarted: [Date]\nSeverity: [Mild/Moderate/Severe]\n\nPlease advise on the next steps.\n\nThank you.'
                }
            };

            const template = templates[type];
            if (template) {
                document.querySelector('[name="subject"]').value = template.subject;
                document.querySelector('[name="category"]').value = template.category;
                document.querySelector('[name="message"]').value = template.message;
            }
        }
    </script>
@endpush
