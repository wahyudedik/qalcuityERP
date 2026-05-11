<x-app-layout>
    <x-slot name="header">Landed Cost</x-slot>

    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <form method="GET" class="flex-1 flex flex-wrap gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor / deskripsi..."
                class="flex-1 min-w-[150px] px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="status" class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900">
                <option value="">Semua Status</option>
                @foreach (['draft' => 'Draft', 'allocated' => 'Dialokasi', 'posted' => 'Diposting'] as $v => $l)
                    <option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>
                @endforeach
            </select>
            <button type="submit"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
        </form>
        @canmodule('landed_cost', 'create')
        <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Landed Cost</button>
        @endcanmodule
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nomor</th>
                        <th class="px-4 py-3 text-left">PO</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Deskripsi</th>
                        <th class="px-4 py-3 text-right">Total Biaya</th>
                        <th class="px-4 py-3 text-center">Metode</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($landedCosts as $lc)
                        @php
                            $sc =
                                ['draft' => 'gray', 'allocated' => 'amber', 'posted' => 'green'][$lc->status] ?? 'gray';
                            $sl =
                                ['draft' => 'Draft', 'allocated' => 'Dialokasi', 'posted' => 'Diposting'][
                                    $lc->status
                                ] ?? $lc->status;
                            $ml =
                                [
                                    'by_value' => 'Nilai',
                                    'by_quantity' => 'Qty',
                                    'by_weight' => 'Berat',
                                    'equal' => 'Rata',
                                ][$lc->allocation_method] ?? $lc->allocation_method;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono text-xs font-medium text-gray-900">
                                <a href="{{ route('landed-cost.show', $lc) }}"
                                    class="hover:text-blue-500">{{ $lc->number }}</a>
                            </td>
                            <td class="px-4 py-3 text-gray-700 text-xs">{{ $lc->purchaseOrder?->number ?? '-' }}</td>
                            <td class="px-4 py-3 hidden sm:table-cell text-gray-500">
                                {{ Str::limit($lc->description, 40) }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">Rp
                                {{ number_format($lc->total_additional_cost, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center text-xs text-gray-500">{{ $ml }}</td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="px-2 py-0.5 rounded-full text-xs bg-{{ $sc }}-100 text-{{ $sc }}-700 $sc }}-500/20 $sc }}-400">{{ $sl }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('landed-cost.show', $lc) }}"
                                        class="text-xs px-2 py-1 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50">Detail</a>
                                    @if ($lc->status !== 'posted')
                                        @canmodule('landed_cost', 'delete')
                                        <form method="POST" action="{{ route('landed-cost.destroy', $lc) }}"
                                            class="inline" data-confirm="Hapus?" data-confirm-type="danger">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="text-xs px-2 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700">Hapus</button>
                                        </form>
                                        @endcanmodule
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-400">Belum ada landed cost.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($landedCosts->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $landedCosts->links() }}</div>
        @endif
    </div>

    {{-- Modal Create --}}
    <div id="modal-add" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Buat Landed Cost</h3>
                <button onclick="document.getElementById('modal-add').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="{{ route('landed-cost.store') }}" class="p-6 space-y-4">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900'; @endphp
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Purchase Order *</label>
                        <select name="purchase_order_id" required class="{{ $cls }}">
                            <option value="">-- Pilih PO --</option>
                            @foreach ($purchaseOrders ?? [] as $po)
                                <option value="{{ $po->id }}">{{ $po->number }} —
                                    {{ $po->supplier?->name ?? '-' }} (Rp
                                    {{ number_format($po->total, 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal *</label>
                        <input type="date" name="date" required value="{{ date('Y-m-d') }}"
                            class="{{ $cls }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Metode Alokasi *</label>
                        <select name="allocation_method" required class="{{ $cls }}">
                            <option value="by_value">Berdasarkan Nilai</option>
                            <option value="by_quantity">Berdasarkan Qty</option>
                            <option value="by_weight">Berdasarkan Berat</option>
                            <option value="equal">Rata (Equal)</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi</label>
                        <input type="text" name="description" placeholder="Biaya impor PO-xxx"
                            class="{{ $cls }}">
                    </div>
                </div>

                {{-- Cost Components --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-xs font-semibold text-gray-500 uppercase">Komponen Biaya</h4>
                        <button type="button" onclick="addCostLine()"
                            class="text-xs px-2 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700">+
                            Tambah</button>
                    </div>
                    <div id="cost-lines" class="space-y-2"></div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            let costIdx = 0;

            function addCostLine() {
                const i = costIdx++;
                const container = document.getElementById('cost-lines');
                const div = document.createElement('div');
                div.className = 'grid grid-cols-12 gap-2 items-end';
                div.id = 'cost-line-' + i;
                const cls = 'w-full px-2 py-1.5 text-xs rounded-lg border border-gray-200 bg-gray-50 text-gray-900';
                div.innerHTML = `
            <div class="col-span-3"><select name="components[${i}][type]" required class="${cls}">
                <option value="freight">Freight</option><option value="customs">Bea Masuk</option><option value="insurance">Asuransi</option><option value="handling">Handling</option><option value="other">Lainnya</option>
            </select></div>
            <div class="col-span-3"><input type="text" name="components[${i}][name]" required placeholder="Nama biaya" class="${cls}"></div>
            <div class="col-span-2"><input type="number" name="components[${i}][amount]" required min="0.01" step="0.01" placeholder="Jumlah" class="${cls}"></div>
            <div class="col-span-2"><input type="text" name="components[${i}][vendor]" placeholder="Vendor" class="${cls}"></div>
            <div class="col-span-1"><input type="text" name="components[${i}][reference]" placeholder="Ref" class="${cls}"></div>
            <div class="col-span-1"><button type="button" onclick="document.getElementById('cost-line-${i}').remove()" class="text-red-500 hover:text-red-700 text-xs">✕</button></div>
        `;
                container.appendChild(div);
            }
            addCostLine();
        </script>
    @endpush
</x-app-layout>
