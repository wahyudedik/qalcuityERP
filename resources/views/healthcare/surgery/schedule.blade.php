<x-app-layout>
    <x-slot name="header">Jadwal Operasi</x-slot>

    @php $tid = auth()->user()->tenant_id; @endphp

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-4 mb-6">
        @php
            $totalSurgeries = \App\Models\SurgerySchedule::where('tenant_id', $tid)->count();
            $scheduledToday = \App\Models\SurgerySchedule::where('tenant_id', $tid)
                ->whereDate('surgery_date', today())
                ->count();
            $inProgressSurgeries = \App\Models\SurgerySchedule::where('tenant_id', $tid)
                ->where('status', 'in_progress')
                ->count();
            $completedToday = \App\Models\SurgerySchedule::where('tenant_id', $tid)
                ->where('status', 'completed')
                ->whereDate('completed_at', today())
                ->count();
            $pendingSurgeries = \App\Models\SurgerySchedule::where('tenant_id', $tid)
                ->where('status', 'scheduled')
                ->count();
        @endphp
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Operasi</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($totalSurgeries) }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Terjadwal Hari Ini</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ $scheduledToday }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Berlangsung</p>
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $inProgressSurgeries }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Selesai Hari Ini</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $completedToday }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Menunggu</p>
            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-1">{{ $pendingSurgeries }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 mb-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-2 flex-1">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari pasien / dokter..."
                    class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="status"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    <option value="">Semua Status</option>
                    <option value="scheduled" @selected(request('status') === 'scheduled')>Scheduled</option>
                    <option value="in_progress" @selected(request('status') === 'in_progress')>In Progress</option>
                    <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                </select>
                <input type="date" name="date" value="{{ request('date') }}"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                <button type="submit"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
            </form>
        </div>
    </div>

    {{-- Surgery Schedule Table --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">ID</th>
                        <th class="px-4 py-3 text-left">Pasien</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Prosedur</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Dokter</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Tanggal</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Ruang Operasi</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($schedules ?? [] as $schedule)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="px-4 py-3">
                                <span
                                    class="font-mono text-sm font-bold text-blue-600 dark:text-blue-400">{{ $schedule->schedule_number ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900 dark:text-white">
                                    {{ $schedule->patient ? $schedule->patient->full_name : '-' }}</p>
                                <p class="text-xs text-gray-500 dark:text-slate-400">
                                    {{ $schedule->patient ? $schedule->patient->medical_record_number : '-' }}</p>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <p class="text-gray-900 dark:text-white">{{ $schedule->procedure_name ?? '-' }}</p>
                                <p class="text-xs text-gray-500 dark:text-slate-400">
                                    {{ $schedule->procedure_type ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-slate-300 hidden lg:table-cell">
                                <p>{{ $schedule->surgeon ? $schedule->surgeon->name : '-' }}</p>
                                <p class="text-xs text-gray-500 dark:text-slate-400">
                                    {{ $schedule->anesthesiologist ? 'Anest: ' . $schedule->anesthesiologist->name : '' }}
                                </p>
                            </td>
                            <td class="px-4 py-3 hidden sm:table-cell">
                                <p class="text-gray-900 dark:text-white">
                                    {{ $schedule->surgery_date ? \Carbon\Carbon::parse($schedule->surgery_date)->format('d M Y') : '-' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-slate-400">
                                    {{ $schedule->surgery_date ? \Carbon\Carbon::parse($schedule->surgery_date)->format('H:i') : '-' }}
                                </p>
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-slate-300 hidden lg:table-cell">
                                {{ $schedule->operating_room ?? '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                @if ($schedule->status === 'scheduled')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">Scheduled</span>
                                @elseif($schedule->status === 'in_progress')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">In
                                        Progress</span>
                                @elseif($schedule->status === 'completed')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Completed</span>
                                @elseif($schedule->status === 'cancelled')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Cancelled</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('healthcare.surgery.schedule.show', $schedule) }}"
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
                                    @if ($schedule->status === 'scheduled')
                                        <a href="{{ route('healthcare.surgery.operations.start', $schedule) }}"
                                            class="p-1.5 text-green-600 hover:bg-green-50 dark:text-green-400 dark:hover:bg-green-900/30 rounded-lg"
                                            title="Mulai Operasi">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z">
                                                </path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-slate-400">
                                <p>Belum ada jadwal operasi</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if (isset($schedules) && $schedules->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-white/10">
                {{ $schedules->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
