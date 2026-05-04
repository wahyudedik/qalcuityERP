@extends('layouts.app')

@section('title', 'Channel Inventory')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Channel Inventory</h1>
                    <p class="mt-1 text-sm text-gray-500">Monitor and manage inventory allocation across channels</p>
                </div>
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
            <form method="GET" action="{{ route('cosmetic.distribution.inventory') }}" class="flex gap-4">
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
                <button type="submit"
                    class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition">
                    Filter
                </button>
            </form>
        </div>

        <!-- Inventory Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Channel
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Allocated</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sold</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reserved
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last
                            Restock</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($inventory as $item)
                        @php
                            $available = $item->allocated_stock - $item->sold_stock - $item->reserved_stock;
                            $isLowStock = $available < 10;
                        @endphp
                        <tr class="hover:bg-gray-50 {{ $isLowStock ? 'bg-red-50' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="font-medium">{{ $item->channel?->channel_name }}</div>
                                <div class="text-xs text-gray-500">{{ $item->channel?->channel_code }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if ($item->product)
                                    <div class="font-medium">{{ $item->product?->formula_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $item->product?->formula_code }}</div>
                                @else
                                    <span class="text-gray-400">Product Deleted</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($item->allocated_stock, 0) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($item->sold_stock, 0) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($item->reserved_stock, 0) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-lg font-bold {{ $isLowStock ? 'text-red-600' : 'text-green-600' }}">
                                    {{ number_format($available, 0) }}
                                </span>
                                @if ($isLowStock)
                                    <div class="text-xs text-red-600 font-semibold">Low Stock!</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if ($item->last_restock_date)
                                    {{ $item->last_restock_date->format('d M Y') }}
                                @else
                                    <span class="text-gray-400">Never</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button type="button"
                                    onclick="openRestockModal({{ $item->id }}, '{{ $item->channel?->channel_name }}', '{{ $item->product ? $item->product?->formula_name : 'Unknown' }}')"
                                    class="text-blue-600 hover:text-blue-900">
                                    Restock
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                <p class="mt-2">No inventory records found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $inventory->links() }}
        </div>
    </div>

    <!-- Restock Modal -->
    <div id="restockModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-40 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900">Restock Inventory</h3>
                <button type="button" onclick="document.getElementById('restockModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div id="restockInfo" class="mb-4 p-3 bg-blue-50 rounded-lg">
                <!-- Dynamic content -->
            </div>

            <form id="restockForm" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantity to Add *</label>
                    <input type="number" name="quantity" step="0.01" min="0" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="document.getElementById('restockModal').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                        Add Stock
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function openRestockModal(inventoryId, channelName, productName) {
                document.getElementById('restockInfo').innerHTML = `
                    <div class="text-sm">
                        <div class="font-medium">${channelName}</div>
                        <div class="text-gray-600">${productName}</div>
                    </div>
                `;
                document.getElementById('restockForm').action = `/cosmetic/distribution/inventory/${inventoryId}/restock`;
                document.getElementById('restockModal').classList.remove('hidden');
            }
        </script>
    @endpush
@endsection
