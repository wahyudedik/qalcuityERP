@extends('layouts.app')

@section('title', 'QC Laboratory')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">QC Laboratory</h1>
                    <p class="mt-1 text-sm text-gray-500">Quality control testing and certificates</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('cosmetic.qc.coa') }}"
                        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition">
                        COA Certificates
                    </a>
                    <a href="{{ route('cosmetic.qc.oos') }}"
                        class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-lg transition">
                        OOS Investigations
                    </a>
                    <button onclick="document.getElementById('add-test-modal').classList.remove('hidden')"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                        + Add Test
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Total Tests</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ $stats['total_tests'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Pending</div>
                <div class="mt-2 text-2xl font-bold text-yellow-600">{{ $stats['pending_tests'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Passed</div>
                <div class="mt-2 text-2xl font-bold text-green-600">{{ $stats['passed_tests'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Failed</div>
                <div class="mt-2 text-2xl font-bold text-red-600">{{ $stats['failed_tests'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Open OOS</div>
                <div class="mt-2 text-2xl font-bold text-orange-600">{{ $stats['open_oos'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Templates</div>
                <div class="mt-2 text-2xl font-bold text-blue-600">{{ $stats['active_templates'] }}</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" action="{{ route('cosmetic.qc.tests') }}" class="flex gap-4">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search by test code..." class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                </div>
                <select name="category" class="px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Categories</option>
                    <option value="microbial" {{ request('category') == 'microbial' ? 'selected' : '' }}>Microbial</option>
                    <option value="heavy_metal" {{ request('category') == 'heavy_metal' ? 'selected' : '' }}>Heavy Metal
                    </option>
                    <option value="preservative" {{ request('category') == 'preservative' ? 'selected' : '' }}>Preservative
                    </option>
                    <option value="patch_test" {{ request('category') == 'patch_test' ? 'selected' : '' }}>Patch Test
                    </option>
                    <option value="physical" {{ request('category') == 'physical' ? 'selected' : '' }}>Physical</option>
                    <option value="chemical" {{ request('category') == 'chemical' ? 'selected' : '' }}>Chemical</option>
                </select>
                <select name="result" class="px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Results</option>
                    <option value="pass" {{ request('result') == 'pass' ? 'selected' : '' }}>Passed</option>
                    <option value="fail" {{ request('result') == 'fail' ? 'selected' : '' }}>Failed</option>
                    <option value="pending" {{ request('result') == 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg">
                    Filter
                </button>
            </form>
        </div>

        <!-- Tests Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Test Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Batch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Result</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Test Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($tests as $test)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $test->test_code }}</div>
                                <div class="text-xs text-gray-500">{{ $test->test_name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-900">{{ $test->category_label }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if ($test->batch)
                                    <div class="text-sm text-gray-900">{{ $test->batch->batch_number }}</div>
                                @else
                                    <span class="text-sm text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-full
                            @if ($test->result == 'pass') bg-green-100 text-green-800
                            @elseif($test->result == 'fail') bg-red-100 text-red-800
                            @elseif($test->result == 'inconclusive') bg-yellow-100 text-yellow-800
                            @else bg-gray-100 text-gray-800 @endif">
                                    {{ $test->result_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-full
                            @if ($test->status == 'approved') bg-green-100 text-green-800
                            @elseif($test->status == 'completed') bg-blue-100 text-blue-800
                            @elseif($test->status == 'rejected') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800 @endif">
                                    {{ $test->status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $test->test_date->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('cosmetic.qc.tests.show', $test) }}"
                                        class="text-blue-600 hover:text-blue-900">View</a>
                                    @if ($test->status == 'draft')
                                        <form method="POST" action="{{ route('cosmetic.qc.tests.approve', $test) }}"
                                            class="inline">
                                            @csrf
                                            <button type="submit"
                                                class="text-green-600 hover:text-green-900">Approve</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                No QC tests found. Create your first test!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($tests->hasPages())
            <div class="mt-4">{{ $tests->links() }}</div>
        @endif
    </div>

    <!-- Add Test Modal -->
    <div id="add-test-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-[700px] shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-semibold mb-4">Add QC Test</h3>
            <form method="POST" action="{{ route('cosmetic.qc.tests.store') }}">
                @csrf
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Test Name *</label>
                            <input type="text" name="test_name" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                            <select name="test_category" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="microbial">Microbial Testing</option>
                                <option value="heavy_metal">Heavy Metal Testing</option>
                                <option value="preservative">Preservative Efficacy</option>
                                <option value="patch_test">Patch Test</option>
                                <option value="physical">Physical Testing</option>
                                <option value="chemical">Chemical Testing</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Batch</label>
                            <select name="batch_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="">None</option>
                                @foreach ($batches as $batch)
                                    <option value="{{ $batch->id }}">{{ $batch->batch_number }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Test Date *</label>
                            <input type="date" name="test_date" value="{{ date('Y-m-d') }}" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sample ID</label>
                        <input type="text" name="sample_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                </div>
                <div class="mt-4 flex gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Create Test
                    </button>
                    <button type="button" onclick="document.getElementById('add-test-modal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
