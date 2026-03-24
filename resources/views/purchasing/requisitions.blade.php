<x-app-layout>
    <x-slot name="title">Purchase Requisition — Qalcuity ERP</x-slot>
    <x-slot name="header">Purchase Requisition (PR)</x-slot>
    <x-slot name="topbarActions">
        <button onclick="document.getElementById('modal-add-pr').classList.remove('hidden')"
            class="flex items-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat PR
        </button>
    </x-slot>

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Menunggu Persetujuan</p>
            <p class="text-2xl font-bold text-amber-500 mt-1">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Disetujui</p>
            <p class="text-2xl font-bold text-green-500 mt-1">{{ $stats['approved'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total PR</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['total'] }}</p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4 mb-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <select name="status" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                <option value="">Semua Status</option>
                @foreach(['pending' => 'Pending', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'converted' => 'Sudah Jadi PO'] as $val => $lbl)
                    <option value="{{ $val }}" @selected(request('status') === $val)>{{ $lbl }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
            <a href="{{ route('purchasing.requisitions') }}" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Reset</a>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nomor</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Pemohon</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Departemen</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Tgl Dibutuhkan</th>
                        <th class="px-4 py-3 text-right">Est. Total</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($requisitions as $pr)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3">
                            <p class="font-mono text-xs font-semibold text-gray-900 dark:text-white">{{ $pr->number }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $pr->created_at->format('d M Y') }}</p>
                        </td>
                        <td class="px-4 py-3 hidden md:table-cell text-gray-700 dark:text-slate-300">{{ $pr->requester->name }}</td>
                        <td class="px-4 py-3 hidden md:table-cell text-gray-500 dark:text-slate-400">{{ $pr->department ?? '—' }}</td>
                        <td class="px-4 py-3 hidden lg:table-cell text-gray-500 dark:text-slate-400 text-xs">
                            {{ $pr->required_date?->format('d M Y') ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">
                            Rp {{ number_format($pr->estimated_total, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs {{ $pr->statusColor() }}">{{ $pr->statusLabel() }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                @if($pr->status === 'pending')
                                <button onclick="openApprove({{ $pr->id }}, '{{ $pr->number }}')"
                                    class="px-2 py-1 text-xs bg-green-600 text-white rounded-lg hover:bg-green-700">Proses</button>
                                @endif
                                @if($pr->status === 'approved')
                                <button onclick="openConvert({{ $pr->id }}, '{{ $pr->number }}')"
                                    class="px-2 py-1 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700">→ PO</button>
                                @endif
                                <button onclick="openDetail({{ $pr->id }})"
                                    class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-white/10">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada Purchase Requisition.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($requisitions->hasPages())
        <div class="px-4 py-3 border-t border-gray-100 dark:border-white/5">{{ $requisitions->links() }}</div>
        @endif
    </div>

    {{-- Modal Buat PR --}}
    <div id="modal-add-pr" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10 sticky top-0 bg-white dark:bg-[#1e293b]">
                <h3 class="font-semibold text-gray-900 dark:text-white">Buat Purchase Requisition</h3>
                <button onclick="document.getElementById('modal-add-pr').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="{{ route('purchasing.requisitions.store') }}" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Departemen</label>
                        <input type="text" name="department" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanggal Dibutuhkan</label>
                        <input type="date" name="required_date" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tujuan / Keperluan</label>
                        <textarea name="purpose" rows="2" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white resize-none"></textarea>
                    </div>
                </div>

                {{-- Items --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wide">Item yang Diminta</p>
                        <button type="button" onclick="addPrItem()" class="text-xs text-blue-600 hover:underline">+ Tambah Item</button>
                    </div>
                    <div id="pr-items" class="space-y-2">
                        <div class="pr-item grid grid-cols-12 gap-2 items-start">
                            <div class="col-span-5">
                                <input type="text" name="items[0][description]" placeholder="Deskripsi item *" required
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                            </div>
                            <div class="col-span-2">
                                <input type="number" name="items[0][quantity]" placeholder="Qty" min="0.01" step="0.01" required
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                            </div>
                            <div class="col-span-2">
                                <input type="text" name="items[0][unit]" placeholder="Satuan"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                            </div>
                            <div class="col-span-2">
                                <input type="number" name="items[0][estimated_price]" placeholder="Est. Harga" min="0" step="1000"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                            </div>
                            <div class="col-span-1 flex items-center pt-1">
                                <button type="button" onclick="removePrItem(this)" class="text-red-400 hover:text-red-600">✕</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-add-pr').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Kirim PR</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Approve --}}
    <div id="modal-approve-pr" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Proses PR</h3>
                <button onclick="document.getElementById('modal-approve-pr').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form id="form-approve-pr" method="POST" class="p-6 space-y-4">
                @csrf @method('PATCH')
                <p id="approve-pr-num" class="text-sm font-medium text-gray-700 dark:text-slate-300"></p>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Keputusan *</label>
                    <select name="action" id="pr-action" onchange="togglePrRejection()" required
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                        <option value="approved">Setujui</option>
                        <option value="rejected">Tolak</option>
                    </select>
                </div>
                <div id="pr-rejection-wrap" class="hidden">
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Alasan Penolakan</label>
                    <textarea name="rejection_reason" rows="2" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white resize-none"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-approve-pr').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Convert to PO --}}
    <div id="modal-convert-pr" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Konversi PR ke PO</h3>
                <button onclick="document.getElementById('modal-convert-pr').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form id="form-convert-pr" method="POST" class="p-6 space-y-4">
                @csrf
                <p id="convert-pr-num" class="text-sm font-medium text-gray-700 dark:text-slate-300"></p>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Supplier *</label>
                    <select name="supplier_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                        <option value="">Pilih supplier...</option>
                        @foreach(\App\Models\Supplier::where('tenant_id', auth()->user()->tenant_id)->where('is_active', true)->orderBy('name')->get() as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
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
                    <button type="button" onclick="document.getElementById('modal-convert-pr').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Buat PO</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    let prItemCount = 1;
    function addPrItem() {
        const i = prItemCount++;
        const div = document.createElement('div');
        div.className = 'pr-item grid grid-cols-12 gap-2 items-start';
        div.innerHTML = `
            <div class="col-span-5"><input type="text" name="items[${i}][description]" placeholder="Deskripsi item *" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white"></div>
            <div class="col-span-2"><input type="number" name="items[${i}][quantity]" placeholder="Qty" min="0.01" step="0.01" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white"></div>
            <div class="col-span-2"><input type="text" name="items[${i}][unit]" placeholder="Satuan" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white"></div>
            <div class="col-span-2"><input type="number" name="items[${i}][estimated_price]" placeholder="Est. Harga" min="0" step="1000" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white"></div>
            <div class="col-span-1 flex items-center pt-1"><button type="button" onclick="removePrItem(this)" class="text-red-400 hover:text-red-600">✕</button></div>`;
        document.getElementById('pr-items').appendChild(div);
    }
    function removePrItem(btn) {
        const items = document.querySelectorAll('.pr-item');
        if (items.length > 1) btn.closest('.pr-item').remove();
    }
    function openApprove(id, num) {
        document.getElementById('form-approve-pr').action = '{{ url("purchasing/requisitions") }}/' + id + '/approve';
        document.getElementById('approve-pr-num').textContent = 'PR: ' + num;
        document.getElementById('modal-approve-pr').classList.remove('hidden');
    }
    function openConvert(id, num) {
        document.getElementById('form-convert-pr').action = '{{ url("purchasing/requisitions") }}/' + id + '/convert';
        document.getElementById('convert-pr-num').textContent = 'PR: ' + num;
        document.getElementById('modal-convert-pr').classList.remove('hidden');
    }
    function togglePrRejection() {
        document.getElementById('pr-rejection-wrap').classList.toggle('hidden', document.getElementById('pr-action').value !== 'rejected');
    }
    function openDetail(id) { /* future */ }
    </script>
    @endpush
</x-app-layout>
