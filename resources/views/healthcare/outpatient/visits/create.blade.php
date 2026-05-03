<x-app-layout>
    <x-slot name="header">Form Kunjungan Rawat Jalan Baru</x-slot>

    <div class="max-w-3xl mx-auto">
        <form action="{{ route('healthcare.outpatient.visits.store') }}" method="POST" class="space-y-6">
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
                            <select name="patient_id" required
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
                            <p class="mt-1 text-xs text-gray-500">
                                <a href="{{ route('healthcare.patients.create') }}"
                                    class="text-blue-600 hover:underline">+ Tambah pasien baru</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Visit Details --}}
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">Detail Kunjungan</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Dokter Tujuan <span class="text-red-500">*</span>
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
                                Tanggal & Waktu Kunjungan <span class="text-red-500">*</span>
                            </label>
                            <input type="datetime-local" name="visit_date"
                                value="{{ old('visit_date', now()->format('Y-m-d\TH:i')) }}" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('visit_date') border-red-500 @enderror">
                            @error('visit_date')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Keperluan / Keluhan <span class="text-red-500">*</span>
                            </label>
                            <textarea name="purpose" rows="3" required placeholder="Jelaskan keperluan atau keluhan pasien..."
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('purpose') border-red-500 @enderror">{{ old('purpose') }}</textarea>
                            @error('purpose')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tipe Kunjungan
                            </label>
                            <select name="visit_type"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="new">Pasien Baru</option>
                                <option value="follow_up">Follow-up / Kontrol</option>
                                <option value="referral">Rujukan</option>
                                <option value="check_up">Medical Check-up</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Sumber Rujukan
                            </label>
                            <select name="referral_source"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Tidak Ada</option>
                                <option value="internal">Internal (Dokter Lain)</option>
                                <option value="external">Eksternal (RS Lain)</option>
                                <option value="clinic">Klinik</option>
                                <option value="puskesmas">Puskesmas</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Catatan Tambahan
                            </label>
                            <textarea name="notes" rows="2" placeholder="Catatan tambahan untuk kunjungan..."
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Metode Pembayaran
                            </label>
                            <select name="payment_method"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="self_pay">Bayar Sendiri</option>
                                <option value="bpjs">BPJS</option>
                                <option value="insurance">Asuransi Swasta</option>
                                <option value="corporate">Korporat</option>
                            </select>
                        </div>

                        <div>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="create_queue" value="1" checked
                                    class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                <span class="text-sm text-gray-700">Buat nomor antrian
                                    otomatis</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex justify-end gap-3">
                <a href="{{ route('healthcare.outpatient.visits.index') }}"
                    class="px-6 py-2.5 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Batal</a>
                <button type="submit"
                    class="px-6 py-2.5 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium">
                    Daftarkan Kunjungan
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
