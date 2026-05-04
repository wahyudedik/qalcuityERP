<x-app-layout>
    <x-slot name="header">Populasi Ternak</x-slot>

    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">{{ session('success') }}</div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Kelompok Aktif</p>
            <p class="text-xl font-bold text-blue-600">{{ $stats['active_herds'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Total Populasi</p>
            <p class="text-xl font-bold text-emerald-600">{{ number_format($stats['total_animals']) }} ekor</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Total Kematian</p>
            <p class="text-xl font-bold text-red-500">{{ number_format(abs($stats['total_mortality'])) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Total Terjual</p>
            <p class="text-xl font-bold text-gray-900">{{ number_format($stats['total_sold']) }}</p>
        </div>
    </div>

    <div class="flex items-center justify-between mb-4">
        <a href="{{ route('farm.plots') }}" class="text-sm text-blue-500 hover:text-blue-600">← Daftar Lahan</a>
        <button onclick="document.getElementById('addHerdModal').classList.remove('hidden')" class="px-3 py-2 text-xs bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition">🐄 Tambah Ternak</button>
    </div>

    {{-- Herd Cards --}}
    @if($herds->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
        <p class="text-3xl mb-3">🐄</p>
        <p class="text-sm text-gray-500">Belum ada data ternak. Tambahkan kelompok ternak pertama.</p>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @foreach($herds ?? [] as $herd)
        <a href="{{ route('farm.livestock.show', $herd) }}" class="block bg-white rounded-xl border border-gray-200 overflow-hidden hover:border-emerald-300 transition">
            <div class="px-5 py-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">{{ explode(' ', \App\Models\LivestockHerd::ANIMAL_TYPES[$herd->animal_type] ?? '🐾')[0] }}</span>
                        <span class="font-bold text-gray-900">{{ $herd->code }}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $herd->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">{{ ucfirst($herd->status) }}</span>
                    </div>
                    <span class="text-2xl font-black text-gray-900">{{ number_format($herd->current_count) }}</span>
                </div>
                <p class="text-sm text-gray-500">{{ $herd->name }}</p>
                <div class="grid grid-cols-3 gap-2 mt-3 text-xs">
                    <div><span class="text-gray-400">Awal:</span> <span class="text-gray-700">{{ $herd->initial_count }}</span></div>
                    <div><span class="text-gray-400">Mati:</span> <span class="text-red-500">{{ abs($herd->mortalityCount()) }} ({{ $herd->mortalityRate() }}%)</span></div>
                    <div><span class="text-gray-400">Umur:</span> <span class="text-gray-700">{{ $herd->ageDays() ?? '-' }} hari</span></div>
                </div>
                @if($herd->plot)
                <p class="text-[10px] text-gray-400 mt-2">📍 {{ $herd->plot?->code }} — {{ $herd->plot?->name }}</p>
                @endif
            </div>
        </a>
        @endforeach
    </div>
    <div class="mt-4">{{ $herds->links() }}</div>
    @endif

    {{-- Add Herd Modal --}}
    <div id="addHerdModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl border border-gray-200 w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-gray-900">🐄 Tambah Kelompok Ternak</h3>
                <button onclick="document.getElementById('addHerdModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="{{ route('farm.livestock.store') }}" class="space-y-4">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900'; @endphp
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Jenis Ternak *</label>
                        <select name="animal_type" required class="{{ $cls }}">
                            @foreach(\App\Models\LivestockHerd::ANIMAL_TYPES as $v => $l)
                            <option value="{{ $v }}">{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Ras/Breed</label>
                        <input type="text" name="breed" placeholder="Broiler, Brahman" class="{{ $cls }}">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama Kelompok *</label>
                    <input type="text" name="name" required placeholder="Ayam Broiler Batch 12" class="{{ $cls }}">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kandang/Area</label>
                        <select name="farm_plot_id" class="{{ $cls }}">
                            <option value="">— Tanpa kandang —</option>
                            @foreach($plots ?? [] as $p)
                            <option value="{{ $p->id }}">{{ $p->code }} — {{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Jumlah Masuk *</label>
                        <input type="number" name="initial_count" required min="1" placeholder="1000" class="{{ $cls }}">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Masuk *</label>
                        <input type="date" name="entry_date" required value="{{ date('Y-m-d') }}" class="{{ $cls }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Umur Masuk (hari)</label>
                        <input type="number" name="entry_age_days" min="0" value="1" class="{{ $cls }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Berat Rata-rata (kg)</label>
                        <input type="number" name="entry_weight_kg" step="0.001" placeholder="0.04" class="{{ $cls }}">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Harga Beli Total (Rp)</label>
                        <input type="number" name="purchase_price" step="1" min="0" class="{{ $cls }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Target Panen</label>
                        <input type="date" name="target_harvest_date" class="{{ $cls }}">
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('addHerdModal').classList.add('hidden')" class="flex-1 px-4 py-2 rounded-lg text-sm border border-gray-200 text-gray-700">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-2 rounded-lg text-sm bg-emerald-600 hover:bg-emerald-700 text-white font-medium">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
