<x-app-layout>
    <x-slot name="header">
        {{ __('Detail Paket Internet') }}
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $package->name }}</h1>
                    <p class="mt-1 text-sm text-gray-600">{{ __('Detail paket internet') }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('telecom.packages.edit', $package) }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-yellow-500 hover:bg-yellow-600">
                        <i class="fas fa-edit mr-2"></i>{{ __('Edit') }}
                    </a>
                    <a href="{{ route('telecom.packages.index') }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <i class="fas fa-arrow-left mr-2"></i>{{ __('Kembali') }}
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Package Info Card -->
                <div class="lg:col-span-1">
                    <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 text-white">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-xl font-bold">{{ $package->name }}</h3>
                                    <p class="text-blue-100 text-sm mt-1">{{ ucfirst($package->billing_cycle ?? 'monthly') }}</p>
                                </div>
                                @if ($package->is_active)
                                    <span class="px-2 py-1 text-xs bg-green-500 rounded-full">{{ __('Aktif') }}</span>
                                @else
                                    <span class="px-2 py-1 text-xs bg-gray-500 rounded-full">{{ __('Nonaktif') }}</span>
                                @endif
                            </div>
                            <div class="mt-4">
                                <p class="text-3xl font-bold">Rp {{ number_format($package->price, 0, ',', '.') }}</p>
                                <p class="text-blue-100 text-sm">/{{ __('bulan') }}</p>
                            </div>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="flex justify-between">
                                <span class="text-gray-600 text-sm">{{ __('Download') }}</span>
                                <span class="font-bold text-gray-900">{{ $package->download_speed_mbps }} Mbps</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 text-sm">{{ __('Upload') }}</span>
                                <span class="font-bold text-gray-900">{{ $package->upload_speed_mbps }} Mbps</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 text-sm">{{ __('Kuota') }}</span>
                                <span class="font-bold text-gray-900">
                                    @if ($package->isUnlimited())
                                        <span class="text-blue-600">∞ {{ __('Unlimited') }}</span>
                                    @else
                                        {{ number_format($package->quota_bytes / 1073741824, 0) }} GB
                                    @endif
                                </span>
                            </div>
                            @if ($package->installation_fee > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-600 text-sm">{{ __('Biaya Pasang') }}</span>
                                    <span class="font-bold text-gray-900">Rp {{ number_format($package->installation_fee, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            @if ($package->description)
                                <div class="pt-4 border-t border-gray-200">
                                    <p class="text-sm text-gray-600">{{ $package->description }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Subscriptions List -->
                <div class="lg:col-span-2">
                    <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                            <h2 class="text-lg font-semibold text-gray-900">
                                {{ __('Subscription Aktif') }}
                                <span class="ml-2 px-2 py-0.5 text-xs bg-blue-100 text-blue-800 rounded-full">
                                    {{ $package->subscriptions->count() }}
                                </span>
                            </h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Pelanggan') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Mulai') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Aksi') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($package->subscriptions as $sub)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $sub->customer?->name ?? '-' }}</div>
                                                <div class="text-xs text-gray-500">{{ $sub->customer?->email ?? '' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    {{ $sub->status === 'active' ? 'bg-green-100 text-green-800' : ($sub->status === 'suspended' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                    {{ ucfirst($sub->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $sub->started_at?->format('d M Y') ?? $sub->activated_at?->format('d M Y') ?? '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <a href="{{ route('telecom.subscriptions.show', $sub) }}"
                                                    class="text-indigo-600 hover:text-indigo-900">
                                                    {{ __('Lihat') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                                {{ __('Belum ada subscription untuk paket ini') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
