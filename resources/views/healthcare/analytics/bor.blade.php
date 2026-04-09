<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Bed Occupancy Rate (BOR)') }}</h2>
            <a href="{{ route('healthcare.analytics.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"><i
                    class="fas fa-arrow-left mr-2"></i>Back</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('healthcare.analytics.bor') }}"
                        class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                            <input type="date" name="date_from"
                                value="{{ $dateFrom instanceof \Carbon\Carbon ? $dateFrom->format('Y-m-d') : $dateFrom }}"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                            <input type="date" name="date_to"
                                value="{{ $dateTo instanceof \Carbon\Carbon ? $dateTo->format('Y-m-d') : $dateTo }}"
                                class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div class="flex items-end">
                            <button type="submit"
                                class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                                    class="fas fa-filter mr-2"></i>Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-100 rounded-md p-3"><i
                                class="fas fa-bed text-blue-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Beds</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $bor['total_beds'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 rounded-md p-3"><i
                                class="fas fa-procedures text-green-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Avg Occupied</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $bor['avg_occupied'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-100 rounded-md p-3"><i
                                class="fas fa-percentage text-purple-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Occupancy Rate</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($bor['occupancy_rate'], 1) }}%
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3"><i
                                class="fas fa-chart-line text-yellow-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Target</p>
                            <p class="text-2xl font-bold text-gray-900">60-85%</p>
                        </div>
                    </div>
                </div>
            </div>

            @if (count($bor['by_ward']) > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-hospital mr-2 text-blue-600"></i>BOR by Ward</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ward
                                        Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total
                                        Beds</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Occupied
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">BOR</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($bor['by_ward'] as $ward)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $ward['name'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            {{ $ward['total_beds'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            {{ $ward['occupied'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-32 bg-gray-200 rounded-full h-2.5 mr-3">
                                                    <div class="bg-blue-600 h-2.5 rounded-full"
                                                        style="width: {{ min($ward['bor'], 100) }}%"></div>
                                                </div>
                                                <span
                                                    class="text-sm font-semibold text-gray-900">{{ number_format($ward['bor'], 1) }}%</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($ward['bor'] >= 60 && $ward['bor'] <= 85)
                                                <span
                                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Optimal</span>
                                            @elseif($ward['bor'] < 60)
                                                <span
                                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Under-utilized</span>
                                            @else
                                                <span
                                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Over-capacity</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                        class="fas fa-info-circle mr-2 text-indigo-600"></i>BOR Classification</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded">
                        <p class="text-sm font-semibold text-red-900">&lt; 60%</p>
                        <p class="text-xs text-red-700 mt-1">Under-utilized</p>
                    </div>
                    <div class="p-4 bg-green-50 border-l-4 border-green-500 rounded">
                        <p class="text-sm font-semibold text-green-900">60% - 85%</p>
                        <p class="text-xs text-green-700 mt-1">Optimal (Ideal)</p>
                    </div>
                    <div class="p-4 bg-yellow-50 border-l-4 border-yellow-500 rounded">
                        <p class="text-sm font-semibold text-yellow-900">85% - 90%</p>
                        <p class="text-xs text-yellow-700 mt-1">High Utilization</p>
                    </div>
                    <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded">
                        <p class="text-sm font-semibold text-red-900">&gt; 90%</p>
                        <p class="text-xs text-red-700 mt-1">Over-capacity</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
