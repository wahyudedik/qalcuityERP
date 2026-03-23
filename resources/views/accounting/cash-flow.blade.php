<x-app-layout>
    <x-slot name="header">Laporan Arus Kas</x-slot>

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
            <a href="{{ route('accounting.cash-flow.pdf') }}?from={{ $from }}&to={{ $to }}"
               class="bg-red-600 hover:bg-red-700 text-white text-sm px-4 py-2 rounded-lg flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export PDF
            </a>
        </form>

        @php
            $fmt = fn($n) => ($n < 0 ? '(' : '') . 'Rp ' . number_format(abs($n), 0, ',', '.') . ($n < 0 ? ')' : '');
            $cls = fn($n) => $n >= 0 ? 'text-white' : 'text-red-400';
        @endphp

        {{-- Reconciliation badge --}}
        <div>
            @if($data['reconciled'])
                <span class="inline-flex items-center gap-1.5 bg-green-500/10 text-green-400 text-xs px-3 py-1.5 rounded-full border border-green-500/20">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    Saldo Kas Rekonsiliasi
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 bg-yellow-500/10 text-yellow-400 text-xs px-3 py-1.5 rounded-full border border-yellow-500/20">
                    ⚠ Saldo Kas Tidak Rekonsiliasi — periksa jurnal kas
                </span>
            @endif
        </div>

        <div class="max-w-2xl space-y-4">

            {{-- Saldo Kas Awal --}}
            <div class="bg-white/5 border border-white/10 rounded-xl px-4 py-3 flex justify-between items-center">
                <span class="text-gray-400 text-sm">Saldo Kas Awal Periode</span>
                <span class="font-semibold text-white">{{ $fmt($data['opening_cash']) }}</span>
            </div>

            {{-- Aktivitas Operasi --}}
            <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
                <div class="px-4 py-2.5 bg-blue-500/10 border-b border-white/10">
                    <span class="text-xs font-semibold text-blue-400 uppercase">I. Arus Kas dari Aktivitas Operasi</span>
                </div>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-white/5">
                        <tr class="hover:bg-white/5">
                            <td class="px-4 py-2.5 text-gray-300">Laba/Rugi Bersih</td>
                            <td class="px-4 py-2.5 text-right {{ $cls($data['operating']['net_income']) }}">
                                {{ $fmt($data['operating']['net_income']) }}
                            </td>
                        </tr>
                        @foreach($data['operating']['wc_adjustments'] as $item)
                        <tr class="hover:bg-white/5">
                            <td class="px-4 py-2.5 text-gray-400 pl-8 text-xs">{{ $item['label'] }}</td>
                            <td class="px-4 py-2.5 text-right text-xs {{ $cls($item['amount']) }}">{{ $fmt($item['amount']) }}</td>
                        </tr>
                        @endforeach
                        @if(empty($data['operating']['wc_adjustments']))
                        <tr><td colspan="2" class="px-4 py-2.5 text-center text-gray-500 text-xs">Tidak ada penyesuaian modal kerja</td></tr>
                        @endif
                    </tbody>
                    <tfoot class="bg-blue-500/10">
                        <tr>
                            <td class="px-4 py-2.5 font-semibold text-blue-400">Arus Kas Bersih dari Operasi</td>
                            <td class="px-4 py-2.5 text-right font-bold {{ $cls($data['operating']['total']) }}">{{ $fmt($data['operating']['total']) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Aktivitas Investasi --}}
            <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
                <div class="px-4 py-2.5 bg-purple-500/10 border-b border-white/10">
                    <span class="text-xs font-semibold text-purple-400 uppercase">II. Arus Kas dari Aktivitas Investasi</span>
                </div>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-white/5">
                        @forelse($data['investing']['items'] as $item)
                        <tr class="hover:bg-white/5">
                            <td class="px-4 py-2.5 text-gray-300">{{ $item['label'] }}</td>
                            <td class="px-4 py-2.5 text-right {{ $cls($item['amount']) }}">{{ $fmt($item['amount']) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="px-4 py-3 text-center text-gray-500 text-xs">Tidak ada aktivitas investasi</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-purple-500/10">
                        <tr>
                            <td class="px-4 py-2.5 font-semibold text-purple-400">Arus Kas Bersih dari Investasi</td>
                            <td class="px-4 py-2.5 text-right font-bold {{ $cls($data['investing']['total']) }}">{{ $fmt($data['investing']['total']) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Aktivitas Pendanaan --}}
            <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
                <div class="px-4 py-2.5 bg-orange-500/10 border-b border-white/10">
                    <span class="text-xs font-semibold text-orange-400 uppercase">III. Arus Kas dari Aktivitas Pendanaan</span>
                </div>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-white/5">
                        @forelse($data['financing']['items'] as $item)
                        <tr class="hover:bg-white/5">
                            <td class="px-4 py-2.5 text-gray-300">{{ $item['label'] }}</td>
                            <td class="px-4 py-2.5 text-right {{ $cls($item['amount']) }}">{{ $fmt($item['amount']) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="px-4 py-3 text-center text-gray-500 text-xs">Tidak ada aktivitas pendanaan</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-orange-500/10">
                        <tr>
                            <td class="px-4 py-2.5 font-semibold text-orange-400">Arus Kas Bersih dari Pendanaan</td>
                            <td class="px-4 py-2.5 text-right font-bold {{ $cls($data['financing']['total']) }}">{{ $fmt($data['financing']['total']) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Kenaikan/Penurunan Kas Bersih --}}
            <div class="bg-white/5 border border-white/10 rounded-xl px-4 py-3 flex justify-between items-center">
                <span class="font-semibold text-gray-300">Kenaikan (Penurunan) Kas Bersih</span>
                <span class="font-bold {{ $cls($data['net_change']) }}">{{ $fmt($data['net_change']) }}</span>
            </div>

            {{-- Saldo Kas Akhir --}}
            <div class="bg-indigo-600/20 border border-indigo-500/30 rounded-xl px-4 py-4 space-y-2">
                <div class="flex justify-between text-sm text-gray-400">
                    <span>Saldo Kas Awal</span>
                    <span>{{ $fmt($data['opening_cash']) }}</span>
                </div>
                <div class="flex justify-between text-sm text-gray-400">
                    <span>Perubahan Kas Bersih</span>
                    <span class="{{ $cls($data['net_change']) }}">{{ $fmt($data['net_change']) }}</span>
                </div>
                <div class="border-t border-white/10 pt-2 flex justify-between items-center">
                    <span class="font-bold text-white">SALDO KAS AKHIR PERIODE</span>
                    <span class="font-bold text-indigo-300 text-lg">{{ $fmt($data['closing_cash']) }}</span>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
