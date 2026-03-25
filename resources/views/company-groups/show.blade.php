<x-app-layout>
    <x-slot name="header">{{ $companyGroup->name }}</x-slot>

    <div class="space-y-6">

        {{-- Period Selector --}}
        <div class="flex flex-wrap items-center gap-3">
            <form method="GET" class="flex items-center gap-3">
                <label class="text-sm text-gray-500 dark:text-slate-400">Periode:</label>
                <input type="month" name="period" value="{{ $period }}"
                    class="rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-sm text-gray-900 dark:text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm hover:bg-blue-700">Tampilkan</button>
            </form>
            <a href="{{ route('company-groups.index') }}" class="text-sm text-gray-400 hover:text-gray-600 dark:hover:text-white ml-auto">← Kembali</a>
        </div>

        {{-- Consolidated KPIs --}}
        @if(!empty($report))
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach([
                ['label' => 'Total Omzet',        'value' => $report['formatted']['total_revenue'], 'color' => 'text-green-500'],
                ['label' => 'Total Biaya',         'value' => $report['formatted']['total_expense'], 'color' => 'text-red-500'],
                ['label' => 'Eliminasi IC',        'value' => $report['formatted']['elimination'],   'color' => 'text-amber-500'],
                ['label' => 'Laba Konsolidasi',    'value' => $report['formatted']['cons_profit'],   'color' => ($report['consolidated_profit'] ?? 0) >= 0 ? 'text-blue-500' : 'text-red-500'],
            ] as $kpi)
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                <p class="text-xs text-gray-500 dark:text-slate-400">{{ $kpi['label'] }}</p>
                <p class="text-lg font-bold {{ $kpi['color'] }} mt-1">{{ $kpi['value'] }}</p>
            </div>
            @endforeach
        </div>

        {{-- Per-Company Breakdown --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Breakdown per Perusahaan</h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Perusahaan</th>
                        <th class="px-4 py-3 text-right">Omzet</th>
                        <th class="px-4 py-3 text-right">Biaya</th>
                        <th class="px-4 py-3 text-right">Laba</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @foreach($report['revenues'] as $tid => $rev)
                    @php $exp = $report['expenses'][$tid]['amount'] ?? 0; $profit = $rev['amount'] - $exp; @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $rev['name'] }}</td>
                        <td class="px-4 py-3 text-right text-green-500">Rp {{ number_format($rev['amount'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-red-500">Rp {{ number_format($exp, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-medium {{ $profit >= 0 ? 'text-blue-500' : 'text-red-500' }}">
                            Rp {{ number_format($profit, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Members --}}
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-4">Anggota Grup ({{ $companyGroup->members->count() }})</h3>
                <div class="space-y-2 mb-4">
                    @foreach($companyGroup->members as $member)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-white/5">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $member->name }}</p>
                            <p class="text-xs text-gray-400 dark:text-slate-500">{{ ucfirst($member->pivot->role) }} · ID #{{ $member->id }}</p>
                        </div>
                        @if($member->pivot->role !== 'owner')
                        <form method="POST" action="{{ route('company-groups.members.remove', [$companyGroup, $member]) }}" onsubmit="return confirm('Hapus dari grup?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-400 hover:text-red-600">Hapus</button>
                        </form>
                        @else
                        <span class="text-xs text-green-500">Owner</span>
                        @endif
                    </div>
                    @endforeach
                </div>
                <form method="POST" action="{{ route('company-groups.members.add', $companyGroup) }}" class="flex gap-2">
                    @csrf
                    <input type="number" name="tenant_id" placeholder="ID Tenant" required min="1"
                        class="flex-1 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-sm text-gray-900 dark:text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm hover:bg-blue-700">+ Tambah</button>
                </form>
            </div>

            {{-- New Transaction Form --}}
            <div class="lg:col-span-2 bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-4">Catat Transaksi Intercompany</h3>
                <form method="POST" action="{{ route('company-groups.transactions.store', $companyGroup) }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Dari Perusahaan *</label>
                            <select name="from_tenant_id" required
                                class="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-sm text-gray-900 dark:text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Pilih...</option>
                                @foreach($companyGroup->members as $m)
                                <option value="{{ $m->id }}">{{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Ke Perusahaan *</label>
                            <select name="to_tenant_id" required
                                class="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-sm text-gray-900 dark:text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Pilih...</option>
                                @foreach($companyGroup->members as $m)
                                <option value="{{ $m->id }}">{{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tipe *</label>
                            <select name="type" required
                                class="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-sm text-gray-900 dark:text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="sale">🛒 Penjualan Antar Entitas</option>
                                <option value="loan">💰 Pinjaman Antar Entitas</option>
                                <option value="expense_allocation">📊 Alokasi Biaya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Jumlah (Rp) *</label>
                            <input type="number" name="amount" min="1" step="1000" required placeholder="0"
                                class="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-sm text-gray-900 dark:text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanggal *</label>
                            <input type="date" name="date" value="{{ today()->toDateString() }}" required
                                class="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-sm text-gray-900 dark:text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Deskripsi</label>
                            <input type="text" name="description" placeholder="Opsional"
                                class="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-sm text-gray-900 dark:text-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700">
                        Catat Transaksi
                    </button>
                </form>
            </div>
        </div>

        {{-- Transaction History --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-white/10 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Riwayat Transaksi Intercompany</h3>
                <div class="flex gap-2 text-xs">
                    <span class="px-2 py-1 bg-green-500/20 text-green-400 rounded-full">{{ $transactions->where('status','posted')->count() }} posted</span>
                    <span class="px-2 py-1 bg-amber-500/20 text-amber-400 rounded-full">{{ $transactions->where('status','pending')->count() }} pending</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Tanggal</th>
                            <th class="px-4 py-3 text-left">Dari → Ke</th>
                            <th class="px-4 py-3 text-left hidden sm:table-cell">Tipe</th>
                            <th class="px-4 py-3 text-left hidden md:table-cell">Deskripsi</th>
                            <th class="px-4 py-3 text-right">Jumlah</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @forelse($transactions as $tx)
                        @php
                            $typeLabels = ['sale' => '🛒 Penjualan', 'loan' => '💰 Pinjaman', 'expense_allocation' => '📊 Alokasi'];
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-slate-400 whitespace-nowrap">{{ $tx->date->format('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <span class="text-gray-900 dark:text-white font-medium">{{ $tx->fromTenant?->name ?? '#'.$tx->from_tenant_id }}</span>
                                <span class="text-gray-400 mx-1">→</span>
                                <span class="text-gray-900 dark:text-white font-medium">{{ $tx->toTenant?->name ?? '#'.$tx->to_tenant_id }}</span>
                            </td>
                            <td class="px-4 py-3 hidden sm:table-cell text-xs text-gray-600 dark:text-slate-400">{{ $typeLabels[$tx->type] ?? $tx->type }}</td>
                            <td class="px-4 py-3 hidden md:table-cell text-xs text-gray-500 dark:text-slate-400 max-w-xs truncate">{{ $tx->description ?? '-' }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">Rp {{ number_format($tx->amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-xs {{ $tx->status === 'posted' ? 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400' : ($tx->status === 'voided' ? 'bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-slate-500' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400') }}">
                                    {{ ucfirst($tx->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($tx->status === 'pending')
                                <div class="flex items-center justify-center gap-1">
                                    <form method="POST" action="{{ route('company-groups.transactions.post', $tx) }}">
                                        @csrf
                                        <button class="text-xs px-2 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700">Post</button>
                                    </form>
                                    <form method="POST" action="{{ route('company-groups.transactions.void', $tx) }}" onsubmit="return confirm('Void transaksi ini?')">
                                        @csrf
                                        <button class="text-xs px-2 py-1 text-red-400 hover:text-red-600">Void</button>
                                    </form>
                                </div>
                                @elseif($tx->status === 'posted')
                                <span class="text-xs text-green-500">✓</span>
                                @else
                                <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400 dark:text-slate-500">Belum ada transaksi intercompany.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
