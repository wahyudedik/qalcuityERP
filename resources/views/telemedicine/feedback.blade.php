<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-star text-blue-600"></i> Consultation Feedback
            </h1>
            <p class="text-gray-500">Rate and review your teleconsultation experience</p>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="w-full md:w-2/3">
            @forelse($consultations as $consultation)
                <div class="bg-white rounded-2xl border border-gray-200 mb-4">
                    <div class="px-5 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <strong>Consultation #{{ $consultation->consultation_number }}</strong>
                                <br><small
                                    class="text-gray-500">{{ $consultation->consultation_date?->format('d/m/Y H:i') ?? '-' }}</small>
                            </div>
                            @if ($consultation->feedback)
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                    <i class="fas fa-check"></i> Feedback Submitted
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                    <i class="fas fa-clock"></i> Pending Feedback
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="p-5">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                            <div class="w-full md:w-1/2">
                                <small class="text-gray-500">Doctor</small>
                                <br><strong>{{ $consultation->doctor?->name ?? '-' }}</strong>
                            </div>
                            <div class="w-full md:w-1/2">
                                <small class="text-gray-500">Consultation Type</small>
                                <br><strong>{{ ucfirst($consultation->consultation_type ?? '-') }}</strong>
                            </div>
                        </div>

                        @if ($consultation->feedback)
                            <div class="bg-gray-50 p-3 rounded">
                                <div class="mb-2">
                                    <strong>Your Rating:</strong>
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i
                                            class="fas fa-star {{ $i <= ($consultation->feedback['rating'] ?? 0) ? 'text-amber-600' : 'text-gray-500' }}"></i>
                                    @endfor
                                </div>
                                <p class="mb-1"><strong>Comment:</strong>
                                    {{ $consultation->feedback['comment'] ?? 'N/A' }}</p>
                                <small class="text-gray-500">Submitted on
                                    {{ $consultation->feedback['submitted_at'] ?? '-' }}</small>
                            </div>
                        @else
                            <form action="{{ route('healthcare.telemedicine.feedback.store', $consultation) }}"
                                method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label font-bold">Overall Rating</label>
                                    <div class="rating-stars" data-rating-input="rating{{ $consultation->id }}">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star fa-2x text-gray-500 rating-star"
                                                data-rating="{{ $i }}" style="cursor: pointer;"></i>
                                        @endfor
                                    </div>
                                    <input type="hidden" name="rating" id="rating{{ $consultation->id }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">What did you like?</label>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="w-full md:w-1/2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="liked[]"
                                                    value="doctor_knowledge">
                                                <label class="form-check-label">Doctor's Knowledge</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="liked[]"
                                                    value="communication">
                                                <label class="form-check-label">Communication</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="liked[]"
                                                    value="wait_time">
                                                <label class="form-check-label">Wait Time</label>
                                            </div>
                                        </div>
                                        <div class="w-full md:w-1/2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="liked[]"
                                                    value="platform_ease">
                                                <label class="form-check-label">Platform Ease of Use</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="liked[]"
                                                    value="video_quality">
                                                <label class="form-check-label">Video/Audio Quality</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="liked[]"
                                                    value="overall_experience">
                                                <label class="form-check-label">Overall Experience</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Your Comments</label>
                                    <textarea name="comment" class="form-control" rows="3" placeholder="Share your experience..." required></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Would you recommend this doctor?</label>
                                    <div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="recommend"
                                                id="recommend_yes{{ $consultation->id }}" value="yes" required>
                                            <label class="form-check-label"
                                                for="recommend_yes{{ $consultation->id }}">Yes</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="recommend"
                                                id="recommend_no{{ $consultation->id }}" value="no">
                                            <label class="form-check-label"
                                                for="recommend_no{{ $consultation->id }}">No</label>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">
                                    <i class="fas fa-paper-plane"></i> Submit Feedback
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-gray-200">
                    <div class="p-5 text-center py-10">
                        <i class="fas fa-star fa-3x text-gray-500 mb-3"></i>
                        <p class="text-gray-500">No consultations to review</p>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="w-full md:w-1/3">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar"></i> Your Feedback Stats
                    </h5>
                </div>
                <div class="p-5">
                    <div class="text-center mb-3">
                        <h2 class="text-amber-600">
                            @for ($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= ($avgRating ?? 0) ? '' : 'text-gray-500' }}"></i>
                            @endfor
                        </h2>
                        <small class="text-gray-500">Average Rating: {{ number_format($avgRating ?? 0, 1) }}/5.0</small>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                        <div class="w-1/2">
                            <h4 class="text-emerald-600">{{ $feedbackSubmitted ?? 0 }}</h4>
                            <small class="text-gray-500">Submitted</small>
                        </div>
                        <div class="w-1/2">
                            <h4 class="text-amber-600">{{ $pendingFeedback ?? 0 }}</h4>
                            <small class="text-gray-500">Pending</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.querySelectorAll('.rating-star').forEach(star => {
                star.addEventListener('click', function() {
                    const rating = this.dataset.rating;
                    const container = this.closest('.rating-stars');
                    const input = document.getElementById(container.dataset.ratingInput);

                    input.value = rating;

                    container.querySelectorAll('.rating-star').forEach(s => {
                        if (s.dataset.rating <= rating) {
                            s.classList.remove('text-muted');
                            s.classList.add('text-warning');
                        } else {
                            s.classList.remove('text-warning');
                            s.classList.add('text-muted');
                        }
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>
