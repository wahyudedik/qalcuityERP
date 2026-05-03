<x-app-layout>
    <x-slot name="header">Form Penerimaan Rawat Inap</x-slot>

    <div class="max-w-4xl mx-auto">
        <form action="{{ route('healthcare.inpatient.admissions.store') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Patient Information --}}
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">Informasi Pasien</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Pilih Pasien <span class="text-red-500">*</span>
                            </label>
                            <select name="patient_id" id="patient-select" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('patient_id') border-red-500 @enderror">
                                <option value="">-- Pilih Pasien --</option>
                                @if (isset($patients))
                                    @foreach ($patients as $patient)
                                        <option value="{{ $patient->id }}" @selected(old('patient_id') == $patient->id)>
                                            {{ $patient->full_name }} - {{ $patient->medical_record_number }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('patient_id')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ward & Bed Assignment --}}
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">Penempatan Ruang & Bed</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Ruang Rawat <span class="text-red-500">*</span>
                            </label>
                            <select name="ward_id" id="ward-select" required onchange="loadAvailableBeds(this.value)"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('ward_id') border-red-500 @enderror">
                                <option value="">-- Pilih Ruang --</option>
                                @if (isset($wards))
                                    @foreach ($wards as $ward)
                                        <option value="{{ $ward->id }}" @selected(old('ward_id') == $ward->id)>
                                            {{ $ward->name }} ({{ $ward->ward_type }})
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('ward_id')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tempat Tidur <span class="text-red-500">*</span>
                            </label>
                            <select name="bed_id" id="bed-select" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('bed_id') border-red-500 @enderror">
                                <option value="">-- Pilih ruang terlebih dahulu --</option>
                            </select>
                            @error('bed_id')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Admission Details --}}
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">Detail Penerimaan</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Dokter Penanggung Jawab <span class="text-red-500">*</span>
                            </label>
                            <select name="doctor_id" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('doctor_id') border-red-500 @enderror">
                                <option value="">-- Pilih Dokter --</option>
                                @if (isset($doctors))
                                    @foreach ($doctors as $doctor)
                                        <option value="{{ $doctor->id }}" @selected(old('doctor_id') == $doctor->id)>
                                            {{ $doctor->name }} - {{ $doctor->specialization }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('doctor_id')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tanggal & Waktu Masuk <span class="text-red-500">*</span>
                            </label>
                            <input type="datetime-local" name="admission_date"
                                value="{{ old('admission_date', now()->format('Y-m-d\TH:i')) }}" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('admission_date') border-red-500 @enderror">
                            @error('admission_date')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Diagnosa Masuk <span class="text-red-500">*</span>
                            </label>
                            <textarea name="admission_diagnosis" rows="3" required placeholder="Diagnosa saat pasien masuk..."
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('admission_diagnosis') border-red-500 @enderror">{{ old('admission_diagnosis') }}</textarea>
                            @error('admission_diagnosis')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Alasan Rawat Inap
                            </label>
                            <textarea name="reason" rows="2" placeholder="Jelaskan alasan pasien perlu rawat inap..."
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('reason') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tipe Perawatan
                            </label>
                            <select name="care_type"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="routine">Routine (Biasa)</option>
                                <option value="intensive">Intensive (Intensif)</option>
                                <option value="critical">Critical (Kritis)</option>
                                <option value="post_surgery">Post Surgery (Pasca Operasi)</option>
                                <option value="maternity">Maternity (Kebidanan)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Estimasi Lama Rawat (hari)
                            </label>
                            <input type="number" name="estimated_days" min="1"
                                value="{{ old('estimated_days', 3) }}"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Catatan Khusus
                            </label>
                            <textarea name="notes" rows="2" placeholder="Catatan tambahan untuk perawat atau dokter..."
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Status Pembayaran
                            </label>
                            <select name="payment_status"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="self_pay">Bayar Sendiri</option>
                                <option value="bpjs">BPJS</option>
                                <option value="insurance">Asuransi Swasta</option>
                                <option value="corporate">Korporat</option>
                            </select>
                        </div>

                        <div>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="notify_family" value="1" checked
                                    class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                <span class="text-sm text-gray-700">Notifikasi keluarga</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex justify-end gap-3">
                <a href="{{ route('healthcare.inpatient.admissions.index') }}"
                    class="px-6 py-2.5 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Batal</a>
                <button type="submit"
                    class="px-6 py-2.5 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium">
                    Proses Penerimaan
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            function loadAvailableBeds(wardId) {
                const bedSelect = document.getElementById('bed-select');
                bedSelect.innerHTML = '<option value="">Loading...</option>';

                if (!wardId) {
                    bedSelect.innerHTML = '<option value="">-- Pilih ruang terlebih dahulu --</option>';
                    return;
                }

                // Fetch available beds via AJAX
                fetch(`/healthcare/api/wards/${wardId}/available-beds`)
                    .then(response => response.json())
                    .then(beds => {
                        bedSelect.innerHTML = '<option value="">-- Pilih Bed --</option>';
                        beds.forEach(bed => {
                            bedSelect.innerHTML += `<option value="${bed.id}">Bed ${bed.bed_number}</option>`;
                        });

                        if (beds.length === 0) {
                            bedSelect.innerHTML = '<option value="">Tidak ada bed tersedia</option>';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        bedSelect.innerHTML = '<option value="">Error loading beds</option>';
                    });
            }
        </script>
    @endpush
</x-app-layout>
