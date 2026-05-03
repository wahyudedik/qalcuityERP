<x-app-layout>
    <x-slot name="header">Bill of Materials (BOM)</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <form method="GET" class="flex-1 flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari BOM / produk..."
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
        </form>
        @canmodule('manufacturing', 'create')
        <button onclick="document.getElementById('modal-create-bom').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Buat BOM</button>
        @endcanmodule
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nama BOM</th>
                        <th class="px-4 py-3 text-left">Produk Jadi</th>
                        <th class="px-4 py-3 text-right">Batch Size</th>
                        <th class="px-4 py-3 text-center">Komponen</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Circular Check</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($boms as $bom)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $bom->name }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $bom->product->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-right text-gray-900">
                                {{ number_format($bom->batch_size, 0, ',', '.') }} {{ $bom->batch_unit }}</td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700">
                                    {{ $bom->lines->count() }} item
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($bom->is_active)
                                    <span
                                        class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700">Aktif</span>
                                @else
                                    <span
                                        class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-500">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $hasCircular = false;
                                    try {
                                        $hasCircular = $bom->hasCircularReference();
                                    } catch (\Exception $e) {
                                        $hasCircular = true;
                                    }
                                @endphp
                                @if ($hasCircular)
                                    <span
                                        class="px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-700"
                                        title="Circular reference terdeteksi! Perlu diperbaiki.">
                                        ⚠️ Circular
                                    </span>
                                @else
                                    <span
                                        class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700">
                                        ✓ OK
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button onclick="openDetailModal({{ $bom->id }})"
                                        class="text-xs px-2 py-1 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50">Detail</button>
                                    @canmodule('manufacturing', 'delete')
                                    <form method="POST" action="{{ url('manufacturing/bom') }}/{{ $bom->id }}"
                                        class="inline" onsubmit="return confirm('Hapus BOM ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="text-xs px-2 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700">Hapus</button>
                                    </form>
                                    @endcanmodule
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-400">Belum
                                ada BOM. Buat BOM pertama untuk memulai.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($boms->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $boms->links() }}</div>
        @endif
    </div>

    {{-- BOM Detail Modals (per BOM) --}}
    @foreach ($boms as $bom)
        <div id="modal-detail-{{ $bom->id }}"
            class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
            <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl max-h-[80vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">{{ $bom->name }}</h3>
                    <button
                        onclick="document.getElementById('modal-detail-{{ $bom->id }}').classList.add('hidden')"
                        class="text-gray-400 hover:text-gray-600">✕</button>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-3 mb-4 text-sm">
                        <div><span class="text-gray-500">Produk:</span> <span
                                class="text-gray-900">{{ $bom->product->name ?? '-' }}</span></div>
                        <div><span class="text-gray-500">Batch:</span> <span
                                class="text-gray-900">{{ $bom->batch_size }}
                                {{ $bom->batch_unit }}</span></div>
                    </div>
                    <h4 class="text-xs font-semibold text-gray-500 uppercase mb-2">Komponen</h4>
                    <table class="w-full text-sm">
                        <thead class="text-xs text-gray-500">
                            <tr>
                                <th class="text-left py-1">Material</th>
                                <th class="text-right py-1">Qty/Batch</th>
                                <th class="text-left py-1">Unit</th>
                                <th class="text-left py-1">Sub-BOM</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($bom->lines as $line)
                                <tr>
                                    <td class="py-1.5 text-gray-900">{{ $line->product->name ?? '-' }}
                                    </td>
                                    <td class="py-1.5 text-right text-gray-900">
                                        {{ number_format($line->quantity_per_batch, 3) }}</td>
                                    <td class="py-1.5 text-gray-500">{{ $line->unit }}</td>
                                    <td class="py-1.5">
                                        @if ($line->childBom)
                                            <span
                                                class="px-1.5 py-0.5 rounded text-xs bg-purple-100 text-purple-700">{{ $line->childBom->name }}</span>
                                        @else
                                            <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Modal Create BOM --}}
    <div id="modal-create-bom" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Buat BOM Baru</h3>
                <button onclick="document.getElementById('modal-create-bom').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="{{ route('manufacturing.bom.store') }}" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Produk Jadi
                            *</label>
                        <select name="product_id" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                            <option value="">-- Pilih Produk --</option>
                            @foreach ($products as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama BOM
                            *</label>
                        <input type="text" name="name" required placeholder="BOM Produk A v1"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Batch Size
                                *</label>
                            <input type="number" name="batch_size" required min="0.001" step="0.001"
                                value="1"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Unit
                                *</label>
                            <input type="text" name="batch_unit" required value="pcs"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Catatan</label>
                        <input type="text" name="notes"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    </div>
                </div>

                {{-- Dynamic Lines --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-xs font-semibold text-gray-500 uppercase">Komponen Material
                        </h4>
                        <button type="button" onclick="addBomLine()"
                            class="text-xs px-2 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700">+
                            Tambah</button>
                    </div>
                    <div id="bom-lines" class="space-y-2"></div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button"
                        onclick="document.getElementById('modal-create-bom').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan
                        BOM</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            const products = @json($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name]));
            const allBoms = @json($allBoms->map(fn($b) => ['id' => $b->id, 'name' => $b->name]));
            let lineIdx = 0;

            function addBomLine() {
                const container = document.getElementById('bom-lines');
                const i = lineIdx++;
                const div = document.createElement('div');
                div.className = 'grid grid-cols-12 gap-2 items-end';
                div.id = 'bom-line-' + i;

                let prodOpts = '<option value="">Material</option>';
                products.forEach(p => {
                    prodOpts += '<option value="' + p.id + '">' + p.name + '</option>';
                });

                let bomOpts = '<option value="">Tanpa Sub-BOM</option>';
                allBoms.forEach(b => {
                    bomOpts += '<option value="' + b.id + '">' + b.name + '</option>';
                });

                const cls =
                    'w-full px-2 py-1.5 text-xs rounded-lg border border-gray-200 bg-gray-50 text-gray-900';

                div.innerHTML = `
            <div class="col-span-4"><select name="lines[${i}][product_id]" required class="${cls}">${prodOpts}</select></div>
            <div class="col-span-2"><input type="number" name="lines[${i}][quantity_per_batch]" required min="0.001" step="0.001" placeholder="Qty" class="${cls}"></div>
            <div class="col-span-2"><input type="text" name="lines[${i}][unit]" required placeholder="Unit" value="pcs" class="${cls}"></div>
            <div class="col-span-3"><select name="lines[${i}][child_bom_id]" class="${cls}">${bomOpts}</select></div>
            <div class="col-span-1"><button type="button" onclick="document.getElementById('bom-line-${i}').remove()" class="text-red-500 hover:text-red-700 text-xs">✕</button></div>
        `;
                container.appendChild(div);
            }

            function openDetailModal(id) {
                document.getElementById('modal-detail-' + id).classList.remove('hidden');
            }

            // Start with 1 line
            addBomLine();
        </script>
    @endpush
</x-app-layout>
