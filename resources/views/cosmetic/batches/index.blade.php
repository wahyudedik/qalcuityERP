@extends('layouts.app')

@section('title', 'Batch Production Records')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Batch Production Records</h1>
                    <p class="mt-1 text-sm text-gray-500">Manage manufacturing batches and quality control</p>
                </div>
                <a href="{{ route('cosmetic.batches.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create Batch
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Total Batches</div>
                <div class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['total_batches'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">In Progress</div>
                <div class="mt-2 text-3xl font-bold text-blue-600">{{ $stats['in_progress'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">QC Pending</div>
                <div class="mt-2 text-3xl font-bold text-yellow-600">{{ $stats['qc_pending'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Released</div>
                <div class="mt-2 text-3xl font-bold text-green-600">{{ $stats['released'] }}</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" action="{{ route('cosmetic.batches.index') }}" class="flex gap-4">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search by batch number or formula..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">All Status</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress
                    </option>
                    <option value="qc_pending" {{ request('status') == 'qc_pending' ? 'selected' : '' }}>QC Pending</option>
                    <option value="released" {{ request('status') == 'released' ? 'selected' : '' }}>Released</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="on_hold" {{ request('status') == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                </select>
                @if ($formulas->count() > 0)
                    <select name="formula_id"
                        class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Formulas</option>
                        @foreach ($formulas as $formula)
                            <option value="{{ $formula->id }}"
                                {{ request('formula_id') == $formula->id ? 'selected' : '' }}>
                                {{ $formula->formula_name }}
                            </option>
                        @endforeach
                    </select>
                @endif
                <button type="submit"
                    class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition">
                    Filter
                </button>
            </form>
        </div>

        <!-- Batches Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Formula
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Yield
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Production Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($batches as $batch)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $batch->batch_number }}</div>
                                @if ($batch->producer)
                                    <div class="text-xs text-gray-500">By: {{ $batch->producer->name }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ $batch->formula->formula_name }}</div>
                                <div class="text-xs text-gray-500">{{ $batch->formula->formula_code }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-full
                            @if ($batch->status == 'draft') bg-gray-100 text-gray-800
                            @elseif($batch->status == 'in_progress') bg-blue-100 text-blue-800
                            @elseif($batch->status == 'qc_pending') bg-yellow-100 text-yellow-800
                            @elseif($batch->status == 'released') bg-green-100 text-green-800
                            @elseif($batch->status == 'rejected') bg-red-100 text-red-800
                            @else bg-orange-100 text-orange-800 @endif">
                                    {{ $batch->status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($batch->actual_quantity)
                                    <div class="text-sm text-gray-900">{{ number_format($batch->actual_quantity, 2) }}
                                    </div>
                                    <div class="text-xs text-gray-500">Planned:
                                        {{ number_format($batch->planned_quantity, 2) }}</div>
                                @else
                                    <div class="text-sm text-gray-500">{{ number_format($batch->planned_quantity, 2) }}
                                    </div>
                                    <div class="text-xs text-gray-400">Planned</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($batch->yield_percentage)
                                    <div
                                        class="text-sm font-medium
                                @if ($batch->yield_percentage >= 95) text-green-600
                                @elseif($batch->yield_percentage >= 90) text-yellow-600
                                @else text-red-600 @endif">
                                        {{ number_format($batch->yield_percentage, 1) }}%
                                    </div>
                                @else
                                    <div class="text-sm text-gray-400">-</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $batch->production_date->format('d M Y') }}
                                @if ($batch->expiry_date)
                                    <div
                                        class="text-xs {{ $batch->isExpired() ? 'text-red-600 font-medium' : 'text-gray-400' }}">
                                        Exp: {{ $batch->expiry_date->format('d M Y') }}
                                        @if ($batch->isExpired())
                                            ⚠️ Expired
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('cosmetic.batches.show', $batch) }}"
                                        class="text-blue-600 hover:text-blue-900">View</a>
                                    <form method="POST" action="{{ route('cosmetic.batches.destroy', $batch) }}"
                                        class="inline"
                                        onsubmit="return confirm('Are you sure you want to delete this batch?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="text-gray-400">
                                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                    <p class="mt-2 text-sm">No batch records found</p>
                                    <a href="{{ route('cosmetic.batches.create') }}"
                                        class="mt-2 inline-block text-blue-600 hover:text-blue-900">
                                        Create your first batch →
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($batches->hasPages())
            <div class="mt-4">
                {{ $batches->links() }}
            </div>
        @endif
    </div>
@endsection
