<x-app-layout>
    <x-slot name="header">Komisi Sales</x-slot>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Total Penjualan</p>
            <p class="text-lg font-bold text-gray-900">Rp {{ number_format($stats['total_sales'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Total Komisi</p>
            <p class="text-lg font-bold text-green-500">Rp {{ number_format($stats['total_commission'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Salesperson</p>
            <p class="text-2xl font-bold text-blue-500">{{ $stats['salespeople'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Approved</p>
            <p class="text-2xl font-bold text-amber-500">{{ $stats['approved'] }}</p>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <form method="GET" class="flex items-center gap-2">
            <input type="month" name="period" value="{{ $period }}"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900">
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Lihat</button>
        </form>
        <div class="flex-1"></div>
        <div class="flex gap-2">
            @canmodule('commission', 'create')
            <form method="POST" action="{{ route('commission.calculate') }}">
                @csrf <input type="hidden" name="period" value="{{ $period }}">
                <button type="submit" class="px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">Hitung Komisi</button>
            </form>
            <button onclick="document.getElementById('modal-target').classList.remove('hidden')"
                class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Set Target</button>
            @endcanmodule
        </div>
    </div>

    {{-- Commission Table --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Salesperson</th>
                        <th class="px-4 py-3 text-right">Target</th>
                        <th class="px-4 py-3 text-right">Penjualan</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Achievement</th>
                        <th class="px-4 py-3 text-right">Komisi</th>
                        <th class="px-4 py-3 text-right hidden sm:table-cell">Bonus</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($calculations as $c)
                    @php
                        $target = $targets[$c->user_id] ?? null;
                        $ach = $target ? $target->achievement_pct : null;
                        $cc = ['draft'=>'gray','approved'=>'amber','paid'=>'green'][$c->status] ?? 'gray';
                        $cl = ['draft'=>'Draft','approved'=>'Approved','paid'=>'Dibayar'][$c->status] ?? $c->status;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-900">{{ $c->user?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-right text-gray-500">{{ $target ? 'Rp ' . number_format($target->target_amount, 0, ',', '.') : '-' }}</td>
                        <td class="px-4 py-3 text-right text-gray-900">Rp {{ number_format($c->total_sales, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center hidden sm:table-cell">
                            @if($ach !== null)
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full {{ $ach >= 100 ? 'bg-green-500' : ($ach >= 80 ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ min($ach, 100) }}%"></div>
                                </div>
                                <span class="text-xs font-medium {{ $ach >= 100 ? 'text-green-500' : 'text-gray-500' }}">{{ $ach }}%</span>
                            </div>
                            @else <span class="text-gray-400">—</span> @endif
                        </td>
                        <td class="px-4 py-3 text-right text-gray-900">Rp {{ number_format($c->commission_amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right hidden sm:table-cell {{ $c->bonus_amount > 0 ? 'text-green-500' : 'text-gray-400' }}">{{ $c->bonus_amount > 0 ? 'Rp ' . number_format($c->bonus_amount, 0, ',', '.') : '—' }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">Rp {{ number_format($c->total_payout, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center"><span class="px-2 py-0.5 rounded-full text-xs bg-{{ $cc  }}-100 text-{{ $cc }}-700 $cc }}-500/20 $cc }}-400">{{ $cl }}</span></td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                @canmodule('commission', 'edit')
                                @if($c->status === 'draft')
                                <form method="POST" action="{{ url('commission') }}/{{ $c->id }}/approve">@csrf @method('PATCH')
                                    <button type="submit" class="text-xs px-2 py-1 bg-amber-600 text-white rounded-lg hover:bg-amber-700">Approve</button>
                                </form>
                                @elseif($c->status === 'approved')
                                <form method="POST" action="{{ url('commission') }}/{{ $c->id }}/pay" data-confirm="Bayar komisi?">@csrf
                                    <button type="submit" class="text-xs px-2 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700">Bayar</button>
                                </form>
                                @endif
                                @endcanmodule
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="px-4 py-12 text-center text-gray-400">Belum ada data komisi. Klik "Hitung Komisi" untuk generate.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Set Target --}}
    <div id="modal-target" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Set Target Sales</h3>
                <button onclick="document.getElementById('modal-target').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">?</button>
            </div>
            <form method="POST" action="{{ route('commission.targets.store') }}" class="p-6 space-y-4">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900'; @endphp
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Salesperson *</label>
                    <select name="user_id" required class="{{ $cls }}"><option value="">-- Pilih --</option>
                        @foreach($salespeople ?? [] as $sp)<option value="{{ $sp->id }}">{{ $sp->name }} ({{ $sp->role }})</option>@endforeach
                    </select>
                </div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Periode *</label>
                    <input type="month" name="period" required value="{{ $period }}" class="{{ $cls }}">
                </div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Target (Rp) *</label>
                    <input type="number" name="target_amount" required min="0" step="100000" class="{{ $cls }}">
                </div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Rule Komisi</label>
                    <select name="commission_rule_id" class="{{ $cls }}"><option value="">-- Default --</option>
                        @foreach($rules ?? [] as $r)<option value="{{ $r->id }}">{{ $r->name }} ({{ $r->type === 'flat_pct' ? $r->rate . '%' : ($r->type === 'flat_amount' ? 'Rp ' . number_format($r->rate, 0, ',', '.') : 'Tiered') }})</option>@endforeach
                    </select>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-target').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
