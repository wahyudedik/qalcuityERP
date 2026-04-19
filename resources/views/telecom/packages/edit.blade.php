<x-app-layout>
    <x-slot name="header">
        {{ __('Edit Paket Internet') }}
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ __('Edit Paket Internet') }}</h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('Perbarui informasi paket internet') }}</p>
                    </div>
                    <a href="{{ route('telecom.packages.index') }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <i class="fas fa-arrow-left mr-2"></i>
                        {{ __('Kembali ke Daftar') }}
                    </a>
                </div>
            </div>

            @if ($errors->any())
                <div class="bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-lg mb-4">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Form Card -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <form action="{{ route('telecom.packages.update', $package) }}" method="POST" class="p-6 space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Package Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('Nama Paket') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" required value="{{ old('name', $package->name) }}"
                            placeholder="{{ __('contoh: Premium 50Mbps') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('name') border-red-300 @enderror">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Speed Configuration -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="download_speed_mbps" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('Kecepatan Download (Mbps)') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="relative mt-1 rounded-md shadow-sm">
                                <input type="number" name="download_speed_mbps" id="download_speed_mbps" required
                                    min="1" max="10000" value="{{ old('download_speed_mbps', $package->download_speed_mbps) }}"
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white pr-16 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('download_speed_mbps') border-red-300 @enderror">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400 sm:text-sm">Mbps</span>
                                </div>
                            </div>
                            @error('download_speed_mbps')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="upload_speed_mbps" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('Kecepatan Upload (Mbps)') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="relative mt-1 rounded-md shadow-sm">
                                <input type="number" name="upload_speed_mbps" id="upload_speed_mbps" required
                                    min="1" max="10000" value="{{ old('upload_speed_mbps', $package->upload_speed_mbps) }}"
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white pr-16 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('upload_speed_mbps') border-red-300 @enderror">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400 sm:text-sm">Mbps</span>
                                </div>
                            </div>
                            @error('upload_speed_mbps')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Quota -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="quota_bytes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('Kuota (bytes, kosongkan untuk unlimited)') }}
                            </label>
                            <input type="number" name="quota_bytes" id="quota_bytes" min="0"
                                value="{{ old('quota_bytes', $package->quota_bytes) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Contoh: 107374182400 = 100 GB') }}</p>
                        </div>

                        <div>
                            <label for="quota_period" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('Periode Kuota') }}
                            </label>
                            <select name="quota_period" id="quota_period"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">{{ __('Tidak ada') }}</option>
                                <option value="hourly" {{ old('quota_period', $package->quota_period) === 'hourly' ? 'selected' : '' }}>{{ __('Per Jam') }}</option>
                                <option value="daily" {{ old('quota_period', $package->quota_period) === 'daily' ? 'selected' : '' }}>{{ __('Per Hari') }}</option>
                                <option value="weekly" {{ old('quota_period', $package->quota_period) === 'weekly' ? 'selected' : '' }}>{{ __('Per Minggu') }}</option>
                                <option value="monthly" {{ old('quota_period', $package->quota_period) === 'monthly' ? 'selected' : '' }}>{{ __('Per Bulan') }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Pricing -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('Harga Bulanan (IDR)') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="relative mt-1 rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400 sm:text-sm">Rp</span>
                                </div>
                                <input type="number" name="price" id="price" required min="0" step="1000"
                                    value="{{ old('price', $package->price) }}"
                                    class="block w-full pl-12 rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('price') border-red-300 @enderror">
                            </div>
                            @error('price')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="billing_cycle" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('Siklus Penagihan') }} <span class="text-red-500">*</span>
                            </label>
                            <select name="billing_cycle" id="billing_cycle" required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('billing_cycle') border-red-300 @enderror">
                                <option value="monthly" {{ old('billing_cycle', $package->billing_cycle) === 'monthly' ? 'selected' : '' }}>{{ __('Bulanan') }}</option>
                                <option value="quarterly" {{ old('billing_cycle', $package->billing_cycle) === 'quarterly' ? 'selected' : '' }}>{{ __('Triwulan (3 bulan)') }}</option>
                                <option value="yearly" {{ old('billing_cycle', $package->billing_cycle) === 'yearly' ? 'selected' : '' }}>{{ __('Tahunan') }}</option>
                            </select>
                            @error('billing_cycle')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Setup Fee -->
                    <div>
                        <label for="setup_fee" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('Biaya Pemasangan (IDR)') }}
                        </label>
                        <div class="relative mt-1 rounded-md shadow-sm max-w-xs">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 dark:text-gray-400 sm:text-sm">Rp</span>
                            </div>
                            <input type="number" name="setup_fee" id="setup_fee" min="0" step="1000"
                                value="{{ old('setup_fee', $package->installation_fee ?? 0) }}"
                                class="block w-full pl-12 rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('Deskripsi') }}
                        </label>
                        <textarea name="description" id="description" rows="3"
                            placeholder="{{ __('Deskripsikan fitur dan keunggulan paket...') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description', $package->description) }}</textarea>
                    </div>

                    <!-- Active Status -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="is_active" id="is_active" value="1"
                                {{ old('is_active', $package->is_active) ? 'checked' : '' }}
                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 dark:border-gray-600 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="is_active" class="font-medium text-gray-700 dark:text-gray-300">{{ __('Aktifkan Paket') }}</label>
                            <p class="text-gray-500 dark:text-gray-400">{{ __('Jadikan paket ini tersedia untuk subscription baru') }}</p>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('telecom.packages.index') }}"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            {{ __('Batal') }}
                        </a>
                        <button type="submit"
                            class="px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-700 dark:hover:bg-indigo-600">
                            <i class="fas fa-save mr-2"></i>
                            {{ __('Simpan Perubahan') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
