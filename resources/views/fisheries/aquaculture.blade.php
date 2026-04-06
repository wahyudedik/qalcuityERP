<x-app-layout>
    <x-slot name="header">🐠 Aquaculture Management</x-slot>

    @if (session('success'))
        <div
            class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 rounded-xl text-sm text-green-700 dark:text-green-400">
            {{ session('success') }}</div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Kolam</p>
            <p class="text-2xl font-bold text-cyan-600">{{ $stats['total_ponds'] ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Kolam Aktif</p>
            <p class="text-2xl font-bold text-emerald-600">{{ $stats['active_ponds'] ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Utilisasi Rata-rata</p>
            <p class="text-2xl font-bold text-blue-600">{{ number_format($stats['avg_utilization'] ?? 0, 1) }}%</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">FCR Rata-rata</p>
            <p class="text-2xl font-bold text-purple-600">{{ number_format($stats['avg_fcr'] ?? 0, 2) }}</p>
        </div>
    </div>

    {{-- Filter + Add --}}
    <div class="flex items-center justify-between mb-4">
        <form class="flex items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kolam..."
                class="px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white w-48">
            <select name="status" onchange="this.form.submit()"
                class="px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white">
                <option value="">Semua Status</option>
                @foreach (\App\Models\AquaculturePond::STATUS_LABELS as $v => $l)
                    <option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>
                @endforeach
            </select>
        </form>
        <button onclick="document.getElementById('addPondModal').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg transition flex items-center gap-2">
            <span>🏊</span> Tambah Kolam
        </button>
    </div>

    {{-- Pond Cards --}}
    @if (empty($ponds) || count($ponds) === 0)
        <div
            class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-12 text-center">
            <p class="text-4xl mb-3">🐠</p>
            <p class="text-sm text-gray-500 dark:text-slate-400">Belum ada kolam budidaya. Tambahkan kolam pertama Anda.
            </p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach ($ponds as $pond)
                @php
                    $sc = match ($pond->status) {
                        'active' => 'emerald',
                        'preparing' => 'blue',
                        'resting' => 'gray',
                        'maintenance' => 'yellow',
                        default => 'gray',
                    };
                @endphp
                <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 overflow-hidden hover:shadow-lg transition group"
                    x-data="{ showWaterForm: false, showFeedingForm: false }">

                    {{-- Header --}}
                    <div
                        class="px-5 py-4 flex items-start justify-between border-b border-gray-100 dark:border-white/5">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-lg font-bold text-gray-900 dark:text-white">{{ $pond->code }}</span>
                                <span
                                    class="text-xs px-2 py-0.5 rounded-full bg-{{ $sc }}-100 text-{{ $sc }}-700 dark:bg-{{ $sc }}-500/20 dark:text-{{ $sc }}-400">
                                    {{ $pond->status_label }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-slate-400 mt-0.5">{{ $pond->name }}</p>
                        </div>
                        <span
                            class="text-xs text-gray-400 dark:text-slate-500">{{ number_format($pond->area_size, 1) }}
                            m²</span>
                    </div>

                    {{-- Pond Details --}}
                    <div class="px-5 py-4">
                        {{-- Utilization Bar --}}
                        @if ($pond->utilization_percentage > 0)
                            <div class="mb-3">
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="text-gray-500 dark:text-slate-400">Utilisasi</span>
                                    <span
                                        class="font-medium text-gray-700 dark:text-slate-300">{{ number_format($pond->utilization_percentage, 1) }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div class="bg-cyan-600 h-2 rounded-full transition-all"
                                        style="width: {{ min($pond->utilization_percentage, 100) }}%"></div>
                                </div>
                            </div>
                        @endif

                        {{-- Current Stock --}}
                        @if ($pond->current_stock_species)
                            <div class="mb-3 p-3 bg-cyan-50 dark:bg-cyan-500/10 rounded-lg">
                                <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Stok Saat Ini</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    🐟 {{ $pond->current_stock_species }} -
                                    {{ number_format($pond->current_stock_count, 0) }} ekor
                                </p>
                                @if ($pond->stocked_at)
                                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">
                                        Dit stocking: {{ $pond->stocked_at->format('d M Y') }}
                                        ({{ $pond->stocked_at->diffInDays(now()) }} hari lalu)
                                    </p>
                                @endif
                            </div>
                        @endif

                        {{-- Water Quality Summary --}}
                        @if ($pond->latest_water_quality)
                            <div class="mb-3 p-3 bg-blue-50 dark:bg-blue-500/10 rounded-lg">
                                <p class="text-xs text-gray-500 dark:text-slate-400 mb-2">Kualitas Air Terakhir</p>
                                <div class="grid grid-cols-3 gap-2 text-xs">
                                    <div>
                                        <span class="text-gray-400 block">pH</span>
                                        <span
                                            class="font-medium text-gray-700 dark:text-slate-300">{{ number_format($pond->latest_water_quality->ph, 1) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-400 block">Oksigen</span>
                                        <span
                                            class="font-medium text-gray-700 dark:text-slate-300">{{ number_format($pond->latest_water_quality->dissolved_oxygen, 1) }}
                                            mg/L</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-400 block">Suhu</span>
                                        <span
                                            class="font-medium text-gray-700 dark:text-slate-300">{{ number_format($pond->latest_water_quality->temperature, 1) }}°C</span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Quick Actions --}}
                        <div class="flex gap-2 mt-3">
                            <button @click="showWaterForm = !showWaterForm"
                                class="flex-1 px-3 py-1.5 text-xs bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                💧 Cek Kualitas Air
                            </button>
                            <button @click="showFeedingForm = !showFeedingForm"
                                class="flex-1 px-3 py-1.5 text-xs bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition">
                                🍽️ Catat Pakan
                            </button>
                        </div>

                        {{-- Water Quality Form --}}
                        <div x-show="showWaterForm" x-transition
                            class="mt-3 p-3 bg-blue-50 dark:bg-blue-500/10 rounded-lg border border-blue-200 dark:border-blue-500/20">
                            <form :action="'{{ route('fisheries.aquaculture.log-water-quality', $pond->id) }}'"
                                method="POST" class="space-y-2">
                                @csrf
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">pH
                                            *</label>
                                        <input type="number" name="ph" required step="0.1" min="0"
                                            max="14" placeholder="7.0"
                                            class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Oksigen
                                            Terlarut (mg/L) *</label>
                                        <input type="number" name="dissolved_oxygen" required step="0.1"
                                            min="0" placeholder="6.5"
                                            class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Suhu
                                            Air (°C)</label>
                                        <input type="number" name="temperature" step="0.1" placeholder="28.5"
                                            class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Amonia
                                            (mg/L)</label>
                                        <input type="number" name="ammonia" step="0.01" min="0"
                                            placeholder="0.02"
                                            class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                                    </div>
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Salinitas
                                        (ppt)</label>
                                    <input type="number" name="salinity" step="0.1" min="0"
                                        placeholder="15.0"
                                        class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                                </div>
                                <div class="flex gap-2">
                                    <button type="submit"
                                        class="flex-1 px-3 py-1.5 text-xs bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                        Simpan
                                    </button>
                                    <button type="button" @click="showWaterForm = false"
                                        class="px-3 py-1.5 text-xs bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg transition">
                                        Batal
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- Feeding Form --}}
                        <div x-show="showFeedingForm" x-transition
                            class="mt-3 p-3 bg-emerald-50 dark:bg-emerald-500/10 rounded-lg border border-emerald-200 dark:border-emerald-500/20">
                            <form :action="'{{ route('fisheries.aquaculture.log-feeding', $pond->id) }}'"
                                method="POST" class="space-y-2">
                                @csrf
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Jumlah
                                            Pakan (kg) *</label>
                                        <input type="number" name="feed_quantity" required step="0.01"
                                            min="0" placeholder="5.5"
                                            class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Biaya
                                            Pakan (Rp)</label>
                                        <input type="number" name="feed_cost" step="100" min="0"
                                            placeholder="50000"
                                            class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                                    </div>
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Jenis
                                        Pakan</label>
                                    <input type="text" name="feed_type" placeholder="Pelet 781-2"
                                        class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Catatan</label>
                                    <textarea name="notes" rows="2" placeholder="Waktu pemberian, kondisi ikan, dll."
                                        class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white"></textarea>
                                </div>
                                <div class="flex gap-2">
                                    <button type="submit"
                                        class="flex-1 px-3 py-1.5 text-xs bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition">
                                        Simpan
                                    </button>
                                    <button type="button" @click="showFeedingForm = false"
                                        class="px-3 py-1.5 text-xs bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg transition">
                                        Batal
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- Additional Info --}}
                        <div class="grid grid-cols-2 gap-2 text-xs mt-3">
                            @if ($pond->pond_type)
                                <div>
                                    <span class="text-gray-400">Tipe:</span>
                                    <span
                                        class="text-gray-700 dark:text-slate-300">{{ ucfirst(str_replace('_', ' ', $pond->pond_type)) }}</span>
                                </div>
                            @endif
                            @if ($pond->water_source)
                                <div>
                                    <span class="text-gray-400">Sumber Air:</span>
                                    <span
                                        class="text-gray-700 dark:text-slate-300">{{ ucfirst($pond->water_source) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div
                        class="px-5 py-3 bg-gray-50 dark:bg-[#0f172a] border-t border-gray-100 dark:border-white/5 flex items-center justify-between text-xs">
                        <span class="text-gray-500 dark:text-slate-400">
                            @if ($pond->last_feeding_at)
                                Terakhir pakan: {{ $pond->last_feeding_at->diffForHumans() }}
                            @else
                                Belum ada pakan
                            @endif
                        </span>
                        <a href="{{ route('fisheries.aquaculture.show', $pond->id) }}"
                            class="text-cyan-600 dark:text-cyan-400 hover:underline">
                            Detail →
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $ponds->links() }}</div>
    @endif

    {{-- Add Pond Modal --}}
    <div id="addPondModal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div
            class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">🏊 Tambah Kolam Budidaya</h3>
                <button onclick="document.getElementById('addPondModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('fisheries.aquaculture.store-pond') }}" class="space-y-4">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; @endphp

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Kode Kolam
                            *</label>
                        <input type="text" name="code" required placeholder="POND-001"
                            class="{{ $cls }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama *</label>
                        <input type="text" name="name" required placeholder="Kolam A1"
                            class="{{ $cls }}">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Luas (m²)
                            *</label>
                        <input type="number" name="area_size" required step="0.01" placeholder="500"
                            class="{{ $cls }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Kedalaman
                            (m)</label>
                        <input type="number" name="depth" step="0.1" placeholder="1.5"
                            class="{{ $cls }}">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tipe
                            Kolam</label>
                        <select name="pond_type" class="{{ $cls }}">
                            <option value="earthen">Kolam Tanah</option>
                            <option value="concrete">Kolam Beton</option>
                            <option value="tarpaulin">Terpal</option>
                            <option value="floating">Keramba Apung</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Sumber
                            Air</label>
                        <select name="water_source" class="{{ $cls }}">
                            <option value="river">Sungai</option>
                            <option value="well">Sumur</option>
                            <option value="reservoir">Waduk</option>
                            <option value="sea">Laut</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Lokasi</label>
                    <input type="text" name="location" placeholder="Area A, Blok 1" class="{{ $cls }}">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Deskripsi</label>
                    <textarea name="description" rows="2" placeholder="Spesifikasi dan catatan tambahan"
                        class="{{ $cls }}"></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2 text-sm bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg transition">
                        💾 Simpan Kolam
                    </button>
                    <button type="button" onclick="document.getElementById('addPondModal').classList.add('hidden')"
                        class="px-4 py-2 text-sm bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg transition">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
