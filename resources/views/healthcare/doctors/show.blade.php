<x-app-layout>
    <x-slot name="header">Profil Dokter - {{ $doctor->name }}</x-slot>

    {{-- Breadcrumbs --}}
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Data Dokter', 'url' => route('healthcare.doctors.index')],
        ['label' => 'Profil Dokter'],
    ]" />

    <div class="py-4">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    @if ($doctor->photo)
                        <img src="{{ $doctor->photo }}" alt="{{ $doctor->name }}" loading="lazy"
                            class="w-20 h-20 rounded-2xl object-cover border-2 border-gray-200 dark:border-white/10">
                    @else
                        <div
                            class="w-20 h-20 rounded-2xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                            <svg class="w-10 h-10 text-purple-600 dark:text-purple-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    @endif
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $doctor->name }}</h2>
                        <p class="text-sm text-gray-500 dark:text-slate-400">
                            {{ $doctor->specialization ?? 'Dokter Umum' }}
                        </p>
                        <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">STR: {{ $doctor->str_number ?? '-' }}
                        </p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('healthcare.doctors.index') }}"
                        class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Kembali</a>
                    <button onclick="document.getElementById('modal-schedule').classList.remove('hidden')"
                        class="px-4 py-2 text-sm bg-purple-600 text-white rounded-xl hover:bg-purple-700">+
                        Jadwal</button>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-6 border-t border-gray-200 dark:border-white/10">
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Email</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ $doctor->email ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Telepon</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ $doctor->phone ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Status</p>
                    <p class="mt-1">
                        @if ($doctor->status === 'active')
                            <span
                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Aktif</span>
                        @elseif($doctor->status === 'inactive')
                            <span
                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">Nonaktif</span>
                        @elseif($doctor->status === 'on_leave')
                            <span
                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Cuti</span>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Total Pasien</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">
                        @php
                            $patientCount = $doctor->appointments
                                ? $doctor->appointments->where('status', 'completed')->count()
                                : 0;
                        @endphp
                        {{ $patientCount }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 mb-6">
            <div class="border-b border-gray-200 dark:border-white/10">
                <nav class="flex gap-6 px-6" aria-label="Tabs">
                    <button onclick="switchTab('schedule')" id="tab-schedule"
                        class="tab-button py-4 px-1 border-b-2 font-medium text-sm border-blue-500 text-blue-600 dark:text-blue-400">
                        Jadwal Praktik
                    </button>
                    <button onclick="switchTab('appointments')" id="tab-appointments"
                        class="tab-button py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 dark:text-slate-400 dark:hover:text-slate-300">
                        Janji Temu
                    </button>
                    <button onclick="switchTab('patients')" id="tab-patients"
                        class="tab-button py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 dark:text-slate-400 dark:hover:text-slate-300">
                        Riwayat Pasien
                    </button>
                </nav>
            </div>

            {{-- Tab Content: Schedule --}}
            <div id="content-schedule" class="tab-content p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Jadwal Praktik Mingguan</h3>
                @if ($doctor->schedules && $doctor->schedules->count() > 0)
                    <div class="space-y-3">
                        @php
                            $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                        @endphp
                        @foreach ($doctor->schedules->sortBy('day_of_week') as $schedule)
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-white/5 rounded-xl">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $days[$schedule->day_of_week] ?? '-' }}</p>
                                        @if ($schedule->location)
                                            <p class="text-xs text-gray-500 dark:text-slate-400">
                                                {{ $schedule->location }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $schedule->start_time }} - {{ $schedule->end_time }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-slate-400">
                                            {{ $schedule->max_patients ? 'Maks ' . $schedule->max_patients . ' pasien' : 'Tidak ada batas' }}
                                        </p>
                                    </div>
                                    @if ($schedule->is_active)
                                        <span
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Aktif</span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">Nonaktif</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-gray-500 dark:text-slate-400 py-8">Belum ada jadwal praktik</p>
                @endif
            </div>

            {{-- Tab Content: Appointments --}}
            <div id="content-appointments" class="tab-content p-6 hidden">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Janji Temu</h3>
                    <a href="{{ route('healthcare.appointments.index', ['doctor_id' => $doctor->id]) }}"
                        class="px-3 py-1.5 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Lihat
                        Semua</a>
                </div>
                @if ($doctor->appointments && $doctor->appointments->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead
                                class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                                <tr>
                                    <th class="px-4 py-3 text-left">Pasien</th>
                                    <th class="px-4 py-3 text-left hidden md:table-cell">Tanggal</th>
                                    <th class="px-4 py-3 text-left hidden lg:table-cell">Layanan</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                                @foreach ($doctor->appointments->take(10) as $appointment)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                        <td class="px-4 py-3">
                                            <p class="font-medium text-gray-900 dark:text-white">
                                                {{ $appointment->patient ? $appointment->patient->full_name : '-' }}
                                            </p>
                                        </td>
                                        <td class="px-4 py-3 hidden md:table-cell">
                                            {{ $appointment->appointment_date ? \Carbon\Carbon::parse($appointment->appointment_date)->format('d M Y H:i') : '-' }}
                                        </td>
                                        <td class="px-4 py-3 hidden lg:table-cell">
                                            {{ $appointment->service_type ?? '-' }}
                                        </td>
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

            {{-- Tab Content: Patients --}}
            <div id="content-patients" class="tab-content p-6 hidden">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Riwayat Pasien</h3>
                <p class="text-center text-gray-500 dark:text-slate-400 py-8">Data riwayat pasien akan ditampilkan di
                    sini
                </p>
            </div>
        </div>

        {{-- Add Schedule Modal --}}
        <div id="modal-schedule"
            class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-lg">
                <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-white/10">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Tambah Jadwal Praktik</h3>
                    <button onclick="document.getElementById('modal-schedule').classList.add('hidden')"
                        class="p-2 hover:bg-gray-100 dark:hover:bg-white/10 rounded-xl">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <form action="{{ route('healthcare.doctors.schedules.store', $doctor) }}" method="POST"
                    class="p-6 space-y-4">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Hari
                                *</label>
                            <select name="day_of_week" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Pilih Hari</option>
                                <option value="1">Senin</option>
                                <option value="2">Selasa</option>
                                <option value="3">Rabu</option>
                                <option value="4">Kamis</option>
                                <option value="5">Jumat</option>
                                <option value="6">Sabtu</option>
                                <option value="0">Minggu</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Jam
                                    Mulai
                                    *</label>
                                <input type="time" name="start_time" required
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Jam
                                    Selesai
                                    *</label>
                                <input type="time" name="end_time" required
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Lokasi</label>
                            <input type="text" name="location" placeholder="Contoh: Ruang Praktik A"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Maksimum
                                Pasien</label>
                            <input type="number" name="max_patients" min="1"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="is_active" value="1" checked
                                    class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                <span class="text-sm text-gray-700 dark:text-slate-300">Jadwal Aktif</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button"
                            onclick="document.getElementById('modal-schedule').classList.add('hidden')"
                            class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Batal</button>
                        <button type="submit"
                            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                    </div>
                </form>
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
