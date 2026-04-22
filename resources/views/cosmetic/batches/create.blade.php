@extends('layouts.app')

@section('title', 'Create Batch')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('cosmetic.batches.index') }}" class="text-blue-600 hover:text-blue-900 mb-2 inline-block">
                ← Back to Batches
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Create New Batch</h1>
            <p class="mt-1 text-sm text-gray-500">Record a new production batch</p>
        </div>

        <!-- Success/Error Messages -->
        @if (session('success'))
            <div class="mb-4 bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-200 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 text-red-800 dark:text-red-200 px-4 py-3 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('cosmetic.batches.store') }}">
            @csrf

            <div class="space-y-6">
                <!-- Batch Information -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Batch Information</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Formula <span class="text-red-500">*</span>
                            </label>
                            <select name="formula_id" required
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 @error('formula_id') border-red-500 @enderror"
                                onchange="updateFormulaInfo(this.value)">
                                <option value="">Select Formula</option>
                                @foreach ($formulas as $formula)
                                    <option value="{{ $formula->id }}"
                                        {{ old('formula_id') == $formula->id || (isset($selectedFormula) && $selectedFormula->id == $formula->id) ? 'selected' : '' }}
                                        data-code="{{ $formula->formula_code }}" data-type="{{ $formula->product_type }}"
                                        data-batch-size="{{ $formula->batch_size }}"
                                        data-batch-unit="{{ $formula->batch_unit }}">
                                        {{ $formula->formula_name }} ({{ $formula->formula_code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('formula_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror

                            <!-- Formula Info Display -->
                            <div id="formula-info" class="mt-2 p-3 bg-blue-50 dark:bg-blue-900 rounded-lg hidden">
                                <div class="text-sm text-blue-900 dark:text-blue-200">
                                    <div><strong>Code:</strong> <span id="info-code">-</span></div>
                                    <div><strong>Type:</strong> <span id="info-type">-</span></div>
                                    <div><strong>Standard Batch:</strong> <span id="info-batch">-</span></div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Batch Number
                            </label>
                            <input type="text" name="batch_number" value="{{ old('batch_number', 'Auto-generated') }}"
                                readonly
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400 rounded-lg bg-gray-50 text-gray-500">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Auto-generated (BMR-YYYY-NNNN)</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Production Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="production_date"
                                value="{{ old('production_date', date('Y-m-d')) }}" required
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 @error('production_date') border-red-500 @enderror">
                            @error('production_date')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Expiry Date
                            </label>
                            <input type="date" name="expiry_date" value="{{ old('expiry_date') }}"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Optional - based on shelf life</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Planned Quantity <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="planned_quantity" value="{{ old('planned_quantity') }}"
                                step="0.01" min="0" required
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 @error('planned_quantity') border-red-500 @enderror"
                                placeholder="e.g., 1000">
                            @error('planned_quantity')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Production Notes
                        </label>
                        <textarea name="production_notes" rows="4"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="Any special instructions, equipment used, operator notes...">{{ old('production_notes') }}</textarea>
                    </div>
                </div>

                <!-- Quick Info -->
                <div class="bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd" />
                        </svg>
                        <div class="text-sm text-yellow-800 dark:text-yellow-200">
                            <strong>Next Steps After Creation:</strong>
                            <ul class="mt-1 ml-4 list-disc space-y-1">
                                <li>Update status to "In Progress" when starting production</li>
                                <li>Record actual quantity produced</li>
                                <li>Add quality checks at each checkpoint</li>
                                <li>Handle any rework if needed</li>
                                <li>Release batch when all QC passed</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex gap-4">
                    <button type="submit"
                        class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                        Create Batch Record
                    </button>
                    <a href="{{ route('cosmetic.batches.index') }}"
                        class="px-6 py-3 bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 font-medium rounded-lg text-center transition">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            function updateFormulaInfo(formulaId) {
                const select = document.querySelector('select[name="formula_id"]');
                const selectedOption = select.options[select.selectedIndex];
                const infoDiv = document.getElementById('formula-info');

                if (formulaId && selectedOption.dataset.code) {
                    document.getElementById('info-code').textContent = selectedOption.dataset.code;
                    document.getElementById('info-type').textContent = selectedOption.dataset.type;
                    document.getElementById('info-batch').textContent = selectedOption.dataset.batchSize + ' ' + selectedOption
                        .dataset.batchUnit;
                    infoDiv.classList.remove('hidden');
                } else {
                    infoDiv.classList.add('hidden');
                }
            }

            // Initialize on page load if formula is pre-selected
            document.addEventListener('DOMContentLoaded', function() {
                const select = document.querySelector('select[name="formula_id"]');
                if (select.value) {
                    updateFormulaInfo(select.value);
                }
            });
        </script>
    @endpush
@endsection
