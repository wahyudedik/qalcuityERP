<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Medical Waste Record') }} -
            {{ $waste->waste_id }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('healthcare.medical-waste.update', $waste) }}">
                    @csrf
                    @method('PUT')
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="waste_type" class="block text-sm font-medium text-gray-700">Waste Type
                                    *</label>
                                <select name="waste_type" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="infectious"
                                        {{ old('waste_type', $waste->waste_type) === 'infectious' ? 'selected' : '' }}>
                                        Infectious Waste</option>
                                    <option value="hazardous"
                                        {{ old('waste_type', $waste->waste_type) === 'hazardous' ? 'selected' : '' }}>
                                        Hazardous Waste</option>
                                    <option value="sharps"
                                        {{ old('waste_type', $waste->waste_type) === 'sharps' ? 'selected' : '' }}>
                                        Sharps</option>
                                    <option value="pharmaceutical"
                                        {{ old('waste_type', $waste->waste_type) === 'pharmaceutical' ? 'selected' : '' }}>
                                        Pharmaceutical</option>
                                    <option value="general"
                                        {{ old('waste_type', $waste->waste_type) === 'general' ? 'selected' : '' }}>
                                        General Waste</option>
                                </select>
                            </div>
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="status"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="pending"
                                        {{ old('status', $waste->status) === 'pending' ? 'selected' : '' }}>Pending
                                        Disposal</option>
                                    <option value="disposed"
                                        {{ old('status', $waste->status) === 'disposed' ? 'selected' : '' }}>Disposed
                                    </option>
                                    <option value="incinerated"
                                        {{ old('status', $waste->status) === 'incinerated' ? 'selected' : '' }}>
                                        Incinerated</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity *</label>
                                <input type="number" name="quantity" required
                                    value="{{ old('quantity', $waste->quantity) }}" min="0" step="0.01"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="unit" class="block text-sm font-medium text-gray-700">Unit *</label>
                                <select name="unit" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="kg" {{ old('unit', $waste->unit) === 'kg' ? 'selected' : '' }}>
                                        Kilograms (kg)</option>
                                    <option value="liters"
                                        {{ old('unit', $waste->unit) === 'liters' ? 'selected' : '' }}>Liters (L)
                                    </option>
                                    <option value="pieces"
                                        {{ old('unit', $waste->unit) === 'pieces' ? 'selected' : '' }}>Pieces</option>
                                    <option value="bags"
                                        {{ old('unit', $waste->unit) === 'bags' ? 'selected' : '' }}>Bags</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="source" class="block text-sm font-medium text-gray-700">Source
                                    Department</label>
                                <input type="text" name="source" value="{{ old('source', $waste->source) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="collection_date" class="block text-sm font-medium text-gray-700">Collection
                                    Date</label>
                                <input type="date" name="collection_date"
                                    value="{{ old('collection_date', $waste->collection_date ? $waste->collection_date->format('Y-m-d') : '') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label for="disposal_method" class="block text-sm font-medium text-gray-700">Disposal
                                Method</label>
                            <input type="text" name="disposal_method"
                                value="{{ old('disposal_method', $waste->disposal_method) }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea name="notes" rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('notes', $waste->notes) }}</textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="{{ route('healthcare.medical-waste.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                                class="fas fa-save mr-2"></i>Update Record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
