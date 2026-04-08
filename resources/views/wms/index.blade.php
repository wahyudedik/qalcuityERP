<x-app-layout>
    <x-slot name="header">WMS — Zone & Bin Location</x-slot>

    {{-- Warehouse selector + Stats --}}
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <form method="GET" class="flex gap-2">
            <select name="warehouse_id" onchange="this.form.submit()"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                @foreach ($warehouses as $w)
                    <option value="{{ $w->id }}" @selected($warehouseId == $w->id)>{{ $w->name }}</option>
                @endforeach
            </select>
        </form>
        <div class="flex-1 grid grid-cols-2 sm:grid-cols-4 gap-3">
            @foreach ([['Zone', $stats['zones'], 'blue'], ['Total Bin', $stats['bins'], 'gray'], ['Terisi', $stats['occupied'], 'green'], ['Produk', $stats['products'], 'purple']] as [$l, $v, $c])
                <div
                    class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-3 text-center">
                    <p class="text-xs text-gray-500 dark:text-slate-400">{{ $l }}</p>
                    <p class="text-xl font-bold text-{{ $c }}-500">{{ $v }}</p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Zones --}}
    @if ($zones->isNotEmpty())
        <div class="flex flex-wrap gap-2 mb-4">
            <a href="?warehouse_id={{ $warehouseId }}"
                class="px-3 py-1.5 text-xs rounded-xl {{ !request('zone_id') ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-slate-300' }}">Semua</a>
            @foreach ($zones as $z)
                <a href="?warehouse_id={{ $warehouseId }}&zone_id={{ $z->id }}"
                    class="px-3 py-1.5 text-xs rounded-xl {{ request('zone_id') == $z->id ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-slate-300' }}">
                    {{ $z->code }} — {{ $z->name }} ({{ $z->bins_count }})
                </a>
            @endforeach
        </div>
    @endif

    {{-- Toolbar --}}
    <div class="flex flex-wrap gap-2 mb-4">
        @canmodule('wms', 'create')
        <button onclick="document.getElementById('modal-zone').classList.remove('hidden')"
            class="text-xs px-3 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Zone</button>
        <button onclick="document.getElementById('modal-bin').classList.remove('hidden')"
            class="text-xs px-3 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700">+ Bin</button>
        <button onclick="document.getElementById('modal-bulk').classList.remove('hidden')"
            class="text-xs px-3 py-2 bg-purple-600 text-white rounded-xl hover:bg-purple-700">⚡ Bulk Bin</button>
        <button onclick="document.getElementById('modal-putaway').classList.remove('hidden')"
            class="text-xs px-3 py-2 bg-amber-600 text-white rounded-xl hover:bg-amber-700">📦 Putaway</button>
        @endcanmodule
        {{-- Batch print label button --}}
        <button onclick="printSelectedBins()"
            class="text-xs px-3 py-2 bg-gray-600 text-white rounded-xl hover:bg-gray-700 hidden" id="btn-batch-label">
            🖨 Print Label Terpilih (<span id="selected-count">0</span>)
        </button>
        <button onclick="selectAllBins()"
            class="text-xs px-3 py-2 border border-gray-200 dark:border-white/10 text-gray-600 dark:text-slate-300 rounded-xl hover:bg-gray-50 dark:hover:bg-white/5">
            Pilih Semua
        </button>
    </div>

    {{-- Batch print form (hidden) --}}
    <form id="form-batch-label" method="POST" action="{{ route('wms.bins.labels.batch') }}" target="_blank"
        class="hidden">
        @csrf
        <div id="batch-label-inputs"></div>
    </form>

    {{-- Bin Grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2">
        @forelse($bins as $bin)
            @php
                $occupied = $bin->stocks->sum('quantity') > 0;
                $bc = $occupied
                    ? 'border-green-300 dark:border-green-500/30 bg-green-50 dark:bg-green-500/5'
                    : 'border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b]';
            @endphp
            <div class="rounded-xl border {{ $bc }} p-3 text-center relative"
                data-bin-id="{{ $bin->id }}">
                {{-- Checkbox for batch print --}}
                <input type="checkbox" class="bin-checkbox absolute top-1.5 left-1.5 w-3.5 h-3.5 cursor-pointer"
                    data-bin-id="{{ $bin->id }}" onchange="updateBatchCount()">
                {{-- Single print label button --}}
                <a href="{{ route('wms.bins.label', $bin) }}" target="_blank" title="Print label"
                    class="absolute top-1.5 right-1.5 text-gray-300 dark:text-slate-600 hover:text-gray-600 dark:hover:text-slate-300 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                </a>
                <p class="font-mono text-xs font-bold text-gray-900 dark:text-white mt-3">{{ $bin->code }}</p>
                <p class="text-[10px] text-gray-400 dark:text-slate-500">{{ $bin->zone->name ?? '-' }}</p>
                @if ($occupied)
                    <div class="mt-1 space-y-0.5">
                        @foreach ($bin->stocks->where('quantity', '>', 0)->take(3) as $bs)
                            <p class="text-[10px] text-gray-600 dark:text-slate-300 truncate">
                                {{ $bs->product->name ?? '?' }}: {{ number_format($bs->quantity, 0) }}</p>
                        @endforeach
                        @if ($bin->stocks->where('quantity', '>', 0)->count() > 3)
                            <p class="text-[10px] text-gray-400">
                                +{{ $bin->stocks->where('quantity', '>', 0)->count() - 3 }}</p>
                        @endif
                    </div>
                @else
                    <p class="text-[10px] text-gray-400 dark:text-slate-500 mt-1">Kosong</p>
                @endif
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-gray-400 dark:text-slate-500 text-sm">Belum ada bin. Buat
                zone dan bin terlebih dahulu.</div>
        @endforelse
    </div>
    @if ($bins->hasPages())
        <div class="mt-4">{{ $bins->links() }}</div>
    @endif

    {{-- Modal Zone --}}
    <div id="modal-zone" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-sm shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Tambah Zone</h3>
                <button onclick="document.getElementById('modal-zone').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('wms.zones.store') }}" class="p-6 space-y-3">
                @csrf
                <input type="hidden" name="warehouse_id" value="{{ $warehouseId }}">
                @php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; @endphp
                <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Kode *</label><input
                        type="text" name="code" required maxlength="10" placeholder="Z01"
                        class="{{ $cls }}"></div>
                <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Nama *</label><input
                        type="text" name="name" required placeholder="Zona Dry Storage"
                        class="{{ $cls }}"></div>
                <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Tipe *</label>
                    <select name="type" required class="{{ $cls }}">
                        <option value="general">General</option>
                        <option value="cold">Cold Storage</option>
                        <option value="hazmat">Hazmat</option>
                        <option value="staging">Staging</option>
                        <option value="returns">Returns</option>
                    </select>
                </div>
                <button type="submit"
                    class="w-full py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
            </form>
        </div>
    </div>

    {{-- Modal Bin --}}
    <div id="modal-bin" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-sm shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Tambah Bin</h3>
                <button onclick="document.getElementById('modal-bin').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('wms.bins.store') }}" class="p-6 space-y-3">
                @csrf
                <input type="hidden" name="warehouse_id" value="{{ $warehouseId }}">
                @php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; @endphp
                <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Zone</label>
                    <select name="zone_id" class="{{ $cls }}">
                        <option value="">-- Tanpa Zone --</option>
                        @foreach ($zones as $z)
                            <option value="{{ $z->id }}">{{ $z->code }} — {{ $z->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Aisle</label><input
                            type="text" name="aisle" maxlength="10" placeholder="01"
                            class="{{ $cls }}"></div>
                    <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Rack</label><input
                            type="text" name="rack" maxlength="10" placeholder="01"
                            class="{{ $cls }}"></div>
                    <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Shelf</label><input
                            type="text" name="shelf" maxlength="10" placeholder="01"
                            class="{{ $cls }}"></div>
                </div>
                <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Tipe</label>
                    <select name="bin_type" class="{{ $cls }}">
                        <option value="storage">Storage</option>
                        <option value="picking">Picking</option>
                        <option value="staging">Staging</option>
                        <option value="returns">Returns</option>
                    </select>
                </div>
                <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Kapasitas Maks
                        (0=unlimited)</label><input type="number" name="max_capacity" min="0" value="0"
                        class="{{ $cls }}"></div>
                <button type="submit"
                    class="w-full py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">Simpan</button>
            </form>
        </div>
    </div>

    {{-- Modal Bulk Bin --}}
    <div id="modal-bulk" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-sm shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h2 class="font-semibold text-gray-900 dark:text-white">Bulk Create Bin</h2>
                <button onclick="document.getElementById('modal-bulk').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('wms.bins.bulk') }}" class="p-6 space-y-3">
                @csrf
                <input type="hidden" name="warehouse_id" value="{{ $warehouseId }}">
                @php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; @endphp
                <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Zone</label>
                    <select name="zone_id" class="{{ $cls }}">
                        <option value="">-- Tanpa Zone --</option>
                        @foreach ($zones as $z)
                            <option value="{{ $z->id }}">{{ $z->code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Aisle Dari</label><input
                            type="number" name="aisle_from" required min="1" value="1"
                            class="{{ $cls }}"></div>
                    <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Aisle Sampai</label><input
                            type="number" name="aisle_to" required min="1" value="3"
                            class="{{ $cls }}"></div>
                    <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Rack Dari</label><input
                            type="number" name="rack_from" required min="1" value="1"
                            class="{{ $cls }}"></div>
                    <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Rack Sampai</label><input
                            type="number" name="rack_to" required min="1" value="5"
                            class="{{ $cls }}"></div>
                    <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Shelf Dari</label><input
                            type="number" name="shelf_from" required min="1" value="1"
                            class="{{ $cls }}"></div>
                    <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Shelf Sampai</label><input
                            type="number" name="shelf_to" required min="1" value="4"
                            class="{{ $cls }}"></div>
                </div>
                <div><select name="bin_type" class="{{ $cls }}">
                        <option value="storage">Storage</option>
                        <option value="picking">Picking</option>
                    </select></div>
                <button type="submit"
                    class="w-full py-2 text-sm bg-purple-600 text-white rounded-xl hover:bg-purple-700">Generate
                    Bins</button>
            </form>
        </div>
    </div>

    {{-- Modal Putaway --}}
    <div id="modal-putaway" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-sm shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">📦 Putaway Barang</h3>
                <button onclick="document.getElementById('modal-putaway').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('wms.putaway') }}" class="p-6 space-y-3">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; @endphp
                <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Produk *</label>
                    <select name="product_id" required class="{{ $cls }}">
                        <option value="">-- Pilih --</option>
                    </select>
                </div>
                <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Bin Lokasi *</label>
                    <select name="bin_id" required class="{{ $cls }}">
                        <option value="">-- Pilih --</option>
                        @foreach ($bins as $b)
                            <option value="{{ $b->id }}">{{ $b->code }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label class="block text-xs text-gray-600 dark:text-slate-400 mb-1">Qty *</label><input
                        type="number" name="quantity" required min="0.001" step="0.001"
                        class="{{ $cls }}"></div>
                <button type="submit"
                    class="w-full py-2 text-sm bg-amber-600 text-white rounded-xl hover:bg-amber-700">Putaway</button>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function updateBatchCount() {
                const checked = document.querySelectorAll('.bin-checkbox:checked');
                const count = checked.length;
                document.getElementById('selected-count').textContent = count;
                const btn = document.getElementById('btn-batch-label');
                if (count > 0) {
                    btn.classList.remove('hidden');
                } else {
                    btn.classList.add('hidden');
                }
            }

            function selectAllBins() {
                const checkboxes = document.querySelectorAll('.bin-checkbox');
                const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                checkboxes.forEach(cb => {
                    cb.checked = !allChecked;
                });
                updateBatchCount();
            }

            function printSelectedBins() {
                const checked = document.querySelectorAll('.bin-checkbox:checked');
                if (checked.length === 0) return;
                const container = document.getElementById('batch-label-inputs');
                container.innerHTML = '';
                checked.forEach(cb => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'bin_ids[]';
                    input.value = cb.dataset.binId;
                    container.appendChild(input);
                });
                document.getElementById('form-batch-label').submit();
            }
        </script>
    @endpush
</x-app-layout>
