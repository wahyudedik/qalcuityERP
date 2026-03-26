<x-app-layout>
    <x-slot name="header">Konsinyasi — {{ $consignmentShipment->number }}</x-slot>

    @php $ship = $consignmentShipment; @endphp
    <div class="space-y-6">
        {{-- Header --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $ship->number }}</h2>
                    <p class="text-sm text-gray-500 dark:text-slate-400">🏪 {{ $ship->partner->name ?? '-' }} · {{ $ship->warehouse->name ?? '-' }}</p>
                </div>
                @php
                    $sc = ['draft'=>'gray','shipped'=>'blue','partial_sold'=>'amber','settled'=>'green','returned'=>'purple'][$ship->status] ?? 'gray';
                    $sl = ['draft'=>'Draft','shipped'=>'Dikirim','partial_sold'=>'Sebagian Terjual','settled'=>'Settled','returned'=>'Diretur'][$ship->status] ?? $ship->status;
                @endphp
                <span class="px-3 py-1 rounded-full text-sm font-medium bg-{{ $sc }}-100 text-{{ $sc }}-700 dark:bg-{{ $sc }}-500/20 dark:text-{{ $sc }}-400">{{ $sl }}</span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div><p class="text-xs text-gray-500 dark:text-slate-400">Tanggal Kirim</p><p class="text-gray-900 dark:text-white">{{ $ship->ship_date->format('d/m/Y') }}</p></div>
                <div><p class="text-xs text-gray-500 dark:text-slate-400">Nilai HPP</p><p class="text-gray-900 dark:text-white">Rp {{ number_format($ship->total_cost, 0, ',', '.') }}</p></div>
                <div><p class="text-xs text-gray-500 dark:text-slate-400">Nilai Retail</p><p class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($ship->total_retail, 0, ',', '.') }}</p></div>
                <div><p class="text-xs text-gray-500 dark:text-slate-400">Komisi Partner</p><p class="text-gray-900 dark:text-white">{{ $ship->partner->commission_pct ?? 0 }}%</p></div>
            </div>
        </div>

        {{-- Items --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Item Titipan</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Produk</th>
                            <th class="px-4 py-3 text-right">Dikirim</th>
                            <th class="px-4 py-3 text-right">Terjual</th>
                            <th class="px-4 py-3 text-right">Diretur</th>
                            <th class="px-4 py-3 text-right">Sisa</th>
                            <th class="px-4 py-3 text-right">HPP</th>
                            <th class="px-4 py-3 text-right">Harga Jual</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @foreach($ship->items as $item)
                        <tr>
                            <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $item->product->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-right text-gray-700 dark:text-slate-300">{{ number_format($item->quantity_sent, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-green-500 font-medium">{{ number_format($item->quantity_sold, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-purple-500">{{ number_format($item->quantity_returned, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-semibold {{ $item->remainingQty() > 0 ? 'text-amber-500' : 'text-gray-400' }}">{{ number_format($item->remainingQty(), 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-gray-500 dark:text-slate-400">Rp {{ number_format($item->cost_price, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-gray-900 dark:text-white">Rp {{ number_format($item->retail_price, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Actions: Report Sales / Return --}}
        @if(in_array($ship->status, ['shipped', 'partial_sold']))
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Report Sales --}}
            @canmodule('consignment', 'create')
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Lapor Penjualan</h3>
                <form method="POST" action="{{ route('consignment.sales-report.store', $ship) }}" class="space-y-3">
                    @csrf
                    @php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; @endphp
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Periode Mulai</label><input type="date" name="period_start" required class="{{ $cls }}"></div>
                        <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Periode Akhir</label><input type="date" name="period_end" required value="{{ date('Y-m-d') }}" class="{{ $cls }}"></div>
                    </div>
                    @foreach($ship->items as $item)
                    @if($item->remainingQty() > 0)
                    <div class="flex items-center gap-3 text-sm">
                        <input type="hidden" name="items[{{ $loop->index }}][item_id]" value="{{ $item->id }}">
                        <span class="flex-1 text-gray-700 dark:text-slate-300">{{ $item->product->name ?? '-' }} <span class="text-xs text-gray-400">(sisa {{ number_format($item->remainingQty(), 0) }})</span></span>
                        <input type="number" name="items[{{ $loop->index }}][quantity_sold]" min="0" max="{{ $item->remainingQty() }}" value="0" step="1"
                            class="w-24 px-2 py-1 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    </div>
                    @endif
                    @endforeach
                    <button type="submit" class="w-full px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">Simpan Laporan</button>
                </form>
            </div>
            @endcanmodule

            {{-- Return Items --}}
            @canmodule('consignment', 'edit')
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Retur Barang</h3>
                <form method="POST" action="{{ route('consignment.return', $ship) }}" class="space-y-3">
                    @csrf
                    @foreach($ship->items as $item)
                    @if($item->remainingQty() > 0)
                    <div class="flex items-center gap-3 text-sm">
                        <input type="hidden" name="items[{{ $loop->index }}][item_id]" value="{{ $item->id }}">
                        <span class="flex-1 text-gray-700 dark:text-slate-300">{{ $item->product->name ?? '-' }} <span class="text-xs text-gray-400">(sisa {{ number_format($item->remainingQty(), 0) }})</span></span>
                        <input type="number" name="items[{{ $loop->index }}][quantity_returned]" min="0" max="{{ $item->remainingQty() }}" value="0" step="1"
                            class="w-24 px-2 py-1 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    </div>
                    @endif
                    @endforeach
                    <button type="submit" class="w-full px-4 py-2 text-sm bg-purple-600 text-white rounded-xl hover:bg-purple-700">Proses Retur</button>
                </form>
            </div>
            @endcanmodule
        </div>
        @endif

        {{-- Sales Reports & Settlements --}}
        @if($ship->salesReports->isNotEmpty())
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Laporan Penjualan & Settlement</h3>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-white/5">
                @foreach($ship->salesReports->sortByDesc('created_at') as $rpt)
                <div class="p-4">
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <span class="font-mono text-xs font-medium text-gray-900 dark:text-white">{{ $rpt->number }}</span>
                            <span class="text-xs text-gray-500 dark:text-slate-400 ml-2">{{ $rpt->period_start->format('d/m') }} — {{ $rpt->period_end->format('d/m/Y') }}</span>
                        </div>
                        @php $rc = ['draft'=>'gray','confirmed'=>'blue','settled'=>'green'][$rpt->status] ?? 'gray'; @endphp
                        <span class="px-2 py-0.5 rounded-full text-xs bg-{{ $rc }}-100 text-{{ $rc }}-700 dark:bg-{{ $rc }}-500/20 dark:text-{{ $rc }}-400">{{ ucfirst($rpt->status) }}</span>
                    </div>
                    <div class="grid grid-cols-4 gap-3 text-xs mb-2">
                        <div><span class="text-gray-500 dark:text-slate-400">Penjualan:</span> <span class="text-gray-900 dark:text-white">Rp {{ number_format($rpt->total_sales, 0, ',', '.') }}</span></div>
                        <div><span class="text-gray-500 dark:text-slate-400">Komisi ({{ $rpt->commission_pct }}%):</span> <span class="text-red-500">Rp {{ number_format($rpt->commission_amount, 0, ',', '.') }}</span></div>
                        <div><span class="text-gray-500 dark:text-slate-400">Net:</span> <span class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($rpt->net_receivable, 0, ',', '.') }}</span></div>
                        <div><span class="text-gray-500 dark:text-slate-400">Sisa:</span> <span class="{{ $rpt->remainingBalance() > 0 ? 'text-amber-500' : 'text-green-500' }}">Rp {{ number_format($rpt->remainingBalance(), 0, ',', '.') }}</span></div>
                    </div>
                    @if($rpt->remainingBalance() > 0.01 && $rpt->status !== 'settled')
                    @canmodule('consignment', 'create')
                    <form method="POST" action="{{ route('consignment.settlement.store', $rpt) }}" class="flex items-end gap-2 mt-2">
                        @csrf
                        <input type="date" name="settlement_date" required value="{{ date('Y-m-d') }}" class="px-2 py-1 text-xs rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                        <input type="number" name="amount" required min="0.01" max="{{ $rpt->remainingBalance() }}" step="0.01" value="{{ $rpt->remainingBalance() }}" placeholder="Jumlah"
                            class="w-32 px-2 py-1 text-xs rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                        <select name="payment_method" class="px-2 py-1 text-xs rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                            <option value="transfer">Transfer</option><option value="cash">Cash</option>
                        </select>
                        <button type="submit" class="px-3 py-1 text-xs bg-green-600 text-white rounded-lg hover:bg-green-700">Settle</button>
                    </form>
                    @endcanmodule
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</x-app-layout>
