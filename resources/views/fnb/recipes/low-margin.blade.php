@extends('layouts.app')
@section('title', 'Resep Margin Rendah')
@section('content')
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <a href="{{ route('fnb.recipes.index') }}"
                class="text-blue-600 hover:text-blue-800 text-sm transition-colors">
                ← Kembali ke Kalkulator Biaya Resep
            </a>
        </div>

        <div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Resep Margin Rendah</h1>
                <p class="mt-1 text-sm text-gray-600">Resep dengan margin keuntungan di bawah threshold</p>
            </div>
        </div>

        <!-- Filter -->
        <form method="GET" class="mb-6 flex items-end gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Threshold Margin (%)</label>
                <input type="number" name="threshold" value="{{ $threshold }}" min="0" max="100"
                    class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500 w-24">
            </div>
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors min-h-[38px]">
                Filter
            </button>
        </form>

        @if ($lowMarginRecipes->isEmpty())
            <div class="bg-green-50 border-l-4 border-green-500 p-6 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-green-700 font-medium">
                        Semua resep memiliki margin di atas {{ $threshold }}%. Bagus!
                    </p>
                </div>
            </div>
        @else
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
                <p class="text-red-700 font-medium">
                    ⚠️ {{ $lowMarginRecipes->count() }} resep memiliki margin keuntungan di bawah {{ $threshold }}%
                </p>
            </div>

            <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resep</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Menu Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Jual</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Biaya per Porsi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Margin</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($lowMarginRecipes as $item)
                                @php
                                    $recipe = $item['recipe'] ?? null;
                                    $margin = $item['margin_percentage'] ?? $item['profit_margin'] ?? 0;
                                    $sellingPrice = $item['selling_price'] ?? ($recipe?->menuItem?->price ?? 0);
                                    $costPerServing = $item['cost_per_serving'] ?? 0;
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $recipe?->name ?? ($item['name'] ?? '-') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $recipe?->menuItem?->name ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        Rp {{ number_format($sellingPrice, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        Rp {{ number_format($costPerServing, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full font-semibold
                                            {{ $margin < 0 ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ round($margin, 1) }}%
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if ($recipe)
                                            <a href="{{ route('fnb.recipes.calculate', $recipe) }}"
                                                class="text-blue-600 hover:text-blue-800 transition-colors">
                                                Analisis →
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
