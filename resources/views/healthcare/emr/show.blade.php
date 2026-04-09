<x-app-layout>
    <x-slot name="header">Rekam Medis - {{ $visit->patient->full_name ?? 'Pasien' }}</x-slot>

    {{-- Patient Info Banner --}}
    <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-2xl p-6 mb-6 text-white">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold">{{ $visit->patient->full_name ?? '-' }}</h2>
                    <p class="text-sm text-white/80">RM: {{ $visit->patient->medical_record_number ?? '-' }} |
                        {{ $visit->visit_type ?? 'Kunjungan' }}</p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('healthcare.emr.prescribe', $visit) }}"
                    class="px-4 py-2 text-sm bg-white text-blue-600 rounded-xl hover:bg-white/90 font-medium">+
                    Resep</a>
                <a href="{{ route('healthcare.emr.diagnose', $visit) }}"
                    class="px-4 py-2 text-sm bg-white/20 backdrop-blur-sm border border-white/30 rounded-xl hover:bg-white/30">+
                    Diagnosa</a>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 mb-6">
        <div class="border-b border-gray-200 dark:border-white/10">
            <nav class="flex gap-6 px-6" aria-label="Tabs">
                <button onclick="switchTab('overview')" id="tab-overview"
                    class="tab-button py-4 px-1 border-b-2 font-medium text-sm border-blue-500 text-blue-600 dark:text-blue-400">
                    Overview
                </button>
                <button onclick="switchTab('diagnoses')" id="tab-diagnoses"
                    class="tab-button py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 dark:text-slate-400 dark:hover:text-slate-300">
                    Diagnosa
                </button>
                <button onclick="switchTab('prescriptions')" id="tab-prescriptions"
                    class="tab-button py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 dark:text-slate-400 dark:hover:text-slate-300">
                    Resep
                </button>
                <button onclick="switchTab('lab-results')" id="tab-lab-results"
                    class="tab-button py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 dark:text-slate-400 dark:hover:text-slate-300">
                    Lab
                </button>
                <button onclick="switchTab('notes')" id="tab-notes"
                    class="tab-button py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 dark:text-slate-400 dark:hover:text-slate-300">
                    Catatan
                </button>
            </nav>
        </div>

        {{-- Tab: Overview --}}
        <div id="content-overview" class="tab-content p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                {{-- Visit Information --}}
                <div class="p-4 bg-gray-50 dark:bg-white/5 rounded-xl">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Informasi Kunjungan</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-slate-400">Tanggal</span>
                            <span
                                class="text-gray-900 dark:text-white font-medium">{{ $visit->visit_date ? \Carbon\Carbon::parse($visit->visit_date)->format('d M Y H:i') : '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-slate-400">Tipe</span>
                            <span
                                class="text-gray-900 dark:text-white font-medium">{{ $visit->visit_type ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-slate-400">Dokter</span>
                            <span
                                class="text-gray-900 dark:text-white font-medium">{{ $visit->doctor ? $visit->doctor->name : '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-slate-400">Status</span>
                            <span>
                                @if ($visit->status === 'in_progress')
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">Berlangsung</span>
                                @elseif($visit->status === 'completed')
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-lg bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Selesai</span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-lg bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">{{ $visit->status ?? '-' }}</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Vitals --}}
                <div class="p-4 bg-gray-50 dark:bg-white/5 rounded-xl">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Tanda Vital</h4>
                    @if ($visit->vitals)
                        @php
                            $vitals = is_string($visit->vitals) ? json_decode($visit->vitals, true) : $visit->vitals;
                        @endphp
                        <div class="grid grid-cols-2 gap-3">
                            @if (isset($vitals['blood_pressure']))
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-slate-400">Tekanan Darah</p>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ $vitals['blood_pressure'] }} mmHg</p>
                                </div>
                            @endif
                            @if (isset($vitals['heart_rate']))
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-slate-400">Denyut Jantung</p>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ $vitals['heart_rate'] }} bpm</p>
                                </div>
                            @endif
                            @if (isset($vitals['temperature']))
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-slate-400">Suhu</p>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ $vitals['temperature'] }}°C</p>
                                </div>
                            @endif
                            @if (isset($vitals['weight']))
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-slate-400">Berat Badan</p>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ $vitals['weight'] }} kg</p>
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-slate-400">Belum ada data tanda vital</p>
                    @endif
                </div>
            </div>

            {{-- Chief Complaint --}}
            <div class="p-4 bg-gray-50 dark:bg-white/5 rounded-xl mb-6">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Keluhan Utama</h4>
                <p class="text-sm text-gray-700 dark:text-slate-300">
                    {{ $visit->chief_complaint ?? 'Belum ada keluhan utama yang dicatat' }}</p>
            </div>

            {{-- Examination Notes --}}
            <div class="p-4 bg-gray-50 dark:bg-white/5 rounded-xl">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Hasil Pemeriksaan</h4>
                <p class="text-sm text-gray-700 dark:text-slate-300">
                    {{ $visit->examination_notes ?? 'Belum ada hasil pemeriksaan' }}</p>
            </div>
        </div>

        {{-- Tab: Diagnoses --}}
        <div id="content-diagnoses" class="tab-content p-6 hidden">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Daftar Diagnosa</h3>
                <a href="{{ route('healthcare.emr.diagnose', $visit) }}"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Tambah Diagnosa</a>
            </div>
            @if ($visit->diagnoses && $visit->diagnoses->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Kode ICD-10</th>
                                <th class="px-4 py-3 text-left">Diagnosa</th>
                                <th class="px-4 py-3 text-center">Tipe</th>
                                <th class="px-4 py-3 text-left hidden md:table-cell">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @foreach ($visit->diagnoses as $diagnosis)
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                    <td class="px-4 py-3">
                                        <span
                                            class="font-mono text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 px-2 py-1 rounded-lg">
                                            {{ $diagnosis->icd_code ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                        {{ $diagnosis->description ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($diagnosis->type === 'primary')
                                            <span
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Utama</span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">Sekunder</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-slate-300 hidden md:table-cell">
                                        {{ $diagnosis->notes ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-center text-gray-500 dark:text-slate-400 py-8">Belum ada diagnosa</p>
            @endif
        </div>

        {{-- Tab: Prescriptions --}}
        <div id="content-prescriptions" class="tab-content p-6 hidden">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Daftar Resep</h3>
                <a href="{{ route('healthcare.emr.prescribe', $visit) }}"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Buat Resep</a>
            </div>
            @if ($visit->prescriptions && $visit->prescriptions->count() > 0)
                <div class="space-y-4">
                    @foreach ($visit->prescriptions as $prescription)
                        <div class="p-4 bg-gray-50 dark:bg-white/5 rounded-xl">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ $prescription->medication_name ?? '-' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-slate-400">
                                        {{ $prescription->dosage ?? '-' }}</p>
                                </div>
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
                                    {{ $prescription->status ?? 'Pending' }}
                                </span>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                                <div>
                                    <p class="text-gray-500 dark:text-slate-400">Frekuensi</p>
                                    <p class="text-gray-900 dark:text-white font-medium mt-1">
                                        {{ $prescription->frequency ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 dark:text-slate-400">Durasi</p>
                                    <p class="text-gray-900 dark:text-white font-medium mt-1">
                                        {{ $prescription->duration ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 dark:text-slate-400">Jumlah</p>
                                    <p class="text-gray-900 dark:text-white font-medium mt-1">
                                        {{ $prescription->quantity ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 dark:text-slate-400">Instruksi</p>
                                    <p class="text-gray-900 dark:text-white font-medium mt-1">
                                        {{ $prescription->instructions ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-gray-500 dark:text-slate-400 py-8">Belum ada resep</p>
            @endif
        </div>

        {{-- Tab: Lab Results --}}
        <div id="content-lab-results" class="tab-content p-6 hidden">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Hasil Laboratorium</h3>
            <p class="text-center text-gray-500 dark:text-slate-400 py-8">Belum ada hasil laboratorium</p>
        </div>

        {{-- Tab: Notes --}}
        <div id="content-notes" class="tab-content p-6 hidden">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Catatan Medis</h3>
            <p class="text-center text-gray-500 dark:text-slate-400 py-8">Belum ada catatan medis</p>
        </div>
    </div>

    @push('scripts')
        <script>
            function switchTab(tabName) {
                document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('.tab-button').forEach(el => {
                    el.classList.remove('border-blue-500', 'text-blue-600', 'dark:text-blue-400');
                    el.classList.add('border-transparent', 'text-gray-500', 'dark:text-slate-400');
                });

                document.getElementById('content-' + tabName).classList.remove('hidden');
                const activeTab = document.getElementById('tab-' + tabName);
                activeTab.classList.remove('border-transparent', 'text-gray-500', 'dark:text-slate-400');
                activeTab.classList.add('border-blue-500', 'text-blue-600', 'dark:text-blue-400');
            }
        </script>
    @endpush
</x-app-layout>
