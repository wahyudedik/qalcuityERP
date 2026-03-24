<x-app-layout>
    <x-slot name="header">Manajemen Proyek</x-slot>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        @foreach([
            ['label'=>'Total Proyek','value'=>$stats['total'],'color'=>'text-gray-900 dark:text-white'],
            ['label'=>'Aktif','value'=>$stats['active'],'color'=>'text-blue-600 dark:text-blue-400'],
            ['label'=>'Selesai','value'=>$stats['completed'],'color'=>'text-green-600 dark:text-green-400'],
            ['label'=>'Terlambat','value'=>$stats['overdue'],'color'=>'text-red-600 dark:text-red-400'],
        ] as $s)
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">{{ $s['label'] }}</p>
            <p class="text-2xl font-bold {{ $s['color'] }} mt-1">{{ $s['value'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 mb-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-2 flex-1">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / nomor proyek..."
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="status" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                <option value="">Semua Status</option>
                @foreach(['planning'=>'Perencanaan','active'=>'Aktif','on_hold'=>'Ditunda','completed'=>'Selesai','cancelled'=>'Dibatalkan'] as $v=>$l)
                <option value="{{ $v }}" @selected(request('status')===$v)>{{ $l }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
        </form>
        @canmodule('projects', 'create')
        <button onclick="document.getElementById('modal-add-project').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 shrink-0">+ Proyek Baru</button>
        @endcanmodule
    </div>

    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 rounded-xl text-sm text-green-700 dark:text-green-400">{{ session('success') }}</div>
    @endif

    {{-- Project Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($projects as $project)
        @php
            $statusColors = ['planning'=>'gray','active'=>'blue','on_hold'=>'amber','completed'=>'green','cancelled'=>'red'];
            $statusLabels = ['planning'=>'Perencanaan','active'=>'Aktif','on_hold'=>'Ditunda','completed'=>'Selesai','cancelled'=>'Dibatalkan'];
            $c = $statusColors[$project->status] ?? 'gray';
            $overBudget = $project->budget > 0 && $project->actual_cost > $project->budget;
            $overdue = $project->end_date && $project->end_date->isPast() && !in_array($project->status, ['completed','cancelled']);
        @endphp
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5 flex flex-col gap-3 hover:shadow-md transition">
            <div class="flex items-start justify-between gap-2">
                <div class="flex-1 min-w-0">
                    <a href="{{ route('projects.show', $project) }}" class="font-semibold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 line-clamp-1">{{ $project->name }}</a>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">{{ $project->number }} {{ $project->customer ? '· '.$project->customer->name : '' }}</p>
                </div>
                <span class="shrink-0 px-2 py-0.5 rounded-full text-xs bg-{{ $c }}-100 text-{{ $c }}-700 dark:bg-{{ $c }}-500/20 dark:text-{{ $c }}-400">
                    {{ $statusLabels[$project->status] ?? $project->status }}
                </span>
            </div>

            {{-- Progress bar --}}
            <div>
                <div class="flex justify-between text-xs text-gray-500 dark:text-slate-400 mb-1">
                    <span>Progress</span>
                    <span>{{ number_format($project->progress, 0) }}%</span>
                </div>
                <div class="w-full bg-gray-100 dark:bg-white/10 rounded-full h-2">
                    <div class="h-2 rounded-full {{ $project->progress >= 100 ? 'bg-green-500' : 'bg-blue-500' }}" style="width:{{ min(100,$project->progress) }}%"></div>
                </div>
            </div>

            {{-- Budget --}}
            @if($project->budget > 0)
            <div class="flex justify-between text-xs">
                <span class="text-gray-500 dark:text-slate-400">Anggaran</span>
                <span class="{{ $overBudget ? 'text-red-500 font-semibold' : 'text-gray-700 dark:text-slate-300' }}">
                    Rp {{ number_format($project->actual_cost,0,',','.') }} / Rp {{ number_format($project->budget,0,',','.') }}
                    @if($overBudget) ⚠️ @endif
                </span>
            </div>
            @endif

            {{-- Dates & Tasks --}}
            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-slate-400">
                <span>
                    @if($project->end_date)
                        {{ $overdue ? '⚠️ ' : '' }}Deadline: <span class="{{ $overdue ? 'text-red-500 font-medium' : '' }}">{{ $project->end_date->format('d M Y') }}</span>
                    @else
                        Tanpa deadline
                    @endif
                </span>
                <span>{{ $project->tasks->count() }} task</span>
            </div>

            <a href="{{ route('projects.show', $project) }}"
                class="mt-auto text-center text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline">
                Lihat Detail →
            </a>
        </div>
        @empty
        <div class="col-span-full py-16 text-center text-gray-400 dark:text-slate-500">
            Belum ada proyek. Buat proyek pertama Anda.
        </div>
        @endforelse
    </div>

    @if($projects->hasPages())
    <div class="mt-4">{{ $projects->links() }}</div>
    @endif

    {{-- Modal Tambah Proyek --}}
    <div id="modal-add-project" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10 sticky top-0 bg-white dark:bg-[#1e293b]">
                <h3 class="font-semibold text-gray-900 dark:text-white">Proyek Baru</h3>
                <button onclick="document.getElementById('modal-add-project').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('projects.store') }}" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama Proyek *</label>
                        <input type="text" name="name" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Klien</label>
                        <select name="customer_id" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Tanpa klien --</option>
                            @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tipe Proyek</label>
                        <input type="text" name="type" placeholder="misal: Website, Konstruksi..." class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanggal Mulai</label>
                        <input type="date" name="start_date" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Deadline</label>
                        <input type="date" name="end_date" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Anggaran (Rp)</label>
                        <input type="number" name="budget" min="0" step="100000" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Deskripsi</label>
                        <textarea name="description" rows="2" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-add-project').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Buat Proyek</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
