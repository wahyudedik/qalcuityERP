<x-app-layout>
    <x-slot name="header">Dashboard Instalasi Gawat Darurat (IGD)</x-slot>

    @php $tid = auth()->user()->tenant_id; @endphp

    {{-- Critical Alerts Banner --}}
    @if (($criticalPatients ?? 0) > 0)
        <div class="bg-red-500 text-white px-6 py-4 rounded-2xl mb-6 flex items-center justify-between animate-pulse">
            <div class="flex items-center gap-3">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                    </path>
                </svg>
                <div>
                    <p class="text-lg font-bold">PERHATIAN: {{ $criticalPatients }} Pasien Kritis</p>
                    <p class="text-sm text-white/80">Memerlukan penanganan segera</p>
                </div>
            </div>
            <a href="{{ route('healthcare.er.triage', ['priority' => 'red']) }}"
                class="px-4 py-2 bg-white text-red-600 rounded-xl font-medium hover:bg-white/90">
                Lihat Detail
            </a>
        </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-4 mb-6">
        @php
            $totalERPatients = \App\Models\EmergencyCase::where('tenant_id', $tid)->where('status', 'active')->count();
            $redPriority = \App\Models\EmergencyCase::where('tenant_id', $tid)
                ->where('triage_level', 'red')
                ->where('status', 'active')
                ->count();
            $yellowPriority = \App\Models\EmergencyCase::where('tenant_id', $tid)
                ->where('triage_level', 'yellow')
                ->where('status', 'active')
                ->count();
            $greenPriority = \App\Models\EmergencyCase::where('tenant_id', $tid)
                ->where('triage_level', 'green')
                ->where('status', 'active')
                ->count();
            $blackPriority = \App\Models\EmergencyCase::where('tenant_id', $tid)
                ->where('triage_level', 'black')
                ->where('status', 'active')
                ->count();
            $avgStayTime =
                \App\Models\EmergencyCase::where('tenant_id', $tid)
                    ->where('status', 'discharged')
                    ->avg('stay_duration_minutes') ?? 0;
            $todayThroughput = \App\Models\EmergencyCase::where('tenant_id', $tid)
                ->whereDate('created_at', today())
                ->count();
        @endphp
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Pasien IGD</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalERPatients }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border-2 border-red-200 dark:border-red-800">
            <p class="text-xs text-red-600 dark:text-red-400 font-semibold">🔴 Resusitasi</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">{{ $redPriority }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border-2 border-amber-200 dark:border-amber-800">
            <p class="text-xs text-amber-600 dark:text-amber-400 font-semibold">🟠 Emergent</p>
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $yellowPriority }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border-2 border-green-200 dark:border-green-800">
            <p class="text-xs text-green-600 dark:text-green-400 font-semibold">🟢 Urgent</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $greenPriority }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Non-Urgent</p>
            <p class="text-2xl font-bold text-gray-600 dark:text-gray-400 mt-1">{{ $blackPriority }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Rata-rata Lama (mnt)</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ round($avgStayTime) }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Throughput Hari Ini</p>
            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-1">{{ $todayThroughput }}</p>
        </div>
    </div>

    {{-- Triage Priority Legend --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4 mb-6">
        <div class="flex flex-wrap items-center gap-4 text-sm">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-red-500"></div>
                <span class="text-gray-700 dark:text-slate-300 font-medium">Red - Resusitasi (Immediate)</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-amber-500"></div>
                <span class="text-gray-700 dark:text-slate-300 font-medium">Yellow - Emergent (< 15 min)</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-green-500"></div>
                <span class="text-gray-700 dark:text-slate-300 font-medium">Green - Urgent (< 60 min)</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-gray-400"></div>
                <span class="text-gray-700 dark:text-slate-300 font-medium">Black - Non-Urgent</span>
            </div>
        </div>
    </div>

    {{-- ER Patients by Priority --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Critical Patients (Red) --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border-2 border-red-200 dark:border-red-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-red-600 dark:text-red-400">🔴 Resusitasi - Immediate</h3>
                    <a href="{{ route('healthcare.er.triage', ['priority' => 'red']) }}"
                        class="text-sm text-red-600 dark:text-red-400 hover:underline">Lihat Semua</a>
                </div>
            </div>
            <div class="p-4 space-y-3">
                @forelse($criticalCases ?? [] as $case)
                    <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <p class="font-bold text-gray-900 dark:text-white">
                                    {{ $case->patient ? $case->patient->full_name : '-' }}</p>
                                <p class="text-sm text-gray-600 dark:text-slate-400">
                                    {{ $case->chief_complaint ?? '-' }}</p>
                            </div>
                            <span class="px-2 py-1 text-xs font-bold bg-red-500 text-white rounded-lg">ESI-1</span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-slate-400">
                            <span>Sejak
                                {{ $case->arrival_time ? \Carbon\Carbon::parse($case->arrival_time)->format('H:i') : '-' }}</span>
                            <a href="{{ route('healthcare.er.triage.assess', $case) }}"
                                class="px-3 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700">Tangani</a>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-500 dark:text-slate-400 py-4">Tidak ada pasien kritis</p>
                @endforelse
            </div>
        </div>

        {{-- Emergent Patients (Yellow) --}}
        <div
            class="bg-white dark:bg-[#1e293b] rounded-2xl border-2 border-amber-200 dark:border-amber-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-amber-600 dark:text-amber-400">🟠 Emergent - < 15 menit</h3>
                            <a href="{{ route('healthcare.er.triage', ['priority' => 'yellow']) }}"
                                class="text-sm text-amber-600 dark:text-amber-400 hover:underline">Lihat Semua</a>
                </div>
            </div>
            <div class="p-4 space-y-3">
                @forelse($emergentCases ?? [] as $case)
                    <div
                        class="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-200 dark:border-amber-800">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <p class="font-bold text-gray-900 dark:text-white">
                                    {{ $case->patient ? $case->patient->full_name : '-' }}</p>
                                <p class="text-sm text-gray-600 dark:text-slate-400">
                                    {{ $case->chief_complaint ?? '-' }}</p>
                            </div>
                            <span class="px-2 py-1 text-xs font-bold bg-amber-500 text-white rounded-lg">ESI-2</span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-slate-400">
                            <span>Sejak
                                {{ $case->arrival_time ? \Carbon\Carbon::parse($case->arrival_time)->format('H:i') : '-' }}</span>
                            <a href="{{ route('healthcare.er.triage.assess', $case) }}"
                                class="px-3 py-1 bg-amber-600 text-white rounded-lg hover:bg-amber-700">Tangani</a>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-500 dark:text-slate-400 py-4">Tidak ada pasien emergent</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Recent ER Admissions Table --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div
            class="px-6 py-4 border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Pemasukan IGD Terbaru</h3>
            <a href="{{ route('healthcare.er.triage') }}"
                class="px-4 py-2 text-sm bg-red-600 text-white rounded-xl hover:bg-red-700">+ Triage Baru</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Pasien</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Keluhan Utama</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Triage</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Waktu Tiba</th>
                        <th class="px-4 py-3 text-center hidden lg:table-cell">Lama (mnt)</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($recentCases ?? [] as $case)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900 dark:text-white">
                                    {{ $case->patient ? $case->patient->full_name : '-' }}</p>
                                <p class="text-xs text-gray-500 dark:text-slate-400">
                                    {{ $case->patient ? $case->patient->medical_record_number : '-' }}</p>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <p class="text-gray-700 dark:text-slate-300">
                                    {{ Str::limit($case->chief_complaint, 50) }}</p>
                            </td>
                            <td class="px-4 py-3 text-center hidden sm:table-cell">
                                @if ($case->triage_level === 'red')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-bold rounded-lg bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">🔴
                                        Red</span>
                                @elseif($case->triage_level === 'yellow')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-bold rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">🟠
                                        Yellow</span>
                                @elseif($case->triage_level === 'green')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-bold rounded-lg bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">🟢
                                        Green</span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-bold rounded-lg bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">⚫
                                        Black</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-left hidden lg:table-cell">
                                <p class="text-gray-900 dark:text-white">
                                    {{ $case->arrival_time ? \Carbon\Carbon::parse($case->arrival_time)->format('d M Y') : '-' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-slate-400">
                                    {{ $case->arrival_time ? \Carbon\Carbon::parse($case->arrival_time)->format('H:i') : '-' }}
                                </p>
                            </td>
                            <td class="px-4 py-3 text-center hidden lg:table-cell">
                                <span
                                    class="font-bold text-gray-900 dark:text-white">{{ $case->stay_duration_minutes ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('healthcare.er.triage.assess', $case) }}"
                                        class="p-1.5 text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30 rounded-lg"
                                        title="Assessment">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-slate-400">
                                <p>Belum ada pasien IGD</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
