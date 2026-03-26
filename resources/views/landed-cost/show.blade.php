<x-app-layout>
    <x-slot name="header">Landed Cost — {{ $landedCost->number }}</x-slot>

    <div class="space-y-6">
        {{-- Header --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $landedCost->number }}</h2>
                    <p class="text-sm text-gray-500 dark:text-slate-400">
                        PO: {{ $landedCost->purchaseOrder->number ?? '-' }}
                        @if($landedCost->purchaseOrder?->supplier) · {{ $landedCost->purchaseOrder->supplier->name }} @endif
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    @php
                        $sc = ['draft'=>'gray','allocated'=>'amber','posted'=>'green'][$landedCost->status] ?? 'gray';
                        $sl = ['draft'=>'Draft','allocated'=>'Dialokasi','posted'=>'Diposting'][$landedCost->status] ?? $landedCost->status;
                    @endphp
                    <span class="px-3 py-1 rounded-full text-sm font-medium bg-{{ $sc }}-100 text-{{ $sc }}-700 dark:bg-{{ $sc }}-500/20 dark:text-{{ $sc }}-400">{{ $sl }}</span>

                    @canmodule('landed_cost', 'edit')
                    @if($landedCost->status === 'draft' || $landedCost->status === 'allocated')
                    <form method="POST" action="{{ route('landed-cost.allocate', $landedCost) }}">
                        @csrf
                        <button type="submit" class="px-3 py-1 text-sm bg-amber-600 text-white rounded-xl hover:bg-amber-700">
                            {{ $landedCost->status === 'draft' ? 'Alokasi' : 'Re-alokasi' }}
                        </button>
                    </form>
                    @endif
                    @if($landedCost->status === 'allocated')
                    <form method="POST" action="{{ route('landed-cost.post', $landedCost) }}" onsubmit="return confirm('Posting akan update HPP produk. Lanjutkan?')">
                        @csrf
                        <button type="submit" class="px-3 py-1 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">Posting & Update HPP</button>
                    </form>
                    @endif
                    @endcanmodule
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div><p class="text-xs text-gray-500 dark:text-slate-400">Tanggal</p><p class="text-gray-900 dark:text-white">{{ $landedCost->date->format('d/m/Y') }}</p></div>
                <div><p class="text-xs text-gray-500 dark:text-slate-400">Metode Alokasi</p><p class="text-gray-900 dark:text-white">{{ ['by_value'=>'Berdasarkan Nilai','by_quantity'=>'Berdasarkan Qty','by_weight'=>'Berdasarkan Berat','equal'=>'Rata (Equal)'][$landedCost->allocation_method] ?? $landedCost->allocation_method }}</p></div>
                <div><p class="text-xs text-gray-500 dark:text-slate-400">Total Biaya Tambahan</p><p class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($landedCost->total_additional_cost, 0, ',', '.') }}</p></div>
                <div><p class="text-xs text-gray-500 dark:text-slate-400">Jurnal</p>
                    @if($landedCost->journalEntry)
                    <a href="{{ url('accounting/journals') }}/{{ $landedCost->journalEntry->id }}" class="text-blue-500 hover:underline">{{ $landedCost->journalEntry->number }}</a>
                    @else <p class="text-gray-400">—</p> @endif
                </div>
            </div>
        </div>

        {{-- Cost Components --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Komponen Biaya</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Tipe</th>
                            <th class="px-4 py-3 text-left">Nama</th>
                            <th class="px-4 py-3 text-right">Jumlah</th>
                            <th class="px-4 py-3 text-left hidden sm:table-cell">Vendor</th>
                            <th class="px-4 py-3 text-left hidden sm:table-cell">Referensi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @foreach($landedCost->components as $comp)
                        <tr>
                            <td class="px-4 py-3 text-xs">
                                <span class="px-2 py-0.5 rounded-full {{ ['freight'=>'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400','customs'=>'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400','insurance'=>'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400','handling'=>'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400'][$comp->type] ?? 'bg-gray-100 text-gray-500' }}">
                                    {{ ['freight'=>'Freight','customs'=>'Bea Masuk','insurance'=>'Asuransi','handling'=>'Handling','other'=>'Lainnya'][$comp->type] ?? $comp->type }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $comp->name }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">Rp {{ number_format($comp->amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 hidden sm:table-cell text-gray-500 dark:text-slate-400">{{ $comp->vendor ?? '-' }}</td>
                            <td class="px-4 py-3 hidden sm:table-cell text-gray-500 dark:text-slate-400 text-xs">{{ $comp->reference ?? '-' }}</td>
                        </tr>
                        @endforeach
                        <tr class="bg-gray-50 dark:bg-white/5 font-semibold">
                            <td colspan="2" class="px-4 py-3 text-gray-900 dark:text-white">Total</td>
                            <td class="px-4 py-3 text-right text-gray-900 dark:text-white">Rp {{ number_format($landedCost->components->sum('amount'), 0, ',', '.') }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Allocation Table --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Alokasi per Produk</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Produk</th>
                            <th class="px-4 py-3 text-right">Qty</th>
                            <th class="px-4 py-3 text-right">Harga Asli</th>
                            <th class="px-4 py-3 text-right">Biaya Dialokasi</th>
                            <th class="px-4 py-3 text-right">Landed Unit Cost</th>
                            @if($landedCost->allocation_method === 'by_weight')
                            <th class="px-4 py-3 text-right">Berat</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @foreach($landedCost->allocations as $alloc)
                        <tr>
                            <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $alloc->product->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-right text-gray-700 dark:text-slate-300">{{ number_format($alloc->quantity, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-gray-700 dark:text-slate-300">Rp {{ number_format($alloc->original_cost, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-medium {{ $alloc->allocated_cost > 0 ? 'text-amber-500' : 'text-gray-400' }}">
                                {{ $alloc->allocated_cost > 0 ? 'Rp ' . number_format($alloc->allocated_cost, 0, ',', '.') : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">
                                {{ $alloc->landed_unit_cost > 0 ? 'Rp ' . number_format($alloc->landed_unit_cost, 0, ',', '.') : '—' }}
                            </td>
                            @if($landedCost->allocation_method === 'by_weight')
                            <td class="px-4 py-3 text-right">
                                @if($landedCost->status !== 'posted')
                                <form method="POST" action="{{ route('landed-cost.weight', $alloc) }}" class="flex items-center justify-end gap-1">
                                    @csrf @method('PATCH')
                                    <input type="number" name="weight" value="{{ $alloc->weight }}" min="0.001" step="0.001"
                                        class="w-20 px-2 py-1 text-xs rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                                    <button type="submit" class="text-xs text-blue-500 hover:underline">✓</button>
                                </form>
                                @else
                                {{ $alloc->weight ?? '-' }}
                                @endif
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
