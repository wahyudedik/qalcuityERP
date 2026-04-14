<x-app-layout>
    <x-slot name="title">RFQ — Qalcuity ERP</x-slot>
    <x-slot name="header">Request for Quotation (RFQ)</x-slot>
    <x-slot name="pageHeader">
        <button onclick="document.getElementById('modal-add-rfq').classList.remove('hidden')"
            class="flex items-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat RFQ
        </button>
    </x-slot>

    <div class="space-y-4">
        @forelse($rfqs as $rfq)
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            {{-- RFQ Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-white/5">
                <div class="flex items-center gap-3">
                    <div>
                        <p class="font-mono text-sm font-semibold text-gray-900 dark:text-white">{{ $rfq->number }}</p>
                        <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">
                            Dibuat: {{ $rfq->issue_date->format('d M Y') }} &bull;
                            Deadline: <span class="{{ $rfq->deadline->isPast() && $rfq->status === 'open' ? 'text-red-500' : '' }}">{{ $rfq->deadline->format('d M Y') }}</span>
                        </p>
                    </div>
                    @php
                        $rfqBadge = match($rfq->status) {
                            'open'      => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400',
                            'converted' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
                            default     => 'bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-slate-400',
                        };
                    @endphp
                    <span class="px-2 py-0.5 rounded-full text-xs {{ $rfqBadge }}">{{ $rfq->statusLabel() }}</span>
                </div>
                <div class="flex items-center gap-2 mt-3 sm:mt-0">
                    <span class="text-xs text-gray-500 dark:text-slate-400">{{ $rfq->responses->count() }} penawaran</span>
                    @if($rfq->status === 'open')
                    <button onclick="openAddResponse({{ $rfq->id }})"
                        class="px-3 py-1.5 text-xs bg-amber-500 text-white rounded-lg hover:bg-amber-600">+ Penawaran</button>
                    @endif
                    @if($rfq->status === 'open' && $rfq->selectedResponse())
                    <button onclick="openConvertRfq({{ $rfq->id }}, '{{ $rfq->number }}')"
                        class="px-3 py-1.5 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700">→ PO</button>
                    @endif
                </div>
            </div>

            {{-- RFQ Items --}}
            <div class="px-5 py-3 border-b border-gray-100 dark:border-white/5">
                <p class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase mb-2">Item</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($rfq->items as $item)
                    <span class="px-2 py-1 bg-gray-100 dark:bg-white/5 rounded-lg text-xs text-gray-700 dark:text-slate-300">
                        {{ $item->description }} &times; {{ number_format($item->quantity, 0) }} {{ $item->unit }}
                    </span>
                    @endforeach
                </div>
            </div>

            {{-- Supplier Responses --}}
            @if($rfq->responses->count())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400">
                        <tr>
                            <th class="px-4 py-2 text-left">Supplier</th>
                            <th class="px-4 py-2 text-right">Total Harga</th>
                            <th class="px-4 py-2 text-center hidden md:table-cell">Pengiriman</th>
                            <th class="px-4 py-2 text-left hidden md:table-cell">Syarat Bayar</th>
                            <th class="px-4 py-2 text-center">Pilih</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @foreach($rfq->responses->sortBy('total_price') as $resp)
                        <tr class="{{ $resp->is_selected ? 'bg-green-50 dark:bg-green-500/10' : 'hover:bg-gray-50 dark:hover:bg-white/5' }}">
                            <td class="px-4 py-2.5">
                                <div class="flex items-center gap-2">
                                    @if($resp->is_selected)
                                    <span class="w-2 h-2 rounded-full bg-green-500 shrink-0"></span>
                                    @endif
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $resp->supplier->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-2.5 text-right font-semibold text-gray-900 dark:text-white">
                                Rp {{ number_format($resp->total_price, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-2.5 text-center hidden md:table-cell text-gray-500 dark:text-slate-400">
                                {{ $resp->delivery_days ? $resp->delivery_days . ' hari' : '—' }}
                            </td>
                            <td class="px-4 py-2.5 hidden md:table-cell text-gray-500 dark:text-slate-400">
                                {{ $resp->payment_terms ?? '—' }}
                            </td>
                            <td class="px-4 py-2.5 text-center">
                                @if(!$resp->is_selected && $rfq->status === 'open')
                                <form method="POST" action="{{ route('purchasing.rfq.response.select', $resp) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="px-2 py-1 text-xs border border-green-500 text-green-600 rounded-lg hover:bg-green-50 dark:hover:bg-green-500/10">Pilih</button>
                                </form>
                                @elseif($resp->is_selected)
                                <span class="text-xs text-green-600 dark:text-green-400 font-semibold">✓ Dipilih</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
        @empty
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 px-4 py-12 text-center text-gray-400 dark:text-slate-500">
            Belum ada RFQ.
        </div>
        @endforelse

        @if($rfqs->hasPages())
        <div>{{ $rfqs->links() }}</div>
        @endif
    </div>

    {{-- Modal Buat RFQ --}}
    <div id="modal-add-rfq" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10 sticky top-0 bg-white dark:bg-[#1e293b]">
                <h3 class="font-semibold text-gray-900 dark:text-white">Buat RFQ</h3>
                <button onclick="document.getElementById('modal-add-rfq').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="{{ route('purchasing.rfq.store') }}" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanggal Terbit *</label>
                        <input type="date" name="issue_date" value="{{ today()->format('Y-m-d') }}" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Deadline Respon *</label>
                        <input type="date" name="deadline" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    </div>
                    @if($requisitions->count())
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Berdasarkan PR (opsional)</label>
                        <select name="purchase_requisition_id" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                            <option value="">Tidak ada</option>
                            @foreach($requisitions as $pr)
                                <option value="{{ $pr->id }}">{{ $pr->number }} — {{ $pr->department ?? 'Umum' }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Catatan</label>
                        <textarea name="notes" rows="2" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white resize-none"></textarea>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wide">Item</p>
                        <button type="button" onclick="addRfqItem()" class="text-xs text-blue-600 hover:underline">+ Tambah</button>
                    </div>
                    <div id="rfq-items" class="space-y-2">
                        <div class="rfq-item grid grid-cols-12 gap-2">
                            <div class="col-span-6"><input type="text" name="items[0][description]" placeholder="Deskripsi *" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white"></div>
                            <div class="col-span-3"><input type="number" name="items[0][quantity]" placeholder="Qty" min="0.01" step="0.01" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white"></div>
                            <div class="col-span-2"><input type="text" name="items[0][unit]" placeholder="Satuan" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white"></div>
                            <div class="col-span-1 flex items-center"><button type="button" onclick="removeRfqItem(this)" class="text-red-400 hover:text-red-600">✕</button></div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-add-rfq').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Kirim RFQ</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Tambah Penawaran --}}
    <div id="modal-add-response" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Input Penawaran Supplier</h3>
                <button onclick="document.getElementById('modal-add-response').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form id="form-add-response" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Supplier *</label>
                    <select name="supplier_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                        <option value="">Pilih supplier...</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanggal Respon *</label>
                        <input type="date" name="response_date" value="{{ today()->format('Y-m-d') }}" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Total Harga *</label>
                        <input type="number" name="total_price" min="0" step="1000" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Estimasi Pengiriman (hari)</label>
                        <input type="number" name="delivery_days" min="1"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Syarat Pembayaran</label>
                        <input type="text" name="payment_terms" placeholder="cth: NET 30"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Catatan</label>
                    <textarea name="notes" rows="2" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white resize-none"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-add-response').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Convert RFQ to PO --}}
    <div id="modal-convert-rfq" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Konversi RFQ ke PO</h3>
                <button onclick="document.getElementById('modal-convert-rfq').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form id="form-convert-rfq" method="POST" class="p-6 space-y-4">
                @csrf
                <p id="convert-rfq-num" class="text-sm font-medium text-gray-700 dark:text-slate-300"></p>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Gudang *</label>
                    <select name="warehouse_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                        <option value="">Pilih gudang...</option>
                        @foreach(\App\Models\Warehouse::where('tenant_id', auth()->user()->tenant_id)->where('is_active', true)->get() as $w)
                            <option value="{{ $w->id }}">{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanggal PO *</label>
                        <input type="date" name="date" value="{{ today()->format('Y-m-d') }}" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Pembayaran *</label>
                        <select name="payment_type" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                            <option value="credit">Kredit</option>
                            <option value="cash">Tunai</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-convert-rfq').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Buat PO</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    let rfqItemCount = 1;
    function addRfqItem() {
        const i = rfqItemCount++;
        const div = document.createElement('div');
        div.className = 'rfq-item grid grid-cols-12 gap-2';
        div.innerHTML = `
            <div class="col-span-6"><input type="text" name="items[${i}][description]" placeholder="Deskripsi *" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white"></div>
            <div class="col-span-3"><input type="number" name="items[${i}][quantity]" placeholder="Qty" min="0.01" step="0.01" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white"></div>
            <div class="col-span-2"><input type="text" name="items[${i}][unit]" placeholder="Satuan" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white"></div>
            <div class="col-span-1 flex items-center"><button type="button" onclick="removeRfqItem(this)" class="text-red-400 hover:text-red-600">✕</button></div>`;
        document.getElementById('rfq-items').appendChild(div);
    }
    function removeRfqItem(btn) {
        const items = document.querySelectorAll('.rfq-item');
        if (items.length > 1) btn.closest('.rfq-item').remove();
    }
    function openAddResponse(rfqId) {
        document.getElementById('form-add-response').action = '{{ url("purchasing/rfq") }}/' + rfqId + '/response';
        document.getElementById('modal-add-response').classList.remove('hidden');
    }
    function openConvertRfq(id, num) {
        document.getElementById('form-convert-rfq').action = '{{ url("purchasing/rfq") }}/' + id + '/convert';
        document.getElementById('convert-rfq-num').textContent = 'RFQ: ' + num;
        document.getElementById('modal-convert-rfq').classList.remove('hidden');
    }
    </script>
    @endpush
</x-app-layout>
