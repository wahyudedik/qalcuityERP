@extends('layouts.app')
@section('title', 'Analisis Penyebab Pemborosan')
@section('content')
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <a href="{{ route('fnb.waste.index') }}"
                class="text-blue-600 hover:text-blue-800 text-sm transition-colors">
                ← Kembali ke Pelacakan Pemborosan
            </a>
        </div>

        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Analisis Penyebab Pemborosan</h1>
            <p class="mt-1 text-sm text-gray-600">Identifikasi penyebab utama pemborosan bahan</p>
        </div>

        <!-- Filter -->
        <form method="GET" class="mb-6 flex items-end gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Periode (hari)</label>
                <select name="days"
                    class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="7" {{ $daysBack == 7 ? 'selected' : '' }}>7 hari terakhir</option>
                    <option value="30" {{ $daysBack == 30 ? 'selected' : '' }}>30 hari terakhir</option>
                    <option value="90" {{ $daysBack == 90 ? 'selected' : '' }}>90 hari terakhir</option>
                </select>
            </div>
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors min-h-[38px]">
                Filter
            </button>
        </form>

        <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">
                    Penyebab Pemborosan ({{ $daysBack }} hari terakhir)
                </h2>
            </div>
            @if (empty($reasons) || count($reasons) === 0)
                <div class="text-center py-12 text-gray-500">
                    Tidak ada data pemborosan untuk periode ini
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Pemborosan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Kejadian</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Biaya</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Persentase</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @php
                                $wasteTypeLabels = [
                                    'spoilage' => 'Spoilage/Rusak',
                                    'over_production' => 'Over Production',
                                    'preparation_error' => 'Kesalahan Persiapan',
                                    'expired' => 'Kadaluarsa',
                                    'other' => 'Lainnya',
                                ];
                                $totalCost = collect($reasons)->sum(fn($r) => is_array($r) ? ($r['total_cost'] ?? 0) : ($r->total_cost ?? 0));
                            @endphp
                            @foreach ($reasons as $reason)
                                @php
                                    $type = is_array($reason) ? ($reason['reason'] ?? $reason['waste_type'] ?? '') : ($reason->reason ?? $reason->waste_type ?? '');
                                    $count = is_array($reason) ? ($reason['count'] ?? 0) : ($reason->count ?? 0);
                                    $cost = is_array($reason) ? ($reason['total_cost'] ?? 0) : ($reason->total_cost ?? 0);
                                    $pct = $totalCost > 0 ? round(($cost / $totalCost) * 100, 1) : 0;
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $wasteTypeLabels[$type] ?? ($type ?: 'Tidak diketahui') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $count }}x
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-red-600">
                                        Rp {{ number_format($cost, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <div class="w-24 bg-gray-200 rounded-full h-2">
                                                <div class="bg-red-500 h-2 rounded-full" style="width: {{ $pct }}%"></div>
                                            </div>
                                            <span class="text-xs text-gray-600">{{ $pct }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
