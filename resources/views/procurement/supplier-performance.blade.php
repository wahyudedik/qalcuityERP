<x-app-layout>
    <x-slot name="header">Supplier Performance</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4">
        <div class="flex items-center gap-2">
            <form method="GET" id="period-form">
                <select name="period" onchange="document.getElementById('period-form').submit()"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="30"  {{ $period == 30  ? 'selected' : '' }}>30 Hari</option>
                    <option value="90"  {{ $period == 90  ? 'selected' : '' }}>90 Hari</option>
                    <option value="180" {{ $period == 180 ? 'selected' : '' }}>6 Bulan</option>
                    <option value="365" {{ $period == 365 ? 'selected' : '' }}>1 Tahun</option>
                </select>
            </form>
        </div>
        <a href="{{ route('supplier-performance.evaluate') }}"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Evaluasi Supplier
        </a>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Total Supplier</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $totalSuppliers }}</p>
            <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">Supplier aktif</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Rata-rata Skor</p>
            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ number_format($avgScore ?? 0, 1) }}</p>
            <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">Performa keseluruhan</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Terbaik</p>
            <p class="text-lg font-bold text-green-600 dark:text-green-400 truncate">{{ $topGrade?->name ?? '-' }}</p>
            <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">Grade: {{ $topGrade?->performance['current_grade'] ?? 'N/A' }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Periode Evaluasi</p>
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $period }} Hari</p>
            <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">{{ $period }} hari terakhir</p>
        </div>
    </div>

    {{-- Top 10 Rankings --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden mb-4">
        <div class="px-4 py-3 border-b border-gray-100 dark:border-white/5 flex items-center gap-2">
            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
            </svg>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Top 10 Supplier Rankings</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left w-12">No</th>
                        <th class="px-4 py-3 text-left">Supplier</th>
                        <th class="px-4 py-3 text-center">Grade</th>
                        <th class="px-4 py-3 text-right">Skor</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Pengiriman</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Kualitas</th>
                        <th class="px-4 py-3 text-right hidden lg:table-cell">Harga</th>
                        <th class="px-4 py-3 text-right hidden lg:table-cell">Tepat Waktu</th>
                        <th class="px-4 py-3 text-right hidden lg:table-cell">Evaluasi</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($rankings as $index => $ranking)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="px-4 py-3">
                                @if($index < 3)
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold
                                        {{ $index === 0 ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400' : '' }}
                                        {{ $index === 1 ? 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-slate-300' : '' }}
                                        {{ $index === 2 ? 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400' : '' }}">
                                        {{ $index + 1 }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400 dark:text-slate-500 font-medium pl-1">#{{ $index + 1 }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('supplier-performance.detail', $ranking['supplier_id']) }}"
                                    class="font-medium text-blue-600 dark:text-blue-400 hover:underline">
                                    {{ $ranking['supplier_name'] }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $gc = match(str_split($ranking['grade'])[0]) {
                                        'A' => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400',
                                        'B' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
                                        'C' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400',
                                        'D' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
                                        default => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400',
                                    };
                                @endphp
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $gc }}">{{ $ranking['grade'] }}</span>
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white">{{ number_format($ranking['avg_score'], 1) }}</td>
                            <td class="px-4 py-3 text-right hidden md:table-cell text-gray-500 dark:text-slate-400">{{ number_format($ranking['avg_delivery'], 1) }}</td>
                            <td class="px-4 py-3 text-right hidden md:table-cell text-gray-500 dark:text-slate-400">{{ number_format($ranking['avg_quality'], 1) }}</td>
                            <td class="px-4 py-3 text-right hidden lg:table-cell text-gray-500 dark:text-slate-400">{{ number_format($ranking['avg_cost'], 1) }}</td>
                            <td class="px-4 py-3 text-right hidden lg:table-cell">
                                <span class="{{ $ranking['on_time_rate'] >= 90 ? 'text-green-600 dark:text-green-400' : 'text-red-500 dark:text-red-400' }} font-medium">
                                    {{ $ranking['on_time_rate'] }}%
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right hidden lg:table-cell text-gray-500 dark:text-slate-400">{{ $ranking['evaluation_count'] }}</td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('supplier-performance.detail', $ranking['supplier_id']) }}"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-lg bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-500/20">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500 text-sm">
                                Belum ada evaluasi supplier. Mulai evaluasi setelah menerima PO.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- All Suppliers Performance --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 dark:border-white/5 flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Semua Supplier</h3>
        </div>
        <div class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @forelse($suppliers as $supplier)
                @php
                    $grade = $supplier->performance['current_grade'] ?? 'N/A';
                    $hasData = $supplier->performance['total_evaluations'] > 0;
                    $gc2 = match(str_split($grade)[0]) {
                        'A' => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400',
                        'B' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
                        'C' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400',
                        'D' => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
                        default => 'bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-slate-400',
                    };
                    $trend = $supplier->performance['trend'] ?? 'stable';
                @endphp
                <div class="border border-gray-200 dark:border-white/10 rounded-xl p-4 hover:border-blue-300 dark:hover:border-blue-500/40 transition-colors">
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <div class="min-w-0">
                            <h4 class="font-semibold text-gray-900 dark:text-white text-sm truncate">{{ $supplier->name }}</h4>
                            <p class="text-xs text-gray-400 dark:text-slate-500 truncate">{{ $supplier->company ?? '-' }}</p>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold shrink-0 {{ $gc2 }}">{{ $grade }}</span>
                    </div>

                    @if($hasData)
                        <div class="space-y-1.5 text-xs">
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-slate-400">Skor Keseluruhan</span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($supplier->performance['avg_overall_score'], 1) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-slate-400">Tepat Waktu</span>
                                <span class="{{ $supplier->performance['on_time_delivery_rate'] >= 90 ? 'text-green-600 dark:text-green-400' : 'text-red-500 dark:text-red-400' }} font-medium">
                                    {{ $supplier->performance['on_time_delivery_rate'] }}%
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-slate-400">Kualitas</span>
                                <span class="text-gray-700 dark:text-slate-300">{{ number_format($supplier->performance['avg_quality_rate'], 1) }}%</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-slate-400">Total PO</span>
                                <span class="text-gray-700 dark:text-slate-300">{{ $supplier->performance['total_pos'] }}</span>
                            </div>
                            <div class="flex justify-between items-center pt-1.5 border-t border-gray-100 dark:border-white/5">
                                <span class="text-gray-500 dark:text-slate-400">Tren</span>
                                <span class="inline-flex items-center gap-1 text-xs font-medium
                                    {{ $trend === 'improving' ? 'text-green-600 dark:text-green-400' : ($trend === 'declining' ? 'text-red-500 dark:text-red-400' : 'text-gray-500 dark:text-slate-400') }}">
                                    @if($trend === 'improving')
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                        Meningkat
                                    @elseif($trend === 'declining')
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17H5m0 0V9m0 8l8-8 4 4 6-6"/></svg>
                                        Menurun
                                    @else
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"/></svg>
                                        Stabil
                                    @endif
                                </span>
                            </div>
                        </div>
                    @else
                        <p class="text-xs text-gray-400 dark:text-slate-500 text-center py-3">Belum ada evaluasi</p>
                    @endif

                    <div class="mt-3 pt-3 border-t border-gray-100 dark:border-white/5">
                        <a href="{{ route('supplier-performance.detail', $supplier->id) }}"
                            class="inline-flex items-center gap-1 text-xs text-blue-600 dark:text-blue-400 hover:underline font-medium">
                            Lihat Detail
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-3 py-12 text-center text-gray-400 dark:text-slate-500 text-sm">
                    Belum ada data supplier.
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
