@extends('layouts.app')

@section('title', 'OOS Investigations')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <a href="{{ route('cosmetic.qc.tests') }}" class="text-blue-600 hover:text-blue-900 mb-2 inline-block">
                        ← Back to QC Tests
                    </a>
                    <h1 class="text-3xl font-bold text-gray-900">OOS Investigations</h1>
                    <p class="mt-1 text-sm text-gray-500">Out-of-Specification investigation management</p>
                </div>
                <button onclick="document.getElementById('add-oos-modal').classList.remove('hidden')"
                    class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-lg transition">
                    + New OOS
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Total OOS</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ $stats['total_oos'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Open</div>
                <div class="mt-2 text-2xl font-bold text-yellow-600">{{ $stats['open_oos'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Critical</div>
                <div class="mt-2 text-2xl font-bold text-red-600">{{ $stats['critical_oos'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">High Priority</div>
                <div class="mt-2 text-2xl font-bold text-orange-600">{{ $stats['high_oos'] }}</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" action="{{ route('cosmetic.qc.oos') }}" class="flex gap-4">
                <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Status</option>
                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                    <option value="investigating" {{ request('status') == 'investigating' ? 'selected' : '' }}>Investigating
                    </option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
                <select name="severity" class="px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Severity</option>
                    <option value="low" {{ request('severity') == 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ request('severity') == 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ request('severity') == 'high' ? 'selected' : '' }}>High</option>
                    <option value="critical" {{ request('severity') == 'critical' ? 'selected' : '' }}>Critical</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg">
                    Filter
                </button>
            </form>
        </div>

        <!-- OOS List -->
        <div class="space-y-4">
            @forelse($oosList as $oos)
                <div
                    class="bg-white rounded-lg shadow p-6 border-l-4
            @if ($oos->severity == 'critical') border-red-500
            @elseif($oos->severity == 'high') border-orange-500
            @elseif($oos->severity == 'medium') border-yellow-500
            @else border-blue-500 @endif">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <span class="text-lg font-semibold text-gray-900">{{ $oos->oos_number }}</span>
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-full
                            @if ($oos->severity == 'critical') bg-red-100 text-red-800
                            @elseif($oos->severity == 'high') bg-orange-100 text-orange-800
                            @elseif($oos->severity == 'medium') bg-yellow-100 text-yellow-800
                            @else bg-blue-100 text-blue-800 @endif">
                                    {{ $oos->severity_label }}
                                </span>
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-full
                            @if ($oos->status == 'completed') bg-green-100 text-green-800
                            @elseif($oos->status == 'investigating') bg-blue-100 text-blue-800
                            @elseif($oos->status == 'closed') bg-gray-100 text-gray-800
                            @else bg-yellow-100 text-yellow-800 @endif">
                                    {{ $oos->status_label }}
                                </span>
                            </div>
                            <div class="text-sm text-gray-600">
                                Type: {{ $oos->type_label }} |
                                Days Open: {{ $oos->days_open }}
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <strong class="text-sm text-gray-700">Description:</strong>
                            <p class="text-sm text-gray-900 mt-1">{{ $oos->description }}</p>
                        </div>
                        @if ($oos->root_cause)
                            <div>
                                <strong class="text-sm text-gray-700">Root Cause:</strong>
                                <p class="text-sm text-gray-900 mt-1">{{ $oos->root_cause }}</p>
                            </div>
                        @endif
                    </div>

                    @if ($oos->corrective_action || $oos->preventive_action)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 p-3 bg-gray-50 rounded">
                            @if ($oos->corrective_action)
                                <div>
                                    <strong class="text-sm text-gray-700">Corrective Action:</strong>
                                    <p class="text-sm text-gray-900 mt-1">{{ $oos->corrective_action }}</p>
                                </div>
                            @endif
                            @if ($oos->preventive_action)
                                <div>
                                    <strong class="text-sm text-gray-700">Preventive Action:</strong>
                                    <p class="text-sm text-gray-900 mt-1">{{ $oos->preventive_action }}</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="flex justify-between items-center text-sm text-gray-500">
                        <div>
                            @if ($oos->batch)
                                Batch: {{ $oos->batch->batch_number }} |
                            @endif
                            Discovered: {{ $oos->discovery_date->format('d M Y') }}
                        </div>
                        <div class="flex gap-2">
                            @if ($oos->status == 'open')
                                <form method="POST" action="{{ route('cosmetic.qc.oos.complete', $oos) }}" class="inline">
                                    @csrf
                                    <button type="submit"
                                        class="text-green-600 hover:text-green-900 font-medium">Complete</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow p-12 text-center text-gray-400">
                    <p class="text-lg">No OOS investigations found. Great job!</p>
                </div>
            @endforelse
        </div>

        @if ($oosList->hasPages())
            <div class="mt-4">{{ $oosList->links() }}</div>
        @endif
    </div>

    <!-- Add OOS Modal -->
    <div id="add-oos-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-[600px] shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-semibold mb-4">Create OOS Investigation</h3>
            <form method="POST" action="{{ route('cosmetic.qc.oos.store') }}">
                @csrf
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">OOS Type *</label>
                            <select name="oos_type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="laboratory">Laboratory</option>
                                <option value="manufacturing">Manufacturing</option>
                                <option value="stability">Stability</option>
                                <option value="complaint">Customer Complaint</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Severity *</label>
                            <select name="severity" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                        <textarea name="description" rows="3" required class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
                    </div>
                </div>
                <div class="mt-4 flex gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
                        Create OOS
                    </button>
                    <button type="button" onclick="document.getElementById('add-oos-modal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
