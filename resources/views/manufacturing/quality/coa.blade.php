<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Certificate of Analysis</h2>
            <div class="flex gap-2">
                <a href="{{ route('manufacturing.quality.checks') }}"
                    class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">
                    Back to QC Checks
                </a>
                <a href="{{ route('manufacturing.quality.coa.print', $quality_check_id) }}" target="_blank"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                    Print COA
                </a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto">
        {{-- COA Document --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-8">
            {{-- Header --}}
            <div class="text-center border-b-2 border-gray-300 dark:border-white/20 pb-6 mb-6">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">CERTIFICATE OF ANALYSIS</h1>
                <p class="text-sm text-gray-600 dark:text-slate-400">COA Number:
                    <strong>{{ $coa['coa_number'] }}</strong>
                </p>
            </div>

            {{-- Product Information --}}
            <div class="mb-6">
                <h3
                    class="text-lg font-semibold text-gray-900 dark:text-white mb-3 border-b border-gray-200 dark:border-white/10 pb-2">
                    Product Information</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Product Name</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $coa['product']['name'] ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">SKU</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $coa['product']['sku'] ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Batch Number</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $coa['product']['batch_number'] ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Work Order</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $coa['work_order'] ?? 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Inspection Details --}}
            <div class="mb-6">
                <h3
                    class="text-lg font-semibold text-gray-900 dark:text-white mb-3 border-b border-gray-200 dark:border-white/10 pb-2">
                    Inspection Details</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">QC Check Number</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $coa['check_number'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Inspection Stage</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $coa['stage'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Inspection Date</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $coa['inspection_date'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Inspector</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $coa['inspector'] ?? 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Test Results --}}
            <div class="mb-6">
                <h3
                    class="text-lg font-semibold text-gray-900 dark:text-white mb-3 border-b border-gray-200 dark:border-white/10 pb-2">
                    Test Results</h3>
                @if ($coa['results'] && count($coa['results']) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-white/5">
                                <tr>
                                    <th
                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-slate-400">
                                        Parameter</th>
                                    <th
                                        class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-slate-400">
                                        Value</th>
                                    <th
                                        class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-slate-400">
                                        Min</th>
                                    <th
                                        class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-slate-400">
                                        Max</th>
                                    <th
                                        class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-slate-400">
                                        Unit</th>
                                    <th
                                        class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-slate-400">
                                        Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                                @foreach ($coa['results'] as $result)
                                    <tr>
                                        <td class="px-4 py-2 text-gray-900 dark:text-white">{{ $result['parameter'] }}
                                        </td>
                                        <td class="px-4 py-2 text-center text-gray-900 dark:text-white">
                                            {{ $result['value'] ?? 'N/A' }}</td>
                                        <td class="px-4 py-2 text-center text-gray-600 dark:text-slate-400">
                                            {{ $result['min_value'] ?? 'N/A' }}</td>
                                        <td class="px-4 py-2 text-center text-gray-600 dark:text-slate-400">
                                            {{ $result['max_value'] ?? 'N/A' }}</td>
                                        <td class="px-4 py-2 text-center text-gray-600 dark:text-slate-400">
                                            {{ $result['unit'] ?? '-' }}</td>
                                        <td class="px-4 py-2 text-center">
                                            @if (isset($result['passed']))
                                                <span
                                                    class="px-2 py-1 text-xs font-semibold rounded-full {{ $result['passed'] ? 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400' }}">
                                                    {{ $result['passed'] ? 'PASS' : 'FAIL' }}
                                                </span>
                                            @else
                                                <span
                                                    class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700 dark:bg-gray-500/20 dark:text-gray-400">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-slate-400 text-center py-4">No test results available</p>
                @endif
            </div>

            {{-- Summary --}}
            <div class="mb-6 p-4 bg-gray-50 dark:bg-white/5 rounded-lg">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Summary</h3>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Sample Size</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $coa['sample_size'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-green-600 dark:text-green-400">Passed</p>
                        <p class="text-lg font-bold text-green-600 dark:text-green-400">{{ $coa['summary']['passed'] }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-red-600 dark:text-red-400">Failed</p>
                        <p class="text-lg font-bold text-red-600 dark:text-red-400">{{ $coa['summary']['failed'] }}</p>
                    </div>
                </div>
                <div class="mt-3">
                    <p class="text-xs text-gray-500 dark:text-slate-400">Pass Rate</p>
                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                        {{ number_format($coa['summary']['pass_rate'], 1) }}%</p>
                </div>
            </div>

            {{-- Defects (if any) --}}
            @if ($coa['defects']->count() > 0)
                <div class="mb-6">
                    <h3
                        class="text-lg font-semibold text-gray-900 dark:text-white mb-3 border-b border-gray-200 dark:border-white/10 pb-2">
                        Defects Found</h3>
                    <div class="space-y-2">
                        @foreach ($coa['defects'] as $defect)
                            <div
                                class="p-3 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-sm font-medium text-red-800 dark:text-red-200">{{ $defect['code'] }}</span>
                                    <span
                                        class="px-2 py-1 text-xs font-semibold rounded-full {{ $defect['severity'] === 'critical' ? 'bg-red-600 text-white' : 'bg-orange-600 text-white' }}">
                                        {{ ucfirst($defect['severity']) }}
                                    </span>
                                </div>
                                <p class="text-xs text-red-700 dark:text-red-300 mt-1">{{ $defect['type'] }} -
                                    {{ $defect['quantity'] }} units</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Conclusion --}}
            <div
                class="mb-6 p-4 {{ $coa['status'] === 'Passed' ? 'bg-green-50 dark:bg-green-500/10 border-green-200 dark:border-green-500/20' : 'bg-yellow-50 dark:bg-yellow-500/10 border-yellow-200 dark:border-yellow-500/20' }} border rounded-lg">
                <h3
                    class="text-sm font-semibold {{ $coa['status'] === 'Passed' ? 'text-green-800 dark:text-green-200' : 'text-yellow-800 dark:text-yellow-200' }} mb-2">
                    Conclusion</h3>
                <p
                    class="text-sm {{ $coa['status'] === 'Passed' ? 'text-green-700 dark:text-green-300' : 'text-yellow-700 dark:text-yellow-300' }}">
                    {{ $coa['conclusion'] }}</p>
                <div class="mt-3">
                    <p
                        class="text-xs {{ $coa['status'] === 'Passed' ? 'text-green-600 dark:text-green-400' : 'text-yellow-600 dark:text-yellow-400' }}">
                        Status: <strong>{{ $coa['status'] }}</strong></p>
                </div>
            </div>

            {{-- Authorization --}}
            <div class="border-t-2 border-gray-300 dark:border-white/20 pt-6 mt-6">
                <div class="grid grid-cols-2 gap-8">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Authorized By</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">
                            {{ $coa['authorized_by'] ?? 'N/A' }}</p>
                        <div class="mt-8 border-t border-gray-300 dark:border-white/20 pt-2">
                            <p class="text-xs text-gray-500 dark:text-slate-400">Signature</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Date</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">{{ $coa['signature_date'] }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
