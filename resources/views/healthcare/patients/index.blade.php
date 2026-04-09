<x-app-layout>
    <x-slot name="header">Data Pasien</x-slot>

    {{-- Breadcrumbs --}}
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Data Pasien'],
    ]" />

    {{-- Stats - Data from Controller (no more queries in Blade) --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Pasien</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                {{ number_format($stats['total_patients']) }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Pasien Aktif</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">
                {{ number_format($stats['active_patients']) }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Janji Hari Ini</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ $stats['today_appointments'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Pasien Rawat Inap</p>
            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-1">{{ $stats['admitted_patients'] }}
            </p>
        </div>
    </div>

    {{-- Toolbar with Filters --}}
    <x-healthcare.toolbar>
        <x-slot name="filters">
            <form method="GET">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <x-healthcare.filter-input name="search" label="Pencarian" value="{{ request('search') }}"
                        placeholder="Cari nama / NIK / RM..." />
                    <x-healthcare.filter-select name="status" label="Status" value="{{ request('status') }}"
                        placeholder="Semua Status" :options="[
                            'active' => 'Aktif',
                            'inactive' => 'Nonaktif',
                            'deceased' => 'Meninggal',
                        ]" />
                    <x-healthcare.filter-select name="gender" label="Gender" value="{{ request('gender') }}"
                        placeholder="Semua Gender" :options="[
                            'male' => 'Laki-laki',
                            'female' => 'Perempuan',
                        ]" />
                    <div class="flex items-end">
                        <x-healthcare.button type="primary" type="submit" icon="search" full-width>
                            Cari
                        </x-healthcare.button>
                    </div>
                </div>
            </form>
        </x-slot>

        <x-slot name="actions">
            <div class="flex items-center gap-2">
                <x-healthcare.button type="secondary" href="{{ route('healthcare.dashboard') }}" icon="home">
                    Dashboard
                </x-healthcare.button>
                <x-healthcare.button type="primary" icon="plus"
                    onclick="document.getElementById('modal-add-patient').classList.remove('hidden')">
                    Tambah Pasien
                </x-healthcare.button>
            </div>
        </x-slot>
    </x-healthcare.toolbar>

    {{-- Table / Card View - Responsive --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        {{-- Desktop Table View (hidden on mobile <768px) --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Pasien</th>
                        <th class="px-4 py-3 text-left">No. RM</th>
                        <th class="px-4 py-3 text-left">NIK</th>
                        <th class="px-4 py-3 text-center">Gender</th>
                        <th class="px-4 py-3 text-left">Telepon</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($patients as $patient)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                            </path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $patient->full_name }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-slate-400">
                                            {{ $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->age . ' tahun' : '-' }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="font-mono text-xs bg-gray-100 dark:bg-white/5 px-2 py-1 rounded-lg">{{ $patient->medical_record_number }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-slate-300">
                                {{ $patient->nik ?? '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                @if ($patient->gender === 'male')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">L</span>
                                @elseif($patient->gender === 'female')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-pink-100 text-pink-700 dark:bg-pink-900/30 dark:text-pink-400">P</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-slate-300">
                                {{ $patient->phone ?? '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                @if ($patient->status === 'active')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Aktif</span>
                                @elseif($patient->status === 'inactive')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">Nonaktif</span>
                                @elseif($patient->status === 'deceased')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Meninggal</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('healthcare.patients.show', $patient) }}"
                                        class="p-1.5 text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30 rounded-lg"
                                        title="Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                            </path>
                                        </svg>
                                    </a>
                                    <button onclick="editPatient({{ $patient->id }})"
                                        class="p-1.5 text-amber-600 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-900/30 rounded-lg"
                                        title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-empty-state icon="users" title="Belum ada data pasien"
                                    message="Belum ada data pasien. Klik tombol di atas untuk menambah."
                                    actionText="Tambah Pasien"
                                    actionUrl="{{ route('healthcare.patients.create') }}" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Card View (visible only on mobile <768px) --}}
        <div class="md:hidden divide-y divide-gray-100 dark:divide-white/5">
            @forelse($patients as $patient)
                <div class="p-4 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <div
                                class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-gray-900 dark:text-white truncate">
                                    {{ $patient->full_name }}</p>
                                <p class="text-xs text-gray-500 dark:text-slate-400">
                                    {{ $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->age . ' tahun' : '-' }}
                                </p>
                            </div>
                        </div>
                        @if ($patient->status === 'active')
                            <span
                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 shrink-0">Aktif</span>
                        @elseif($patient->status === 'inactive')
                            <span
                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 shrink-0">Nonaktif</span>
                        @elseif($patient->status === 'deceased')
                            <span
                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 shrink-0">Meninggal</span>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs mb-3">
                        <div>
                            <p class="text-gray-400 dark:text-slate-500">No. RM</p>
                            <p class="font-mono text-gray-700 dark:text-slate-300">
                                {{ $patient->medical_record_number }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400 dark:text-slate-500">Gender</p>
                            <p class="text-gray-700 dark:text-slate-300">
                                @if ($patient->gender === 'male')
                                    <span class="text-blue-600 dark:text-blue-400">Laki-laki</span>
                                @elseif($patient->gender === 'female')
                                    <span class="text-pink-600 dark:text-pink-400">Perempuan</span>
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        @if ($patient->nik)
                            <div class="col-span-2">
                                <p class="text-gray-400 dark:text-slate-500">NIK</p>
                                <p class="text-gray-700 dark:text-slate-300">{{ $patient->nik }}</p>
                            </div>
                        @endif
                        <div class="col-span-2">
                            <p class="text-gray-400 dark:text-slate-500">Telepon</p>
                            <a href="tel:{{ $patient->phone }}"
                                class="text-blue-600 dark:text-blue-400 hover:underline">{{ $patient->phone ?? '-' }}</a>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-2 border-t border-gray-100 dark:border-white/5">
                        <a href="{{ route('healthcare.patients.show', $patient) }}"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                </path>
                            </svg>
                            Detail
                        </a>
                        <button onclick="editPatient({{ $patient->id }})"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-medium text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 hover:bg-amber-100 dark:hover:bg-amber-900/30 rounded-lg transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                            Edit
                        </button>
                    </div>
                </div>
            @empty
                <x-empty-state icon="users" title="Belum ada data pasien"
                    message="Belum ada data pasien. Klik tombol di atas untuk menambah." actionText="Tambah Pasien"
                    actionUrl="{{ route('healthcare.patients.create') }}" />
            @endforelse
        </div>

        {{-- Pagination --}}
        @if ($patients->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-white/10">
                {{ $patients->links() }}
            </div>
        @endif
    </div>

    {{-- Add Patient Modal --}}
    <div id="modal-add-patient"
        class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-white/10">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Tambah Pasien Baru</h3>
                <button onclick="document.getElementById('modal-add-patient').classList.add('hidden')"
                    class="p-2 hover:bg-gray-100 dark:hover:bg-white/10 rounded-xl">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form action="{{ route('healthcare.patients.store') }}" method="POST" x-data="{ loading: false }"
                @submit="loading = true" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Nama Lengkap
                            *</label>
                        <input type="text" name="full_name" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">NIK</label>
                        <input type="text" name="nik"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Tanggal Lahir
                            *</label>
                        <input type="date" name="date_of_birth" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Gender
                            *</label>
                        <select name="gender" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih Gender</option>
                            <option value="male">Laki-laki</option>
                            <option value="female">Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Telepon</label>
                        <input type="tel" name="phone"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Email</label>
                        <input type="email" name="email"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Alamat</label>
                        <textarea name="address" rows="3"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button"
                        onclick="document.getElementById('modal-add-patient').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Batal</button>
                    <button type="submit" :disabled="loading"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center">
                        <template x-if="loading">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </template>
                        <span x-text="loading ? 'Memproses...' : 'Simpan'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
