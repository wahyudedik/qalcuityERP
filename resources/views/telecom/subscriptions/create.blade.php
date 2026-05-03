<x-app-layout>
    <x-slot name="header">
        {{ __('Buat Subscription Baru') }}
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('Buat Subscription Baru') }}</h1>
                    <p class="mt-1 text-sm text-gray-600">{{ __('Daftarkan pelanggan ke paket internet') }}</p>
                </div>
                <a href="{{ route('telecom.subscriptions.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-arrow-left mr-2"></i>{{ __('Kembali') }}
                </a>
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

            <div class="bg-white shadow-sm sm:rounded-lg">
                <form action="{{ route('telecom.subscriptions.store') }}" method="POST" class="p-6 space-y-6">
                    @csrf

                    <!-- Customer -->
                    <div>
                        <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('Pelanggan') }} <span class="text-red-500">*</span>
                        </label>
                        <select name="customer_id" id="customer_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('customer_id') border-red-300 @enderror">
                            <option value="">{{ __('Pilih Pelanggan...') }}</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }} {{ $customer->email ? '(' . $customer->email . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Package -->
                    <div>
                        <label for="package_id" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('Paket Internet') }} <span class="text-red-500">*</span>
                        </label>
                        <select name="package_id" id="package_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('package_id') border-red-300 @enderror">
                            <option value="">{{ __('Pilih Paket...') }}</option>
                            @foreach ($packages as $package)
                                <option value="{{ $package->id }}" {{ old('package_id') == $package->id ? 'selected' : '' }}>
                                    {{ $package->name }} — {{ $package->download_speed_mbps }}/{{ $package->upload_speed_mbps }} Mbps — Rp {{ number_format($package->price, 0, ',', '.') }}/bln
                                </option>
                            @endforeach
                        </select>
                        @error('package_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Device -->
                    <div>
                        <label for="device_id" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('Perangkat Jaringan') }} <span class="text-red-500">*</span>
                        </label>
                        <select name="device_id" id="device_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('device_id') border-red-300 @enderror">
                            <option value="">{{ __('Pilih Perangkat...') }}</option>
                            @foreach ($devices as $device)
                                <option value="{{ $device->id }}" {{ old('device_id') == $device->id ? 'selected' : '' }}>
                                    {{ $device->name }} ({{ $device->ip_address }}) — {{ ucfirst($device->status) }}
                                </option>
                            @endforeach
                        </select>
                        @error('device_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Dates -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="started_at" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('Tanggal Mulai') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="started_at" id="started_at" required
                                value="{{ old('started_at', now()->format('Y-m-d')) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('started_at') border-red-300 @enderror">
                            @error('started_at')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="ends_at" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('Tanggal Berakhir (opsional)') }}
                            </label>
                            <input type="date" name="ends_at" id="ends_at"
                                value="{{ old('ends_at') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                    </div>

                    <!-- Auth Type -->
                    <div>
                        <label for="auth_type" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('Tipe Autentikasi') }} <span class="text-red-500">*</span>
                        </label>
                        <select name="auth_type" id="auth_type" required
                            class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('auth_type') border-red-300 @enderror">
                            <option value="username_password" {{ old('auth_type') === 'username_password' ? 'selected' : '' }}>{{ __('Username & Password') }}</option>
                            <option value="mac_address" {{ old('auth_type') === 'mac_address' ? 'selected' : '' }}>{{ __('MAC Address') }}</option>
                            <option value="voucher" {{ old('auth_type') === 'voucher' ? 'selected' : '' }}>{{ __('Voucher') }}</option>
                        </select>
                        @error('auth_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Hotspot Credentials -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="hotspot_username" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('Username Hotspot') }}
                            </label>
                            <input type="text" name="hotspot_username" id="hotspot_username"
                                value="{{ old('hotspot_username') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="hotspot_password" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('Password Hotspot') }}
                            </label>
                            <input type="password" name="hotspot_password" id="hotspot_password"
                                class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('Catatan') }}
                        </label>
                        <textarea name="notes" id="notes" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('notes') }}</textarea>
                    </div>

                    <!-- Submit -->
                    <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                        <a href="{{ route('telecom.subscriptions.index') }}"
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            {{ __('Batal') }}
                        </a>
                        <button type="submit"
                            class="px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                            <i class="fas fa-check mr-2"></i>{{ __('Buat Subscription') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
