@extends('layouts.app')

@section('title', 'Telecom Monitoring Dashboard')

@push('styles')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Telecom Monitoring Dashboard</h1>
                <p class="text-gray-600 mt-1">Real-time network monitoring & analytics</p>
            </div>
            <div class="flex gap-2">
                <button onclick="refreshDashboard()"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Refresh
                </button>
                <span id="lastUpdate" class="text-sm text-gray-500 flex items-center">
                    Last updated: {{ now()->format('H:i:s') }}
                </span>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Devices Stats -->
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Devices</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total_devices'] }}</p>
                        <p class="text-xs text-green-600 mt-1">
                            <span class="font-semibold">{{ $stats['online_devices'] }}</span> online
                        </p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Subscriptions</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['active_subscriptions'] }}</p>
                        <p class="text-xs text-gray-600 mt-1">
                            of {{ $stats['total_subscriptions'] }} total
                        </p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Hotspot Users</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['online_hotspot_users'] }}</p>
                        <p class="text-xs text-gray-600 mt-1">
                            of {{ $stats['total_hotspot_users'] }} online
                        </p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                        </svg>
                    </div>
                </div>
            </div>

            <div
                class="bg-white rounded-lg shadow p-4 border-l-4 {{ $stats['critical_alerts'] > 0 ? 'border-red-500' : 'border-yellow-500' }}">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Alerts</p>
                        <p
                            class="text-2xl font-bold {{ $stats['critical_alerts'] > 0 ? 'text-red-600' : 'text-gray-900' }}">
                            {{ $stats['total_alerts'] }}
                        </p>
                        @if ($stats['critical_alerts'] > 0)
                            <p class="text-xs text-red-600 mt-1 font-semibold">
                                {{ $stats['critical_alerts'] }} critical
                            </p>
                        @else
                            <p class="text-xs text-gray-600 mt-1">No critical alerts</p>
                        @endif
                    </div>
                    <div class="{{ $stats['critical_alerts'] > 0 ? 'bg-red-100' : 'bg-yellow-100' }} p-3 rounded-full">
                        <svg class="w-6 h-6 {{ $stats['critical_alerts'] > 0 ? 'text-red-600' : 'text-yellow-600' }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Summary -->
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow p-6 mb-6 text-white">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-green-100 text-sm">Monthly Revenue (Active Subscriptions)</p>
                    <p class="text-4xl font-bold mt-2">{{ $revenueSummary['formatted_current'] }}</p>
                    @if ($revenueSummary['growth_percent'] != 0)
                        <p
                            class="text-sm mt-2 {{ $revenueSummary['growth_percent'] > 0 ? 'text-green-200' : 'text-red-200' }}">
                            {{ $revenueSummary['growth_percent'] > 0 ? '↑' : '↓' }}
                            {{ abs($revenueSummary['growth_percent']) }}% from last month
                            ({{ $revenueSummary['formatted_last'] }})
                        </p>
                    @endif
                </div>
                <div class="bg-white bg-opacity-20 p-4 rounded-full">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Bandwidth Usage Chart -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Bandwidth Usage (Last 24 Hours)</h3>
                <canvas id="bandwidthChart" height="250"></canvas>
            </div>

            <!-- Device Status Distribution -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Device Status Distribution</h3>
                <canvas id="deviceStatusChart" height="250"></canvas>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Subscription Status -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Subscription Status</h3>
                <canvas id="subscriptionStatusChart" height="250"></canvas>
            </div>

            <!-- Top Devices -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Devices by Active Subscriptions</h3>
                <div class="space-y-3">
                    @forelse($topDevices as $index => $device)
                        <div
                            class="flex items-center justify-between p-3 {{ $index % 2 == 0 ? 'bg-gray-50' : 'bg-white' }} rounded-lg">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-full {{ $device['status'] === 'online' ? 'bg-green-100' : 'bg-red-100' }} flex items-center justify-center">
                                    <svg class="w-5 h-5 {{ $device['status'] === 'online' ? 'text-green-600' : 'text-red-600' }}"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $device['name'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $device['ip_address'] }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-blue-600">{{ $device['active_subscriptions'] }} subs</p>
                                <p class="text-xs text-gray-500">{{ $device['hotspot_users'] }} users</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-gray-500 py-8">No devices found</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Network Topology & Alerts -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Network Topology -->
            <div class="lg:col-span-2 bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Network Topology</h3>
                <div id="topologyContainer" class="border border-gray-200 rounded-lg p-4" style="min-height: 400px;">
                    @if (count($topologyData['nodes']) > 0)
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            @foreach ($topologyData['nodes'] as $node)
                                <div
                                    class="border-2 {{ $node['status'] === 'online' ? 'border-green-500 bg-green-50' : ($node['status'] === 'offline' ? 'border-red-500 bg-red-50' : 'border-yellow-500 bg-yellow-50') }} rounded-lg p-3">
                                    <div class="flex items-center gap-2 mb-2">
                                        <div
                                            class="w-3 h-3 rounded-full {{ $node['status'] === 'online' ? 'bg-green-500' : ($node['status'] === 'offline' ? 'bg-red-500' : 'bg-yellow-500') }}">
                                        </div>
                                        <span class="font-semibold text-sm truncate">{{ $node['label'] }}</span>
                                    </div>
                                    <p class="text-xs text-gray-600">{{ ucfirst($node['type']) }}</p>
                                    <p class="text-xs text-gray-500 font-mono">{{ $node['ip'] }}</p>
                                    @if (isset($node['parent']))
                                        <p class="text-xs text-blue-600 mt-1">↓ Child of {{ $node['parent'] }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex items-center justify-center h-full text-gray-400">
                            <div class="text-center">
                                <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                <p class="mt-2 text-sm">No devices registered yet</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Alerts -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Alerts</h3>
                    @if ($stats['total_alerts'] > 0)
                        <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">{{ $stats['total_alerts'] }}
                            new</span>
                    @endif
                </div>
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse($recentAlerts as $alert)
                        <div
                            class="border-l-4 {{ $alert->severity === 'critical' ? 'border-red-500' : ($alert->severity === 'high' ? 'border-orange-500' : ($alert->severity === 'medium' ? 'border-yellow-500' : 'border-blue-500')) }} pl-3 py-2">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $alert->title }}</p>
                                    <p class="text-xs text-gray-600 mt-1">{{ Str::limit($alert->message, 60) }}</p>
                                    @if ($alert->device)
                                        <p class="text-xs text-blue-600 mt-1">{{ $alert->device->name }}</p>
                                    @endif
                                </div>
                                <span
                                    class="text-xs text-gray-500 whitespace-nowrap ml-2">{{ $alert->triggered_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-400">
                            <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="mt-2 text-sm">No alerts</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Chart.js Configuration
            const bandwidthCtx = document.getElementById('bandwidthChart').getContext('2d');
            const deviceStatusCtx = document.getElementById('deviceStatusChart').getContext('2d');
            const subscriptionStatusCtx = document.getElementById('subscriptionStatusChart').getContext('2d');

            // Bandwidth Usage Chart
            new Chart(bandwidthCtx, {
                type: 'line',
                data: {
                    labels: @json($bandwidthData['labels']),
                    datasets: [{
                            label: 'Download (MB)',
                            data: @json($bandwidthData['downloads']),
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Upload (MB)',
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
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Device Status Chart
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
                            position: 'bottom',
                        }
                    }
                }
            });

            // Subscription Status Chart
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
                            position: 'bottom',
                        }
                    }
                }
            });

            // Auto-refresh function
            function refreshDashboard() {
                // Update timestamp
                const now = new Date();
                document.getElementById('lastUpdate').textContent = 'Last updated: ' + now.toLocaleTimeString();

                // Fetch latest device status
                fetch('{{ route('telecom.dashboard.device-status') }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log('Device status updated:', data.devices.length);
                            // You can update the UI here with new data
                        }
                    })
                    .catch(error => console.error('Error refreshing:', error));
            }

            // Auto-refresh every 30 seconds
            setInterval(refreshDashboard, 30000);
        </script>
    @endpush
@endsection
