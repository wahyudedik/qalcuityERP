@extends('layouts.app')

@section('title', 'Tambah Perangkat Fingerprint')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="mb-6">
            <a href="{{ route('hrm.fingerprint.devices.index') }}"
                class="text-blue-600 hover:text-blue-800 flex items-center gap-2 mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Kembali ke Daftar Perangkat
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Tambah Perangkat Fingerprint</h1>
            <p class="text-sm text-gray-600 mt-1">Konfigurasi perangkat fingerprint baru untuk absensi
                karyawan</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <form action="{{ route('hrm.fingerprint.devices.store') }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nama Perangkat -->
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Perangkat <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white text-gray-900"
                            placeholder="Contoh: Fingerprint Lobby Utama">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Device ID -->
                    <div>
                        <label for="device_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Device ID <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="device_id" id="device_id" value="{{ old('device_id') }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white text-gray-900"
                            placeholder="Contoh: FP001">
                        @error('device_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">ID unik untuk identifikasi perangkat</p>
                    </div>

                    <!-- Vendor -->
                    <div>
                        <label for="vendor" class="block text-sm font-medium text-gray-700 mb-2">
                            Vendor <span class="text-red-500">*</span>
                        </label>
                        <select name="vendor" id="vendor" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white text-gray-900">
                            <option value="">Pilih Vendor</option>
                            <option value="zkteco" {{ old('vendor') == 'zkteco' ? 'selected' : '' }}>ZKTeco</option>
                            <option value="suprema" {{ old('vendor') == 'suprema' ? 'selected' : '' }}>Suprema</option>
                            <option value="generic" {{ old('vendor') == 'generic' ? 'selected' : '' }}>Generic/Lainnya
                            </option>
                        </select>
                        @error('vendor')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Model -->
                    <div>
                        <label for="model" class="block text-sm font-medium text-gray-700 mb-2">
                            Model Perangkat
                        </label>
                        <input type="text" name="model" id="model" value="{{ old('model') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white text-gray-900"
                            placeholder="Contoh: F18, uFace 800">
                        @error('model')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Protocol -->
                    <div>
                        <label for="protocol" class="block text-sm font-medium text-gray-700 mb-2">
                            Protokol <span class="text-red-500">*</span>
                        </label>
                        <select name="protocol" id="protocol" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white text-gray-900">
                            <option value="tcp" {{ old('protocol') == 'tcp' ? 'selected' : '' }}>TCP/IP</option>
                            <option value="udp" {{ old('protocol') == 'udp' ? 'selected' : '' }}>UDP</option>
                            <option value="http" {{ old('protocol') == 'http' ? 'selected' : '' }}>HTTP</option>
                            <option value="https" {{ old('protocol') == 'https' ? 'selected' : '' }}>HTTPS</option>
                        </select>
                        @error('protocol')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- IP Address -->
                    <div>
                        <label for="ip_address" class="block text-sm font-medium text-gray-700 mb-2">
                            IP Address
                        </label>
                        <input type="text" name="ip_address" id="ip_address" value="{{ old('ip_address') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white text-gray-900"
                            placeholder="Contoh: 192.168.1.100">
                        @error('ip_address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Port -->
                    <div>
                        <label for="port" class="block text-sm font-medium text-gray-700 mb-2">
                            Port
                        </label>
                        <input type="number" name="port" id="port" value="{{ old('port', 4370) }}" min="1"
                            max="65535"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white text-gray-900"
                            placeholder="4370">
                        @error('port')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Default: 4370 untuk ZKTeco</p>
                    </div>

                    <!-- API Key -->
                    <div>
                        <label for="api_key" class="block text-sm font-medium text-gray-700 mb-2">
                            API Key (Opsional)
                        </label>
                        <input type="text" name="api_key" id="api_key" value="{{ old('api_key') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white text-gray-900"
                            placeholder="API Key jika diperlukan">
                        @error('api_key')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Secret Key -->
                    <div>
                        <label for="secret_key" class="block text-sm font-medium text-gray-700 mb-2">
                            Secret Key (Opsional)
                        </label>
                        <input type="text" name="secret_key" id="secret_key" value="{{ old('secret_key') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white text-gray-900"
                            placeholder="Secret key untuk autentikasi">
                        @error('secret_key')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Notes -->
                    <div class="md:col-span-2">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Catatan
                        </label>
                        <textarea name="notes" id="notes" rows="3"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white text-gray-900"
                            placeholder="Catatan tambahan tentang perangkat...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Is Active -->
                    <div class="md:col-span-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" value="1"
                                {{ old('is_active', true) ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Aktifkan perangkat</span>
                        </label>
                    </div>
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                        Simpan Perangkat
                    </button>
                    <a href="{{ route('hrm.fingerprint.devices.index') }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
