@extends('layouts.app')

@section('title', 'Edit Device: ' . $device->name)

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-6">
                <a href="{{ route('telecom.devices.show', $device) }}"
                    class="text-blue-600 hover:text-blue-800 flex items-center gap-2 mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Detail
                </a>
                <h1 class="text-3xl font-bold text-gray-900">Edit Device</h1>
                <p class="text-gray-600 mt-1">{{ $device->name }}</p>
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
            <form action="{{ route('telecom.devices.update', $device) }}" method="POST"
                class="bg-white rounded-lg shadow p-6">
                @csrf
                @method('PUT')

                <!-- Basic Information -->
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Informasi Dasar</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Device *</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $device->name) }}"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <div>
                            <label for="device_type" class="block text-sm font-medium text-gray-700 mb-1">Tipe Device
                                *</label>
                            <select name="device_type" id="device_type" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Pilih Tipe</option>
                                <option value="router"
                                    {{ old('device_type', $device->device_type) == 'router' ? 'selected' : '' }}>Router
                                </option>
                                <option value="access_point"
                                    {{ old('device_type', $device->device_type) == 'access_point' ? 'selected' : '' }}>
                                    Access Point</option>
                                <option value="switch"
                                    {{ old('device_type', $device->device_type) == 'switch' ? 'selected' : '' }}>Switch
                                </option>
                                <option value="firewall"
                                    {{ old('device_type', $device->device_type) == 'firewall' ? 'selected' : '' }}>Firewall
                                </option>
                            </select>
                        </div>

                        <div>
                            <label for="brand" class="block text-sm font-medium text-gray-700 mb-1">Brand *</label>
                            <select name="brand" id="brand" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Pilih Brand</option>
                                <option value="mikrotik" {{ old('brand', $device->brand) == 'mikrotik' ? 'selected' : '' }}>
                                    MikroTik</option>
                                <option value="ubiquiti"
                                    {{ old('brand', $device->brand) == 'ubiquiti' ? 'selected' : '' }}>Ubiquiti</option>
                                <option value="cisco" {{ old('brand', $device->brand) == 'cisco' ? 'selected' : '' }}>
                                    Cisco</option>
                                <option value="openwrt" {{ old('brand', $device->brand) == 'openwrt' ? 'selected' : '' }}>
                                    OpenWRT</option>
                                <option value="other" {{ old('brand', $device->brand) == 'other' ? 'selected' : '' }}>
                                    Other</option>
                            </select>
                        </div>

                        <div>
                            <label for="model" class="block text-sm font-medium text-gray-700 mb-1">Model</label>
                            <input type="text" name="model" id="model" value="{{ old('model', $device->model) }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
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
                            <input type="text" name="ip_address" id="ip_address"
                                value="{{ old('ip_address', $device->ip_address) }}" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono">
                        </div>

                        <div>
                            <label for="port" class="block text-sm font-medium text-gray-700 mb-1">Port</label>
                            <input type="number" name="port" id="port" value="{{ old('port', $device->port) }}"
                                min="1" max="65535"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username *</label>
                            <input type="text" name="username" id="username"
                                value="{{ old('username', $device->username) }}" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <input type="password" name="password" id="password"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Kosongkan jika tidak ingin mengubah">
                            <p class="text-xs text-gray-500 mt-1">Biarkan kosong untuk mempertahankan password lama</p>
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
                                        {{ old('parent_device_id', $device->parent_device_id) == $parent->id ? 'selected' : '' }}>
                                        {{ $parent->name }} ({{ $parent->ip_address }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" id="status" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="online" {{ old('status', $device->status) == 'online' ? 'selected' : '' }}>
                                    Online</option>
                                <option value="offline"
                                    {{ old('status', $device->status) == 'offline' ? 'selected' : '' }}>Offline</option>
                                <option value="maintenance"
                                    {{ old('status', $device->status) == 'maintenance' ? 'selected' : '' }}>Maintenance
                                </option>
                                <option value="pending"
                                    {{ old('status', $device->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                            </select>
                        </div>

                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                            <input type="text" name="location" id="location"
                                value="{{ old('location', $device->location) }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <div class="md:col-span-2">
                            <label for="description"
                                class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                            <textarea name="description" id="description" rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('description', $device->description) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-3">
                    <a href="{{ route('telecom.devices.show', $device) }}"
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Update Device
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
