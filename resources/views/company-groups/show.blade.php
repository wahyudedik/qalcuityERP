<x-app-layout>
    <x-slot name="header">{{ $companyGroup->name }}</x-slot>

    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 rounded-xl text-sm text-green-700 dark:text-green-400">{{ session('success') }}</div>
    @endif

    {{-- Header Controls --}}
    <div class="flex flex-wrap items-center gap-3 mb-6">
        <form method="GET" class="flex items-center gap-2">
            <label class="text-sm text-gray-500 dark:text-slate-400">Periode:</label>
            <input type="month" name="period" value="{{ $period }}"
                class="rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-sm text-gray-900 dark:text-white px-3 py-2">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm hover:bg-blue-700">Tampilkan</button>
        </form>
        <a href="{{ route('company-groups.export', [$companyGroup, 'period' => $period]) }}"
           class="px-4 py-2 border border-gray-200 dark:border-white/10 text-gray-600 dark:text-slate-300 rounded-xl text-sm hover:bg-gray-50 dark:hover:bg-white/5 transition">
            📥 Export CSV
        </a>
        <a href="{{ route('company-groups.index') }}" class="text-sm text-gray-400 hover:text-gray-600 dark:hover:text-white ml-auto">← Kembali</a>
    </div>

    {{-- Consolidated KPIs --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        @php $profit = $report['consolidated_profit'] ?? 0; @endphp
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Omzet</p>
            <p class="text-lg font-bold text-green-500 mt-1">{{ $report['formatted']['total_revenue'] }}</p>
            <p class="text-[10px] text-gray-400">{{ count($report['revenues']) }} perusahaan</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Biaya</p>
            <p class="text-lg font-bold text-red-500 mt-1">{{ $report['formatted']['total_expense'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-amber-200 dark:border-amber-500/20 p-4">
            <p class="text-xs text-amber-600 dark:text-amber-400">Eliminasi IC</p>
            <p class="text-lg font-bold text-amber-500 mt-1">{{ $report['formatted']['elimination'] }}</p>
            <p class="text-[10px] text-gray-400">{{ count($report['elimination']['items'] ?? []) }} transaksi</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border {{ $profit >= 0 ? 'border-blue-200 dark:border-blue-500/20' : 'border-red-200 dark:border-red-500/20' }} p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Laba Konsolidasi</p>
            <p class="text-lg font-bold {{ $profit >= 0 ? 'text-blue-500' : 'text-red-500' }} mt-1">{{ $report['formatted']['cons_profit'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
        {{-- P&L per Company --}}
        <div class="xl:col-span-2 bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white text-sm">📊 Laba Rugi per Perusahaan</h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr><th class="px-4 py-3 text-left">Perusahaan</th><th class="px-4 py-3 text-right">Omzet</th><th class="px-4 py-3 text-right">Biaya</th><th class="px-4 py-3 text-right">Laba</th><th class="px-4 py-3 text-right">Margin</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @foreach($report['revenues'] as $tid => $rev)
                    @php $exp = $report['expenses'][$tid]['amount'] ?? 0; $p = $rev['amount'] - $exp; $margin = $rev['amount'] > 0 ? round($p / $rev['amount'] * 100, 1) : 0; @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $rev['name'] }}</td>
                        <td class="px-4 py-3 text-right text-green-500 font-mono">Rp {{ number_format($rev['amount'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-red-500 font-mono">Rp {{ number_format($exp, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-mono font-medium {{ $p >= 0 ? 'text-blue-500' : 'text-red-500' }}">Rp {{ number_format($p, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-xs {{ $margin >= 20 ? 'text-green-500' : ($margin >= 0 ? 'text-amber-500' : 'text-red-500') }}">{{ $margin }}%</td>
                    </tr>
                    @endforeach
                    <tr class="bg-gray-50 dark:bg-white/5 font-bold">
                        <td class="px-4 py-3 text-gray-900 dark:text-white">TOTAL KONSOLIDASI</td>
                        <td class="px-4 py-3 text-right text-green-500 font-mono">{{ $report['formatted']['total_revenue'] }}</td>
                        <td class="px-4 py-3 text-right text-red-500 font-mono">{{ $report['formatted']['total_expense'] }}</td>
                        <td class="px-4 py-3 text-right font-mono {{ ($report['consolidated_profit'] ?? 0) >= 0 ? 'text-blue-500' : 'text-red-500' }}">{{ $report['formatted']['cons_profit'] }}</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Cash Flow Summary --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-4">💧 Arus Kas Konsolidasi</h3>
            <div class="space-y-3">
                @foreach($cashFlow['per_member'] ?? [] as $cf)
                <div class="p-3 rounded-xl bg-gray-50 dark:bg-white/5">
                    <p class="text-xs font-medium text-gray-700 dark:text-slate-300">{{ $cf['name'] }}</p>
                    <div class="flex justify-between mt-1 text-xs">
                        <span class="text-green-500">+Rp {{ number_format($cf['inflow'], 0, ',', '.') }}</span>
                        <span class="text-red-500">-Rp {{ number_format($cf['outflow'], 0, ',', '.') }}</span>
                        <span class="font-bold {{ $cf['net'] >= 0 ? 'text-blue-500' : 'text-red-500' }}">= Rp {{ number_format($cf['net'], 0, ',', '.') }}</span>
                    </div>
                </div>
                @endforeach
                <div class="border-t border-gray-100 dark:border-white/10 pt-3">
                    <div class="flex justify-between text-sm font-bold">
                        <span class="text-gray-900 dark:text-white">Net Cash</span>
                        <span class="{{ ($cashFlow['net_change'] ?? 0) >= 0 ? 'text-blue-500' : 'text-red-500' }}">Rp {{ number_format($cashFlow['net_change'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Balance Sheet --}}
    @if(!empty($report['balance_sheet']))
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-white/10">
            <h3 class="font-semibold text-gray-900 dark:text-white text-sm">🏦 Neraca Konsolidasi</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-gray-100 dark:divide-white/10">
            @foreach($report['balance_sheet'] as $type => $data)
            <div class="p-5">
                <p class="text-xs font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider mb-3">{{ $data['label'] }}</p>
                <p class="text-xl font-black text-gray-900 dark:text-white mb-3">Rp {{ number_format($data['total'], 0, ',', '.') }}</p>
                <div class="space-y-1.5">
                    @foreach($data['per_member'] as $m)
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-500 dark:text-slate-400">{{ $m['name'] }}</span>
                        <span class="font-mono text-gray-700 dark:text-slate-300">Rp {{ number_format($m['amount'], 0, ',', '.') }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Trend Chart --}}
    @if(!empty($trend))
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5 mb-6">
        <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-4">📈 Tren 6 Bulan Terakhir</h3>
        <div class="space-y-2">
            @php $maxRev = collect($trend)->max('revenue') ?: 1; @endphp
            @foreach($trend as $t)
            <div class="flex items-center gap-3">
                <span class="text-xs text-gray-400 w-16 shrink-0">{{ $t['label'] }}</span>
                <div class="flex-1 bg-gray-100 dark:bg-white/10 rounded-full h-2">
                    <div class="h-2 rounded-full bg-blue-500" style="width:{{ min(100, round($t['revenue'] / $maxRev * 100)) }}%"></div>
                </div>
                <span class="text-xs font-mono text-gray-600 dark:text-slate-300 w-28 text-right">Rp {{ number_format($t['revenue'], 0, ',', '.') }}</span>
                <span class="text-xs {{ $t['profit'] >= 0 ? 'text-green-500' : 'text-red-500' }} w-24 text-right">{{ $t['profit'] >= 0 ? '+' : '' }}Rp {{ number_format($t['profit'], 0, ',', '.') }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Members --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-4">🏢 Anggota Grup ({{ $companyGroup->members->count() }})</h3>
            <div class="space-y-2 mb-4">
                @foreach($companyGroup->members as $member)
                <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-white/5">
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $member->name }}</p>
                        <p class="text-xs text-gray-400">{{ ucfirst($member->pivot->role) }} · ID #{{ $member->id }}</p>
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
            <form method="POST" action="{{ route('company-groups.members.add', $companyGroup) }}" class="space-y-2">
                @csrf
                <select name="tenant_id" required class="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-sm text-gray-900 dark:text-white px-3 py-2">
                    <option value="">— Pilih perusahaan —</option>
                    @foreach($availableTenants as $t)
                    <option value="{{ $t->id }}">{{ $t->name }} (#{{ $t->id }})</option>
                    @endforeach
                </select>
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-xl text-sm hover:bg-blue-700">+ Tambah Anggota</button>
            </form>
        </div>

        {{-- New Transaction Form --}}
        <div class="lg:col-span-2 bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-4">🔄 Catat Transaksi Intercompany</h3>
            <form method="POST" action="{{ route('company-groups.transactions.store', $companyGroup) }}" class="space-y-4">
                @csrf
                @php $cls = 'w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-sm text-gray-900 dark:text-white px-3 py-2'; @endphp
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Dari Perusahaan *</label>
                        <select name="from_tenant_id" required class="{{ $cls }}">
                            <option value="">Pilih...</option>
                            @foreach($companyGroup->members as $m)
                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Ke Perusahaan *</label>
                        <select name="to_tenant_id" required class="{{ $cls }}">
                            <option value="">Pilih...</option>
                            @foreach($companyGroup->members as $m)
                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tipe *</label>
                        <select name="type" required class="{{ $cls }}">
                            <option value="sale">🛒 Penjualan Antar Entitas</option>
                            <option value="loan">💰 Pinjaman Antar Entitas</option>
                            <option value="expense_allocation">📊 Alokasi Biaya</option>
                            <option value="dividend">💵 Dividen</option>
                            <option value="management_fee">🏢 Management Fee</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Jumlah (Rp) *</label>
                        <input type="number" name="amount" min="1" step="1000" required placeholder="0" class="{{ $cls }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanggal *</label>
                        <input type="date" name="date" value="{{ today()->toDateString() }}" required class="{{ $cls }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Deskripsi</label>
                        <input type="text" name="description" placeholder="Opsional" class="{{ $cls }}">
                    </div>
                </div>
                <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700">Catat Transaksi</button>
            </form>
        </div>
    </div>

    {{-- Transaction History --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden mt-6">
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
                        <th class="px-4 py-3 text-left">Tipe</th>
                        <th class="px-4 py-3 text-left">Ref</th>
                        <th class="px-4 py-3 text-right">Jumlah</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($transactions as $tx)
                    @php $typeLabels = ['sale'=>'🛒 Penjualan','loan'=>'💰 Pinjaman','expense_allocation'=>'📊 Alokasi','dividend'=>'💵 Dividen','management_fee'=>'🏢 Mgmt Fee']; @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">{{ $tx->date->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-xs">
                            <span class="font-medium text-gray-900 dark:text-white">{{ $tx->fromTenant?->name ?? '#'.$tx->from_tenant_id }}</span>
                            <span class="text-gray-400 mx-1">→</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $tx->toTenant?->name ?? '#'.$tx->to_tenant_id }}</span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $typeLabels[$tx->type] ?? $tx->type }}</td>
                        <td class="px-4 py-3 text-xs font-mono text-gray-400">{{ $tx->reference }}</td>
                        <td class="px-4 py-3 text-right font-mono font-medium text-gray-900 dark:text-white">Rp {{ number_format($tx->amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs {{ $tx->status === 'posted' ? 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400' : ($tx->status === 'voided' ? 'bg-gray-100 text-gray-500' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400') }}">
                                {{ ucfirst($tx->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($tx->status === 'pending')
                            <div class="flex items-center justify-center gap-1">
                                <form method="POST" action="{{ route('company-groups.transactions.post', $tx) }}">@csrf
                                    <button class="text-xs px-2 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700">Post</button>
                                </form>
                                <form method="POST" action="{{ route('company-groups.transactions.void', $tx) }}" onsubmit="return confirm('Void?')">@csrf
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
                    <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">Belum ada transaksi intercompany.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3">{{ $transactions->links() }}</div>
    </div>
</x-app-layout>
