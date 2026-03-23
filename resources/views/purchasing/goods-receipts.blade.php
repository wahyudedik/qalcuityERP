<x-app-layout>
    <x-slot name="title">Goods Receipt — Qalcuity ERP</x-slot>
    <x-slot name="header">Goods Receipt (GR)</x-slot>
    <x-slot name="topbarActions">
        @if($openPos->count())
        <button onclick="document.getElementById('modal-add-gr').classList.remove('hidden')"
            class="flex items-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Catat Penerimaan
        </button>
        @endif
    </x-slot>

    {{-- GR List --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nomor GR</th>
                        <th class="px-4 py-3 text-left">PO / Supplier</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Gudang</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Surat Jalan</th>
                        <th class="px-4 py-3 text-center">Tgl Terima</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($receipts as $gr)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3">
                            <p class="font-mono text-xs font-semibold text-gray-900 dark:text-white">{{ $gr->number }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900 dark:text-white text-xs">{{ $gr->purchaseOrder->number }}</p>
                            <p class="text-xs text-gray-500 dark:text-slate-400">{{ $gr->purchaseOrder->supplier->name }}</p>
                        </td>
                        <td class="px-4 py-3 hidden md:table-cell text-gray-500 dark:text-slate-400">{{ $gr->warehouse->name }}</td>
                        <td class="px-4 py-3 hidden lg:table-cell text-gray-500 dark:text-slate-400 text-xs">{{ $gr->delivery_note ?? '—' }}</td>
                        <td class="px-4 py-3 text-center text-xs text-gray-500 dark:text-slate-400">{{ $gr->receipt_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs {{ $gr->status === 'confirmed' ? 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400' : 'bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-slate-400' }}">
                                {{ $gr->status === 'confirmed' ? 'Dikonfirmasi' : 'Draft' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada Goods Receipt.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($receipts->hasPages())
        <div class="px-4 py-3 border-t border-gray-100 dark:border-white/5">{{ $receipts->links() }}</div>
        @endif
    </div>

    {{-- Modal Catat GR --}}
    <div id="modal-add-gr" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-3xl shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10 sticky top-0 bg-white dark:bg-[#1e293b]">
                <h3 class="font-semibold text-gray-900 dark:text-white">Catat Penerimaan Barang</h3>
                <button onclick="document.getElementById('modal-add-gr').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="{{ route('purchasing.goods-receipts.store') }}" class="p-6 space-y-5" id="form-gr">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Purchase Order *</label>
                        <select name="purchase_order_id" id="gr-po-select" required onchange="loadPoItems()"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                            <option value="">Pilih PO...</option>
                            @foreach($openPos as $po)
                                <option value="{{ $po->id }}" data-items="{{ json_encode($po->items->map(fn($i) => ['id' => $i->id, 'product_id' => $i->product_id, 'product' => $i->product->name ?? '-', 'qty_ordered' => $i->quantity_ordered, 'qty_received' => $i->quantity_received, 'remaining' => $i->quantity_ordered - $i->quantity_received])) }}">
                                    {{ $po->number }} — {{ $po->supplier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Gudang *</label>
                        <select name="warehouse_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                            <option value="">Pilih gudang...</option>
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanggal Terima *</label>
                        <input type="date" name="receipt_date" value="{{ today()->format('Y-m-d') }}" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">No. Surat Jalan</label>
                        <input type="text" name="delivery_note"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Catatan</label>
                        <input type="text" name="notes"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    </div>
                </div>

                {{-- Dynamic items table --}}
                <div id="gr-items-wrap" class="hidden">
                    <p class="text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wide mb-2">Detail Penerimaan</p>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400">
                                <tr>
                                    <th class="px-3 py-2 text-left">Produk</th>
                                    <th class="px-3 py-2 text-center">Dipesan</th>
                                    <th class="px-3 py-2 text-center">Sisa</th>
                                    <th class="px-3 py-2 text-center">Diterima</th>
                                    <th class="px-3 py-2 text-center">Diterima (QC)</th>
                                    <th class="px-3 py-2 text-center">Ditolak</th>
                                </tr>
                            </thead>
                            <tbody id="gr-items-body" class="divide-y divide-gray-100 dark:divide-white/5"></tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-add-gr').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan GR</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function loadPoItems() {
        const sel = document.getElementById('gr-po-select');
        const opt = sel.options[sel.selectedIndex];
        if (!opt.value) { document.getElementById('gr-items-wrap').classList.add('hidden'); return; }

        const items = JSON.parse(opt.dataset.items || '[]');
        const tbody = document.getElementById('gr-items-body');
        tbody.innerHTML = '';

        items.forEach((item, i) => {
            const remaining = Math.max(0, item.remaining);
            tbody.innerHTML += `
            <tr>
                <td class="px-3 py-2 text-gray-900 dark:text-white">${item.product}
                    <input type="hidden" name="items[${i}][purchase_order_item_id]" value="${item.id}">
                    <input type="hidden" name="items[${i}][product_id]" value="${item.product_id}">
                </td>
                <td class="px-3 py-2 text-center text-gray-500 dark:text-slate-400">${item.qty_ordered}</td>
                <td class="px-3 py-2 text-center font-semibold ${remaining > 0 ? 'text-amber-600' : 'text-green-600'}">${remaining}</td>
                <td class="px-3 py-2 text-center">
                    <input type="number" name="items[${i}][quantity_received]" value="${remaining}" min="0" max="${remaining}" step="0.01"
                        oninput="syncAccepted(this, ${i})"
                        class="w-20 px-2 py-1 text-sm text-center rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                </td>
                <td class="px-3 py-2 text-center">
                    <input type="number" id="accepted-${i}" name="items[${i}][quantity_accepted]" value="${remaining}" min="0" step="0.01"
                        oninput="syncRejected(this, ${i})"
                        class="w-20 px-2 py-1 text-sm text-center rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                </td>
                <td class="px-3 py-2 text-center">
                    <input type="number" id="rejected-${i}" name="items[${i}][quantity_rejected]" value="0" min="0" step="0.01" readonly
                        class="w-20 px-2 py-1 text-sm text-center rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-red-500 dark:text-red-400">
                </td>
            </tr>`;
        });

        document.getElementById('gr-items-wrap').classList.remove('hidden');
    }

    function syncAccepted(input, i) {
        const accepted = document.getElementById('accepted-' + i);
        if (parseFloat(accepted.value) > parseFloat(input.value)) {
            accepted.value = input.value;
        }
        syncRejected(accepted, i);
    }

    function syncRejected(acceptedInput, i) {
        const receivedInput = document.querySelector(`[name="items[${i}][quantity_received]"]`);
        const rejected = document.getElementById('rejected-' + i);
        const diff = parseFloat(receivedInput.value || 0) - parseFloat(acceptedInput.value || 0);
        rejected.value = Math.max(0, diff).toFixed(2);
    }
    </script>
    @endpush
</x-app-layout>
