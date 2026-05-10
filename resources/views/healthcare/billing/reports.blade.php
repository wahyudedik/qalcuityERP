<x-app-layout>
    <x-slot name="header">{{ __('Billing Reports') }}</x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <form method="GET" class="flex items-center gap-4">
                    <div>
                        <label class="text-sm text-gray-500">From</label>
                        <input type="date" name="date_from"
                            value="{{ is_string($dateFrom) ? $dateFrom : $dateFrom->format('Y-m-d') }}"
                            class="border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">To</label>
                        <input type="date" name="date_to"
                            value="{{ is_string($dateTo) ? $dateTo : $dateTo->format('Y-m-d') }}"
                            class="border-gray-300 rounded-md shadow-sm">
                    </div>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm mt-5">Filter</button>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Billed</p>
                    <p class="text-xl font-semibold">Rp {{ number_format($report['total_billed'] ?? 0, 0, ',', '.') }}
                    </p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Collected</p>
                    <p class="text-xl font-semibold text-green-600">Rp
                        {{ number_format($report['total_collected'] ?? 0, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Pending</p>
                    <p class="text-xl font-semibold text-yellow-600">Rp
                        {{ number_format($report['total_pending'] ?? 0, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm text-gray-500">Collection Rate</p>
                    <p class="text-xl font-semibold">{{ $report['collection_rate'] ?? 0 }}%</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
