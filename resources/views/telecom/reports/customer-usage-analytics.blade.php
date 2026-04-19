<x-app-layout>
    <x-slot name="header">
        {{ __('Analitik Penggunaan Pelanggan') }}
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ __('Analitik Penggunaan Pelanggan') }}</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Analisis mendalam perilaku pelanggan dan pola subscription') }}</p>
                </div>
                <a href="{{ route('telecom.reports.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                    <i class="fas fa-arrow-left mr-2"></i>{{ __('Kembali') }}
                </a>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 mb-6">
                <form method="GET" action="{{ route('telecom.reports.customer-usage-analytics') }}" class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Dari Tanggal') }}</label>
                        <input type="date" name="start_date" value="{{ $filters['start_date'] instanceof \Carbon\Carbon ? $filters['start_date']->format('Y-m-d') : $filters['start_date'] }}"
                            class="rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Sampai Tanggal') }}</label>
                        <input type="date" name="end_date" value="{{ $filters['end_date'] instanceof \Carbon\Carbon ? $filters['end_date']->format('Y-m-d') : $filters['end_date'] }}"
                            class="rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Urutkan') }}</label>
                        <select name="sort_by" class="rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="usage" {{ ($filters['sort_by'] ?? 'usage') === 'usage' ? 'selected' : '' }}>{{ __('Penggunaan') }}</option>
                            <option value="name" {{ ($filters['sort_by'] ?? '') === 'name' ? 'selected' : '' }}>{{ __('Nama') }}</option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm">
                            <i class="fas fa-filter mr-1"></i>{{ __('Filter') }}
                        </button>
                        <a href="{{ route('telecom.reports.customer-usage-analytics') }}?export=excel&start_date={{ $filters['start_date'] instanceof \Carbon\Carbon ? $filters['start_date']->format('Y-m-d') : $filters['start_date'] }}&end_date={{ $filters['end_date'] instanceof \Carbon\Carbon ? $filters['end_date']->format('Y-m-d') : $filters['end_date'] }}"
                            class="bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600 text-white px-4 py-2 rounded-md text-sm">
                            <i class="fas fa-file-excel mr-1"></i>{{ __('Export Excel') }}
                        </a>
                    </div>
                </form>
            </div>

            <!-- Data Table -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Penggunaan per Pelanggan') }}</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Pelanggan') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Paket') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Download') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Upload') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Total') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($report['customers'] ?? [] as $cust)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $cust['customer_name'] ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $cust['package_name'] ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-blue-600 dark:text-blue-400">{{ number_format(($cust['total_download'] ?? 0) / 1073741824, 2) }} GB</td>
                                    <td class="px-6 py-4 text-sm text-green-600 dark:text-green-400">{{ number_format(($cust['total_upload'] ?? 0) / 1073741824, 2) }} GB</td>
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white">{{ number_format((($cust['total_download'] ?? 0) + ($cust['total_upload'] ?? 0)) / 1073741824, 2) }} GB</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full
                                            {{ ($cust['status'] ?? '') === 'active' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-400' }}">
                                            {{ ucfirst($cust['status'] ?? '-') }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">{{ __('Tidak ada data untuk periode ini') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
