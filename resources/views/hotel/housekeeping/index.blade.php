<x-app-layout title="Housekeeping Dashboard">
    <x-slot name="header">Housekeeping Dashboard</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <button onclick="openMaintenanceModal()"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-medium transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Report Maintenance
                </button>
        <a href="{{ route('hotel.housekeeping.tasks.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    View Tasks
                </a>
    </div>

    <div class="space-y-6">
        {{-- Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Rooms Stats --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-900">Rooms Status</h3>
                    <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-600">Clean</span>
                        <span
                            class="text-sm font-semibold text-green-600">{{ $stats['rooms']['clean'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-600">Dirty</span>
                        <span
                            class="text-sm font-semibold text-orange-600">{{ $stats['rooms']['dirty'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-600">Inspected</span>
                        <span
                            class="text-sm font-semibold text-blue-600">{{ $stats['rooms']['inspected'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-600">Out of Order</span>
                        <span
                            class="text-sm font-semibold text-red-600">{{ $stats['rooms']['out_of_order'] ?? 0 }}</span>
                    </div>
                </div>
            </div>

            {{-- Task Stats --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-900">Tasks</h3>
                    <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-600">Pending</span>
                        <span
                            class="text-sm font-semibold text-orange-600">{{ $stats['tasks']['pending'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-600">In Progress</span>
                        <span
                            class="text-sm font-semibold text-blue-600">{{ $stats['tasks']['in_progress'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-600">Completed Today</span>
                        <span
                            class="text-sm font-semibold text-green-600">{{ $stats['tasks']['completed_today'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-600">Overdue</span>
                        <span
                            class="text-sm font-semibold text-red-600">{{ $stats['tasks']['overdue'] ?? 0 }}</span>
                    </div>
                </div>
            </div>

            {{-- Maintenance Stats --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-900">Maintenance</h3>
                    <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-600">Pending</span>
                        <span
                            class="text-sm font-semibold text-orange-600">{{ $stats['maintenance']['pending'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-600">Urgent</span>
                        <span
                            class="text-sm font-semibold text-red-600">{{ $stats['maintenance']['urgent'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-600">Overdue</span>
                        <span
                            class="text-sm font-semibold text-red-600">{{ $stats['maintenance']['overdue'] ?? 0 }}</span>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Quick Actions</h3>
                <div class="space-y-2">
                    <a href="{{ route('hotel.housekeeping.room-board') }}"
                        class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50 transition">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        <span class="text-sm text-gray-700">Room Board</span>
                    </a>
                    <a href="{{ route('hotel.housekeeping.linen.index') }}"
                        class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50 transition">
                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <span class="text-sm text-gray-700">Linen Inventory</span>
                    </a>
                    <a href="{{ route('hotel.housekeeping.supplies.index') }}"
                        class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50 transition">
                        <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <span class="text-sm text-gray-700">Supplies</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Room Status Overview --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Rooms by Status</h2>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @foreach (['dirty', 'clean', 'inspected', 'out_of_order'] as $status)
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-medium text-gray-700 capitalize">
                                {{ ucfirst($status) }} Rooms</h3>
                            <span
                                class="text-xs px-2 py-1 rounded-full {{ $status === 'dirty' ? 'bg-orange-100 text-orange-700' : ($status === 'clean' ? 'bg-green-100 text-green-700' : ($status === 'inspected' ? 'bg-blue-100 text-blue-700' : 'bg-red-100 text-red-700')) }}">
                                {{ count($rooms[$status] ?? []) }}
                            </span>
                        </div>
                        <div class="space-y-2">
                            @forelse($rooms[$status] ?? [] as $room)
                                <div
                                    class="flex items-center justify-between p-2 rounded-lg bg-gray-50">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Room
                                            {{ $room->number }}</p>
                                        <p class="text-xs text-gray-600">
                                            {{ $room->roomType?->name ?? 'N/A' }}</p>
                                    </div>
                                    <button onclick="updateRoomStatus({{ $room->id }}, 'clean')"
                                        class="text-xs px-3 py-1 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
                                        Mark Clean
                                    </button>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500 text-center py-4">No rooms</p>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Pending Tasks --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Pending Tasks</h2>
                <a href="{{ route('hotel.housekeeping.tasks.index') }}"
                    class="text-sm text-blue-600 hover:underline">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Room</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Type</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Priority</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Assigned To</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($pendingTasks->take(5) as $task)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $task->room?->number }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    {{ ucwords(str_replace('_', ' ', $task->type)) }}</td>
                                <td class="px-4 py-3">
                                    <span
                                        class="text-xs px-2 py-1 rounded-full {{ $task->priority === 'urgent' ? 'bg-red-100 text-red-700' : ($task->priority === 'high' ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-700') }}">
                                        {{ ucfirst($task->priority) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    {{ $task->assignedTo?->name ?? 'Unassigned' }}</td>
                                <td class="px-4 py-3">
                                    <button onclick="assignTask({{ $task->id }})"
                                        class="text-xs text-blue-600 hover:underline">Assign</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5"
                                    class="px-4 py-8 text-center text-sm text-gray-500">No pending
                                    tasks</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Maintenance Request Modal --}}
    <div id="modal-maintenance" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6">
            <form action="{{ route('hotel.housekeeping.maintenance.store') }}" method="POST">
                @csrf
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Report Maintenance Issue</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Room *</label>
                        <select name="room_id" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach ($rooms['dirty'] ?? [] as $room)
                                <option value="{{ $room->id }}">Room {{ $room->number }} -
                                    {{ $room->roomType?->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Title *</label>
                        <input type="text" name="title" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Brief description">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Category
                            *</label>
                        <select name="category" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="Plumbing">Plumbing</option>
                            <option value="Electrical">Electrical</option>
                            <option value="HVAC">HVAC</option>
                            <option value="Furniture">Furniture</option>
                            <option value="Appliances">Appliances</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Priority
                            *</label>
                        <select name="priority" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="low">Low</option>
                            <option value="normal" selected>Normal</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Description
                            *</label>
                        <textarea name="description" required rows="3"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Detailed description of the issue"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeMaintenanceModal()"
                        class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-xl">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-red-600 text-white rounded-xl hover:bg-red-700">Submit
                        Request</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function openMaintenanceModal() {
                document.getElementById('modal-maintenance').classList.remove('hidden');
            }

            function closeMaintenanceModal() {
                document.getElementById('modal-maintenance').classList.add('hidden');
            }

            function updateRoomStatus(roomId, status) {
                if (confirm('Mark this room as ' + status + '?')) {
                    fetch(`/hotel/housekeeping/rooms/${roomId}/status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            status
                        })
                    }).then(response => {
                        if (response.ok) location.reload();
                    });
                }
            }

            function assignTask(taskId) {
                const staffId = prompt('Enter staff user ID to assign:');
                if (staffId) {
                    fetch(`/hotel/housekeeping/tasks/${taskId}/assign`, {
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
        </script>
    @endpush
</x-app-layout>
