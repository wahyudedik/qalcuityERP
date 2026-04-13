<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <span>Material Requirement Planning (MRP)</span>
            <div class="flex gap-2">
                <a href="{{ route('manufacturing.mrp.accuracy') }}"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    📊 Accuracy Dashboard
                </a>
                @if (isset($planningReport) && $planningReport['status'] === 'success')
                    <form method="POST" action="{{ route('manufacturing.mrp.export-pdf') }}" target="_blank"
                        class="inline">
                        @csrf
                        @if (request('bom_id'))
                            <input type="hidden" name="bom_id" value="{{ request('bom_id') }}">
                        @endif
                        <input type="hidden" name="quantity" value="{{ $quantity ?? 1 }}">
                        <button type="submit"
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                            📄 Export PDF Report
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    {{-- Dashboard Summary --}}
    @if (isset($dashboardData))
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                <div class="text-sm text-gray-500 dark:text-slate-400">Work Orders Aktif</div>
                <div class="text-2xl font-bold text-blue-600">{{ $dashboardData['pending_work_orders'] }}</div>
            </div>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                <div class="text-sm text-gray-500 dark:text-slate-400">PO Pending</div>
                <div class="text-2xl font-bold text-purple-600">{{ $dashboardData['pending_purchase_orders'] }}</div>
            </div>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                <div class="text-sm text-gray-500 dark:text-slate-400">Stok Rendah</div>
                <div class="text-2xl font-bold text-orange-600">{{ $dashboardData['low_stock_items'] }}</div>
            </div>
            @if (isset($dashboardData['planning']['summary']))
                <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                    <div class="text-sm text-gray-500 dark:text-slate-400">MRP Health</div>
                    <div
                        class="text-2xl font-bold {{ $dashboardData['planning']['summary']['health_percentage'] >= 80 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $dashboardData['planning']['summary']['health_percentage'] }}%
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Planning Report Summary --}}
    @if (isset($planningReport) && $planningReport['status'] === 'success')
        <div
            class="bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 rounded-2xl border border-blue-200 dark:border-blue-800 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-900 dark:text-white text-lg">📊 Planning Report Summary</h3>
                <span class="text-xs text-gray-500 dark:text-slate-400">Generated:
                    {{ \Carbon\Carbon::parse($planningReport['generated_at'])->format('d M Y H:i') }}</span>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-4">
                <div>
                    <div class="text-xs text-gray-500 dark:text-slate-400">Total Items</div>
                    <div class="text-xl font-bold">{{ $planningReport['summary']['total_items'] }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-slate-400">Shortage</div>
                    <div class="text-xl font-bold text-red-600">{{ $planningReport['summary']['items_with_shortage'] }}
                    </div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-slate-400">Critical</div>
                    <div class="text-xl font-bold text-red-700">{{ $planningReport['summary']['critical_items'] }}
                    </div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-slate-400">High Priority</div>
                    <div class="text-xl font-bold text-orange-600">
                        {{ $planningReport['summary']['high_priority_items'] }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 dark:text-slate-400">Est. Shortage Value</div>
                    <div class="text-xl font-bold text-gray-900 dark:text-white">Rp
                        {{ number_format($planningReport['summary']['estimated_shortage_value'], 0, ',', '.') }}</div>
                </div>
            </div>

            @if ($planningReport['summary']['critical_items'] > 0)
                <div class="bg-red-100 dark:bg-red-900/30 border border-red-300 dark:border-red-700 rounded-lg p-3">
                    <span class="text-sm text-red-800 dark:text-red-200">⚠️ Ada
                        {{ $planningReport['summary']['critical_items'] }} item critical yang perlu segera
                        diorder!</span>
                </div>
            @endif
        </div>
    @endif

    {{-- Single BOM Calculator --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 mb-6">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Kalkulasi Kebutuhan Material</h3>
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <select name="bom_id"
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                <option value="">-- Pilih BOM --</option>
                @foreach ($boms as $b)
                    <option value="{{ $b->id }}" @selected(request('bom_id') == $b->id)>{{ $b->name }}
                        ({{ $b->product->name ?? '-' }})
                    </option>
                @endforeach
            </select>
            <input type="number" name="quantity" min="1" step="1" value="{{ $quantity }}"
                placeholder="Jumlah produksi"
                class="w-32 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
            <button type="submit"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Hitung</button>
            <button type="submit" name="full_mrp" value="1"
                class="px-4 py-2 text-sm bg-purple-600 text-white rounded-xl hover:bg-purple-700">Full MRP (Semua
                WO)</button>
        </form>
    </div>

    {{-- Single BOM Results --}}
    @if ($results !== null)
        <div
            class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden mb-6">
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
                        @foreach ($results as $r)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                <td class="px-4 py-3 text-gray-900 dark:text-white">
                                    @if ($r['level'] > 0)
                                        <span class="text-gray-400">{{ str_repeat('└─ ', $r['level']) }}</span>
                                    @endif
                                    {{ $r['product_name'] }}
                                </td>
                                <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">
                                    {{ number_format($r['required'], 2, ',', '.') }} {{ $r['unit'] }}</td>
                                <td class="px-4 py-3 text-right text-gray-700 dark:text-slate-300">
                                    {{ number_format($r['on_hand'], 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right text-gray-700 dark:text-slate-300">
                                    {{ number_format($r['on_order'], 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right text-gray-700 dark:text-slate-300">
                                    {{ number_format($r['other_demand'], 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right text-gray-900 dark:text-white">
                                    {{ number_format($r['available'], 2, ',', '.') }}</td>
                                <td
                                    class="px-4 py-3 text-right font-bold {{ $r['shortage'] > 0 ? 'text-red-500' : 'text-green-500' }}">
                                    {{ $r['shortage'] > 0 ? number_format($r['shortage'], 2, ',', '.') : '—' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($r['shortage'] > 0)
                                        <span
                                            class="px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400">Kurang</span>
                                    @else
                                        <span
                                            class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400">Cukup</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @php $totalShortage = collect($results)->sum('shortage'); @endphp
            <div class="px-6 py-3 border-t border-gray-100 dark:border-white/10 flex items-center gap-4">
                @if ($totalShortage > 0)
                    <span class="text-sm text-red-500">⚠️ Ada
                        {{ collect($results)->where('shortage', '>', 0)->count() }} material yang kurang stok.</span>
                @else
                    <span class="text-sm text-green-500">✅ Semua material tersedia untuk produksi.</span>
                @endif
            </div>
        </div>
    @endif

    {{-- Full MRP Results --}}
    @if ($fullMrp !== null)
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Full MRP — Semua Work Order Aktif</h3>
                <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Agregasi kebutuhan material dari semua WO
                    pending/in-progress yang memiliki BOM</p>
            </div>
            @if (count($fullMrp) > 0)
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
                            @foreach ($fullMrp as $r)
                                <tr
                                    class="hover:bg-gray-50 dark:hover:bg-white/5 {{ $r['shortage'] > 0 ? 'bg-red-50/50 dark:bg-red-500/5' : '' }}">
                                    <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $r['product_name'] }}</td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">
                                        {{ number_format($r['required'], 2, ',', '.') }} {{ $r['unit'] }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700 dark:text-slate-300">
                                        {{ number_format($r['on_hand'], 2, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700 dark:text-slate-300">
                                        {{ number_format($r['on_order'], 2, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right text-gray-900 dark:text-white">
                                        {{ number_format($r['available'], 2, ',', '.') }}</td>
                                    <td
                                        class="px-4 py-3 text-right font-bold {{ $r['shortage'] > 0 ? 'text-red-500' : 'text-green-500' }}">
                                        {{ $r['shortage'] > 0 ? number_format($r['shortage'], 2, ',', '.') : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-500 dark:text-slate-400">
                                        {{ implode(', ', array_slice($r['wo_refs'], 0, 3)) }}
                                        @if (count($r['wo_refs']) > 3)
                                            <span class="text-gray-400">+{{ count($r['wo_refs']) - 3 }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($r['shortage'] > 0)
                                            <span
                                                class="px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400">Kurang</span>
                                        @else
                                            <span
                                                class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400">Cukup</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @php $shortageCount = collect($fullMrp)->where('shortage', '>', 0)->count(); @endphp
                <div class="px-6 py-3 border-t border-gray-100 dark:border-white/10">
                    @if ($shortageCount > 0)
                        <span class="text-sm text-red-500">⚠️ {{ $shortageCount }} material kekurangan stok. Buat
                            Purchase Order untuk memenuhi kebutuhan.</span>
                    @else
                        <span class="text-sm text-green-500">✅ Semua material tersedia untuk seluruh Work Order
                            aktif.</span>
                    @endif
                </div>
            @else
                <div class="px-6 py-12 text-center text-gray-400 dark:text-slate-500">
                    Tidak ada Work Order aktif yang memiliki BOM. Buat WO dengan BOM terlebih dahulu.
                </div>
            @endif
        </div>
    @endif

    {{-- Planning Report Detail --}}
    @if (isset($planningReport) && $planningReport['status'] === 'success' && count($planningReport['items']) > 0)
        <div
            class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden mt-6">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10 flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white">📋 Planning Recommendations</h3>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Prioritas berdasarkan shortage, lead
                        time, dan quantity</p>
                </div>
                <div class="flex gap-2">
                    <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs">Critical</span>
                    <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded text-xs">High</span>
                    <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded text-xs">Medium</span>
                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs">Low</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Priority</th>
                            <th class="px-4 py-3 text-left">Material</th>
                            <th class="px-4 py-3 text-right">Shortage</th>
                            <th class="px-4 py-3 text-center">Lead Time</th>
                            <th class="px-4 py-3 text-center">Order By</th>
                            <th class="px-4 py-3 text-left">Supplier</th>
                            <th class="px-4 py-3 text-center">Action</th>
                            <th class="px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @foreach ($planningReport['items'] as $item)
                            @if ($item['has_shortage'])
                                <tr
                                    class="hover:bg-gray-50 dark:hover:bg-white/5 {{ $item['action']['urgency'] === 'critical' ? 'bg-red-50/50 dark:bg-red-500/5' : '' }}">
                                    <td class="px-4 py-3 text-center">
                                        <div
                                            class="font-bold {{ $item['priority'] >= 70 ? 'text-red-600' : ($item['priority'] >= 50 ? 'text-orange-600' : 'text-yellow-600') }}">
                                            {{ $item['priority'] }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-900 dark:text-white font-medium">
                                        {{ $item['product_name'] }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-red-600">
                                        {{ number_format($item['shortage'], 2, ',', '.') }} {{ $item['unit'] }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span
                                            class="px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded text-xs">
                                            {{ $item['lead_time_days'] }} days
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center text-xs text-gray-700 dark:text-slate-300">
                                        {{ $item['order_by_date_formatted'] }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-600 dark:text-slate-400">
                                        @if ($item['supplier_info'])
                                            <div class="font-medium">{{ $item['supplier_info']['name'] }}</div>
                                            @if ($item['supplier_info']['phone'])
                                                <div class="text-gray-400">{{ $item['supplier_info']['phone'] }}</div>
                                            @endif
                                        @else
                                            <span class="text-gray-400">No history</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($item['action']['type'] === 'purchase_recommended')
                                            <button
                                                onclick="createAutoPO({{ $item['product_id'] }}, {{ $item['shortage'] }}, '{{ $item['product_name'] }}')"
                                                class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs">
                                                Create PO
                                            </button>
                                        @else
                                            <span class="text-gray-400 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($item['action']['urgency'] === 'critical')
                                            <span
                                                class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400 font-bold">CRITICAL</span>
                                        @elseif($item['action']['urgency'] === 'high')
                                            <span
                                                class="px-2 py-1 rounded-full text-xs bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400">HIGH</span>
                                        @elseif($item['action']['urgency'] === 'medium')
                                            <span
                                                class="px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-400">MEDIUM</span>
                                        @else
                                            <span
                                                class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400">LOW</span>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Auto PO Modal --}}
    <dialog id="autoPOModal" class="modal">
        <div class="modal-box max-w-lg">
            <h3 class="font-bold text-lg mb-4">Create Purchase Order</h3>
            <form id="autoPOForm" method="POST" action="{{ route('manufacturing.mrp.create-po') }}">
                @csrf
                <input type="hidden" id="po_product_id" name="product_id">

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Product</label>
                        <input type="text" id="po_product_name" readonly
                            class="w-full border rounded px-3 py-2 bg-gray-100 dark:bg-gray-700">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Quantity *</label>
                        <input type="number" id="po_quantity" name="quantity" step="0.01" required
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Supplier</label>
                        <select name="supplier_id" class="w-full border rounded px-3 py-2">
                            <option value="">-- Select Supplier --</option>
                            @php
                                $suppliers = \App\Models\Supplier::where('tenant_id', auth()->user()->tenant_id)
                                    ->where('is_active', true)
                                    ->orderBy('name')
                                    ->get();
                            @endphp
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Expected Date</label>
                        <input type="date" name="expected_date" value="{{ now()->addDays(7)->format('Y-m-d') }}"
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Notes</label>
                        <textarea name="notes" rows="2" class="w-full border rounded px-3 py-2"
                            placeholder="Auto-generated from MRP Planning"></textarea>
                    </div>
                </div>

                <div class="modal-action">
                    <button type="button" onclick="document.getElementById('autoPOModal').close()"
                        class="btn">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create PO</button>
                </div>
            </form>
        </div>
    </dialog>

    <script>
        function createAutoPO(productId, quantity, productName) {
            document.getElementById('po_product_id').value = productId;
            document.getElementById('po_quantity').value = Math.ceil(quantity);
            document.getElementById('po_product_name').value = productName;
            document.getElementById('autoPOModal').showModal();
        }
    </script>
</x-app-layout>
