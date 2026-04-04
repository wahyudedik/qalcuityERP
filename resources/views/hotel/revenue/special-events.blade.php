@extends('layouts.app')

@section('title', 'Special Events')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Special Events</h1>
                <p class="text-gray-600">Manage events that affect demand and pricing</p>
            </div>
            <button onclick="document.getElementById('createModal').classList.remove('hidden')"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                + Add Event
            </button>
        </div>

        <!-- Events Table -->
        <div class="bg-white rounded-lg shadow">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Event Name</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Dates</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Impact Level</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Demand Increase</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Affects Pricing</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($events as $event)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ $event->name }}</div>
                                    <div class="text-sm text-gray-500">{{ Str::limit($event->description, 50) }}</div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div>{{ $event->start_date->format('M d, Y') }}</div>
                                    <div class="text-sm text-gray-500">to {{ $event->end_date->format('M d, Y') }}</div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="px-2 py-1 text-xs rounded
                                {{ $event->impact_level === 'very_high' ? 'bg-red-100 text-red-700' : '' }}
                                {{ $event->impact_level === 'high' ? 'bg-orange-100 text-orange-700' : '' }}
                                {{ $event->impact_level === 'medium' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                {{ $event->impact_level === 'low' ? 'bg-green-100 text-green-700' : '' }}">
                                        {{ ucfirst(str_replace('_', ' ', $event->impact_level)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($event->expected_demand_increase > 0)
                                        <span
                                            class="font-medium text-blue-600">+{{ number_format($event->expected_demand_increase, 0) }}%</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($event->affects_pricing)
                                        <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Yes</span>
                                    @else
                                        <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-700">No</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                    No upcoming special events. Add events to enable event-based pricing.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t">
                {{ $events->links() }}
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div id="createModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-lg max-w-lg w-full">
                <div class="px-6 py-4 border-b flex justify-between items-center">
                    <h3 class="text-lg font-semibold">Add Special Event</h3>
                    <button onclick="document.getElementById('createModal').classList.add('hidden')"
                        class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>
                <form action="{{ route('revenue.special-events.store') }}" method="POST" class="p-6">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Event Name</label>
                            <input type="text" name="name" required class="w-full border rounded px-3 py-2"
                                placeholder="e.g., Summer Music Festival">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" rows="2" class="w-full border rounded px-3 py-2"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                                <input type="date" name="start_date" required class="w-full border rounded px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                                <input type="date" name="end_date" required class="w-full border rounded px-3 py-2">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Impact Level</label>
                            <select name="impact_level" required class="w-full border rounded px-3 py-2">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="very_high">Very High</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Expected Demand Increase (%)</label>
                            <input type="number" name="expected_demand_increase" min="0" max="100"
                                class="w-full border rounded px-3 py-2" placeholder="e.g., 25">
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="affects_pricing" value="1" checked class="mr-2">
                                <span class="text-sm">Affects Pricing</span>
                            </label>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="document.getElementById('createModal').classList.add('hidden')"
                            class="px-4 py-2 border rounded hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Add
                            Event</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
