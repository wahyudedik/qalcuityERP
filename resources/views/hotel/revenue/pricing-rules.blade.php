@extends('layouts.app')

@section('title', 'Dynamic Pricing Rules')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Dynamic Pricing Rules</h1>
                <p class="text-gray-600">Configure automated pricing adjustments</p>
            </div>
            <button onclick="document.getElementById('createModal').classList.remove('hidden')"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                + New Pricing Rule
            </button>
        </div>

        <!-- Rules Table -->
        <div class="bg-white rounded-lg shadow">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Name</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Type</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Adjustment</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Priority</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Status</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Valid Period</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($rules as $rule)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ $rule->name }}</div>
                                    @if ($rule->ratePlan)
                                        <div class="text-sm text-gray-500">Applies to: {{ $rule->ratePlan?->name }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-1 text-xs rounded bg-gray-100">
                                        {{ ucfirst(str_replace('_', ' ', $rule->rule_type)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="font-medium {{ $rule->adjustment_value > 0 ? 'text-red-600' : 'text-green-600' }}">
                                        {{ $rule->adjustment_value > 0 ? '+' : '' }}{{ number_format($rule->adjustment_value, 1) }}{{ $rule->adjustment_type === 'percentage' ? '%' : '$' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="px-2 py-1 text-xs rounded
                                {{ $rule->priority === 'high' ? 'bg-red-100 text-red-700' : '' }}
                                {{ $rule->priority === 'medium' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                {{ $rule->priority === 'low' ? 'bg-green-100 text-green-700' : '' }}">
                                        {{ ucfirst($rule->priority) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="px-2 py-1 text-xs rounded {{ $rule->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                        {{ $rule->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center text-sm">
                                    @if ($rule->valid_from && $rule->valid_to)
                                        {{ $rule->valid_from->format('M d') }} - {{ $rule->valid_to->format('M d, Y') }}
                                    @elseif($rule->valid_from)
                                        From {{ $rule->valid_from->format('M d, Y') }}
                                    @elseif($rule->valid_to)
                                        Until {{ $rule->valid_to->format('M d, Y') }}
                                    @else
                                        Always Active
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                    No pricing rules configured. Create your first rule to enable dynamic pricing.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t">
                {{ $rules->links() }}
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div id="createModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full max-h-screen overflow-y-auto">
                <div class="px-6 py-4 border-b flex justify-between items-center">
                    <h3 class="text-lg font-semibold">Create Pricing Rule</h3>
                    <button onclick="document.getElementById('createModal').classList.add('hidden')"
                        class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>
                <form action="{{ route('revenue.pricing-rules.store') }}" method="POST" class="p-6">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input type="text" name="name" required class="w-full border rounded px-3 py-2"
                                placeholder="e.g., Weekend Premium">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" rows="2" class="w-full border rounded px-3 py-2"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Rule Type</label>
                                <select name="rule_type" required class="w-full border rounded px-3 py-2">
                                    <option value="occupancy_based">Occupancy Based</option>
                                    <option value="seasonal">Seasonal</option>
                                    <option value="day_of_week">Day of Week</option>
                                    <option value="length_of_stay">Length of Stay</option>
                                    <option value="advance_booking">Advance Booking</option>
                                    <option value="competitor_based">Competitor Based</option>
                                    <option value="event_based">Event Based</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Rate Plan (Optional)</label>
                                <select name="rate_plan_id" class="w-full border rounded px-3 py-2">
                                    <option value="">All Rate Plans</option>
                                    @foreach ($ratePlans as $plan)
                                        <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Adjustment Type</label>
                                <select name="adjustment_type" required class="w-full border rounded px-3 py-2">
                                    <option value="percentage">Percentage (%)</option>
                                    <option value="fixed_amount">Fixed Amount ($)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Adjustment Value</label>
                                <input type="number" name="adjustment_value" step="0.01" required
                                    class="w-full border rounded px-3 py-2" placeholder="e.g., 10 for +10% or -10 for -10%">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                            <select name="priority" required class="w-full border rounded px-3 py-2">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Valid From</label>
                                <input type="date" name="valid_from" class="w-full border rounded px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Valid To</label>
                                <input type="date" name="valid_to" class="w-full border rounded px-3 py-2">
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="document.getElementById('createModal').classList.add('hidden')"
                            class="px-4 py-2 border rounded hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Create
                            Rule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
