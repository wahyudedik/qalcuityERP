<x-app-layout>
    <x-slot name="header">Triage Instalasi Gawat Darurat</x-slot>

    @php $tid = auth()->user()->tenant_id; @endphp

    {{-- Triage Queue Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        @php
            $awaitingTriage = \App\Models\EmergencyCase::where('tenant_id', $tid)
                ->where('triage_status', 'pending')
                ->count();
            $triaged = \App\Models\EmergencyCase::where('tenant_id', $tid)
                ->where('triage_status', 'completed')
                ->where('status', 'active')
                ->count();
            $redCases = \App\Models\EmergencyCase::where('tenant_id', $tid)
                ->where('triage_level', 'red')
                ->where('status', 'active')
                ->count();
            $avgTriageTime =
                \App\Models\EmergencyCase::where('tenant_id', $tid)
                    ->where('triage_status', 'completed')
                    ->avg('triage_duration_minutes') ?? 0;
        @endphp
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Menunggu Triage</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $awaitingTriage }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Sudah Triage</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ $triaged }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border-2 border-red-200">
            <p class="text-xs text-red-600 font-semibold">Prioritas Merah</p>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ $redCases }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Rata-rata Triage (mnt)</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ round($avgTriageTime) }}</p>
        </div>
    </div>

    {{-- Pending Triage Cases --}}
    <div
        class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900">Menunggu Penilaian Triage</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Pasien</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Keluhan Utama</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Waktu Tiba</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Menunggu (mnt)</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pendingCases ?? [] as $case)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-9 h-9 rounded-xl bg-red-100 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4 text-red-600" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                            </path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">
                                            {{ $case->patient ? $case->patient?->full_name : '-' }}</p>
                                        <p class="text-xs text-gray-500">
                                            {{ $case->patient ? $case->patient?->medical_record_number : '-' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <p class="text-gray-700">
                                    {{ Str::limit($case->chief_complaint, 50) }}</p>
                            </td>
                            <td class="px-4 py-3 hidden lg:table-cell">
                                <p class="text-gray-900">
                                    {{ $case->arrival_time ? \Carbon\Carbon::parse($case->arrival_time)->format('H:i') : '-' }}
                                </p>
                            </td>
                            <td class="px-4 py-3 text-center hidden sm:table-cell">
                                @php
                                    $waitMinutes = $case->arrival_time
                                        ? \Carbon\Carbon::parse($case->arrival_time)->diffInMinutes(now())
                                        : 0;
                                @endphp
                                <span
                                    class="font-bold @if ($waitMinutes > 15) text-red-600 @else text-gray-900 @endif">
                                    {{ $waitMinutes }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('healthcare.er.triage.assess', $case) }}"
                                    class="px-4 py-2 text-sm bg-red-600 text-white rounded-xl hover:bg-red-700 font-medium">
                                    Mulai Triage
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                <p>Tidak ada pasien menunggu triage</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recently Triaged --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900">Baru Saja Ditriage</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Pasien</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Keluhan Utama</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Level Triage</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Waktu Triage</th>
                        <th class="px-4 py-3 text-center hidden lg:table-cell">Durasi (mnt)</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($recentTriaged ?? [] as $case)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900">
                                    {{ $case->patient ? $case->patient?->full_name : '-' }}</p>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <p class="text-gray-700">
                                    {{ Str::limit($case->chief_complaint, 40) }}</p>
                            </td>
                            <td class="px-4 py-3 text-center hidden sm:table-cell">
                                @if ($case->triage_level === 'red')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-bold rounded-lg bg-red-100 text-red-700">🔴
                                        Red</span>
                                @elseif($case->triage_level === 'yellow')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-bold rounded-lg bg-amber-100 text-amber-700">🟠
                                        Yellow</span>
                                @elseif($case->triage_level === 'green')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-bold rounded-lg bg-green-100 text-green-700">🟢
                                        Green</span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-bold rounded-lg bg-gray-100 text-gray-700">⚫
                                        Black</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 hidden lg:table-cell">
                                <p class="text-gray-900">
                                    {{ $case->triage_time ? \Carbon\Carbon::parse($case->triage_time)->format('H:i') : '-' }}
                                </p>
                            </td>
                            <td class="px-4 py-3 text-center hidden lg:table-cell">
                                <span
                                    class="font-bold text-gray-900">{{ $case->triage_duration_minutes ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('healthcare.er.triage.assess', $case) }}"
                                    class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg"
                                    title="Lihat Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                        </path>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                <p>Belum ada pasien yang ditriage</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
