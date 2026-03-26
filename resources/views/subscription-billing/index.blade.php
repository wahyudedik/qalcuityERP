<x-app-layout>
    <x-slot name="header">Subscription Billing</x-slot>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Aktif</p>
            <p class="text-2xl font-bold text-green-500">{{ $stats['active'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Trial</p>
            <p class="text-2xl font-bold text-blue-500">{{ $stats['trial'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">MRR</p>
            <p class="text-lg font-bold text-gray-900 dark:text-white">Rp {{ number_format($stats['mrr'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Past Due</p>
            <p class="text-2xl font-bold text-red-500">{{ $stats['past_due'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Jatuh Tempo Hari Ini</p>
            <p class="text-2xl font-bold text-amber-500">{{ $stats['due_today'] }}</p>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <form method="GET" class="flex-1 flex flex-wrap gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari subscription / customer..."
                class="flex-1 min-w-[150px] px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="status" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                <option value="">Semua Status</option>
                @foreach(['trial'=>'Trial','active'=>'Aktif','past_due'=>'Past Due','cancelled'=>'Batal','expired'=>'Expired'] as $v=>$l)
                <option value="{{ $v }}" @selected(request('status')===$v)>{{ $l }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
        </form>
        <div class="flex gap-2">
            <a href="{{ route('subscription-billing.plans') }}" class="px-3 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Plans</a>
            @canmodule('subscription_billing', 'create')
            <form method="POST" action="{{ route('subscription-billing.bulk-generate') }}">@csrf
                <button type="submit" class="px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700" onclick="return confirm('Generate invoice untuk semua subscription jatuh tempo?')">⚡ Bulk Generate</button>
            </form>
            <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Subscription</button>
            @endcanmodule
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">No.</th>
                        <th class="px-4 py-3 text-left">Customer</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Plan</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Harga</th>
                        <th class="px-4 py-3 text-center hidden md:table-cell">Next Billing</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($subscriptions as $s)
                    @php
                        $sc = ['trial'=>'blue','active'=>'green','past_due'=>'red','cancelled'=>'gray','expired'=>'gray'][$s->status] ?? 'gray';
                        $sl = ['trial'=>'Trial','active'=>'Aktif','past_due'=>'Past Due','cancelled'=>'Batal','expired'=>'Expired'][$s->status] ?? $s->status;
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3 font-mono text-xs font-medium text-gray-900 dark:text-white">
                            <a href="{{ route('subscription-billing.show', $s) }}" class="hover:text-blue-500">{{ $s->subscription_number }}</a>
                        </td>
                        <td class="px-4 py-3 text-gray-700 dark:text-slate-300">{{ $s->customer->name ?? '-' }}</td>
                        <td class="px-4 py-3 hidden sm:table-cell text-gray-500 dark:text-slate-400 text-xs">{{ $s->plan->name ?? '-' }} ({{ $s->plan->cycleLabel() ?? '' }})</td>
                        <td class="px-4 py-3 text-right hidden md:table-cell text-gray-900 dark:text-white">Rp {{ number_format($s->effectivePrice(), 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center hidden md:table-cell text-xs {{ $s->next_billing_date->isPast() ? 'text-red-500 font-semibold' : 'text-gray-500 dark:text-slate-400' }}">{{ $s->next_billing_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-center"><span class="px-2 py-0.5 rounded-full text-xs bg-{{ $sc }}-100 text-{{ $sc }}-700 dark:bg-{{ $sc }}-500/20 dark:text-{{ $sc }}-400">{{ $sl }}</span></td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('subscription-billing.show', $s) }}" class="text-xs px-2 py-1 border border-gray-200 dark:border-white/10 rounded-lg text-gray-600 dark:text-slate-300">Detail</a>
                                @if(in_array($s->status, ['active', 'trial']) && $s->next_billing_date->lte(today()))
                                @canmodule('subscription_billing', 'create')
                                <form method="POST" action="{{ route('subscription-billing.generate', $s) }}" class="inline">@csrf
                                    <button type="submit" class="text-xs px-2 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700">Invoice</button>
                                </form>
                                @endcanmodule
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada subscription.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($subscriptions->hasPages())<div class="px-4 py-3 border-t border-gray-100 dark:border-white/5">{{ $subscriptions->links() }}</div>@endif
    </div>

    {{-- Modal Create Subscription --}}
    <div id="modal-add" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Buat Subscription</h3>
                <button onclick="document.getElementById('modal-add').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('subscription-billing.store') }}" class="p-6 space-y-4">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; @endphp
                <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Customer *</label>
                    <select name="customer_id" required class="{{ $cls }}"><option value="">-- Pilih --</option>
                        @foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                    </select>
                </div>
                <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Plan *</label>
                    <select name="plan_id" required class="{{ $cls }}"><option value="">-- Pilih --</option>
                        @foreach($plans as $p)<option value="{{ $p->id }}">{{ $p->name }} — Rp {{ number_format($p->price, 0, ',', '.') }}/{{ $p->cycleLabel() }}</option>@endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Mulai *</label><input type="date" name="start_date" required value="{{ date('Y-m-d') }}" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Diskon (%)</label><input type="number" name="discount_pct" min="0" max="100" step="0.01" value="0" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Harga Override</label><input type="number" name="price_override" min="0" step="1000" placeholder="Kosong = pakai harga plan" class="{{ $cls }}"></div>
                    <div class="flex items-end"><label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="auto_renew" value="1" checked class="rounded"><span class="text-sm text-gray-700 dark:text-slate-300">Auto Renew</span></label></div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Buat</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
