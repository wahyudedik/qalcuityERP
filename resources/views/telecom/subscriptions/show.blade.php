<x-app-layout>
    <x-slot name="header">
        {{ __('Detail Subscription') }}
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ __('Detail Subscription') }}</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ $subscription->customer?->name ?? '-' }} — {{ $subscription->package?->name ?? '-' }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('telecom.subscriptions.edit', $subscription) }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-yellow-500 hover:bg-yellow-600 dark:bg-yellow-600 dark:hover:bg-yellow-700">
                        <i class="fas fa-edit mr-2"></i>{{ __('Edit') }}
                    </a>
                    <a href="{{ route('telecom.subscriptions.index') }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <i class="fas fa-arrow-left mr-2"></i>{{ __('Kembali') }}
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-800 text-green-700 dark:text-green-400 px-4 py-3 rounded-lg mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-lg mb-4">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Subscription Info -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Status Card -->
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Informasi Subscription') }}</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">{{ __('Status') }}</p>
                                <span class="mt-1 px-3 py-1 inline-flex text-sm font-semibold rounded-full
                                    {{ $subscription->status === 'active' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400' : ($subscription->status === 'suspended' ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400' : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400') }}">
                                    {{ ucfirst($subscription->status) }}
                                </span>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">{{ __('Pelanggan') }}</p>
                                <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $subscription->customer?->name ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">{{ __('Paket') }}</p>
                                <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $subscription->package?->name ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">{{ __('Perangkat') }}</p>
                                <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $subscription->device?->name ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">{{ __('Tanggal Mulai') }}</p>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                    {{ $subscription->started_at?->format('d M Y') ?? $subscription->activated_at?->format('d M Y') ?? '-' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">{{ __('Tagihan Berikutnya') }}</p>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                    {{ $subscription->next_billing_date?->format('d M Y') ?? '-' }}
                                </p>
                            </div>
                            @if ($subscription->hotspot_username)
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">{{ __('Username Hotspot') }}</p>
                                    <p class="mt-1 text-sm font-mono text-gray-900 dark:text-white">{{ $subscription->hotspot_username }}</p>
                                </div>
                            @endif
                            @if ($subscription->notes)
                                <div class="col-span-2">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">{{ __('Catatan') }}</p>
                                    <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $subscription->notes }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Usage Summary -->
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Ringkasan Penggunaan') }}</h2>
                        @php
                            $quotaBytes = $subscription->package?->quota_bytes ?? 0;
                            $usedBytes = $subscription->current_usage_bytes;
                            $percentage = $quotaBytes > 0 ? min(100, round(($usedBytes / $quotaBytes) * 100, 1)) : 0;
                            $color = $percentage > 90 ? 'red' : ($percentage > 70 ? 'yellow' : 'green');
                        @endphp
                        @if ($quotaBytes > 0)
                            <div class="mb-4">
                                <div class="flex justify-between text-sm mb-2">
                                    <span class="text-gray-600 dark:text-gray-400">{{ __('Terpakai') }}: {{ round($usedBytes / 1073741824, 2) }} GB</span>
                                    <span class="text-gray-600 dark:text-gray-400">{{ __('Total') }}: {{ round($quotaBytes / 1073741824, 2) }} GB</span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4">
                                    <div class="bg-{{ $color }}-500 h-4 rounded-full transition-all" style="width: {{ $percentage }}%"></div>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $percentage }}% {{ __('terpakai') }}</p>
                            </div>
                        @else
                            <p class="text-sm text-blue-600 dark:text-blue-400 font-semibold">∞ {{ __('Kuota Unlimited') }}</p>
                        @endif

                        @if (isset($usageSummary))
                            <div class="grid grid-cols-3 gap-4 mt-4">
                                <div class="text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Download') }}</p>
                                    <p class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                        {{ round(($usageSummary['total_download'] ?? 0) / 1073741824, 2) }} GB
                                    </p>
                                </div>
                                <div class="text-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Upload') }}</p>
                                    <p class="text-lg font-bold text-green-600 dark:text-green-400">
                                        {{ round(($usageSummary['total_upload'] ?? 0) / 1073741824, 2) }} GB
                                    </p>
                                </div>
                                <div class="text-center p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Total') }}</p>
                                    <p class="text-lg font-bold text-purple-600 dark:text-purple-400">
                                        {{ round((($usageSummary['total_download'] ?? 0) + ($usageSummary['total_upload'] ?? 0)) / 1073741824, 2) }} GB
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Actions Panel -->
                <div class="space-y-4">
                    <!-- Quick Actions -->
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Aksi Cepat') }}</h2>
                        <div class="space-y-3">
                            @if ($subscription->status === 'active')
                                <form action="{{ route('telecom.subscriptions.suspend', $subscription) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="w-full bg-yellow-500 hover:bg-yellow-600 dark:bg-yellow-600 dark:hover:bg-yellow-700 text-white px-4 py-2 rounded-lg text-sm font-medium"
                                        onclick="return confirm('{{ __('Suspend subscription ini?') }}')">
                                        <i class="fas fa-pause mr-2"></i>{{ __('Suspend Subscription') }}
                                    </button>
                                </form>
                            @elseif($subscription->status === 'suspended')
                                <form action="{{ route('telecom.subscriptions.reactivate', $subscription) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="w-full bg-green-500 hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                                        <i class="fas fa-play mr-2"></i>{{ __('Aktifkan Kembali') }}
                                    </button>
                                </form>
                            @endif

                            <form action="{{ route('telecom.subscriptions.reset-quota', $subscription) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="w-full bg-purple-500 hover:bg-purple-600 dark:bg-purple-600 dark:hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium"
                                    onclick="return confirm('{{ __('Reset kuota subscription ini?') }}')">
                                    <i class="fas fa-redo mr-2"></i>{{ __('Reset Kuota') }}
                                </button>
                            </form>

                            <a href="{{ route('telecom.subscriptions.edit', $subscription) }}"
                                class="block w-full text-center bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                                <i class="fas fa-edit mr-2"></i>{{ __('Edit Subscription') }}
                            </a>
                        </div>
                    </div>

                    <!-- Package Info -->
                    @if ($subscription->package)
                        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Info Paket') }}</h2>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">{{ __('Download') }}</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $subscription->package->download_speed_mbps }} Mbps</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">{{ __('Upload') }}</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $subscription->package->upload_speed_mbps }} Mbps</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">{{ __('Harga') }}</span>
                                    <span class="font-medium text-gray-900 dark:text-white">Rp {{ number_format($subscription->package->price, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
