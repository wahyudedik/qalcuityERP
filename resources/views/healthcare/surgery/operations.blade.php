<x-app-layout>
    <x-slot name="header">Log Operasi</x-slot>

    @php $tid = auth()->user()->tenant_id; @endphp

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        @php
            $totalOperations = \App\Models\OperationLog::where('tenant_id', $tid)->count();
            $completedOperations = \App\Models\OperationLog::where('tenant_id', $tid)
                ->where('status', 'completed')
                ->count();
            $avgDuration = \App\Models\OperationLog::where('tenant_id', $tid)
                ->whereNotNull('duration_minutes')
                ->avg('duration_minutes');
            $complicationRate =
                $totalOperations > 0
                    ? (\App\Models\OperationLog::where('tenant_id', $tid)->where('has_complications', true)->count() /
                            $totalOperations) *
                        100
                    : 0;
        @endphp
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Operasi</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($totalOperations) }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Selesai</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $completedOperations }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Durasi Rata-rata</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">
                {{ $avgDuration ? round($avgDuration) . ' min' : '-' }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Komplikasi</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">{{ number_format($complicationRate, 1) }}%
            </p>
        </div>
    </div>

    {{-- Operations Table --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Pasien</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Prosedur</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Dokter</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Tanggal</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Durasi</th>
                        <th class="px-4 py-3 text-center hidden lg:table-cell">Komplikasi</th>
                        <th class="px-4 py-3 text-center">Outcome</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($operations ?? [] as $operation)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900 dark:text-white">
                                    {{ $operation->patient ? $operation->patient->full_name : '-' }}</p>
                                <p class="text-xs text-gray-500 dark:text-slate-400">
                                    {{ $operation->patient ? $operation->patient->medical_record_number : '-' }}</p>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <p class="text-gray-900 dark:text-white">{{ $operation->procedure_name ?? '-' }}</p>
                                <p class="text-xs text-gray-500 dark:text-slate-400">
                                    {{ $operation->surgery_type ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-slate-300 hidden lg:table-cell">
                                {{ $operation->surgeon ? $operation->surgeon->name : '-' }}</td>
                            <td class="px-4 py-3 hidden sm:table-cell">
                                <p class="text-gray-900 dark:text-white">
                                    {{ $operation->operation_date ? \Carbon\Carbon::parse($operation->operation_date)->format('d M Y') : '-' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-slate-400">
                                    {{ $operation->operation_date ? \Carbon\Carbon::parse($operation->operation_date)->format('H:i') : '-' }}
                                </p>
                            </td>
                            <td class="px-4 py-3 text-center hidden sm:table-cell">
                                <span
                                    class="font-medium text-gray-900 dark:text-white">{{ $operation->duration_minutes ?? '-' }}
                                    min</span>
                            </td>
                            <td class="px-4 py-3 text-center hidden lg:table-cell">
                                @if ($operation->has_complications)
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Yes</span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">No</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($operation->outcome === 'successful')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Successful</span>
                                @elseif($operation->outcome === 'partial')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Partial</span>
                                @elseif($operation->outcome === 'failed')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Failed</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('healthcare.surgery.operations.show', $operation) }}"
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
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-slate-400">
                                <p>Belum ada log operasi</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if (isset($operations) && $operations->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-white/10">
                {{ $operations->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
