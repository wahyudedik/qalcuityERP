@extends('layouts.app')
@section('title', 'Laporan Segment P&L')
@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Laporan Segment (P&L per Cost Center)</h2>
            <p class="text-sm text-slate-500 mt-0.5">Laba rugi per divisi / cabang / proyek</p>
        </div>
        <a href="{{ route('cost-centers.index') }}" class="px-4 py-2 rounded-xl text-sm border border-gray-200 text-gray-700 hover:bg-gray-50 transition">
            ← Kembali
        </a>
    </div>

    <form method="GET" class="flex gap-3 flex-wrap items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Dari</label>
            <input type="date" name="from" value="{{ $from }}" class="px-3 py-2 rounded-xl text-sm border border-gray-200 bg-white text-gray-900">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Sampai</label>
            <input type="date" name="to" value="{{ $to }}" class="px-3 py-2 rounded-xl text-sm border border-gray-200 bg-white text-gray-900">
        </div>
        <button type="submit" class="px-4 py-2 rounded-xl text-sm bg-blue-600 text-white hover:bg-blue-700">Tampilkan</button>
    </form>

    @if($centers->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-200 p-10 text-center">
        <p class="text-gray-400 text-sm">Belum ada cost center aktif. <a href="{{ route('cost-centers.index') }}" class="text-blue-500 hover:underline">Tambahkan cost center</a> terlebih dahulu.</p>
    </div>
    @elseif($report->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-200 p-10 text-center">
        <p class="text-gray-400 text-sm">Tidak ada data jurnal yang terhubung ke cost center pada periode ini.</p>
        <p class="text-xs text-gray-400 mt-2">Pastikan transaksi sudah diposting dan memiliki cost center yang dipilih.</p>
    </div>
    @else
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <p class="text-sm font-semibold text-gray-700">
                Periode: {{ \Carbon\Carbon::parse($from)->format('d M Y') }} — {{ \Carbon\Carbon::parse($to)->format('d M Y') }}
            </p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-5 py-3 text-left">Cost Center</th>
                        <th class="px-5 py-3 text-right">Pendapatan</th>
                        <th class="px-5 py-3 text-right">Biaya</th>
                        <th class="px-5 py-3 text-right">Laba / Rugi</th>
                        <th class="px-5 py-3 text-right">Margin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($report as $row)
                    @php $margin = $row['revenue'] > 0 ? round($row['profit'] / $row['revenue'] * 100, 1) : 0; @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-medium text-gray-900">{{ $row['label'] }}</td>
                        <td class="px-5 py-3 text-right text-green-600 font-mono">
                            Rp {{ number_format($row['revenue'], 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-3 text-right text-red-500 font-mono">
                            Rp {{ number_format($row['expense'], 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-3 text-right font-semibold font-mono {{ $row['profit'] >= 0 ? 'text-blue-600' : 'text-red-500' }}">
                            {{ $row['profit'] < 0 ? '-' : '' }}Rp {{ number_format(abs($row['profit']), 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-3 text-right text-xs {{ $margin >= 20 ? 'text-green-500' : ($margin >= 0 ? 'text-amber-500' : 'text-red-500') }}">
                            {{ $margin }}%
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 font-bold border-t-2 border-gray-200">
                    <tr>
                        <td class="px-5 py-3 text-gray-900">TOTAL</td>
                        <td class="px-5 py-3 text-right text-green-600 font-mono">
                            Rp {{ number_format($totals['revenue'], 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-3 text-right text-red-500 font-mono">
                            Rp {{ number_format($totals['expense'], 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-3 text-right font-mono {{ $totals['profit'] >= 0 ? 'text-blue-600' : 'text-red-500' }}">
                            {{ $totals['profit'] < 0 ? '-' : '' }}Rp {{ number_format(abs($totals['profit']), 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-3 text-right text-xs text-gray-400">
                            @if($totals['revenue'] > 0)
                            {{ round($totals['profit'] / $totals['revenue'] * 100, 1) }}%
                            @endif
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
