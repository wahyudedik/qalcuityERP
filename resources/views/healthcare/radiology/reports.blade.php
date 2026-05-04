<x-app-layout>
    <x-slot name="header">Laporan Radiologi</x-slot>

    @php $tid = auth()->user()->tenant_id; @endphp

    @if (!isset($exam))
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 mb-6">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-amber-600 mt-0.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                    </path>
                </svg>
                <div>
                    <p class="font-bold text-amber-800">Pilih Pemeriksaan</p>
                    <p class="text-sm text-amber-700 mt-1">Silakan pilih pemeriksaan radiologi dari
                        daftar untuk membuat laporan.</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Exam Info --}}
    @if (isset($exam))
        <div
            class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6">
            <div
                class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Exam:
                            {{ $exam->exam_number ?? '-' }}</h3>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ $exam->patient ? $exam->patient?->full_name : '-' }} |
                            {{ $exam->patient ? $exam->patient?->medical_record_number : '-' }}</p>
                    </div>
                    <a href="{{ route('healthcare.radiology.exams') }}"
                        class="px-4 py-2 text-sm border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50">
                        Kembali
                    </a>
                </div>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs text-gray-500">Jenis Exam</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">
                        {{ ucfirst(str_replace('_', ' ', $exam->exam_type ?? '-')) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Body Part</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">{{ $exam->body_part ?? '-' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Dokter</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">
                        {{ $exam->doctor ? $exam->doctor?->name : '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Tanggal Exam</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">
                        {{ $exam->exam_date ? \Carbon\Carbon::parse($exam->exam_date)->format('d M Y H:i') : '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Report Form --}}
        <form action="{{ route('healthcare.radiology.reports.store', $exam) }}" method="POST" class="space-y-6">
            @csrf

            {{-- Clinical Information --}}
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Informasi Klinis</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Clinical
                            History</label>
                        <textarea name="clinical_history" rows="3"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Riwayat klinis pasien...">{{ old('clinical_history', $exam->clinical_notes ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Findings --}}
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Temuan (Findings)</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi
                            Temuan</label>
                        <textarea name="findings" rows="6"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Deskripsi lengkap temuan radiologi...">{{ old('findings') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ukuran Lesi (jika
                            ada)</label>
                        <input type="text" name="lesion_size" value="{{ old('lesion_size') }}"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Contoh: 2.5 x 3.0 cm">
                    </div>
                </div>
            </div>

            {{-- Impression & Conclusion --}}
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Kesan & Kesimpulan</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Impression /
                            Diagnosis</label>
                        <textarea name="impression" rows="4"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Kesan radiologis...">{{ old('impression') }}</textarea>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2">Rekomendasi</label>
                        <textarea name="recommendations" rows="3"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Rekomendasi follow-up...">{{ old('recommendations') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Urgency Flag --}}
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Status Laporan</h3>
                </div>
                <div class="p-6">
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_urgent" value="1"
                                {{ old('is_urgent') ? 'checked' : '' }}
                                class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                            <span class="text-sm text-gray-700">Temuan Kritis (Urgent)</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="notify_doctor" value="1" checked
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="text-sm text-gray-700">Notifikasi Dokter</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('healthcare.radiology.exams') }}"
                    class="px-6 py-2.5 text-sm border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit"
                    class="px-6 py-2.5 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium">
                    Simpan Laporan
                </button>
            </div>
        </form>
    @endif
</x-app-layout>
