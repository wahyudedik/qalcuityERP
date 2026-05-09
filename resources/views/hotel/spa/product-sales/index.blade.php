<x-app-layout title="Penjualan Produk Spa">
    <x-slot name="header">Penjualan Produk</x-slot>

    <x-slot name="pageTitle">Penjualan Produk Spa</x-slot>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <p class="text-xs text-gray-500">Total Penjualan</p>
            <p class="text-xl font-bold text-gray-900 mt-1">Rp
                {{ number_format($stats['total_sales'] ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <p class="text-xs text-gray-500">Total Profit</p>
            <p class="text-xl font-bold text-green-600 mt-1">Rp
                {{ number_format($stats['total_profit'] ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <p class="text-xs text-gray-500">Item Terjual</p>
            <p class="text-xl font-bold text-gray-900 mt-1">{{ number_format($stats['total_items_sold'] ?? 0) }}</p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-4 mb-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Dari</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Sampai</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
            </div>
            <button type="submit"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
        </form>
    </div>

    {{-- Sales Table --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        @if ($sales->isEmpty())
            <div class="p-8 text-center text-gray-500">Belum ada data penjualan produk</div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-6 py-3 text-left">Tanggal</th>
                            <th class="px-6 py-3 text-left">Produk</th>
                            <th class="px-6 py-3 text-center">Qty</th>
                            <th class="px-6 py-3 text-right">Total</th>
                            <th class="px-6 py-3 text-left">Dijual Oleh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($sales as $sale)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 text-gray-900 whitespace-nowrap">
                                    {{ $sale->sale_date ? \Carbon\Carbon::parse($sale->sale_date)->format('d M Y') : '-' }}
                                </td>
                                <td class="px-6 py-3 text-gray-900">{{ $sale->product?->name ?? '-' }}</td>
                                <td class="px-6 py-3 text-center text-gray-700">{{ $sale->quantity ?? 0 }}</td>
                                <td class="px-6 py-3 text-right text-gray-900 whitespace-nowrap">
                                    Rp {{ number_format($sale->total_price ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-3 text-gray-700">{{ $sale->soldBy?->name ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($sales->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">{{ $sales->links() }}</div>
            @endif
        @endif
    </div>
</x-app-layout>
