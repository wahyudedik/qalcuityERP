<x-app-layout>
    <x-slot name="header">{{ $cropCycle->number }} — {{ $cropCycle->crop_name }}</x-slot>

    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 rounded-xl text-sm text-green-700 dark:text-green-400">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between mb-4">
        <a href="{{ route('farm.cycles') }}" class="text-sm text-blue-500 hover:text-blue-600">← Daftar Siklus</a>
        <a href="{{ route('farm.plots.show', $cropCycle->plot) }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-slate-400">Lahan {{ $cropCycle->plot->code }} →</a>
    </div>

    {{-- Phase Timeline --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5 mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <span class="text-lg font-bold text-gray-900 dark:text-white">{{ $cropCycle->crop_name }}</span>
                @if($cropCycle->crop_variety) <span class="text-sm text-gray-400 ml-2">var. {{ $cropCycle->crop_variety }}</span> @endif
                @if($cropCycle->season) <span class="text-xs ml-2 px-2 py-0.5 rounded bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">{{ $cropCycle->season }}</span> @endif
            </div>
            @if(!in_array($cropCycle->phase, ['completed', 'cancelled']))
            <form method="POST" action="{{ route('farm.cycles.phase', $cropCycle) }}" class="flex items-center gap-2">
                @csrf @method('PATCH')
                <select name="phase" class="text-xs rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white py-1.5 px-2">
                    @foreach(\App\Models\CropCycle::PHASE_LABELS as $v => $l)
                    @if(\App\Models\CropCycle::PHASE_ORDER[$v] > $cropCycle->phaseIndex() || $v === 'cancelled')
                    <option value="{{ $v }}">→ {{ $l }}</option>
                    @endif
                    @endforeach
                </select>
                <button type="submit" class="px-3 py-1.5 text-xs bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">Ubah Fase</button>
            </form>
            @endif
        </div>

        {{-- Phase progress --}}
        @php $phases = ['planning','land_prep','planting','vegetative','generative','harvest','post_harvest','completed']; @endphp
        <div class="flex gap-1 mb-1">
            @foreach($phases as $p)
            @php
                $idx = \App\Models\CropCycle::PHASE_ORDER[$p];
                $currentIdx = $cropCycle->phaseIndex();
                $isCurrent = $cropCycle->phase === $p;
                $done = $idx < $currentIdx && $cropCycle->phase !== 'cancelled';
                $color = $isCurrent ? 'bg-emerald-500' : ($done ? 'bg-emerald-400' : 'bg-gray-200 dark:bg-white/10');
            @endphp
            <div class="flex-1 h-2 rounded-full {{ $color }} {{ $isCurrent ? 'ring-2 ring-emerald-300' : '' }}"></div>
            @endforeach
        </div>
        <div class="flex justify-between text-[9px] text-gray-400 dark:text-slate-500 mt-1 px-0.5">
            @foreach($phases as $p)
            <span class="{{ $cropCycle->phase === $p ? 'text-emerald-600 dark:text-emerald-400 font-bold' : '' }}">
                {{ explode(' ', \App\Models\CropCycle::PHASE_LABELS[$p])[0] }}
            </span>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- KPI --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
                    <p class="text-xs text-gray-500 dark:text-slate-400">Durasi</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $cropCycle->durationDays() ?? 0 }} hari</p>
                </div>
                <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
                    <p class="text-xs text-gray-500 dark:text-slate-400">Hasil Panen</p>
                    <p class="text-lg font-bold text-emerald-600">{{ number_format($cropCycle->actual_yield_qty, 0) }} / {{ number_format($cropCycle->target_yield_qty, 0) }} {{ $cropCycle->target_yield_unit }}</p>
                    @if($cropCycle->target_yield_qty > 0)
                    <p class="text-[10px] text-gray-400">({{ $cropCycle->yieldPercent() }}%)</p>
                    @endif
                </div>
                <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
                    <p class="text-xs text-gray-500 dark:text-slate-400">Biaya</p>
                    <p class="text-lg font-bold {{ $cropCycle->budgetUsedPercent() > 100 ? 'text-red-500' : 'text-gray-900 dark:text-white' }}">Rp {{ number_format($cropCycle->actual_cost, 0, ',', '.') }}</p>
                    @if($cropCycle->estimated_budget > 0)
                    <p class="text-[10px] text-gray-400">dari Rp {{ number_format($cropCycle->estimated_budget, 0, ',', '.') }} ({{ $cropCycle->budgetUsedPercent() }}%)</p>
                    @endif
                </div>
                <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
                    <p class="text-xs text-gray-500 dark:text-slate-400">HPP / {{ $cropCycle->target_yield_unit }}</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $cropCycle->costPerUnit() ? 'Rp '.number_format($cropCycle->costPerUnit(), 0, ',', '.') : '-' }}</p>
                </div>
            </div>

            {{-- Activity Log --}}
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-white/10 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Aktivitas ({{ $cropCycle->activities->count() }})</h3>
                    @if(!in_array($cropCycle->phase, ['completed', 'cancelled']))
                    <button onclick="document.getElementById('actModal').classList.remove('hidden')" class="px-3 py-1.5 text-xs bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">+ Catat</button>
                    @endif
                </div>
                @if($cropCycle->activities->isEmpty())
                <div class="p-8 text-center text-sm text-gray-400">Belum ada aktivitas.</div>
                @else
                <div class="divide-y divide-gray-100 dark:divide-white/5">
                    @foreach($cropCycle->activities->take(50) as $act)
                    <div class="px-5 py-3 flex items-start gap-3">
                        <span class="text-lg mt-0.5">{{ explode(' ', \App\Models\FarmPlotActivity::ACTIVITY_TYPES[$act->activity_type] ?? '📝')[0] }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900 dark:text-white">{{ $act->description }}</p>
                            <div class="flex flex-wrap gap-x-3 gap-y-1 mt-0.5 text-xs text-gray-500 dark:text-slate-400">
                                <span>{{ $act->date->format('d M Y') }}</span>
                                @if($act->input_product)<span>{{ $act->input_product }}: {{ $act->input_quantity }} {{ $act->input_unit }}</span>@endif
                                @if($act->harvest_qty > 0)<span class="text-emerald-600 font-medium">Panen: {{ number_format($act->harvest_qty, 0) }} {{ $act->harvest_unit }}</span>@endif
                                @if($act->cost > 0)<span class="text-red-500">Rp {{ number_format($act->cost, 0, ',', '.') }}</span>@endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- Right: Info --}}
        <div class="space-y-6">
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Jadwal</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500 dark:text-slate-400">Lahan</span><span class="text-gray-900 dark:text-white font-medium">{{ $cropCycle->plot->code }} — {{ $cropCycle->plot->name }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500 dark:text-slate-400">Luas</span><span>{{ $cropCycle->plot->area_size }} {{ $cropCycle->plot->area_unit }}</span></div>
                    @if($cropCycle->plan_prep_start)
                    <div class="flex justify-between"><span class="text-gray-500 dark:text-slate-400">Renc. Olah Tanah</span><span>{{ $cropCycle->plan_prep_start->format('d M Y') }}</span></div>
                    @endif
                    @if($cropCycle->plan_plant_date)
                    <div class="flex justify-between"><span class="text-gray-500 dark:text-slate-400">Renc. Tanam</span><span>{{ $cropCycle->plan_plant_date->format('d M Y') }}</span></div>
                    @endif
                    @if($cropCycle->plan_harvest_date)
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-slate-400">Renc. Panen</span>
                        <span class="{{ $cropCycle->isHarvestOverdue() ? 'text-red-500 font-medium' : '' }}">{{ $cropCycle->plan_harvest_date->format('d M Y') }}</span>
                    </div>
                    @endif
                    @if($cropCycle->actual_prep_start)
                    <div class="flex justify-between"><span class="text-emerald-600">✓ Olah Tanah</span><span>{{ $cropCycle->actual_prep_start->format('d M Y') }}</span></div>
                    @endif
                    @if($cropCycle->actual_plant_date)
                    <div class="flex justify-between"><span class="text-emerald-600">✓ Tanam</span><span>{{ $cropCycle->actual_plant_date->format('d M Y') }}</span></div>
                    @endif
                    @if($cropCycle->actual_harvest_date)
                    <div class="flex justify-between"><span class="text-emerald-600">✓ Panen</span><span>{{ $cropCycle->actual_harvest_date->format('d M Y') }}</span></div>
                    @endif
                </div>
            </div>

            @if($costByType->isNotEmpty())
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Breakdown Biaya</h3>
                <div class="space-y-2">
                    @foreach($costByType as $ct)
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-600 dark:text-slate-300">{{ \App\Models\FarmPlotActivity::ACTIVITY_TYPES[$ct->activity_type] ?? $ct->activity_type }}</span>
                        <span class="font-mono text-gray-900 dark:text-white">Rp {{ number_format($ct->total_cost, 0, ',', '.') }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Activity Modal --}}
    <div id="actModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 w-full max-w-md p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Catat Aktivitas</h3>
                <button onclick="document.getElementById('actModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('farm.cycles.activities.store', $cropCycle) }}" class="space-y-4">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; @endphp
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Jenis *</label>
                        <select name="activity_type" id="cyc-act-type" required onchange="document.getElementById('cyc-harvest').classList.toggle('hidden', this.value!=='harvesting')" class="{{ $cls }}">
                            @foreach(\App\Models\FarmPlotActivity::ACTIVITY_TYPES as $v => $l)
                            <option value="{{ $v }}">{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanggal *</label>
                        <input type="date" name="date" required value="{{ date('Y-m-d') }}" class="{{ $cls }}">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Deskripsi *</label>
                    <input type="text" name="description" required class="{{ $cls }}">
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div><label class="block text-xs text-gray-500 mb-1">Input</label><input type="text" name="input_product" class="{{ $cls }}"></div>
                    <div><label class="block text-xs text-gray-500 mb-1">Jumlah</label><input type="number" name="input_quantity" step="0.001" class="{{ $cls }}"></div>
                    <div><label class="block text-xs text-gray-500 mb-1">Satuan</label><input type="text" name="input_unit" class="{{ $cls }}"></div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Biaya (Rp)</label>
                    <input type="number" name="cost" step="1" min="0" class="{{ $cls }}">
                </div>
                <div id="cyc-harvest" class="hidden space-y-3 border-t border-gray-100 dark:border-white/10 pt-3">
                    <div class="grid grid-cols-3 gap-3">
                        <div><label class="block text-xs text-emerald-600 mb-1">Jumlah Panen</label><input type="number" name="harvest_qty" step="0.001" class="{{ $cls }}"></div>
                        <div><label class="block text-xs text-emerald-600 mb-1">Satuan</label><input type="text" name="harvest_unit" value="{{ $cropCycle->target_yield_unit }}" class="{{ $cls }}"></div>
                        <div><label class="block text-xs text-emerald-600 mb-1">Grade</label><input type="text" name="harvest_grade" class="{{ $cls }}"></div>
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('actModal').classList.add('hidden')" class="flex-1 px-4 py-2 rounded-lg text-sm border border-gray-200 dark:border-white/10 text-gray-700 dark:text-slate-300">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-2 rounded-lg text-sm bg-emerald-600 hover:bg-emerald-700 text-white font-medium">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
