<x-app-layout>
    <x-slot name="header">Janji Temu</x-slot>

    {{-- Breadcrumbs --}}
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Janji Temu'],
    ]" />

    {{-- Stats - Data from Controller (no more queries in Blade) --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Total Janji Temu</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">
                {{ number_format($statistics['total_appointments']) }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Hari Ini</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $statistics['today'] }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Terjadwal</p>
            <p class="text-2xl font-bold text-purple-600 mt-1">{{ $statistics['scheduled'] }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Selesai</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ $statistics['completed'] }}</p>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="bg-white rounded-2xl border border-gray-200 mb-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-2 flex-1">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari pasien / dokter..."
                    class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                <select name="status"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    <option value="">Semua Status</option>
                    <option value="scheduled" @selected(request('status') === 'scheduled')>Terjadwal</option>
                    <option value="completed" @selected(request('status') === 'completed')>Selesai</option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>Dibatalkan</option>
                    <option value="no_show" @selected(request('status') === 'no_show')>Tidak Hadir</option>
                </select>
                <button type="submit"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
            </form>
            <div class="flex gap-2">
                <a href="{{ route('healthcare.appointments.book') }}"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Janji Temu</a>
            </div>
        </div>
    </div>

    {{-- View Toggle --}}
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <button onclick="switchView('list')" id="view-list"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl">
                List
            </button>
            <button onclick="switchView('calendar')" id="view-calendar"
                class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                Kalender
            </button>
        </div>
        <p class="text-sm text-gray-500">
            Menampilkan {{ $appointments->count() }} dari {{ $appointments->total() }} janji temu
        </p>
    </div>

    {{-- List View - Responsive Table/Card --}}
    <div id="content-list" class="view-content">
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            {{-- Desktop Table View (hidden on mobile <768px) --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Pasien</th>
                            <th class="px-4 py-3 text-left">Dokter</th>
                            <th class="px-4 py-3 text-left">Tanggal & Waktu</th>
                            <th class="px-4 py-3 text-left">Layanan</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($appointments as $appointment)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                                </path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">
                                                {{ $appointment->patient ? $appointment->patient?->full_name : '-' }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{ $appointment->patient ? $appointment->patient?->medical_record_number : '-' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="text-gray-900">
                                        {{ $appointment->doctor ? $appointment->doctor?->name : '-' }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ $appointment->doctor ? $appointment->doctor?->specialization : '' }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="text-gray-900">
                                        {{ $appointment->appointment_date ? \Carbon\Carbon::parse($appointment->appointment_date)->format('d M Y') : '-' }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $appointment->appointment_date ? \Carbon\Carbon::parse($appointment->appointment_date)->format('H:i') : '-' }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-purple-100 text-purple-700">
                                        {{ $appointment->service_type ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($appointment->status === 'scheduled')
                                        <span
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700">Terjadwal</span>
                                    @elseif($appointment->status === 'completed')
                                        <span
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700">Selesai</span>
                                    @elseif($appointment->status === 'cancelled')
                                        <span
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700">Dibatalkan</span>
                                    @elseif($appointment->status === 'no_show')
                                        <span
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700">Tidak
                                            Hadir</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('healthcare.appointments.show', $appointment) }}"
                                            class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg" title="Detail">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="1.5"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                </path>
                                            </svg>
                                        </a>
                                        @if ($appointment->status === 'scheduled')
                                            <button onclick="completeAppointment({{ $appointment->id }})"
                                                class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg"
                                                title="Selesai">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.5" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </button>
                                            <button onclick="cancelAppointment({{ $appointment->id }})"
                                                class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg"
                                                title="Batalkan">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.5" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                    <p>Belum ada janji temu</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile Card View (visible only on mobile <768px) --}}
            <div class="md:hidden divide-y divide-gray-100">
                @forelse($appointments as $appointment)
                    <div class="p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                <div
                                    class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-gray-900 truncate">
                                        {{ $appointment->patient ? $appointment->patient?->full_name : '-' }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $appointment->patient ? $appointment->patient?->medical_record_number : '-' }}
                                    </p>
                                </div>
                            </div>
                            @if ($appointment->status === 'scheduled')
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700 shrink-0">Terjadwal</span>
                            @elseif($appointment->status === 'completed')
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700 shrink-0">Selesai</span>
                            @elseif($appointment->status === 'cancelled')
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700 shrink-0">Dibatalkan</span>
                            @elseif($appointment->status === 'no_show')
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700 shrink-0">Tidak
                                    Hadir</span>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs mb-3">
                            <div class="col-span-2">
                                <p class="text-gray-400">Dokter</p>
                                <p class="text-gray-700">
                                    {{ $appointment->doctor ? $appointment->doctor?->name : '-' }}
                                    @if ($appointment->doctor && $appointment->doctor?->specialization)
                                        <span class="text-gray-400">-
                                            {{ $appointment->doctor?->specialization }}</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-gray-400">Tanggal & Waktu</p>
                                <p class="text-gray-700">
                                    {{ $appointment->appointment_date ? \Carbon\Carbon::parse($appointment->appointment_date)->format('d M Y, H:i') : '-' }}
                                </p>
                            </div>
                            @if ($appointment->service_type)
                                <div class="col-span-2">
                                    <p class="text-gray-400">Layanan</p>
                                    <p class="text-purple-600 font-medium">
                                        {{ $appointment->service_type }}</p>
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 pt-2 border-t border-gray-100">
                            <a href="{{ route('healthcare.appointments.show', $appointment) }}"
                                class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                    </path>
                                </svg>
                                Detail
                            </a>
                            @if ($appointment->status === 'scheduled')
                                <button onclick="completeAppointment({{ $appointment->id }})"
                                    class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-medium text-green-600 bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Selesai
                                </button>
                                <button onclick="cancelAppointment({{ $appointment->id }})"
                                    class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Batal
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center">
                        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                        <p class="text-gray-500">Belum ada janji temu</p>
                        <p class="text-xs text-gray-400 mt-1">Klik tombol "+ Janji Temu" untuk
                            menjadwalkan</p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if ($appointments->hasPages())
                <div class="px-4 py-3 border-t border-gray-200">
                    {{ $appointments->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Calendar View (Placeholder) --}}
    <div id="content-calendar" class="view-content hidden">
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                    </path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Tampilan Kalender</h3>
                <p class="text-sm text-gray-500">Fitur tampilan kalender akan segera tersedia</p>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function switchView(viewName) {
                // Hide all views
                document.querySelectorAll('.view-content').forEach(el => el.classList.add('hidden'));
                // Reset button styles
                document.getElementById('view-list').className =
                    'px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50';
                document.getElementById('view-calendar').className =
                    'px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50';

                // Show selected view
                document.getElementById('content-' + viewName).classList.remove('hidden');
                // Activate button
                document.getElementById('view-' + viewName).className = 'px-4 py-2 text-sm bg-blue-600 text-white rounded-xl';
            }

            async function completeAppointment(id) {
                const confirmed = await Dialog.confirm('Tandai janji temu ini sebagai selesai?');
                if (!confirmed) return;
                // Add your AJAX call or form submission here
                Dialog.alert('Fitur ini akan menghubungkan ke controller');
            }

            async function cancelAppointment(id) {
                const confirmed = await Dialog.danger('Batalkan janji temu ini?');
                if (!confirmed) return;
                // Add your AJAX call or form submission here
                Dialog.alert('Fitur ini akan menghubungkan ke controller');
            }
        </script>
    @endpush
</x-app-layout>
