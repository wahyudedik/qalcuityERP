<x-app-layout>
    <x-slot name="header">Subscription — {{ $customerSubscription->subscription_number }}</x-slot>

    @php $sub = $customerSubscription; @endphp
    <div class="space-y-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ $sub->plan?->name ?? '-' }}</h2>
                    <p class="text-sm text-gray-500">{{ $sub->subscription_number }} · 👤 {{ $sub->customer?->name ?? '-' }}</p>
                </div>
                <div class="flex items-center gap-2">
                    @php $sc = ['trial'=>'blue','active'=>'green','past_due'=>'red','cancelled'=>'gray','expired'=>'gray'][$sub->status] ?? 'gray'; @endphp
                    <span class="px-3 py-1 rounded-full text-sm font-medium bg-{{ $sc  }}-100 text-{{ $sc }}-700 $sc }}-500/20 $sc }}-400">{{ ucfirst(str_replace('_', ' ', $sub->status)) }}</span>
                    @if(in_array($sub->status, ['active', 'trial']))
                    @canmodule('subscription_billing', 'edit')
                    <form method="POST" action="{{ route('subscription-billing.cancel', $sub) }}" data-confirm="Batalkan subscription ini?">@csrf @method('PATCH')
                        <button type="submit" class="px-3 py-1 text-sm bg-red-600 text-white rounded-xl hover:bg-red-700">Cancel</button>
                    </form>
                    @endcanmodule
                    @endif
                </div>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div><p class="text-xs text-gray-500">Mulai</p><p class="text-gray-900">{{ $sub->start_date->format('d/m/Y') }}</p></div>
                <div><p class="text-xs text-gray-500">Siklus</p><p class="text-gray-900">{{ $sub->plan?->cycleLabel() ?? '-' }}</p></div>
                <div><p class="text-xs text-gray-500">Harga Efektif</p><p class="font-semibold text-gray-900">Rp {{ number_format($sub->effectivePrice(), 0, ',', '.') }}</p></div>
                <div><p class="text-xs text-gray-500">MRR</p><p class="font-semibold text-green-500">Rp {{ number_format($sub->mrr(), 0, ',', '.') }}</p></div>
                <div><p class="text-xs text-gray-500">Next Billing</p><p class="{{ $sub->next_billing_date->isPast() ? 'text-red-500' : 'text-gray-900' }}">{{ $sub->next_billing_date->format('d/m/Y') }}</p></div>
                <div><p class="text-xs text-gray-500">Auto Renew</p><p class="text-gray-900">{{ $sub->auto_renew ? '✅ Ya' : '❌ Tidak' }}</p></div>
                @if($sub->discount_pct > 0)<div><p class="text-xs text-gray-500">Diskon</p><p class="text-gray-900">{{ $sub->discount_pct }}%</p></div>@endif
                @if($sub->trial_ends_at)<div><p class="text-xs text-gray-500">Trial Ends</p><p class="text-gray-900">{{ $sub->trial_ends_at->format('d/m/Y') }}</p></div>@endif
            </div>
        </div>

        {{-- Generate button --}}
        @if(in_array($sub->status, ['active']) && $sub->next_billing_date->lte(today()))
        @canmodule('subscription_billing', 'create')
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-center justify-between">
            <p class="text-sm text-amber-700">Billing jatuh tempo: {{ $sub->next_billing_date->format('d/m/Y') }}</p>
            <form method="POST" action="{{ route('subscription-billing.generate', $sub) }}">@csrf
                <button type="submit" class="px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">Generate Invoice</button>
            </form>
        </div>
        @endcanmodule
        @endif

        {{-- Invoice History --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Riwayat Invoice</h3>
            </div>
            @if($sub->invoices->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr><th class="px-4 py-3 text-left">Invoice</th><th class="px-4 py-3 text-center">Periode</th><th class="px-4 py-3 text-right">Jumlah</th><th class="px-4 py-3 text-right">Diskon</th><th class="px-4 py-3 text-right">Net</th><th class="px-4 py-3 text-center">Status</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($sub->invoices->sortByDesc('billing_date') as $si)
                        @php $ic = ['pending'=>'gray','invoiced'=>'blue','paid'=>'green','failed'=>'red'][$si->status] ?? 'gray'; @endphp
                        <tr>
                            <td class="px-4 py-3 font-mono text-xs text-gray-900">{{ $si->invoice?->number ?? '-' }}</td>
                            <td class="px-4 py-3 text-center text-xs text-gray-500">{{ $si->period_start->format('d/m') }} — {{ $si->period_end->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-right text-gray-700">Rp {{ number_format($si->amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-gray-500">{{ $si->discount > 0 ? 'Rp ' . number_format($si->discount, 0, ',', '.') : '-' }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">Rp {{ number_format($si->net_amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center"><span class="px-2 py-0.5 rounded-full text-xs bg-{{ $ic  }}-100 text-{{ $ic }}-700 $ic }}-500/20 $ic }}-400">{{ ucfirst($si->status) }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="px-6 py-8 text-center text-gray-400 text-sm">Belum ada invoice.</div>
            @endif
        </div>
    </div>
</x-app-layout>
