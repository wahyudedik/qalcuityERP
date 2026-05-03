<x-app-layout>
    <x-slot name="header">
        {{ __('Laporan Pendapatan per Paket') }}
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('Pendapatan per Paket') }}</h1>
                    <p class="mt-1 text-sm text-gray-600">{{ __('Analisis distribusi pendapatan dari paket internet') }}</p>
                </div>
                <a href="{{ route('telecom.reports.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-arrow-left mr-2"></i>{{ __('Kembali') }}
                </a>
            </div>

            <!-- Filters -->
            <div class="bg-white shadow-sm sm:rounded-lg p-4 mb-6">
                <form method="GET" action="{{ route('telecom.reports.revenue-by-package') }}" class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Dari Tanggal') }}</label>
                        <input type="date" name="start_date" value="{{ $filters['start_date'] instanceof \Carbon\Carbon ? $filters['start_date']->format('Y-m-d') : $filters['start_date'] }}"
                            class="rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Sampai Tanggal') }}</label>
                        <input type="date" name="end_date" value="{{ $filters['end_date'] instanceof \Carbon\Carbon ? $filters['end_date']->format('Y-m-d') : $filters['end_date'] }}"
                            class="rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm">
                            <i class="fas fa-filter mr-1"></i>{{ __('Filter') }}
                        </button>
                        <a href="{{ route('telecom.reports.revenue-by-package') }}?export=excel&start_date={{ $filters['start_date'] instanceof \Carbon\Carbon ? $filters['start_date']->format('Y-m-d') : $filters['start_date'] }}&end_date={{ $filters['end_date'] instanceof \Carbon\Carbon ? $filters['end_date']->format('Y-m-d') : $filters['end_date'] }}"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm">
                            <i class="fas fa-file-excel mr-1"></i>{{ __('Export Excel') }}
                        </a>
                    </div>
                </form>
            </div>

            <!-- Summary Cards -->
            @if (isset($report['summary']))
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-white shadow-sm sm:rounded-lg p-4 border-l-4 border-green-500">
                        <p class="text-sm text-gray-600">{{ __('Total Pendapatan') }}</p>
                        <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($report['summary']['total_revenue'] ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-white shadow-sm sm:rounded-lg p-4 border-l-4 border-blue-500">
                        <p class="text-sm text-gray-600">{{ __('Total Subscription Aktif') }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($report['summary']['total_subscriptions'] ?? 0) }}</p>
                    </div>
                    <div class="bg-white shadow-sm sm:rounded-lg p-4 border-l-4 border-purple-500">
                        <p class="text-sm text-gray-600">{{ __('Jumlah Paket') }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ count($report['packages'] ?? []) }}</p>
                    </div>
                </div>
            @endif

            <!-- Report Table -->
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('Detail per Paket') }}</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Paket') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Kecepatan') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Harga') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Subscription Aktif') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Total Pendapatan') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('% Kontribusi') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($report['packages'] ?? [] as $pkg)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $pkg['name'] ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $pkg['speed'] ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">Rp {{ number_format($pkg['price'] ?? 0, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ number_format($pkg['active_subscriptions'] ?? 0) }}</td>
                                    <td class="px-6 py-4 text-sm font-semibold text-green-600">Rp {{ number_format($pkg['total_revenue'] ?? 0, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ number_format($pkg['revenue_percentage'] ?? 0, 1) }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">{{ __('Tidak ada data untuk periode ini') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
