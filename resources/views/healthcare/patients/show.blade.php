<x-app-layout>
    <x-slot name="header">Detail Pasien - {{ $patient->full_name }}</x-slot>

    {{-- Breadcrumbs --}}
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Data Pasien', 'url' => route('healthcare.patients.index')],
        ['label' => 'Detail Pasien'],
    ]" />

    <div class="py-4">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                        <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $patient->full_name }}</h2>
                        <p class="text-sm text-gray-500 dark:text-slate-400">RM: {{ $patient->medical_record_number }} |
                            NIK: {{ $patient->nik ?? '-' }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('healthcare.patients.edit', $patient) }}"
                        class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Edit</a>
                    <a href="{{ route('healthcare.appointments.book', ['patient_id' => $patient->id]) }}"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Janji Temu</a>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-6 border-t border-gray-200 dark:border-white/10">
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Tanggal Lahir</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">
                        {{ $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->format('d M Y') : '-' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Gender</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">
                        {{ $patient->gender === 'male' ? 'Laki-laki' : ($patient->gender === 'female' ? 'Perempuan' : '-') }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Telepon</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ $patient->phone ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Status</p>
                    <p class="mt-1">
                        @if ($patient->status === 'active')
                            <span
                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Aktif</span>
                        @elseif($patient->status === 'inactive')
                            <span
                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">Nonaktif</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 mb-6">
            <div class="border-b border-gray-200 dark:border-white/10">
                <nav class="flex gap-6 px-6" aria-label="Tabs">
                    <button onclick="switchTab('visits')" id="tab-visits"
                        class="tab-button py-4 px-1 border-b-2 font-medium text-sm border-blue-500 text-blue-600 dark:text-blue-400">
                        Kunjungan
                    </button>
                    <button onclick="switchTab('appointments')" id="tab-appointments"
                        class="tab-button py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 dark:text-slate-400 dark:hover:text-slate-300">
                        Janji Temu
                    </button>
                    <button onclick="switchTab('emr')" id="tab-emr"
                        class="tab-button py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 dark:text-slate-400 dark:hover:text-slate-300">
                        Rekam Medis
                    </button>
                    <button onclick="switchTab('billing')" id="tab-billing"
                        class="tab-button py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 dark:text-slate-400 dark:hover:text-slate-300">
                        Tagihan
                    </button>
                </nav>
            </div>

            {{-- Tab Content: Visits --}}
            <div id="content-visits" class="tab-content p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Riwayat Kunjungan</h3>
                @if ($patient->visits && $patient->visits->count() > 0)
                    <div class="space-y-3">
                        @foreach ($patient->visits->take(10) as $visit)
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-white/5 rounded-xl">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                            </path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $visit->visit_type ?? 'Kunjungan' }}</p>
                                        <p class="text-xs text-gray-500 dark:text-slate-400">
                                            {{ $visit->doctor ? $visit->doctor->name : '-' }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-900 dark:text-white">
                                        {{ $visit->visit_date ? \Carbon\Carbon::parse($visit->visit_date)->format('d M Y') : '-' }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-slate-400">{{ $visit->status ?? '-' }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-gray-500 dark:text-slate-400 py-8">Belum ada riwayat kunjungan</p>
                @endif
            </div>

            {{-- Tab Content: Appointments --}}
            <div id="content-appointments" class="tab-content p-6 hidden">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Janji Temu</h3>
                    <a href="{{ route('healthcare.appointments.book', ['patient_id' => $patient->id]) }}"
                        class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Buat Janji</a>
                </div>
                @if ($patient->appointments && $patient->appointments->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead
                                class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                                <tr>
                                    <th class="px-4 py-3 text-left">Tanggal</th>
                                    <th class="px-4 py-3 text-left">Dokter</th>
                                    <th class="px-4 py-3 text-left">Layanan</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                                @foreach ($patient->appointments->take(10) as $appointment)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                        <td class="px-4 py-3">
                                            {{ $appointment->appointment_date ? \Carbon\Carbon::parse($appointment->appointment_date)->format('d M Y H:i') : '-' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            {{ $appointment->doctor ? $appointment->doctor->name : '-' }}
                                        </td>
                                        <td class="px-4 py-3">{{ $appointment->service_type ?? '-' }}</td>
                                        <td class="px-4 py-3 text-center">
                                            @if ($appointment->status === 'scheduled')
                                                <span
                                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">Terjadwal</span>
                                            @elseif($appointment->status === 'completed')
                                                <span
                                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Selesai</span>
                                            @elseif($appointment->status === 'cancelled')
                                                <span
                                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Dibatalkan</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-center text-gray-500 dark:text-slate-400 py-8">Belum ada janji temu</p>
                @endif
            </div>

            {{-- Tab Content: EMR --}}
            <div id="content-emr" class="tab-content p-6 hidden">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Rekam Medis Elektronik</h3>
                    @if ($patient->visits && $patient->visits->count() > 0)
                        <a href="{{ route('healthcare.emr.show', $patient->visits->first()) }}"
                            class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Lihat
                            EMR</a>
                    @endif
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 bg-gray-50 dark:bg-white/5 rounded-xl">
                        <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Golongan Darah</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $patient->blood_type ?? '-' }}
                        </p>
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-white/5 rounded-xl">
                        <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Alergi</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $patient->allergies ?? '-' }}
                        </p>
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-white/5 rounded-xl">
                        <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Asuransi</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $patient->insurance_provider ?? '-' }}</p>
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-white/5 rounded-xl">
                        <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">No. Polis</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $patient->insurance_policy ?? '-' }}</p>
                    </div>
                </div>
            </div>

            {{-- Tab Content: Billing --}}
            <div id="content-billing" class="tab-content p-6 hidden">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Riwayat Tagihan</h3>
                <p class="text-center text-gray-500 dark:text-slate-400 py-8">Fitur tagihan akan ditampilkan di sini
                </p>
            </div>
        </div>

        @push('scripts')
            <script>
                function switchTab(tabName) {
                    // Hide all tab contents
                    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
                    // Remove active state from all tabs
                    document.querySelectorAll('.tab-button').forEach(el => {
                        el.classList.remove('border-blue-500', 'text-blue-600', 'dark:text-blue-400');
                        el.classList.add('border-transparent', 'text-gray-500', 'dark:text-slate-400');
                    });

                    // Show selected tab content
                    document.getElementById('content-' + tabName).classList.remove('hidden');
                    // Activate selected tab
                    const activeTab = document.getElementById('tab-' + tabName);
                    activeTab.classList.remove('border-transparent', 'text-gray-500', 'dark:text-slate-400');
                    activeTab.classList.add('border-blue-500', 'text-blue-600', 'dark:text-blue-400');
                }
            </script>
        @endpush
</x-app-layout>
