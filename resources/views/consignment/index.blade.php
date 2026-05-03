<x-app-layout>
    <x-slot name="header">Konsinyasi</x-slot>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Partner Aktif</p>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['partners'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Pengiriman Aktif</p>
            <p class="text-2xl font-bold text-blue-500">{{ $stats['active_shipments'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Nilai Titipan</p>
            <p class="text-lg font-bold text-gray-900">Rp {{ number_format($stats['consigned_value'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Belum Settle</p>
            <p class="text-lg font-bold text-amber-500">Rp {{ number_format($stats['pending_settlement'], 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <form method="GET" class="flex-1 flex flex-wrap gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor..."
                class="flex-1 min-w-[120px] px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="status" class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900">
                <option value="">Semua Status</option>
                @foreach(['draft'=>'Draft','shipped'=>'Dikirim','partial_sold'=>'Sebagian Terjual','settled'=>'Settled','returned'=>'Diretur'] as $v=>$l)
                <option value="{{ $v }}" @selected(request('status')===$v)>{{ $l }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
        </form>
        <div class="flex gap-2">
            <a href="{{ route('consignment.partners') }}" class="px-3 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Partner</a>
            @canmodule('consignment', 'create')
            <button onclick="document.getElementById('modal-ship').classList.remove('hidden')"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Kirim Titipan</button>
            @endcanmodule
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nomor</th>
                        <th class="px-4 py-3 text-left">Partner</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Tanggal</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Nilai Retail</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($shipments as $s)
                    @php
                        $sc = ['draft'=>'gray','shipped'=>'blue','partial_sold'=>'amber','settled'=>'green','returned'=>'purple'][$s->status] ?? 'gray';
                        $sl = ['draft'=>'Draft','shipped'=>'Dikirim','partial_sold'=>'Sebagian','settled'=>'Settled','returned'=>'Diretur'][$s->status] ?? $s->status;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs font-medium text-gray-900">
                            <a href="{{ route('consignment.shipments.show', $s) }}" class="hover:text-blue-500">{{ $s->number }}</a>
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ $s->partner->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-center hidden sm:table-cell text-xs text-gray-500">{{ $s->ship_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-right hidden md:table-cell text-gray-900">Rp {{ number_format($s->total_retail, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs bg-{{ $sc }}-100 text-{{ $sc }}-700 $sc }}-500/20 $sc }}-400">{{ $sl }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('consignment.shipments.show', $s) }}" class="text-xs px-2 py-1 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50">Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">Belum ada pengiriman konsinyasi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($shipments->hasPages())<div class="px-4 py-3 border-t border-gray-100">{{ $shipments->links() }}</div>@endif
    </div>

    {{-- Modal Create Shipment --}}
    <div id="modal-ship" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Kirim Stok Titipan</h3>
                <button onclick="document.getElementById('modal-ship').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="{{ route('consignment.shipments.store') }}" class="p-6 space-y-4">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900'; @endphp
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Partner *</label>
                        <select name="partner_id" required class="{{ $cls }}"><option value="">-- Pilih --</option>
                            @foreach($partners as $p)<option value="{{ $p->id }}">{{ $p->name }} ({{ $p->commission_pct }}%)</option>@endforeach
                        </select>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Gudang Asal *</label>
                        <select name="warehouse_id" required class="{{ $cls }}"><option value="">-- Pilih --</option>
                            @foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach
                        </select>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Kirim *</label>
                        <input type="date" name="ship_date" required value="{{ date('Y-m-d') }}" class="{{ $cls }}">
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-xs font-semibold text-gray-500 uppercase">Item Titipan</h4>
                        <button type="button" onclick="addItem()" class="text-xs px-2 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700">+ Item</button>
                    </div>
                    <div id="ship-items" class="space-y-2"></div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-ship').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Kirim</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    const prods = @json($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'price' => $p->price_sell]));
    let idx = 0;
    function addItem() {
        const i = idx++;
        const c = document.getElementById('ship-items');
        const d = document.createElement('div');
        d.className = 'grid grid-cols-12 gap-2 items-end'; d.id = 'si-' + i;
        const cls = 'w-full px-2 py-1.5 text-xs rounded-lg border border-gray-200 bg-gray-50 text-gray-900';
        let opts = '<option value="">Produk</option>';
        prods.forEach(p => { opts += `<option value="${p.id}" data-price="${p.price}">${p.name}</option>`; });
        d.innerHTML = `
            <div class="col-span-5"><select name="items[${i}][product_id]" required class="${cls}" onchange="setPrice(this,${i})">${opts}</select></div>
            <div class="col-span-3"><input type="number" name="items[${i}][quantity_sent]" required min="0.001" step="0.001" placeholder="Qty" class="${cls}"></div>
            <div class="col-span-3"><input type="number" name="items[${i}][retail_price]" id="rp-${i}" required min="0" step="100" placeholder="Harga Jual" class="${cls}"></div>
            <div class="col-span-1"><button type="button" onclick="document.getElementById('si-${i}').remove()" class="text-red-500 text-xs">✕</button></div>`;
        c.appendChild(d);
    }
    function setPrice(sel, i) {
        const opt = sel.options[sel.selectedIndex];
        document.getElementById('rp-' + i).value = opt.dataset.price || '';
    }
    addItem();
    </script>
    @endpush
</x-app-layout>
