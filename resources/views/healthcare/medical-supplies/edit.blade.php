<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Medical Supply') }} -
            {{ $supply->item_code }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('healthcare.medical-supplies.update', $supply) }}">
                    @csrf
                    @method('PUT')
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Supply Name
                                    *</label>
                                <input type="text" name="name" required value="{{ old('name', $supply->name) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700">Category *</label>
                                <select name="category" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="consumables"
                                        {{ old('category', $supply->category) === 'consumables' ? 'selected' : '' }}>
                                        Consumables</option>
                                    <option value="instruments"
                                        {{ old('category', $supply->category) === 'instruments' ? 'selected' : '' }}>
                                        Instruments</option>
                                    <option value="medications"
                                        {{ old('category', $supply->category) === 'medications' ? 'selected' : '' }}>
                                        Medications</option>
                                    <option value="surgical"
                                        {{ old('category', $supply->category) === 'surgical' ? 'selected' : '' }}>
                                        Surgical Supplies</option>
                                    <option value="diagnostic"
                                        {{ old('category', $supply->category) === 'diagnostic' ? 'selected' : '' }}>
                                        Diagnostic Supplies</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity *</label>
                                <input type="number" name="quantity" required
                                    value="{{ old('quantity', $supply->quantity) }}" min="0"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="unit" class="block text-sm font-medium text-gray-700">Unit *</label>
                                <input type="text" name="unit" required value="{{ old('unit', $supply->unit) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="min_stock_level" class="block text-sm font-medium text-gray-700">Minimum
                                    Stock Level</label>
                                <input type="number" name="min_stock_level"
                                    value="{{ old('min_stock_level', $supply->min_stock_level) }}" min="0"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="status"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="in_stock"
                                        {{ old('status', $supply->status) === 'in_stock' ? 'selected' : '' }}>In Stock
                                    </option>
                                    <option value="low_stock"
                                        {{ old('status', $supply->status) === 'low_stock' ? 'selected' : '' }}>Low
                                        Stock</option>
                                    <option value="out_of_stock"
                                        {{ old('status', $supply->status) === 'out_of_stock' ? 'selected' : '' }}>Out
                                        of Stock</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="expiry_date" class="block text-sm font-medium text-gray-700">Expiry
                                    Date</label>
                                <input type="date" name="expiry_date"
                                    value="{{ old('expiry_date', $supply->expiry_date ? $supply->expiry_date->format('Y-m-d') : '') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="supplier" class="block text-sm font-medium text-gray-700">Supplier</label>
                                <input type="text" name="supplier" value="{{ old('supplier', $supply->supplier) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label for="storage_location" class="block text-sm font-medium text-gray-700">Storage
                                Location</label>
                            <input type="text" name="storage_location"
                                value="{{ old('storage_location', $supply->storage_location) }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea name="notes" rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('notes', $supply->notes) }}</textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="{{ route('healthcare.medical-supplies.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                                class="fas fa-save mr-2"></i>Update Supply</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
