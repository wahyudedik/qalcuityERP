<x-app-layout>
    <x-slot name="header">
        {{ __('Detail Penggunaan Pelanggan') }}
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $customer->name }}</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ $customer->email ?? __('Tidak ada email') }} •
                        {{ $customer->phone ?? __('Tidak ada telepon') }}
                    </p>
                </div>
                <a href="{{ route('telecom.customers.usage') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-arrow-left mr-2"></i>{{ __('Kembali') }}
                </a>
            </div>

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left: Subscription & Usage -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Subscription Info -->
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Subscription Aktif') }}</h2>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-xs text-gray-500 uppercase">{{ __('Paket') }}</p>
                                <p class="mt-1 font-medium text-gray-900">{{ $subscription->package?->name ?? '-' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">{{ __('Status') }}</p>
                                <span
                                    class="mt-1 px-2 py-0.5 inline-flex text-xs font-semibold rounded-full
                                    {{ $subscription->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ ucfirst($subscription->status) }}
                                </span>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">{{ __('Kecepatan') }}</p>
                                <p class="mt-1 font-medium text-gray-900">
                                    {{ $subscription->package?->download_speed_mbps ?? 0 }}/{{ $subscription->package?->upload_speed_mbps ?? 0 }}
                                    Mbps
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">{{ __('Perangkat') }}</p>
                                <p class="mt-1 font-medium text-gray-900">{{ $subscription->device?->name ?? '-' }}</p>
                            </div>
                            @if ($subscription->hotspot_username)
                                <div>
                                    <p class="text-xs text-gray-500 uppercase">{{ __('Username') }}</p>
                                    <p class="mt-1 font-mono text-gray-900">{{ $subscription->hotspot_username }}</p>
                                </div>
                            @endif
                            <div>
                                <p class="text-xs text-gray-500 uppercase">{{ __('Tagihan Berikutnya') }}</p>
                                <p class="mt-1 font-medium text-gray-900">
                                    {{ $subscription->next_billing_date?->format('d M Y') ?? '-' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Usage Summary -->
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">{{ __('Ringkasan Penggunaan') }}</h2>
                            <div class="flex gap-2">
                                @foreach (['daily' => __('Harian'), 'weekly' => __('Mingguan'), 'monthly' => __('Bulanan')] as $p => $label)
                                    <a href="{{ route('telecom.customers.show-usage', $customer) }}?period={{ $p }}"
                                        class="px-3 py-1 text-xs rounded-full {{ $period === $p ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                        {{ $label }}
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        @php
                            $quotaBytes = $subscription->package?->quota_bytes ?? 0;
                            $usedBytes = $subscription->current_usage_bytes;
                            $percentage = $quotaBytes > 0 ? min(100, round(($usedBytes / $quotaBytes) * 100, 1)) : 0;
                            $color = $percentage > 90 ? 'red' : ($percentage > 70 ? 'yellow' : 'green');
                        @endphp

                        @if ($quotaBytes > 0)
                            <div class="mb-6">
                                <div class="flex justify-between text-sm mb-2">
                                    <span class="text-gray-600">{{ round($usedBytes / 1073741824, 2) }} GB
                                        {{ __('terpakai') }}</span>
                                    <span class="text-gray-600">{{ round($quotaBytes / 1073741824, 2) }} GB
                                        {{ __('total') }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-4">
                                    <div class="bg-{{ $color }}-500 h-4 rounded-full"
                                        style="width: {{ $percentage }}%"></div>
                                </div>
                                <p class="text-sm text-gray-500 mt-1">{{ $percentage }}% {{ __('terpakai') }}</p>
                            </div>
                        @else
                            <p class="text-sm text-blue-600 font-semibold mb-4">∞ {{ __('Kuota Unlimited') }}</p>
                        @endif

                        @if (isset($usageSummary))
                            <div class="grid grid-cols-3 gap-4">
                                <div class="text-center p-4 bg-blue-50 rounded-lg">
                                    <p class="text-xs text-gray-500 mb-1">{{ __('Download') }}</p>
                                    <p class="text-xl font-bold text-blue-600">
                                        {{ round(($usageSummary['total_download'] ?? 0) / 1073741824, 2) }} GB
                                    </p>
                                </div>
                                <div class="text-center p-4 bg-green-50 rounded-lg">
                                    <p class="text-xs text-gray-500 mb-1">{{ __('Upload') }}</p>
                                    <p class="text-xl font-bold text-green-600">
                                        {{ round(($usageSummary['total_upload'] ?? 0) / 1073741824, 2) }} GB
                                    </p>
                                </div>
                                <div class="text-center p-4 bg-purple-50 rounded-lg">
                                    <p class="text-xs text-gray-500 mb-1">{{ __('Total') }}</p>
                                    <p class="text-xl font-bold text-purple-600">
                                        {{ round((($usageSummary['total_download'] ?? 0) + ($usageSummary['total_upload'] ?? 0)) / 1073741824, 2) }}
                                        GB
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Usage Chart -->
                    @if (isset($chartData) && count($chartData['labels']) > 0)
                        <div class="bg-white shadow-sm sm:rounded-lg p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                                {{ __('Tren Penggunaan (7 Hari Terakhir)') }}</h2>
                            <canvas id="usageChart" height="200"></canvas>
                        </div>
                    @endif

                    <!-- Usage History -->
                    @if (isset($usageHistory) && $usageHistory->count() > 0)
                        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-900">{{ __('Riwayat Penggunaan') }}</h2>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                {{ __('Periode') }}</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                {{ __('Download') }}</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                {{ __('Upload') }}</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                {{ __('Total') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($usageHistory as $record)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-3 text-sm text-gray-900">
                                                    {{ $record->period_start?->format('d M Y') ?? '-' }}
                                                </td>
                                                <td class="px-6 py-3 text-sm text-blue-600">
                                                    {{ round(($record->bytes_in ?? 0) / 1048576, 2) }} MB
                                                </td>
                                                <td class="px-6 py-3 text-sm text-green-600">
                                                    {{ round(($record->bytes_out ?? 0) / 1048576, 2) }} MB
                                                </td>
                                                <td class="px-6 py-3 text-sm text-gray-900">
                                                    {{ round((($record->bytes_in ?? 0) + ($record->bytes_out ?? 0)) / 1048576, 2) }}
                                                    MB
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Right: Actions -->
                <div class="space-y-4">
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Aksi') }}</h2>
                        <div class="space-y-3">
                            @if ($subscription->status === 'active')
                                <form action="{{ route('telecom.customers.suspend', $customer) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="w-full bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm font-medium"
                                        data-confirm="{{ __('Suspend subscription pelanggan ini?') }}">
                                        <i class="fas fa-pause mr-2"></i>{{ __('Suspend') }}
                                    </button>
                                </form>
                            @elseif($subscription->status === 'suspended')
                                <form action="{{ route('telecom.customers.reactivate', $customer) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="w-full bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
                                        <i class="fas fa-play mr-2"></i>{{ __('Aktifkan Kembali') }}
                                    </button>
                                </form>
                            @endif

                            <form action="{{ route('telecom.customers.reset-quota', $customer) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="w-full bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg text-sm font-medium"
                                    data-confirm="{{ __('Reset kuota pelanggan ini?') }}">
                                    <i class="fas fa-redo mr-2"></i>{{ __('Reset Kuota') }}
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Customer Info -->
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Info Pelanggan') }}</h2>
                        <div class="space-y-3 text-sm">
                            <div>
                                <p class="text-xs text-gray-500 uppercase">{{ __('Nama') }}</p>
                                <p class="mt-1 font-medium text-gray-900">{{ $customer->name }}</p>
                            </div>
                            @if ($customer->email)
                                <div>
                                    <p class="text-xs text-gray-500 uppercase">{{ __('Email') }}</p>
                                    <p class="mt-1 text-gray-900">{{ $customer->email }}</p>
                                </div>
                            @endif
                            @if ($customer->phone)
                                <div>
                                    <p class="text-xs text-gray-500 uppercase">{{ __('Telepon') }}</p>
                                    <p class="mt-1 text-gray-900">{{ $customer->phone }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (isset($chartData) && count($chartData['labels']) > 0)
        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                const ctx = document.getElementById('usageChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: @json($chartData['labels']),
                        datasets: [{
                            label: '{{ __('Download (MB)') }}',
                            data: @json($chartData['downloads']),
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true
                        }, {
                            label: '{{ __('Upload (MB)') }}',
                            data: @json($chartData['uploads']),
                            borderColor: 'rgb(16, 185, 129)',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            </script>
        @endpush
    @endif
</x-app-layout>
