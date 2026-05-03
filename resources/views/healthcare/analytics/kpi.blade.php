<x-app-layout>
    <x-slot name="header">{{ __('KPI Dashboard') }}</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('healthcare.analytics.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"><i
                    class="fas fa-arrow-left mr-2"></i>Back</a>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-500">Bed Occupancy Rate</h3>
                        <i class="fas fa-bed text-blue-600 text-2xl"></i>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($kpis['bed_occupancy_rate'], 1) }}%</p>
                    <p class="text-xs text-gray-500 mt-2">Target: 60-85%</p>
                    @if ($kpis['bed_occupancy_rate'] >= 60 && $kpis['bed_occupancy_rate'] <= 85)
                        <span
                            class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">On
                            Target</span>
                    @else
                        <span
                            class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Off
                            Target</span>
                    @endif
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-500">Avg Length of Stay</h3>
                        <i class="fas fa-clock text-green-600 text-2xl"></i>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($kpis['average_length_of_stay'], 1) }}
                        days</p>
                    <p class="text-xs text-gray-500 mt-2">Target: < 5 days</p>
                            @if ($kpis['average_length_of_stay'] < 5)
                                <span
                                    class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Good</span>
                            @else
                                <span
                                    class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Needs
                                    Improvement</span>
                            @endif
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-500">Mortality Rate</h3>
                        <i class="fas fa-heartbeat text-red-600 text-2xl"></i>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($kpis['mortality_rate'], 2) }}%</p>
                    <p class="text-xs text-gray-500 mt-2">Target: < 3%</p>
                            @if ($kpis['mortality_rate'] < 3)
                                <span
                                    class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Acceptable</span>
                            @else
                                <span
                                    class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Critical</span>
                            @endif
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-500">Infection Rate</h3>
                        <i class="fas fa-virus text-orange-600 text-2xl"></i>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($kpis['infection_rate'], 2) }}%</p>
                    <p class="text-xs text-gray-500 mt-2">Target: < 2%</p>
                            @if ($kpis['infection_rate'] < 2)
                                <span
                                    class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Good</span>
                            @else
                                <span
                                    class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">High
                                    Risk</span>
                            @endif
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-500">Patient Satisfaction</h3>
                        <i class="fas fa-smile text-teal-600 text-2xl"></i>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($kpis['patient_satisfaction'], 1) }}%
                    </p>
                    <p class="text-xs text-gray-500 mt-2">Target: > 85%</p>
                    @if ($kpis['patient_satisfaction'] >= 85)
                        <span
                            class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Excellent</span>
                    @else
                        <span
                            class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Needs
                            Work</span>
                    @endif
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-500">Readmission Rate</h3>
                        <i class="fas fa-undo text-purple-600 text-2xl"></i>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($kpis['readmission_rate'], 2) }}%</p>
                    <p class="text-xs text-gray-500 mt-2">Target: < 5%</p>
                            @if ($kpis['readmission_rate'] < 5)
                                <span
                                    class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Good</span>
                            @else
                                <span
                                    class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">High</span>
                            @endif
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-500">Surgery Cancellation</h3>
                        <i class="fas fa-ban text-pink-600 text-2xl"></i>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">
                        {{ number_format($kpis['surgery_cancellation_rate'], 2) }}%</p>
                    <p class="text-xs text-gray-500 mt-2">Target: < 3%</p>
                            @if ($kpis['surgery_cancellation_rate'] < 3)
                                <span
                                    class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Acceptable</span>
                            @else
                                <span
                                    class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">High</span>
                            @endif
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-500">Emergency Wait Time</h3>
                        <i class="fas fa-ambulance text-indigo-600 text-2xl"></i>
                    </div>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($kpis['emergency_wait_time'], 0) }} min
                    </p>
                    <p class="text-xs text-gray-500 mt-2">Target: < 30 min</p>
                            @if ($kpis['emergency_wait_time'] < 30)
                                <span
                                    class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Good</span>
                            @else
                                <span
                                    class="inline-block mt-2 px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Slow</span>
                            @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
