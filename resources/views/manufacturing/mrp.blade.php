<x-app-layout>
    <x-slot name="header">Material Requirement Planning (MRP)</x-slot>

    {{-- Single BOM Calculator --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 mb-6">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Kalkulasi Kebutuhan Material</h3>
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <select name="bom_id" class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                <option value="">-- Pilih BOM --</option>
                @foreach($boms as $b)
                    <option value="{{ $b->id }}" @selected(request('bom_id') == $b->id)>{{ $b->name }} ({{ $b->product->name ?? '-' }})</option>
                @endforeach
            </select>
            <input type="number" name="quantity" min="1" step="1" value="{{ $quantity }}" placeholder="Jumlah produksi"
                class="w-32 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Hitung</button>
            <button type="submit" name="full_mrp" value="1" class="px-4 py-2 text-sm bg-purple-600 text-white rounded-xl hover:bg-purple-700">Full MRP (Semua WO)</button>
        </form>
    </div>

    {{-- Single BOM Results --}}
    @if($results !== null)
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10">
            <h3 class="font-semibold text-gray-900 dark:text-white">
                Kebutuhan: {{ $selectedBom->name ?? '-' }} × {{ number_format($quantity, 0, ',', '.') }}
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Material</th>
                        <th class="px-4 py-3 text-right">Dibutuhkan</th>
                        <th class="px-4 py-3 text-right">Stok</th>
                        <th class="px-4 py-3 text-right">PO Pending</th>
                        <th class="px-4 py-3 text-right">Demand WO Lain</th>
                        <th class="px-4 py-3 text-right">Tersedia</th>
                        <th class="px-4 py-3 text-right">Kekurangan</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @foreach($results as $r)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3 text-gray-900 dark:text-white">
                            @if($r['level'] > 0)<span class="text-gray-400">{{ str_repeat('└─ ', $r['level']) }}</span>@endif
                            {{ $r['product_name'] }}
                        </td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">{{ number_format($r['required'], 2, ',', '.') }} {{ $r['unit'] }}</td>
                        <td class="px-4 py-3 text-right text-gray-700 dark:text-slate-300">{{ number_format($r['on_hand'], 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-gray-700 dark:text-slate-300">{{ number_format($r['on_order'], 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-gray-700 dark:text-slate-300">{{ number_format($r['other_demand'], 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-gray-900 dark:text-white">{{ number_format($r['available'], 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-bold {{ $r['shortage'] > 0 ? 'text-red-500' : 'text-green-500' }}">
                            {{ $r['shortage'] > 0 ? number_format($r['shortage'], 2, ',', '.') : '—' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($r['shortage'] > 0)
                                <span class="px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400">Kurang</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400">Cukup</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @php $totalShortage = collect($results)->sum('shortage'); @endphp
        <div class="px-6 py-3 border-t border-gray-100 dark:border-white/10 flex items-center gap-4">
            @if($totalShortage > 0)
                <span class="text-sm text-red-500">⚠️ Ada {{ collect($results)->where('shortage', '>', 0)->count() }} material yang kurang stok.</span>
            @else
                <span class="text-sm text-green-500">✅ Semua material tersedia untuk produksi.</span>
            @endif
        </div>
    </div>
    @endif

    {{-- Full MRP Results --}}
    @if($fullMrp !== null)
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10">
            <h3 class="font-semibold text-gray-900 dark:text-white">Full MRP — Semua Work Order Aktif</h3>
            <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Agregasi kebutuhan material dari semua WO pending/in-progress yang memiliki BOM</p>
        </div>
        @if(count($fullMrp) > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Material</th>
                        <th class="px-4 py-3 text-right">Total Dibutuhkan</th>
                        <th class="px-4 py-3 text-right">Stok</th>
                        <th class="px-4 py-3 text-right">PO Pending</th>
                        <th class="px-4 py-3 text-right">Tersedia</th>
                        <th class="px-4 py-3 text-right">Kekurangan</th>
                        <th class="px-4 py-3 text-left">Work Order</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @foreach($fullMrp as $r)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5 {{ $r['shortage'] > 0 ? 'bg-red-50/50 dark:bg-red-500/5' : '' }}">
                        <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $r['product_name'] }}</td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">{{ number_format($r['required'], 2, ',', '.') }} {{ $r['unit'] }}</td>
                        <td class="px-4 py-3 text-right text-gray-700 dark:text-slate-300">{{ number_format($r['on_hand'], 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-gray-700 dark:text-slate-300">{{ number_format($r['on_order'], 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-gray-900 dark:text-white">{{ number_format($r['available'], 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-bold {{ $r['shortage'] > 0 ? 'text-red-500' : 'text-green-500' }}">
                            {{ $r['shortage'] > 0 ? number_format($r['shortage'], 2, ',', '.') : '—' }}
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500 dark:text-slate-400">
                            {{ implode(', ', array_slice($r['wo_refs'], 0, 3)) }}
                            @if(count($r['wo_refs']) > 3) <span class="text-gray-400">+{{ count($r['wo_refs']) - 3 }}</span> @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($r['shortage'] > 0)
                                <span class="px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400">Kurang</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400">Cukup</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @php $shortageCount = collect($fullMrp)->where('shortage', '>', 0)->count(); @endphp
        <div class="px-6 py-3 border-t border-gray-100 dark:border-white/10">
            @if($shortageCount > 0)
                <span class="text-sm text-red-500">⚠️ {{ $shortageCount }} material kekurangan stok. Buat Purchase Order untuk memenuhi kebutuhan.</span>
            @else
                <span class="text-sm text-green-500">✅ Semua material tersedia untuk seluruh Work Order aktif.</span>
            @endif
        </div>
        @else
        <div class="px-6 py-12 text-center text-gray-400 dark:text-slate-500">
            Tidak ada Work Order aktif yang memiliki BOM. Buat WO dengan BOM terlebih dahulu.
        </div>
        @endif
    </div>
    @endif
</x-app-layout>
