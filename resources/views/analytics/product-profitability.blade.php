@extends('layouts.app')

@section('title', 'Product Profitability Matrix')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <a href="{{ route('analytics.dashboard') }}" class="text-blue-600 hover:text-blue-900 text-sm">← Back to
                Dashboard</a>
            <h1 class="text-3xl font-bold text-gray-900 mt-2">Product Profitability Matrix</h1>
        </div>

        <!-- Quadrant Summary -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            @foreach (['Stars' => 'bg-green', 'Cash Cows' => 'bg-blue', 'Question Marks' => 'bg-yellow', 'Dogs' => 'bg-red'] as $quadrant => $color)
                @php $data = $profitabilityData['quadrants'][$quadrant] ?? null; @endphp
                <div
                    class="bg-white rounded-lg shadow p-4 border-l-4 {{ str_replace('bg-', "border-{$color}-500 ", $color) }}">
                    <div class="text-xs font-medium text-gray-500 uppercase">{{ $quadrant }}</div>
                    <div class="mt-2 text-xl font-bold text-gray-900">{{ $data['count'] ?? 0 }} products</div>
                    <div class="text-xs text-gray-500">Avg Margin: {{ $data['avg_margin'] ?? 0 }}%</div>
                    <div class="text-sm font-semibold text-green-600">Rp
                        {{ number_format($data['total_profit'] ?? 0, 0, ',', '.') }}</div>
                </div>
            @endforeach
        </div>

        <!-- Products Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Revenue</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Profit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Margin %</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty Sold</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quadrant</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($profitabilityData['matrix'] as $product)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $product['product_name'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp
                                {{ number_format($product['total_revenue'], 0, ',', '.') }}</td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm font-semibold {{ $product['total_profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                Rp {{ number_format($product['total_profit'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product['profit_margin'] }}%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ number_format($product['total_qty_sold']) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $colors = [
                                        'Stars' => 'bg-green-100 text-green-800',
                                        'Cash Cows' => 'bg-blue-100 text-blue-800',
                                        'Question Marks' => 'bg-yellow-100 text-yellow-800',
                                        'Dogs' => 'bg-red-100 text-red-800',
                                    ];
                                @endphp
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $colors[$product['quadrant']] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $product['quadrant'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">No product data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
