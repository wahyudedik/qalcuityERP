@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Uang Muka (Down Payment)</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola uang muka customer dan supplier</p>
        </div>
        @canmodule('down_payments', 'create')
        <button onclick="document.getElementById('modalDP').classList.remove('hidden')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Catat Uang Muka
        </button>
        @endcanmodule
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700">
            <p class="text-xs text-slate-500 dark:text-slate-400">DP Customer Belum Dipakai</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">Rp {{ number_format($stats['customer_pending'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700">
            <p class="text-xs text-slate-500 dark:text-slate-400">DP Supplier Belum Dipakai</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">Rp {{ number_format($stats['supplier_pending'], 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" class="flex gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor..."
               class="flex-1 px-3 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
        <select name="type" class="px-3 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white">
            <option value="">Semua Tipe</option>
            <option value="customer" @selected(request('type') === 'customer')>Customer</option>
            <option value="supplier" @selected(request('type') === 'supplier')>Supplier</option>
        </select>
        <select name="status" class="px-3 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white">
            <option value="">Semua Status</option>
            <option value="pending" @selected(request('status') === 'pending')>Menunggu</option>
            <option value="partial" @selected(request('status') === 'partial')>Sebagian</option>
            <option value="applied" @selected(request('status') === 'applied')>Sudah Dipakai</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-slate-700 hover:bg-slate-800 text-white text-sm rounded-lg">Filter</button>
    </form>

    {{-- Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-400 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Nomor</th>
                    <th class="px-4 py-3 text-left">Tipe</th>
                    <th class="px-4 py-3 text-left">Pihak</th>
                    <th class="px-4 py-3 text-left">Tanggal</th>
                    <th class="px-4 py-3 text-right">Jumlah</th>
                    <th class="px-4 py-3 text-right">Sisa</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse($downPayments as $dp)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                    <td class="px-4 py-3 font-mono font-medium text-slate-800 dark:text-white">{{ $dp->number }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 text-xs rounded-full {{ $dp->type === 'customer' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ ucfirst($dp->type) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $dp->party?->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ $dp->payment_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-right font-medium text-slate-800 dark:text-white">Rp {{ number_format($dp->amount, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-300">Rp {{ number_format($dp->remaining_amount, 0, ',', '.') }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $dp->statusColor() }}">
                            {{ $dp->statusLabel() }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($dp->type === 'customer' && in_array($dp->status, ['pending', 'partial']))
                        <button onclick="openApplyModal({{ $dp->id }}, '{{ $dp->number }}', {{ $dp->remaining_amount }})"
                                class="text-xs px-2 py-1 bg-green-100 text-green-700 hover:bg-green-200 rounded">
                            Aplikasikan
                        </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-12 text-center text-slate-400 dark:text-slate-500">Belum ada uang muka</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $downPayments->links() }}
</div>

{{-- Modal: Catat DP --}}
<div id="modalDP" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-xl w-full max-w-md">
        <div class="flex items-center justify-between p-5 border-b border-slate-200 dark:border-slate-700">
            <h3 class="font-semibold text-slate-800 dark:text-white">Catat Uang Muka</h3>
            <button onclick="document.getElementById('modalDP').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('down-payments.store') }}" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tipe</label>
                <select name="type" id="dpType" required onchange="toggleParty()"
                        class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="customer">Customer (DP Masuk)</option>
                    <option value="supplier">Supplier (DP Keluar)</option>
                </select>
            </div>
            <div id="customerParty">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Customer</label>
                <select name="party_id" id="customerPartySelect"
                        class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">-- Pilih Customer --</option>
                    @foreach($customers as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div id="supplierParty" class="hidden">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Supplier</label>
                <select name="party_id" id="supplierPartySelect"
                        class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">-- Pilih Supplier --</option>
                    @foreach($suppliers as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tanggal</label>
                    <input type="date" name="payment_date" value="{{ today()->toDateString() }}" required
                           class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Metode</label>
                    <select name="payment_method" required
                            class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="transfer">Transfer</option>
                        <option value="cash">Tunai</option>
                        <option value="qris">QRIS</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Jumlah</label>
                <input type="number" name="amount" min="1" step="1" required placeholder="0"
                       class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modalDP').classList.add('hidden')"
                        class="px-4 py-2 text-sm text-slate-600 dark:text-slate-400 border border-slate-300 dark:border-slate-600 rounded-lg">Batal</button>
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Apply DP --}}
<div id="modalApply" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-xl w-full max-w-md">
        <div class="flex items-center justify-between p-5 border-b border-slate-200 dark:border-slate-700">
            <h3 class="font-semibold text-slate-800 dark:text-white">Aplikasikan DP ke Invoice</h3>
            <button onclick="document.getElementById('modalApply').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="applyForm" method="POST" class="p-5 space-y-4">
            @csrf
            <p class="text-sm text-slate-600 dark:text-slate-400">DP: <span id="applyDpNumber" class="font-mono font-medium"></span></p>
            <p class="text-sm text-slate-600 dark:text-slate-400">Sisa: <span id="applyDpRemaining" class="font-medium text-green-600"></span></p>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Invoice</label>
                <select name="invoice_id" required
                        class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">-- Pilih Invoice --</option>
                    @foreach(\App\Models\Invoice::where('tenant_id', auth()->user()->tenant_id)->whereIn('status', ['unpaid', 'partial'])->orderBy('number')->get() as $inv)
                    <option value="{{ $inv->id }}">{{ $inv->number }} — Sisa Rp {{ number_format($inv->remaining_amount, 0, ',', '.') }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Jumlah</label>
                <input type="number" name="amount" id="applyAmount" min="1" step="1" required
                       class="w-full px-3 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modalApply').classList.add('hidden')"
                        class="px-4 py-2 text-sm text-slate-600 dark:text-slate-400 border border-slate-300 dark:border-slate-600 rounded-lg">Batal</button>
                <button type="submit" class="px-5 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg">Aplikasikan</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleParty() {
    const type = document.getElementById('dpType').value;
    document.getElementById('customerParty').classList.toggle('hidden', type !== 'customer');
    document.getElementById('supplierParty').classList.toggle('hidden', type !== 'supplier');
    document.getElementById('customerPartySelect').required = type === 'customer';
    document.getElementById('supplierPartySelect').required = type === 'supplier';
}

function openApplyModal(dpId, dpNumber, remaining) {
    document.getElementById('applyForm').action = '{{ url("down-payments") }}/' + dpId + '/apply';
    document.getElementById('applyDpNumber').textContent = dpNumber;
    document.getElementById('applyDpRemaining').textContent = 'Rp ' + remaining.toLocaleString('id-ID');
    document.getElementById('applyAmount').max = remaining;
    document.getElementById('applyAmount').value = '';
    document.getElementById('modalApply').classList.remove('hidden');
}
</script>
@endsection
