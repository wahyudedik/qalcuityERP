<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('Create QC Inspection') }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">New quality control inspection record</p>
            </div>
            <a href="{{ route('qc.inspections.index') }}"
                class="bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 px-4 py-2 rounded-lg">
                <i class="fas fa-arrow-left mr-2"></i>Back to List
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('qc.inspections.store') }}" method="POST">
                    @csrf

                    <!-- Work Order -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Work Order <span class="text-red-500">*</span>
                        </label>
                        <select name="work_order_id" required
                            class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 @error('work_order_id') border-red-500 @enderror">
                            <option value="">Select Work Order</option>
                            @foreach ($workOrders as $wo)
                                <option value="{{ $wo->id }}"
                                    {{ $selectedWorkOrder && $selectedWorkOrder->id == $wo->id ? 'selected' : '' }}>
                                    {{ $wo->number }} - {{ $wo->product?->name ?? 'No Product' }}
                                </option>
                            @endforeach
                        </select>
                        @error('work_order_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Template -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Test Template (Optional)
                        </label>
                        <select name="template_id"
                            class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                            <option value="">No Template (Manual Inspection)</option>
                            @foreach ($templates as $template)
                                <option value="{{ $template->id }}">
                                    {{ $template->name }} ({{ $template->stage_label }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Stage -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Inspection Stage <span class="text-red-500">*</span>
                        </label>
                        <select name="stage" required
                            class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 @error('stage') border-red-500 @enderror">
                            <option value="">Select Stage</option>
                            <option value="incoming" {{ old('stage') == 'incoming' ? 'selected' : '' }}>Incoming
                                Material</option>
                            <option value="in-process" {{ old('stage') == 'in-process' ? 'selected' : '' }}>In-Process
                            </option>
                            <option value="final" {{ old('stage') == 'final' ? 'selected' : '' }}>Final Inspection
                            </option>
                            <option value="random" {{ old('stage') == 'random' ? 'selected' : '' }}>Random Check
                            </option>
                        </select>
                        @error('stage')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Sample Size -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Sample Size <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="sample_size" value="{{ old('sample_size', 10) }}" min="1"
                            required
                            class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 @error('sample_size') border-red-500 @enderror">
                        @error('sample_size')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3">
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex-1">
                            <i class="fas fa-save mr-2"></i>Create Inspection
                        </button>
                        <a href="{{ route('qc.inspections.index') }}"
                            class="bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 px-6 py-2 rounded-lg text-center">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
