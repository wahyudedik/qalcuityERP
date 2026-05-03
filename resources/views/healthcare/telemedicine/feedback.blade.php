<x-app-layout>
    <x-slot name="header">{{ __('Telemedicine Feedback') }}</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('healthcare.telemedicine.consultations') }}"
            class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-200">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Detail Konsultasi</h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-sm text-gray-600">Dokter: <span class="font-medium text-gray-900">Dr.
                                {{ $consultation->doctor?->name ?? '-' }}</span></p>
                        <p class="text-sm text-gray-600">Tanggal: <span
                                class="font-medium text-gray-900">{{ $consultation->scheduled_time ? $consultation->scheduled_time->format('l, d F Y') : '-' }}</span>
                        </p>
                        <p class="text-sm text-gray-600">Durasi: <span
                                class="font-medium text-gray-900">{{ $consultation->scheduled_duration ?? 30 }}
                                menit</span></p>
                    </div>
                </div>

                <form method="POST" action="{{ route('healthcare.telemedicine.feedback.store', $consultation) }}">
                    @csrf

                    {{-- Overall Rating --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pengalaman
                            Keseluruhan *</label>
                        <div class="flex items-center gap-2" id="star-rating">
                            @for ($i = 1; $i <= 5; $i++)
                                <button type="button"
                                    class="star text-3xl text-gray-300 hover:text-yellow-400 transition-colors"
                                    data-value="{{ $i }}">
                                    ★
                                </button>
                            @endfor
                            <span id="rating-text" class="ml-2 text-sm text-gray-600">Pilih
                                rating</span>
                        </div>
                        <input type="hidden" name="rating" id="rating-value" required />
                        @error('rating')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Detailed Ratings --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Profesionalisme
                                Dokter *</label>
                            <select name="doctor_rating" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Pilih rating</option>
                                @for ($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}">{{ $i }} -
                                        {{ ['Buruk', 'Cukup', 'Baik', 'Sangat Baik', 'Luar Biasa'][$i - 1] }}</option>
                                @endfor
                            </select>
                            @error('doctor_rating')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kualitas
                                Video/Audio</label>
                            <select name="video_quality"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Pilih rating (opsional)</option>
                                @for ($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}">{{ $i }} -
                                        {{ ['Buruk', 'Cukup', 'Baik', 'Sangat Baik', 'Luar Biasa'][$i - 1] }}</option>
                                @endfor
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kemudahan
                                Platform</label>
                            <select name="platform_rating"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Pilih rating (opsional)</option>
                                @for ($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}">{{ $i }} -
                                        {{ ['Buruk', 'Cukup', 'Baik', 'Sangat Baik', 'Luar Biasa'][$i - 1] }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    {{-- Feedback Text --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ulasan
                            Anda</label>
                        <textarea name="feedback" rows="4"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Ceritakan pengalaman konsultasi Anda...">{{ old('feedback') }}</textarea>
                    </div>

                    {{-- Positive & Negative Feedback --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Apa yang
                                baik?</label>
                            <textarea name="positive_feedback" rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Aspek positif...">{{ old('positive_feedback') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Apa yang
                                perlu diperbaiki?</label>
                            <textarea name="negative_feedback" rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Area perbaikan...">{{ old('negative_feedback') }}</textarea>
                        </div>
                    </div>

                    {{-- Suggestions --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Saran</label>
                        <textarea name="suggestions" rows="3"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Saran untuk perbaikan...">{{ old('suggestions') }}</textarea>
                    </div>

                    {{-- Recommendation & Follow-up --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div class="flex items-center">
                            <input type="checkbox" name="would_recommend" id="would_recommend" value="1"
                                {{ old('would_recommend', true) ? 'checked' : '' }}
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="would_recommend" class="ml-2 block text-sm text-gray-700">
                                Apakah Anda merekomendasikan dokter ini?
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" name="needs_followup" id="needs_followup" value="1"
                                {{ old('needs_followup') ? 'checked' : '' }}
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="needs_followup" class="ml-2 block text-sm text-gray-700">
                                Apakah Anda memerlukan konsultasi lanjutan?
                            </label>
                        </div>
                    </div>

                    {{-- Follow-up Notes --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Lanjutan
                            (jika diperlukan)</label>
                        <textarea name="followup_notes" rows="3"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Catatan tambahan untuk tindak lanjut...">{{ old('followup_notes') }}</textarea>
                    </div>

                    {{-- Submit Buttons --}}
                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('healthcare.telemedicine.consultations') }}"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Lewati Feedback
                        </a>
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">
                            <i class="fas fa-paper-plane mr-2"></i>Kirim Feedback
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            const stars = document.querySelectorAll('.star');
            const ratingValue = document.getElementById('rating-value');
            const ratingText = document.getElementById('rating-text');
            const ratingLabels = ['Buruk', 'Cukup', 'Baik', 'Sangat Baik', 'Luar Biasa'];

            stars.forEach(star => {
                star.addEventListener('click', function() {
                    const value = parseInt(this.dataset.value);
                    ratingValue.value = value;
                    stars.forEach((s, index) => {
                        s.classList.toggle('text-yellow-400', index < value);
                        s.classList.toggle('text-gray-300', index >= value);
                    });
                    ratingText.textContent = ratingLabels[value - 1];
                });

                star.addEventListener('mouseenter', function() {
                    const value = parseInt(this.dataset.value);
                    stars.forEach((s, index) => {
                        s.classList.toggle('text-yellow-300', index < value);
                    });
                });

                star.addEventListener('mouseleave', function() {
                    const currentValue = parseInt(ratingValue.value) || 0;
                    stars.forEach((s, index) => {
                        s.classList.remove('text-yellow-300');
                        s.classList.toggle('text-yellow-400', index < currentValue);
                        s.classList.toggle('text-gray-300', index >= currentValue);
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>
