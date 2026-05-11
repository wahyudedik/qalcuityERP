<x-app-layout>
    <x-slot name="header">
        {{ __('Tambah Network Device') }}
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <a href="{{ route('telecom.devices.index') }}"
                    class="text-blue-600 hover:text-blue-800 flex items-center gap-2 mb-4">
                    <i class="fas fa-arrow-left"></i>
                    {{ __('Kembali ke Devices') }}
                </a>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('Tambah Network Device') }}</h1>
                <p class="text-gray-600 mt-1">{{ __('Daftarkan router atau network device baru') }}
                </p>
            </div>

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Form -->
            <form action="{{ route('telecom.devices.store') }}" method="POST"
                class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @csrf

                <!-- Basic Information -->
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('Informasi Dasar') }}
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="name"
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('Nama Device') }}
                                *</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Contoh: Main Router Kantor">
                        </div>

                        <div>
                            <label for="device_type"
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('Tipe Device') }}
                                *</label>
                            <select name="device_type" id="device_type" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">{{ __('Pilih Tipe') }}</option>
                                <option value="router" {{ old('device_type') == 'router' ? 'selected' : '' }}>Router
                                </option>
                                <option value="access_point"
                                    {{ old('device_type') == 'access_point' ? 'selected' : '' }}>Access Point</option>
                                <option value="switch" {{ old('device_type') == 'switch' ? 'selected' : '' }}>Switch
                                </option>
                                <option value="firewall" {{ old('device_type') == 'firewall' ? 'selected' : '' }}>
                                    Firewall</option>
                            </select>
                        </div>

                        <div>
                            <label for="brand"
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('Brand') }}
                                *</label>
                            <select name="brand" id="brand" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">{{ __('Pilih Brand') }}</option>
                                <option value="mikrotik" {{ old('brand') == 'mikrotik' ? 'selected' : '' }}>MikroTik
                                </option>
                                <option value="ubiquiti" {{ old('brand') == 'ubiquiti' ? 'selected' : '' }}>Ubiquiti
                                </option>
                                <option value="cisco" {{ old('brand') == 'cisco' ? 'selected' : '' }}>Cisco</option>
                                <option value="openwrt" {{ old('brand') == 'openwrt' ? 'selected' : '' }}>OpenWRT
                                </option>
                                <option value="other" {{ old('brand') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>

                        <div>
                            <label for="model"
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('Model') }}</label>
                            <input type="text" name="model" id="model" value="{{ old('model') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Contoh: RB750Gr3">
                        </div>
                    </div>
                </div>

                <!-- Connection Settings -->
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('Pengaturan Koneksi') }}
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="ip_address"
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('IP Address') }}
                                *</label>
                            <input type="text" name="ip_address" id="ip_address" value="{{ old('ip_address') }}"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono"
                                placeholder="192.168.88.1">
                        </div>

                        <div>
                            <label for="port"
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('Port') }}</label>
                            <input type="number" name="port" id="port" value="{{ old('port') }}"
                                min="1" max="65535"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="8728 (MikroTik API)">
                            <p class="text-xs text-gray-500 mt-1">
                                {{ __('Default: 8728 (MikroTik), 443 (HTTPS)') }}</p>
                        </div>

                        <div>
                            <label for="username"
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('Username') }}
                                *</label>
                            <input type="text" name="username" id="username" value="{{ old('username') }}" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="admin">
                        </div>

                        <div>
                            <label for="password"
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('Password') }}
                                *</label>
                            <input type="password" name="password" id="password" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="••••••••">
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('Informasi Tambahan') }}
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="parent_device_id"
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('Parent Device') }}</label>
                            <select name="parent_device_id" id="parent_device_id"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">{{ __('Tidak ada (Root Device)') }}</option>
                                @foreach ($parentDevices as $parent)
                                    <option value="{{ $parent->id }}"
                                        {{ old('parent_device_id') == $parent->id ? 'selected' : '' }}>
                                        {{ $parent->name }} ({{ $parent->ip_address }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="location"
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('Lokasi') }}</label>
                            <input type="text" name="location" id="location" value="{{ old('location') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Contoh: Tower A - Jakarta Selatan">
                        </div>

                        <div class="md:col-span-2">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2">{{ __('Koordinat GPS (Opsional)') }}</label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                                <div>
                                    <input type="number" name="latitude" id="latitude"
                                        value="{{ old('latitude') }}" step="0.000001"
                                        placeholder="Latitude (-90 to 90)"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <input type="number" name="longitude" id="longitude"
                                        value="{{ old('longitude') }}" step="0.000001"
                                        placeholder="Longitude (-180 to 180)"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <button type="button" id="getLocationBtn"
                                        class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center justify-center gap-2">
                                        <i class="fas fa-map-marker-alt"></i>
                                        {{ __('Get Current Location') }}
                                    </button>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mb-3">
                                {{ __('Klik tombol di atas untuk mendapatkan koordinat dari browser, atau masukkan manual') }}
                            </p>

                            <!-- Mini Map Preview -->
                            <div id="mapPreview" class="border border-gray-300 rounded-lg"
                                style="height: 250px; display: none;"></div>
                            <div id="mapPlaceholder"
                                class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center bg-gray-50">
                                <i class="fas fa-map mx-auto h-12 w-12 text-gray-400"></i>
                                <p class="mt-2 text-sm text-gray-500">
                                    {{ __('Map akan muncul setelah koordinat diisi') }}</p>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label for="coverage_radius"
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('Coverage Radius (meter)') }}</label>
                            <input type="number" name="coverage_radius" id="coverage_radius"
                                value="{{ old('coverage_radius') }}" min="1" max="50000"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Contoh: 1000 (1 km)">
                            <p class="text-xs text-gray-500 mt-1">
                                {{ __('Radius coverage area dalam meter (1-50000). Kosongkan jika tidak diketahui.') }}
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <label for="description"
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('Deskripsi') }}</label>
                            <textarea name="description" id="description" rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Deskripsi tambahan tentang device ini...">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle h-5 w-5 text-blue-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">
                                {{ __('Catatan Penting') }}</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>{{ __('Pastikan device dapat diakses dari server ERP') }}</li>
                                    <li>{{ __('Untuk MikroTik, aktifkan REST API di IP > Services') }}</li>
                                    <li>{{ __('Koneksi akan di-test otomatis setelah device ditambahkan') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-3">
                    <a href="{{ route('telecom.devices.index') }}"
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        {{ __('Batal') }}
                    </a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        {{ __('Simpan & Test Koneksi') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let map = null;
                let marker = null;
                const latInput = document.getElementById('latitude');
                const lngInput = document.getElementById('longitude');
                const getLocationBtn = document.getElementById('getLocationBtn');
                const mapPreview = document.getElementById('mapPreview');
                const mapPlaceholder = document.getElementById('mapPlaceholder');

                // Initialize map if coordinates exist
                function initMap(lat, lng) {
                    if (map) {
                        map.setView([lat, lng], 15);
                        marker.setLatLng([lat, lng]);
                        return;
                    }

                    mapPreview.style.display = 'block';
                    mapPlaceholder.style.display = 'none';

                    map = L.map('mapPreview').setView([lat, lng], 15);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(map);

                    marker = L.marker([lat, lng], {
                        draggable: true
                    }).addTo(map);

                    marker.on('dragend', function(event) {
                        const position = marker.getLatLng();
                        latInput.value = position.lat.toFixed(7);
                        lngInput.value = position.lng.toFixed(7);
                    });
                }

                // Update map when coordinates change
                function updateMap() {
                    const lat = parseFloat(latInput.value);
                    const lng = parseFloat(lngInput.value);

                    if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                        initMap(lat, lng);
                    }
                }

                // Get current location button
                getLocationBtn.addEventListener('click', function() {
                    if (!navigator.geolocation) {
                        Dialog.warning('{{ __('Geolocation tidak didukung oleh browser Anda') }}');
                        return;
                    }

                    getLocationBtn.disabled = true;
                    getLocationBtn.innerHTML =
                        '<i class="fas fa-spinner fa-spin"></i> {{ __('Getting location...') }}';

                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;

                            latInput.value = lat.toFixed(7);
                            lngInput.value = lng.toFixed(7);

                            updateMap();

                            getLocationBtn.disabled = false;
                            getLocationBtn.innerHTML =
                                '<i class="fas fa-map-marker-alt"></i> {{ __('Get Current Location') }}';
                        },
                        function(error) {
                            Dialog.warning('{{ __('Gagal mendapatkan lokasi: ') }}' + error.message);
                            getLocationBtn.disabled = false;
                            getLocationBtn.innerHTML =
                                '<i class="fas fa-map-marker-alt"></i> {{ __('Get Current Location') }}';
                        }, {
                            enableHighAccuracy: true,
                            timeout: 10000,
                            maximumAge: 0
                        }
                    );
                });

                // Listen for coordinate input changes
                latInput.addEventListener('change', updateMap);
                lngInput.addEventListener('change', updateMap);

                // Check if old input has coordinates
                if (latInput.value && lngInput.value) {
                    updateMap();
                }
            });
        </script>
    @endpush
</x-app-layout>
