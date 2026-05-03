<x-app-layout>
    <x-slot name="header">{{ __('Create Bed') }}</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('healthcare.beds.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                <i class="fas fa-arrow-left mr-2"></i>Back to List
            </a>
    </div>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('healthcare.beds.store') }}">
                        @csrf

                        <div class="space-y-6">
                            <div>
                                <label for="bed_number" class="block text-sm font-medium text-gray-700">Bed
                                    Number</label>
                                <input type="text" name="bed_number" id="bed_number" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="e.g., BED-001">
                            </div>

                            <div>
                                <label for="ward_id" class="block text-sm font-medium text-gray-700">Ward</label>
                                <select name="ward_id" id="ward_id" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select Ward</option>
                                    @foreach ($wards as $ward)
                                        <option value="{{ $ward->id }}">{{ $ward->ward_code }} - {{ $ward->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="bed_type" class="block text-sm font-medium text-gray-700">Bed Type</label>
                                <select name="bed_type" id="bed_type" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select Type</option>
                                    <option value="general">General</option>
                                    <option value="icu">ICU</option>
                                    <option value="nicu">NICU</option>
                                    <option value="picu">PICU</option>
                                    <option value="isolation">Isolation</option>
                                    <option value="maternity">Maternity</option>
                                    <option value="pediatric">Pediatric</option>
                                    <option value="surgical">Surgical</option>
                                </select>
                            </div>

                            <div>
                                <label for="room_number" class="block text-sm font-medium text-gray-700">Room
                                    Number</label>
                                <input type="text" name="room_number" id="room_number"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="e.g., 101A">
                            </div>

                            <div>
                                <label for="floor" class="block text-sm font-medium text-gray-700">Floor</label>
                                <input type="text" name="floor" id="floor"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="e.g., 1, 2, 3">
                            </div>

                            <div>
                                <label for="rate_per_day" class="block text-sm font-medium text-gray-700">Rate Per Day
                                    (Rp)</label>
                                <input type="number" name="rate_per_day" id="rate_per_day" required min="0"
                                    step="1000"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="e.g., 500000">
                            </div>

                            <div>
                                <label for="amenities" class="block text-sm font-medium text-gray-700">Amenities</label>
                                <textarea name="amenities" id="amenities" rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="e.g., TV, AC, Private Bathroom"></textarea>
                            </div>

                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="status" id="status" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="available">Available</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="reserved">Reserved</option>
                                </select>
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
                            <a href="{{ route('healthcare.beds.index') }}"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                                Cancel
                            </a>
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                <i class="fas fa-save mr-2"></i>Save Bed
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
