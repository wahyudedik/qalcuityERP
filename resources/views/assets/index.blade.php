<x-app-layout>
    <x-slot name="header">Manajemen Aset</x-slot>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        @php
            $tid = auth()->user()->tenant_id;
            $totalAssets = \App\Models\Asset::where('tenant_id',$tid)->count();
            $activeAssets = \App\Models\Asset::where('tenant_id',$tid)->where('status','active')->count();
        @endphp
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Aset</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalAssets }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Aset Aktif</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $activeAssets }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Nilai Buku</p>
            <p class="text-lg font-bold text-blue-600 dark:text-blue-400 mt-1">Rp {{ number_format($totalValue/1000000,1) }}jt</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Harga Perolehan</p>
            <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">Rp {{ number_format($totalCost/1000000,1) }}jt</p>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 mb-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-2 flex-1">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / kode aset..."
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="category" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                <option value="">Semua Kategori</option>
                @foreach(['vehicle'=>'Kendaraan','machine'=>'Mesin','equipment'=>'Peralatan','furniture'=>'Furnitur','building'=>'Bangunan'] as $v=>$l)
                <option value="{{ $v }}" @selected(request('category')===$v)>{{ $l }}</option>
                @endforeach
            </select>
            <select name="status" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                <option value="">Semua Status</option>
                <option value="active" @selected(request('status')==='active')>Aktif</option>
                <option value="maintenance" @selected(request('status')==='maintenance')>Maintenance</option>
                <option value="disposed" @selected(request('status')==='disposed')>Disposed</option>
            </select>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
        </form>
        <div class="flex gap-2">
            <a href="{{ route('assets.maintenance') }}" class="px-3 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Maintenance</a>
            <button onclick="document.getElementById('modal-depreciate').classList.remove('hidden')"
                class="px-3 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Depresiasi</button>
            <button onclick="document.getElementById('modal-add-asset').classList.remove('hidden')"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Aset</button>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Aset</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Kode</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Kategori</th>
                        <th class="px-4 py-3 text-right hidden sm:table-cell">Harga Beli</th>
                        <th class="px-4 py-3 text-right">Nilai Buku</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Depresiasi/bln</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($assets as $asset)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900 dark:text-white">{{ $asset->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-slate-400">{{ $asset->brand ?? '' }} {{ $asset->model ?? '' }}</p>
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell font-mono text-xs text-gray-500 dark:text-slate-400">{{ $asset->asset_code }}</td>
                        <td class="px-4 py-3 hidden md:table-cell text-gray-500 dark:text-slate-400 capitalize">{{ $asset->category }}</td>
                        <td class="px-4 py-3 text-right hidden sm:table-cell text-gray-700 dark:text-slate-300">Rp {{ number_format($asset->purchase_price,0,',','.') }}</td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">Rp {{ number_format($asset->current_value,0,',','.') }}</td>
                        <td class="px-4 py-3 text-right hidden md:table-cell text-gray-500 dark:text-slate-400">Rp {{ number_format($asset->monthlyDepreciation(),0,',','.') }}</td>
                        <td class="px-4 py-3 text-center">
                            @php $sc = ['active'=>'green','maintenance'=>'yellow','disposed'=>'red','retired'=>'gray'][$asset->status] ?? 'gray'; @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs bg-{{ $sc }}-100 text-{{ $sc }}-700 dark:bg-{{ $sc }}-500/20 dark:text-{{ $sc }}-400">
                                {{ ucfirst($asset->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button onclick="openEditAsset({{ $asset->id }}, '{{ addslashes($asset->name) }}', '{{ addslashes($asset->location ?? '') }}', '{{ $asset->status }}')"
                                class="p-1.5 rounded-lg text-gray-500 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-white/10">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada aset terdaftar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($assets->hasPages())
        <div class="px-4 py-3 border-t border-gray-100 dark:border-white/5">{{ $assets->links() }}</div>
        @endif
    </div>

    {{-- Modal Tambah Aset --}}
    <div id="modal-add-asset" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10 sticky top-0 bg-white dark:bg-[#1e293b]">
                <h3 class="font-semibold text-gray-900 dark:text-white">Daftarkan Aset Baru</h3>
                <button onclick="document.getElementById('modal-add-asset').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('assets.store') }}" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama Aset *</label>
                        <input type="text" name="name" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Kategori *</label>
                        <select name="category" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="equipment">Peralatan</option>
                            <option value="vehicle">Kendaraan</option>
                            <option value="machine">Mesin</option>
                            <option value="furniture">Furnitur</option>
                            <option value="building">Bangunan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Lokasi</label>
                        <input type="text" name="location" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Merek</label>
                        <input type="text" name="brand" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Model/Tipe</label>
                        <input type="text" name="model" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanggal Beli *</label>
                        <input type="date" name="purchase_date" value="{{ today()->format('Y-m-d') }}" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Harga Perolehan (Rp) *</label>
                        <input type="number" name="purchase_price" min="0" step="100000" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nilai Sisa (Rp)</label>
                        <input type="number" name="salvage_value" value="0" min="0" step="100000" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Umur Ekonomis (tahun) *</label>
                        <input type="number" name="useful_life_years" value="5" min="1" max="50" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Metode Depresiasi *</label>
                        <select name="depreciation_method" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="straight_line">Garis Lurus</option>
                            <option value="declining_balance">Saldo Menurun</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-add-asset').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit Aset --}}
    <div id="modal-edit-asset" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-sm shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Edit Aset</h3>
                <button onclick="document.getElementById('modal-edit-asset').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form id="form-edit-asset" method="POST" class="p-6 space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama *</label>
                    <input type="text" id="ea-name" name="name" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Lokasi</label>
                    <input type="text" id="ea-location" name="location" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Status</label>
                    <select id="ea-status" name="status" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="active">Aktif</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="disposed">Disposed</option>
                        <option value="retired">Retired</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Catatan</label>
                    <textarea name="notes" rows="2" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-edit-asset').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Depresiasi --}}
    <div id="modal-depreciate" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-sm shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Hitung Depresiasi</h3>
                <button onclick="document.getElementById('modal-depreciate').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('assets.depreciate') }}" class="p-6 space-y-4">
                @csrf
                <p class="text-sm text-gray-600 dark:text-slate-400">Hitung dan catat depresiasi semua aset aktif untuk periode yang dipilih.</p>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Periode *</label>
                    <input type="month" name="period" value="{{ now()->format('Y-m') }}" required
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-depreciate').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Hitung</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openEditAsset(id, name, location, status) {
        document.getElementById('form-edit-asset').action = '/assets/' + id;
        document.getElementById('ea-name').value = name;
        document.getElementById('ea-location').value = location;
        document.getElementById('ea-status').value = status;
        document.getElementById('modal-edit-asset').classList.remove('hidden');
    }
    </script>
    @endpush
</x-app-layout>
