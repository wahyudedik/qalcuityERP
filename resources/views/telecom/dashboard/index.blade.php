<x-app-layout>
    <x-slot name="header">
        {{ __('Telecom Monitoring Dashboard') }}
    </x-slot>

    @push('styles')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endpush

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('Telecom Monitoring Dashboard') }}
                    </h1>
                    <p class="text-gray-600 mt-1">{{ __('Real-time network monitoring & analytics') }}
                    </p>
                </div>
                <div class="flex gap-2 items-center">
                    <a href="{{ route('telecom.maps') }}"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                        <i class="fas fa-map"></i>
                        {{ __('View Maps') }}
                    </a>
                    <button onclick="refreshDashboard()"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                        <i class="fas fa-sync-alt"></i>
                        {{ __('Refresh') }}
                    </button>
                    <span id="lastUpdate" class="text-sm text-gray-500 flex items-center">
                        {{ __('Last updated') }}: {{ now()->format('H:i:s') }}
                    </span>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <!-- Devices Stats -->
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Total Devices') }}</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['total_devices'] }}
                            </p>
                            <p class="text-xs text-green-600 mt-1">
                                <span class="font-semibold">{{ $stats['online_devices'] }}</span> {{ __('online') }}
                            </p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <i class="fas fa-server text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Subscriptions') }}</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ $stats['active_subscriptions'] }}</p>
                            <p class="text-xs text-gray-600 mt-1">
                                {{ __('of') }} {{ $stats['total_subscriptions'] }} {{ __('total') }}
                            </p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full">
                            <i class="fas fa-users text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 border-purple-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Hotspot Users') }}</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ $stats['online_hotspot_users'] }}</p>
                            <p class="text-xs text-gray-600 mt-1">
                                {{ __('of') }} {{ $stats['total_hotspot_users'] }} {{ __('online') }}
                            </p>
                        </div>
                        <div class="bg-purple-100 p-3 rounded-full">
                            <i class="fas fa-wifi text-purple-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border-l-4 {{ $stats['critical_alerts'] > 0 ? 'border-red-500' : 'border-yellow-500' }}">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">{{ __('Alerts') }}</p>
                            <p
                                class="text-2xl font-bold {{ $stats['critical_alerts'] > 0 ? 'text-red-600' : 'text-gray-900' }}">
                                {{ $stats['total_alerts'] }}
                            </p>
                            @if ($stats['critical_alerts'] > 0)
                                <p class="text-xs text-red-600 mt-1 font-semibold">
                                    {{ $stats['critical_alerts'] }} {{ __('critical') }}
                                </p>
                            @else
                                <p class="text-xs text-gray-600 mt-1">{{ __('No critical alerts') }}
                                </p>
                            @endif
                        </div>
                        <div
                            class="{{ $stats['critical_alerts'] > 0 ? 'bg-red-100' : 'bg-yellow-100' }} p-3 rounded-full">
                            <i
                                class="fas fa-bell {{ $stats['critical_alerts'] > 0 ? 'text-red-600' : 'text-yellow-600' }} text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue Summary -->
            <div
                class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-sm p-6 mb-6 text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-green-100 text-sm">
                            {{ __('Monthly Revenue (Active Subscriptions)') }}</p>
                        <p class="text-4xl font-bold mt-2">{{ $revenueSummary['formatted_current'] }}</p>
                        @if ($revenueSummary['growth_percent'] != 0)
                            <p
                                class="text-sm mt-2 {{ $revenueSummary['growth_percent'] > 0 ? 'text-green-200' : 'text-red-200' }}">
                                {{ $revenueSummary['growth_percent'] > 0 ? '↑' : '↓' }}
                                {{ abs($revenueSummary['growth_percent']) }}% {{ __('from last month') }}
                                ({{ $revenueSummary['formatted_last'] }})
                            </p>
                        @endif
                    </div>
                    <div class="bg-white bg-opacity-20 p-4 rounded-full">
                        <i class="fas fa-dollar-sign text-4xl"></i>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Bandwidth Usage Chart -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        {{ __('Bandwidth Usage (Last 24 Hours)') }}</h3>
                    <canvas id="bandwidthChart" height="250"></canvas>
                </div>

                <!-- Device Status Distribution -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        {{ __('Device Status Distribution') }}</h3>
                    <canvas id="deviceStatusChart" height="250"></canvas>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Subscription Status -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        {{ __('Subscription Status') }}</h3>
                    <canvas id="subscriptionStatusChart" height="250"></canvas>
                </div>

                <!-- Top Devices -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        {{ __('Top Devices by Active Subscriptions') }}</h3>
                    <div class="space-y-3">
                        @forelse($topDevices as $index => $device)
                            <div
                                class="flex items-center justify-between p-3 {{ $index % 2 == 0 ? 'bg-gray-50' : 'bg-white' }} rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-full {{ $device['status'] === 'online' ? 'bg-green-100' : 'bg-red-100' }} flex items-center justify-center">
                                        <i
                                            class="fas fa-server {{ $device['status'] === 'online' ? 'text-green-600' : 'text-red-600' }}"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $device['name'] }}
                                        </p>
                                        <p class="text-xs text-gray-500">{{ $device['ip_address'] }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-blue-600">
                                        {{ $device['active_subscriptions'] }} {{ __('subs') }}</p>
                                    <p class="text-xs text-gray-500">{{ $device['hotspot_users'] }}
                                        {{ __('users') }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-gray-500 py-8">{{ __('No devices found') }}
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Network Topology & Alerts -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Network Topology -->
                <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('Network Topology') }}
                        </h3>
                        <a href="{{ route('telecom.maps') }}"
                            class="text-sm text-green-600 hover:text-green-800 flex items-center gap-1">
                            <i class="fas fa-map"></i>
                            {{ __('View on Map') }}
                        </a>
                    </div>
                    <div id="topologyContainer" class="border border-gray-200 rounded-lg p-4"
                        style="min-height: 400px;">
                        @if (count($topologyData['nodes']) > 0)
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                @foreach ($topologyData['nodes'] as $node)
                                    <div
                                        class="border-2 {{ $node['status'] === 'online' ? 'border-green-500 bg-green-50' : ($node['status'] === 'offline' ? 'border-red-500 bg-red-50' : 'border-yellow-500 bg-yellow-50') }} rounded-lg p-3">
                                        <div class="flex items-center gap-2 mb-2">
                                            <div
                                                class="w-3 h-3 rounded-full {{ $node['status'] === 'online' ? 'bg-green-500' : ($node['status'] === 'offline' ? 'bg-red-500' : 'bg-yellow-500') }}">
                                            </div>
                                            <span
                                                class="font-semibold text-sm truncate text-gray-900">{{ $node['label'] }}</span>
                                            @if ($node['has_coordinates'] ?? false)
                                                <i
                                                    class="fas fa-map-marker-alt text-green-600 flex-shrink-0"></i>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-600">
                                            {{ ucfirst($node['type']) }}</p>
                                        <p class="text-xs text-gray-500 font-mono">
                                            {{ $node['ip'] }}</p>
                                        @if (isset($node['location']) && $node['location'])
                                            <p class="text-xs text-gray-600 mt-1 truncate"
                                                title="{{ $node['location'] }}">
                                                <i class="fas fa-location-dot inline"></i>
                                                {{ Str::limit($node['location'], 20) }}
                                            </p>
                                        @endif
                                        @if (isset($node['parent']))
                                            <p class="text-xs text-blue-600 mt-1">↓
                                                {{ __('Child of') }} {{ $node['parent'] }}</p>
                                        @endif

                                        @if ($node['has_coordinates'] ?? false)
                                            <a href="{{ route('telecom.maps') }}?device_id={{ $node['id'] }}"
                                                class="mt-2 pt-2 border-t border-gray-200 text-xs text-green-600 hover:text-green-800 flex items-center gap-1 transition-colors">
                                                <i class="fas fa-map flex-shrink-0"></i>
                                                <span class="truncate">{{ __('View on Map') }}</span>
                                            </a>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="flex items-center justify-center h-full text-gray-400">
                                <div class="text-center">
                                    <i class="fas fa-network-wired text-4xl mb-2"></i>
                                    <p class="text-sm">{{ __('No devices registered yet') }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Recent Alerts -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('Recent Alerts') }}</h3>
                        @if ($stats['total_alerts'] > 0)
                            <span
                                class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">{{ $stats['total_alerts'] }}
                                {{ __('new') }}</span>
                        @endif
                    </div>
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @forelse($recentAlerts as $alert)
                            <div
                                class="border-l-4 {{ $alert->severity === 'critical' ? 'border-red-500' : ($alert->severity === 'high' ? 'border-orange-500' : ($alert->severity === 'medium' ? 'border-yellow-500' : 'border-blue-500')) }} pl-3 py-2">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">
                                            {{ $alert->title }}</p>
                                        <p class="text-xs text-gray-600 mt-1">
                                            {{ Str::limit($alert->message, 60) }}</p>
                                        @if ($alert->device)
                                            <p class="text-xs text-blue-600 mt-1">
                                                {{ $alert->device?->name }}</p>
                                        @endif
                                    </div>
                                    <span
                                        class="text-xs text-gray-500 whitespace-nowrap ml-2">{{ $alert->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-400">
                                <i class="fas fa-check-circle text-4xl mb-2"></i>
                                <p class="text-sm">{{ __('No alerts') }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            const bandwidthCtx = document.getElementById('bandwidthChart').getContext('2d');
            const deviceStatusCtx = document.getElementById('deviceStatusChart').getContext('2d');
            const subscriptionStatusCtx = document.getElementById('subscriptionStatusChart').getContext('2d');

            new Chart(bandwidthCtx, {
                type: 'line',
                data: {
                    labels: @json($bandwidthData['labels']),
                    datasets: [{
                            label: '{{ __('Download (MB)') }}',
                            data: @json($bandwidthData['downloads']),
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: '{{ __('Upload (MB)') }}',
                            data: @json($bandwidthData['uploads']),
                            borderColor: 'rgb(16, 185, 129)',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            tension: 0.4,
                            fill: true
                        }
                    ]
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

            new Chart(deviceStatusCtx, {
                type: 'doughnut',
                data: {
                    labels: @json($deviceStatusData['labels']),
                    datasets: [{
                        data: @json($deviceStatusData['data']),
                        backgroundColor: [
                            'rgb(34, 197, 94)',
                            'rgb(239, 68, 68)',
                            'rgb(234, 179, 8)',
                            'rgb(156, 163, 175)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            new Chart(subscriptionStatusCtx, {
                type: 'pie',
                data: {
                    labels: @json($subscriptionStatusData['labels']),
                    datasets: [{
                        data: @json($subscriptionStatusData['data']),
                        backgroundColor: [
                            'rgb(34, 197, 94)',
                            'rgb(234, 179, 8)',
                            'rgb(156, 163, 175)',
                            'rgb(239, 68, 68)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            function refreshDashboard() {
                const now = new Date();
                document.getElementById('lastUpdate').textContent = '{{ __('Last updated') }}: ' + now.toLocaleTimeString();

                fetch('{{ route('telecom.dashboard.device-status') }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log('Device status updated:', data.devices.length);
                        }
                    })
                    .catch(error => console.error('Error refreshing:', error));
            }

            setInterval(refreshDashboard, 30000);
        </script>
    @endpush
</x-app-layout>
