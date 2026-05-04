<x-app-layout>
    <x-slot name="header">{{ __('QC Inspections') }}</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('qc.inspections.create') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-plus mr-2"></i>New Inspection
            </a>
    </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Total Inspections</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                        </div>
                        <i class="fas fa-clipboard-check text-3xl text-blue-500"></i>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Passed</p>
                            <p class="text-2xl font-bold text-green-600">{{ $stats['passed'] }}</p>
                        </div>
                        <i class="fas fa-check-circle text-3xl text-green-500"></i>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Failed</p>
                            <p class="text-2xl font-bold text-red-600">{{ $stats['failed'] }}</p>
                        </div>
                        <i class="fas fa-times-circle text-3xl text-red-500"></i>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Pass Rate</p>
                            <p class="text-2xl font-bold text-blue-600">{{ $stats['pass_rate'] }}%</p>
                        </div>
                        <i class="fas fa-chart-line text-3xl text-blue-500"></i>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 mb-6">
                <form method="GET" action="{{ route('qc.inspections.index') }}"
                    class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status"
                            class="w-full rounded-md border-gray-300">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending
                            </option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In
                                Progress</option>
                            <option value="passed" {{ request('status') == 'passed' ? 'selected' : '' }}>Passed</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed
                            </option>
                            <option value="conditional_pass"
                                {{ request('status') == 'conditional_pass' ? 'selected' : '' }}>Conditional Pass
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Stage</label>
                        <select name="stage"
                            class="w-full rounded-md border-gray-300">
                            <option value="">All Stages</option>
                            <option value="incoming" {{ request('stage') == 'incoming' ? 'selected' : '' }}>Incoming
                            </option>
                            <option value="in-process" {{ request('stage') == 'in-process' ? 'selected' : '' }}>
                                In-Process</option>
                            <option value="final" {{ request('stage') == 'final' ? 'selected' : '' }}>Final</option>
                            <option value="random" {{ request('stage') == 'random' ? 'selected' : '' }}>Random</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Work
                            Order</label>
                        <select name="work_order_id"
                            class="w-full rounded-md border-gray-300">
                            <option value="">All Work Orders</option>
                            @foreach ($workOrders as $wo)
                                <option value="{{ $wo->id }}"
                                    {{ request('work_order_id') == $wo->id ? 'selected' : '' }}>
                                    {{ $wo->number }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                            class="w-full rounded-md border-gray-300">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                            class="w-full rounded-md border-gray-300">
                    </div>

                    <div class="md:col-span-5 flex justify-end gap-2">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-filter mr-2"></i>Apply Filters
                        </button>
                        <a href="{{ route('qc.inspections.index') }}"
                            class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded-lg">
                            <i class="fas fa-undo mr-2"></i>Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Inspections Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Inspection #</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Work Order</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Stage</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Sample</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Pass Rate</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Grade</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Date</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($inspections as $inspection)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="text-sm font-medium text-blue-600">{{ $inspection->inspection_number }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $inspection->workOrder?->number ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded bg-gray-100 text-gray-700">
                                            {{ $inspection->stage_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $inspection->sample_passed }}/{{ $inspection->sample_size }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="text-sm font-semibold {{ $inspection->pass_rate >= 95 ? 'text-green-600' : ($inspection->pass_rate >= 85 ? 'text-yellow-600' : 'text-red-600') }}">
                                            {{ $inspection->pass_rate ?? 'N/A' }}%
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($inspection->grade)
                                            <span
                                                class="px-2 py-1 text-xs font-bold rounded 
                                            {{ $inspection->grade == 'A' ? 'bg-green-100 text-green-700' : '' }}
                                            {{ $inspection->grade == 'B' ? 'bg-blue-100 text-blue-700' : '' }}
                                            {{ $inspection->grade == 'C' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                            {{ $inspection->grade == 'D' ? 'bg-orange-100 text-orange-700' : '' }}
                                            {{ $inspection->grade == 'F' ? 'bg-red-100 text-red-700' : '' }}">
                                                {{ $inspection->grade }}
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded 
                                        {{ $inspection->status_color == 'green' ? 'bg-green-100 text-green-700' : '' }}
                                        {{ $inspection->status_color == 'red' ? 'bg-red-100 text-red-700' : '' }}
                                        {{ $inspection->status_color == 'yellow' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                        {{ $inspection->status_color == 'blue' ? 'bg-blue-100 text-blue-700' : '' }}
                                        {{ $inspection->status_color == 'gray' ? 'bg-gray-100 text-gray-700' : '' }}">
                                            {{ str_replace('_', ' ', ucfirst($inspection->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $inspection->created_at->format('Y-m-d H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex gap-2">
                                            <a href="{{ route('qc.inspections.show', $inspection) }}"
                                                class="text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if ($inspection->status == 'pending' || $inspection->status == 'in_progress')
                                                <a href="{{ route('qc.inspections.edit', $inspection) }}"
                                                    class="text-green-600 hover:text-green-800">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-12 text-center">
                                        <i
                                            class="fas fa-clipboard-list text-6xl text-gray-300 mb-4"></i>
                                        <p class="text-gray-500">No QC inspections found</p>
                                        <a href="{{ route('qc.inspections.create') }}"
                                            class="mt-4 inline-block text-blue-600 hover:text-blue-800">
                                            Create your first inspection
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $inspections->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
