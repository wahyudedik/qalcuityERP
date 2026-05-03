<x-app-layout>
    <x-slot name="header">{{ __('Edit QC Test Template') }}</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('qc.templates.show', $template) }}"
            class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded-lg">
            <i class="fas fa-arrow-left mr-2"></i>Back to Details
        </a>
    </div>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('qc.templates.update', $template) }}" method="POST" x-data="templateForm()">
                    @csrf
                    @method('PUT')

                    <!-- Template Name -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Template Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $template->name) }}" required
                            class="w-full rounded-md border-gray-300 @error('name') border-red-500 @enderror"
                            placeholder="e.g., Incoming Material Inspection">
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Product Type -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Product Type
                        </label>
                        <input type="text" name="product_type"
                            value="{{ old('product_type', $template->product_type) }}"
                            class="w-full rounded-md border-gray-300"
                            placeholder="e.g., Electronics, Textiles (leave blank for all types)">
                    </div>

                    <!-- Stage -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Inspection Stage <span class="text-red-500">*</span>
                        </label>
                        <select name="stage" required
                            class="w-full rounded-md border-gray-300 @error('stage') border-red-500 @enderror">
                            <option value="">Select Stage</option>
                            <option value="incoming"
                                {{ old('stage', $template->stage) == 'incoming' ? 'selected' : '' }}>Incoming Material
                            </option>
                            <option value="in-process"
                                {{ old('stage', $template->stage) == 'in-process' ? 'selected' : '' }}>In-Process
                            </option>
                            <option value="final" {{ old('stage', $template->stage) == 'final' ? 'selected' : '' }}>
                                Final Inspection</option>
                        </select>
                        @error('stage')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Sample Size Formula -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Sample Size Formula <span class="text-red-500">*</span>
                        </label>
                        <select name="sample_size_formula" required
                            class="w-full rounded-md border-gray-300 @error('sample_size_formula') border-red-500 @enderror">
                            <option value="1"
                                {{ old('sample_size_formula', $template->sample_size_formula) == 1 ? 'selected' : '' }}>
                                √n (Square Root of Lot Size)</option>
                            <option value="2"
                                {{ old('sample_size_formula', $template->sample_size_formula) == 2 ? 'selected' : '' }}>
                                10% of Lot Size</option>
                            <option value="3"
                                {{ old('sample_size_formula', $template->sample_size_formula) == 3 ? 'selected' : '' }}>
                                5% of Lot Size (min 3)</option>
                        </select>
                        @error('sample_size_formula')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- AQL -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Acceptance Quality Limit (AQL %) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="acceptance_quality_limit" step="0.01" min="0"
                            max="100"
                            value="{{ old('acceptance_quality_limit', $template->acceptance_quality_limit) }}" required
                            class="w-full rounded-md border-gray-300 @error('acceptance_quality_limit') border-red-500 @enderror">
                        @error('acceptance_quality_limit')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Test Parameters -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Test Parameters <span class="text-red-500">*</span>
                        </label>

                        <template x-for="(param, index) in parameters" :key="index">
                            <div class="mb-3 p-4 border rounded-lg relative">
                                <button type="button" x-show="parameters.length > 1" @click="removeParameter(index)"
                                    class="absolute top-2 right-2 text-red-500 hover:text-red-700">
                                    <i class="fas fa-times"></i>
                                </button>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Parameter
                                            Name *</label>
                                        <input type="text" :name="`test_parameters[${index}][name]`"
                                            x-model="param.name" required
                                            class="w-full rounded-md border-gray-300 text-sm"
                                            placeholder="e.g., Dimension, Weight">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Unit</label>
                                        <input type="text" :name="`test_parameters[${index}][unit]`"
                                            x-model="param.unit"
                                            class="w-full rounded-md border-gray-300 text-sm"
                                            placeholder="e.g., mm, kg, °C">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Min
                                            Value</label>
                                        <input type="number" step="any" :name="`test_parameters[${index}][min]`"
                                            x-model="param.min"
                                            class="w-full rounded-md border-gray-300 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Max
                                            Value</label>
                                        <input type="number" step="any" :name="`test_parameters[${index}][max]`"
                                            x-model="param.max"
                                            class="w-full rounded-md border-gray-300 text-sm">
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <label class="inline-flex items-center">
                                        <input type="hidden" :name="`test_parameters[${index}][critical]`"
                                            value="0">
                                        <input type="checkbox" :name="`test_parameters[${index}][critical]`"
                                            value="1" x-model="param.critical"
                                            class="rounded border-gray-300 text-blue-600">
                                        <span class="ml-2 text-sm text-gray-700">Critical
                                            parameter</span>
                                    </label>
                                </div>
                            </div>
                        </template>

                        <button type="button" @click="addParameter()"
                            class="text-blue-600 hover:text-blue-800 text-sm">
                            <i class="fas fa-plus mr-1"></i>Add Parameter
                        </button>

                        @error('test_parameters')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Instructions -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Instructions
                        </label>
                        <textarea name="instructions" rows="3"
                            class="w-full rounded-md border-gray-300"
                            placeholder="Step-by-step inspection instructions (optional)">{{ old('instructions', $template->instructions) }}</textarea>
                    </div>

                    <!-- Active Status -->
                    <div class="mb-6">
                        <label class="inline-flex items-center">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1"
                                {{ old('is_active', $template->is_active) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-blue-600">
                            <span class="ml-2 text-sm text-gray-700">Active</span>
                        </label>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3">
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex-1">
                            <i class="fas fa-save mr-2"></i>Update Template
                        </button>
                        <a href="{{ route('qc.templates.show', $template) }}"
                            class="bg-gray-200 hover:bg-gray-300 px-6 py-2 rounded-lg text-center">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function templateForm() {
                return {
                    parameters: @json($template->test_parameters ?? [['name' => '', 'unit' => '', 'min' => '', 'max' => '', 'critical' => false]]),
                    addParameter() {
                        this.parameters.push({
                            name: '',
                            unit: '',
                            min: '',
                            max: '',
                            critical: false
                        });
                    },
                    removeParameter(index) {
                        this.parameters.splice(index, 1);
                    }
                };
            }
        </script>
    @endpush
</x-app-layout>
