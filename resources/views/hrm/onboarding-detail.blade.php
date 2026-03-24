<x-app-layout>
    <x-slot name="header">Onboarding — {{ $onboarding->employee->name }}</x-slot>

    @php
    $pct = $onboarding->progressPercent();
    $tasksByCategory = $onboarding->tasks->groupBy('category');
    @endphp

    {{-- Header card --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ $onboarding->employee->name }}</h2>
                    <span id="ob-status-badge" class="px-2 py-0.5 rounded-full text-xs {{ $onboarding->status === 'completed' ? 'bg-green-500/20 text-green-400' : 'bg-blue-500/20 text-blue-400' }}">
                        {{ $onboarding->status === 'completed' ? 'Selesai' : 'Berjalan' }}
                    </span>
                </div>
                <p class="text-sm text-gray-500 dark:text-slate-400">
                    {{ $onboarding->employee->position ?? '-' }} · {{ $onboarding->employee->department ?? '-' }}
                    · Mulai: {{ $onboarding->start_date->format('d M Y') }}
                </p>
            </div>
            <div class="text-center shrink-0">
                <p id="ob-pct" class="text-3xl font-bold {{ $pct >= 100 ? 'text-green-400' : 'text-blue-400' }}">{{ $pct }}%</p>
                <div class="w-40 h-2.5 bg-gray-200 dark:bg-white/10 rounded-full mt-2">
                    <div id="ob-bar" class="h-2.5 rounded-full {{ $pct >= 100 ? 'bg-green-500' : 'bg-blue-500' }} transition-all" style="width:{{ $pct }}%"></div>
                </div>
                <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">
                    {{ $onboarding->tasks->where('is_done', true)->count() }}/{{ $onboarding->tasks->count() }} tugas selesai
                </p>
            </div>
        </div>
    </div>

    <a href="{{ route('hrm.onboarding') }}" class="text-sm text-blue-500 hover:underline mb-4 inline-block">← Kembali</a>

    {{-- Tasks by category --}}
    <div class="space-y-4 mt-4">
        @foreach($tasksByCategory as $category => $tasks)
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 dark:bg-white/5 border-b border-gray-100 dark:border-white/10">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-slate-400">
                    {{ $category ?? 'Umum' }}
                    <span class="ml-2 font-normal normal-case">
                        ({{ $tasks->where('is_done', true)->count() }}/{{ $tasks->count() }})
                    </span>
                </p>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-white/5">
                @foreach($tasks->sortBy('sort_order') as $task)
                <div id="task-row-{{ $task->id }}" class="flex items-start gap-3 px-4 py-3 {{ $task->is_done ? 'opacity-60' : '' }}">
                    <button onclick="toggleTask({{ $task->id }})"
                        class="mt-0.5 shrink-0 w-5 h-5 rounded-full border-2 flex items-center justify-center transition
                               {{ $task->is_done ? 'bg-green-500 border-green-500' : 'border-gray-300 dark:border-white/30 hover:border-blue-500' }}">
                        @if($task->is_done)
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        @endif
                    </button>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-900 dark:text-white {{ $task->is_done ? 'line-through' : '' }}">
                            {{ $task->task }}
                            @if($task->required)<span class="text-red-400 text-xs ml-1">*</span>@endif
                        </p>
                        <div class="flex flex-wrap gap-2 mt-0.5">
                            <span class="text-xs text-gray-400 dark:text-slate-500">Hari ke-{{ $task->due_day }}</span>
                            @if($task->is_done && $task->done_at)
                            <span class="text-xs text-green-500">✓ {{ $task->done_at->format('d M Y') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

    @push('scripts')
    <script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    async function toggleTask(taskId) {
        const row = document.getElementById('task-row-' + taskId);
        const btn = row.querySelector('button');

        try {
            const res  = await fetch('/hrm/onboarding/tasks/' + taskId + '/toggle', {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            });
            const data = await res.json();

            // Update row UI
            const isDone = data.is_done;
            row.classList.toggle('opacity-60', isDone);
            const taskText = row.querySelector('p.text-sm');
            taskText.classList.toggle('line-through', isDone);

            btn.className = 'mt-0.5 shrink-0 w-5 h-5 rounded-full border-2 flex items-center justify-center transition '
                + (isDone ? 'bg-green-500 border-green-500' : 'border-gray-300 dark:border-white/30 hover:border-blue-500');
            btn.innerHTML = isDone
                ? '<svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>'
                : '';

            // Update progress bar
            const pct = data.progress;
            document.getElementById('ob-pct').textContent = pct + '%';
            document.getElementById('ob-bar').style.width = pct + '%';
            const isComplete = data.status === 'completed';
            document.getElementById('ob-pct').className = 'text-3xl font-bold ' + (isComplete ? 'text-green-400' : 'text-blue-400');
            document.getElementById('ob-bar').className = 'h-2.5 rounded-full transition-all ' + (isComplete ? 'bg-green-500' : 'bg-blue-500');
            const badge = document.getElementById('ob-status-badge');
            badge.textContent = isComplete ? 'Selesai' : 'Berjalan';
            badge.className = 'px-2 py-0.5 rounded-full text-xs ' + (isComplete ? 'bg-green-500/20 text-green-400' : 'bg-blue-500/20 text-blue-400');
        } catch(e) {
            console.error(e);
        }
    }
    </script>
    @endpush
</x-app-layout>
