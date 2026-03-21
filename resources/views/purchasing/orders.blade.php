<x-app-layout>
    <x-slot name="header">Purchase Order</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 mb-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-2 flex-1">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor PO / supplier..."
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="status" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                <option value="">Semua Status</option>
                @foreach(['draft'=>'Draft','sent'=>'Terkirim','partial'=>'Sebagian','received'=>'Diterima','cancelled'=>'Dibatalkan'] as $v=>$l)
                <option value="{{ $v }}" @selected(request('status')===$v)>{{ $l }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
        </form>
        <div class="flex gap-2">
            <a href="{{ route('purchasing.suppliers') }}" class="px-3 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Supplier</a>
            <button onclick="document.getElementById('modal-create-po').classList.remove('hidden')"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Buat PO</button>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nomor PO</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Supplier</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Gudang</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Tanggal</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($orders as $po)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3 font-mono text-xs font-medium text-gray-900 dark:text-white">{{ $po->number }}</td>
                        <td class="px-4 py-3 hidden sm:table-cell text-gray-700 dark:text-slate-300">{{ $po->supplier->name ?? '-' }}</td>
                        <td class="px-4 py-3 hidden md:table-cell text-gray-500 dark:text-slate-400">{{ $po->warehouse->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">Rp {{ number_format($po->total,0,',','.') }}</td>
                        <td class="px-4 py-3 text-center">
                            @php $colors = ['draft'=>'gray','sent'=>'blue','partial'=>'yellow','received'=>'green','cancelled'=>'red']; $c = $colors[$po->status] ?? 'gray'; @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs bg-{{ $c }}-100 text-{{ $c }}-700 dark:bg-{{ $c }}-500/20 dark:text-{{ $c }}-400">
                                {{ ucfirst($po->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell text-xs text-gray-500 dark:text-slate-400">{{ $po->date->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($po->status !== 'received' && $po->status !== 'cancelled')
                            <form method="POST" action="{{ route('purchasing.orders.status', $po) }}" class="inline">
                                @csrf @method('PATCH')
                                <select name="status" onchange="this.form.submit()" class="text-xs px-2 py-1 rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                                    @foreach(['draft'=>'Draft','sent'=>'Terkirim','partial'=>'Sebagian','received'=>'Diterima','cancelled'=>'Batal'] as $v=>$l)
                                    <option value="{{ $v }}" @selected($po->status===$v)>{{ $l }}</option>
                                    @endforeach
                                </select>
                            </form>
                            @else
                            <span class="text-xs text-gray-400 dark:text-slate-500">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada purchase order.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
        <div class="px-4 py-3 border-t border-gray-100 dark:border-white/5">{{ $orders->links() }}</div>
        @endif
    </div>

    {{-- Modal Buat PO --}}
    <div id="modal-create-po" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10 sticky top-0 bg-white dark:bg-[#1e293b]">
                <h3 class="font-semibold text-gray-900 dark:text-white">Buat Purchase Order</h3>
                <button onclick="document.getElementById('modal-create-po').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('purchasing.orders.store') }}" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Supplier *</label>
                        <select name="supplier_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Supplier --</option>
                            @foreach($suppliers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Gudang Tujuan *</label>
                        <select name="warehouse_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Gudang --</option>
                            @foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanggal PO *</label>
                        <input type="date" name="date" value="{{ today()->format('Y-m-d') }}" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Estimasi Terima</label>
                        <input type="date" name="expected_date" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Pembayaran</label>
                        <select name="payment_type" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="cash">Cash</option>
                            <option value="credit">Tempo/Kredit</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Catatan</label>
                        <input type="text" name="notes" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                {{-- Items --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-medium text-gray-600 dark:text-slate-400">Item Produk *</label>
                        <button type="button" onclick="addPoItem()" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">+ Tambah Item</button>
                    </div>
                    <div id="po-items" class="space-y-2">
                        <div class="po-item grid grid-cols-12 gap-2 items-center">
                            <div class="col-span-5">
                                <select name="items[0][product_id]" required class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">-- Produk --</option>
                                    @foreach($products as $p)<option value="{{ $p->id }}" data-price="{{ $p->price_buy }}">{{ $p->name }}</option>@endforeach
                                </select>
                            </div>
                            <div class="col-span-3">
                                <input type="number" name="items[0][quantity]" placeholder="Qty" min="1" required class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="col-span-3">
                                <input type="number" name="items[0][price]" placeholder="Harga" min="0" required class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="col-span-1 text-center">
                                <button type="button" onclick="this.closest('.po-item').remove()" class="text-red-500 hover:text-red-700 text-lg leading-none">×</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-create-po').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Buat PO</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    let poItemCount = 1;
    const productOptions = `@foreach($products as $p)<option value="{{ $p->id }}" data-price="{{ $p->price_buy }}">{{ addslashes($p->name) }}</option>@endforeach`;

    function addPoItem() {
        const i = poItemCount++;
        const div = document.createElement('div');
        div.className = 'po-item grid grid-cols-12 gap-2 items-center';
        div.innerHTML = `
            <div class="col-span-5">
                <select name="items[${i}][product_id]" required class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Produk --</option>${productOptions}
                </select>
            </div>
            <div class="col-span-3">
                <input type="number" name="items[${i}][quantity]" placeholder="Qty" min="1" required class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="col-span-3">
                <input type="number" name="items[${i}][price]" placeholder="Harga" min="0" required class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="col-span-1 text-center">
                <button type="button" onclick="this.closest('.po-item').remove()" class="text-red-500 hover:text-red-700 text-lg leading-none">×</button>
            </div>`;
        document.getElementById('po-items').appendChild(div);
    }
    </script>
    @endpush
</x-app-layout>
