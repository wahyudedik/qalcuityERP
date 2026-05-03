<x-app-layout>
    <x-slot name="header">Deferred Revenue & Prepaid Expense</x-slot>

    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 mb-6">
        <form method="GET" class="flex gap-2 flex-wrap">
            <select name="type" onchange="this.form.submit()" class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Tipe</option>
                <option value="deferred_revenue" {{ request('type') === 'deferred_revenue' ? 'selected' : '' }}>Pendapatan Diterima di Muka</option>
                <option value="prepaid_expense" {{ request('type') === 'prepaid_expense' ? 'selected' : '' }}>Biaya Dibayar di Muka</option>
            </select>
            <select name="status" onchange="this.form.submit()" class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
            </select>
        </form>
        <a href="{{ route('deferred.create') }}" class="ml-auto px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 shrink-0">+ Buat Baru</a>
    </div>

    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nomor / Deskripsi</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Tipe</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Diakui</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Sisa</th>
                        <th class="px-4 py-3 text-center">Progress</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($items as $item)
                    @php
                        $pct = $item->progressPercent();
                        $statusColor = match($item->status) {
                            'active'    => 'bg-blue-100 text-blue-700',
                            'completed' => 'bg-green-100 text-green-700',
                            default     => 'bg-gray-100 text-gray-500',
                        };
                        $typeColor = $item->type === 'deferred_revenue'
                            ? 'bg-purple-100 text-purple-700'
                            : 'bg-orange-100 text-orange-700';
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <a href="{{ route('deferred.show', $item) }}" class="font-medium text-blue-600 hover:underline">{{ $item->number }}</a>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $item->description }}</p>
                            <p class="text-xs text-gray-400">{{ $item->start_date->format('d M Y') }} – {{ $item->end_date->format('d M Y') }}</p>
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $typeColor }}">
                                {{ $item->type === 'deferred_revenue' ? 'Pend. di Muka' : 'Biaya di Muka' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900">Rp {{ number_format($item->total_amount,0,',','.') }}</td>
                        <td class="px-4 py-3 text-right hidden md:table-cell text-green-600">Rp {{ number_format($item->recognized_amount,0,',','.') }}</td>
                        <td class="px-4 py-3 text-right hidden md:table-cell text-gray-500">Rp {{ number_format($item->remaining_amount,0,',','.') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-100 rounded-full h-2 min-w-[60px]">
                                    <div class="bg-blue-500 h-2 rounded-full" style="width:{{ $pct }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500 whitespace-nowrap">{{ $item->recognized_periods }}/{{ $item->total_periods }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                {{ ucfirst($item->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('deferred.show', $item) }}" class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 inline-flex">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-4 py-12 text-center text-gray-400">Belum ada data deferred item.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $items->links() }}</div>
        @endif
    </div>
</x-app-layout>
