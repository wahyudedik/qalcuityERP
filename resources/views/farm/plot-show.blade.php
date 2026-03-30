<x-app-layout>
    <x-slot name="header">{{ $farmPlot->code }} — {{ $farmPlot->name }}</x-slot>

    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 rounded-xl text-sm text-green-700 dark:text-green-400">{{ session('success') }}</div>
    @endif

    <div class="mb-4">
        <a href="{{ route('farm.plots') }}" class="text-sm text-blue-500 hover:text-blue-600">← Kembali ke Daftar Lahan</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: Info + Activities --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Status & KPI --}}
            @php $sc = $farmPlot->statusColor(); @endphp
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ $farmPlot->code }}</span>
                        <span class="px-3 py-1 rounded-full text-sm font-medium bg-{{ $sc }}-100 text-{{ $sc }}-700 dark:bg-{{ $sc }}-500/20 dark:text-{{ $sc }}-400">{{ $farmPlot->statusLabel() }}</span>
                    </div>
                    <form method="POST" action="{{ route('farm.plots.status', $farmPlot) }}" class="flex items-center gap-2">
                        @csrf @method('PATCH')
                        <select name="status" class="text-xs rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white py-1.5 px-2">
                            @foreach(\App\Models\FarmPlot::STATUS_LABELS as $v => $l)
                            <option value="{{ $v }}" @selected($farmPlot->status === $v)>{{ $l }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="px-3 py-1.5 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700">Update</button>
                    </form>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Luas</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($farmPlot->area_size, 1) }} {{ $farmPlot->area_unit }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Total Biaya</p>
                        <p class="text-lg font-bold text-red-500">Rp {{ number_format($farmPlot->totalCost(), 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Total Panen</p>
                        <p class="text-lg font-bold text-emerald-600">{{ number_format($farmPlot->totalHarvest(), 0) }} {{ $farmPlot->activities->where('activity_type', 'harvesting')->first()?->harvest_unit ?? 'kg' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">HPP / Unit</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $farmPlot->costPerUnit() ? 'Rp '.number_format($farmPlot->costPerUnit(), 0, ',', '.') : '-' }}</p>
                    </div>
                </div>
                @if($farmPlot->planted_at || $farmPlot->expected_harvest)
                <div class="flex items-center gap-4 mt-3 text-xs text-gray-500 dark:text-slate-400">
                    @if($farmPlot->planted_at)<span>🌱 Tanam: {{ $farmPlot->planted_at->format('d M Y') }} ({{ $farmPlot->daysSincePlanted() }} hari)</span>@endif
                    @if($farmPlot->expected_harvest)
                    <span class="{{ $farmPlot->isHarvestOverdue() ? 'text-red-500 font-medium' : '' }}">
                        🌾 Panen: {{ $farmPlot->expected_harvest->format('d M Y') }}
                        @if($farmPlot->isHarvestOverdue()) (terlambat!) @elseif($farmPlot->daysUntilHarvest()) ({{ $farmPlot->daysUntilHarvest() }}h lagi) @endif
                    </span>
                    @endif
                </div>
                @endif
            </div>

            {{-- Cost Breakdown --}}
            @if($costByType->isNotEmpty())
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Breakdown Biaya</h3>
                <div class="flex flex-wrap gap-3">
                    @foreach($costByType as $ct)
                    <div class="px-3 py-2 rounded-lg bg-gray-50 dark:bg-white/5 text-xs">
                        <span class="font-medium text-gray-700 dark:text-slate-300">{{ \App\Models\FarmPlotActivity::ACTIVITY_TYPES[$ct->activity_type] ?? $ct->activity_type }}</span>
                        <span class="text-gray-400 ml-1">Rp {{ number_format($ct->total_cost, 0, ',', '.') }}</span>
                        <span class="text-gray-300 ml-1">({{ $ct->count }}x)</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Activity Log --}}
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-white/10 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Riwayat Aktivitas ({{ $farmPlot->activities->count() }})</h3>
                    <button onclick="document.getElementById('activityModal').classList.remove('hidden')" class="px-3 py-1.5 text-xs bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">+ Catat Aktivitas</button>
                </div>
                @if($farmPlot->activities->isEmpty())
                <div class="p-8 text-center text-sm text-gray-400 dark:text-slate-500">Belum ada aktivitas tercatat.</div>
                @else
                <div class="divide-y divide-gray-100 dark:divide-white/5">
                    @foreach($farmPlot->activities->take(30) as $act)
                    <div class="px-5 py-3 flex items-start gap-3">
                        <span class="text-lg mt-0.5">{{ \App\Models\FarmPlotActivity::ACTIVITY_TYPES[$act->activity_type] ? explode(' ', \App\Models\FarmPlotActivity::ACTIVITY_TYPES[$act->activity_type])[0] : '📝' }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900 dark:text-white">{{ $act->description }}</p>
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-0.5 text-xs text-gray-500 dark:text-slate-400">
                                <span>{{ $act->date->format('d M Y') }}</span>
                                @if($act->input_product)<span>{{ $act->input_product }}: {{ $act->input_quantity }} {{ $act->input_unit }}</span>@endif
                                @if($act->harvest_qty > 0)<span class="text-emerald-600 font-medium">Panen: {{ number_format($act->harvest_qty, 0) }} {{ $act->harvest_unit }} {{ $act->harvest_grade ? "({$act->harvest_grade})" : '' }}</span>@endif
                                @if($act->cost > 0)<span class="text-red-500">Rp {{ number_format($act->cost, 0, ',', '.') }}</span>@endif
                                <span>oleh {{ $act->user?->name ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- Right: Edit Panel --}}
        <div class="space-y-6">
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Info Lahan</h3>
                <form method="POST" action="{{ route('farm.plots.update', $farmPlot) }}" class="space-y-3">
                    @csrf @method('PUT')
                    @php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; @endphp
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama</label>
                        <input type="text" name="name" value="{{ $farmPlot->name }}" required class="{{ $cls }}"></div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Luas</label>
                            <input type="number" name="area_size" value="{{ $farmPlot->area_size }}" step="0.001" class="{{ $cls }}"></div>
                        <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Satuan</label>
                            <select name="area_unit" class="{{ $cls }}">
                                @foreach(['ha'=>'Hektar','are'=>'Are','m2'=>'m²'] as $v=>$l)
                                <option value="{{ $v }}" @selected($farmPlot->area_unit === $v)>{{ $l }}</option>
                                @endforeach
                            </select></div>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanaman</label>
                        <input type="text" name="current_crop" value="{{ $farmPlot->current_crop }}" class="{{ $cls }}"></div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tgl Tanam</label>
                            <input type="date" name="planted_at" value="{{ $farmPlot->planted_at?->format('Y-m-d') }}" class="{{ $cls }}"></div>
                        <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Est. Panen</label>
                            <input type="date" name="expected_harvest" value="{{ $farmPlot->expected_harvest?->format('Y-m-d') }}" class="{{ $cls }}"></div>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Lokasi</label>
                        <input type="text" name="location" value="{{ $farmPlot->location }}" class="{{ $cls }}"></div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Jenis Tanah</label>
                            <input type="text" name="soil_type" value="{{ $farmPlot->soil_type }}" class="{{ $cls }}"></div>
                        <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Irigasi</label>
                            <input type="text" name="irrigation_type" value="{{ $farmPlot->irrigation_type }}" class="{{ $cls }}"></div>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Catatan</label>
                        <textarea name="notes" rows="2" class="{{ $cls }}">{{ $farmPlot->notes }}</textarea></div>
                    <button type="submit" class="w-full py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Activity Modal --}}
    <div id="activityModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 w-full max-w-md p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Catat Aktivitas</h3>
                <button onclick="document.getElementById('activityModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('farm.plots.activities.store', $farmPlot) }}" class="space-y-4">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; @endphp
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Jenis Aktivitas *</label>
                        <select name="activity_type" id="act-type" required onchange="toggleHarvestFields()" class="{{ $cls }}">
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
                    <input type="text" name="description" required placeholder="Pemupukan urea 50 kg/ha" class="{{ $cls }}">
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Input/Produk</label>
                        <input type="text" name="input_product" placeholder="Urea, Pestisida" class="{{ $cls }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Jumlah</label>
                        <input type="number" name="input_quantity" step="0.001" class="{{ $cls }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Satuan</label>
                        <input type="text" name="input_unit" placeholder="kg, liter" class="{{ $cls }}">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Biaya (Rp)</label>
                    <input type="number" name="cost" step="1" min="0" placeholder="0" class="{{ $cls }}">
                </div>
                <div id="harvest-fields" class="hidden space-y-3 border-t border-gray-100 dark:border-white/10 pt-3">
                    <p class="text-xs font-bold text-emerald-600 uppercase">Data Panen</p>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Jumlah Panen</label>
                            <input type="number" name="harvest_qty" step="0.001" class="{{ $cls }}">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Satuan</label>
                            <input type="text" name="harvest_unit" placeholder="kg, ton" class="{{ $cls }}">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Grade</label>
                            <input type="text" name="harvest_grade" placeholder="A, B, Premium" class="{{ $cls }}">
                        </div>
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('activityModal').classList.add('hidden')" class="flex-1 px-4 py-2 rounded-lg text-sm border border-gray-200 dark:border-white/10 text-gray-700 dark:text-slate-300">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-2 rounded-lg text-sm bg-emerald-600 hover:bg-emerald-700 text-white font-medium">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function toggleHarvestFields() {
        const type = document.getElementById('act-type').value;
        document.getElementById('harvest-fields').classList.toggle('hidden', type !== 'harvesting');
    }
    </script>
    @endpush
</x-app-layout>
