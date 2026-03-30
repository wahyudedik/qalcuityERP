<x-app-layout>
    <x-slot name="header">Pencatatan Panen</x-slot>

    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 rounded-xl text-sm text-green-700 dark:text-green-400">{{ session('success') }}</div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Sesi Panen</p>
            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $stats['total_harvests'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Panen</p>
            <p class="text-xl font-bold text-emerald-600">{{ number_format($stats['total_qty'], 0) }} kg</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Reject</p>
            <p class="text-xl font-bold text-red-500">{{ number_format($stats['total_reject'], 0) }} kg</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Biaya Panen</p>
            <p class="text-xl font-bold text-gray-900 dark:text-white">Rp {{ number_format($stats['total_cost'], 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Productivity per Plot --}}
    @if($perPlot->isNotEmpty())
    <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4 mb-6">
        <p class="text-xs font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider mb-3">Produktivitas per Lahan</p>
        <div class="flex flex-wrap gap-3">
            @foreach($perPlot as $pp)
            @php $perHa = $pp->area_size > 0 ? round($pp->total / $pp->area_size, 0) : 0; @endphp
            <div class="px-3 py-2 rounded-lg bg-gray-50 dark:bg-white/5 text-xs">
                <span class="font-bold text-gray-700 dark:text-slate-300">{{ $pp->code }}</span>
                <span class="text-emerald-600 ml-1">{{ number_format($pp->total, 0) }} kg</span>
                <span class="text-gray-400 ml-1">({{ $pp->sessions }}x)</span>
                @if($perHa > 0)<span class="text-blue-500 ml-1">{{ number_format($perHa, 0) }} kg/{{ $pp->area_unit }}</span>@endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="flex items-center justify-between mb-4">
        <a href="{{ route('farm.plots') }}" class="text-sm text-blue-500 hover:text-blue-600">← Daftar Lahan</a>
        <button onclick="document.getElementById('harvestModal').classList.remove('hidden')" class="px-3 py-2 text-xs bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition">🌾 Catat Panen</button>
    </div>

    {{-- Harvest Log Table --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">No</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Lahan</th>
                        <th class="px-4 py-3 text-left">Tanaman</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-right">Reject</th>
                        <th class="px-4 py-3 text-left">Grade</th>
                        <th class="px-4 py-3 text-right">Biaya</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition">
                        <td class="px-4 py-3 font-mono text-xs text-gray-400">
                            <a href="{{ route('farm.harvests.show', $log) }}" class="text-blue-500 hover:underline">{{ $log->number }}</a>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $log->harvest_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $log->plot?->code }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-slate-300">{{ $log->crop_name }}</td>
                        <td class="px-4 py-3 text-right font-mono font-medium text-emerald-600">{{ number_format($log->total_qty, 0) }} {{ $log->unit }}</td>
                        <td class="px-4 py-3 text-right font-mono {{ $log->reject_qty > 0 ? 'text-red-500' : 'text-gray-300' }}">
                            {{ $log->reject_qty > 0 ? number_format($log->reject_qty, 0) . ' ' . $log->unit : '-' }}
                        </td>
                        <td class="px-4 py-3">
                            @foreach($log->grades as $g)
                            <span class="inline-block text-[10px] px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 mr-1">{{ $g->grade }}: {{ number_format($g->quantity, 0) }}</span>
                            @endforeach
                        </td>
                        <td class="px-4 py-3 text-right text-xs text-gray-500">Rp {{ number_format($log->totalCost(), 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-4 py-12 text-center text-gray-400">Belum ada data panen.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $logs->links() }}</div>

    {{-- Harvest Modal --}}
    <div id="harvestModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">🌾 Catat Panen</h3>
                <button onclick="document.getElementById('harvestModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('farm.harvests.store') }}" class="space-y-4">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; @endphp
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Lahan *</label>
                        <select name="farm_plot_id" required class="{{ $cls }}">
                            @foreach($plots as $p)
                            <option value="{{ $p->id }}">{{ $p->code }} — {{ $p->current_crop ?? $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanggal *</label>
                        <input type="date" name="harvest_date" required value="{{ date('Y-m-d') }}" class="{{ $cls }}">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanaman *</label>
                        <input type="text" name="crop_name" required placeholder="Padi" class="{{ $cls }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Total Panen *</label>
                        <input type="number" name="total_qty" required step="0.001" placeholder="500" class="{{ $cls }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Satuan *</label>
                        <input type="text" name="unit" required value="kg" class="{{ $cls }}">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Reject</label>
                        <input type="number" name="reject_qty" step="0.001" value="0" class="{{ $cls }}">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Kadar Air (%)</label>
                        <input type="number" name="moisture_pct" step="0.1" class="{{ $cls }}">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Cuaca</label>
                        <input type="text" name="weather" placeholder="Cerah" class="{{ $cls }}">
                    </div>
                </div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 pt-1">Breakdown Grade (opsional)</p>
                <div id="grade-rows" class="space-y-2">
                    <div class="grid grid-cols-3 gap-2">
                        <input type="text" name="grades[0][grade]" placeholder="Grade A" class="{{ $cls }}">
                        <input type="number" name="grades[0][quantity]" step="0.001" placeholder="Jumlah" class="{{ $cls }}">
                        <input type="number" name="grades[0][price]" step="1" placeholder="Harga/unit" class="{{ $cls }}">
                    </div>
                </div>
                <button type="button" onclick="addGradeRow()" class="text-xs text-blue-500 hover:text-blue-600">+ Tambah grade</button>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Upah Panen (Rp)</label>
                        <input type="number" name="labor_cost" step="1" min="0" class="{{ $cls }}">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Biaya Angkut (Rp)</label>
                        <input type="number" name="transport_cost" step="1" min="0" class="{{ $cls }}">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Gudang Tujuan</label>
                    <input type="text" name="storage_location" placeholder="Gudang Panen" class="{{ $cls }}">
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('harvestModal').classList.add('hidden')" class="flex-1 px-4 py-2 rounded-lg text-sm border border-gray-200 dark:border-white/10 text-gray-700 dark:text-slate-300">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-2 rounded-lg text-sm bg-emerald-600 hover:bg-emerald-700 text-white font-medium">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    let gradeIdx = 1;
    function addGradeRow() {
        const cls = 'w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white';
        document.getElementById('grade-rows').insertAdjacentHTML('beforeend', `
            <div class="grid grid-cols-3 gap-2">
                <input type="text" name="grades[${gradeIdx}][grade]" placeholder="Grade B" class="${cls}">
                <input type="number" name="grades[${gradeIdx}][quantity]" step="0.001" placeholder="Jumlah" class="${cls}">
                <input type="number" name="grades[${gradeIdx}][price]" step="1" placeholder="Harga/unit" class="${cls}">
            </div>`);
        gradeIdx++;
    }
    </script>
    @endpush
</x-app-layout>
