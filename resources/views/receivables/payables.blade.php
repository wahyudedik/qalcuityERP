<x-app-layout>
    <x-slot name="header">Hutang (Payables)</x-slot>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Total Outstanding</p>
            <p class="text-xl font-bold text-gray-900 dark:text-white">Rp {{ number_format($stats['total_outstanding'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Belum Bayar</p>
            <p class="text-xl font-bold text-red-500">{{ $stats['unpaid_count'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Sebagian Bayar</p>
            <p class="text-xl font-bold text-amber-500">{{ $stats['partial_count'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Jatuh Tempo</p>
            <p class="text-xl font-bold text-red-600">{{ $stats['overdue_count'] }}</p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-2 flex-1">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor hutang / supplier..."
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="status" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                <option value="">Semua Status</option>
                <option value="unpaid" @selected(request('status')==='unpaid')>Belum Bayar</option>
                <option value="partial" @selected(request('status')==='partial')>Sebagian</option>
                <option value="paid" @selected(request('status')==='paid')>Lunas</option>
            </select>
            <label class="flex items-center gap-2 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] cursor-pointer">
                <input type="checkbox" name="overdue" value="1" @checked(request('overdue')) class="rounded">
                <span class="text-gray-700 dark:text-slate-300">Jatuh Tempo</span>
            </label>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
        </form>
        <a href="{{ route('receivables.index') }}" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5 text-center">
            ← Piutang (Receivables)
        </a>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">No. Hutang</th>
                        <th class="px-4 py-3 text-left">Supplier</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Ref. PO</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-right">Sisa</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Jatuh Tempo</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($payables as $pay)
                    @php $overdue = in_array($pay->status, ['unpaid','partial']) && $pay->due_date < today(); @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5 {{ $overdue ? 'bg-red-50/50 dark:bg-red-900/10' : '' }}">
                        <td class="px-4 py-3 font-mono text-xs font-medium text-gray-900 dark:text-white">{{ $pay->number }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-slate-300">{{ $pay->supplier->name ?? '-' }}</td>
                        <td class="px-4 py-3 hidden md:table-cell">
                            @if($pay->purchaseOrder)
                            <span class="font-mono text-xs text-blue-600 dark:text-blue-400">{{ $pay->purchaseOrder->number }}</span>
                            @else
                            <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-gray-900 dark:text-white">Rp {{ number_format($pay->total_amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-semibold {{ $pay->remaining_amount > 0 ? 'text-red-500' : 'text-green-500' }}">
                            Rp {{ number_format($pay->remaining_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php $colors = ['unpaid'=>'red','partial'=>'amber','paid'=>'green']; $c = $colors[$pay->status] ?? 'gray'; @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs bg-{{ $c }}-100 text-{{ $c }}-700 dark:bg-{{ $c }}-500/20 dark:text-{{ $c }}-400">
                                {{ ['unpaid'=>'Belum Bayar','partial'=>'Sebagian','paid'=>'Lunas'][$pay->status] ?? $pay->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell text-xs {{ $overdue ? 'text-red-500 font-semibold' : 'text-gray-500 dark:text-slate-400' }}">
                            {{ $pay->due_date->format('d M Y') }}
                            @if($overdue) <span class="text-red-400">({{ $pay->daysOverdue() }}h)</span> @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($pay->status !== 'paid')
                            <button onclick="openPayModal('{{ $pay->id }}','{{ $pay->number }}','{{ $pay->remaining_amount }}')"
                                class="text-xs px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                Bayar
                            </button>
                            @else
                            <span class="text-xs text-gray-400 dark:text-slate-500">Lunas</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Tidak ada hutang.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payables->hasPages())
        <div class="px-4 py-3 border-t border-gray-100 dark:border-white/5">{{ $payables->links() }}</div>
        @endif
    </div>

    {{-- Modal --}}
    <div id="modal-pay" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Catat Pembayaran Hutang</h3>
                <button onclick="document.getElementById('modal-pay').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form id="form-pay" method="POST" class="p-6 space-y-4">
                @csrf
                <p class="text-sm text-gray-600 dark:text-slate-400">Hutang: <span id="pay-number" class="font-mono font-semibold text-gray-900 dark:text-white"></span></p>
                <p class="text-sm text-gray-600 dark:text-slate-400">Sisa: <span id="pay-remaining" class="font-semibold text-red-500"></span></p>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Jumlah Bayar *</label>
                    <input type="number" name="amount" id="pay-amount" required min="1" step="1"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Metode Bayar *</label>
                    <select name="method" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="cash">Cash</option>
                        <option value="transfer">Transfer Bank</option>
                        <option value="qris">QRIS</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Catatan</label>
                    <input type="text" name="notes" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-pay').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openPayModal(id, number, remaining) {
        document.getElementById('pay-number').textContent = number;
        document.getElementById('pay-remaining').textContent = 'Rp ' + parseInt(remaining).toLocaleString('id-ID');
        document.getElementById('pay-amount').max = remaining;
        document.getElementById('pay-amount').value = remaining;
        document.getElementById('form-pay').action = '{{ url("payables") }}/' + id + '/payment';
        document.getElementById('modal-pay').classList.remove('hidden');
    }
    </script>
    @endpush
</x-app-layout>
