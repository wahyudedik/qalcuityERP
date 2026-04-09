<x-app-layout>
    <x-slot name="header">Buat Janji Temu Baru</x-slot>

    {{-- Breadcrumbs --}}
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Janji Temu', 'url' => route('healthcare.appointments.index')],
        ['label' => 'Buat Janji Temu'],
    ]" />

    <div class="py-4">
        <form action="{{ route('healthcare.appointments.store') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Patient Selection --}}
            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Informasi Pasien</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                                Pilih Pasien <span class="text-red-500">*</span>
                            </label>
                            <select name="patient_id" id="patient-select" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 @error('patient_id') border-red-500 @enderror">
                                <option value="">-- Pilih Pasien --</option>
                                @if (isset($patients))
                                    @foreach ($patients as $patient)
                                        <option value="{{ $patient->id }}" @selected(old('patient_id', request('patient_id')) == $patient->id)>
                                            {{ $patient->full_name }} - {{ $patient->medical_record_number }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('patient_id')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500 dark:text-slate-400">
                                <a href="{{ route('healthcare.patients.create') }}"
                                    class="text-blue-600 hover:underline">+ Tambah pasien baru</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Doctor & Schedule --}}
            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Dokter & Jadwal</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                                Pilih Dokter <span class="text-red-500">*</span>
                            </label>
                            <select name="doctor_id" id="doctor-select" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 @error('doctor_id') border-red-500 @enderror">
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
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                                Tanggal Janji Temu <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="appointment_date" id="appointment-date"
                                value="{{ old('appointment_date', request('date')) }}" min="{{ date('Y-m-d') }}"
                                required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 @error('appointment_date') border-red-500 @enderror">
                            @error('appointment_date')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                                    Waktu Mulai <span class="text-red-500">*</span>
                                </label>
                                <input type="time" name="start_time" value="{{ old('start_time') }}" required
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 @error('start_time') border-red-500 @enderror">
                                @error('start_time')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                                    Waktu Selesai
                                </label>
                                <input type="time" name="end_time" value="{{ old('end_time') }}"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                                Tipe Layanan <span class="text-red-500">*</span>
                            </label>
                            <select name="service_type" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 @error('service_type') border-red-500 @enderror">
                                <option value="">-- Pilih Layanan --</option>
                                <option value="Konsultasi Umum" @selected(old('service_type') === 'Konsultasi Umum')>Konsultasi Umum</option>
                                <option value="Pemeriksaan Rutin" @selected(old('service_type') === 'Pemeriksaan Rutin')>Pemeriksaan Rutin
                                </option>
                                <option value="Konsultasi Spesialis" @selected(old('service_type') === 'Konsultasi Spesialis')>Konsultasi Spesialis
                                </option>
                                <option value="Tindakan Medis" @selected(old('service_type') === 'Tindakan Medis')>Tindakan Medis</option>
                                <option value="Follow-up" @selected(old('service_type') === 'Follow-up')>Follow-up</option>
                                <option value="Emergency" @selected(old('service_type') === 'Emergency')>Emergency</option>
                            </select>
                            @error('service_type')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Additional Information --}}
            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Informasi Tambahan</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                                Prioritas
                            </label>
                            <select name="priority"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="normal" @selected(old('priority', 'normal') === 'normal')>Normal</option>
                                <option value="urgent" @selected(old('priority') === 'urgent')>Urgent</option>
                                <option value="high" @selected(old('priority') === 'high')>High</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                                Keluhan / Catatan
                            </label>
                            <textarea name="notes" rows="4" placeholder="Deskripsikan keluhan atau catatan tambahan..."
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                                Metode Pembayaran
                            </label>
                            <select name="payment_method"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih Metode --</option>
                                <option value="self_pay" @selected(old('payment_method') === 'self_pay')>Bayar Sendiri</option>
                                <option value="bpjs" @selected(old('payment_method') === 'bpjs')>BPJS</option>
                                <option value="insurance" @selected(old('payment_method') === 'insurance')>Asuransi Swasta</option>
                                <option value="corporate" @selected(old('payment_method') === 'corporate')>Korporat</option>
                            </select>
                        </div>

                        <div>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="send_notification" value="1" checked
                                    class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                <span class="text-sm text-gray-700 dark:text-slate-300">Kirim notifikasi ke
                                    pasien</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex justify-end gap-3">
                <a href="{{ route('healthcare.appointments.index') }}"
                    class="px-6 py-2.5 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Batal</a>
                <button type="submit"
                    class="px-6 py-2.5 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium">
                    Buat Janji Temu
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            // Auto-fill end time when start time changes
            document.querySelector('input[name="start_time"]')?.addEventListener('change', function() {
                const startTime = this.value;
                if (startTime && !document.querySelector('input[name="end_time"]').value) {
                    const [hours, minutes] = startTime.split(':');
                    const endDate = new Date();
                    endDate.setHours(parseInt(hours) + 1);
                    endDate.setMinutes(minutes);
                    const endTime = endDate.toTimeString().slice(0, 5);
                    document.querySelector('input[name="end_time"]').value = endTime;
                }
            });
        </script>
    @endpush
</x-app-layout>
