<x-app-layout>
    <x-slot name="header">
        {{ __('Statistik Voucher') }}
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ __('Statistik Voucher') }}</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Analisis penggunaan dan performa voucher') }}</p>
                </div>
                <a href="{{ route('telecom.vouchers.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                    <i class="fas fa-arrow-left mr-2"></i>{{ __('Kembali ke Daftar') }}
                </a>
            </div>

            <!-- Stats Overview -->
            @if (isset($stats))
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-blue-500">
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Total Voucher') }}</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total'] ?? 0) }}</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-green-500">
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Belum Digunakan') }}</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($stats['unused'] ?? 0) }}</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-purple-500">
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Sudah Digunakan') }}</p>
                        <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ number_format($stats['used'] ?? 0) }}</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-red-500">
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Kadaluarsa') }}</p>
                        <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format($stats['expired'] ?? 0) }}</p>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Top Packages by Voucher Usage -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Paket Terpopuler (Berdasarkan Voucher)') }}</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Paket') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Penggunaan') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Pendapatan') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($topPackages ?? [] as $pkg)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $pkg->package_name }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ number_format($pkg->usage_count) }}</td>
                                        <td class="px-6 py-4 text-sm text-green-600 dark:text-green-400">
                                            Rp {{ number_format($pkg->total_revenue ?? 0, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                            {{ __('Belum ada data penggunaan voucher') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Aktivitas Terbaru') }}</h2>
                    </div>
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($recentActivity ?? [] as $voucher)
                            <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700">
                                <div>
                                    <p class="text-sm font-mono font-bold text-gray-900 dark:text-white">{{ $voucher->code }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $voucher->package?->name ?? '-' }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full
                                        {{ $voucher->status === 'unused' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400' : ($voucher->status === 'used' ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400' : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400') }}">
                                        {{ ucfirst($voucher->status) }}
                                    </span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $voucher->updated_at?->diffForHumans() }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                {{ __('Belum ada aktivitas voucher') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
