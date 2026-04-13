<x-app-layout>
    <x-slot name="header">
        {{ __('Network Maps - Device Location Tracking') }}
    </x-slot>

    <div class="h-screen flex flex-col" style="height: calc(100vh - 64px);">
        <!-- Header Bar -->
        <div
            class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-3 flex justify-between items-center shadow-sm">
            <div class="flex items-center gap-4">
                <a href="{{ route('telecom.dashboard') }}"
                    class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('Network Maps') }}</h1>
                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ $stats['devices_with_location'] }}
                        {{ __('of') }} {{ $stats['total_devices'] }} {{ __('devices with location') }}</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 text-sm">
                    <span class="flex items-center gap-1">
                        <span class="w-3 h-3 rounded-full bg-green-500"></span>
                        <span class="text-gray-600 dark:text-gray-400">{{ __('Online') }}</span>
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-3 h-3 rounded-full bg-red-500"></span>
                        <span class="text-gray-600 dark:text-gray-400">{{ __('Offline') }}</span>
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                        <span class="text-gray-600 dark:text-gray-400">{{ __('Maintenance') }}</span>
                    </span>
                </div>
                <button id="fitAllMarkers"
                    class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600 text-white text-sm rounded-lg">
                    <i class="fas fa-expand-arrows-alt mr-1"></i> {{ __('Fit All Devices') }}
                </button>
                <a href="{{ route('telecom.maps.export-pdf') }}"
                    class="px-3 py-1.5 bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-600 text-white text-sm rounded-lg flex items-center gap-1">
                    <i class="fas fa-file-pdf"></i>
                    {{ __('Export PDF') }}
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex flex-1 overflow-hidden">
            <!-- Sidebar -->
            <div class="w-80 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col">
                <!-- Search & Filters -->
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <input type="text" id="searchDevice" placeholder="{{ __('Search devices...') }}"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg text-sm focus:ring-2 focus:ring-blue-500 mb-3">

                    <div class="space-y-2">
                        <select id="filterStatus"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                            <option value="">{{ __('All Status') }}</option>
                            <option value="online">{{ __('Online') }}</option>
                            <option value="offline">{{ __('Offline') }}</option>
                            <option value="maintenance">{{ __('Maintenance') }}</option>
                        </select>

                        <select id="filterType"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                            <option value="">{{ __('All Types') }}</option>
                            <option value="router">{{ __('Router') }}</option>
                            <option value="access_point">{{ __('Access Point') }}</option>
                            <option value="switch">{{ __('Switch') }}</option>
                            <option value="firewall">{{ __('Firewall') }}</option>
                        </select>
                    </div>

                    <button id="toggleCoverage"
                        class="w-full mt-3 px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 flex items-center justify-center gap-2">
                        <i class="fas fa-eye"></i>
                        {{ __('Toggle Coverage Areas') }}
                    </button>
                </div>

                <!-- Device List -->
                <div id="deviceList" class="flex-1 overflow-y-auto">
                    <div class="p-4 text-center text-gray-500 dark:text-gray-400 text-sm">
                        <i class="fas fa-spinner fa-spin text-4xl mb-2"></i>
                        {{ __('Loading devices...') }}
                    </div>
                </div>
            </div>

            <!-- Map Container -->
            <div class="flex-1 relative">
                <div id="map" class="w-full h-full"></div>

                <!-- Map Loading Overlay -->
                <div id="mapLoading"
                    class="absolute inset-0 bg-white dark:bg-gray-800 bg-opacity-75 flex items-center justify-center z-[1000]">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin text-6xl text-blue-600 dark:text-blue-400 mb-4"></i>
                        <p class="text-gray-600 dark:text-gray-400 font-medium">{{ __('Loading map...') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <style>
            #map {
                z-index: 1;
            }

            .device-list-item {
                transition: all 0.2s;
                cursor: pointer;
            }

            .device-list-item:hover {
                background-color: #f3f4f6;
            }

            .dark .device-list-item:hover {
                background-color: #374151;
            }

            .device-list-item.active {
                background-color: #dbeafe;
                border-left: 3px solid #3b82f6;
            }

            .dark .device-list-item.active {
                background-color: #1e3a8a;
                border-left: 3px solid #3b82f6;
            }

            .custom-marker {
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                border: 3px solid white;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
                transition: transform 0.2s;
            }

            .custom-marker:hover {
                transform: scale(1.2);
            }

            .marker-online {
                background-color: #22c55e;
            }

            .marker-offline {
                background-color: #ef4444;
            }

            .marker-maintenance {
                background-color: #eab508;
            }

            .marker-pending {
                background-color: #9ca3af;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let map = null;
                let markers = {};
                let coverageCircles = {};
                let showCoverage = false;
                let allDevices = [];

                function initMap() {
                    map = L.map('map').setView([-6.200000, 106.816666], 6);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors',
                        maxZoom: 19
                    }).addTo(map);

                    document.getElementById('mapLoading').style.display = 'none';
                }

                function createMarkerIcon(status) {
                    const statusClass = `marker-${status}`;
                    return L.divIcon({
                        className: `custom-marker ${statusClass}`,
                        iconSize: [24, 24],
                        iconAnchor: [12, 12],
                        popupAnchor: [0, -12]
                    });
                }

                function loadDevices(focusDeviceId = null) {
                    const status = document.getElementById('filterStatus').value;
                    const type = document.getElementById('filterType').value;
                    const search = document.getElementById('searchDevice').value;

                    let url = '{{ route('telecom.maps.api.devices') }}?';
                    if (status) url += `status=${status}&`;
                    if (type) url += `type=${type}&`;
                    if (search) url += `search=${search}&`;
                    if (focusDeviceId) url += `device_id=${focusDeviceId}&`;

                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                allDevices = data.data;
                                updateMapMarkers(data.data);
                                updateDeviceList(data.data);

                                if (focusDeviceId && data.data.length > 0) {
                                    setTimeout(() => {
                                        focusOnDevice(focusDeviceId);
                                    }, 300);
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error loading devices:', error);
                            document.getElementById('deviceList').innerHTML = `
                                <div class="p-4 text-center text-red-500 dark:text-red-400 text-sm">
                                    <i class="fas fa-exclamation-triangle text-4xl mb-2"></i>
                                    <p>{{ __('Failed to load devices') }}</p>
                                </div>
                            `;
                        });
                }

                function updateMapMarkers(devices) {
                    Object.values(markers).forEach(marker => map.removeLayer(marker));
                    Object.values(coverageCircles).forEach(circle => map.removeLayer(circle));
                    markers = {};
                    coverageCircles = {};

                    devices.forEach(device => {
                        const marker = L.marker([device.latitude, device.longitude], {
                            icon: createMarkerIcon(device.status)
                        }).addTo(map);

                        const popupContent = `
                            <div class="p-2" style="min-width: 250px;">
                                <h3 class="font-bold text-lg mb-2">${device.name}</h3>
                                <div class="space-y-1 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">{{ __('Status') }}:</span>
                                        <span class="px-2 py-0.5 rounded text-xs font-semibold 
                                            ${device.status === 'online' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 
                                              device.status === 'offline' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : 
                                              'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400'}">
                                            ${device.status.toUpperCase()}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">{{ __('Type') }}:</span>
                                        <span>${device.device_type.replace('_', ' ').toUpperCase()}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">{{ __('Brand') }}:</span>
                                        <span>${device.brand.toUpperCase()}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">{{ __('IP') }}:</span>
                                        <span class="font-mono">${device.ip_address}</span>
                                    </div>
                                    ${device.location ? `
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600 dark:text-gray-400">{{ __('Location') }}:</span>
                                                    <span>${device.location}</span>
                                                </div>
                                            ` : ''}
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">{{ __('Subscriptions') }}:</span>
                                        <span class="font-semibold">${device.subscriptions_count}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">{{ __('Users') }}:</span>
                                        <span class="font-semibold">${device.hotspot_users_count}</span>
                                    </div>
                                    ${device.last_seen_at ? `
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600 dark:text-gray-400">{{ __('Last Seen') }}:</span>
                                                    <span>${device.last_seen_at}</span>
                                                </div>
                                            ` : ''}
                                </div>
                                <div class="mt-3 pt-3 border-t dark:border-gray-700">
                                    <a href="/telecom/devices/${device.id}" 
                                       class="block w-full text-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded">
                                        <i class="fas fa-eye mr-1"></i> {{ __('View Details') }}
                                    </a>
                                </div>
                            </div>
                        `;

                        marker.bindPopup(popupContent);
                        markers[device.id] = marker;

                        if (device.coverage_radius && showCoverage) {
                            const circle = L.circle([device.latitude, device.longitude], {
                                radius: device.coverage_radius,
                                color: device.status === 'online' ? '#22c55e' : device.status ===
                                    'offline' ? '#ef4444' : '#eab508',
                                fillColor: device.status === 'online' ? '#22c55e' : device.status ===
                                    'offline' ? '#ef4444' : '#eab508',
                                fillOpacity: 0.1,
                                weight: 2
                            }).addTo(map);
                            coverageCircles[device.id] = circle;
                        }
                    });
                }

                function updateDeviceList(devices) {
                    const deviceList = document.getElementById('deviceList');

                    if (devices.length === 0) {
                        deviceList.innerHTML = `
                            <div class="p-4 text-center text-gray-500 dark:text-gray-400 text-sm">
                                <i class="fas fa-server text-4xl mb-2"></i>
                                <p>{{ __('No devices found') }}</p>
                            </div>
                        `;
                        return;
                    }

                    deviceList.innerHTML = devices.map(device => `
                        <div class="device-list-item p-3 border-b border-gray-200 dark:border-gray-700" data-device-id="${device.id}">
                            <div class="flex items-start gap-3">
                                <div class="w-3 h-3 rounded-full mt-1.5 flex-shrink-0 
                                    ${device.status === 'online' ? 'bg-green-500' : 
                                      device.status === 'offline' ? 'bg-red-500' : 
                                      'bg-yellow-500'}">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-semibold text-sm text-gray-900 dark:text-white truncate">${device.name}</h4>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">${device.device_type.replace('_', ' ').toUpperCase()}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 font-mono mt-0.5">${device.ip_address}</p>
                                    ${device.location ? `<p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">${device.location}</p>` : ''}
                                    <div class="flex items-center gap-3 mt-1.5 text-xs text-gray-600 dark:text-gray-400">
                                        <span>${device.subscriptions_count} {{ __('subs') }}</span>
                                        <span>${device.hotspot_users_count} {{ __('users') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `).join('');

                    document.querySelectorAll('.device-list-item').forEach(item => {
                        item.addEventListener('click', function() {
                            const deviceId = parseInt(this.dataset.deviceId);
                            const device = allDevices.find(d => d.id === deviceId);

                            if (device && markers[deviceId]) {
                                map.setView([device.latitude, device.longitude], 15);
                                markers[deviceId].openPopup();

                                document.querySelectorAll('.device-list-item').forEach(i => i.classList
                                    .remove('active'));
                                this.classList.add('active');
                            }
                        });
                    });
                }

                function fitAllMarkers() {
                    const markersList = Object.values(markers);
                    if (markersList.length > 0) {
                        const group = L.featureGroup(markersList);
                        map.fitBounds(group.getBounds().pad(0.1));
                    }
                }

                function focusOnDevice(deviceId) {
                    const device = allDevices.find(d => d.id === deviceId);
                    if (device && markers[deviceId]) {
                        map.setView([device.latitude, device.longitude], 16);
                        markers[deviceId].openPopup();

                        const listItem = document.querySelector(`.device-list-item[data-device-id="${deviceId}"]`);
                        if (listItem) {
                            document.querySelectorAll('.device-list-item').forEach(i => i.classList.remove('active'));
                            listItem.classList.add('active');
                            listItem.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                        }
                    }
                }

                function toggleCoverageCircles() {
                    showCoverage = !showCoverage;

                    if (showCoverage) {
                        allDevices.forEach(device => {
                            if (device.coverage_radius && !coverageCircles[device.id]) {
                                const circle = L.circle([device.latitude, device.longitude], {
                                    radius: device.coverage_radius,
                                    color: device.status === 'online' ? '#22c55e' : device.status ===
                                        'offline' ? '#ef4444' : '#eab508',
                                    fillColor: device.status === 'online' ? '#22c55e' : device
                                        .status === 'offline' ? '#ef4444' : '#eab508',
                                    fillOpacity: 0.1,
                                    weight: 2
                                }).addTo(map);
                                coverageCircles[device.id] = circle;
                            }
                        });
                    } else {
                        Object.values(coverageCircles).forEach(circle => map.removeLayer(circle));
                        coverageCircles = {};
                    }
                }

                initMap();

                const urlParams = new URLSearchParams(window.location.search);
                const focusDeviceId = urlParams.get('device_id');

                loadDevices(focusDeviceId ? parseInt(focusDeviceId) : null);

                document.getElementById('fitAllMarkers').addEventListener('click', fitAllMarkers);
                document.getElementById('toggleCoverage').addEventListener('click', toggleCoverageCircles);
                document.getElementById('searchDevice').addEventListener('input', debounce(loadDevices, 500));
                document.getElementById('filterStatus').addEventListener('change', loadDevices);
                document.getElementById('filterType').addEventListener('change', loadDevices);
            });

            function debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }
        </script>
    @endpush
</x-app-layout>
