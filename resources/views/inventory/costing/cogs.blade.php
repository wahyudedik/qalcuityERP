<x-app-layout>
    <x-slot name="header">Laporan HPP (COGS)</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('inventory.costing.valuation') }}" class="text-xs text-gray-500 hover:text-blue-500">← Valuasi Stok</a>
    </div>

    <div class="space-y-6">

        {{-- Filter --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Dari Tanggal</label>
                    <input type="date" name="from" value="{{ $from }}"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Sampai Tanggal</label>
                    <input type="date" name="to" value="{{ $to }}"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-xl hover:bg-blue-700">Tampilkan</button>
            </form>
        </div>

        {{-- Summary --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <p class="text-xs text-gray-500">Total HPP Periode Ini</p>
                <p class="text-2xl font-bold text-red-600 mt-1">
                    Rp {{ number_format($report['total_cogs'], 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-400 mt-1">Metode: {{ strtoupper($report['method']) }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <p class="text-xs text-gray-500">Jumlah SKU Terjual</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ count($report['rows']) }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <p class="text-xs text-gray-500">Periode</p>
                <p class="text-sm font-semibold text-gray-900 mt-1">
                    {{ \Carbon\Carbon::parse($from)->format('d M Y') }} — {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
                </p>
            </div>
        </div>

        {{-- COGS Table --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="font-semibold text-gray-900">HPP per Produk</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Produk</th>
                            <th class="px-4 py-3 text-left hidden sm:table-cell">SKU</th>
                            <th class="px-4 py-3 text-right">Qty Terjual</th>
                            <th class="px-4 py-3 text-right">HPP/Unit</th>
                            <th class="px-4 py-3 text-right">Total HPP</th>
                            <th class="px-4 py-3 text-right hidden md:table-cell">% dari Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($report['rows'] as $row)
                        @php
                            $pct = $report['total_cogs'] > 0 ? ($row['total_cogs'] / $report['total_cogs']) * 100 : 0;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $row['product_name'] }}</td>
                            <td class="px-4 py-3 text-gray-500 font-mono text-xs hidden sm:table-cell">{{ $row['sku'] ?? '—' }}</td>
                            <td class="px-4 py-3 text-right text-gray-900">{{ number_format($row['qty_sold'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-gray-700">Rp {{ number_format($row['avg_unit_cost'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-red-600">Rp {{ number_format($row['total_cogs'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-gray-100 rounded-full h-1.5">
                                        <div class="bg-red-400 h-1.5 rounded-full" style="width: {{ min(100, $pct) }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-500 w-10 text-right">{{ number_format($pct, 1) }}%</span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-400">
                                Belum ada data COGS untuk periode ini.
                                @if($tenant->costing_method === 'simple')
                                <br><span class="text-xs">Data COGS akan tercatat otomatis saat ada transaksi keluar stok.</span>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if(count($report['rows']) > 0)
                    <tfoot class="bg-gray-50 font-semibold text-sm">
                        <tr>
                            <td colspan="4" class="px-4 py-3 text-right text-gray-700">Total HPP</td>
                            <td class="px-4 py-3 text-right text-red-600">Rp {{ number_format($report['total_cogs'], 0, ',', '.') }}</td>
                            <td class="hidden md:table-cell"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

        @if($tenant->costing_method === 'simple')
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-sm text-amber-700">
            <strong>Mode Simple:</strong> HPP dihitung dari harga beli tetap di master produk. Data COGS mulai tercatat sejak fitur ini diaktifkan.
            Untuk akurasi lebih tinggi, beralih ke <a href="{{ route('inventory.costing.valuation') }}" class="underline">AVCO atau FIFO</a>.
        </div>
        @endif

    </div>
</x-app-layout>
