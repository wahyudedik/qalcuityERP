<x-app-layout>
    <x-slot name="header">Withdraw Affiliate</x-slot>

    <div class="flex gap-2 mb-4">
        @foreach([''=>'Semua','pending'=>'Pending','completed'=>'Completed','rejected'=>'Rejected'] as $v=>$l)
        <a href="?status={{ $v }}" class="px-3 py-1.5 text-xs rounded-xl {{ request('status')===$v ? 'bg-blue-600 text-white' : 'bg-white/5 border border-white/10 text-slate-300 hover:bg-white/10' }}">{{ $l }}</a>
        @endforeach
    </div>

    <div class="bg-[#1e293b] border border-white/10 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-white/5 text-xs text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Affiliate</th>
                        <th class="px-4 py-3 text-right">Jumlah</th>
                        <th class="px-4 py-3 text-left">Rekening</th>
                        <th class="px-4 py-3 text-center">Diajukan</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($payouts as $p)
                    @php
                        $sc = ['pending'=>'amber','completed'=>'green','rejected'=>'red'][$p->status] ?? 'gray';
                        $aff = $p->affiliate;
                    @endphp
                    <tr class="hover:bg-white/5">
                        <td class="px-4 py-3">
                            <p class="text-white">{{ $aff->user->name ?? '-' }}</p>
                            <p class="text-xs text-slate-500">{{ $aff->user->email ?? '' }}</p>
                        </td>
                        <td class="px-4 py-3 text-right text-amber-400 font-bold">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-xs text-slate-400">
                            {{ $aff->bank_name ?? '-' }} {{ $aff->bank_account ?? '' }}<br>
                            a/n {{ $aff->bank_holder ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-center text-xs text-slate-400">{{ $p->requested_at?->format('d/m/Y H:i') ?? $p->created_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs bg-{{ $sc }}-500/20 text-{{ $sc }}-400">{{ ucfirst($p->status) }}</span>
                            @if($p->reject_reason)<p class="text-xs text-red-400 mt-1">{{ $p->reject_reason }}</p>@endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($p->status === 'pending')
                            <div class="flex items-center justify-center gap-1">
                                <form method="POST" action="{{ route('super-admin.affiliates.payouts.approve', $p) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-xs px-2 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700" onclick="return confirm('Approve withdraw ini? Saldo affiliate akan dikurangi.')">✓ Approve</button>
                                </form>
                                <form method="POST" action="{{ route('super-admin.affiliates.payouts.reject', $p) }}" onsubmit="const r=prompt('Alasan reject:'); if(!r) return false; this.querySelector('[name=reason]').value=r;">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="reason" value="">
                                    <button type="submit" class="text-xs px-2 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700">✕ Reject</button>
                                </form>
                            </div>
                            @elseif($p->processor)
                            <span class="text-xs text-slate-500">{{ $p->processor->name ?? '' }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-slate-500">Belum ada withdraw.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payouts->hasPages())<div class="px-4 py-3 border-t border-white/5">{{ $payouts->links() }}</div>@endif
    </div>
</x-app-layout>
