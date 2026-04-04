@extends('layouts.app')

@section('title', 'Generate Vouchers')

@section('content')
    <div class="container mx-auto px-4 py-6 max-w-3xl">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Generate Vouchers</h1>
                <p class="text-gray-600 mt-1">Create new voucher codes for customers</p>
            </div>
            <a href="{{ route('telecom.vouchers.index') }}"
                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                Back to List
            </a>
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

        <!-- Generate Form -->
        <div class="bg-white rounded-lg shadow p-6">
            <form action="{{ route('telecom.vouchers.store') }}" method="POST">
                @csrf

                <!-- Package Selection -->
                <div class="mb-6">
                    <label for="package_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Internet Package <span class="text-red-500">*</span>
                    </label>
                    <select name="package_id" id="package_id" required
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Package...</option>
                        @foreach ($packages as $package)
                            <option value="{{ $package->id }}" {{ old('package_id') == $package->id ? 'selected' : '' }}>
                                {{ $package->name }} - {{ $package->download_speed_mbps }}/{{ $package->upload_speed_mbps }}
                                Mbps
                                @if ($package->quota_bytes)
                                    ({{ round($package->quota_bytes / 1073741824, 2) }} GB)
                                @else
                                    (Unlimited)
                                @endif
                                - Rp {{ number_format($package->price, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                    @error('package_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Quantity -->
                <div class="mb-6">
                    <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">
                        Quantity <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="quantity" id="quantity" min="1" max="1000"
                        value="{{ old('quantity', 10) }}" required
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-gray-500">Max 1000 vouchers per batch</p>
                    @error('quantity')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Code Configuration -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="code_length" class="block text-sm font-medium text-gray-700 mb-2">
                            Code Length
                        </label>
                        <input type="number" name="code_length" id="code_length" min="6" max="16"
                            value="{{ old('code_length', 8) }}"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-500">6-16 characters</p>
                    </div>

                    <div>
                        <label for="code_pattern" class="block text-sm font-medium text-gray-700 mb-2">
                            Code Pattern
                        </label>
                        <select name="code_pattern" id="code_pattern"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="alphanumeric" {{ old('code_pattern') === 'alphanumeric' ? 'selected' : '' }}>
                                Alphanumeric (A-Z, 0-9)
                            </option>
                            <option value="numeric" {{ old('code_pattern') === 'numeric' ? 'selected' : '' }}>
                                Numeric Only (0-9)
                            </option>
                            <option value="alphabetic" {{ old('code_pattern') === 'alphabetic' ? 'selected' : '' }}>
                                Alphabetic Only (A-Z)
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Validity & Usage -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="validity_hours" class="block text-sm font-medium text-gray-700 mb-2">
                            Validity Period (Hours)
                        </label>
                        <input type="number" name="validity_hours" id="validity_hours" min="1"
                            value="{{ old('validity_hours', 24) }}"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Default: 24 hours</p>
                    </div>

                    <div>
                        <label for="max_usage" class="block text-sm font-medium text-gray-700 mb-2">
                            Max Usage Count
                        </label>
                        <input type="number" name="max_usage" id="max_usage" min="1"
                            value="{{ old('max_usage', 1) }}"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-500">How many times can be used</p>
                    </div>
                </div>

                <!-- Sale Price -->
                <div class="mb-6">
                    <label for="sale_price" class="block text-sm font-medium text-gray-700 mb-2">
                        Sale Price (Optional)
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">Rp</span>
                        <input type="number" name="sale_price" id="sale_price" min="0" step="1000"
                            value="{{ old('sale_price') }}"
                            class="w-full pl-12 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Leave empty to use package price</p>
                </div>

                <!-- Batch Number -->
                <div class="mb-6">
                    <label for="batch_number" class="block text-sm font-medium text-gray-700 mb-2">
                        Batch Number (Optional)
                    </label>
                    <input type="text" name="batch_number" id="batch_number"
                        value="{{ old('batch_number', 'BATCH-' . now()->format('Ymd')) }}"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-gray-500">For grouping vouchers together</p>
                </div>

                <!-- Preview -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <h3 class="text-sm font-semibold text-blue-900 mb-2">Preview:</h3>
                    <ul class="text-xs text-blue-800 space-y-1">
                        <li>• Package: <span id="preview_package">-</span></li>
                        <li>• Quantity: <span id="preview_quantity">10</span> vouchers</li>
                        <li>• Code Format: <span id="preview_code">8 chars, alphanumeric</span></li>
                        <li>• Valid For: <span id="preview_validity">24 hours</span></li>
                        <li>• Batch: <span id="preview_batch">-</span></li>
                    </ul>
                </div>

                <!-- Submit Buttons -->
                <div class="flex gap-3">
                    <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold">
                        Generate Vouchers
                    </button>
                    <a href="{{ route('telecom.vouchers.index') }}"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-lg font-semibold text-center">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            // Live preview updates
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
@endsection
