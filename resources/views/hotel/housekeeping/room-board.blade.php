<x-app-layout>
    <x-slot name="header">Housekeeping Board</x-slot>

    <div x-data="housekeepingBoard()" class="space-y-6">
        {{-- Header with New Task Button --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Housekeeping Board</h1>
                <p class="text-sm text-gray-500 dark:text-slate-400">Today's tasks •
                    {{ $board['pending']->count() + $board['in_progress']->count() + $board['completed']->count() + $board['inspected']->count() }}
                    total</p>
            </div>
            <button @click="showNewTaskModal = true"
                class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Task
            </button>
        </div>

        {{-- Kanban Board --}}
        <div class="overflow-x-auto pb-4">
            <div class="flex gap-4 min-w-max">
                {{-- Pending Column --}}
                <div class="w-72 flex-shrink-0">
                    <div
                        class="bg-amber-50 dark:bg-amber-500/10 rounded-t-2xl px-4 py-3 border-b border-amber-200 dark:border-amber-500/20">
                        <div class="flex items-center justify-between">
                            <h3 class="font-medium text-amber-800 dark:text-amber-300">Pending</h3>
                            <span
                                class="px-2 py-0.5 text-xs font-medium bg-amber-200 dark:bg-amber-500/20 text-amber-800 dark:text-amber-300 rounded-full">{{ $board['pending']->count() }}</span>
                        </div>
                    </div>
                    <div
                        class="bg-white dark:bg-[#1e293b] rounded-b-2xl border border-t-0 border-amber-200 dark:border-amber-500/20 p-3 space-y-3 min-h-[200px]">
                        @forelse($board['pending'] as $task)
                            @include('hotel.housekeeping.partials.task-card', ['task' => $task])
                        @empty
                            <div class="text-center py-8 text-gray-400 dark:text-slate-500 text-sm">
                                No pending tasks
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- In Progress Column --}}
                <div class="w-72 flex-shrink-0">
                    <div
                        class="bg-blue-50 dark:bg-blue-500/10 rounded-t-2xl px-4 py-3 border-b border-blue-200 dark:border-blue-500/20">
                        <div class="flex items-center justify-between">
                            <h3 class="font-medium text-blue-800 dark:text-blue-300">In Progress</h3>
                            <span
                                class="px-2 py-0.5 text-xs font-medium bg-blue-200 dark:bg-blue-500/20 text-blue-800 dark:text-blue-300 rounded-full">{{ $board['in_progress']->count() }}</span>
                        </div>
                    </div>
                    <div
                        class="bg-white dark:bg-[#1e293b] rounded-b-2xl border border-t-0 border-blue-200 dark:border-blue-500/20 p-3 space-y-3 min-h-[200px]">
                        @forelse($board['in_progress'] as $task)
                            @include('hotel.housekeeping.partials.task-card', ['task' => $task])
                        @empty
                            <div class="text-center py-8 text-gray-400 dark:text-slate-500 text-sm">
                                No tasks in progress
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Completed Column --}}
                <div class="w-72 flex-shrink-0">
                    <div
                        class="bg-green-50 dark:bg-green-500/10 rounded-t-2xl px-4 py-3 border-b border-green-200 dark:border-green-500/20">
                        <div class="flex items-center justify-between">
                            <h3 class="font-medium text-green-800 dark:text-green-300">Completed</h3>
                            <span
                                class="px-2 py-0.5 text-xs font-medium bg-green-200 dark:bg-green-500/20 text-green-800 dark:text-green-300 rounded-full">{{ $board['completed']->count() }}</span>
                        </div>
                    </div>
                    <div
                        class="bg-white dark:bg-[#1e293b] rounded-b-2xl border border-t-0 border-green-200 dark:border-green-500/20 p-3 space-y-3 min-h-[200px]">
                        @forelse($board['completed'] as $task)
                            @include('hotel.housekeeping.partials.task-card', ['task' => $task])
                        @empty
                            <div class="text-center py-8 text-gray-400 dark:text-slate-500 text-sm">
                                No completed tasks
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Inspected Column --}}
                <div class="w-72 flex-shrink-0">
                    <div
                        class="bg-gray-100 dark:bg-white/5 rounded-t-2xl px-4 py-3 border-b border-gray-200 dark:border-white/10">
                        <div class="flex items-center justify-between">
                            <h3 class="font-medium text-gray-700 dark:text-slate-300">Inspected</h3>
                            <span
                                class="px-2 py-0.5 text-xs font-medium bg-gray-200 dark:bg-white/10 text-gray-700 dark:text-slate-300 rounded-full">{{ $board['inspected']->count() }}</span>
                        </div>
                    </div>
                    <div
                        class="bg-white dark:bg-[#1e293b] rounded-b-2xl border border-t-0 border-gray-200 dark:border-white/10 p-3 space-y-3 min-h-[200px]">
                        @forelse($board['inspected'] as $task)
                            @include('hotel.housekeeping.partials.task-card', ['task' => $task])
                        @empty
                            <div class="text-center py-8 text-gray-400 dark:text-slate-500 text-sm">
                                No inspected tasks
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- New Task Modal --}}
        <div x-show="showNewTaskModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
            @click.self="showNewTaskModal = false">
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl" @click.stop>
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                    <h3 class="font-semibold text-gray-900 dark:text-white">New Housekeeping Task</h3>
                    <button @click="showNewTaskModal = false"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
                </div>
                <form method="POST" action="{{ route('hotel.housekeeping.store') }}" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-2">Room *</label>
                        <select name="room_id" required
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select room</option>
                            @foreach ($rooms as $room)
                                <option value="{{ $room->id }}">Room {{ $room->number }} —
                                    {{ $room->roomType?->name ?? 'N/A' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-2">Task Type
                            *</label>
                        <select name="type" required
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach ($taskTypes as $type)
                                <option value="{{ $type }}">{{ ucfirst(str_replace('_', ' ', $type)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-2">Priority
                            *</label>
                        <select name="priority" required
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach ($priorities as $priority)
                                <option value="{{ $priority }}" @selected($priority === 'normal')>
                                    {{ ucfirst($priority) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-2">Assigned
                            To</label>
                        <select name="assigned_to"
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Unassigned</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-2">Notes</label>
                        <textarea name="notes" rows="2" placeholder="Optional notes..."
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showNewTaskModal = false"
                            class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Cancel</button>
                        <button type="submit"
                            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Create
                            Task</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Assign Modal --}}
        <div x-show="showAssignModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
            @click.self="showAssignModal = false">
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-sm shadow-xl" @click.stop>
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Assign Task</h3>
                    <button @click="showAssignModal = false"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
                </div>
                <form :action="assignUrl" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-2">Assign to
                            *</label>
                        <select name="assigned_to" required
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select staff</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showAssignModal = false"
                            class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Cancel</button>
                        <button type="submit"
                            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Assign</button>
                    </div>
                </form>
            </div>
        </div>
    </div>{{-- end x-data --}}

    {{-- Alpine.js Component - Must be before closing x-app-layout --}}
    <script>
        // Define housekeepingBoard component for Alpine.js
        window.housekeepingBoard = function() {
            return {
                showNewTaskModal: false,
                showAssignModal: false,
                assignUrl: '',

                openAssignModal(taskId) {
                    this.assignUrl = '{{ url('hotel/housekeeping/tasks') }}/' + taskId + '/assign';
                    this.showAssignModal = true;
                },
            }
        };
    </script>
</x-app-layout>
