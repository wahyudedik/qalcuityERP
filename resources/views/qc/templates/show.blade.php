<x-app-layout>
    <x-slot name="header">{{ __('QC Test Template Details') }}</x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4 flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $template->name }}</h1>
                    <p class="text-sm text-gray-600">{{ $template->stage_label }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('qc.templates.edit', $template) }}"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-edit mr-2"></i>Edit
                    </a>
                    <a href="{{ route('qc.templates.index') }}"
                        class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded-lg">
                        <i class="fas fa-arrow-left mr-2"></i>Back to List
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Template Details -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Template Information</h2>

                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-gray-600">Template Name</label>
                                <p class="text-sm font-semibold text-gray-900">{{ $template->name }}
                                </p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-600">Status</label>
                                <p class="text-sm font-medium">
                                    <span
                                        class="px-2 py-1 rounded {{ $template->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                        {{ $template->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-gray-600">Product Type</label>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $template->product_type ?? 'All Types' }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-600">Stage</label>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $template->stage_label }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-gray-600">Sample Size Formula</label>
                                <p class="text-sm font-medium text-gray-900">
                                    @switch($template->sample_size_formula)
                                        @case(1)
                                            √n (Square Root)
                                        @break

                                        @case(2)
                                            10% of Lot Size
                                        @break

                                        @default
                                            5% of Lot Size (min 3)
                                    @endswitch
                                </p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-600">AQL</label>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $template->acceptance_quality_limit }}%</p>
                            </div>
                        </div>

                        @if ($template->instructions)
                            <div>
                                <label class="text-sm text-gray-600">Instructions</label>
                                <p class="text-sm text-gray-900 mt-1 whitespace-pre-line">
                                    {{ $template->instructions }}</p>
                            </div>
                        @endif

                        <div class="grid grid-cols-2 gap-4 pt-2 border-t">
                            <div>
                                <label class="text-sm text-gray-600">Created</label>
                                <p class="text-sm text-gray-900">
                                    {{ $template->created_at->format('Y-m-d H:i') }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-600">Last Updated</label>
                                <p class="text-sm text-gray-900">
                                    {{ $template->updated_at->format('Y-m-d H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Test Parameters -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        Test Parameters
                        <span
                            class="text-sm font-normal text-gray-500">({{ is_array($template->test_parameters) ? count($template->test_parameters) : 0 }})</span>
                    </h2>

                    @if ($template->test_parameters && count($template->test_parameters) > 0)
                        <div class="space-y-3 max-h-96 overflow-y-auto">
                            @foreach ($template->test_parameters as $param)
                                <div class="p-3 border rounded-lg">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900">
                                                {{ $param['name'] ?? 'Unnamed' }}
                                                @if ($param['critical'] ?? false)
                                                    <span
                                                        class="ml-1 px-1.5 py-0.5 text-xs bg-red-100 text-red-700 rounded">Critical</span>
                                                @endif
                                            </h3>
                                            <p class="text-sm text-gray-600">
                                                Range: {{ $param['min'] ?? '∞' }} - {{ $param['max'] ?? '∞' }}
                                                @if (isset($param['unit']) && $param['unit'])
                                                    {{ $param['unit'] }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-sm">No test parameters defined.</p>
                    @endif
                </div>
            </div>

            <!-- Recent Inspections -->
            @if ($template->inspections && $template->inspections->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Inspections</h2>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Inspection #</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Work Order</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Stage</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Status</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($template->inspections as $inspection)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm">
                                        <a href="{{ route('qc.inspections.show', $inspection) }}"
                                            class="text-blue-600 hover:text-blue-800">
                                            {{ $inspection->inspection_number }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ $inspection->workOrder->number ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ $inspection->stage_label ?? $inspection->stage }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded
                                            {{ $inspection->status == 'passed' ? 'bg-green-100 text-green-700' : '' }}
                                            {{ $inspection->status == 'failed' ? 'bg-red-100 text-red-700' : '' }}
                                            {{ $inspection->status == 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                            {{ $inspection->status == 'in_progress' ? 'bg-blue-100 text-blue-700' : '' }}">
                                            {{ str_replace('_', ' ', ucfirst($inspection->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ $inspection->created_at->format('Y-m-d') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
