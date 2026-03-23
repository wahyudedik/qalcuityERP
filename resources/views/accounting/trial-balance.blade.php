<x-app-layout>
    <x-slot name="header">Neraca Saldo (Trial Balance)</x-slot>

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
        </form>

        {{-- Table --}}
        <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
            <table class="w-full text-sm text-gray-300">
                <thead class="bg-white/5 text-gray-400 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Kode</th>
                        <th class="px-4 py-3 text-left">Nama Akun</th>
                        <th class="px-4 py-3 text-left">Tipe</th>
                        <th class="px-4 py-3 text-right">Debit</th>
                        <th class="px-4 py-3 text-right">Kredit</th>
                        <th class="px-4 py-3 text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @php $totalDebit = 0; $totalCredit = 0; @endphp
                    @forelse($accounts as $acc)
                    @php $totalDebit += $acc['debit']; $totalCredit += $acc['credit']; @endphp
                    <tr class="hover:bg-white/5">
                        <td class="px-4 py-3 font-mono text-xs">{{ $acc['code'] }}</td>
                        <td class="px-4 py-3">{{ $acc['name'] }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $acc['type'] }}</td>
                        <td class="px-4 py-3 text-right">{{ $acc['debit'] > 0 ? number_format($acc['debit'], 0, ',', '.') : '-' }}</td>
                        <td class="px-4 py-3 text-right">{{ $acc['credit'] > 0 ? number_format($acc['credit'], 0, ',', '.') : '-' }}</td>
                        <td class="px-4 py-3 text-right font-medium {{ $acc['balance'] >= 0 ? 'text-white' : 'text-red-400' }}">
                            {{ number_format(abs($acc['balance']), 0, ',', '.') }}
                            {{ $acc['balance'] < 0 ? '(K)' : '' }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Tidak ada data untuk periode ini.</td></tr>
                    @endforelse
                </tbody>
                @if($accounts->count() > 0)
                <tfoot class="bg-white/5 font-semibold text-white">
                    <tr>
                        <td colspan="3" class="px-4 py-3 text-right">TOTAL</td>
                        <td class="px-4 py-3 text-right">{{ number_format($totalDebit, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($totalCredit, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right {{ abs($totalDebit - $totalCredit) < 0.01 ? 'text-green-400' : 'text-red-400' }}">
                            {{ abs($totalDebit - $totalCredit) < 0.01 ? '✓ Balance' : 'TIDAK BALANCE' }}
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</x-app-layout>
