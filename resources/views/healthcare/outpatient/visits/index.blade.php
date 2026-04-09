<x-app-layout>
    <x-slot name="header">Kunjungan Rawat Jalan</x-slot>

    @php $tid = auth()->user()->tenant_id; @endphp

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        @php
            $totalVisits = \App\Models\OutpatientVisit::where('tenant_id', $tid)->count();
            $todayVisits = \App\Models\OutpatientVisit::where('tenant_id', $tid)
                ->whereDate('visit_date', today())
                ->count();
            $activeVisits = \App\Models\OutpatientVisit::where('tenant_id', $tid)
                ->where('status', 'in_progress')
                ->count();
            $completedVisits = \App\Models\OutpatientVisit::where('tenant_id', $tid)
                ->where('status', 'completed')
                ->count();
        @endphp
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Kunjungan</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($totalVisits) }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Kunjungan Hari Ini</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ $todayVisits }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Sedang Berlangsung</p>
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $activeVisits }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Selesai</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $completedVisits }}</p>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 mb-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-2 flex-1">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari pasien / No. RM..."
                    class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                <select name="status"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    <option value="">Semua Status</option>
                    <option value="registered" @selected(request('status') === 'registered')>Terdaftar</option>
                    <option value="in_progress" @selected(request('status') === 'in_progress')>Berlangsung</option>
                    <option value="completed" @selected(request('status') === 'completed')>Selesai</option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>Dibatalkan</option>
                </select>
                <button type="submit"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
            </form>
            <div class="flex gap-2">
                <a href="{{ route('healthcare.outpatient.visits.create') }}"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Kunjungan</a>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Pasien</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Dokter</th>
                        <th class="px-4 py-3 text-left">Tanggal Kunjungan</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Keperluan</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($visits ?? [] as $visit)
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
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ $visit->patient ? $visit->patient->full_name : '-' }}</p>
                                        <p class="text-xs text-gray-500 dark:text-slate-400">
                                            {{ $visit->patient ? $visit->patient->medical_record_number : '-' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <p class="text-gray-900 dark:text-white">
                                    {{ $visit->doctor ? $visit->doctor->name : '-' }}</p>
                                <p class="text-xs text-gray-500 dark:text-slate-400">
                                    {{ $visit->doctor ? $visit->doctor->specialization : '' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-gray-900 dark:text-white">
                                    {{ $visit->visit_date ? \Carbon\Carbon::parse($visit->visit_date)->format('d M Y') : '-' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-slate-400">
                                    {{ $visit->visit_date ? \Carbon\Carbon::parse($visit->visit_date)->format('H:i') : '-' }}
                                </p>
                            </td>
                            <td class="px-4 py-3 hidden lg:table-cell">
                                <p class="text-gray-700 dark:text-slate-300">
                                    {{ Str::limit($visit->purpose ?? '-', 40) }}</p>
                            </td>
                            <td class="px-4 py-3 text-center hidden sm:table-cell">
                                @if ($visit->status === 'registered')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">Terdaftar</span>
                                @elseif($visit->status === 'in_progress')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Berlangsung</span>
                                @elseif($visit->status === 'completed')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Selesai</span>
                                @elseif($visit->status === 'cancelled')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Dibatalkan</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('healthcare.emr.show', $visit) }}"
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
                                    @if ($visit->status === 'registered' || $visit->status === 'in_progress')
                                        <a href="{{ route('healthcare.emr.show', $visit) }}"
                                            class="p-1.5 text-green-600 hover:bg-green-50 dark:text-green-400 dark:hover:bg-green-900/30 rounded-lg"
                                            title="Mulai Konsultasi">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="1.5"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                </path>
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-slate-400">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-slate-600" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                    </path>
                                </svg>
                                <p>Belum ada kunjungan rawat jalan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if (isset($visits) && $visits->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-white/10">
                {{ $visits->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
