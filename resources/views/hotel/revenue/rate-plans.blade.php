@extends('layouts.app')

@section('title', 'Rate Plans')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Rate Plans</h1>
                <p class="text-gray-600">Manage pricing strategies and rate configurations</p>
            </div>
            <button onclick="document.getElementById('createModal').classList.remove('hidden')"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                + New Rate Plan
            </button>
        </div>

        <!-- Rate Plans Table -->
        <div class="bg-white rounded-lg shadow">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Name</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Room Type</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Base Rate</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Type</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Min/Max Stay</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Status</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($ratePlans as $plan)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ $plan->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $plan->code }}</div>
                                </td>
                                <td class="px-4 py-3">{{ $plan->roomType?->name ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-center font-medium">${{ number_format($plan->base_rate, 2) }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="px-2 py-1 text-xs rounded 
                                {{ $plan->type === 'standard' ? 'bg-gray-100 text-gray-700' : '' }}
                                {{ $plan->type === 'non_refundable' ? 'bg-red-100 text-red-700' : '' }}
                                {{ $plan->type === 'package' ? 'bg-blue-100 text-blue-700' : '' }}
                                {{ $plan->type === 'corporate' ? 'bg-purple-100 text-purple-700' : '' }}
                                {{ $plan->type === 'promotional' ? 'bg-green-100 text-green-700' : '' }}">
                                        {{ ucfirst(str_replace('_', ' ', $plan->type)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center text-sm">
                                    {{ $plan->min_stay }} / {{ $plan->max_stay ?? '∞' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="px-2 py-1 text-xs rounded {{ $plan->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                        {{ $plan->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button onclick="editPlan({{ $plan->id }})"
                                        class="text-blue-600 hover:text-blue-800 mr-2">Edit</button>
                                    <a href="{{ route('revenue.pricing-rules') }}?rate_plan={{ $plan->id }}"
                                        class="text-gray-600 hover:text-gray-800">Rules</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                    No rate plans found. Create your first rate plan to get started.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t">
                {{ $ratePlans->links() }}
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div id="createModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full max-h-screen overflow-y-auto">
                <div class="px-6 py-4 border-b flex justify-between items-center">
                    <h3 class="text-lg font-semibold">Create Rate Plan</h3>
                    <button onclick="document.getElementById('createModal').classList.add('hidden')"
                        class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>
                <form action="{{ route('revenue.rate-plans.store') }}" method="POST" class="p-6">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input type="text" name="name" required class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Code</label>
                            <input type="text" name="code" required class="w-full border rounded px-3 py-2"
                                placeholder="e.g., STD">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Room Type</label>
                            <select name="room_type_id" required class="w-full border rounded px-3 py-2">
                                @foreach ($roomTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Base Rate ($)</label>
                            <input type="number" name="base_rate" step="0.01" min="0" required
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                            <select name="type" required class="w-full border rounded px-3 py-2">
                                <option value="standard">Standard</option>
                                <option value="non_refundable">Non-Refundable</option>
                                <option value="package">Package</option>
                                <option value="corporate">Corporate</option>
                                <option value="promotional">Promotional</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Min Stay</label>
                            <input type="number" name="min_stay" min="1" value="1"
                                class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Max Stay</label>
                            <input type="number" name="max_stay" min="1" class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Valid From</label>
                            <input type="date" name="valid_from" class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Valid To</label>
                            <input type="date" name="valid_to" class="w-full border rounded px-3 py-2">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" rows="2" class="w-full border rounded px-3 py-2"></textarea>
                        </div>
                        <div class="col-span-2 flex space-x-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_refundable" value="1" checked class="mr-2">
                                <span class="text-sm">Refundable</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="includes_breakfast" value="1" class="mr-2">
                                <span class="text-sm">Includes Breakfast</span>
                            </label>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="document.getElementById('createModal').classList.add('hidden')"
                            class="px-4 py-2 border rounded hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Create
                            Rate Plan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
