@php
    $priorityColors = [
        'low' => 'bg-gray-100 text-gray-600',
        'normal' => 'bg-blue-100 text-blue-600',
        'high' => 'bg-orange-100 text-orange-600',
        'urgent' => 'bg-red-100 text-red-600',
    ];

    $typeLabels = [
        'checkout_clean' => 'Checkout Clean',
        'stay_clean' => 'Stay Clean',
        'deep_clean' => 'Deep Clean',
        'inspection' => 'Inspection',
    ];

    $typeColors = [
        'checkout_clean' => 'bg-purple-100 text-purple-600',
        'stay_clean' => 'bg-cyan-100 text-cyan-600',
        'deep_clean' => 'bg-indigo-100 text-indigo-600',
        'inspection' => 'bg-amber-100 text-amber-600',
    ];
@endphp

<div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
    {{-- Header: Room Number --}}
    <div class="flex items-start justify-between mb-2">
        <div>
            <p class="text-lg font-bold text-gray-900">{{ $task->room?->number ?? 'N/A' }}</p>
            <p class="text-xs text-gray-500">Floor {{ $task->room?->floor ?? '-' }}</p>
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
    <p class="text-xs text-gray-500 mb-2">
        <span class="font-medium">Assigned:</span>
        {{ $task->assignedTo?->name ?? 'Unassigned' }}
    </p>

    {{-- Time Info --}}
    @if ($task->status === 'pending' || $task->status === 'assigned')
        <p class="text-xs text-gray-400">
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
    <div class="flex flex-wrap gap-1 mt-3 pt-2 border-t border-gray-100">
        @if ($task->status === 'pending' || $task->status === 'assigned')
            {{-- Assign Button --}}
            <button @click="openAssignModal({{ $task->id }})"
                class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200">
                Assign
            </button>
            {{-- Start Button --}}
            <form method="POST" action="{{ route('hotel.housekeeping.tasks.start', $task->id) }}" class="inline">
                @csrf
                <button type="submit"
                    class="px-2 py-1 text-xs bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200">
                    Start
                </button>
            </form>
        @elseif($task->status === 'in_progress')
            {{-- Complete Button --}}
            <form method="POST" action="{{ route('hotel.housekeeping.tasks.complete', $task->id) }}" class="inline">
                @csrf
                <button type="submit"
                    class="px-2 py-1 text-xs bg-green-100 text-green-600 rounded-lg hover:bg-green-200">
                    Complete
                </button>
            </form>
        @endif
    </div>

    {{-- Notes --}}
    @if ($task->notes)
        <p class="text-xs text-gray-400 mt-2 italic">"{{ Str::limit($task->notes, 50) }}"</p>
    @endif
</div>
