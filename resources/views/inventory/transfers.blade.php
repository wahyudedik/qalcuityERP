<x-app-layout>
    <x-slot name="header">Transfer Stok Antar Gudang</x-slot>

    <div class="space-y-5">

        @if(session('success'))
            <div class="bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-4 py-3 text-sm">
                <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Form Transfer --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h2 class="font-semibold text-gray-900 mb-4">Buat Transfer</h2>
                <form method="POST" action="{{ route('inventory.transfers.store') }}" id="transfer-form" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Dari Gudang <span class="text-red-400">*</span></label>
                        <select name="from_warehouse_id" id="from_wh" required
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih gudang asal...</option>
                            @foreach($warehouses ?? [] as $w)
                                <option value="{{ $w->id }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Ke Gudang <span class="text-red-400">*</span></label>
                        <select name="to_warehouse_id" id="to_wh" required
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih gudang tujuan...</option>
                            @foreach($warehouses ?? [] as $w)
                                <option value="{{ $w->id }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Catatan</label>
                        <input type="text" name="notes" placeholder="Opsional"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- Items --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs text-gray-500">Produk yang Ditransfer</label>
                            <button type="button" id="add-item" class="text-xs px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">+ Tambah</button>
                        </div>
                        <div id="items-container" class="space-y-2">
                            <div class="item-row flex gap-2">
                                <select name="items[0][product_id]" required
                                    class="flex-1 bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none">
                                    <option value="">Produk...</option>
                                    @foreach($products ?? [] as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                                <input type="number" name="items[0][quantity]" min="1" value="1" required placeholder="Qty"
                                    class="w-20 bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none">
                                <button type="button" class="remove-item text-red-400 hover:text-red-300 transition text-sm">✕</button>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">
                        Proses Transfer
                    </button>
                </form>
            </div>

            {{-- Riwayat Transfer --}}
            <div class="lg:col-span-2 space-y-4">
                {{-- Filter --}}
                <form method="GET" class="bg-white rounded-2xl border border-gray-200 p-4 flex flex-wrap gap-3">
                    <select name="warehouse_id" class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none">
                        <option value="">Semua Gudang</option>
                        @foreach($warehouses ?? [] as $w)
                            <option value="{{ $w->id }}" {{ request('warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                        @endforeach
                    </select>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none">
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none">
                    <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl text-sm hover:bg-gray-200 transition">Filter</button>
                </form>

                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    @if($transfers->isEmpty())
                        <div class="px-6 py-12 text-center text-gray-400 text-sm">Belum ada riwayat transfer.</div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-100 text-xs text-gray-500">
                                        <th class="px-4 py-3 text-left">Ref</th>
                                        <th class="px-4 py-3 text-left">Produk</th>
                                        <th class="px-4 py-3 text-left">Dari</th>
                                        <th class="px-4 py-3 text-left">Ke</th>
                                        <th class="px-4 py-3 text-right">Qty</th>
                                        <th class="px-4 py-3 text-left">Oleh</th>
                                        <th class="px-4 py-3 text-left">Waktu</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @foreach($transfers ?? [] as $t)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-4 py-3 font-mono text-xs text-blue-400">{{ $t->reference ?? '-' }}</td>
                                            <td class="px-4 py-3 text-gray-700">{{ $t->product?->name ?? '-' }}</td>
                                            <td class="px-4 py-3 text-xs text-gray-500">{{ $t->warehouse?->name ?? '-' }}</td>
                                            <td class="px-4 py-3 text-xs text-gray-500">{{ $t->toWarehouse?->name ?? '-' }}</td>
                                            <td class="px-4 py-3 text-right font-medium text-gray-900">{{ $t->quantity }}</td>
                                            <td class="px-4 py-3 text-xs text-gray-500">{{ $t->user?->name ?? '-' }}</td>
                                            <td class="px-4 py-3 text-xs text-gray-500">{{ $t->created_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($transfers->hasPages())
                            <div class="px-6 py-4 border-t border-gray-100">{{ $transfers->links() }}</div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
    (function() {
        let idx = 1;
        const products = @json($products->map(function($p) { return ['id' => $p->id, 'name' => $p->name]; }));

        function buildSelect(i) {
            return `<option value="">Produk...</option>` +
                products.map(p => `<option value="${p.id}">${p.name}</option>`).join('');
        }

        document.getElementById('add-item').addEventListener('click', function() {
            const row = document.createElement('div');
            row.className = 'item-row flex gap-2';
            row.innerHTML = `
                <select name="items[${idx}][product_id]" required
                    class="flex-1 bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none">
                    ${buildSelect(idx)}
                </select>
                <input type="number" name="items[${idx}][quantity]" min="1" value="1" required placeholder="Qty"
                    class="w-20 bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none">
                <button type="button" class="remove-item text-red-400 hover:text-red-300 transition text-sm">✕</button>`;
            document.getElementById('items-container').appendChild(row);
            row.querySelector('.remove-item').addEventListener('click', () => {
                if (document.querySelectorAll('.item-row').length > 1) row.remove();
            });
            idx++;
        });

        document.querySelectorAll('.remove-item').forEach(btn => {
            btn.addEventListener('click', function() {
                if (document.querySelectorAll('.item-row').length > 1) btn.closest('.item-row').remove();
            });
        });
    })();
    </script>
</x-app-layout>
