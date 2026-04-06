@extends('layouts.app')

@section('title', 'Material Deliveries')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Material Delivery Tracking</h1>
                <p class="text-sm text-gray-600 mt-1">Track material deliveries, quality checks, and shortages</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('construction.deliveries.delayed-report') }}"
                    class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                    Delayed Report
                </a>
                <a href="{{ route('construction.deliveries.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    New Delivery
                </a>
            </div>
        </div>

        <!-- Project Filter -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" class="flex gap-4 items-end">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Project</label>
                    <select name="project_id" onchange="this.form.submit()"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">-- Select Project --</option>
                        @foreach ($projects as $project)
                            <option value="{{ $project->id }}" {{ $selectedProject == $project->id ? 'selected' : '' }}>
                                {{ $project->name }} ({{ $project->number }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="w-48">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Period</label>
                    <select name="period" onchange="this.form.submit()"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="week" {{ $period === 'week' ? 'selected' : '' }}>This Week</option>
                        <option value="month" {{ $period === 'month' ? 'selected' : '' }}>This Month</option>
                        <option value="all" {{ $period === 'all' ? 'selected' : '' }}>All Time</option>
                    </select>
                </div>
            </form>
        </div>

        @if ($summary)
            <!-- Summary Stats -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
                    <div class="text-sm text-gray-600">Total Deliveries</div>
                    <div class="text-2xl font-bold text-blue-700">{{ $summary['total_deliveries'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
                    <div class="text-sm text-gray-600">On-Time</div>
                    <div class="text-2xl font-bold text-green-700">{{ $summary['on_time_deliveries'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
                    <div class="text-sm text-gray-600">Delayed</div>
                    <div class="text-2xl font-bold text-red-700">{{ $summary['delayed_deliveries'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
                    <div class="text-sm text-gray-600">Pending</div>
                    <div class="text-2xl font-bold text-yellow-700">{{ $summary['pending_deliveries'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-500">
                    <div class="text-sm text-gray-600">Total Value</div>
                    <div class="text-xl font-bold text-purple-700">Rp
                        {{ number_format($summary['total_value'], 0, ',', '.') }}</div>
                </div>
            </div>

            <!-- Status Breakdown -->
            @if (!empty($summary['by_status']))
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Delivery Status Breakdown</h3>
                    <div class="flex flex-wrap gap-3">
                        @foreach ($summary['by_status'] as $status => $count)
                            <div class="px-4 py-2 bg-gray-100 rounded-lg">
                                <span class="font-medium capitalize">{{ str_replace('_', ' ', $status) }}</span>
                                <span class="text-gray-600 ml-2">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Recent Deliveries Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Deliveries</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Delivery #</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Material</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Expected Date
                                </th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actual Date
                                </th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Quality</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($recentDeliveries as $delivery)
                                <tr>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                        {{ $delivery->delivery_number }}</td>
                                    <td class="px-4 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $delivery->material_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $delivery->supplier_name }}</div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-center text-sm">
                                        {{ $delivery->quantity_delivered }}/{{ $delivery->quantity_ordered }}
                                        {{ $delivery->unit }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-center text-sm">
                                        {{ $delivery->expected_date?->format('d M Y') }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap text-center text-sm">
                                        {{ $delivery->actual_delivery_date?->format('d M Y H:i') ?? '-' }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap text-center">
                                        @if ($delivery->delivery_status === 'delivered')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Delivered</span>
                                        @elseif($delivery->delivery_status === 'in_transit')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">In
                                                Transit</span>
                                        @elseif($delivery->delivery_status === 'partial')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Partial</span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 capitalize">{{ str_replace('_', ' ', $delivery->delivery_status) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-center">
                                        @if ($delivery->quality_check_status === 'passed')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Passed</span>
                                        @elseif($delivery->quality_check_status === 'failed')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Failed</span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Pending</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <a href="{{ route('construction.deliveries.show', $delivery) }}"
                                            class="text-blue-600 hover:text-blue-900">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-12 text-center text-gray-500">
                                        No deliveries found. Create your first material delivery record!
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($recentDeliveries instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $recentDeliveries->links() }}
                    </div>
                @endif
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No project selected</h3>
                <p class="mt-1 text-sm text-gray-500">Select a project to view material deliveries.</p>
            </div>
        @endif
    </div>
@endsection
