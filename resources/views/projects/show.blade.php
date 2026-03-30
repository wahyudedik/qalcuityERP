<x-app-layout>
    <x-slot name="header">{{ $project->name }}</x-slot>

    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 rounded-xl text-sm text-green-700 dark:text-green-400">{{ session('success') }}</div>
    @endif

    {{-- Billing & RAB Links --}}
    <div class="mb-4 flex items-center gap-2">
        <a href="{{ route('projects.rab', $project) }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">
            📐 RAB (Rencana Anggaran Biaya)
        </a>
        @canmodule('project_billing', 'view')
        <a href="{{ route('project-billing.show', $project) }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">
            💰 Project Billing
        </a>
        @endcanmodule
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Left: Info + Tasks ──────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Progress & Budget --}}
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Progress</p>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($project->progress,0) }}%</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Anggaran</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $project->budget > 0 ? 'Rp '.number_format($project->budget,0,',','.') : '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Realisasi</p>
                        <p class="text-sm font-semibold {{ $project->budget > 0 && $project->actual_cost > $project->budget ? 'text-red-500' : 'text-gray-900 dark:text-white' }}">
                            Rp {{ number_format($project->actual_cost,0,',','.') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Deadline</p>
                        <p class="text-sm font-semibold {{ $project->end_date && $project->end_date->isPast() && !in_array($project->status,['completed','cancelled']) ? 'text-red-500' : 'text-gray-900 dark:text-white' }}">
                            {{ $project->end_date?->format('d M Y') ?? '-' }}
                        </p>
                    </div>
                </div>
                <div class="w-full bg-gray-100 dark:bg-white/10 rounded-full h-3">
                    <div class="h-3 rounded-full {{ $project->progress >= 100 ? 'bg-green-500' : 'bg-blue-500' }} transition-all" style="width:{{ min(100,$project->progress) }}%"></div>
                </div>
                @if($project->budget > 0)
                <div class="mt-2 w-full bg-gray-100 dark:bg-white/10 rounded-full h-1.5">
                    @php $budgetPct = min(100, $project->budgetUsedPercent()); @endphp
                    <div class="h-1.5 rounded-full {{ $budgetPct >= 100 ? 'bg-red-500' : ($budgetPct >= 80 ? 'bg-amber-500' : 'bg-emerald-500') }} transition-all" style="width:{{ $budgetPct }}%"></div>
                </div>
                <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">Pemakaian anggaran: {{ $project->budgetUsedPercent() }}%</p>
                @endif
            </div>

            {{-- Tasks Kanban --}}
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Task ({{ $project->tasks->count() }})</h3>
                    <button onclick="document.getElementById('modal-add-task').classList.remove('hidden')"
                        class="px-3 py-1.5 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700">+ Task</button>
                </div>

                @php
                    $taskStatuses = ['todo'=>'Belum','in_progress'=>'Dikerjakan','review'=>'Review','done'=>'Selesai','cancelled'=>'Batal'];
                    $taskColors   = ['todo'=>'gray','in_progress'=>'blue','review'=>'purple','done'=>'green','cancelled'=>'red'];
                @endphp

                <div class="space-y-2">
                    @forelse($project->tasks->sortBy('status') as $task)
                    @php $tc = $taskColors[$task->status] ?? 'gray'; @endphp
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-white/5 group">
                        <select onchange="updateTaskStatus({{ $task->id }}, this.value)"
                            class="text-xs rounded-lg border-0 bg-{{ $tc }}-100 text-{{ $tc }}-700 dark:bg-{{ $tc }}-500/20 dark:text-{{ $tc }}-400 font-medium focus:ring-2 focus:ring-blue-500 cursor-pointer">
                            @foreach($taskStatuses as $v=>$l)
                            <option value="{{ $v }}" @selected($task->status===$v)>{{ $l }}</option>
                            @endforeach
                        </select>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white {{ $task->status==='done' ? 'line-through opacity-60' : '' }}">{{ $task->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-slate-400">
                                {{ $task->assignedTo?->name ?? 'Belum ditugaskan' }}
                                @if($task->due_date) · {{ $task->due_date->format('d M') }} @endif
                                · Bobot: {{ $task->weight }}
                            </p>
                            @if($task->isVolumeTracked())
                            <div class="mt-1.5">
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="text-gray-500 dark:text-slate-400">Volume:</span>
                                    <span class="font-mono font-medium {{ $task->volumeProgress() >= 100 ? 'text-green-600 dark:text-green-400' : 'text-blue-600 dark:text-blue-400' }}">
                                        {{ number_format($task->actual_volume, $task->actual_volume == (int)$task->actual_volume ? 0 : 1) }} / {{ number_format($task->target_volume, $task->target_volume == (int)$task->target_volume ? 0 : 1) }} {{ $task->volume_unit }}
                                    </span>
                                    <span class="text-gray-400">({{ $task->volumeProgress() }}%)</span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-white/10 rounded-full h-1.5 mt-1">
                                    <div class="h-1.5 rounded-full {{ $task->volumeProgress() >= 100 ? 'bg-green-500' : 'bg-blue-500' }} transition-all" style="width:{{ min(100, $task->volumeProgress()) }}%"></div>
                                </div>
                                @if($task->status !== 'done' && $task->status !== 'cancelled')
                                <form method="POST" action="{{ route('projects.tasks.volume', $task) }}" class="flex items-center gap-2 mt-1.5">
                                    @csrf
                                    <input type="number" name="volume" step="0.001" min="0.001" required placeholder="+vol" class="w-20 px-2 py-1 text-xs rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white">
                                    <input type="text" name="description" placeholder="Keterangan" class="flex-1 px-2 py-1 text-xs rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white">
                                    <button type="submit" class="px-2 py-1 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700">+</button>
                                </form>
                                @endif
                            </div>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('projects.tasks.destroy', $task) }}" onsubmit="return confirm('Hapus task?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="opacity-0 group-hover:opacity-100 text-red-400 hover:text-red-600 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </form>
                    </div>
                    @empty
                    <p class="text-center text-sm text-gray-400 dark:text-slate-500 py-6">Belum ada task. Tambahkan task pertama.</p>
                    @endforelse
                </div>
            </div>

            {{-- Expenses --}}
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Pengeluaran ({{ $project->expenses->count() }})</h3>
                    <button onclick="document.getElementById('modal-add-expense').classList.remove('hidden')"
                        class="px-3 py-1.5 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700">+ Catat</button>
                </div>
                <div class="space-y-2">
                    @forelse($project->expenses->sortByDesc('date') as $exp)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-white/5 text-sm">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $exp->description }}</p>
                            <p class="text-xs text-gray-500 dark:text-slate-400">{{ $exp->category }} · {{ $exp->date->format('d M Y') }}</p>
                        </div>
                        <span class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($exp->amount,0,',','.') }}</span>
                    </div>
                    @empty
                    <p class="text-center text-sm text-gray-400 dark:text-slate-500 py-4">Belum ada pengeluaran.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ── Right: Info Panel ───────────────────────────────── --}}
        <div class="space-y-6">
            {{-- Edit Project --}}
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Info Proyek</h3>
                <form method="POST" action="{{ route('projects.update', $project) }}" class="space-y-3">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama</label>
                        <input type="text" name="name" value="{{ $project->name }}" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Status</label>
                        <select name="status" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach($statusLabels as $v=>$l)
                            <option value="{{ $v }}" @selected($project->status===$v)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Deadline</label>
                        <input type="date" name="end_date" value="{{ $project->end_date?->format('Y-m-d') }}" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Anggaran (Rp)</label>
                        <input type="number" name="budget" value="{{ $project->budget }}" min="0" step="100000" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Catatan</label>
                        <textarea name="notes" rows="2" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">{{ $project->notes }}</textarea>
                    </div>
                    <button type="submit" class="w-full py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </form>
                <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('Hapus proyek ini?')" class="mt-3">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full py-2 text-sm border border-red-200 dark:border-red-500/30 text-red-500 rounded-xl hover:bg-red-50 dark:hover:bg-red-500/10">Hapus Proyek</button>
                </form>
            </div>

            {{-- Timesheets summary --}}
            @if($project->timesheets->count() > 0)
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Timesheet</h3>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($project->timesheets->sum('hours'),1) }} jam</p>
                <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">dari {{ $project->timesheets->count() }} entri</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Modal Add Task --}}
    <div id="modal-add-task" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Tambah Task</h3>
                <button onclick="document.getElementById('modal-add-task').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('projects.tasks.store', $project) }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama Task *</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Ditugaskan ke</label>
                        <select name="assigned_to" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih --</option>
                            @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Deadline</label>
                        <input type="date" name="due_date" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Bobot (1-100)</label>
                    <input type="number" name="weight" value="1" min="1" max="100" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                {{-- Volume Tracking --}}
                <div class="border-t border-gray-100 dark:border-white/10 pt-3">
                    <label class="flex items-center gap-2 cursor-pointer mb-2">
                        <input type="checkbox" id="toggle-volume" onchange="document.getElementById('volume-fields').classList.toggle('hidden')" class="rounded border-gray-300 dark:border-white/20 text-blue-600">
                        <span class="text-xs font-medium text-gray-600 dark:text-slate-400">📐 Track progress berdasarkan volume fisik</span>
                    </label>
                    <div id="volume-fields" class="hidden grid grid-cols-3 gap-3">
                        <input type="hidden" name="progress_method" value="status" id="progress-method-input">
                        <div class="col-span-1">
                            <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Target Volume</label>
                            <input type="number" name="target_volume" step="0.001" placeholder="120" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                        </div>
                        <div class="col-span-1">
                            <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Satuan</label>
                            <input type="text" name="volume_unit" placeholder="m³, m², kg" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                        </div>
                        <div class="col-span-1 flex items-end">
                            <p class="text-[10px] text-gray-400 dark:text-slate-500 pb-2">Progress otomatis dihitung dari volume aktual vs target</p>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-add-task').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Tambah</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Add Expense --}}
    <div id="modal-add-expense" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Catat Pengeluaran</h3>
                <button onclick="document.getElementById('modal-add-expense').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('projects.expenses.store', $project) }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Deskripsi *</label>
                    <input type="text" name="description" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Kategori</label>
                        <input type="text" name="category" placeholder="Material, Jasa..." class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanggal</label>
                        <input type="date" name="date" value="{{ today()->format('Y-m-d') }}" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Jumlah (Rp) *</label>
                    <input type="number" name="amount" min="0" step="1000" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-add-expense').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    const TASK_STATUS_BASE = '{{ url("projects/tasks") }}/';
    async function updateTaskStatus(taskId, status) {
        const res = await fetch(TASK_STATUS_BASE + taskId + '/status', {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ status }),
        });
        if (res.ok) {
            const data = await res.json();
            document.querySelectorAll('[style*="width"]').forEach(el => {
                if (el.closest('.h-3')) el.style.width = Math.min(100, data.progress) + '%';
            });
        }
    }

    // Toggle volume tracking in add task modal
    document.getElementById('toggle-volume')?.addEventListener('change', function() {
        document.getElementById('progress-method-input').value = this.checked ? 'volume' : 'status';
    });
    </script>
    @endpush
</x-app-layout>
