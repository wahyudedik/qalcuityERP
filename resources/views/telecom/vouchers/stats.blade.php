<x-app-layout>
    <x-slot name="header">
        {{ __('Statistik Voucher') }}
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('Statistik Voucher') }}</h1>
                    <p class="mt-1 text-sm text-gray-600">{{ __('Analisis penggunaan dan performa voucher') }}</p>
                </div>
                <a href="{{ route('telecom.vouchers.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-arrow-left mr-2"></i>{{ __('Kembali ke Daftar') }}
                </a>
            </div>

            <!-- Stats Overview -->
            @if (isset($stats))
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-blue-500">
                        <p class="text-sm text-gray-600">{{ __('Total Voucher') }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total'] ?? 0) }}</p>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-green-500">
                        <p class="text-sm text-gray-600">{{ __('Belum Digunakan') }}</p>
                        <p class="text-2xl font-bold text-green-600">{{ number_format($stats['unused'] ?? 0) }}</p>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-purple-500">
                        <p class="text-sm text-gray-600">{{ __('Sudah Digunakan') }}</p>
                        <p class="text-2xl font-bold text-purple-600">{{ number_format($stats['used'] ?? 0) }}</p>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-red-500">
                        <p class="text-sm text-gray-600">{{ __('Kadaluarsa') }}</p>
                        <p class="text-2xl font-bold text-red-600">{{ number_format($stats['expired'] ?? 0) }}</p>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Top Packages by Voucher Usage -->
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">{{ __('Paket Terpopuler (Berdasarkan Voucher)') }}</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Paket') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Penggunaan') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Pendapatan') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($topPackages ?? [] as $pkg)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $pkg->package_name }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ number_format($pkg->usage_count) }}</td>
                                        <td class="px-6 py-4 text-sm text-green-600">
                                            Rp {{ number_format($pkg->total_revenue ?? 0, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                                            {{ __('Belum ada data penggunaan voucher') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">{{ __('Aktivitas Terbaru') }}</h2>
                    </div>
                    <div class="divide-y divide-gray-200">
                        @forelse($recentActivity ?? [] as $voucher)
                            <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50">
                                <div>
                                    <p class="text-sm font-mono font-bold text-gray-900">{{ $voucher->code }}</p>
                                    <p class="text-xs text-gray-500">{{ $voucher->package?->name ?? '-' }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full
                                        {{ $voucher->status === 'unused' ? 'bg-green-100 text-green-800' : ($voucher->status === 'used' ? 'bg-purple-100 text-purple-800' : 'bg-red-100 text-red-800') }}">
                                        {{ ucfirst($voucher->status) }}
                                    </span>
                                    <p class="text-xs text-gray-500 mt-1">{{ $voucher->updated_at?->diffForHumans() }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="px-6 py-8 text-center text-gray-500">
                                {{ __('Belum ada aktivitas voucher') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
