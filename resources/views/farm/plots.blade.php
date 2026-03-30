<x-app-layout>
    <x-slot name="header">Manajemen Lahan</x-slot>

    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 rounded-xl text-sm text-green-700 dark:text-green-400">{{ session('success') }}</div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Lahan</p>
            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Luas</p>
            <p class="text-xl font-bold text-emerald-600">{{ number_format($stats['total_area'], 1) }} ha</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Sedang Ditanam</p>
            <p class="text-xl font-bold text-blue-600">{{ $stats['planted'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Siap Panen</p>
            <p class="text-xl font-bold text-green-600">{{ $stats['ready_harvest'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Kosong / Bera</p>
            <p class="text-xl font-bold text-gray-400">{{ $stats['idle'] }}</p>
        </div>
    </div>

    {{-- Filter + Add --}}
    <div class="flex items-center justify-between mb-4">
        <form class="flex items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari lahan..." class="px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white w-48">
            <select name="status" onchange="this.form.submit()" class="px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white">
                <option value="">Semua Status</option>
                @foreach(\App\Models\FarmPlot::STATUS_LABELS as $v => $l)
                <option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>
                @endforeach
            </select>
        </form>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="px-3 py-2 text-xs bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition">🌱 Tambah Lahan</button>
    </div>

    {{-- Plot Cards --}}
    @if($plots->isEmpty())
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-12 text-center">
        <p class="text-3xl mb-3">🌾</p>
        <p class="text-sm text-gray-500 dark:text-slate-400">Belum ada lahan. Tambahkan lahan/blok kebun pertama Anda.</p>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @foreach($plots as $plot)
        @php $sc = $plot->statusColor(); @endphp
        <a href="{{ route('farm.plots.show', $plot) }}" class="block bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 overflow-hidden hover:border-emerald-300 dark:hover:border-emerald-500/30 transition group">
            <div class="px-5 py-4 flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-lg font-bold text-gray-900 dark:text-white">{{ $plot->code }}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-{{ $sc }}-100 text-{{ $sc }}-700 dark:bg-{{ $sc }}-500/20 dark:text-{{ $sc }}-400">{{ $plot->statusLabel() }}</span>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-slate-400 mt-0.5">{{ $plot->name }}</p>
                </div>
                <span class="text-xs text-gray-400 dark:text-slate-500">{{ number_format($plot->area_size, 1) }} {{ $plot->area_unit }}</span>
            </div>
            <div class="px-5 pb-4 grid grid-cols-2 gap-2 text-xs">
                @if($plot->current_crop)
                <div><span class="text-gray-400">Tanaman:</span> <span class="text-gray-700 dark:text-slate-300">{{ $plot->current_crop }}</span></div>
                @endif
                @if($plot->planted_at)
                <div><span class="text-gray-400">Tanam:</span> <span class="text-gray-700 dark:text-slate-300">{{ $plot->planted_at->format('d M Y') }}</span></div>
                @endif
                @if($plot->expected_harvest)
                <div>
                    <span class="text-gray-400">Panen:</span>
                    <span class="{{ $plot->isHarvestOverdue() ? 'text-red-500 font-medium' : 'text-gray-700 dark:text-slate-300' }}">
                        {{ $plot->expected_harvest->format('d M Y') }}
                        @if($plot->isHarvestOverdue()) (terlambat) @elseif($plot->daysUntilHarvest()) ({{ $plot->daysUntilHarvest() }}h lagi) @endif
                    </span>
                </div>
                @endif
                @if($plot->activities_count > 0)
                <div><span class="text-gray-400">Aktivitas:</span> <span class="text-gray-700 dark:text-slate-300">{{ $plot->activities_count }}</span></div>
                @endif
            </div>
        </a>
        @endforeach
    </div>
    <div class="mt-4">{{ $plots->links() }}</div>
    @endif

    {{-- Add Plot Modal --}}
    <div id="addModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">🌱 Tambah Lahan</h3>
                <button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('farm.plots.store') }}" class="space-y-4">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; @endphp
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Kode Lahan *</label>
                        <input type="text" name="code" required placeholder="A1, Blok-01" class="{{ $cls }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama *</label>
                        <input type="text" name="name" required placeholder="Sawah Utara" class="{{ $cls }}">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Luas *</label>
                        <input type="number" name="area_size" required step="0.001" placeholder="2.5" class="{{ $cls }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Satuan</label>
                        <select name="area_unit" class="{{ $cls }}">
                            <option value="ha">Hektar (ha)</option>
                            <option value="are">Are</option>
                            <option value="m2">m²</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Kepemilikan</label>
                        <select name="ownership" class="{{ $cls }}">
                            <option value="owned">Milik Sendiri</option>
                            <option value="rented">Sewa</option>
                            <option value="shared">Bagi Hasil</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Jenis Tanah</label>
                        <input type="text" name="soil_type" placeholder="Liat, berpasir, humus" class="{{ $cls }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Irigasi</label>
                        <input type="text" name="irrigation_type" placeholder="Irigasi, tadah hujan" class="{{ $cls }}">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Lokasi</label>
                    <input type="text" name="location" placeholder="Desa, kecamatan, atau koordinat" class="{{ $cls }}">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanaman Saat Ini</label>
                    <input type="text" name="current_crop" placeholder="Padi, jagung, kelapa sawit..." class="{{ $cls }}">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Catatan</label>
                    <textarea name="notes" rows="2" class="{{ $cls }}"></textarea>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="flex-1 px-4 py-2 rounded-lg text-sm border border-gray-200 dark:border-white/10 text-gray-700 dark:text-slate-300">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-2 rounded-lg text-sm bg-emerald-600 hover:bg-emerald-700 text-white font-medium">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
