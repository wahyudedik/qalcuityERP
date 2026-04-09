@extends('layouts.app')

@section('title', 'Defect Records')

@section('content')
    <div class="p-6">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Defect Records</h1>
            <p class="text-gray-600 mt-1">Track and resolve quality defects</p>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Severity</label>
                    <select name="severity" class="w-full rounded-md border-gray-300">
                        <option value="">All Severities</option>
                        <option value="critical" {{ request('severity') == 'critical' ? 'selected' : '' }}>Critical</option>
                        <option value="major" {{ request('severity') == 'major' ? 'selected' : '' }}>Major</option>
                        <option value="minor" {{ request('severity') == 'minor' ? 'selected' : '' }}>Minor</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full rounded-md border-gray-300">
                        <option value="">All Status</option>
                        <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                        <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Defect Type</label>
                    <select name="defect_type" class="w-full rounded-md border-gray-300">
                        <option value="">All Types</option>
                        <option value="cosmetic" {{ request('defect_type') == 'cosmetic' ? 'selected' : '' }}>Cosmetic
                        </option>
                        <option value="functional" {{ request('defect_type') == 'functional' ? 'selected' : '' }}>Functional
                        </option>
                        <option value="dimensional" {{ request('defect_type') == 'dimensional' ? 'selected' : '' }}>
                            Dimensional</option>
                        <option value="material" {{ request('defect_type') == 'material' ? 'selected' : '' }}>Material
                        </option>
                        <option value="other" {{ request('defect_type') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg w-full">Filter</button>
                </div>
            </form>
        </div>

        <!-- Defects Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Defect Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Severity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty Defected</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Disposition</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cost Impact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($defects as $defect)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $defect->defect_code }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $defect->product?->name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    {{ ucfirst($defect->defect_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
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
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">{{ $defect->quantity_defected }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ ucfirst(str_replace('_', ' ', $defect->disposition)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($defect->resolved_at)
                                    <span
                                        class="px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded">RESOLVED</span>
                                @else
                                    <span
                                        class="px-2 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded">OPEN</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">Rp
                                    {{ number_format($defect->cost_impact, 0, ',', '.') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                @if (!$defect->resolved_at)
                                    <button onclick="openResolveModal({{ $defect->id }}, '{{ $defect->defect_code }}')"
                                        class="text-blue-600 hover:text-blue-900">Resolve</button>
                                @else
                                    <span class="text-gray-500">Resolved</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                                No defects found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $defects->links() }}
        </div>
    </div>

    <!-- Resolve Defect Modal -->
    <div id="resolveModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Resolve Defect: <span id="defectCode"></span></h3>
                <form id="resolveForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Root Cause *</label>
                            <textarea name="root_cause" required rows="3" class="w-full rounded-md border-gray-300"
                                placeholder="What caused this defect?"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Corrective Action *</label>
                            <textarea name="corrective_action" required rows="3" class="w-full rounded-md border-gray-300"
                                placeholder="What action was taken to fix this?"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Preventive Action</label>
                            <textarea name="preventive_action" rows="3" class="w-full rounded-md border-gray-300"
                                placeholder="How to prevent this in the future?"></textarea>
                        </div>
                    </div>
                    <div class="flex gap-3 mt-6">
                        <button type="submit"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Resolve</button>
                        <button type="button" onclick="closeResolveModal()"
                            class="flex-1 bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openResolveModal(defectId, defectCode) {
            document.getElementById('defectCode').textContent = defectCode;
            document.getElementById('resolveForm').action = `/manufacturing/quality/defects/${defectId}/resolve`;
            document.getElementById('resolveModal').classList.remove('hidden');
        }

        function closeResolveModal() {
            document.getElementById('resolveModal').classList.add('hidden');
        }
    </script>
@endsection
