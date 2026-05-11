<x-app-layout title="Maintenance Requests">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Maintenance Requests</h1>
                <p class="mt-1 text-sm text-gray-600">Track and manage maintenance issues</p>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        {{-- Filters --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <form method="GET" class="flex flex-wrap gap-4">
                <select name="status" onchange="this.form.submit()"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    <option value="">All Status</option>
                    <option value="reported" {{ request('status') === 'reported' ? 'selected' : '' }}>Reported</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress
                    </option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed
                    </option>
                </select>

                <select name="priority" onchange="this.form.submit()"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    <option value="">All Priorities</option>
                    <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                    <option value="normal" {{ request('priority') === 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                </select>
            </form>
        </div>

        {{-- Requests Table --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Room</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Title</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Category</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Priority</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Assigned To</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($requests as $request)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                    {{ $request->room?->number }}</td>
                                <td class="px-4 py-3">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $request->title }}</p>
                                        <p class="text-xs text-gray-600">
                                            {{ $request->created_at->diffForHumans() }}</p>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    {{ $request->category }}</td>
                                <td class="px-4 py-3">
                                    <span
                                        class="text-xs px-2 py-1 rounded-full {{ $request->priority === 'urgent'
                                            ? 'bg-red-100 text-red-700'
                                            : ($request->priority === 'high'
                                                ? 'bg-orange-100 text-orange-700'
                                                : ($request->priority === 'normal'
                                                    ? 'bg-blue-100 text-blue-700'
                                                    : 'bg-gray-100 text-gray-700')) }}">
                                        {{ ucfirst($request->priority) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="text-xs px-2 py-1 rounded-full {{ $request->status === 'completed'
                                            ? 'bg-green-100 text-green-700'
                                            : ($request->status === 'in_progress'
                                                ? 'bg-blue-100 text-blue-700'
                                                : 'bg-yellow-100 text-yellow-700') }}">
                                        {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    {{ $request->assignedTo?->name ?? 'Unassigned' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        @if ($request->status === 'reported')
                                            <button onclick="assignRequest({{ $request->id }})"
                                                class="text-xs px-3 py-1 rounded-lg bg-blue-600 text-white hover:bg-blue-700">Assign</button>
                                        @elseif($request->status === 'in_progress')
                                            <button onclick="openCompleteModal({{ $request->id }})"
                                                class="text-xs px-3 py-1 rounded-lg bg-green-600 text-white hover:bg-green-700">Complete</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">No
                                    maintenance requests found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t border-gray-200">
                {{ $requests->links() }}
            </div>
        </div>
    </div>

    {{-- Complete Modal --}}
    <div id="modal-complete" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6">
            <form id="form-complete" method="POST">
                @csrf
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Complete Maintenance Request</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Resolution Notes
                            *</label>
                        <textarea name="resolution_notes" required rows="3"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900"
                            placeholder="Describe what was done to fix the issue"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Cost
                            (Optional)</label>
                        <input type="number" name="cost" step="0.01" min="0"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900"
                            placeholder="0.00">
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeCompleteModal()"
                        class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-xl">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">Complete</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            async function assignRequest(requestId) {
                const staffId = await Dialog.prompt('Enter technician user ID to assign:');
                if (staffId) {
                    fetch(`/hotel/housekeeping/maintenance/${requestId}/assign`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            assigned_to: staffId
                        })
                    }).then(response => {
                        if (response.ok) location.reload();
                    });
                }
            }

            function openCompleteModal(requestId) {
                document.getElementById('form-complete').action = `/hotel/housekeeping/maintenance/${requestId}/complete`;
                document.getElementById('modal-complete').classList.remove('hidden');
            }

            function closeCompleteModal() {
                document.getElementById('modal-complete').classList.add('hidden');
            }
        </script>
    @endpush
</x-app-layout>
