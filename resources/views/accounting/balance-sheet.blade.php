<x-app-layout>
    <x-slot name="header">Neraca (Balance Sheet)</x-slot>

    <div class="space-y-5">

        {{-- Filter --}}
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="text-xs text-gray-500 mb-1 block">Per Tanggal</label>
                <input type="date" name="as_of" value="{{ $asOf }}"
                    class="bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-900 focus:outline-none focus:border-indigo-500">
            </div>
            <button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg">Tampilkan</button>
            <a href="{{ route('accounting.balance-sheet.pdf') }}?as_of={{ $asOf }}"
                class="bg-red-600 hover:bg-red-700 text-white text-sm px-4 py-2 rounded-lg flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Export PDF
            </a>
        </form>

        @php
            $fmt = fn($n) => number_format(abs($n), 0, ',', '.');
        @endphp

        {{-- Balance indicator --}}
        <div class="flex flex-wrap items-center gap-3">
            @if (isset($data['balance_warning']) && !$data['balance_warning']['is_balanced'])
                <span
                    class="inline-flex items-center gap-1.5 bg-red-50 text-red-600 text-xs px-3 py-1.5 rounded-full border border-red-200">
                    ⚠ {{ $data['balance_warning']['message'] }}
                </span>
            @elseif($data['is_balanced'])
                <span
                    class="inline-flex items-center gap-1.5 bg-green-50 text-green-600 text-xs px-3 py-1.5 rounded-full border border-green-200">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                    Neraca Balance
                </span>
            @else
                <span
                    class="inline-flex items-center gap-1.5 bg-red-50 text-red-600 text-xs px-3 py-1.5 rounded-full border border-red-200">
                    ⚠ Neraca Tidak Balance — selisih Rp {{ $fmt($data['total_assets'] - $data['total_l_e']) }}
                </span>
            @endif
            @if (isset($data['gl_integrity']))
                @if ($data['gl_integrity']['is_balanced'])
                    <span
                        class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-600 text-xs px-3 py-1.5 rounded-full border border-blue-200">
                        ✓ GL Integrity OK ({{ $data['gl_integrity']['journal_count'] }} jurnal)
                    </span>
                @else
                    <span
                        class="inline-flex items-center gap-1.5 bg-red-50 text-red-600 text-xs px-3 py-1.5 rounded-full border border-red-200">
                        ⚠ {{ $data['gl_integrity']['unbalanced_count'] }} jurnal tidak balance — selisih Rp
                        {{ number_format($data['gl_integrity']['difference'], 0, ',', '.') }}
                    </span>
                @endif
            @endif
            <span class="text-xs text-gray-500">Per {{ \Carbon\Carbon::parse($asOf)->translatedFormat('d F Y') }}</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            {{-- ASET --}}
            <div class="space-y-4">
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">ASET</h3>

                {{-- Aset Lancar --}}
                <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                    <div class="px-4 py-2.5 bg-gray-50 border-b border-gray-200">
                        <span class="text-xs font-semibold text-gray-600 uppercase">Aset Lancar</span>
                    </div>
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($data['assets']['current'] as $acc)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2.5 text-gray-700">
                                        <span class="font-mono text-xs text-gray-400 mr-2">{{ $acc['code'] }}</span>
                                        {{ $acc['name'] }}
                                    </td>
                                    <td class="px-4 py-2.5 text-right text-gray-900 font-medium">
                                        {{ $fmt($acc['balance']) }}</td>
                                </tr>
                            @endforeach
                            @if ($data['assets']['current']->isEmpty())
                                <tr>
                                    <td colspan="2" class="px-4 py-4 text-center text-gray-400 text-xs">Tidak ada
                                        data</td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td class="px-4 py-2.5 text-xs font-semibold text-gray-600">Total Aset Lancar</td>
                                <td class="px-4 py-2.5 text-right font-bold text-gray-900">
                                    {{ $fmt($data['assets']['current']->sum('balance')) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Aset Tidak Lancar --}}
                <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                    <div class="px-4 py-2.5 bg-gray-50 border-b border-gray-200">
                        <span class="text-xs font-semibold text-gray-600 uppercase">Aset Tidak Lancar</span>
                    </div>
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($data['assets']['non_current'] as $acc)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2.5 text-gray-700">
                                        <span class="font-mono text-xs text-gray-400 mr-2">{{ $acc['code'] }}</span>
                                        {{ $acc['name'] }}
                                    </td>
                                    <td class="px-4 py-2.5 text-right text-gray-900 font-medium">
                                        {{ $fmt($acc['balance']) }}</td>
                                </tr>
                            @endforeach
                            @if ($data['assets']['non_current']->isEmpty())
                                <tr>
                                    <td colspan="2" class="px-4 py-4 text-center text-gray-400 text-xs">Tidak ada
                                        data</td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td class="px-4 py-2.5 text-xs font-semibold text-gray-600">Total Aset Tidak Lancar</td>
                                <td class="px-4 py-2.5 text-right font-bold text-gray-900">
                                    {{ $fmt($data['assets']['non_current']->sum('balance')) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Total Aset --}}
                <div class="bg-indigo-600 rounded-xl px-4 py-3 flex justify-between items-center shadow-sm">
                    <span class="font-bold text-white">TOTAL ASET</span>
                    <span class="font-bold text-white text-lg">Rp {{ $fmt($data['total_assets']) }}</span>
                </div>
            </div>

            {{-- KEWAJIBAN & EKUITAS --}}
            <div class="space-y-4">
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">KEWAJIBAN & EKUITAS</h3>

                {{-- Kewajiban Lancar --}}
                <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                    <div class="px-4 py-2.5 bg-gray-50 border-b border-gray-200">
                        <span class="text-xs font-semibold text-gray-600 uppercase">Kewajiban Lancar</span>
                    </div>
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($data['liabilities']['current'] as $acc)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2.5 text-gray-700">
                                        <span class="font-mono text-xs text-gray-400 mr-2">{{ $acc['code'] }}</span>
                                        {{ $acc['name'] }}
                                    </td>
                                    <td class="px-4 py-2.5 text-right text-gray-900 font-medium">
                                        {{ $fmt($acc['balance']) }}</td>
                                </tr>
                            @endforeach
                            @if ($data['liabilities']['current']->isEmpty())
                                <tr>
                                    <td colspan="2" class="px-4 py-4 text-center text-gray-400 text-xs">Tidak ada
                                        data</td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td class="px-4 py-2.5 text-xs font-semibold text-gray-600">Total Kewajiban Lancar</td>
                                <td class="px-4 py-2.5 text-right font-bold text-gray-900">
                                    {{ $fmt($data['liabilities']['current']->sum('balance')) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Kewajiban Jangka Panjang --}}
                @if ($data['liabilities']['long_term']->isNotEmpty())
                    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                        <div class="px-4 py-2.5 bg-gray-50 border-b border-gray-200">
                            <span class="text-xs font-semibold text-gray-600 uppercase">Kewajiban Jangka Panjang</span>
                        </div>
                        <table class="w-full text-sm">
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($data['liabilities']['long_term'] as $acc)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2.5 text-gray-700">
                                            <span
                                                class="font-mono text-xs text-gray-400 mr-2">{{ $acc['code'] }}</span>
                                            {{ $acc['name'] }}
                                        </td>
                                        <td class="px-4 py-2.5 text-right text-gray-900 font-medium">
                                            {{ $fmt($acc['balance']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td class="px-4 py-2.5 text-xs font-semibold text-gray-600">Total Kewajiban Jangka
                                        Panjang</td>
                                    <td class="px-4 py-2.5 text-right font-bold text-gray-900">
                                        {{ $fmt($data['liabilities']['long_term']->sum('balance')) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif

                {{-- Ekuitas --}}
                <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                    <div class="px-4 py-2.5 bg-gray-50 border-b border-gray-200">
                        <span class="text-xs font-semibold text-gray-600 uppercase">Ekuitas</span>
                    </div>
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($data['equity']['items'] as $acc)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2.5 text-gray-700">
                                        <span class="font-mono text-xs text-gray-400 mr-2">{{ $acc['code'] }}</span>
                                        {{ $acc['name'] }}
                                    </td>
                                    <td class="px-4 py-2.5 text-right text-gray-900 font-medium">
                                        {{ $fmt($acc['balance']) }}</td>
                                </tr>
                            @endforeach
                            {{-- Laba/Rugi Berjalan --}}
                            <tr class="hover:bg-gray-50 border-t border-gray-200">
                                <td class="px-4 py-2.5 text-gray-700 italic">Laba/Rugi Tahun Berjalan</td>
                                <td
                                    class="px-4 py-2.5 text-right font-medium {{ $data['net_income'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $data['net_income'] < 0 ? '(' : '' }}{{ $fmt($data['net_income']) }}{{ $data['net_income'] < 0 ? ')' : '' }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td class="px-4 py-2.5 text-xs font-semibold text-gray-600">Total Ekuitas</td>
                                <td class="px-4 py-2.5 text-right font-bold text-gray-900">
                                    {{ $fmt($data['equity']['total'] + $data['net_income']) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Total Kewajiban + Ekuitas --}}
                <div class="bg-indigo-600 rounded-xl px-4 py-3 flex justify-between items-center shadow-sm">
                    <span class="font-bold text-white">TOTAL KEWAJIBAN & EKUITAS</span>
                    <span class="font-bold text-white text-lg">Rp {{ $fmt($data['total_l_e']) }}</span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
