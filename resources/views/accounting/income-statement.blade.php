<x-app-layout>
    <x-slot name="header">Laporan Laba Rugi</x-slot>

    <div class="space-y-5">

        {{-- Filter --}}
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="text-xs text-gray-400 mb-1 block">Dari Tanggal</label>
                <input type="date" name="from" value="{{ $from }}"
                    class="bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
            </div>
            <div>
                <label class="text-xs text-gray-400 mb-1 block">Sampai Tanggal</label>
                <input type="date" name="to" value="{{ $to }}"
                    class="bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
            </div>
            <button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg">Tampilkan</button>
            <a href="{{ route('accounting.income-statement.pdf') }}?from={{ $from }}&to={{ $to }}"
               class="bg-red-600 hover:bg-red-700 text-white text-sm px-4 py-2 rounded-lg flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export PDF
            </a>
        </form>

        @php
            $fmt = fn($n) => 'Rp ' . number_format(abs($n), 0, ',', '.');
            $pct = fn($part, $total) => $total > 0 ? round(($part / $total) * 100, 1) . '%' : '-';
        @endphp

        <div class="max-w-2xl space-y-4">

            {{-- Pendapatan --}}
            <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
                <div class="px-4 py-2.5 bg-green-500/10 border-b border-white/10 flex justify-between">
                    <span class="text-xs font-semibold text-green-400 uppercase">Pendapatan</span>
                    <span class="text-xs text-gray-500">% dari total</span>
                </div>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-white/5">
                        @foreach($data['revenue']['items'] as $acc)
                        <tr class="hover:bg-white/5">
                            <td class="px-4 py-2.5 text-gray-300">
                                <span class="font-mono text-xs text-gray-500 mr-2">{{ $acc['code'] }}</span>{{ $acc['name'] }}
                            </td>
                            <td class="px-4 py-2.5 text-right text-white">{{ $fmt($acc['balance']) }}</td>
                            <td class="px-4 py-2.5 text-right text-gray-500 text-xs w-16">{{ $pct($acc['balance'], $data['revenue']['total']) }}</td>
                        </tr>
                        @endforeach
                        @if(empty($data['revenue']['items']->toArray()))
                        <tr><td colspan="3" class="px-4 py-4 text-center text-gray-500 text-xs">Tidak ada data pendapatan</td></tr>
                        @endif
                    </tbody>
                    <tfoot class="bg-green-500/10">
                        <tr>
                            <td class="px-4 py-2.5 font-semibold text-green-400">Total Pendapatan</td>
                            <td class="px-4 py-2.5 text-right font-bold text-green-400">{{ $fmt($data['revenue']['total']) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- HPP --}}
            @if($data['cogs']['items']->isNotEmpty())
            <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
                <div class="px-4 py-2.5 bg-white/5 border-b border-white/10">
                    <span class="text-xs font-semibold text-gray-400 uppercase">Harga Pokok Penjualan (HPP)</span>
                </div>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-white/5">
                        @foreach($data['cogs']['items'] as $acc)
                        <tr class="hover:bg-white/5">
                            <td class="px-4 py-2.5 text-gray-300">
                                <span class="font-mono text-xs text-gray-500 mr-2">{{ $acc['code'] }}</span>{{ $acc['name'] }}
                            </td>
                            <td class="px-4 py-2.5 text-right text-white">({{ $fmt($acc['balance']) }})</td>
                            <td class="px-4 py-2.5 text-right text-gray-500 text-xs w-16">{{ $pct($acc['balance'], $data['revenue']['total']) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-white/5">
                        <tr>
                            <td class="px-4 py-2.5 font-semibold text-gray-400">Total HPP</td>
                            <td class="px-4 py-2.5 text-right font-bold text-white">({{ $fmt($data['cogs']['total']) }})</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @endif

            {{-- Laba Kotor --}}
            <div class="bg-white/5 border border-white/10 rounded-xl px-4 py-3 flex justify-between items-center">
                <span class="font-semibold text-gray-300">Laba Kotor</span>
                <span class="font-bold {{ $data['gross_profit'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                    {{ $data['gross_profit'] < 0 ? '(' : '' }}{{ $fmt($data['gross_profit']) }}{{ $data['gross_profit'] < 0 ? ')' : '' }}
                </span>
            </div>

            {{-- Beban Operasional --}}
            @if($data['opex']['items']->isNotEmpty())
            <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
                <div class="px-4 py-2.5 bg-white/5 border-b border-white/10">
                    <span class="text-xs font-semibold text-gray-400 uppercase">Beban Operasional</span>
                </div>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-white/5">
                        @foreach($data['opex']['items'] as $acc)
                        <tr class="hover:bg-white/5">
                            <td class="px-4 py-2.5 text-gray-300">
                                <span class="font-mono text-xs text-gray-500 mr-2">{{ $acc['code'] }}</span>{{ $acc['name'] }}
                            </td>
                            <td class="px-4 py-2.5 text-right text-white">({{ $fmt($acc['balance']) }})</td>
                            <td class="px-4 py-2.5 text-right text-gray-500 text-xs w-16">{{ $pct($acc['balance'], $data['revenue']['total']) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-white/5">
                        <tr>
                            <td class="px-4 py-2.5 font-semibold text-gray-400">Total Beban Operasional</td>
                            <td class="px-4 py-2.5 text-right font-bold text-white">({{ $fmt($data['opex']['total']) }})</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @endif

            {{-- Laba Operasi --}}
            <div class="bg-white/5 border border-white/10 rounded-xl px-4 py-3 flex justify-between items-center">
                <span class="font-semibold text-gray-300">Laba Operasi</span>
                <span class="font-bold {{ $data['operating_income'] >= 0 ? 'text-blue-400' : 'text-red-400' }}">
                    {{ $data['operating_income'] < 0 ? '(' : '' }}{{ $fmt($data['operating_income']) }}{{ $data['operating_income'] < 0 ? ')' : '' }}
                </span>
            </div>

            {{-- Beban/Pendapatan Lain --}}
            @if($data['other_expense']['items']->isNotEmpty())
            <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
                <div class="px-4 py-2.5 bg-white/5 border-b border-white/10">
                    <span class="text-xs font-semibold text-gray-400 uppercase">Beban / Pendapatan Lain-lain</span>
                </div>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-white/5">
                        @foreach($data['other_expense']['items'] as $acc)
                        <tr class="hover:bg-white/5">
                            <td class="px-4 py-2.5 text-gray-300">
                                <span class="font-mono text-xs text-gray-500 mr-2">{{ $acc['code'] }}</span>{{ $acc['name'] }}
                            </td>
                            <td class="px-4 py-2.5 text-right text-white">({{ $fmt($acc['balance']) }})</td>
                            <td></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            {{-- Net Income --}}
            <div class="border-2 rounded-xl px-4 py-4 flex justify-between items-center
                {{ $data['net_income'] >= 0 ? 'bg-green-500/10 border-green-500/30' : 'bg-red-500/10 border-red-500/30' }}">
                <div>
                    <div class="font-bold text-white text-base">{{ $data['net_income'] >= 0 ? 'LABA BERSIH' : 'RUGI BERSIH' }}</div>
                    <div class="text-xs text-gray-400 mt-0.5">
                        {{ \Carbon\Carbon::parse($from)->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($to)->translatedFormat('d M Y') }}
                    </div>
                </div>
                <span class="font-bold text-xl {{ $data['net_income'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                    {{ $data['net_income'] < 0 ? '(' : '' }}Rp {{ number_format(abs($data['net_income']), 0, ',', '.') }}{{ $data['net_income'] < 0 ? ')' : '' }}
                </span>
            </div>

        </div>
    </div>
</x-app-layout>
