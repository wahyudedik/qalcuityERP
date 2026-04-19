@extends('layouts.app')
@section('title', 'Biaya Resep — ' . $recipe->name)
@section('content')
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <a href="{{ route('fnb.recipes.index') }}"
                class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm transition-colors">
                ← Kembali ke Daftar Resep
            </a>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-2">{{ $recipe->name }}</h1>
            @if ($recipe->description)
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $recipe->description }}</p>
            @endif
        </div>

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 rounded text-green-700 dark:text-green-300">
                {{ session('success') }}
            </div>
        @endif

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Biaya</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    Rp {{ number_format($costData['total_cost'] ?? 0, 0, ',', '.') }}
                </div>
            </div>
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg shadow p-4 border-l-4 border-blue-500">
                <div class="text-sm text-blue-600 dark:text-blue-400">Biaya per Porsi</div>
                <div class="text-2xl font-bold text-blue-700 dark:text-blue-300">
                    Rp {{ number_format($costData['cost_per_serving'] ?? 0, 0, ',', '.') }}
                </div>
            </div>
            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg shadow p-4 border-l-4 border-green-500">
                <div class="text-sm text-green-600 dark:text-green-400">Harga Jual</div>
                <div class="text-2xl font-bold text-green-700 dark:text-green-300">
                    Rp {{ number_format($costData['profit_margin']['selling_price'] ?? 0, 0, ',', '.') }}
                </div>
            </div>
            @php
                $marginPct = $costData['profit_margin']['margin_percentage'] ?? 0;
                $marginGood = $marginPct >= 30;
            @endphp
            <div class="{{ $marginGood ? 'bg-purple-50 dark:bg-purple-900/20 border-purple-500' : 'bg-red-50 dark:bg-red-900/20 border-red-500' }} rounded-lg shadow p-4 border-l-4">
                <div class="text-sm {{ $marginGood ? 'text-purple-600 dark:text-purple-400' : 'text-red-600 dark:text-red-400' }}">
                    Margin Keuntungan
                </div>
                <div class="text-2xl font-bold {{ $marginGood ? 'text-purple-700 dark:text-purple-300' : 'text-red-700 dark:text-red-300' }}">
                    {{ $marginPct }}%
                </div>
            </div>
        </div>

        <!-- Ingredients Breakdown -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden mb-6 border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Rincian Bahan</h2>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ count($costData['ingredients'] ?? []) }} bahan
                </span>
            </div>
            @if (empty($costData['ingredients']))
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    Belum ada bahan dalam resep ini
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Bahan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jumlah</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Harga/Satuan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">% Biaya</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($costData['ingredients'] as $ingredient)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $ingredient['name'] ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                        {{ $ingredient['quantity'] ?? 0 }} {{ $ingredient['unit'] ?? '' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                        Rp {{ number_format($ingredient['cost_per_unit'] ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        Rp {{ number_format($ingredient['line_total'] ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <div class="w-16 bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                                                <div class="bg-blue-600 h-2 rounded-full"
                                                    style="width: {{ min($ingredient['percentage'] ?? 0, 100) }}%"></div>
                                            </div>
                                            <span class="text-xs text-gray-600 dark:text-gray-400">{{ $ingredient['percentage'] ?? 0 }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Profit Analysis -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Analisis Keuntungan</h2>
            <div class="space-y-3">
                <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700 rounded">
                    <span class="text-gray-700 dark:text-gray-300">Harga Jual per Porsi</span>
                    <span class="font-semibold text-gray-900 dark:text-gray-100">
                        Rp {{ number_format($costData['profit_margin']['selling_price'] ?? 0, 0, ',', '.') }}
                    </span>
                </div>
                <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700 rounded">
                    <span class="text-gray-700 dark:text-gray-300">Biaya per Porsi</span>
                    <span class="font-semibold text-gray-900 dark:text-gray-100">
                        Rp {{ number_format($costData['profit_margin']['cost_per_serving'] ?? 0, 0, ',', '.') }}
                    </span>
                </div>
                @php $isProfitable = $costData['profit_margin']['is_profitable'] ?? false; @endphp
                <div class="flex justify-between items-center p-3 {{ $isProfitable ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20' }} rounded">
                    <span class="font-medium text-gray-700 dark:text-gray-300">Keuntungan per Porsi</span>
                    <span class="font-bold {{ $isProfitable ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }}">
                        Rp {{ number_format($costData['profit_margin']['profit_per_serving'] ?? 0, 0, ',', '.') }}
                    </span>
                </div>
                <div class="flex justify-between items-center p-3 {{ $marginGood ? 'bg-green-50 dark:bg-green-900/20' : 'bg-yellow-50 dark:bg-yellow-900/20' }} rounded">
                    <span class="font-medium text-gray-700 dark:text-gray-300">Persentase Margin</span>
                    <span class="font-bold {{ $marginGood ? 'text-green-700 dark:text-green-300' : 'text-yellow-700 dark:text-yellow-300' }}">
                        {{ $marginPct }}%
                    </span>
                </div>
            </div>

            @if (!$isProfitable)
                <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 rounded">
                    <p class="text-red-700 dark:text-red-300 font-medium">
                        ⚠️ Resep ini merugi! Pertimbangkan untuk menaikkan harga jual atau mengurangi biaya bahan.
                    </p>
                </div>
            @elseif (!$marginGood)
                <div class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-500 rounded">
                    <p class="text-yellow-700 dark:text-yellow-300 font-medium">
                        ⚠️ Margin keuntungan di bawah 30%. Pertimbangkan untuk mengoptimalkan biaya bahan.
                    </p>
                </div>
            @endif
        </div>
    </div>
@endsection
