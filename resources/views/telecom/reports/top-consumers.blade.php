<x-app-layout>
    <x-slot name="header">
        {{ __('Laporan Konsumen Teratas') }}
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ __('Konsumen Teratas') }}</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Identifikasi konsumen bandwidth tertinggi') }}</p>
                </div>
                <a href="{{ route('telecom.reports.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                    <i class="fas fa-arrow-left mr-2"></i>{{ __('Kembali') }}
                </a>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 mb-6">
                <form method="GET" action="{{ route('telecom.reports.top-consumers') }}" class="flex flex-wrap gap-4 items-end">
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
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Metrik') }}</label>
                        <select name="metric" class="rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="usage" {{ ($filters['metric'] ?? 'usage') === 'usage' ? 'selected' : '' }}>{{ __('Total Penggunaan') }}</option>
                            <option value="download" {{ ($filters['metric'] ?? '') === 'download' ? 'selected' : '' }}>{{ __('Download') }}</option>
                            <option value="upload" {{ ($filters['metric'] ?? '') === 'upload' ? 'selected' : '' }}>{{ __('Upload') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Jumlah') }}</label>
                        <select name="limit" class="rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="10" {{ ($filters['limit'] ?? 20) == 10 ? 'selected' : '' }}>Top 10</option>
                            <option value="20" {{ ($filters['limit'] ?? 20) == 20 ? 'selected' : '' }}>Top 20</option>
                            <option value="50" {{ ($filters['limit'] ?? 20) == 50 ? 'selected' : '' }}>Top 50</option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm">
                            <i class="fas fa-filter mr-1"></i>{{ __('Filter') }}
                        </button>
                        <a href="{{ route('telecom.reports.top-consumers') }}?export=excel&start_date={{ $filters['start_date'] instanceof \Carbon\Carbon ? $filters['start_date']->format('Y-m-d') : $filters['start_date'] }}&end_date={{ $filters['end_date'] instanceof \Carbon\Carbon ? $filters['end_date']->format('Y-m-d') : $filters['end_date'] }}"
                            class="bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600 text-white px-4 py-2 rounded-md text-sm">
                            <i class="fas fa-file-excel mr-1"></i>{{ __('Export Excel') }}
                        </a>
                    </div>
                </form>
            </div>

            <!-- Data Table -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Peringkat Konsumen Bandwidth') }}</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">#</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Pelanggan') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Paket') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Download') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Upload') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($report['consumers'] ?? [] as $index => $consumer)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 text-sm font-bold text-gray-500 dark:text-gray-400">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $consumer['customer_name'] ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $consumer['package_name'] ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-blue-600 dark:text-blue-400">{{ number_format(($consumer['total_download'] ?? 0) / 1073741824, 2) }} GB</td>
                                    <td class="px-6 py-4 text-sm text-green-600 dark:text-green-400">{{ number_format(($consumer['total_upload'] ?? 0) / 1073741824, 2) }} GB</td>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-white">{{ number_format((($consumer['total_download'] ?? 0) + ($consumer['total_upload'] ?? 0)) / 1073741824, 2) }} GB</td>
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
