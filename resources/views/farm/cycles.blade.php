<x-app-layout>
    <x-slot name="header">Siklus Tanam</x-slot>

    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 rounded-xl text-sm text-green-700 dark:text-green-400">{{ session('success') }}</div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Siklus Aktif</p>
            <p class="text-xl font-bold text-blue-600">{{ $stats['active'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Selesai</p>
            <p class="text-xl font-bold text-green-600">{{ $stats['completed'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Panen Terlambat</p>
            <p class="text-xl font-bold {{ $stats['overdue'] > 0 ? 'text-red-500' : 'text-gray-400' }}">{{ $stats['overdue'] }}</p>
        </div>
    </div>

    <div class="flex items-center justify-between mb-4">
        <a href="{{ route('farm.plots') }}" class="text-sm text-blue-500 hover:text-blue-600">← Daftar Lahan</a>
        <button onclick="document.getElementById('addCycleModal').classList.remove('hidden')" class="px-3 py-2 text-xs bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition">🌱 Mulai Siklus Baru</button>
    </div>

    {{-- Cycles --}}
    @if($cycles->isEmpty())
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-12 text-center">
        <p class="text-3xl mb-3">🌱</p>
        <p class="text-sm text-gray-500 dark:text-slate-400">Belum ada siklus tanam. Mulai siklus pertama untuk tracking dari persiapan lahan hingga panen.</p>
    </div>
    @else
    <div class="space-y-3">
        @foreach($cycles as $cycle)
        @php $pc = $cycle->phaseColor(); @endphp
        <a href="{{ route('farm.cycles.show', $cycle) }}" class="block bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-5 hover:border-emerald-300 dark:hover:border-emerald-500/30 transition">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    <span class="font-mono text-xs text-gray-400">{{ $cycle->number }}</span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ $cycle->crop_name }}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-{{ $pc }}-100 text-{{ $pc }}-700 dark:bg-{{ $pc }}-500/20 dark:text-{{ $pc }}-400">{{ $cycle->phaseLabel() }}</span>
                </div>
                <span class="text-xs text-gray-400">{{ $cycle->plot?->code }} · {{ $cycle->plot?->name }}</span>
            </div>
            {{-- Phase progress bar --}}
            <div class="flex gap-1 mb-2">
                @foreach(['planning','land_prep','planting','vegetative','generative','harvest','post_harvest','completed'] as $p)
                @php
                    $idx = \App\Models\CropCycle::PHASE_ORDER[$p];
                    $currentIdx = $cycle->phaseIndex();
                    $done = $idx <= $currentIdx && $cycle->phase !== 'cancelled';
                @endphp
                <div class="flex-1 h-1.5 rounded-full {{ $done ? 'bg-emerald-500' : 'bg-gray-200 dark:bg-white/10' }}"></div>
                @endforeach
            </div>
            <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-slate-400">
                @if($cycle->plan_harvest_date)
                <span class="{{ $cycle->isHarvestOverdue() ? 'text-red-500 font-medium' : '' }}">
                    Panen: {{ $cycle->plan_harvest_date->format('d M Y') }}
                    @if($cycle->isHarvestOverdue()) (terlambat) @elseif($cycle->daysUntilHarvest()) ({{ $cycle->daysUntilHarvest() }}h) @endif
                </span>
                @endif
                @if($cycle->actual_yield_qty > 0)
                <span class="text-emerald-600">Hasil: {{ number_format($cycle->actual_yield_qty, 0) }} {{ $cycle->target_yield_unit }}</span>
                @endif
                @if($cycle->actual_cost > 0)
                <span>Biaya: Rp {{ number_format($cycle->actual_cost, 0, ',', '.') }}</span>
                @endif
            </div>
        </a>
        @endforeach
    </div>
    <div class="mt-4">{{ $cycles->links() }}</div>
    @endif

    {{-- Add Cycle Modal --}}
    <div id="addCycleModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">🌱 Mulai Siklus Tanam Baru</h3>
                <button onclick="document.getElementById('addCycleModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('farm.cycles.store') }}" class="space-y-4">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; @endphp
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Lahan *</label>
                        <select name="farm_plot_id" required class="{{ $cls }}">
                            @foreach($plots as $p)
                            <option value="{{ $p->id }}">{{ $p->code }} — {{ $p->name }} ({{ $p->area_size }} {{ $p->area_unit }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Musim</label>
                        <input type="text" name="season" placeholder="MT1, Gadu, Rendeng" class="{{ $cls }}">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanaman *</label>
                        <input type="text" name="crop_name" required placeholder="Padi IR64, Jagung Hibrida" class="{{ $cls }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Varietas</label>
                        <input type="text" name="crop_variety" placeholder="IR64, NK7328" class="{{ $cls }}">
                    </div>
                </div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500 pt-1">Rencana Jadwal</p>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Olah Tanah</label>
                        <input type="date" name="plan_prep_start" class="{{ $cls }}">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Tanam</label>
                        <input type="date" name="plan_plant_date" class="{{ $cls }}">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Panen</label>
                        <input type="date" name="plan_harvest_date" class="{{ $cls }}">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Target Panen</label>
                        <input type="number" name="target_yield_qty" step="0.001" placeholder="5000" class="{{ $cls }}">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Satuan</label>
                        <input type="text" name="target_yield_unit" value="kg" class="{{ $cls }}">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Anggaran (Rp)</label>
                        <input type="number" name="estimated_budget" step="1000" class="{{ $cls }}">
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('addCycleModal').classList.add('hidden')" class="flex-1 px-4 py-2 rounded-lg text-sm border border-gray-200 dark:border-white/10 text-gray-700 dark:text-slate-300">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-2 rounded-lg text-sm bg-emerald-600 hover:bg-emerald-700 text-white font-medium">Mulai Siklus</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
