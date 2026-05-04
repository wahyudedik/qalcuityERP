<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('QC Inspection Details') }} - {{ $inspection->inspection_number }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $inspection->inspection_number }}
                    </h1>
                    <p class="text-sm text-gray-600">{{ $inspection->stage_label }}</p>
                </div>
                <div class="flex gap-2">
                    @if ($inspection->status == 'pending' || $inspection->status == 'in_progress')
                        <a href="{{ route('qc.inspections.edit', $inspection) }}"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-edit mr-2"></i>Record Results
                        </a>
                    @endif
                    <a href="{{ route('qc.inspections.index') }}"
                        class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded-lg">
                        <i class="fas fa-arrow-left mr-2"></i>Back to List
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Inspection Details -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Inspection Information</h2>

                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-gray-600">Inspection Number</label>
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ $inspection->inspection_number }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-600">Status</label>
                                <p class="text-sm font-medium">
                                    <span
                                        class="px-2 py-1 rounded 
                                        {{ $inspection->status_color == 'green' ? 'bg-green-100 text-green-700' : '' }}
                                        {{ $inspection->status_color == 'red' ? 'bg-red-100 text-red-700' : '' }}
                                        {{ $inspection->status_color == 'yellow' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                        {{ $inspection->status_color == 'blue' ? 'bg-blue-100 text-blue-700' : '' }}">
                                        {{ str_replace('_', ' ', ucfirst($inspection->status)) }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-gray-600">Work Order</label>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $inspection->workOrder?->number ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-600">Stage</label>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $inspection->stage_label }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-gray-600">Template</label>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $inspection->template?->name ?? 'Manual Inspection' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-600">Inspector</label>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $inspection->inspector?->name ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="text-sm text-gray-600">Sample Size</label>
                                <p class="text-lg font-bold text-gray-900">
                                    {{ $inspection->sample_size }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-600">Passed</label>
                                <p class="text-lg font-bold text-green-600">{{ $inspection->sample_passed }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-600">Failed</label>
                                <p class="text-lg font-bold text-red-600">{{ $inspection->sample_failed }}</p>
                            </div>
                        </div>

                        @if ($inspection->pass_rate !== null)
                            <div>
                                <label class="text-sm text-gray-600">Pass Rate</label>
                                <div class="flex items-center gap-3 mt-1">
                                    <div class="flex-1 bg-gray-200 rounded-full h-4">
                                        <div class="h-4 rounded-full {{ $inspection->pass_rate >= 95 ? 'bg-green-500' : ($inspection->pass_rate >= 85 ? 'bg-yellow-500' : 'bg-red-500') }}"
                                            style="width: {{ $inspection->pass_rate }}%"></div>
                                    </div>
                                    <span
                                        class="text-lg font-bold {{ $inspection->pass_rate >= 95 ? 'text-green-600' : ($inspection->pass_rate >= 85 ? 'text-yellow-600' : 'text-red-600') }}">
                                        {{ $inspection->pass_rate }}%
                                    </span>
                                </div>
                            </div>
                        @endif

                        @if ($inspection->grade)
                            <div>
                                <label class="text-sm text-gray-600">Grade</label>
                                <p
                                    class="text-2xl font-bold 
                                {{ $inspection->grade == 'A' ? 'text-green-600' : '' }}
                                {{ $inspection->grade == 'B' ? 'text-blue-600' : '' }}
                                {{ $inspection->grade == 'C' ? 'text-yellow-600' : '' }}
                                {{ $inspection->grade == 'D' ? 'text-orange-600' : '' }}
                                {{ $inspection->grade == 'F' ? 'text-red-600' : '' }}">
                                    {{ $inspection->grade }}
                                </p>
                            </div>
                        @endif

                        @if ($inspection->inspected_at)
                            <div>
                                <label class="text-sm text-gray-600">Inspected At</label>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $inspection->inspected_at->format('Y-m-d H:i:s') }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Test Results -->
                @if ($inspection->test_results)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Test Results</h2>

                        <div class="space-y-3 max-h-96 overflow-y-auto">
                            @foreach ($inspection->test_results as $result)
                                <div class="p-3 border rounded-lg">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900">
                                                {{ $result['parameter'] }}</h3>
                                            <p class="text-sm text-gray-600">
                                                Value: <span class="font-medium">{{ $result['value'] }}</span>
                                                @if (isset($result['unit']))
                                                    {{ $result['unit'] }}
                                                @endif
                                            </p>
                                            @if (isset($result['notes']) && $result['notes'])
                                                <p class="text-xs text-gray-500 mt-1">Notes:
                                                    {{ $result['notes'] }}</p>
                                            @endif
                                        </div>
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded 
                                    {{ $result['passed'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                            {{ $result['passed'] ? 'PASSED' : 'FAILED' }}
                                        </span>
                                    </div>
                                    @if (isset($result['error']) && $result['error'])
                                        <p class="text-xs text-red-600 mt-2"><i
                                                class="fas fa-exclamation-triangle mr-1"></i>{{ $result['error'] }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            @if ($inspection->corrective_action || $inspection->defects_found || $inspection->inspector_notes)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Additional Notes</h2>

                    <div class="space-y-3">
                        @if ($inspection->inspector_notes)
                            <div>
                                <label class="text-sm font-medium text-gray-700">Inspector
                                    Notes</label>
                                <p class="text-sm text-gray-900 mt-1">
                                    {{ $inspection->inspector_notes }}</p>
                            </div>
                        @endif

                        @if ($inspection->defects_found)
                            <div>
                                <label class="text-sm font-medium text-red-700">Defects Found</label>
                                <p class="text-sm text-gray-900 mt-1">
                                    {{ $inspection->defects_found }}</p>
                            </div>
                        @endif

                        @if ($inspection->corrective_action)
                            <div>
                                <label class="text-sm font-medium text-orange-700">Corrective
                                    Action</label>
                                <p class="text-sm text-gray-900 mt-1">
                                    {{ $inspection->corrective_action }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
