@extends('layouts.app')
@section('title', 'Ingredient Waste Tracking')
@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Ingredient Waste Tracking</h1>
                <p class="mt-1 text-sm text-gray-600">Monitor and reduce ingredient waste</p>
            </div>
            <button onclick="document.getElementById('wasteModal').classList.remove('hidden')"
                class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                + Record Waste
            </button>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-500">Total Waste Cost</div>
                <div class="text-2xl font-bold text-red-600">Rp {{ number_format($stats['total_waste_cost'], 0, ',', '.') }}
                </div>
            </div>
            <div class="bg-yellow-50 rounded-lg shadow p-4 border-l-4 border-yellow-500">
                <div class="text-sm text-yellow-600">Items Wasted</div>
                <div class="text-2xl font-bold text-yellow-700">{{ $stats['total_items_wasted'] }}</div>
            </div>
            <div class="bg-blue-50 rounded-lg shadow p-4 border-l-4 border-blue-500">
                <div class="text-sm text-blue-600">Daily Average</div>
                <div class="text-xl font-bold text-blue-700">Rp {{ number_format($stats['daily_average'], 0, ',', '.') }}
                </div>
            </div>
            <div
                class="{{ $trends['trend_direction'] === 'decreasing' ? 'bg-green-50 border-green-500' : ($trends['trend_direction'] === 'increasing' ? 'bg-red-50 border-red-500' : 'bg-gray-50 border-gray-500') }} rounded-lg shadow p-4 border-l-4">
                <div
                    class="text-sm {{ $trends['trend_direction'] === 'decreasing' ? 'text-green-600' : ($trends['trend_direction'] === 'increasing' ? 'text-red-600' : 'text-gray-600') }}">
                    Trend</div>
                <div class="text-xl font-bold capitalize">{{ $trends['trend_direction'] }}</div>
            </div>
        </div>

        @if (!empty($recommendations))
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6">
                <h3 class="font-semibold text-yellow-800 mb-2">Recommendations to Reduce Waste:</h3>
                <ul class="space-y-2">
                    @foreach ($recommendations as $rec)
                        <li class="flex items-start">
                            <span
                                class="px-2 py-0.5 text-xs rounded mr-2 {{ $rec['priority'] === 'high' ? 'bg-red-600 text-white' : 'bg-yellow-600 text-white' }}">
                                {{ strtoupper($rec['priority']) }}
                            </span>
                            <span class="text-sm">{{ $rec['message'] }}</span>
                            <span class="ml-auto text-xs text-green-700 font-medium">Potential savings: Rp
                                {{ number_format($rec['potential_savings'], 0, ',', '.') }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Recent Wastes -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h2 class="text-lg font-semibold">Recent Waste Records</h2>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cost</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentWastes as $waste)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $waste->wasted_at->format('d M Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ $waste->item_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $waste->quantity_wasted }}
                                {{ $waste->unit }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-red-600">Rp
                                {{ number_format($waste->total_waste_cost, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">{{ $waste->getWasteTypeLabel() }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm capitalize">{{ $waste->department }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">No waste records yet
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Record Waste Modal -->
    <div id="wasteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h2 class="text-xl font-bold mb-4">Record Ingredient Waste</h2>
            <form action="{{ route('fnb.waste.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Item Name</label>
                        <input type="text" name="item_name" required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Quantity</label>
                            <input type="number" name="quantity_wasted" required step="0.001" min="0.001"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Unit</label>
                            <input type="text" name="unit" required placeholder="kg, pcs, liters"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Cost per Unit (Rp)</label>
                        <input type="number" name="cost_per_unit" required step="0.01" min="0"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Waste Type</label>
                        <select name="waste_type" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="spoilage">Spoilage/Rusak</option>
                            <option value="over_production">Over Production</option>
                            <option value="preparation_error">Preparation Error</option>
                            <option value="expired">Expired</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Reason</label>
                        <textarea name="reason" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Department</label>
                        <select name="department" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="kitchen">Kitchen</option>
                            <option value="bar">Bar</option>
                            <option value="storage">Storage</option>
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="document.getElementById('wasteModal').classList.add('hidden')"
                        class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Record
                        Waste</button>
                </div>
            </form>
        </div>
    </div>
@endsection
