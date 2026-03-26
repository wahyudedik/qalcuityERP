<x-app-layout>
    <x-slot name="header">Komisi Affiliate</x-slot>

    <div class="flex gap-2 mb-4">
        @foreach([''=>'Semua','pending'=>'Pending','approved'=>'Approved','paid'=>'Paid'] as $v=>$l)
        <a href="?status={{ $v }}" class="px-3 py-1.5 text-xs rounded-xl {{ request('status')===$v ? 'bg-blue-600 text-white' : 'bg-white/5 border border-white/10 text-slate-300 hover:bg-white/10' }}">{{ $l }}</a>
        @endforeach
    </div>

    <div class="bg-[#1e293b] border border-white/10 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-white/5 text-xs text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Affiliate</th>
                        <th class="px-4 py-3 text-left">Tenant</th>
                        <th class="px-4 py-3 text-left">Plan</th>
                        <th class="px-4 py-3 text-right">Pembayaran</th>
                        <th class="px-4 py-3 text-right">Komisi</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($commissions as $c)
                    @php $sc = ['pending'=>'amber','approved'=>'blue','paid'=>'green','rejected'=>'red'][$c->status] ?? 'gray'; @endphp
                    <tr class="hover:bg-white/5">
                        <td class="px-4 py-3 text-white">{{ $c->affiliate->user->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-300 text-xs">{{ $c->tenant->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-400 text-xs">{{ $c->plan_name }}</td>
                        <td class="px-4 py-3 text-right text-white">Rp {{ number_format($c->payment_amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-amber-400 font-medium">Rp {{ number_format($c->commission_amount, 0, ',', '.') }} <span class="text-xs text-slate-500">({{ $c->commission_rate }}%)</span></td>
                        <td class="px-4 py-3 text-center"><span class="px-2 py-0.5 rounded-full text-xs bg-{{ $sc }}-500/20 text-{{ $sc }}-400">{{ ucfirst($c->status) }}</span></td>
                        <td class="px-4 py-3 text-center">
                            @if($c->status === 'pending')
                            <form method="POST" action="{{ route('super-admin.affiliates.commissions.approve', $c) }}" class="inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="text-xs px-2 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Approve</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-slate-500">Belum ada komisi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($commissions->hasPages())<div class="px-4 py-3 border-t border-white/5">{{ $commissions->links() }}</div>@endif
    </div>
</x-app-layout>
