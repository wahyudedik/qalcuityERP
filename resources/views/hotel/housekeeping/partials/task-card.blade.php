@php
    $priorityColors = [
        'low' => 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-slate-400',
        'normal' => 'bg-blue-100 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400',
        'high' => 'bg-orange-100 text-orange-600 dark:bg-orange-500/20 dark:text-orange-400',
        'urgent' => 'bg-red-100 text-red-600 dark:bg-red-500/20 dark:text-red-400',
    ];

    $typeLabels = [
        'checkout_clean' => 'Checkout Clean',
        'stay_clean' => 'Stay Clean',
        'deep_clean' => 'Deep Clean',
        'inspection' => 'Inspection',
    ];

    $typeColors = [
        'checkout_clean' => 'bg-purple-100 text-purple-600 dark:bg-purple-500/20 dark:text-purple-400',
        'stay_clean' => 'bg-cyan-100 text-cyan-600 dark:bg-cyan-500/20 dark:text-cyan-400',
        'deep_clean' => 'bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400',
        'inspection' => 'bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400',
    ];
@endphp

<div class="bg-gray-50 dark:bg-white/5 rounded-xl p-3 border border-gray-100 dark:border-white/5">
    {{-- Header: Room Number --}}
    <div class="flex items-start justify-between mb-2">
        <div>
            <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $task->room?->number ?? 'N/A' }}</p>
            <p class="text-xs text-gray-500 dark:text-slate-400">Floor {{ $task->room?->floor ?? '-' }}</p>
        </div>
        <span
            class="px-2 py-0.5 text-xs font-medium rounded-full {{ $priorityColors[$task->priority] ?? $priorityColors['normal'] }}">
            {{ ucfirst($task->priority) }}
        </span>
    </div>

    {{-- Task Type Badge --}}
    <div class="mb-2">
        <span
            class="px-2 py-0.5 text-xs font-medium rounded-full {{ $typeColors[$task->type] ?? 'bg-gray-100 text-gray-600' }}">
            {{ $typeLabels[$task->type] ?? $task->type }}
        </span>
    </div>

    {{-- Assigned To --}}
    <p class="text-xs text-gray-500 dark:text-slate-400 mb-2">
        <span class="font-medium">Assigned:</span>
        {{ $task->assignedTo?->name ?? 'Unassigned' }}
    </p>

    {{-- Time Info --}}
    @if ($task->status === 'pending' || $task->status === 'assigned')
        <p class="text-xs text-gray-400 dark:text-slate-500">
            Scheduled: {{ $task->scheduled_at?->format('H:i') ?? '-' }}
        </p>
    @elseif($task->status === 'in_progress')
        <p class="text-xs text-blue-500">
            Started: {{ $task->started_at?->format('H:i') ?? '-' }}
        </p>
    @elseif($task->status === 'completed')
        <p class="text-xs text-green-500">
            Completed: {{ $task->completed_at?->format('H:i') ?? '-' }}
        </p>
    @endif

    {{-- Action Buttons --}}
    <div class="flex flex-wrap gap-1 mt-3 pt-2 border-t border-gray-100 dark:border-white/5">
        @if ($task->status === 'pending' || $task->status === 'assigned')
            {{-- Assign Button --}}
            <button @click="openAssignModal({{ $task->id }})"
                class="px-2 py-1 text-xs bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-slate-300 rounded-lg hover:bg-gray-200 dark:hover:bg-white/20">
                Assign
            </button>
            {{-- Start Button --}}
            <form method="POST" action="{{ route('hotel.housekeeping.tasks.start', $task->id) }}" class="inline">
                @csrf
                <button type="submit"
                    class="px-2 py-1 text-xs bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-500/30">
                    Start
                </button>
            </form>
        @elseif($task->status === 'in_progress')
            {{-- Complete Button --}}
            <form method="POST" action="{{ route('hotel.housekeeping.tasks.complete', $task->id) }}" class="inline">
                @csrf
                <button type="submit"
                    class="px-2 py-1 text-xs bg-green-100 dark:bg-green-500/20 text-green-600 dark:text-green-400 rounded-lg hover:bg-green-200 dark:hover:bg-green-500/30">
                    Complete
                </button>
            </form>
        @endif
    </div>

    {{-- Notes --}}
    @if ($task->notes)
        <p class="text-xs text-gray-400 dark:text-slate-500 mt-2 italic">"{{ Str::limit($task->notes, 50) }}"</p>
    @endif
</div>
