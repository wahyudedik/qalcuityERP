<x-app-layout>
    <x-slot name="header">Analisis Biaya & Produktivitas Lahan</x-slot>

    <div class="mb-4">
        <a href="{{ route('farm.plots') }}" class="text-sm text-blue-500 hover:text-blue-600">← Daftar Lahan</a>
    </div>

    {{-- Global KPI --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Total Luas</p>
            <p class="text-xl font-bold text-gray-900">{{ number_format($totalArea, 1) }} ha</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Total Biaya</p>
            <p class="text-xl font-bold text-red-500">Rp {{ number_format($totalCost, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Total Panen Bersih</p>
            <p class="text-xl font-bold text-emerald-600">{{ number_format($totalHarvest, 0) }} kg</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Rata-rata HPP/kg</p>
            <p class="text-xl font-bold text-gray-900">{{ $avgHpp ? 'Rp '.number_format($avgHpp, 0, ',', '.') : '-' }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Yield Rata-rata</p>
            <p class="text-xl font-bold text-blue-600">{{ number_format($avgYieldPerHa, 0) }} kg/ha</p>
        </div>
    </div>

    {{-- Comparison Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-900">Perbandingan Biaya & Produktivitas per Lahan</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Lahan</th>
                        <th class="px-4 py-3 text-left">Tanaman</th>
                        <th class="px-4 py-3 text-right">Luas</th>
                        <th class="px-4 py-3 text-right">Total Biaya</th>
                        <th class="px-4 py-3 text-right">Biaya/Ha</th>
                        <th class="px-4 py-3 text-right">Panen Bersih</th>
                        <th class="px-4 py-3 text-right">Yield/Ha</th>
                        <th class="px-4 py-3 text-right">HPP/kg</th>
                        <th class="px-4 py-3 text-right">Reject</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($comparison as $p)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <a href="{{ route('farm.plots.show', $p['code']) }}" class="font-medium text-gray-900 hover:text-blue-600">{{ $p['code'] }}</a>
                            <span class="text-xs text-gray-400 ml-1">{{ $p['name'] }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $p['crop'] }}</td>
                        <td class="px-4 py-3 text-right text-xs text-gray-500">{{ $p['area'] }}</td>
                        <td class="px-4 py-3 text-right font-mono {{ $p['total_cost'] > 0 ? 'text-red-500' : 'text-gray-300' }}">
                            {{ $p['total_cost'] > 0 ? 'Rp '.number_format($p['total_cost'], 0, ',', '.') : '-' }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-gray-600">
                            {{ $p['cost_per_ha'] > 0 ? 'Rp '.number_format($p['cost_per_ha'], 0, ',', '.') : '-' }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono font-medium text-emerald-600">
                            {{ $p['total_harvest'] > 0 ? number_format($p['total_harvest'], 0).' kg' : '-' }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-blue-600">
                            {{ $p['yield_per_ha'] > 0 ? number_format($p['yield_per_ha'], 0).' kg' : '-' }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono font-bold {{ $p['hpp_per_kg'] !== null ? 'text-gray-900' : 'text-gray-300' }}">
                            {{ $p['hpp_per_kg'] !== null ? 'Rp '.number_format($p['hpp_per_kg'], 0, ',', '.') : '-' }}
                        </td>
                        <td class="px-4 py-3 text-right text-xs {{ $p['reject_pct'] > 5 ? 'text-red-500 font-medium' : 'text-gray-400' }}">
                            {{ $p['reject_pct'] > 0 ? $p['reject_pct'].'%' : '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Efficiency Ranking --}}
    @if($ranked->isNotEmpty())
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-900 mb-4">🏆 Ranking Efisiensi (HPP Terendah = Paling Efisien)</h3>
        <div class="space-y-3">
            @foreach($ranked as $i => $p)
            @php
                $medal = match($i) { 0 => '🥇', 1 => '🥈', 2 => '🥉', default => '#'.($i+1) };
                $barWidth = $ranked->first()['hpp_per_kg'] > 0 ? min(100, round($p['hpp_per_kg'] / $ranked->last()['hpp_per_kg'] * 100)) : 0;
            @endphp
            <div class="flex items-center gap-3">
                <span class="text-lg w-8 text-center">{{ $medal }}</span>
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-gray-900">{{ $p['code'] }} — {{ $p['crop'] }}</span>
                        <span class="text-sm font-bold text-gray-900">Rp {{ number_format($p['hpp_per_kg'], 0, ',', '.') }}/kg</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="h-2 rounded-full {{ $i === 0 ? 'bg-emerald-500' : ($i === $ranked->count()-1 ? 'bg-red-400' : 'bg-blue-400') }}" style="width:{{ $barWidth }}%"></div>
                    </div>
                    <div class="flex gap-4 mt-1 text-[10px] text-gray-400">
                        <span>Biaya/ha: Rp {{ number_format($p['cost_per_ha'], 0, ',', '.') }}</span>
                        <span>Yield/ha: {{ number_format($p['yield_per_ha'], 0) }} kg</span>
                        <span>Panen: {{ number_format($p['total_harvest'], 0) }} kg</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</x-app-layout>
