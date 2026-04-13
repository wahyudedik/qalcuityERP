<x-app-layout>
    <x-slot name="header">
        {{ __('Create Internet Package') }}
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ __('Create Internet Package') }}
                        </h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('Define a new internet service package for customers') }}</p>
                    </div>
                    <a href="{{ route('telecom.packages.index') }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <i class="fas fa-arrow-left mr-2"></i>
                        {{ __('Back to Packages') }}
                    </a>
                </div>
            </div>

            <!-- Form Card -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <form action="{{ route('telecom.packages.store') }}" method="POST" class="p-6 space-y-6">
                    @csrf

                    <!-- Package Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('Package Name') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" required value="{{ old('name') }}"
                            placeholder="{{ __('e.g., Premium 50Mbps') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('name') border-red-300 @enderror">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Speed Configuration -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Download Speed -->
                        <div>
                            <label for="download_speed_mbps"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('Download Speed (Mbps)') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="relative mt-1 rounded-md shadow-sm">
                                <input type="number" name="download_speed_mbps" id="download_speed_mbps" required
                                    min="1" max="10000" value="{{ old('download_speed_mbps', 10) }}"
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white pr-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('download_speed_mbps') border-red-300 @enderror">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400 sm:text-sm">Mbps</span>
                                </div>
                            </div>
                            @error('download_speed_mbps')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Upload Speed -->
                        <div>
                            <label for="upload_speed_mbps"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('Upload Speed (Mbps)') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="relative mt-1 rounded-md shadow-sm">
                                <input type="number" name="upload_speed_mbps" id="upload_speed_mbps" required
                                    min="1" max="10000" value="{{ old('upload_speed_mbps', 5) }}"
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white pr-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('upload_speed_mbps') border-red-300 @enderror">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400 sm:text-sm">Mbps</span>
                                </div>
                            </div>
                            @error('upload_speed_mbps')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Quota Configuration -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('Monthly Quota') }}
                        </label>
                        <div class="flex items-center space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="quota_type" value="unlimited"
                                    {{ old('quota_type') === 'unlimited' || !old('quota_type') ? 'checked' : '' }}
                                    class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
                                    onchange="toggleQuotaInput(false)">
                                <span
                                    class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('Unlimited') }}</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="quota_type" value="limited"
                                    {{ old('quota_type') === 'limited' ? 'checked' : '' }}
                                    class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
                                    onchange="toggleQuotaInput(true)">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ __('Limited') }}</span>
                            </label>
                        </div>

                        <div id="quota_input" class="mt-3 {{ old('quota_type') === 'limited' ? '' : 'hidden' }}">
                            <div class="relative mt-1 rounded-md shadow-sm max-w-xs">
                                <input type="number" name="quota_gb" id="quota_gb" min="1" max="10000"
                                    value="{{ old('quota_gb', 100) }}"
                                    {{ old('quota_type') === 'limited' ? 'required' : '' }}
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white pr-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('quota_gb') border-red-300 @enderror">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400 sm:text-sm">GB</span>
                                </div>
                            </div>
                            @error('quota_gb')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Pricing -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Price -->
                        <div>
                            <label for="price"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('Monthly Price (IDR)') }} <span class="text-red-500">*</span>
                            </label>
                            <div class="relative mt-1 rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400 sm:text-sm">Rp</span>
                                </div>
                                <input type="number" name="price" id="price" required min="0"
                                    step="1000" value="{{ old('price', 100000) }}"
                                    class="block w-full pl-12 rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('price') border-red-300 @enderror">
                            </div>
                            @error('price')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Billing Cycle -->
                        <div>
                            <label for="billing_cycle"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                {{ __('Billing Cycle') }} <span class="text-red-500">*</span>
                            </label>
                            <select name="billing_cycle" id="billing_cycle" required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('billing_cycle') border-red-300 @enderror">
                                <option value="monthly" {{ old('billing_cycle') === 'monthly' ? 'selected' : '' }}>
                                    {{ __('Monthly') }}</option>
                                <option value="quarterly"
                                    {{ old('billing_cycle') === 'quarterly' ? 'selected' : '' }}>
                                    {{ __('Quarterly (3 months)') }}</option>
                                <option value="yearly" {{ old('billing_cycle') === 'yearly' ? 'selected' : '' }}>
                                    {{ __('Yearly') }}</option>
                            </select>
                            @error('billing_cycle')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('Description') }}
                        </label>
                        <textarea name="description" id="description" rows="3"
                            placeholder="{{ __('Describe the package features and benefits...') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('description') border-red-300 @enderror">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Features (Optional) -->
                    <div>
                        <label for="features" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('Features (one per line)') }}
                        </label>
                        <textarea name="features" id="features" rows="4"
                            placeholder="24/7 Support&#10;{{ __('Free Installation') }}&#10;{{ __('Static IP Available') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('features') border-red-300 @enderror">{{ old('features') }}</textarea>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ __('Enter each feature on a new line') }}</p>
                        @error('features')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Active Status -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="is_active" id="is_active" value="1"
                                {{ old('is_active', true) ? 'checked' : '' }}
                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="is_active"
                                class="font-medium text-gray-700 dark:text-gray-300">{{ __('Activate Package') }}</label>
                            <p class="text-gray-500 dark:text-gray-400">
                                {{ __('Make this package available for new subscriptions immediately') }}</p>
                        </div>
                    </div>

                    <!-- Live Preview -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ __('Package Preview') }}
                        </h3>
                        <div
                            class="bg-gradient-to-br from-indigo-500 to-purple-600 dark:from-indigo-600 dark:to-purple-700 rounded-lg p-6 text-white max-w-md">
                            <div class="flex items-center justify-between mb-4">
                                <h4 id="preview_name" class="text-xl font-bold">{{ __('Premium 50Mbps') }}</h4>
                                <span
                                    class="bg-white bg-opacity-20 px-3 py-1 rounded-full text-sm font-medium">{{ __('POPULAR') }}</span>
                            </div>

                            <div class="space-y-2 mb-4">
                                <div class="flex items-center">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <span id="preview_download">{{ __('10 Mbps Download') }}</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <span id="preview_upload">{{ __('5 Mbps Upload') }}</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <span id="preview_quota">{{ __('Unlimited Quota') }}</span>
                                </div>
                            </div>

                            <div class="border-t border-white border-opacity-20 pt-4">
                                <div class="flex items-baseline">
                                    <span class="text-3xl font-bold" id="preview_price">Rp 100.000</span>
                                    <span class="ml-2 text-sm opacity-80">/{{ __('month') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div
                        class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('telecom.packages.index') }}"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            {{ __('Cancel') }}
                        </a>
                        <button type="submit"
                            class="px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-700 dark:hover:bg-indigo-600">
                            <i class="fas fa-check mr-2"></i>
                            {{ __('Create Package') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function toggleQuotaInput(show) {
                const quotaInput = document.getElementById('quota_input');
                const quotaField = document.getElementById('quota_gb');

                if (show) {
                    quotaInput.classList.remove('hidden');
                    quotaField.setAttribute('required', 'required');
                } else {
                    quotaInput.classList.add('hidden');
                    quotaField.removeAttribute('required');
                }
            }

            document.getElementById('name').addEventListener('input', function() {
                document.getElementById('preview_name').textContent = this.value || '{{ __('Package Name') }}';
            });

            document.getElementById('download_speed_mbps').addEventListener('input', function() {
                document.getElementById('preview_download').textContent = this.value + ' {{ __('Mbps Download') }}';
            });

            document.getElementById('upload_speed_mbps').addEventListener('input', function() {
                document.getElementById('preview_upload').textContent = this.value + ' {{ __('Mbps Upload') }}';
            });

            document.getElementById('price').addEventListener('input', function() {
                const price = parseInt(this.value) || 0;
                document.getElementById('preview_price').textContent = 'Rp ' + price.toLocaleString('id-ID');
            });

            document.querySelectorAll('input[name="quota_type"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'unlimited') {
                        document.getElementById('preview_quota').textContent = '{{ __('Unlimited Quota') }}';
                    } else {
                        const gb = document.getElementById('quota_gb').value || 0;
                        document.getElementById('preview_quota').textContent = gb +
                            ' {{ __('GB Monthly Quota') }}';
                    }
                });
            });

            document.getElementById('quota_gb').addEventListener('input', function() {
                if (document.querySelector('input[name="quota_type"]:checked').value === 'limited') {
                    document.getElementById('preview_quota').textContent = this.value +
                    ' {{ __('GB Monthly Quota') }}';
                }
            });
        </script>
    @endpush
</x-app-layout>
