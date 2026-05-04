@extends('layouts.app')

@section('title', 'Channel Pricing')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Channel Pricing</h1>
                    <p class="mt-1 text-sm text-gray-500">Manage pricing formulas for distribution channels</p>
                </div>
                <button type="button" onclick="document.getElementById('addPricingModal').classList.remove('hidden')"
                    class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Pricing
                </button>
            </div>
        </div>

        <!-- Back Link -->
        <div class="mb-4">
            <a href="{{ route('cosmetic.distribution.index') }}" class="text-blue-600 hover:text-blue-800">
                ← Back to Distribution Channels
            </a>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" action="{{ route('cosmetic.distribution.pricing') }}" class="flex gap-4">
                <div class="flex-1">
                    <select name="channel_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Channels</option>
                        @foreach ($channels as $channel)
                            <option value="{{ $channel->id }}"
                                {{ request('channel_id') == $channel->id ? 'selected' : '' }}>
                                {{ $channel->channel_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1">
                    <select name="formula_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Products</option>
                        @foreach ($formulas as $formula)
                            <option value="{{ $formula->id }}"
                                {{ request('formula_id') == $formula->id ? 'selected' : '' }}>
                                {{ $formula->formula_code }} - {{ $formula->formula_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit"
                    class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition">
                    Filter
                </button>
            </form>
        </div>

        <!-- Pricing Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Channel
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Base
                            Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Channel
                            Price</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Min Qty
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bulk
                            Discount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Effective
                            Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($pricing as $price)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="font-medium">{{ $price->channel?->channel_name }}</div>
                                <div class="text-xs text-gray-500">{{ $price->channel?->channel_code }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if ($price->product)
                                    <div class="font-medium">{{ $price->product?->formula_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $price->product?->formula_code }}</div>
                                @else
                                    <span class="text-gray-400">Product Deleted</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                Rp {{ number_format($price->base_price, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
                                Rp {{ number_format($price->channel_price, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($price->minimum_order_quantity, 0) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if ($price->bulk_discount_threshold)
                                    <div>{{ number_format($price->bulk_discount_rate, 1) }}%</div>
                                    <div class="text-xs text-gray-500">≥
                                        {{ number_format($price->bulk_discount_threshold, 0) }}
                                        units</div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if ($price->effective_date)
                                    <div>{{ $price->effective_date->format('d M Y') }}</div>
                                    @if ($price->expiry_date)
                                        <div class="text-xs text-gray-500">
                                            to {{ $price->expiry_date->format('d M Y') }}</div>
                                    @endif
                                @else
                                    <span class="text-gray-400">No limit</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($price->isCurrentlyActive())
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Active
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="mt-2">No pricing records found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $pricing->links() }}
        </div>
    </div>

    <!-- Add Pricing Modal -->
    <div id="addPricingModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-3xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900">Add Channel Pricing</h3>
                <button type="button" onclick="document.getElementById('addPricingModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('cosmetic.distribution.pricing.store') }}" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Distribution Channel *</label>
                        <select name="channel_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Channel</option>
                            @foreach ($channels as $channel)
                                <option value="{{ $channel->id }}">{{ $channel->channel_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Product Formula *</label>
                        <select name="formula_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Product</option>
                            @foreach ($formulas as $formula)
                                <option value="{{ $formula->id }}">
                                    {{ $formula->formula_code }} - {{ $formula->formula_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Base Price (Rp) *</label>
                        <input type="number" name="base_price" step="0.01" min="0" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Channel Price (Rp) *</label>
                        <input type="number" name="channel_price" step="0.01" min="0" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Order Quantity</label>
                    <input type="number" name="minimum_order_quantity" step="1" min="1" value="1"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bulk Discount Threshold</label>
                        <input type="number" name="bulk_discount_threshold" step="0.01" min="0"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Minimum quantity to trigger bulk discount</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bulk Discount Rate (%)</label>
                        <input type="number" name="bulk_discount_rate" step="0.01" min="0" max="100"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Effective Date</label>
                        <input type="date" name="effective_date"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date</label>
                        <input type="date" name="expiry_date"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="document.getElementById('addPricingModal').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition">
                        Save Pricing
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
