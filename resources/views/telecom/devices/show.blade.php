@extends('layouts.app')

@section('title', $device->name)

@section('content')
    <div class="container mx-auto px-4 py-6">
        <!-- Breadcrumb -->
        <div class="mb-4">
            <a href="{{ route('telecom.devices.index') }}" class="text-blue-600 hover:text-blue-800 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Devices
            </a>
        </div>

        <!-- Header with Actions -->
        <div class="flex justify-between items-start mb-6">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-3xl font-bold text-gray-900">{{ $device->name }}</h1>
                    @if ($device->status === 'online')
                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">Online</span>
                    @elseif($device->status === 'offline')
                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">Offline</span>
                    @elseif($device->status === 'maintenance')
                        <span
                            class="px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">Maintenance</span>
                    @else
                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">Pending</span>
                    @endif
                </div>
                <p class="text-gray-600 mt-1">{{ ucfirst($device->device_type) }} • {{ ucfirst($device->brand) }}
                    {{ $device->model }}</p>
            </div>

            <div class="flex gap-2">
                <form action="{{ route('telecom.devices.test-connection', $device) }}" method="POST"
                    id="testConnectionForm">
                    @csrf
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Test Koneksi
                    </button>
                </form>

                <form action="{{ route('telecom.devices.toggle-maintenance', $device) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        </svg>
                        {{ $device->status === 'maintenance' ? 'Exit Maintenance' : 'Maintenance Mode' }}
                    </button>
                </form>

                <a href="{{ route('telecom.devices.edit', $device) }}"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-600">Subscriptions</p>
                <p class="text-2xl font-bold text-blue-600">{{ $device->subscriptions->count() }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-600">Hotspot Users</p>
                <p class="text-2xl font-bold text-purple-600">{{ $device->hotspotUsers->count() }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-600">Uptime</p>
                <p class="text-2xl font-bold text-green-600">
                    @if ($device->uptime_seconds)
                        {{ gmdate('H:i:s', $device->uptime_seconds) }}
                    @else
                        -
                    @endif
                </p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-600">Last Seen</p>
                <p class="text-lg font-bold text-gray-900">
                    {{ $device->last_seen_at ? $device->last_seen_at->diffForHumans() : 'Never' }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Device Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Connection Info -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Informasi Koneksi</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">IP Address</p>
                            <p class="text-lg font-mono font-semibold">{{ $device->ip_address }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Port</p>
                            <p class="text-lg font-semibold">{{ $device->port }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Username</p>
                            <p class="text-lg font-semibold">{{ $device->username }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Brand</p>
                            <p class="text-lg font-semibold">{{ ucfirst($device->brand) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Health Check Results -->
                @if ($healthCheck)
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Health Check</h2>

                        @if ($healthCheck['success'])
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="text-green-800 font-semibold">Device terhubung dengan baik</span>
                                </div>
                            </div>

                            @if (isset($healthCheck['details']))
                                <div class="space-y-2">
                                    @foreach ($healthCheck['details'] as $key => $value)
                                        <div class="flex justify-between py-2 border-b border-gray-100">
                                            <span class="text-gray-600">{{ ucfirst(str_replace('_', ' ', $key)) }}</span>
                                            <span
                                                class="font-semibold">{{ is_array($value) ? json_encode($value) : $value }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @else
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="text-red-800 font-semibold">Koneksi gagal</span>
                                </div>
                                @if (isset($healthCheck['error']))
                                    <p class="mt-2 text-sm text-red-700">{{ $healthCheck['error'] }}</p>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Bandwidth Usage -->
                @if ($bandwidthUsage)
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Bandwidth Usage</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-blue-50 rounded-lg p-4">
                                <p class="text-sm text-blue-600 mb-1">Download</p>
                                <p class="text-2xl font-bold text-blue-900">
                                    {{ $bandwidthUsage['download_formatted'] ?? '0 B' }}</p>
                                <p class="text-xs text-blue-600 mt-1">{{ $bandwidthUsage['download_bps'] ?? 0 }} bps</p>
                            </div>
                            <div class="bg-green-50 rounded-lg p-4">
                                <p class="text-sm text-green-600 mb-1">Upload</p>
                                <p class="text-2xl font-bold text-green-900">
                                    {{ $bandwidthUsage['upload_formatted'] ?? '0 B' }}</p>
                                <p class="text-xs text-green-600 mt-1">{{ $bandwidthUsage['upload_bps'] ?? 0 }} bps</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Subscriptions -->
                @if ($device->subscriptions->isNotEmpty())
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Active Subscriptions
                            ({{ $device->subscriptions->count() }})</h2>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Customer</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Package
                                        </th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status
                                        </th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quota
                                            Used</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($device->subscriptions->take(5) as $subscription)
                                        <tr>
                                            <td class="px-4 py-2 text-sm">{{ $subscription->customer?->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-4 py-2 text-sm">{{ $subscription->package?->name ?? 'N/A' }}
                                            </td>
                                            <td class="px-4 py-2">
                                                <span
                                                    class="px-2 py-1 text-xs rounded-full {{ $subscription->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                    {{ ucfirst($subscription->status) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 text-sm">
                                                {{ number_format($subscription->quota_used_bytes / 1073741824, 2) }} GB
                                            </td>
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
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Detail Tambahan</h2>

                    @if ($device->location)
                        <div class="mb-4">
                            <p class="text-sm text-gray-600 mb-1">Lokasi</p>
                            <p class="font-semibold">{{ $device->location }}</p>
                        </div>
                    @endif

                    @if ($device->parentDevice)
                        <div class="mb-4">
                            <p class="text-sm text-gray-600 mb-1">Parent Device</p>
                            <a href="{{ route('telecom.devices.show', $device->parentDevice) }}"
                                class="text-blue-600 hover:underline">
                                {{ $device->parentDevice->name }}
                            </a>
                        </div>
                    @endif

                    @if ($device->description)
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Deskripsi</p>
                            <p class="text-sm text-gray-700">{{ $device->description }}</p>
                        </div>
                    @endif
                </div>

                <!-- Recent Alerts -->
                @if ($recentAlerts->isNotEmpty())
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Recent Alerts</h2>
                        <div class="space-y-3">
                            @foreach ($recentAlerts->take(5) as $alert)
                                <div
                                    class="border-l-4 {{ $alert->severity === 'critical' ? 'border-red-500' : ($alert->severity === 'high' ? 'border-orange-500' : 'border-yellow-500') }} pl-3 py-2">
                                    <p class="text-sm font-semibold">{{ $alert->title }}</p>
                                    <p class="text-xs text-gray-600 mt-1">{{ $alert->triggered_at->diffForHumans() }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Created Info -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xs text-gray-600">Dibuat: {{ $device->created_at->format('d M Y H:i') }}</p>
                    <p class="text-xs text-gray-600 mt-1">Terakhir diupdate: {{ $device->updated_at->diffForHumans() }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.getElementById('testConnectionForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const button = this.querySelector('button');
                const originalText = button.innerHTML;
                button.disabled = true;
                button.innerHTML =
                    '<svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Testing...';

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
                            alert('✅ ' + data.message);
                            location.reload();
                        } else {
                            alert('❌ ' + data.message);
                            button.disabled = false;
                            button.innerHTML = originalText;
                        }
                    })
                    .catch(error => {
                        alert('Error: ' + error.message);
                        button.disabled = false;
                        button.innerHTML = originalText;
                    });
            });
        </script>
    @endpush
@endsection
