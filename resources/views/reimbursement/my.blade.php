<x-app-layout>
    <x-slot name="header">Reimbursement Saya</x-slot>

    @if(!$employee)
    <div class="bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 rounded-2xl p-4 mb-4">
        <p class="text-sm text-amber-700 dark:text-amber-400">Akun Anda belum terhubung ke data karyawan. Hubungi admin.</p>
    </div>
    @else
    <div class="flex justify-end mb-4">
        <button onclick="document.getElementById('modal-submit').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Ajukan Reimbursement</button>
    </div>

    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">No.</th>
                        <th class="px-4 py-3 text-left">Kategori</th>
                        <th class="px-4 py-3 text-left">Deskripsi</th>
                        <th class="px-4 py-3 text-center">Tanggal</th>
                        <th class="px-4 py-3 text-right">Jumlah</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($reimbursements as $r)
                    @php $sc = ['draft'=>'gray','submitted'=>'amber','approved'=>'blue','rejected'=>'red','paid'=>'green'][$r->status] ?? 'gray'; @endphp
                    <tr>
                        <td class="px-4 py-3 font-mono text-xs text-gray-900 dark:text-white">{{ $r->number }}</td>
                        <td class="px-4 py-3 text-gray-500 dark:text-slate-400 text-xs">{{ $r->categoryLabel() }}</td>
                        <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $r->description }}</td>
                        <td class="px-4 py-3 text-center text-xs text-gray-500">{{ $r->expense_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">Rp {{ number_format($r->amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs bg-{{ $sc }}-100 text-{{ $sc }}-700 dark:bg-{{ $sc }}-500/20 dark:text-{{ $sc }}-400">{{ ucfirst($r->status) }}</span>
                            @if($r->reject_reason)<p class="text-xs text-red-400 mt-0.5">{{ $r->reject_reason }}</p>@endif
                            @if($r->status === 'paid')<p class="text-xs text-green-500 mt-0.5">Dibayar {{ $r->paid_at?->format('d/m') }}</p>@endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada pengajuan reimbursement.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reimbursements->hasPages())<div class="px-4 py-3 border-t border-gray-100 dark:border-white/5">{{ $reimbursements->links() }}</div>@endif
    </div>

    {{-- Modal Submit --}}
    <div id="modal-submit" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Ajukan Reimbursement</h3>
                <button onclick="document.getElementById('modal-submit').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('reimbursement.my.store') }}" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; @endphp
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
                    <button type="button" onclick="document.getElementById('modal-submit').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Ajukan</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</x-app-layout>
