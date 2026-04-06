@extends('layouts.app')

@section('title', 'New BPOM Registration')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('cosmetic.registrations.index') }}" class="text-blue-600 hover:text-blue-900 mb-2 inline-block">
                ← Back to Registrations
            </a>
            <h1 class="text-3xl font-bold text-gray-900">New BPOM Registration</h1>
            <p class="mt-1 text-sm text-gray-500">Register product with BPOM</p>
        </div>

        <form method="POST" action="{{ route('cosmetic.registrations.store') }}">
            @csrf

            <div class="space-y-6">
                <!-- Registration Information -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Registration Information</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Formula (Optional)
                            </label>
                            <select name="formula_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Formula</option>
                                @foreach ($formulas as $formula)
                                    <option value="{{ $formula->id }}"
                                        {{ old('formula_id') == $formula->id ? 'selected' : '' }}>
                                        {{ $formula->formula_name }} ({{ $formula->formula_code }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Link to cosmetic formula for compliance checking</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                BPOM Registration Number <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="registration_number" value="{{ old('registration_number') }}"
                                required placeholder="e.g., NA12345678901"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('registration_number') border-red-500 @enderror">
                            @error('registration_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Product Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="product_name" value="{{ old('product_name') }}" required
                                placeholder="e.g., Moisturizing Cream SPF 30"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('product_name') border-red-500 @enderror">
                            @error('product_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Product Category <span class="text-red-500">*</span>
                            </label>
                            <select name="product_category" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('product_category') border-red-500 @enderror">
                                <option value="">Select Category</option>
                                <option value="skincare" {{ old('product_category') == 'skincare' ? 'selected' : '' }}>
                                    Skincare</option>
                                <option value="haircare" {{ old('product_category') == 'haircare' ? 'selected' : '' }}>
                                    Haircare</option>
                                <option value="makeup" {{ old('product_category') == 'makeup' ? 'selected' : '' }}>Makeup
                                </option>
                                <option value="fragrance" {{ old('product_category') == 'fragrance' ? 'selected' : '' }}>
                                    Fragrance</option>
                                <option value="personal_care"
                                    {{ old('product_category') == 'personal_care' ? 'selected' : '' }}>Personal Care
                                </option>
                                <option value="baby_care" {{ old('product_category') == 'baby_care' ? 'selected' : '' }}>
                                    Baby Care</option>
                            </select>
                            @error('product_category')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Registration Type <span class="text-red-500">*</span>
                            </label>
                            <select name="registration_type" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="notification"
                                    {{ old('registration_type') == 'notification' ? 'selected' : '' }}>Notification
                                </option>
                                <option value="certification"
                                    {{ old('registration_type') == 'certification' ? 'selected' : '' }}>Certification
                                </option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Notification for cosmetics, Certification for special
                                products</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Expiry Date
                            </label>
                            <input type="date" name="expiry_date" value="{{ old('expiry_date') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <p class="mt-1 text-xs text-gray-500">Registration expiry date (if applicable)</p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Notes
                        </label>
                        <textarea name="notes" rows="4"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="Additional notes, BPOM comments, etc.">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <!-- Compliance Info -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd" />
                        </svg>
                        <div class="text-sm text-blue-800">
                            <strong>Ingredient Compliance:</strong>
                            <p class="mt-1">
                                If you link a formula, the system will automatically check for restricted or banned
                                ingredients
                                according to BPOM regulations.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex gap-4">
                    <button type="submit"
                        class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                        Create Registration
                    </button>
                    <a href="{{ route('cosmetic.registrations.index') }}"
                        class="px-6 py-3 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded-lg text-center transition">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
@endsection
