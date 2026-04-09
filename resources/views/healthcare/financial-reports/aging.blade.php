<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Aging Report') }}</h2>
            <a href="{{ route('healthcare.financial-reports.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"><i
                    class="fas fa-arrow-left mr-2"></i>Back</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 rounded-md p-3"><i
                                class="fas fa-check-circle text-green-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Current</p>
                            <p class="text-2xl font-bold text-gray-900">Rp
                                {{ number_format($aging['current'], 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-100 rounded-md p-3"><i
                                class="fas fa-calendar text-blue-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">1-30 Days</p>
                            <p class="text-2xl font-bold text-gray-900">Rp
                                {{ number_format($aging['1_30_days'], 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3"><i
                                class="fas fa-calendar-week text-yellow-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">31-60 Days</p>
                            <p class="text-2xl font-bold text-gray-900">Rp
                                {{ number_format($aging['31_60_days'], 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-orange-100 rounded-md p-3"><i
                                class="fas fa-calendar-alt text-orange-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">61-90 Days</p>
                            <p class="text-2xl font-bold text-gray-900">Rp
                                {{ number_format($aging['61_90_days'], 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-red-100 rounded-md p-3"><i
                                class="fas fa-exclamation-triangle text-red-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Over 90 Days</p>
                            <p class="text-2xl font-bold text-gray-900">Rp
                                {{ number_format($aging['over_90_days'], 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                        class="fas fa-chart-bar mr-2 text-blue-600"></i>Aging Distribution</h3>
                @php
                    $total = array_sum($aging);
                @endphp
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-700">Current (Not Due)</span>
                            <span class="text-sm font-semibold text-gray-900">Rp
                                {{ number_format($aging['current'], 0, ',', '.') }}
                                ({{ $total > 0 ? number_format(($aging['current'] / $total) * 100, 1) : 0 }}%)</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-4">
                            <div class="bg-green-600 h-4 rounded-full"
                                style="width: {{ $total > 0 ? ($aging['current'] / $total) * 100 : 0 }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-700">1-30 Days Overdue</span>
                            <span class="text-sm font-semibold text-gray-900">Rp
                                {{ number_format($aging['1_30_days'], 0, ',', '.') }}
                                ({{ $total > 0 ? number_format(($aging['1_30_days'] / $total) * 100, 1) : 0 }}%)</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-4">
                            <div class="bg-blue-600 h-4 rounded-full"
                                style="width: {{ $total > 0 ? ($aging['1_30_days'] / $total) * 100 : 0 }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-700">31-60 Days Overdue</span>
                            <span class="text-sm font-semibold text-gray-900">Rp
                                {{ number_format($aging['31_60_days'], 0, ',', '.') }}
                                ({{ $total > 0 ? number_format(($aging['31_60_days'] / $total) * 100, 1) : 0 }}%)</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-4">
                            <div class="bg-yellow-600 h-4 rounded-full"
                                style="width: {{ $total > 0 ? ($aging['31_60_days'] / $total) * 100 : 0 }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-700">61-90 Days Overdue</span>
                            <span class="text-sm font-semibold text-gray-900">Rp
                                {{ number_format($aging['61_90_days'], 0, ',', '.') }}
                                ({{ $total > 0 ? number_format(($aging['61_90_days'] / $total) * 100, 1) : 0 }}%)</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-4">
                            <div class="bg-orange-600 h-4 rounded-full"
                                style="width: {{ $total > 0 ? ($aging['61_90_days'] / $total) * 100 : 0 }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-700">Over 90 Days (Critical)</span>
                            <span class="text-sm font-semibold text-gray-900">Rp
                                {{ number_format($aging['over_90_days'], 0, ',', '.') }}
                                ({{ $total > 0 ? number_format(($aging['over_90_days'] / $total) * 100, 1) : 0 }}%)</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-4">
                            <div class="bg-red-600 h-4 rounded-full"
                                style="width: {{ $total > 0 ? ($aging['over_90_days'] / $total) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                        class="fas fa-info-circle mr-2 text-indigo-600"></i>Total Outstanding</h3>
                <div class="bg-indigo-50 border-l-4 border-indigo-500 p-4 rounded">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-indigo-900">Total Outstanding Amount</p>
                            <p class="text-3xl font-bold text-indigo-900 mt-2">Rp
                                {{ number_format($total, 0, ',', '.') }}</p>
                        </div>
                        <i class="fas fa-money-bill-wave text-indigo-600 text-4xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                        class="fas fa-lightbulb mr-2 text-yellow-600"></i>Collection Priority</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded">
                        <p class="text-sm font-semibold text-red-900">High Priority</p>
                        <p class="text-xs text-red-700 mt-1">Over 60 days: Rp
                            {{ number_format($aging['61_90_days'] + $aging['over_90_days'], 0, ',', '.') }}</p>
                        <p class="text-xs text-red-600 mt-2">Immediate action required</p>
                    </div>
                    <div class="p-4 bg-yellow-50 border-l-4 border-yellow-500 rounded">
                        <p class="text-sm font-semibold text-yellow-900">Medium Priority</p>
                        <p class="text-xs text-yellow-700 mt-1">31-60 days: Rp
                            {{ number_format($aging['31_60_days'], 0, ',', '.') }}</p>
                        <p class="text-xs text-yellow-600 mt-2">Send reminder notices</p>
                    </div>
                    <div class="p-4 bg-green-50 border-l-4 border-green-500 rounded">
                        <p class="text-sm font-semibold text-green-900">Low Priority</p>
                        <p class="text-xs text-green-700 mt-1">0-30 days: Rp
                            {{ number_format($aging['current'] + $aging['1_30_days'], 0, ',', '.') }}</p>
                        <p class="text-xs text-green-600 mt-2">Monitor and follow up</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
