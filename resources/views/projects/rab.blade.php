<x-app-layout>
    <x-slot name="header">RAB — {{ $project->name }}</x-slot>

    @if (session('success'))
        <div
            class="mb-4 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
            {{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div
            class="mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
            {{ session('error') }}</div>
    @endif

    {{-- Back + Actions --}}
    <div class="flex items-center justify-between mb-5">
        <a href="{{ route('projects.show', $project) }}" class="text-sm text-blue-500 hover:text-blue-600">← Kembali ke
            Proyek</a>
        <div class="flex items-center gap-2">
            <a href="{{ route('projects.rab.export', $project) }}"
                class="px-3 py-1.5 text-xs border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 transition">📥
                Export CSV</a>
            <button onclick="document.getElementById('importModal').classList.remove('hidden')"
                class="px-3 py-1.5 text-xs border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 transition">Import
                CSV</button>
            <button onclick="document.getElementById('addModal').classList.remove('hidden')"
                class="px-3 py-1.5 text-xs bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">+ Tambah
                Item</button>
        </div>
    </div>

    {{-- Summary KPI --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Total RAB</p>
            <p class="text-lg font-bold text-blue-600">Rp
                {{ number_format($summary['total_rab'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Realisasi</p>
            <p
                class="text-lg font-bold {{ $summary['total_actual'] > $summary['total_rab'] ? 'text-red-500' : 'text-emerald-600' }}">
                Rp {{ number_format($summary['total_actual'], 0, ',', '.') }}
            </p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Selisih</p>
            @php $variance = $summary['total_rab'] - $summary['total_actual']; @endphp
            <p
                class="text-lg font-bold {{ $variance < 0 ? 'text-red-500' : 'text-emerald-600' }}">
                {{ $variance < 0 ? '-' : '' }}Rp {{ number_format(abs($variance), 0, ',', '.') }}
            </p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Item Pekerjaan</p>
            <p class="text-lg font-bold text-gray-900">{{ $summary['item_count'] }} <span
                    class="text-xs font-normal text-gray-400">item</span></p>
        </div>
    </div>

    {{-- Category Breakdown --}}
    @if ($byCategory->isNotEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
            <p class="text-xs font-semibold text-gray-500 mb-3 uppercase tracking-wider">Breakdown
                per Kategori</p>
            <div class="flex flex-wrap gap-3">
                @foreach ($byCategory as $cat)
                    @php $pct = $summary['total_rab'] > 0 ? round($cat->total_rab / $summary['total_rab'] * 100, 1) : 0; @endphp
                    <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-50 text-xs">
                        <span
                            class="font-medium text-gray-700 capitalize">{{ $cat->cat }}</span>
                        <span class="text-gray-400">Rp {{ number_format($cat->total_rab, 0, ',', '.') }}</span>
                        <span class="text-gray-300">({{ $pct }}%)</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- RAB Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50 text-left">
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 w-16">Kode</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500">Uraian Pekerjaan
                        </th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 text-right">Volume
                        </th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500">Satuan</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 text-right">Harga
                            Satuan</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 text-right">Koef
                        </th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 text-right">Jumlah
                            (RAB)</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 text-right">
                            Realisasi</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 w-20">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($tree as $group)
                        @include('projects._rab_row', ['item' => $group, 'depth' => 0])
                        @foreach ($group->children as $child)
                            @include('projects._rab_row', ['item' => $child, 'depth' => 1])
                            @foreach ($child->children as $grandchild)
                                @include('projects._rab_row', ['item' => $grandchild, 'depth' => 2])
                            @endforeach
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center text-gray-400">
                                Belum ada item RAB. Klik "Tambah Item" atau "Import CSV" untuk memulai.
                            </td>
                        </tr>
                    @endforelse

                    {{-- Grand Total --}}
                    @if ($tree->isNotEmpty())
                        <tr class="bg-gray-50 font-bold">
                            <td class="px-4 py-3" colspan="6">
                                <span class="text-gray-900">GRAND TOTAL</span>
                            </td>
                            <td class="px-4 py-3 text-right text-blue-600">Rp
                                {{ number_format($summary['total_rab'], 0, ',', '.') }}</td>
                            <td
                                class="px-4 py-3 text-right {{ $summary['total_actual'] > $summary['total_rab'] ? 'text-red-500' : 'text-emerald-600' }}">
                                Rp {{ number_format($summary['total_actual'], 0, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- Add Item Modal --}}
    <div id="addModal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div
            class="bg-white rounded-2xl border border-gray-200 w-full max-w-lg p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-gray-900">Tambah Item RAB</h3>
                <button onclick="document.getElementById('addModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="{{ route('projects.rab.store', $project) }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tipe</label>
                        <select name="type" id="rab-type" onchange="toggleItemFields()"
                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900">
                            <option value="group">Grup / Header</option>
                            <option value="item" selected>Item Pekerjaan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kode</label>
                        <input type="text" name="code" placeholder="I.1.a"
                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Uraian Pekerjaan
                        *</label>
                    <input type="text" name="name" required placeholder="Pengecoran Lantai 1"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Parent
                        (Grup)</label>
                    <select name="parent_id"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900">
                        <option value="">— Tanpa parent (root) —</option>
                        @foreach (\App\Models\RabItem::where('project_id', $project->id)->where('type', 'group')->orderBy('sort_order')->get() as $g)
                            <option value="{{ $g->id }}">
                                {{ $g->code ? $g->code . ' — ' : '' }}{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="item-fields">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-xs font-medium text-gray-600 mb-1">Kategori</label>
                            <select name="category"
                                class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900">
                                <option value="">—</option>
                                <option value="material">Material</option>
                                <option value="labor">Upah / Tenaga</option>
                                <option value="equipment">Alat / Sewa</option>
                                <option value="subcontract">Subkontraktor</option>
                                <option value="overhead">Overhead</option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-xs font-medium text-gray-600 mb-1">Satuan</label>
                            <input type="text" name="unit" placeholder="m3, m2, kg, ls"
                                class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900">
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-4 mt-4">
                        <div>
                            <label
                                class="block text-xs font-medium text-gray-600 mb-1">Volume</label>
                            <input type="number" name="volume" step="0.001" placeholder="0"
                                class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Harga
                                Satuan</label>
                            <input type="number" name="unit_price" step="1" placeholder="0"
                                class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900">
                        </div>
                        <div>
                            <label
                                class="block text-xs font-medium text-gray-600 mb-1">Koefisien</label>
                            <input type="number" name="coefficient" step="0.0001" value="1"
                                class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900">
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Catatan</label>
                    <textarea name="notes" rows="2"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900"></textarea>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 rounded-lg text-sm border border-gray-200 text-gray-700 hover:bg-gray-50">Batal</button>
                    <button type="submit"
                        class="flex-1 px-4 py-2 rounded-lg text-sm bg-blue-600 hover:bg-blue-700 text-white font-medium">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Import Modal --}}
    <div id="importModal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div
            class="bg-white rounded-2xl border border-gray-200 w-full max-w-md p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-gray-900">Import RAB dari CSV</h3>
                <button onclick="document.getElementById('importModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="{{ route('projects.rab.import', $project) }}" enctype="multipart/form-data"
                class="space-y-4">
                @csrf
                <p class="text-xs text-gray-500">Format kolom: <code
                        class="bg-gray-100 px-1 rounded">Kode, Uraian Pekerjaan, Tipe, Kategori,
                        Volume, Satuan, Harga Satuan, Koefisien</code></p>
                <input type="file" name="file" accept=".csv,.txt" required
                    class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-gray-100 file:text-gray-700">
                <div class="flex gap-3">
                    <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 rounded-lg text-sm border border-gray-200 text-gray-700">Batal</button>
                    <button type="submit"
                        class="flex-1 px-4 py-2 rounded-lg text-sm bg-blue-600 hover:bg-blue-700 text-white font-medium">Import</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function toggleItemFields() {
                const type = document.getElementById('rab-type').value;
                document.getElementById('item-fields').style.display = type === 'item' ? '' : 'none';
            }

            function openActualModal(id, name, currentCost, currentVolume) {
                document.getElementById('actual-item-name').textContent = name;
                document.getElementById('actual-cost-input').value = currentCost || '';
                document.getElementById('actual-volume-input').value = currentVolume || '';
                document.getElementById('actual-form').action = '/projects/rab/' + id + '/actual';
                document.getElementById('actualModal').classList.remove('hidden');
            }
        </script>
    @endpush

    {{-- Record Actual Modal --}}
    <div id="actualModal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div
            class="bg-white rounded-2xl border border-gray-200 w-full max-w-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-gray-900">Catat Realisasi</h3>
                <button onclick="document.getElementById('actualModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <p class="text-sm text-gray-500 mb-4" id="actual-item-name"></p>
            <form method="POST" id="actual-form" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Realisasi Biaya
                        (Rp)</label>
                    <input type="number" name="actual_cost" id="actual-cost-input" step="1"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Realisasi
                        Volume</label>
                    <input type="number" name="actual_volume" id="actual-volume-input" step="0.001"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900">
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="document.getElementById('actualModal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 rounded-lg text-sm border border-gray-200 text-gray-700">Batal</button>
                    <button type="submit"
                        class="flex-1 px-4 py-2 rounded-lg text-sm bg-emerald-600 hover:bg-emerald-700 text-white font-medium">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
