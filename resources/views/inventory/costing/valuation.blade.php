<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            Valuasi Inventori
            <a href="{{ route('inventory.costing.cogs') }}" class="text-xs text-gray-500 dark:text-slate-400 hover:text-blue-500">Laporan COGS →</a>
        </div>
    </x-slot>

    <div class="space-y-6">

        {{-- Costing Method Setting --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <p class="font-semibold text-gray-900 dark:text-white">Metode Kalkulasi Biaya</p>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Berlaku untuk semua perhitungan HPP dan valuasi stok</p>
                </div>
                <form method="POST" action="{{ route('inventory.costing.method') }}" class="flex items-center gap-3">
                    @csrf
                    <select name="costing_method" onchange="this.form.submit()"
                        class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="simple" @selected($tenant->costing_method === 'simple')>Simple (Harga Beli Tetap)</option>
                        <option value="avco"   @selected($tenant->costing_method === 'avco')>AVCO (Rata-rata Tertimbang)</option>
                        <option value="fifo"   @selected($tenant->costing_method === 'fifo')>FIFO (Masuk Pertama, Keluar Pertama)</option>
                    </select>
                </form>
            </div>
            @php
                $methodDesc = [
                    'simple' => 'Menggunakan harga beli tetap dari master produk. Cocok untuk UMKM dan bisnis dengan harga beli stabil.',
                    'avco'   => 'Harga pokok dihitung dari rata-rata tertimbang semua pembelian. Sesuai PSAK 14, cocok untuk sebagian besar industri.',
                    'fifo'   => 'Barang yang masuk pertama dikeluarkan pertama. Cocok untuk produk dengan expiry date (makanan, farmasi, FMCG).',
                ];
            @endphp
            <p class="mt-3 text-xs text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/20 rounded-xl px-3 py-2">
                {{ $methodDesc[$tenant->costing_method] ?? '' }}
            </p>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <p class="text-xs text-gray-500 dark:text-slate-400">Total Nilai Stok</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                    Rp {{ number_format($report['total'], 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">Metode: {{ strtoupper($report['method']) }}</p>
            </div>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <p class="text-xs text-gray-500 dark:text-slate-400">Jumlah SKU</p>
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ count($report['rows']) }}</p>
            </div>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <p class="text-xs text-gray-500 dark:text-slate-400">Rata-rata Nilai/SKU</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                    Rp {{ count($report['rows']) > 0 ? number_format($report['total'] / count($report['rows']), 0, ',', '.') : '0' }}
                </p>
            </div>
        </div>

        {{-- Valuation Table --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10">
                <h2 class="font-semibold text-gray-900 dark:text-white">Detail Valuasi per Produk</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Produk</th>
                            <th class="px-4 py-3 text-left hidden sm:table-cell">SKU</th>
                            <th class="px-4 py-3 text-left hidden md:table-cell">Gudang</th>
                            <th class="px-4 py-3 text-right">Qty</th>
                            <th class="px-4 py-3 text-right">Harga Beli</th>
                            <th class="px-4 py-3 text-right">HPP/Unit</th>
                            <th class="px-4 py-3 text-right">Total Nilai</th>
                            <th class="px-4 py-3 text-center hidden lg:table-cell">Selisih</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @forelse($report['rows'] as $row)
                        @php
                            $diff = $row['unit_cost'] - $row['price_buy'];
                            $diffPct = $row['price_buy'] > 0 ? ($diff / $row['price_buy']) * 100 : 0;
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $row['product_name'] }}</td>
                            <td class="px-4 py-3 text-gray-500 dark:text-slate-400 font-mono text-xs hidden sm:table-cell">{{ $row['sku'] ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-500 dark:text-slate-400 text-xs hidden md:table-cell">{{ $row['warehouse_name'] }}</td>
                            <td class="px-4 py-3 text-right text-gray-900 dark:text-white">{{ number_format($row['quantity'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-gray-500 dark:text-slate-400">Rp {{ number_format($row['price_buy'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">Rp {{ number_format($row['unit_cost'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-blue-600 dark:text-blue-400">Rp {{ number_format($row['total_value'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center hidden lg:table-cell">
                                @if(abs($diffPct) < 0.1)
                                <span class="text-xs text-gray-400">—</span>
                                @elseif($diff > 0)
                                <span class="text-xs text-amber-600 dark:text-amber-400">+{{ number_format($diffPct, 1) }}%</span>
                                @else
                                <span class="text-xs text-green-600 dark:text-green-400">{{ number_format($diffPct, 1) }}%</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="px-6 py-10 text-center text-gray-400 dark:text-slate-500">Belum ada data stok.</td></tr>
                        @endforelse
                    </tbody>
                    @if(count($report['rows']) > 0)
                    <tfoot class="bg-gray-50 dark:bg-white/5 font-semibold text-sm">
                        <tr>
                            <td colspan="6" class="px-4 py-3 text-right text-gray-700 dark:text-slate-300">Total Nilai Inventori</td>
                            <td class="px-4 py-3 text-right text-blue-600 dark:text-blue-400">Rp {{ number_format($report['total'], 0, ',', '.') }}</td>
                            <td class="hidden lg:table-cell"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

        @if($tenant->costing_method === 'simple')
        <div class="bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-2xl p-4 text-sm text-amber-700 dark:text-amber-300">
            <strong>Mode Simple:</strong> Nilai stok dihitung dari harga beli tetap di master produk. Untuk akurasi HPP yang lebih baik, pertimbangkan beralih ke AVCO atau FIFO.
        </div>
        @endif

    </div>
</x-app-layout>
