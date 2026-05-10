<x-app-layout>
    <x-slot name="header">{{ __('Pharmacy Reports') }}</x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filter -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <form method="GET" class="flex items-center gap-4">
                    <div>
                        <label class="text-sm text-gray-500">From</label>
                        <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}"
                            class="border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">To</label>
                        <input type="date" name="date_to" value="{{ $dateTo ?? '' }}"
                            class="border-gray-300 rounded-md shadow-sm">
                    </div>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm mt-5">Filter</button>
                </form>
            </div>

            <!-- Report Summary -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Dispensed</p>
                    <p class="text-2xl font-semibold">{{ $report['total_dispensed'] ?? 0 }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Revenue</p>
                    <p class="text-2xl font-semibold">Rp {{ number_format($report['total_revenue'] ?? 0, 0, ',', '.') }}
                    </p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm text-gray-500">Items Restocked</p>
                    <p class="text-2xl font-semibold">{{ $report['items_restocked'] ?? 0 }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm text-gray-500">Expired Items</p>
                    <p class="text-2xl font-semibold text-red-600">{{ $report['expired_items'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
