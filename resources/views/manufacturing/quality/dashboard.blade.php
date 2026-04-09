@extends('layouts.app')

@section('title', 'Quality Control Dashboard')

@section('content')
    <div class="p-6">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Quality Control Dashboard</h1>
            <p class="text-gray-600 mt-1">Monitor quality checks and defect tracking</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Quality Checks Stats -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total QC Checks</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $statistics['quality_checks']['total'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <span
                        class="text-green-600 text-sm font-semibold">{{ number_format($statistics['quality_checks']['pass_rate'], 1) }}%
                        Pass Rate</span>
                </div>
            </div>

            <!-- Passed -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Passed</p>
                        <p class="text-3xl font-bold text-green-600">{{ $statistics['quality_checks']['passed'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Failed -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Failed</p>
                        <p class="text-3xl font-bold text-red-600">{{ $statistics['quality_checks']['failed'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Open Defects -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Open Defects</p>
                        <p class="text-3xl font-bold text-orange-600">{{ $statistics['defects']['open'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-red-600 text-sm font-semibold">{{ $statistics['defects']['critical'] }}
                        Critical</span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mb-6 flex gap-3">
            <a href="{{ route('manufacturing.quality.checks.create') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Quality Check
            </a>
            <a href="{{ route('manufacturing.quality.defects') }}"
                class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01" />
                </svg>
                View Defects
            </a>
            <a href="{{ route('manufacturing.quality.standards') }}"
                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                QC Standards
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Quality Checks -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Recent Quality Checks</h2>
                </div>
                <div class="p-6">
                    @if ($recentChecks->count() > 0)
                        <div class="space-y-3">
                            @foreach ($recentChecks->take(10) as $check)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                                    <div class="flex-1">
                                        <p class="font-semibold text-gray-900">{{ $check->check_number }}</p>
                                        <p class="text-sm text-gray-600">
                                            {{ $check->workOrder?->number ?? 'N/A' }} | {{ $check->stage }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        @if ($check->status === 'passed')
                                            <span
                                                class="px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded">PASSED</span>
                                        @elseif($check->status === 'failed')
                                            <span
                                                class="px-2 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded">FAILED</span>
                                        @elseif($check->status === 'conditional_pass')
                                            <span
                                                class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded">CONDITIONAL</span>
                                        @else
                                            <span
                                                class="px-2 py-1 bg-gray-100 text-gray-800 text-xs font-semibold rounded">PENDING</span>
                                        @endif
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ $check->inspected_at?->format('d/m/Y H:i') ?? 'Not inspected' }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-4">No quality checks yet</p>
                    @endif
                </div>
            </div>

            <!-- Open Defects -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Open Defects</h2>
                </div>
                <div class="p-6">
                    @if ($openDefects->count() > 0)
                        <div class="space-y-3">
                            @foreach ($openDefects->take(10) as $defect)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                                    <div class="flex-1">
                                        <p class="font-semibold text-gray-900">{{ $defect->defect_code }}</p>
                                        <p class="text-sm text-gray-600">
                                            {{ $defect->product?->name ?? 'N/A' }} | {{ $defect->defect_type }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        @if ($defect->severity === 'critical')
                                            <span
                                                class="px-2 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded">CRITICAL</span>
                                        @elseif($defect->severity === 'major')
                                            <span
                                                class="px-2 py-1 bg-orange-100 text-orange-800 text-xs font-semibold rounded">MAJOR</span>
                                        @else
                                            <span
                                                class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded">MINOR</span>
                                        @endif
                                        <p class="text-xs text-gray-500 mt-1">Qty: {{ $defect->quantity_defected }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-4">No open defects</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
