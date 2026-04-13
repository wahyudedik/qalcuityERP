<x-app-layout>
    <x-slot name="header">
        {{ $device->name }}
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <div class="mb-4">
                <a href="{{ route('telecom.devices.index') }}"
                    class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i>
                    {{ __('Kembali ke Devices') }}
                </a>
            </div>

            <!-- Header with Actions -->
            <div class="flex justify-between items-start mb-6">
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $device->name }}</h1>
                        @if ($device->status === 'online')
                            <span
                                class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">{{ __('Online') }}</span>
                        @elseif($device->status === 'offline')
                            <span
                                class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400">{{ __('Offline') }}</span>
                        @elseif($device->status === 'maintenance')
                            <span
                                class="px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400">{{ __('Maintenance') }}</span>
                        @else
                            <span
                                class="px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-400">{{ __('Pending') }}</span>
                        @endif
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">{{ ucfirst($device->device_type) }} •
                        {{ ucfirst($device->brand) }} {{ $device->model }}</p>
                </div>

                <div class="flex gap-2">
                    <form action="{{ route('telecom.devices.test-connection', $device) }}" method="POST"
                        id="testConnectionForm">
                        @csrf
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                            <i class="fas fa-check-circle"></i>
                            {{ __('Test Koneksi') }}
                        </button>
                    </form>

                    <form action="{{ route('telecom.devices.toggle-maintenance', $device) }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="bg-yellow-600 hover:bg-yellow-700 dark:bg-yellow-700 dark:hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                            <i class="fas fa-wrench"></i>
                            {{ $device->status === 'maintenance' ? __('Exit Maintenance') : __('Maintenance Mode') }}
                        </button>
                    </form>

                    <a href="{{ route('telecom.devices.edit', $device) }}"
                        class="bg-gray-600 hover:bg-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                        <i class="fas fa-edit"></i>
                        {{ __('Edit') }}
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div
                    class="bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-800 text-green-700 dark:text-green-400 px-4 py-3 rounded-lg mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Subscriptions') }}</p>
                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                        {{ $device->subscriptions->count() }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Hotspot Users') }}</p>
                    <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                        {{ $device->hotspotUsers->count() }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Uptime') }}</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                        @if ($device->uptime_seconds)
                            {{ gmdate('H:i:s', $device->uptime_seconds) }}
                        @else
                            -
                        @endif
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Last Seen') }}</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white">
                        {{ $device->last_seen_at ? $device->last_seen_at->diffForHumans() : __('Never') }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Device Details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Connection Info -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                            {{ __('Informasi Koneksi') }}</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('IP Address') }}</p>
                                <p class="text-lg font-mono font-semibold text-gray-900 dark:text-white">
                                    {{ $device->ip_address }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Port') }}</p>
                                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $device->port }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Username') }}</p>
                                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $device->username }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Brand') }}</p>
                                <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ ucfirst($device->brand) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Health Check Results -->
                    @if ($healthCheck)
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                                {{ __('Health Check') }}</h2>

                            @if ($healthCheck['success'])
                                <div
                                    class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-4">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-check-circle text-green-600 dark:text-green-400"></i>
                                        <span
                                            class="text-green-800 dark:text-green-400 font-semibold">{{ __('Device terhubung dengan baik') }}</span>
                                    </div>
                                </div>

                                @if (isset($healthCheck['details']))
                                    <div class="space-y-2">
                                        @foreach ($healthCheck['details'] as $key => $value)
                                            <div
                                                class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                                                <span
                                                    class="text-gray-600 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $key)) }}</span>
                                                <span
                                                    class="font-semibold text-gray-900 dark:text-white">{{ is_array($value) ? json_encode($value) : $value }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @else
                                <div
                                    class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-times-circle text-red-600 dark:text-red-400"></i>
                                        <span
                                            class="text-red-800 dark:text-red-400 font-semibold">{{ __('Koneksi gagal') }}</span>
                                    </div>
                                    @if (isset($healthCheck['error']))
                                        <p class="mt-2 text-sm text-red-700 dark:text-red-400">
                                            {{ $healthCheck['error'] }}</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Bandwidth Usage -->
                    @if ($bandwidthUsage)
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                                {{ __('Bandwidth Usage') }}</h2>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                                    <p class="text-sm text-blue-600 dark:text-blue-400 mb-1">{{ __('Download') }}</p>
                                    <p class="text-2xl font-bold text-blue-900 dark:text-blue-300">
                                        {{ $bandwidthUsage['download_formatted'] ?? '0 B' }}</p>
                                    <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                                        {{ $bandwidthUsage['download_bps'] ?? 0 }} bps</p>
                                </div>
                                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                                    <p class="text-sm text-green-600 dark:text-green-400 mb-1">{{ __('Upload') }}</p>
                                    <p class="text-2xl font-bold text-green-900 dark:text-green-300">
                                        {{ $bandwidthUsage['upload_formatted'] ?? '0 B' }}</p>
                                    <p class="text-xs text-green-600 dark:text-green-400 mt-1">
                                        {{ $bandwidthUsage['upload_bps'] ?? 0 }} bps</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Subscriptions -->
                    @if ($device->subscriptions->isNotEmpty())
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                                {{ __('Active Subscriptions') }} ({{ $device->subscriptions->count() }})</h2>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th
                                                class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                {{ __('Customer') }}</th>
                                            <th
                                                class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                {{ __('Package') }}</th>
                                            <th
                                                class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                {{ __('Status') }}</th>
                                            <th
                                                class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                {{ __('Quota Used') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach ($device->subscriptions->take(5) as $subscription)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                                <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">
                                                    {{ $subscription->customer?->name ?? 'N/A' }}</td>
                                                <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">
                                                    {{ $subscription->package?->name ?? 'N/A' }}</td>
                                                <td class="px-4 py-2">
                                                    <span
                                                        class="px-2 py-1 text-xs rounded-full {{ $subscription->status === 'active' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-400' }}">
                                                        {{ ucfirst($subscription->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">
                                                    {{ number_format($subscription->quota_used_bytes / 1073741824, 2) }}
                                                    GB</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Additional Info -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                            {{ __('Detail Tambahan') }}</h2>

                        @if ($device->location)
                            <div class="mb-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">{{ __('Lokasi') }}</p>
                                <p class="font-semibold text-gray-900 dark:text-white">{{ $device->location }}</p>
                            </div>
                        @endif

                        @if ($device->parentDevice)
                            <div class="mb-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">{{ __('Parent Device') }}</p>
                                <a href="{{ route('telecom.devices.show', $device->parentDevice) }}"
                                    class="text-blue-600 dark:text-blue-400 hover:underline">
                                    {{ $device->parentDevice->name }}
                                </a>
                            </div>
                        @endif

                        @if ($device->description)
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">{{ __('Deskripsi') }}</p>
                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $device->description }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Recent Alerts -->
                    @if ($recentAlerts->isNotEmpty())
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                                {{ __('Recent Alerts') }}</h2>
                            <div class="space-y-3">
                                @foreach ($recentAlerts->take(5) as $alert)
                                    <div
                                        class="border-l-4 {{ $alert->severity === 'critical' ? 'border-red-500' : ($alert->severity === 'high' ? 'border-orange-500' : 'border-yellow-500') }} pl-3 py-2">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ $alert->title }}</p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                            {{ $alert->triggered_at->diffForHumans() }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Created Info -->
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <p class="text-xs text-gray-600 dark:text-gray-400">{{ __('Dibuat') }}:
                            {{ $device->created_at->format('d M Y H:i') }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ __('Terakhir diupdate') }}:
                            {{ $device->updated_at->diffForHumans() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Location History & Route Tracking -->
    @if ($device->hasCoordinates())
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            <i class="fas fa-map-marker-alt text-blue-600 dark:text-blue-400 mr-2"></i>
                            {{ __('Location History & Tracking') }}
                        </h3>
                        <div class="flex gap-2">
                            <button onclick="showLocationHistory()"
                                class="bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                                <i class="fas fa-history mr-1"></i> {{ __('Location History') }}
                            </button>
                            <button onclick="showRouteTracking()"
                                class="bg-purple-600 hover:bg-purple-700 dark:bg-purple-700 dark:hover:bg-purple-600 text-white px-3 py-1 rounded text-sm">
                                <i class="fas fa-route mr-1"></i> {{ __('Route Tracking') }}
                            </button>
                            <button onclick="showGeofenceAlerts()"
                                class="bg-orange-600 hover:bg-orange-700 dark:bg-orange-700 dark:hover:bg-orange-600 text-white px-3 py-1 rounded text-sm">
                                <i class="fas fa-bell mr-1"></i> {{ __('Geofence Alerts') }}
                            </button>
                        </div>
                    </div>

                    <!-- Location Map -->
                    <div id="locationMap" style="height: 400px;"
                        class="rounded-lg border border-gray-300 dark:border-gray-600"></div>

                    <!-- Location Info -->
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded">
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Current Location') }}</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $device->latitude }},
                                {{ $device->longitude }}</p>
                        </div>
                        @if ($device->location)
                            <div class="bg-green-50 dark:bg-green-900/20 p-3 rounded">
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Location Name') }}</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $device->location }}
                                </p>
                            </div>
                        @endif
                        @if ($device->coverage_radius)
                            <div class="bg-purple-50 dark:bg-purple-900/20 p-3 rounded">
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Coverage Radius') }}</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($device->coverage_radius) }} meters</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Location History Modal -->
        <div id="locationHistoryModal"
            class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div
                class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 shadow-lg rounded-md bg-white dark:bg-gray-800">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('Location History') }}</h3>
                    <button onclick="closeModal('locationHistoryModal')"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('Time') }}</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('Coordinates') }}</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('Accuracy') }}</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('Speed') }}</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('Source') }}</th>
                            </tr>
                        </thead>
                        <tbody id="locationHistoryTable"
                            class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr>
                                <td colspan="5" class="px-4 py-2 text-center text-gray-500 dark:text-gray-400">
                                    {{ __('Loading...') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Route Tracking Modal -->
        <div id="routeTrackingModal"
            class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div
                class="relative top-10 mx-auto p-5 border w-11/12 md:w-5/6 shadow-lg rounded-md bg-white dark:bg-gray-800">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('Route Tracking') }}</h3>
                    <button onclick="closeModal('routeTrackingModal')"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div id="routeMap" style="height: 500px;"
                    class="rounded-lg border border-gray-300 dark:border-gray-600 mb-4"></div>
                <div id="routeStats" class="grid grid-cols-2 md:grid-cols-4 gap-3"></div>
            </div>
        </div>

        <!-- Geofence Alerts Modal -->
        <div id="geofenceAlertsModal"
            class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div
                class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 shadow-lg rounded-md bg-white dark:bg-gray-800">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ __('Geofence Alerts') }}</h3>
                    <button onclick="closeModal('geofenceAlertsModal')"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('Time') }}</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('Event') }}</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('Zone') }}</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('Message') }}</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody id="geofenceAlertsTable"
                            class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr>
                                <td colspan="5" class="px-4 py-2 text-center text-gray-500 dark:text-gray-400">
                                    {{ __('Loading...') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            // Initialize location map
            @if ($device->hasCoordinates())
                let locationMap = L.map('locationMap').setView([{{ $device->latitude }}, {{ $device->longitude }}], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(locationMap);

                const deviceIcon = L.divIcon({
                    html: '<div style="background-color: #3B82F6; width: 24px; height: 24px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>',
                    iconSize: [24, 24],
                    iconAnchor: [12, 12],
                    className: ''
                });
                L.marker([{{ $device->latitude }}, {{ $device->longitude }}], {
                        icon: deviceIcon
                    })
                    .addTo(locationMap)
                    .bindPopup('<b>{{ $device->name }}</b><br>Status: {{ $device->status }}');

                @if ($device->coverage_radius)
                    L.circle([{{ $device->latitude }}, {{ $device->longitude }}], {
                        radius: {{ $device->coverage_radius }},
                        color: '#3B82F6',
                        fillColor: '#3B82F6',
                        fillOpacity: 0.1
                    }).addTo(locationMap);
                @endif
            @endif

            function closeModal(modalId) {
                document.getElementById(modalId).classList.add('hidden');
            }

            function showLocationHistory() {
                document.getElementById('locationHistoryModal').classList.remove('hidden');
                loadLocationHistory();
            }

            function showRouteTracking() {
                document.getElementById('routeTrackingModal').classList.remove('hidden');
                loadRouteTracking();
            }

            function showGeofenceAlerts() {
                document.getElementById('geofenceAlertsModal').classList.remove('hidden');
                loadGeofenceAlerts();
            }

            function loadLocationHistory() {
                fetch('{{ route('telecom.api.location.history', $device->id) }}?limit=50')
                    .then(res => res.json())
                    .then(data => {
                        const tbody = document.getElementById('locationHistoryTable');
                        if (data.data && data.data.length > 0) {
                            tbody.innerHTML = data.data.map(item => `
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">${new Date(item.recorded_at).toLocaleString()}</td>
                                    <td class="px-4 py-2 text-sm font-mono text-gray-900 dark:text-white">${item.coordinates}</td>
                                    <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">${item.accuracy_meters || '-'}m</td>
                                    <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">${item.speed_kmh ? item.speed_kmh + ' km/h' : '-'}</td>
                                    <td class="px-4 py-2 text-sm"><span class="px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400 rounded text-xs">${item.source}</span></td>
                                </tr>
                            `).join('');
                        } else {
                            tbody.innerHTML =
                                '<tr><td colspan="5" class="px-4 py-2 text-center text-gray-500 dark:text-gray-400">{{ __('No location history found') }}</td></tr>';
                        }
                    })
                    .catch(err => {
                        console.error('Error loading location history:', err);
                        document.getElementById('locationHistoryTable').innerHTML =
                            '<tr><td colspan="5" class="px-4 py-2 text-center text-red-500">{{ __('Error loading data') }}</td></tr>';
                    });
            }

            function loadRouteTracking() {
                fetch('{{ route('telecom.api.location.route', $device->id) }}')
                    .then(res => res.json())
                    .then(data => {
                        if (data.data && data.data.length > 0) {
                            setTimeout(() => {
                                const routeMap = L.map('routeMap').setView([data.data[0].latitude, data.data[0]
                                    .longitude
                                ], 14);
                                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                    attribution: '© OpenStreetMap contributors'
                                }).addTo(routeMap);

                                const coordinates = data.data.map(point => [point.latitude, point.longitude]);
                                L.polyline(coordinates, {
                                    color: '#8B5CF6',
                                    weight: 4,
                                    opacity: 0.8
                                }).addTo(routeMap);

                                const startIcon = L.divIcon({
                                    html: '<div style="background-color: #10B981; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white;"></div>',
                                    iconSize: [20, 20],
                                    className: ''
                                });
                                L.marker([data.data[0].latitude, data.data[0].longitude], {
                                    icon: startIcon
                                }).addTo(routeMap).bindPopup('{{ __('Start Point') }}');

                                const endIcon = L.divIcon({
                                    html: '<div style="background-color: #EF4444; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white;"></div>',
                                    iconSize: [20, 20],
                                    className: ''
                                });
                                const lastPoint = data.data[data.data.length - 1];
                                L.marker([lastPoint.latitude, lastPoint.longitude], {
                                    icon: endIcon
                                }).addTo(routeMap).bindPopup('{{ __('End Point') }}');

                                routeMap.fitBounds(coordinates);
                            }, 100);
                        }

                        if (data.stats) {
                            document.getElementById('routeStats').innerHTML = `
                                <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded">
                                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ __('Total Points') }}</p>
                                    <p class="text-lg font-bold text-gray-900 dark:text-white">${data.stats.total_points}</p>
                                </div>
                                <div class="bg-green-50 dark:bg-green-900/20 p-3 rounded">
                                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ __('Distance') }}</p>
                                    <p class="text-lg font-bold text-gray-900 dark:text-white">${(data.stats.total_distance_meters / 1000).toFixed(2)} km</p>
                                </div>
                                <div class="bg-purple-50 dark:bg-purple-900/20 p-3 rounded">
                                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ __('Duration') }}</p>
                                    <p class="text-lg font-bold text-gray-900 dark:text-white">${data.stats.duration_minutes.toFixed(0)} min</p>
                                </div>
                                <div class="bg-orange-50 dark:bg-orange-900/20 p-3 rounded">
                                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ __('Avg Speed') }}</p>
                                    <p class="text-lg font-bold text-gray-900 dark:text-white">${data.stats.total_distance_meters > 0 ? ((data.stats.total_distance_meters / 1000) / (data.stats.duration_minutes / 60)).toFixed(1) : 0} km/h</p>
                                </div>
                            `;
                        }
                    })
                    .catch(err => console.error('Error loading route tracking:', err));
            }

            function loadGeofenceAlerts() {
                fetch('{{ route('telecom.api.location.geofence-alerts', $device->id) }}?limit=50')
                    .then(res => res.json())
                    .then(data => {
                        const tbody = document.getElementById('geofenceAlertsTable');
                        if (data.data && data.data.length > 0) {
                            tbody.innerHTML = data.data.map(item => `
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">${new Date(item.triggered_at).toLocaleString()}</td>
                                    <td class="px-4 py-2 text-sm">
                                        <span class="px-2 py-1 ${item.event_type === 'enter' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400' : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400'} rounded text-xs">
                                            ${item.event_type.toUpperCase()}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">${item.zone_name || '-'}</td>
                                    <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">${item.message}</td>
                                    <td class="px-4 py-2 text-sm">
                                        <span class="px-2 py-1 ${item.is_notified ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400' : 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400'} rounded text-xs">
                                            ${item.is_notified ? '{{ __('Notified') }}' : '{{ __('Pending') }}'}
                                        </span>
                                    </td>
                                </tr>
                            `).join('');
                        } else {
                            tbody.innerHTML =
                                '<tr><td colspan="5" class="px-4 py-2 text-center text-gray-500 dark:text-gray-400">{{ __('No alerts found') }}</td></tr>';
                        }
                    })
                    .catch(err => {
                        console.error('Error loading geofence alerts:', err);
                        document.getElementById('geofenceAlertsTable').innerHTML =
                            '<tr><td colspan="5" class="px-4 py-2 text-center text-red-500">{{ __('Error loading data') }}</td></tr>';
                    });
            }

            document.getElementById('testConnectionForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const button = this.querySelector('button');
                const originalText = button.innerHTML;
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> {{ __('Testing...') }}';

                fetch(this.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Toast.success(data.message);
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            Toast.error(data.message);
                            button.disabled = false;
                            button.innerHTML = originalText;
                        }
                    })
                    .catch(error => {
                        Toast.error('Error: ' + error.message);
                        button.disabled = false;
                        button.innerHTML = originalText;
                    });
            });
        </script>
    @endpush
</x-app-layout>
