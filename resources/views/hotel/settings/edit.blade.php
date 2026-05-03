<x-app-layout>
    <x-slot name="header">Hotel Settings</x-slot>

    <div x-data="hotelSettings()" class="max-w-3xl mx-auto space-y-6">
        <form method="POST" action="{{ route('hotel.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- General Settings Section --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-4">General
                    Settings</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    {{-- Hotel Name --}}
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-2">Hotel Name
                            *</label>
                        <input type="text" name="hotel_name" required
                            value="{{ old('hotel_name', $settings->hotel_name ?? '') }}"
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- Check-in Time --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Check-in Time
                            *</label>
                        <input type="time" name="check_in_time" required
                            value="{{ old('check_in_time', $settings->check_in_time ?? '14:00') }}"
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- Check-out Time --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Check-out Time
                            *</label>
                        <input type="time" name="check_out_time" required
                            value="{{ old('check_out_time', $settings->check_out_time ?? '12:00') }}"
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- Timezone --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Timezone
                            *</label>
                        <select name="timezone" required
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach ($timezones as $tz => $label)
                                <option value="{{ $tz }}" @selected(old('timezone', $settings->timezone ?? 'Asia/Jakarta') === $tz)>{{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Currency --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Currency
                            *</label>
                        <select name="currency" required
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach ($currencies as $code => $name)
                                <option value="{{ $code }}" @selected(old('currency', $settings->currency ?? 'IDR') === $code)>{{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tax Rate --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Tax Rate (%)
                            *</label>
                        <input type="number" name="tax_rate" required min="0" max="100" step="0.01"
                            value="{{ old('tax_rate', $settings->tax_rate ?? 10) }}"
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- Tax Included --}}
                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="tax_included" id="tax_included" value="1"
                            {{ old('tax_included', $settings->tax_included ?? false) ? 'checked' : '' }}
                            class="rounded text-blue-600">
                        <label for="tax_included" class="text-sm text-gray-700">
                            Tax included in room rates
                        </label>
                    </div>
                </div>
            </div>

            {{-- Contact Information Section --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-4">Contact
                    Information</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $settings->phone ?? '') }}"
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Email</label>
                        <input type="email" name="email" value="{{ old('email', $settings->email ?? '') }}"
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Website</label>
                        <input type="url" name="website" placeholder="https://"
                            value="{{ old('website', $settings->website ?? '') }}"
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-2">Address</label>
                        <textarea name="address" rows="2"
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('address', $settings->address ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Deposit Settings Section --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-4">Deposit
                    Settings</h3>

                <div class="space-y-4">
                    {{-- Deposit Required Toggle --}}
                    <div class="flex items-center justify-between py-2">
                        <div>
                            <p class="font-medium text-gray-900">Deposit Required</p>
                            <p class="text-xs text-gray-500">Require deposit at check-in</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="deposit_required" id="deposit_required" value="1"
                                x-model="depositRequired"
                                {{ old('deposit_required', $settings->deposit_required ?? false) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div
                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                            </div>
                        </label>
                    </div>

                    {{-- Default Deposit Amount --}}
                    <div x-show="depositRequired" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-2">Default
                                    Deposit Amount</label>
                                <div class="relative">
                                    <span
                                        class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">Rp</span>
                                    <input type="number" name="default_deposit_amount" min="0" step="10000"
                                        value="{{ old('default_deposit_amount', $settings->default_deposit_amount ?? 0) }}"
                                        class="w-full pl-10 pr-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-2">Deposit
                                    Type</label>
                                <select name="deposit_type"
                                    class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="fixed" @selected(old('deposit_type', $settings->deposit_type ?? 'fixed') === 'fixed')>Fixed Amount</option>
                                    <option value="percentage" @selected(old('deposit_type', $settings->deposit_type ?? 'fixed') === 'percentage')>Percentage of Total
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Operations Section --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-4">
                    Operations</h3>

                <div class="space-y-4">
                    {{-- Overbooking Allowed --}}
                    <div class="flex items-start justify-between gap-4 py-2">
                        <div>
                            <p class="font-medium text-gray-900">Allow Overbooking</p>
                            <p class="text-xs text-gray-500">Allow reservations beyond available
                                capacity</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer mt-0.5">
                            <input type="checkbox" name="overbooking_allowed" value="1"
                                {{ old('overbooking_allowed', $settings->overbooking_allowed ?? false) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div
                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                            </div>
                        </label>
                    </div>

                    {{-- Auto Assign Room --}}
                    <div class="flex items-start justify-between gap-4 py-2">
                        <div>
                            <p class="font-medium text-gray-900">Auto-assign Room</p>
                            <p class="text-xs text-gray-500">Automatically assign available room at
                                check-in</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer mt-0.5">
                            <input type="checkbox" name="auto_assign_room" value="1"
                                {{ old('auto_assign_room', $settings->auto_assign_room ?? false) ? 'checked' : '' }}
                                class="sr-only peer">
                            <div
                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Policies Section --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-4">Policies
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Cancellation
                            Policy</label>
                        <textarea name="cancellation_policy" rows="3"
                            placeholder="e.g., Free cancellation up to 24 hours before check-in..."
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('cancellation_policy', $settings->cancellation_policy ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Terms &
                            Conditions</label>
                        <textarea name="terms_conditions" rows="4" placeholder="Hotel terms and conditions..."
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('terms_conditions', $settings->terms_conditions ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Submit Button --}}
            <div class="flex justify-end gap-3">
                <a href="{{ route('hotel.dashboard') }}"
                    class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Cancel</a>
                <button type="submit"
                    class="px-6 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Save Settings
                </button>
            </div>
        </form>
    </div>

    {{-- Alpine.js Component --}}
    <script>
        window.hotelSettings = function() {
            return {
                depositRequired: {{ old('deposit_required', $settings->deposit_required ?? false) ? 'true' : 'false' }},
            }
        };
    </script>
</x-app-layout>
