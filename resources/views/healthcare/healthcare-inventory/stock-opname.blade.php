<x-app-layout>
    <x-slot name="header">Stock Opname</x-slot>

    @php $tid = auth()->user()->tenant_id; @endphp

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        @php
            $totalOpnames = \App\Models\StockOpname::where('tenant_id', $tid)->count();
            $inProgressOpnames = \App\Models\StockOpname::where('tenant_id', $tid)
                ->where('status', 'in_progress')
                ->count();
            $completedOpnames = \App\Models\StockOpname::where('tenant_id', $tid)
                ->where('status', 'completed')
                ->count();
            $totalDiscrepancies = \App\Models\StockOpname::where('tenant_id', $tid)
                ->where('status', 'completed')
                ->sum('discrepancy_count');
        @endphp
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Total Opname</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalOpnames) }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Berlangsung</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $inProgressOpnames }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Selesai</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ $completedOpnames }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Total Discrepancy</p>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ number_format($totalDiscrepancies) }}
            </p>
        </div>
    </div>

    {{-- Stock Opname Table --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Opname ID</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Lokasi</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Petugas</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Tanggal</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Items Checked</th>
                        <th class="px-4 py-3 text-center hidden lg:table-cell">Discrepancies</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($opnames ?? [] as $opname)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <span
                                    class="font-mono text-sm font-bold text-blue-600">{{ $opname->opname_number ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <p class="text-gray-900">{{ $opname->location ?? '-' }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $opname->category ?? 'All Categories' }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-600 hidden lg:table-cell">
                                {{ $opname->conductedBy ? $opname->conductedBy->name : '-' }}</td>
                            <td class="px-4 py-3 hidden sm:table-cell">
                                <p class="text-gray-900">
                                    {{ $opname->opname_date ? \Carbon\Carbon::parse($opname->opname_date)->format('d M Y') : '-' }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $opname->opname_date ? \Carbon\Carbon::parse($opname->opname_date)->format('H:i') : '-' }}
                                </p>
                            </td>
                            <td class="px-4 py-3 text-center hidden sm:table-cell">
                                <span
                                    class="font-medium text-gray-900">{{ $opname->items_checked ?? 0 }}</span>
                            </td>
                            <td class="px-4 py-3 text-center hidden lg:table-cell">
                                @if ($opname->discrepancy_count > 0)
                                    <span
                                        class="font-bold text-red-600">{{ $opname->discrepancy_count }}</span>
                                @else
                                    <span class="text-green-600">0</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($opname->status === 'draft')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700">Draft</span>
                                @elseif($opname->status === 'in_progress')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700">In
                                        Progress</span>
                                @elseif($opname->status === 'completed')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700">Completed</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('healthcare.inventory.stock-opname.show', $opname) }}"
                                        class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg"
                                        title="Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                            </path>
                                        </svg>
                                    </a>
                                    @if ($opname->status === 'draft' || $opname->status === 'in_progress')
                                        <a href="{{ route('healthcare.inventory.stock-opname.count', $opname) }}"
                                            class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg"
                                            title="Hitung Stok">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                                                </path>
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                <p>Belum ada stock opname</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if (isset($opnames) && $opnames->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $opnames->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
