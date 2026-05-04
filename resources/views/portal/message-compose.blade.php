<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-envelope text-blue-600"></i> Compose Message
            </h1>
            <p class="text-gray-500">Send a message to your healthcare provider</p>
        </div>
        <div>
            <a href="{{ route('portal.messages.inbox') }}" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-xl text-sm font-medium transition">
                <i class="fas fa-argrid grid-cols-1 md:grid-cols-2 gap-6-left"></i> Back to Inbox
            </a>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="w-full md:w-2/3 mx-auto">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h5 class="mb-0">New Message</h5>
                </div>
                <div class="p-5">
                    <form action="{{ route('portal.messages.send') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">To (Doctor/Department) <span class="text-red-600">*</span></label>
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
                            <label class="form-label">Subject <span class="text-red-600">*</span></label>
                            <input type="text" name="subject" class="form-control" required
                                placeholder="e.g., Follow-up on prescription, Question about test results">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Message Category <span class="text-red-600">*</span></label>
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
                            <label class="form-label">Message <span class="text-red-600">*</span></label>
                            <textarea name="message" class="form-control" rows="10" required
                                placeholder="Please describe your question or concern in detail..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Attachments (Optional)</label>
                            <input type="file" name="attachments[]" class="form-control" multiple
                                accept="image/*,.pdf,.doc,.docx">
                            <small class="text-gray-500">Accepted formats: Images, PDF, Word documents (Max 10MB per
                                file)</small>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Response Time:</strong> You can expect a response within 24-48 hours during business
                            days.
                            For urgent medical concerns, please call our emergency line or visit the nearest facility.
                        </div>

                        <div class="flex justify-between">
                            <button type="button" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-xl text-sm font-medium transition" onclick="history.back()">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">
                                <i class="fas fa-paper-plane"></i> Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Templates -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
        <div class="w-full md:w-2/3 mx-auto">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h6 class="mb-0">
                        <i class="fas fa-lightbulb text-amber-600"></i> Message Templates
                    </h6>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="w-full md:w-1/2">
                            <button type="button" class="w-full mb-2 px-3 py-2 border border-blue-500 text-blue-600 hover:bg-blue-50 rounded-lg text-sm text-left transition"
                                onclick="fillTemplate('prescription')">
                                <i class="fas fa-pills"></i> Prescription Refill Request
                            </button>
                            <button type="button" class="w-full mb-2 px-3 py-2 border border-blue-500 text-blue-600 hover:bg-blue-50 rounded-lg text-sm text-left transition"
                                onclick="fillTemplate('results')">
                                <i class="fas fa-flask"></i> Test Results Inquiry
                            </button>
                        </div>
                        <div class="w-full md:w-1/2">
                            <button type="button" class="w-full mb-2 px-3 py-2 border border-blue-500 text-blue-600 hover:bg-blue-50 rounded-lg text-sm text-left transition"
                                onclick="fillTemplate('appointment')">
                                <i class="fas fa-calendar"></i> Appointment Follow-up
                            </button>
                            <button type="button" class="w-full mb-2 px-3 py-2 border border-blue-500 text-blue-600 hover:bg-blue-50 rounded-lg text-sm text-left transition"
                                onclick="fillTemplate('symptoms')">
                                <i class="fas fa-stethoscope"></i> Report New Symptoms
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
