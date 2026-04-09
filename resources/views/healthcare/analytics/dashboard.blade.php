<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Analytics Dashboard') }}</h2>
            <a href="{{ route('healthcare.analytics.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"><i
                    class="fas fa-arrow-left mr-2"></i>Back</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-100 rounded-md p-3"><i
                                class="fas fa-user-injured text-blue-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Patients</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $overview['total_patients'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 rounded-md p-3"><i
                                class="fas fa-procedures text-green-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Admissions</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $overview['total_admissions'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-100 rounded-md p-3"><i
                                class="fas fa-cut text-purple-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Surgeries</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $overview['total_surgeries'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-indigo-100 rounded-md p-3"><i
                                class="fas fa-stethoscope text-indigo-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Consultations</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $overview['total_consultations'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-pink-100 rounded-md p-3"><i
                                class="fas fa-dollar-sign text-pink-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Revenue</p>
                            <p class="text-2xl font-bold text-gray-900">Rp
                                {{ number_format($overview['total_revenue'], 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3"><i
                                class="fas fa-bed text-yellow-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Bed Occupancy</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ number_format($overview['bed_occupancy_rate'], 1) }}%</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-orange-100 rounded-md p-3"><i
                                class="fas fa-clock text-orange-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Avg Length of Stay</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ number_format($overview['average_length_of_stay'], 1) }} days</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-red-100 rounded-md p-3"><i
                                class="fas fa-heartbeat text-red-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Mortality Rate</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ number_format($overview['mortality_rate'], 2) }}%</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-teal-100 rounded-md p-3"><i
                                class="fas fa-smile text-teal-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Patient Satisfaction</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ number_format($overview['patient_satisfaction'], 1) }}%</p>
                        </div>
                    </div>
                </div>
            </div>

            @if (count($alerts) > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-exclamation-triangle mr-2 text-yellow-600"></i>Active Alerts</h3>
                    <div class="space-y-2">
                        @foreach ($alerts as $alert)
                            <div
                                class="flex items-start p-3 {{ $alert['severity'] === 'critical' ? 'bg-red-50 border-l-4 border-red-500' : 'bg-yellow-50 border-l-4 border-yellow-500' }} rounded">
                                <i
                                    class="fas {{ $alert['severity'] === 'critical' ? 'fa-exclamation-circle text-red-600' : 'fa-exclamation-triangle text-yellow-600' }} mt-0.5 mr-3"></i>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $alert['title'] }}</p>
                                    <p class="text-xs text-gray-600 mt-1">{{ $alert['message'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                        class="fas fa-chart-bar mr-2 text-blue-600"></i>Recent Trends</h3>
                @if (count($recentTrends) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Metric
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Current
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Previous
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Change
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trend
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($recentTrends as $trend)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $trend['metric'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            {{ $trend['current'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            {{ $trend['previous'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full {{ $trend['change'] >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $trend['change'] >= 0 ? '+' : '' }}{{ $trend['change'] }}%
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <i
                                                class="fas {{ $trend['change'] >= 0 ? 'fa-arrow-up text-green-600' : 'fa-arrow-down text-red-600' }}"></i>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-500 text-center py-8">No trend data available</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
