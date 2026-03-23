<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('company-groups.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">←</a>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">{{ $companyGroup->name }}</h2>
        </div>
    </x-slot>

    <div class="py-6 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if(session('success'))
            <div class="p-3 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-lg text-sm">{{ session('success') }}</div>
        @endif

        <!-- Period Selector -->
        <form method="GET" class="flex items-center gap-3">
            <label class="text-sm text-gray-600 dark:text-gray-400">Periode:</label>
            <input type="month" name="period" value="{{ $period }}"
                   class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
            <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">Tampilkan</button>
        </form>

        <!-- Consolidated KPIs -->
        @if(!empty($report))
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach([
                    ['label' => 'Total Omzet', 'value' => $report['formatted']['total_revenue'], 'color' => 'text-green-600'],
                    ['label' => 'Total Biaya', 'value' => $report['formatted']['total_expense'], 'color' => 'text-red-500'],
                    ['label' => 'Eliminasi IC', 'value' => $report['formatted']['elimination'], 'color' => 'text-yellow-600'],
                    ['label' => 'Laba Konsolidasi', 'value' => $report['formatted']['cons_profit'], 'color' => ($report['consolidated_profit'] ?? 0) >= 0 ? 'text-blue-600' : 'text-red-500'],
                ] as $kpi)
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 text-center">
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $kpi['label'] }}</p>
                        <p class="text-lg font-bold {{ $kpi['color'] }} mt-1">{{ $kpi['value'] }}</p>
                    </div>
                @endforeach
            </div>

            <!-- Per-Company Breakdown -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5">
                <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-4">Breakdown per Perusahaan</h3>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-xs text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
                            <th class="text-left py-2">Perusahaan</th>
                            <th class="text-right py-2">Omzet</th>
                            <th class="text-right py-2">Biaya</th>
                            <th class="text-right py-2">Laba</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report['revenues'] as $tid => $rev)
                            @php
                                $exp = $report['expenses'][$tid]['amount'] ?? 0;
                                $profit = $rev['amount'] - $exp;
                                $fmt = fn($n) => 'Rp ' . number_format(abs($n), 0, ',', '.');
                            @endphp
                            <tr class="border-b border-gray-100 dark:border-gray-700">
                                <td class="py-2 font-medium text-gray-700 dark:text-gray-300">{{ $rev['name'] }}</td>
                                <td class="text-right py-2 text-green-600">{{ $fmt($rev['amount']) }}</td>
                                <td class="text-right py-2 text-red-500">{{ $fmt($exp) }}</td>
                                <td class="text-right py-2 {{ $profit >= 0 ? 'text-blue-600' : 'text-red-500' }}">
                                    {{ ($profit >= 0 ? '' : '-') . $fmt($profit) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Members -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5">
                <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-3">Anggota Grup</h3>
                <div class="space-y-2 mb-4">
                    @foreach($companyGroup->members as $member)
                        <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                            <div>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $member->name }}</p>
                                <p class="text-xs text-gray-400">{{ $member->pivot->role }}</p>
                            </div>
                            @if($member->pivot->role !== 'owner')
                                <form method="POST" action="{{ route('company-groups.members.remove', [$companyGroup, $member]) }}">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-red-500 hover:text-red-700">Hapus</button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
                <form method="POST" action="{{ route('company-groups.members.add', $companyGroup) }}" class="flex gap-2">
                    @csrf
                    <input type="number" name="tenant_id" placeholder="ID Tenant" required
                           class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                    <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">
                        Tambah
                    </button>
                </form>
            </div>

            <!-- Intercompany Transactions -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5">
                <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-3">Transaksi Intercompany</h3>
                <form method="POST" action="{{ route('company-groups.transactions.store', $companyGroup) }}" class="space-y-2 mb-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="text-xs text-gray-500">Dari (Tenant ID)</label>
                            <input type="number" name="from_tenant_id" required
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">Ke (Tenant ID)</label>
                            <input type="number" name="to_tenant_id" required
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">Tipe</label>
                            <select name="type" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-slate-800 dark:text-white text-sm">
                                <option value="sale">Penjualan</option>
                                <option value="loan">Pinjaman</option>
                                <option value="expense_allocation">Alokasi Biaya</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">Jumlah (Rp)</label>
                            <input type="number" name="amount" min="1" required
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                        </div>
                        <div class="col-span-2">
                            <label class="text-xs text-gray-500">Tanggal</label>
                            <input type="date" name="date" value="{{ today()->toDateString() }}" required
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                        </div>
                    </div>
                    <button type="submit" class="w-full px-3 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">
                        Catat Transaksi
                    </button>
                </form>

                <div class="space-y-2 max-h-48 overflow-y-auto">
                    @foreach($transactions as $tx)
                        <div class="flex items-center justify-between text-xs py-1.5 border-b border-gray-100 dark:border-gray-700">
                            <div>
                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $tx->fromTenant?->name }} → {{ $tx->toTenant?->name }}</span>
                                <span class="text-gray-400 ml-1">({{ $tx->type }})</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-medium">Rp {{ number_format($tx->amount, 0, ',', '.') }}</span>
                                @if($tx->status === 'pending')
                                    <form method="POST" action="{{ route('company-groups.transactions.post', $tx) }}">
                                        @csrf
                                        <button class="text-blue-500 hover:text-blue-700">Post</button>
                                    </form>
                                @else
                                    <span class="text-green-500">✓</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
