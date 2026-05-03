<x-app-layout>
    <x-slot name="header">{{ __('Create Ward') }}</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('healthcare.wards.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                <i class="fas fa-arrow-left mr-2"></i>Back to List
            </a>
    </div>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('healthcare.wards.store') }}">
                        @csrf

                        <div class="space-y-6">
                            <div>
                                <label for="ward_code" class="block text-sm font-medium text-gray-700">Ward Code</label>
                                <input type="text" name="ward_code" id="ward_code" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    value="WARD-{{ date('Ymd') }}-{{ str_pad(rand(1, 999), 4, '0', STR_PAD_LEFT) }}"
                                    readonly>
                                <p class="mt-1 text-sm text-gray-500">Auto-generated code</p>
                            </div>

                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Ward Name</label>
                                <input type="text" name="name" id="name" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label for="ward_type" class="block text-sm font-medium text-gray-700">Ward Type</label>
                                <select name="ward_type" id="ward_type" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select Type</option>
                                    <option value="general">General</option>
                                    <option value="icu">ICU</option>
                                    <option value="emergency">Emergency</option>
                                    <option value="maternity">Maternity</option>
                                    <option value="pediatric">Pediatric</option>
                                    <option value="surgical">Surgical</option>
                                    <option value="psychiatric">Psychiatric</option>
                                </select>
                            </div>

                            <div>
                                <label for="floor" class="block text-sm font-medium text-gray-700">Floor</label>
                                <input type="text" name="floor" id="floor" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="e.g., 1, 2, 3A">
                            </div>

                            <div>
                                <label for="total_beds" class="block text-sm font-medium text-gray-700">Total
                                    Beds</label>
                                <input type="number" name="total_beds" id="total_beds" required min="1"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label for="description"
                                    class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea name="description" id="description" rows="4"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                            </div>

                            <div>
                                <label for="is_active" class="flex items-center">
                                    <input type="checkbox" name="is_active" id="is_active" value="1" checked
                                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Active</span>
                                </label>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <a href="{{ route('healthcare.wards.index') }}"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                                Cancel
                            </a>
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                <i class="fas fa-save mr-2"></i>Save Ward
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
