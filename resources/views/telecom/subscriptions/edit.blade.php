<x-app-layout>
    <x-slot name="header">
        {{ __('Edit Subscription') }}
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('Edit Subscription') }}</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ $subscription->customer?->name ?? '-' }}
                    </p>
                </div>
                <a href="{{ route('telecom.subscriptions.show', $subscription) }}"
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
                <form action="{{ route('telecom.subscriptions.update', $subscription) }}" method="POST" class="p-6 space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Customer (read-only) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Pelanggan') }}</label>
                        <p class="mt-1 text-sm text-gray-900 font-medium">{{ $subscription->customer?->name ?? '-' }}</p>
                    </div>

                    <!-- Package -->
                    <div>
                        <label for="package_id" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('Paket Internet') }} <span class="text-red-500">*</span>
                        </label>
                        <select name="package_id" id="package_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('package_id') border-red-300 @enderror">
                            @foreach ($packages as $package)
                                <option value="{{ $package->id }}" {{ old('package_id', $subscription->package_id) == $package->id ? 'selected' : '' }}>
                                    {{ $package->name }} — {{ $package->download_speed_mbps }}/{{ $package->upload_speed_mbps }} Mbps — Rp {{ number_format($package->price, 0, ',', '.') }}/bln
                                </option>
                            @endforeach
                        </select>
                        @error('package_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('Status') }} <span class="text-red-500">*</span>
                        </label>
                        <select name="status" id="status" required
                            class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('status') border-red-300 @enderror">
                            <option value="active" {{ old('status', $subscription->status) === 'active' ? 'selected' : '' }}>{{ __('Aktif') }}</option>
                            <option value="suspended" {{ old('status', $subscription->status) === 'suspended' ? 'selected' : '' }}>{{ __('Disuspend') }}</option>
                            <option value="cancelled" {{ old('status', $subscription->status) === 'cancelled' ? 'selected' : '' }}>{{ __('Dibatalkan') }}</option>
                            <option value="expired" {{ old('status', $subscription->status) === 'expired' ? 'selected' : '' }}>{{ __('Kadaluarsa') }}</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- End Date -->
                    <div>
                        <label for="ends_at" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('Tanggal Berakhir') }}
                        </label>
                        <input type="date" name="ends_at" id="ends_at"
                            value="{{ old('ends_at', $subscription->ends_at?->format('Y-m-d') ?? $subscription->expires_at?->format('Y-m-d')) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('Catatan') }}
                        </label>
                        <textarea name="notes" id="notes" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('notes', $subscription->notes) }}</textarea>
                    </div>

                    <!-- Submit -->
                    <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                        <a href="{{ route('telecom.subscriptions.show', $subscription) }}"
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            {{ __('Batal') }}
                        </a>
                        <button type="submit"
                            class="px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                            <i class="fas fa-save mr-2"></i>{{ __('Simpan Perubahan') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
