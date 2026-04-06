@extends('layouts.app')

@section('title', 'Formula Cost Analysis')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Formula Cost Analysis</h1>
                    <p class="mt-1 text-sm text-gray-500">Ingredient cost breakdown and trends</p>
                </div>
                <a href="{{ route('cosmetic.analytics.dashboard') }}" class="text-blue-600 hover:text-blue-800">← Back to
                    Analytics</a>
            </div>
        </div>

        <!-- Formula Selector -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" class="flex gap-4">
                <select name="formula_id" onchange="this.form.submit()"
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">Select Formula for Detailed Analysis</option>
                    @foreach ($formulas as $formula)
                        <option value="{{ $formula->id }}" {{ $formulaId == $formula->id ? 'selected' : '' }}>
                            {{ $formula->formula_code }} - {{ $formula->formula_name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        @if ($costAnalysis)
            <!-- Detailed Cost Breakdown -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="text-lg font-semibold mb-4">Cost Breakdown: {{ $costAnalysis['formula']->formula_name }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="p-4 bg-blue-50 rounded-lg">
                        <div class="text-sm text-gray-600">Total Ingredient Cost</div>
                        <div class="mt-2 text-2xl font-bold text-blue-700">Rp
                            {{ number_format($costAnalysis['total_ingredient_cost'], 0, ',', '.') }}</div>
                    </div>
                    <div class="p-4 bg-green-50 rounded-lg">
                        <div class="text-sm text-gray-600">Cost Per Unit</div>
                        <div class="mt-2 text-2xl font-bold text-green-700">Rp
                            {{ number_format($costAnalysis['cost_per_unit'], 0, ',', '.') }}</div>
                    </div>
                    <div class="p-4 bg-purple-50 rounded-lg">
                        <div class="text-sm text-gray-600">Total Ingredients</div>
                        <div class="mt-2 text-2xl font-bold text-purple-700">
                            {{ count($costAnalysis['ingredient_breakdown']) }}</div>
                    </div>
                </div>

                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ingredient</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Cost</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Cost</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">% of Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($costAnalysis['ingredient_breakdown'] as $ingredient)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $ingredient['name'] ?? ($ingredient['inci_name'] ?? '-') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $ingredient['quantity'] ?? 0 }} {{ $ingredient['unit'] ?? '' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp
                                    {{ number_format($ingredient['unit_cost'] ?? 0, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">Rp
                                    {{ number_format($ingredient['calculated_cost'], 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @php $pct = $costAnalysis['total_ingredient_cost'] > 0 ? ($ingredient['calculated_cost'] / $costAnalysis['total_ingredient_cost']) * 100 : 0; @endphp
                                    {{ number_format($pct, 1) }}%
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Cost Comparison -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold">Cost Comparison - All Formulas</h3>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Formula</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Cost</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cost Per Unit</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($costComparison as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $item['formula']->formula_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full {{ $item['formula']->status === 'production' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ ucfirst($item['formula']->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp
                                {{ number_format($item['total_cost'], 0, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">Rp
                                {{ number_format($item['cost_per_unit'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
