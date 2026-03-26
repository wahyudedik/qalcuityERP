<x-app-layout>
    <x-slot name="header">Kelola Affiliate</x-slot>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        @foreach([
            ['label'=>'Total','value'=>$stats['total'],'color'=>'text-white'],
            ['label'=>'Aktif','value'=>$stats['active'],'color'=>'text-green-400'],
            ['label'=>'Referral','value'=>$stats['total_referrals'],'color'=>'text-blue-400'],
            ['label'=>'Total Earned','value'=>'Rp '.number_format($stats['total_earned'],0,',','.'),'color'=>'text-amber-400'],
            ['label'=>'Pending Withdraw','value'=>'Rp '.number_format($stats['pending_withdraw'],0,',','.'),'color'=>'text-red-400'],
            ['label'=>'Fraud Alerts','value'=>$stats['fraud_alerts'],'color'=>$stats['fraud_alerts'] > 0 ? 'text-red-400' : 'text-green-400'],
        ] as $s)
        <div class="bg-[#1e293b] border border-white/10 rounded-2xl p-4">
            <p class="text-[10px] text-slate-500 uppercase tracking-wider mb-1">{{ $s['label'] }}</p>
            <p class="text-lg font-bold {{ $s['color'] }}">{{ $s['value'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <form method="GET" class="flex-1 flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / email..."
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-white/10 bg-white/5 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
        </form>
        <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Buat Affiliate</button>
    </div>

    {{-- Table --}}
    <div class="bg-[#1e293b] border border-white/10 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-white/5 text-xs text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Affiliate</th>
                        <th class="px-4 py-3 text-left">Kode</th>
                        <th class="px-4 py-3 text-center">Referral</th>
                        <th class="px-4 py-3 text-right">Earned</th>
                        <th class="px-4 py-3 text-right">Balance</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($affiliates as $aff)
                    <tr class="hover:bg-white/5">
                        <td class="px-4 py-3">
                            <p class="text-white font-medium">{{ $aff->user->name ?? '-' }}</p>
                            <p class="text-xs text-slate-500">{{ $aff->user->email ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-blue-400">{{ $aff->code }}</td>
                        <td class="px-4 py-3 text-center text-white">{{ $aff->referrals_count }}</td>
                        <td class="px-4 py-3 text-right text-white">Rp {{ number_format($aff->total_earned, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right {{ $aff->balance > 0 ? 'text-amber-400' : 'text-slate-500' }}">Rp {{ number_format($aff->balance, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($aff->is_active)<span class="px-2 py-0.5 rounded-full text-xs bg-green-500/20 text-green-400">Aktif</span>
                            @else<span class="px-2 py-0.5 rounded-full text-xs bg-red-500/20 text-red-400">Nonaktif</span>@endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <form method="POST" action="{{ route('super-admin.affiliates.toggle', $aff) }}" class="inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="text-xs px-2 py-1 border border-white/10 rounded-lg text-slate-300 hover:bg-white/10">{{ $aff->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-slate-500">Belum ada affiliate.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($affiliates->hasPages())<div class="px-4 py-3 border-t border-white/5">{{ $affiliates->links() }}</div>@endif
    </div>

    {{-- Modal Create Affiliate --}}
    <div id="modal-add" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70">
        <div class="bg-[#1e293b] border border-white/10 rounded-2xl w-full max-w-md shadow-2xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-white/10">
                <h3 class="font-semibold text-white">Buat Affiliate Baru</h3>
                <button onclick="document.getElementById('modal-add').classList.add('hidden')" class="text-slate-400 hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('super-admin.affiliates.store') }}" class="p-6 space-y-4">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-white/10 bg-white/5 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500'; @endphp
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2"><label class="block text-xs text-slate-400 mb-1">Nama *</label><input type="text" name="name" required class="{{ $cls }}"></div>
                    <div><label class="block text-xs text-slate-400 mb-1">Email *</label><input type="email" name="email" required class="{{ $cls }}"></div>
                    <div><label class="block text-xs text-slate-400 mb-1">Password *</label><input type="password" name="password" required minlength="8" class="{{ $cls }}"></div>
                    <div><label class="block text-xs text-slate-400 mb-1">Telepon</label><input type="text" name="phone" class="{{ $cls }}"></div>
                    <div><label class="block text-xs text-slate-400 mb-1">Perusahaan</label><input type="text" name="company_name" class="{{ $cls }}"></div>
                    <div><label class="block text-xs text-slate-400 mb-1">Bank</label><input type="text" name="bank_name" placeholder="BCA" class="{{ $cls }}"></div>
                    <div><label class="block text-xs text-slate-400 mb-1">No. Rekening</label><input type="text" name="bank_account" class="{{ $cls }}"></div>
                    <div><label class="block text-xs text-slate-400 mb-1">Atas Nama</label><input type="text" name="bank_holder" class="{{ $cls }}"></div>
                    <div><label class="block text-xs text-slate-400 mb-1">Komisi (%)</label><input type="number" name="commission_rate" min="0" max="50" step="0.5" value="10" class="{{ $cls }}"></div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')" class="px-4 py-2 text-sm border border-white/10 rounded-xl text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Buat Affiliate</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
