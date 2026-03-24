<x-app-layout>
    <x-slot name="header">Penawaran Harga (Quotation)</x-slot>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Draft</p>
            <p class="text-2xl font-bold text-gray-500">{{ $stats['draft'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Terkirim</p>
            <p class="text-2xl font-bold text-blue-500">{{ $stats['sent'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Diterima</p>
            <p class="text-2xl font-bold text-green-500">{{ $stats['accepted'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Kadaluarsa</p>
            <p class="text-2xl font-bold text-red-500">{{ $stats['expired'] }}</p>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-2 flex-1">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor QT / customer..."
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="status" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                <option value="">Semua Status</option>
                @foreach(['draft'=>'Draft','sent'=>'Terkirim','accepted'=>'Diterima','rejected'=>'Ditolak','expired'=>'Kadaluarsa'] as $v=>$l)
                <option value="{{ $v }}" @selected(request('status')===$v)>{{ $l }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
        </form>
        <button onclick="document.getElementById('modal-create-qt').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Buat Penawaran</button>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nomor</th>
                        <th class="px-4 py-3 text-left">Customer</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Berlaku Hingga</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($quotations as $qt)
                    @php
                        $expired = in_array($qt->status, ['draft','sent']) && $qt->valid_until && $qt->valid_until < today();
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5 {{ $expired ? 'opacity-60' : '' }}">
                        <td class="px-4 py-3 font-mono text-xs font-medium text-gray-900 dark:text-white">
                            <a href="{{ route('quotations.show', $qt) }}" class="hover:text-blue-500">{{ $qt->number }}</a>
                        </td>
                        <td class="px-4 py-3 text-gray-700 dark:text-slate-300">{{ $qt->customer->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">Rp {{ number_format($qt->total, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $colors = ['draft'=>'gray','sent'=>'blue','accepted'=>'green','rejected'=>'red','expired'=>'orange'];
                                $labels = ['draft'=>'Draft','sent'=>'Terkirim','accepted'=>'Diterima','rejected'=>'Ditolak','expired'=>'Kadaluarsa'];
                                $status = $expired ? 'expired' : $qt->status;
                                $c = $colors[$status] ?? 'gray';
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs bg-{{ $c }}-100 text-{{ $c }}-700 dark:bg-{{ $c }}-500/20 dark:text-{{ $c }}-400">
                                {{ $labels[$status] ?? $status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell text-xs {{ $expired ? 'text-red-500' : 'text-gray-500 dark:text-slate-400' }}">
                            {{ $qt->valid_until?->format('d M Y') ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('quotations.show', $qt) }}" class="text-xs px-2 py-1 border border-gray-200 dark:border-white/10 rounded-lg text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">
                                    Detail
                                </a>
                                @if(in_array($qt->status, ['draft','sent']) && !$expired)
                                <button onclick="openEditQt({{ $qt->id }}, {{ $qt->customer_id }}, {{ $qt->valid_until ? $qt->date->diffInDays($qt->valid_until) : 7 }}, {{ $qt->discount }}, '{{ addslashes($qt->notes ?? '') }}', @json($qt->items))"
                                    class="text-xs px-2 py-1 border border-gray-200 dark:border-white/10 rounded-lg text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('quotations.convert', $qt) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs px-2 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700"
                                        onclick="return confirm('Konversi ke Sales Order?')">
                                        → SO
                                    </button>
                                </form>
                                @endif
                                @if($qt->status !== 'accepted')
                                <form method="POST" action="{{ route('quotations.destroy', $qt) }}" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs px-2 py-1 text-red-500 hover:text-red-700"
                                        onclick="return confirm('Hapus penawaran ini?')">✕</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada penawaran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($quotations->hasPages())
        <div class="px-4 py-3 border-t border-gray-100 dark:border-white/5">{{ $quotations->links() }}</div>
        @endif
    </div>

    {{-- Modal Buat Penawaran --}}
    <div id="modal-create-qt" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10 sticky top-0 bg-white dark:bg-[#1e293b]">
                <h3 class="font-semibold text-gray-900 dark:text-white">Buat Penawaran Harga</h3>
                <button onclick="document.getElementById('modal-create-qt').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('quotations.store') }}" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Customer *</label>
                        <select name="customer_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Customer --</option>
                            @foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Berlaku (hari) *</label>
                        <input type="number" name="valid_days" value="7" required min="1" max="365"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                {{-- Items --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-medium text-gray-600 dark:text-slate-400">Item Penawaran *</label>
                        <button type="button" onclick="addQtItem()" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">+ Tambah Item</button>
                    </div>
                    <div id="qt-items" class="space-y-2">
                        <div class="qt-item grid grid-cols-12 gap-2 items-center">
                            <div class="col-span-4">
                                <select name="items[0][product_id]" onchange="fillDesc(this,0)" class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">-- Produk (opsional) --</option>
                                    @foreach($products as $p)<option value="{{ $p->id }}" data-name="{{ $p->name }}" data-price="{{ $p->price_sell }}">{{ $p->name }}</option>@endforeach
                                </select>
                            </div>
                            <div class="col-span-3">
                                <input type="text" name="items[0][description]" id="desc-0" placeholder="Deskripsi *" required class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="col-span-2">
                                <input type="number" name="items[0][quantity]" placeholder="Qty" min="0.001" step="0.001" required class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="col-span-2">
                                <input type="number" name="items[0][price]" id="price-0" placeholder="Harga" min="0" required class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="col-span-1 text-center">
                                <button type="button" onclick="this.closest('.qt-item').remove()" class="text-red-500 hover:text-red-700 text-lg leading-none">×</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Diskon (Rp)</label>
                        <input type="number" name="discount" min="0" step="1000" value="0"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Catatan</label>
                        <input type="text" name="notes" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-create-qt').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Buat Penawaran</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit Penawaran --}}
    <div id="modal-edit-qt" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10 sticky top-0 bg-white dark:bg-[#1e293b]">
                <h3 class="font-semibold text-gray-900 dark:text-white">Edit Penawaran</h3>
                <button onclick="document.getElementById('modal-edit-qt').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form id="form-edit-qt" method="POST" class="p-6 space-y-4">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Customer *</label>
                        <select id="eq-customer" name="customer_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Customer --</option>
                            @foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Berlaku (hari) *</label>
                        <input type="number" id="eq-valid-days" name="valid_days" value="7" required min="1" max="365"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-medium text-gray-600 dark:text-slate-400">Item Penawaran *</label>
                        <button type="button" onclick="addEqItem()" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">+ Tambah Item</button>
                    </div>
                    <div id="eq-items" class="space-y-2"></div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Diskon (Rp)</label>
                        <input type="number" id="eq-discount" name="discount" min="0" step="1000" value="0"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Catatan</label>
                        <input type="text" id="eq-notes" name="notes" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-edit-qt').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    let qtItemCount = 1;
    let eqItemCount = 0;
    const productOpts = @json($products->map(function($p) { return ['id'=>$p->id,'name'=>$p->name,'price'=>$p->price_sell]; }));

    function fillDesc(sel, idx) {
        const opt = sel.options[sel.selectedIndex];
        const desc = document.getElementById('desc-' + idx);
        const price = document.getElementById('price-' + idx);
        if (opt.value) {
            if (desc) desc.value = opt.dataset.name;
            if (price) price.value = opt.dataset.price;
        }
    }

    function buildItemRow(prefix, i, item = null) {
        const opts = productOpts.map(p =>
            `<option value="${p.id}" data-name="${p.name}" data-price="${p.price}" ${item && item.product_id == p.id ? 'selected' : ''}>${p.name}</option>`
        ).join('');
        const div = document.createElement('div');
        div.className = `${prefix}-item grid grid-cols-12 gap-2 items-center`;
        div.innerHTML = `
            <div class="col-span-4">
                <select name="items[${i}][product_id]" onchange="fillDesc(this,'${prefix}-${i}')" class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Produk (opsional) --</option>${opts}
                </select>
            </div>
            <div class="col-span-3">
                <input type="text" name="items[${i}][description]" id="desc-${prefix}-${i}" placeholder="Deskripsi *" required value="${item ? item.description : ''}"
                    class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="col-span-2">
                <input type="number" name="items[${i}][quantity]" placeholder="Qty" min="0.001" step="0.001" required value="${item ? item.quantity : ''}"
                    class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="col-span-2">
                <input type="number" name="items[${i}][price]" id="price-${prefix}-${i}" placeholder="Harga" min="0" required value="${item ? item.price : ''}"
                    class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="col-span-1 text-center">
                <button type="button" onclick="this.closest('.${prefix}-item').remove()" class="text-red-500 hover:text-red-700 text-lg leading-none">×</button>
            </div>`;
        return div;
    }

    function addQtItem() {
        const i = qtItemCount++;
        document.getElementById('qt-items').appendChild(buildItemRow('qt', i));
    }

    function addEqItem(item = null) {
        const i = eqItemCount++;
        document.getElementById('eq-items').appendChild(buildItemRow('eq', i, item));
    }

    function openEditQt(id, customerId, validDays, discount, notes, items) {
        const form = document.getElementById('form-edit-qt');
        form.action = '{{ route("quotations.index") }}/' + id;
        document.getElementById('eq-customer').value = customerId;
        document.getElementById('eq-valid-days').value = validDays;
        document.getElementById('eq-discount').value = discount;
        document.getElementById('eq-notes').value = notes;

        // Reset items
        eqItemCount = 0;
        document.getElementById('eq-items').innerHTML = '';
        items.forEach(item => addEqItem(item));

        document.getElementById('modal-edit-qt').classList.remove('hidden');
    }
    </script>
    @endpush
</x-app-layout>
