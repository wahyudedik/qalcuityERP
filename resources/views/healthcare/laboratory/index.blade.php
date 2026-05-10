<x-app-layout>
    <x-slot name="header">{{ __('Laboratory') }}</x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm text-gray-500">Pending Orders</p>
                    <p class="text-2xl font-semibold text-yellow-600">{{ $statistics['pending_orders'] ?? 0 }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm text-gray-500">Samples Collected</p>
                    <p class="text-2xl font-semibold text-blue-600">{{ $statistics['samples_collected'] ?? 0 }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm text-gray-500">In Analysis</p>
                    <p class="text-2xl font-semibold text-purple-600">{{ $statistics['in_analysis'] ?? 0 }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm text-gray-500">Completed Today</p>
                    <p class="text-2xl font-semibold text-green-600">{{ $statistics['completed_today'] ?? 0 }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm text-gray-500">Critical Results</p>
                    <p class="text-2xl font-semibold text-red-600">{{ $statistics['critical_results'] ?? 0 }}</p>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium mb-4">Recent Lab Orders</h3>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Test</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($recentOrders ?? [] as $order)
                                <tr>
                                    <td class="px-6 py-4 text-sm">{{ $order->order_number }}</td>
                                    <td class="px-6 py-4 text-sm">{{ $order->patient?->name ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm">{{ $order->labTest?->test_name ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm">{{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm">{{ $order->created_at?->format('d M Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No recent orders.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
