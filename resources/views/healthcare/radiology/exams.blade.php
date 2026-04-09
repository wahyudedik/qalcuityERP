<x-app-layout>
    <x-slot name="header">Pemeriksaan Radiologi</x-slot>

    {{-- Breadcrumbs --}}
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Radiologi', 'url' => route('healthcare.radiology.index')],
        ['label' => 'Pemeriksaan'],
    ]" />

    {{-- Stats - Data from Controller (no more queries in Blade) --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Exam</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                {{ number_format($statistics['total_exams'] ?? 0) }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Terjadwal Hari Ini</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ $statistics['scheduled_today'] ?? 0 }}
            </p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Selesai Hari Ini</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">
                {{ $statistics['completed_today'] ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Pending Report</p>
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">
                {{ $statistics['pending_reports'] ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Urgent</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">{{ $statistics['urgent_exams'] ?? 0 }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 mb-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-2 flex-1">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari pasien / No. exam..."
                    class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="exam_type"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    <option value="">Semua Jenis</option>
                    <option value="xray" @selected(request('exam_type') === 'xray')>X-Ray</option>
                    <option value="ct_scan" @selected(request('exam_type') === 'ct_scan')>CT Scan</option>
                    <option value="mri" @selected(request('exam_type') === 'mri')>MRI</option>
                    <option value="ultrasound" @selected(request('exam_type') === 'ultrasound')>Ultrasound</option>
                    <option value="fluoroscopy" @selected(request('exam_type') === 'fluoroscopy')>Fluoroscopy</option>
                </select>
                <select name="status"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    <option value="">Semua Status</option>
                    <option value="scheduled" @selected(request('status') === 'scheduled')>Scheduled</option>
                    <option value="in_progress" @selected(request('status') === 'in_progress')>In Progress</option>
                    <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                </select>
                <button type="submit"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
            </form>
        </div>
    </div>

    {{-- Table / Card View - Responsive --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        {{-- Desktop Table View (hidden on mobile <768px) --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">No. Exam</th>
                        <th class="px-4 py-3 text-left">Pasien</th>
                        <th class="px-4 py-3 text-left">Jenis Exam</th>
                        <th class="px-4 py-3 text-left">Body Part</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-center">Priority</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($exams ?? [] as $exam)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="px-4 py-3">
                                <span
                                    class="font-mono text-sm font-bold text-blue-600 dark:text-blue-400">{{ $exam->exam_number ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900 dark:text-white">
                                    {{ $exam->patient ? $exam->patient->full_name : '-' }}</p>
                                <p class="text-xs text-gray-500 dark:text-slate-400">
                                    {{ $exam->patient ? $exam->patient->medical_record_number : '-' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                @if ($exam->exam_type === 'xray')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">X-Ray</span>
                                @elseif($exam->exam_type === 'ct_scan')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">CT
                                        Scan</span>
                                @elseif($exam->exam_type === 'mri')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400">MRI</span>
                                @elseif($exam->exam_type === 'ultrasound')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Ultrasound</span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">{{ ucfirst($exam->exam_type ?? '-') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-slate-300">
                                {{ $exam->body_part ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <p class="text-gray-900 dark:text-white">
                                    {{ $exam->exam_date ? \Carbon\Carbon::parse($exam->exam_date)->format('d M Y') : '-' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-slate-400">
                                    {{ $exam->exam_date ? \Carbon\Carbon::parse($exam->exam_date)->format('H:i') : '-' }}
                                </p>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($exam->priority === 'urgent')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Urgent</span>
                                @elseif($exam->priority === 'high')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400">High</span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">Normal</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($exam->status === 'scheduled')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">Scheduled</span>
                                @elseif($exam->status === 'in_progress')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">In
                                        Progress</span>
                                @elseif($exam->status === 'completed')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Completed</span>
                                @elseif($exam->status === 'cancelled')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Cancelled</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('healthcare.radiology.exams.show', $exam) }}"
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
                                    @if ($exam->status === 'completed')
                                        <a href="{{ route('healthcare.radiology.reports.create', $exam) }}"
                                            class="p-1.5 text-green-600 hover:bg-green-50 dark:text-green-400 dark:hover:bg-green-900/30 rounded-lg"
                                            title="Buat Laporan">
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
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-slate-400">
                                <p>Belum ada pemeriksaan radiologi</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Card View (visible only on mobile <768px) --}}
        <div class="md:hidden divide-y divide-gray-100 dark:divide-white/5">
            @forelse($exams ?? [] as $exam)
                <div class="p-4 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="flex-1 min-w-0">
                            <p class="font-mono text-sm font-bold text-blue-600 dark:text-blue-400">
                                {{ $exam->exam_number ?? '-' }}</p>
                            <p class="font-semibold text-gray-900 dark:text-white truncate mt-0.5">
                                {{ $exam->patient ? $exam->patient->full_name : '-' }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-slate-400">
                                {{ $exam->patient ? $exam->patient->medical_record_number : '-' }}
                            </p>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            @if ($exam->status === 'scheduled')
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 shrink-0">Scheduled</span>
                            @elseif($exam->status === 'in_progress')
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 shrink-0">In
                                    Progress</span>
                            @elseif($exam->status === 'completed')
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 shrink-0">Completed</span>
                            @elseif($exam->status === 'cancelled')
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 shrink-0">Cancelled</span>
                            @endif
                            @if ($exam->priority === 'urgent')
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 shrink-0">Urgent</span>
                            @elseif($exam->priority === 'high')
                                <span
                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400 shrink-0">High</span>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs mb-3">
                        <div>
                            <p class="text-gray-400 dark:text-slate-500">Jenis Exam</p>
                            <p class="text-gray-700 dark:text-slate-300 font-medium">
                                @if ($exam->exam_type === 'xray')
                                    <span class="text-blue-600 dark:text-blue-400">X-Ray</span>
                                @elseif($exam->exam_type === 'ct_scan')
                                    <span class="text-purple-600 dark:text-purple-400">CT Scan</span>
                                @elseif($exam->exam_type === 'mri')
                                    <span class="text-indigo-600 dark:text-indigo-400">MRI</span>
                                @elseif($exam->exam_type === 'ultrasound')
                                    <span class="text-green-600 dark:text-green-400">Ultrasound</span>
                                @else
                                    {{ ucfirst($exam->exam_type ?? '-') }}
                                @endif
                            </p>
                        </div>
                        @if ($exam->body_part)
                            <div>
                                <p class="text-gray-400 dark:text-slate-500">Body Part</p>
                                <p class="text-gray-700 dark:text-slate-300">{{ $exam->body_part }}</p>
                            </div>
                        @endif
                        <div class="col-span-2">
                            <p class="text-gray-400 dark:text-slate-500">Tanggal</p>
                            <p class="text-gray-700 dark:text-slate-300">
                                {{ $exam->exam_date ? \Carbon\Carbon::parse($exam->exam_date)->format('d M Y, H:i') : '-' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-2 border-t border-gray-100 dark:border-white/5">
                        <a href="{{ route('healthcare.radiology.exams.show', $exam) }}"
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
                        @if ($exam->status === 'completed')
                            <a href="{{ route('healthcare.radiology.reports.create', $exam) }}"
                                class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-medium text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30 rounded-lg transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                Laporan
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-slate-600 mb-4" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    <p class="text-gray-500 dark:text-slate-400">Belum ada pemeriksaan radiologi</p>
                    <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">Klik tombol "+ Exam" untuk membuat
                        pemeriksaan baru</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if (isset($exams) && $exams->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-white/10">
                {{ $exams->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
