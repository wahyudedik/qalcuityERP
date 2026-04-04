@extends('layouts.app')

@section('title', 'Tambah Network Device')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-6">
                <a href="{{ route('telecom.devices.index') }}"
                    class="text-blue-600 hover:text-blue-800 flex items-center gap-2 mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Devices
                </a>
                <h1 class="text-3xl font-bold text-gray-900">Tambah Network Device</h1>
                <p class="text-gray-600 mt-1">Daftarkan router atau network device baru</p>
            </div>

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Form -->
            <form action="{{ route('telecom.devices.store') }}" method="POST" class="bg-white rounded-lg shadow p-6">
                @csrf

                <!-- Basic Information -->
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Informasi Dasar</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Device *</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Contoh: Main Router Kantor">
                        </div>

                        <div>
                            <label for="device_type" class="block text-sm font-medium text-gray-700 mb-1">Tipe Device
                                *</label>
                            <select name="device_type" id="device_type" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Pilih Tipe</option>
                                <option value="router" {{ old('device_type') == 'router' ? 'selected' : '' }}>Router
                                </option>
                                <option value="access_point" {{ old('device_type') == 'access_point' ? 'selected' : '' }}>
                                    Access Point</option>
                                <option value="switch" {{ old('device_type') == 'switch' ? 'selected' : '' }}>Switch
                                </option>
                                <option value="firewall" {{ old('device_type') == 'firewall' ? 'selected' : '' }}>Firewall
                                </option>
                            </select>
                        </div>

                        <div>
                            <label for="brand" class="block text-sm font-medium text-gray-700 mb-1">Brand *</label>
                            <select name="brand" id="brand" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Pilih Brand</option>
                                <option value="mikrotik" {{ old('brand') == 'mikrotik' ? 'selected' : '' }}>MikroTik
                                </option>
                                <option value="ubiquiti" {{ old('brand') == 'ubiquiti' ? 'selected' : '' }}>Ubiquiti
                                </option>
                                <option value="cisco" {{ old('brand') == 'cisco' ? 'selected' : '' }}>Cisco</option>
                                <option value="openwrt" {{ old('brand') == 'openwrt' ? 'selected' : '' }}>OpenWRT</option>
                                <option value="other" {{ old('brand') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>

                        <div>
                            <label for="model" class="block text-sm font-medium text-gray-700 mb-1">Model</label>
                            <input type="text" name="model" id="model" value="{{ old('model') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Contoh: RB750Gr3">
                        </div>
                    </div>
                </div>

                <!-- Connection Settings -->
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Pengaturan Koneksi</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="ip_address" class="block text-sm font-medium text-gray-700 mb-1">IP Address
                                *</label>
                            <input type="text" name="ip_address" id="ip_address" value="{{ old('ip_address') }}"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono"
                                placeholder="192.168.88.1">
                        </div>

                        <div>
                            <label for="port" class="block text-sm font-medium text-gray-700 mb-1">Port</label>
                            <input type="number" name="port" id="port" value="{{ old('port') }}" min="1"
                                max="65535"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="8728 (MikroTik API)">
                            <p class="text-xs text-gray-500 mt-1">Default: 8728 (MikroTik), 443 (HTTPS)</p>
                        </div>

                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username *</label>
                            <input type="text" name="username" id="username" value="{{ old('username') }}" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="admin">
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
                            <input type="password" name="password" id="password" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="••••••••">
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Informasi Tambahan</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="parent_device_id" class="block text-sm font-medium text-gray-700 mb-1">Parent
                                Device</label>
                            <select name="parent_device_id" id="parent_device_id"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Tidak ada (Root Device)</option>
                                @foreach ($parentDevices as $parent)
                                    <option value="{{ $parent->id }}"
                                        {{ old('parent_device_id') == $parent->id ? 'selected' : '' }}>
                                        {{ $parent->name }} ({{ $parent->ip_address }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                            <input type="text" name="location" id="location" value="{{ old('location') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Contoh: Ruang Server Lt.2">
                        </div>

                        <div class="md:col-span-2">
                            <label for="description"
                                class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
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
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Catatan Penting</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Pastikan device dapat diakses dari server ERP</li>
                                    <li>Untuk MikroTik, aktifkan REST API di IP > Services</li>
                                    <li>Koneksi akan di-test otomatis setelah device ditambahkan</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-3">
                    <a href="{{ route('telecom.devices.index') }}"
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Simpan & Test Koneksi
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
