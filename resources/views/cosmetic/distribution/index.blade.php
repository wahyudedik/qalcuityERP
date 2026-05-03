@extends('layouts.app')

@section('title', 'Distribution Channels')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Distribution Channels</h1>
                    <p class="mt-1 text-sm text-gray-500">Manage your distribution channels and partnerships</p>
                </div>
                <button type="button" onclick="document.getElementById('addChannelModal').classList.remove('hidden')"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Channel
                </button>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <a href="{{ route('cosmetic.distribution.index') }}"
                class="bg-white rounded-lg shadow p-4 hover:shadow-md transition {{ request()->routeIs('cosmetic.distribution.index') ? 'ring-2 ring-blue-500' : '' }}">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500">Channels</div>
                        <div class="text-lg font-bold text-gray-900">Manage</div>
                    </div>
                </div>
            </a>
            <a href="{{ route('cosmetic.distribution.pricing') }}"
                class="bg-white rounded-lg shadow p-4 hover:shadow-md transition {{ request()->routeIs('cosmetic.distribution.pricing') ? 'ring-2 ring-blue-500' : '' }}">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500">Pricing</div>
                        <div class="text-lg font-bold text-gray-900">Manage</div>
                    </div>
                </div>
            </a>
            <a href="{{ route('cosmetic.distribution.inventory') }}"
                class="bg-white rounded-lg shadow p-4 hover:shadow-md transition {{ request()->routeIs('cosmetic.distribution.inventory') ? 'ring-2 ring-blue-500' : '' }}">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500">Inventory</div>
                        <div class="text-lg font-bold text-gray-900">Manage</div>
                    </div>
                </div>
            </a>
            <a href="{{ route('cosmetic.distribution.performance') }}"
                class="bg-white rounded-lg shadow p-4 hover:shadow-md transition {{ request()->routeIs('cosmetic.distribution.performance') ? 'ring-2 ring-blue-500' : '' }}">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-500">Performance</div>
                        <div class="text-lg font-bold text-gray-900">Analytics</div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Total Channels</div>
                <div class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['total_channels'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Active Channels</div>
                <div class="mt-2 text-3xl font-bold text-green-600">{{ $stats['active_channels'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Retail</div>
                <div class="mt-2 text-3xl font-bold text-blue-600">{{ $stats['retail_channels'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Online</div>
                <div class="mt-2 text-3xl font-bold text-purple-600">{{ $stats['online_channels'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Distributors</div>
                <div class="mt-2 text-3xl font-bold text-orange-600">{{ $stats['distributor_channels'] }}</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" action="{{ route('cosmetic.distribution.index') }}" class="flex gap-4">
                <div class="flex-1">
                    <select name="type"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Channel Types</option>
                        <option value="retail" {{ request('type') == 'retail' ? 'selected' : '' }}>Retail Store</option>
                        <option value="online_marketplace" {{ request('type') == 'online_marketplace' ? 'selected' : '' }}>
                            Online Marketplace</option>
                        <option value="distributor" {{ request('type') == 'distributor' ? 'selected' : '' }}>Distributor
                        </option>
                        <option value="reseller_mlm" {{ request('type') == 'reseller_mlm' ? 'selected' : '' }}>Reseller/MLM
                        </option>
                    </select>
                </div>
                <button type="submit"
                    class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition">
                    Filter
                </button>
            </form>
        </div>

        <!-- Channels Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Channel
                            Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Commission</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($channels as $channel)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $channel->channel_code }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="font-medium">{{ $channel->channel_name }}</div>
                                @if ($channel->description)
                                    <div class="text-xs text-gray-500">{{ Str::limit($channel->description, 50) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full
                                    {{ $channel->channel_type === 'retail'
                                        ? 'bg-blue-100 text-blue-800'
                                        : ($channel->channel_type === 'online_marketplace'
                                            ? 'bg-purple-100 text-purple-800'
                                            : ($channel->channel_type === 'distributor'
                                                ? 'bg-orange-100 text-orange-800'
                                                : 'bg-green-100 text-green-800')) }}">
                                    {{ $channel->type_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if ($channel->contact_person)
                                    <div class="font-medium">{{ $channel->contact_person }}</div>
                                    @if ($channel->contact_email)
                                        <div class="text-xs">{{ $channel->contact_email }}</div>
                                    @endif
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($channel->commission_rate, 2) }}%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($channel->discount_rate, 2) }}%</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full {{ $channel->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $channel->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <span class="text-gray-500" title="Pricing: {{ $channel->pricing_count }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </span>
                                    <span class="text-gray-500" title="Inventory: {{ $channel->inventory_count }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                <p class="mt-2">No distribution channels found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $channels->links() }}
        </div>
    </div>

    <!-- Add Channel Modal -->
    <div id="addChannelModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900">Add Distribution Channel</h3>
                <button type="button" onclick="document.getElementById('addChannelModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('cosmetic.distribution.channels.store') }}" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Channel Name *</label>
                        <input type="text" name="channel_name" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Channel Type *</label>
                        <select name="channel_type" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Type</option>
                            <option value="retail">Retail Store</option>
                            <option value="online_marketplace">Online Marketplace</option>
                            <option value="distributor">Distributor</option>
                            <option value="reseller_mlm">Reseller/MLM</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contact Person</label>
                        <input type="text" name="contact_person"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contact Email</label>
                        <input type="email" name="contact_email"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contact Phone</label>
                        <input type="text" name="contact_phone"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Commission Rate (%)</label>
                        <input type="number" name="commission_rate" step="0.01" min="0" max="100"
                            value="0"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Discount Rate (%)</label>
                        <input type="number" name="discount_rate" step="0.01" min="0" max="100"
                            value="0"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" checked
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-gray-700">Active</label>
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="document.getElementById('addChannelModal').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                        Create Channel
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
