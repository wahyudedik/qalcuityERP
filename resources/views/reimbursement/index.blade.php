<x-app-layout>
    <x-slot name="header">Reimbursement</x-slot>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Menunggu Approval</p>
            <p class="text-2xl font-bold text-amber-500">{{ $stats['submitted'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Approved (Belum Bayar)</p>
            <p class="text-2xl font-bold text-blue-500">{{ $stats['approved'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Total Pending</p>
            <p class="text-lg font-bold text-red-500">Rp {{ number_format($stats['total_pending'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Dibayar Bulan Ini</p>
            <p class="text-lg font-bold text-green-500">Rp {{ number_format($stats['paid_month'], 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <form method="GET" class="flex-1 flex flex-wrap gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari..."
                class="flex-1 min-w-[120px] px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="status" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                <option value="">Semua Status</option>
                @foreach(['submitted'=>'Submitted','approved'=>'Approved','rejected'=>'Rejected','paid'=>'Paid'] as $v=>$l)
                <option value="{{ $v }}" @selected(request('status')===$v)>{{ $l }}</option>
                @endforeach
            </select>
            <select name="category" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                <option value="">Semua Kategori</option>
                @foreach(['transport'=>'Transportasi','meal'=>'Makan','medical'=>'Kesehatan','office'=>'Kantor','travel'=>'Perjalanan','training'=>'Pelatihan','other'=>'Lainnya'] as $v=>$l)
                <option value="{{ $v }}" @selected(request('category')===$v)>{{ $l }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
        </form>
        @canmodule('reimbursement', 'create')
        <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Reimbursement</button>
        @endcanmodule
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">No.</th>
                        <th class="px-4 py-3 text-left">Karyawan</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Kategori</th>
                        <th class="px-4 py-3 text-left">Deskripsi</th>
                        <th class="px-4 py-3 text-right">Jumlah</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($reimbursements as $r)
                    @php $sc = ['draft'=>'gray','submitted'=>'amber','approved'=>'blue','rejected'=>'red','paid'=>'green'][$r->status] ?? 'gray'; @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3 font-mono text-xs text-gray-900 dark:text-white">{{ $r->number }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-slate-300">{{ $r->employee->name ?? '-' }}</td>
                        <td class="px-4 py-3 hidden sm:table-cell text-xs text-gray-500 dark:text-slate-400">{{ $r->categoryLabel() }}</td>
                        <td class="px-4 py-3 text-gray-900 dark:text-white">
                            {{ Str::limit($r->description, 30) }}
                            @if($r->receipt_image) <span class="text-xs text-blue-400">📎</span> @endif
                        </td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">Rp {{ number_format($r->amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs bg-{{ $sc }}-100 text-{{ $sc }}-700 dark:bg-{{ $sc }}-500/20 dark:text-{{ $sc }}-400">{{ ucfirst($r->status) }}</span>
                            @if($r->reject_reason)<p class="text-xs text-red-400 mt-0.5">{{ Str::limit($r->reject_reason, 20) }}</p>@endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                @canmodule('reimbursement', 'edit')
                                @if($r->status === 'submitted')
                                <form method="POST" action="{{ route('reimbursement.approve', $r) }}" class="inline">@csrf @method('PATCH')
                                    <button type="submit" class="text-xs px-2 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('reimbursement.reject', $r) }}" class="inline" onsubmit="const r=prompt('Alasan reject:'); if(!r) return false; this.querySelector('[name=reason]').value=r;">
                                    @csrf @method('PATCH') <input type="hidden" name="reason" value="">
                                    <button type="submit" class="text-xs px-2 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700">Reject</button>
                                </form>
                                @elseif($r->status === 'approved')
                                <form method="POST" action="{{ route('reimbursement.pay', $r) }}" class="inline" onsubmit="return confirm('Bayar reimbursement ini?')">
                                    @csrf
                                    <select name="payment_method" class="text-xs px-1 py-0.5 rounded border border-gray-200 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white">
                                        <option value="transfer">Transfer</option><option value="cash">Cash</option>
                                    </select>
                                    <button type="submit" class="text-xs px-2 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700">Bayar</button>
                                </form>
                                @endif
                                @endcanmodule
                                @if(in_array($r->status, ['draft', 'submitted']))
                                @canmodule('reimbursement', 'delete')
                                <form method="POST" action="{{ route('reimbursement.destroy', $r) }}" class="inline" onsubmit="return confirm('Hapus?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs px-2 py-1 text-red-400 hover:text-red-300">✕</button>
                                </form>
                                @endcanmodule
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada reimbursement.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reimbursements->hasPages())<div class="px-4 py-3 border-t border-gray-100 dark:border-white/5">{{ $reimbursements->links() }}</div>@endif
    </div>

    {{-- Modal Create --}}
    <div id="modal-add" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Ajukan Reimbursement</h3>
                <button onclick="document.getElementById('modal-add').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('reimbursement.store') }}" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; @endphp
                <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Karyawan *</label>
                    <select name="employee_id" required class="{{ $cls }}"><option value="">-- Pilih --</option>
                        @foreach($employees as $e)<option value="{{ $e->id }}">{{ $e->name }} ({{ $e->employee_id }})</option>@endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Kategori *</label>
                        <select name="category" required class="{{ $cls }}">
                            <option value="transport">Transportasi</option><option value="meal">Makan & Minum</option><option value="medical">Kesehatan</option>
                            <option value="office">Perlengkapan Kantor</option><option value="travel">Perjalanan Dinas</option><option value="training">Pelatihan</option><option value="other">Lainnya</option>
                        </select>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanggal *</label><input type="date" name="expense_date" required value="{{ date('Y-m-d') }}" class="{{ $cls }}"></div>
                </div>
                <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Deskripsi *</label><input type="text" name="description" required placeholder="Ongkos taksi meeting client" class="{{ $cls }}"></div>
                <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Jumlah (Rp) *</label><input type="number" name="amount" required min="1000" step="500" class="{{ $cls }}"></div>
                <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Foto Struk/Bukti</label><input type="file" name="receipt_image" accept="image/*" class="{{ $cls }}"></div>
                <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Catatan</label><input type="text" name="notes" class="{{ $cls }}"></div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Ajukan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
