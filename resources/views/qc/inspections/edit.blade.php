<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('Record Test Results') }} - {{ $inspection->inspection_number }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $inspection->stage_label }}</p>
            </div>
            <a href="{{ route('qc.inspections.show', $inspection) }}"
                class="bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 px-4 py-2 rounded-lg">
                <i class="fas fa-arrow-left mr-2"></i>Back to Details
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Inspection Info -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Inspection Details</h2>

                        <div class="space-y-3">
                            <div>
                                <label class="text-sm text-gray-600 dark:text-gray-400">Work Order</label>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $inspection->workOrder->number ?? 'N/A' }}</p>
                            </div>

                            <div>
                                <label class="text-sm text-gray-600 dark:text-gray-400">Template</label>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $inspection->template->name ?? 'Manual Inspection' }}</p>
                            </div>

                            <div>
                                <label class="text-sm text-gray-600 dark:text-gray-400">Stage</label>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $inspection->stage_label }}</p>
                            </div>

                            <div>
                                <label class="text-sm text-gray-600 dark:text-gray-400">Sample Size</label>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $inspection->sample_size }}</p>
                            </div>

                            <div>
                                <label class="text-sm text-gray-600 dark:text-gray-400">Status</label>
                                <p class="text-sm font-medium">
                                    <span
                                        class="px-2 py-1 rounded 
                                        {{ $inspection->status_color == 'green' ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : '' }}
                                        {{ $inspection->status_color == 'red' ? 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300' : '' }}
                                        {{ $inspection->status_color == 'yellow' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300' : '' }}
                                        {{ $inspection->status_color == 'blue' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' : '' }}">
                                        {{ str_replace('_', ' ', ucfirst($inspection->status)) }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        @if ($inspection->template && $inspection->template->instructions)
                            <div class="mt-4 p-3 bg-blue-50 dark:bg-gray-700 rounded">
                                <label class="text-sm font-medium text-blue-900 dark:text-blue-300">Instructions</label>
                                <p class="text-sm text-blue-800 dark:text-blue-200 mt-1">
                                    {{ $inspection->template->instructions }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Test Results Form -->
                <div class="lg:col-span-2">
                    <form action="{{ route('qc.inspections.update', $inspection) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Test Results</h2>

                            @if ($inspection->template && $inspection->template->test_parameters)
                                <!-- Template-based testing -->
                                <div id="test-results-container">
                                    @foreach ($inspection->template->test_parameters as $index => $parameter)
                                        <div class="mb-4 p-4 border dark:border-gray-700 rounded-lg"
                                            data-parameter-index="{{ $index }}">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label
                                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                        {{ $parameter['name'] }}
                                                        @if ($parameter['critical'] ?? false)
                                                            <span class="text-red-500">*</span>
                                                        @endif
                                                    </label>
                                                    <input type="hidden"
                                                        name="test_results[{{ $index }}][parameter]"
                                                        value="{{ $parameter['name'] }}">
                                                    <input type="number" step="any"
                                                        name="test_results[{{ $index }}][value]"
                                                        class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100"
                                                        placeholder="Enter value">
                                                </div>

                                                <div>
                                                    <label
                                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                        Range: {{ $parameter['min'] ?? '∞' }} -
                                                        {{ $parameter['max'] ?? '∞' }} {{ $parameter['unit'] ?? '' }}
                                                    </label>
                                                    <input type="hidden" name="test_results[{{ $index }}][min]"
                                                        value="{{ $parameter['min'] ?? '' }}">
                                                    <input type="hidden" name="test_results[{{ $index }}][max]"
                                                        value="{{ $parameter['max'] ?? '' }}">
                                                </div>
                                            </div>

                                            <div class="mt-2">
                                                <input type="text" name="test_results[{{ $index }}][notes]"
                                                    class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100"
                                                    placeholder="Notes (optional)">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <!-- Manual testing -->
                                <div id="manual-test-container">
                                    <div class="mb-4 p-4 border dark:border-gray-700 rounded-lg manual-test-row">
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div>
                                                <label
                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Parameter
                                                    Name</label>
                                                <input type="text" name="test_results[0][parameter]"
                                                    class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Value</label>
                                                <input type="number" step="any" name="test_results[0][value]"
                                                    class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                                                <input type="text" name="test_results[0][notes]"
                                                    class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <button type="button" onclick="addManualTestRow()"
                                    class="mb-4 text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                    <i class="fas fa-plus mr-1"></i>Add Test Parameter
                                </button>
                            @endif

                            <!-- Inspector Notes -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Inspector
                                    Notes</label>
                                <textarea name="inspector_notes" rows="3"
                                    class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">{{ old('inspector_notes', $inspection->inspector_notes) }}</textarea>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-3">
                                <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex-1">
                                    <i class="fas fa-save mr-2"></i>Save Results
                                </button>
                            </div>
                        </div>
                    </form>

                    @if ($inspection->status == 'in_progress' || $inspection->status == 'pending')
                        <!-- Decision Buttons -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mt-6">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Inspection Decision
                            </h2>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Pass -->
                                <form action="{{ route('qc.inspections.pass', $inspection) }}" method="POST">
                                    @csrf
                                    <div class="p-4 border-2 border-green-300 dark:border-green-700 rounded-lg">
                                        <i class="fas fa-check-circle text-3xl text-green-500 mb-2"></i>
                                        <h3 class="font-semibold text-green-700 dark:text-green-400">Pass</h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">All tests passed</p>
                                        <input type="text" name="notes" placeholder="Optional notes"
                                            class="w-full mb-2 rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 text-sm">
                                        <button type="submit"
                                            class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                                            Mark as Passed
                                        </button>
                                    </div>
                                </form>

                                <!-- Conditional Pass -->
                                <form action="{{ route('qc.inspections.conditional-pass', $inspection) }}"
                                    method="POST">
                                    @csrf
                                    <div class="p-4 border-2 border-yellow-300 dark:border-yellow-700 rounded-lg">
                                        <i class="fas fa-exclamation-triangle text-3xl text-yellow-500 mb-2"></i>
                                        <h3 class="font-semibold text-yellow-700 dark:text-yellow-400">Conditional Pass
                                        </h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">Minor issues found</p>
                                        <textarea name="notes" placeholder="Conditions and notes *" required rows="2"
                                            class="w-full mb-2 rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 text-sm"></textarea>
                                        <button type="submit"
                                            class="w-full bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg">
                                            Conditional Pass
                                        </button>
                                    </div>
                                </form>

                                <!-- Fail -->
                                <form action="{{ route('qc.inspections.fail', $inspection) }}" method="POST">
                                    @csrf
                                    <div class="p-4 border-2 border-red-300 dark:border-red-700 rounded-lg">
                                        <i class="fas fa-times-circle text-3xl text-red-500 mb-2"></i>
                                        <h3 class="font-semibold text-red-700 dark:text-red-400">Fail</h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">Critical failures</p>
                                        <textarea name="corrective_action" placeholder="Corrective action required *" required rows="2"
                                            class="w-full mb-2 rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 text-sm"></textarea>
                                        <input type="text" name="defects" placeholder="Defects found"
                                            class="w-full mb-2 rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 text-sm">
                                        <button type="submit"
                                            class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                                            Mark as Failed
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            let manualTestIndex = 1;

            function addManualTestRow() {
                const container = document.getElementById('manual-test-container');
                const newRow = document.createElement('div');
                newRow.className = 'mb-4 p-4 border dark:border-gray-700 rounded-lg manual-test-row';
                newRow.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Parameter Name</label>
                    <input type="text" name="test_results[${manualTestIndex}][parameter]" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Value</label>
                    <input type="number" step="any" name="test_results[${manualTestIndex}][value]" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                    <input type="text" name="test_results[${manualTestIndex}][notes]" class="w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                </div>
            </div>
        `;
                container.appendChild(newRow);
                manualTestIndex++;
            }
        </script>
    @endpush
</x-app-layout>
