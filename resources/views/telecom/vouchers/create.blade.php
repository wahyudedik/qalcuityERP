<x-app-layout>
    <x-slot name="header">
        {{ __('Generate Vouchers') }}
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ __('Generate Vouchers') }}</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Create new voucher codes for customers') }}
                    </p>
                </div>
                <a href="{{ route('telecom.vouchers.index') }}"
                    class="bg-gray-600 hover:bg-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                    {{ __('Back to List') }}
                </a>
            </div>

            @if ($errors->any())
                <div
                    class="bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-lg mb-4">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Generate Form -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('telecom.vouchers.store') }}" method="POST">
                    @csrf

                    <!-- Package Selection -->
                    <div class="mb-6">
                        <label for="package_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('Internet Package') }} <span class="text-red-500">*</span>
                        </label>
                        <select name="package_id" id="package_id" required
                            class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">{{ __('Select Package...') }}</option>
                            @foreach ($packages as $package)
                                <option value="{{ $package->id }}"
                                    {{ old('package_id') == $package->id ? 'selected' : '' }}>
                                    {{ $package->name }} -
                                    {{ $package->download_speed_mbps }}/{{ $package->upload_speed_mbps }} Mbps
                                    @if ($package->quota_bytes)
                                        ({{ round($package->quota_bytes / 1073741824, 2) }} GB)
                                    @else
                                        ({{ __('Unlimited') }})
                                    @endif
                                    - Rp {{ number_format($package->price, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                        @error('package_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Quantity -->
                    <div class="mb-6">
                        <label for="quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('Quantity') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="quantity" id="quantity" min="1" max="1000"
                            value="{{ old('quantity', 10) }}" required
                            class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ __('Max 1000 vouchers per batch') }}</p>
                        @error('quantity')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Code Configuration -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="code_length"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Code Length') }}
                            </label>
                            <input type="number" name="code_length" id="code_length" min="6" max="16"
                                value="{{ old('code_length', 8) }}"
                                class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('6-16 characters') }}</p>
                        </div>

                        <div>
                            <label for="code_pattern"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Code Pattern') }}
                            </label>
                            <select name="code_pattern" id="code_pattern"
                                class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="alphanumeric"
                                    {{ old('code_pattern') === 'alphanumeric' ? 'selected' : '' }}>
                                    {{ __('Alphanumeric (A-Z, 0-9)') }}
                                </option>
                                <option value="numeric" {{ old('code_pattern') === 'numeric' ? 'selected' : '' }}>
                                    {{ __('Numeric Only (0-9)') }}
                                </option>
                                <option value="alphabetic"
                                    {{ old('code_pattern') === 'alphabetic' ? 'selected' : '' }}>
                                    {{ __('Alphabetic Only (A-Z)') }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Validity & Usage -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="validity_hours"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Validity Period (Hours)') }}
                            </label>
                            <input type="number" name="validity_hours" id="validity_hours" min="1"
                                value="{{ old('validity_hours', 24) }}"
                                class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Default: 24 hours') }}</p>
                        </div>

                        <div>
                            <label for="max_usage"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Max Usage Count') }}
                            </label>
                            <input type="number" name="max_usage" id="max_usage" min="1"
                                value="{{ old('max_usage', 1) }}"
                                class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ __('How many times can be used') }}</p>
                        </div>
                    </div>

                    <!-- Sale Price -->
                    <div class="mb-6">
                        <label for="sale_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('Sale Price (Optional)') }}
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500 dark:text-gray-400">Rp</span>
                            <input type="number" name="sale_price" id="sale_price" min="0" step="1000"
                                value="{{ old('sale_price') }}"
                                class="w-full pl-12 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ __('Leave empty to use package price') }}</p>
                    </div>

                    <!-- Batch Number -->
                    <div class="mb-6">
                        <label for="batch_number"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('Batch Number (Optional)') }}
                        </label>
                        <input type="text" name="batch_number" id="batch_number"
                            value="{{ old('batch_number', 'BATCH-' . now()->format('Ymd')) }}"
                            class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ __('For grouping vouchers together') }}</p>
                    </div>

                    <!-- Preview -->
                    <div
                        class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
                        <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-300 mb-2">{{ __('Preview:') }}
                        </h3>
                        <ul class="text-xs text-blue-800 dark:text-blue-400 space-y-1">
                            <li>• {{ __('Package') }}: <span id="preview_package">-</span></li>
                            <li>• {{ __('Quantity') }}: <span id="preview_quantity">10</span> {{ __('vouchers') }}
                            </li>
                            <li>• {{ __('Code Format') }}: <span
                                    id="preview_code">{{ __('8 chars, alphanumeric') }}</span></li>
                            <li>• {{ __('Valid For') }}: <span id="preview_validity">{{ __('24 hours') }}</span></li>
                            <li>• {{ __('Batch') }}: <span id="preview_batch">-</span></li>
                        </ul>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex gap-3">
                        <button type="submit"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold">
                            <i class="fas fa-ticket-alt mr-2"></i> {{ __('Generate Vouchers') }}
                        </button>
                        <a href="{{ route('telecom.vouchers.index') }}"
                            class="flex-1 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-6 py-3 rounded-lg font-semibold text-center">
                            {{ __('Cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.getElementById('package_id').addEventListener('change', function() {
                const selected = this.options[this.selectedIndex];
                document.getElementById('preview_package').textContent = selected.text.split(' - ')[0] || '-';
            });

            document.getElementById('quantity').addEventListener('input', function() {
                document.getElementById('preview_quantity').textContent = this.value;
            });

            document.getElementById('code_length').addEventListener('input', function() {
                const pattern = document.getElementById('code_pattern').value;
                document.getElementById('preview_code').textContent = `${this.value} chars, ${pattern}`;
            });

            document.getElementById('code_pattern').addEventListener('change', function() {
                const length = document.getElementById('code_length').value;
                document.getElementById('preview_code').textContent = `${length} chars, ${this.value}`;
            });

            document.getElementById('validity_hours').addEventListener('input', function() {
                document.getElementById('preview_validity').textContent = `${this.value} hours`;
            });

            document.getElementById('batch_number').addEventListener('input', function() {
                document.getElementById('preview_batch').textContent = this.value || '-';
            });
        </script>
    @endpush
</x-app-layout>
