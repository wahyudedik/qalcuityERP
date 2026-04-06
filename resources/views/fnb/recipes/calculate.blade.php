@extends('layouts.app')
@section('title', 'Recipe Cost - ' . $recipe->name)
@section('content')
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <a href="{{ route('fnb.recipes.index') }}" class="text-blue-600 hover:text-blue-900 text-sm">← Back to Recipes</a>
            <h1 class="text-3xl font-bold text-gray-900 mt-2">{{ $recipe->name }}</h1>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-500">Total Cost</div>
                <div class="text-2xl font-bold text-gray-900">Rp {{ number_format($costData['total_cost'], 0, ',', '.') }}
                </div>
            </div>
            <div class="bg-blue-50 rounded-lg shadow p-4 border-l-4 border-blue-500">
                <div class="text-sm text-blue-600">Cost per Serving</div>
                <div class="text-2xl font-bold text-blue-700">Rp
                    {{ number_format($costData['cost_per_serving'], 0, ',', '.') }}</div>
            </div>
            <div class="bg-green-50 rounded-lg shadow p-4 border-l-4 border-green-500">
                <div class="text-sm text-green-600">Selling Price</div>
                <div class="text-2xl font-bold text-green-700">Rp
                    {{ number_format($costData['profit_margin']['selling_price'], 0, ',', '.') }}</div>
            </div>
            <div
                class="{{ $costData['profit_margin']['margin_percentage'] >= 30 ? 'bg-purple-50 border-purple-500' : 'bg-red-50 border-red-500' }} rounded-lg shadow p-4 border-l-4">
                <div
                    class="text-sm {{ $costData['profit_margin']['margin_percentage'] >= 30 ? 'text-purple-600' : 'text-red-600' }}">
                    Profit Margin</div>
                <div
                    class="text-2xl font-bold {{ $costData['profit_margin']['margin_percentage'] >= 30 ? 'text-purple-700' : 'text-red-700' }}">
                    {{ $costData['profit_margin']['margin_percentage'] }}%</div>
            </div>
        </div>

        <!-- Ingredients Breakdown -->
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-semibold">Ingredients Breakdown</h2>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ingredient</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cost/Unit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Line Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">% of Cost</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($costData['ingredients'] as $ingredient)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ $ingredient['name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $ingredient['quantity'] }}
                                {{ $ingredient['unit'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">Rp
                                {{ number_format($ingredient['cost_per_unit'], 0, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">Rp
                                {{ number_format($ingredient['line_total'], 0, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-blue-600 h-2 rounded-full"
                                            style="width: {{ $ingredient['percentage'] }}%"></div>
                                    </div>
                                    <span class="text-xs">{{ $ingredient['percentage'] }}%</span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Profit Analysis -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Profit Analysis</h2>
            <div class="space-y-3">
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                    <span>Selling Price per Serving</span>
                    <span class="font-semibold">Rp
                        {{ number_format($costData['profit_margin']['selling_price'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                    <span>Cost per Serving</span>
                    <span class="font-semibold">Rp
                        {{ number_format($costData['profit_margin']['cost_per_serving'], 0, ',', '.') }}</span>
                </div>
                <div
                    class="flex justify-between items-center p-3 {{ $costData['profit_margin']['is_profitable'] ? 'bg-green-50' : 'bg-red-50' }} rounded">
                    <span class="font-medium">Profit per Serving</span>
                    <span
                        class="font-bold {{ $costData['profit_margin']['is_profitable'] ? 'text-green-700' : 'text-red-700' }}">
                        Rp {{ number_format($costData['profit_margin']['profit_per_serving'], 0, ',', '.') }}
                    </span>
                </div>
                <div
                    class="flex justify-between items-center p-3 {{ $costData['profit_margin']['margin_percentage'] >= 30 ? 'bg-green-50' : 'bg-yellow-50' }} rounded">
                    <span class="font-medium">Margin Percentage</span>
                    <span
                        class="font-bold {{ $costData['profit_margin']['margin_percentage'] >= 30 ? 'text-green-700' : 'text-yellow-700' }}">
                        {{ $costData['profit_margin']['margin_percentage'] }}%
                    </span>
                </div>
            </div>

            @if (!$costData['profit_margin']['is_profitable'])
                <div class="mt-4 p-4 bg-red-50 border-l-4 border-red-500 rounded">
                    <p class="text-red-700 font-medium">⚠️ This recipe is losing money! Consider increasing the selling
                        price or reducing ingredient costs.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
