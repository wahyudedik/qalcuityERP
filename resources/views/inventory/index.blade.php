<x-app-layout>
    <x-slot name="header">Inventori & Produk</x-slot>

    @php $tid = auth()->user()->tenant_id; @endphp

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        @php
            $totalProducts = \App\Models\Product::where('tenant_id',$tid)->count();
            $activeProducts = \App\Models\Product::where('tenant_id',$tid)->where('is_active',true)->count();
            $totalStock = \App\Models\ProductStock::whereHas('product',fn($q)=>$q->where('tenant_id',$tid))->sum('quantity');
        @endphp
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Total Produk</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $totalProducts }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Produk Aktif</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ $activeProducts }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Total Stok</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ number_format($totalStock) }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Stok Menipis</p>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ $lowCount }}</p>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="bg-white rounded-2xl border border-gray-200 mb-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-2 flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / SKU..."
                    class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="category" class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    <option value="">Semua Kategori</option>
                    @foreach($categories ?? [] as $cat)
                        <option value="{{ $cat }}" @selected(request('category')===$cat)>{{ $cat }}</option>
                    @endforeach
                </select>
                <select name="status" class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    <option value="">Semua Status</option>
                    <option value="active" @selected(request('status')==='active')>Aktif</option>
                    <option value="inactive" @selected(request('status')==='inactive')>Nonaktif</option>
                    <option value="low" @selected(request('status')==='low')>Stok Menipis</option>
                </select>
                <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
            </form>
            <div class="flex gap-2">
                <a href="{{ route('inventory.movements') }}" class="px-3 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Riwayat Stok</a>
                <a href="{{ route('inventory.warehouses') }}" class="px-3 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Gudang</a>
                @canmodule('inventory', 'create')
                <button onclick="document.getElementById('modal-add-product').classList.remove('hidden')"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Produk</button>
                @endcanmodule
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Produk</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">SKU</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Kategori</th>
                        <th class="px-4 py-3 text-right">Harga Jual</th>
                        <th class="px-4 py-3 text-right">Stok</th>
                        <th class="px-4 py-3 text-center hidden lg:table-cell">Prediksi AI</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($products as $product)
                    @php $totalStock = $product->productStocks->sum('quantity'); $isLow = $totalStock <= $product->stock_min; @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900">{{ $product->name }}</p>
                            <p class="text-xs text-gray-500">{{ $product->unit }}</p>
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell text-gray-500 font-mono text-xs">{{ $product->sku }}</td>
                        <td class="px-4 py-3 hidden md:table-cell text-gray-500">{{ $product->category ?? '-' }}</td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900">Rp {{ number_format($product->price_sell,0,',','.') }}</td>
                        <td class="px-4 py-3 text-right">
                            <span class="font-semibold {{ $isLow ? 'text-red-600' : 'text-gray-900' }}">{{ $totalStock }}</span>
                            @if($isLow)<span class="ml-1 text-xs text-red-500">⚠</span>@endif
                        </td>
                        {{-- AI Prediction Cell --}}
                        <td class="px-4 py-3 text-center hidden lg:table-cell">
                            <div id="ai-inv-{{ $product->id }}" class="text-xs text-slate-500 italic">—</div>
                        </td>
                        <td class="px-4 py-3 text-center hidden sm:table-cell">
                            <span class="px-2 py-0.5 rounded-full text-xs {{ $product->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                @canmodule('inventory', 'create')
                                <button onclick="openAddStock({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ $product->unit }}')"
                                    class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50" title="Tambah Stok">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                </button>
                                @endcanmodule
                                <button onclick="openAiDetail({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ $product->unit }}')"
                                    class="p-1.5 rounded-lg text-purple-500 hover:bg-purple-50" title="Analisis AI">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.364.364A4.004 4.004 0 0112 16a4.004 4.004 0 01-2.772-1.1l-.364-.364z"/></svg>
                                </button>
                                @canmodule('inventory', 'edit')
                                <button onclick="openEditProduct({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ $product->sku }}', '{{ addslashes($product->category ?? '') }}', '{{ $product->unit }}', {{ $product->price_sell }}, {{ $product->price_buy }}, {{ $product->stock_min }}, {{ $product->is_active ? 'true' : 'false' }}, '{{ $product->image }}')"
                                    class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                @endcanmodule
                                @canmodule('inventory', 'delete')
                                <form method="POST" action="{{ route('inventory.destroy', $product) }}" onsubmit="return confirm('Hapus produk ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-red-500 hover:bg-red-50" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                                @endcanmodule
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">Belum ada produk. Tambahkan produk pertama Anda.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $products->links() }}</div>
        @endif
    </div>

    {{-- Modal Tambah Produk --}}
    <div id="modal-add-product" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Tambah Produk</h3>
                <button onclick="document.getElementById('modal-add-product').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="{{ route('inventory.store') }}" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Image Upload --}}
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Foto Produk</label>
                        <div class="flex items-center gap-4">
                            <div id="add-img-preview" class="w-16 h-16 rounded-xl bg-gray-100 border border-gray-200 flex items-center justify-center overflow-hidden shrink-0">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <label class="cursor-pointer px-3 py-2 text-xs border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                                Pilih Gambar
                                <input type="file" name="image" accept="image/*" class="hidden" onchange="previewImage(this, 'add-img-preview')">
                            </label>
                            <span class="text-xs text-gray-400">JPG, PNG, WebP. Maks 2MB</span>
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama Produk *</label>
                        <input type="text" name="name" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">SKU (opsional)</label>
                        <input type="text" name="sku" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kategori</label>
                        <input type="text" name="category" list="cat-list" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <datalist id="cat-list">@foreach($categories ?? [] as $c)<option value="{{ $c }}">@endforeach</datalist>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Satuan *</label>
                        <input type="text" name="unit" value="pcs" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Harga Jual *</label>
                        <input type="number" name="price_sell" min="0" step="100" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Harga Beli</label>
                        <input type="number" name="price_buy" min="0" step="100" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Stok Minimum</label>
                        <input type="number" name="stock_min" value="5" min="0" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Stok Awal</label>
                        <input type="number" name="initial_stock" min="0" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Gudang (untuk stok awal)</label>
                        <select name="warehouse_id" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Gudang --</option>
                            @foreach($warehouses ?? [] as $wh)<option value="{{ $wh->id }}">{{ $wh->name }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-add-product').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit Produk --}}
    <div id="modal-edit-product" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Edit Produk</h3>
                <button onclick="document.getElementById('modal-edit-product').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form id="form-edit-product" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Image Upload --}}
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Foto Produk</label>
                        <div class="flex items-center gap-4">
                            <div id="edit-img-preview" class="w-16 h-16 rounded-xl bg-gray-100 border border-gray-200 flex items-center justify-center overflow-hidden shrink-0">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <label class="cursor-pointer px-3 py-2 text-xs border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                                Ganti Gambar
                                <input type="file" name="image" accept="image/*" class="hidden" onchange="previewImage(this, 'edit-img-preview')">
                            </label>
                            <span class="text-xs text-gray-400">JPG, PNG, WebP. Maks 2MB</span>
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama Produk *</label>
                        <input type="text" id="edit-name" name="name" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kategori</label>
                        <input type="text" id="edit-category" name="category" list="cat-list" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Satuan *</label>
                        <input type="text" id="edit-unit" name="unit" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Harga Jual *</label>
                        <input type="number" id="edit-price-sell" name="price_sell" min="0" step="100" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Harga Beli</label>
                        <input type="number" id="edit-price-buy" name="price_buy" min="0" step="100" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Stok Minimum</label>
                        <input type="number" id="edit-stock-min" name="stock_min" min="0" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="sm:col-span-2 flex items-center gap-2">
                        <input type="checkbox" id="edit-is-active" name="is_active" value="1" class="rounded">
                        <label for="edit-is-active" class="text-sm text-gray-700">Produk Aktif</label>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-edit-product').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Tambah Stok --}}
    <div id="modal-add-stock" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-sm shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Tambah Stok</h3>
                <button onclick="document.getElementById('modal-add-stock').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form id="form-add-stock" method="POST" class="p-6 space-y-4">
                @csrf
                <p id="stock-product-name" class="text-sm font-medium text-gray-900"></p>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Gudang *</label>
                    <select name="warehouse_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach($warehouses ?? [] as $wh)<option value="{{ $wh->id }}">{{ $wh->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Jumlah *</label>
                    <input type="number" name="quantity" min="1" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Catatan</label>
                    <input type="text" name="notes" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-add-stock').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Tambah</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal AI Inventory Detail --}}
    <div id="modal-ai-inventory" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 id="ai-modal-title" class="font-semibold text-gray-900 text-sm">Analisis AI Stok</h3>
                <button onclick="document.getElementById('modal-ai-inventory').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <div id="ai-modal-content" class="p-6 space-y-4"></div>
        </div>
    </div>

    @push('scripts')
    <script>
    // ── Toast Notification ────────────────────────────────────────
    function showToast(message, type = 'success') {
        const colors = {
            success: 'bg-green-600',
            error:   'bg-red-600',
            warning: 'bg-yellow-500',
            info:    'bg-blue-600',
        };
        const icons = {
            success: '✓',
            error:   '✕',
            warning: '⚠',
            info:    'ℹ',
        };
        const toast = document.createElement('div');
        toast.className = `fixed bottom-6 right-6 z-[9999] flex items-center gap-3 px-4 py-3 rounded-2xl text-white text-sm font-medium shadow-xl transition-all duration-300 translate-y-4 opacity-0 ${colors[type] || colors.success}`;
        toast.innerHTML = `<span class="text-base">${icons[type] || icons.success}</span><span>${message}</span>`;
        document.body.appendChild(toast);
        requestAnimationFrame(() => {
            toast.classList.remove('translate-y-4', 'opacity-0');
        });
        setTimeout(() => {
            toast.classList.add('translate-y-4', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }

    // Auto-show flash messages as toast
    @if(session('success'))
        showToast(@json(session('success')), 'success');
    @endif
    @if(session('error'))
        showToast(@json(session('error')), 'error');
    @endif
    @if($errors->any())
        showToast(@json($errors->first()), 'error');
    @endif

    // ── Image Preview ─────────────────────────────────────────────
    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                preview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover rounded-xl">`;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function openAddStock(id, name, unit) {
        document.getElementById('stock-product-name').textContent = name + ' (' + unit + ')';
        document.getElementById('form-add-stock').action = '{{ url("inventory") }}/' + id + '/stock';
        document.getElementById('modal-add-stock').classList.remove('hidden');
    }

    function openEditProduct(id, name, sku, category, unit, priceSell, priceBuy, stockMin, isActive, image) {
        document.getElementById('form-edit-product').action = '{{ url("inventory") }}/' + id;
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-category').value = category;
        document.getElementById('edit-unit').value = unit;
        document.getElementById('edit-price-sell').value = priceSell;
        document.getElementById('edit-price-buy').value = priceBuy;
        document.getElementById('edit-stock-min').value = stockMin;
        document.getElementById('edit-is-active').checked = isActive;

        // Set image preview
        const preview = document.getElementById('edit-img-preview');
        if (image) {
            preview.innerHTML = `<img src="${image}" class="w-full h-full object-cover rounded-xl">`;
        } else {
            preview.innerHTML = `<svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>`;
        }

        document.getElementById('modal-edit-product').classList.remove('hidden');
    }

    // ── AI: Batch analyze all products on page load ───────────────
    const analyzeAllUrl  = '{{ route("inventory.ai.analyze-all") }}';
    const stockoutBase   = '/inventory/ai/stockout/';
    const reorderBase    = '/inventory/ai/reorder/';

    const urgencyBadge = {
        critical: 'px-2 py-0.5 rounded-full bg-red-500/20 text-red-400 border border-red-500/20',
        warning:  'px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-400 border border-amber-500/20',
        ok:       'px-2 py-0.5 rounded-full bg-green-500/20 text-green-400 border border-green-500/20',
        unknown:  'px-2 py-0.5 rounded-full bg-white/10 text-slate-400',
    };
    const urgencyLabel = {
        critical: '🔴 Kritis',
        warning:  '🟡 Perhatian',
        ok:       '✓ Aman',
        unknown:  '— Belum ada data',
    };
    const trendIcon = { increasing: '↑', stable: '→', decreasing: '↓', unknown: '—' };

    async function loadBatchAnalysis() {
        try {
            const res  = await fetch(analyzeAllUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();
            const analysis = data.analysis ?? {};

            for (const [id, info] of Object.entries(analysis)) {
                const el = document.getElementById(`ai-inv-${id}`);
                if (!el) continue;
                const badge = urgencyBadge[info.urgency] ?? urgencyBadge.unknown;
                const label = urgencyLabel[info.urgency] ?? '—';
                const days  = info.days_remaining != null ? ` · ${info.days_remaining}h` : '';
                el.className = `text-xs ${badge}`;
                el.textContent = label + days;
                el.title = info.days_remaining != null
                    ? `Stok habis ~${info.days_remaining} hari lagi (avg keluar: ${info.avg_daily_out}/hari)`
                    : 'Tidak ada data penjualan';
            }
        } catch (e) { /* silent */ }
    }

    async function openAiDetail(productId, productName, unit) {
        document.getElementById('ai-modal-title').textContent = 'Analisis AI — ' + productName;
        document.getElementById('ai-modal-content').innerHTML =
            '<div class="animate-pulse text-slate-500 text-sm text-center py-4">Menganalisis...</div>';
        document.getElementById('modal-ai-inventory').classList.remove('hidden');

        try {
            const [predRes, reorderRes] = await Promise.all([
                fetch(stockoutBase + productId, { headers: { 'X-Requested-With': 'XMLHttpRequest' } }),
                fetch(reorderBase  + productId, { headers: { 'X-Requested-With': 'XMLHttpRequest' } }),
            ]);
            const predData   = await predRes.json();
            const reorderData = await reorderRes.json();
            const p = predData.prediction;
            const r = reorderData.suggestion;

            const urgColors = { critical: 'text-red-400 bg-red-500/10 border-red-500/20', warning: 'text-amber-400 bg-amber-500/10 border-amber-500/20', ok: 'text-green-400 bg-green-500/10 border-green-500/20', unknown: 'text-slate-400 bg-white/5 border-white/10' };
            const confColor = { high: 'text-green-400', medium: 'text-yellow-400', low: 'text-slate-400' };
            const urg = urgColors[p.urgency] ?? urgColors.unknown;

            let html = `
                <div class="p-3 rounded-xl border ${urg} text-sm mb-1">
                    <div class="font-medium mb-0.5">${urgencyLabel[p.urgency] ?? '—'}</div>
                    <div class="text-xs opacity-80">${esc(p.message)}</div>
                </div>
                <div class="grid grid-cols-3 gap-2 text-center">
                    <div class="bg-white/5 rounded-xl p-2 border border-white/10">
                        <p class="text-xs text-slate-400">Stok Saat Ini</p>
                        <p class="font-bold text-white">${p.current_stock} <span class="text-xs font-normal text-slate-400">${esc(unit)}</span></p>
                    </div>
                    <div class="bg-white/5 rounded-xl p-2 border border-white/10">
                        <p class="text-xs text-slate-400">Hari Tersisa</p>
                        <p class="font-bold text-white">${p.days_remaining ?? '—'}</p>
                    </div>
                    <div class="bg-white/5 rounded-xl p-2 border border-white/10">
                        <p class="text-xs text-slate-400">Tren</p>
                        <p class="font-bold text-white">${trendIcon[p.trend] ?? '—'} <span class="text-xs font-normal text-slate-400">${p.trend ?? ''}</span></p>
                    </div>
                </div>
                ${p.stockout_date ? `<p class="text-xs text-slate-400 text-center">Estimasi habis: <span class="text-white font-medium">${p.stockout_date}</span></p>` : ''}
                <hr class="border-white/10">
                <div>
                    <p class="text-xs text-slate-400 mb-2 font-medium uppercase tracking-wide">Saran Reorder</p>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="bg-purple-500/10 rounded-xl p-3 border border-purple-500/20 text-center">
                            <p class="text-xs text-slate-400">Qty Reorder</p>
                            <p class="text-xl font-bold text-purple-300">${r.reorder_qty}</p>
                            <p class="text-xs text-slate-500">${esc(unit)}</p>
                        </div>
                        <div class="bg-white/5 rounded-xl p-3 border border-white/10 text-center">
                            <p class="text-xs text-slate-400">Safety Stock</p>
                            <p class="text-xl font-bold text-white">${r.safety_stock}</p>
                            <p class="text-xs text-slate-500">${esc(unit)}</p>
                        </div>
                    </div>
                    <div class="mt-2 text-xs space-y-0.5">
                        <p class="${confColor[r.confidence] ?? 'text-slate-400'}">${esc(r.basis)}</p>
                        <p class="text-slate-500">Lead time: ${r.lead_time_days} hari · Cover: ${r.cover_days} hari · EOQ: ${r.economic_order} ${esc(unit)}</p>
                    </div>
                </div>
                <button onclick="prefillAddStock(${productId}, ${r.reorder_qty}, '${esc(productName)}', '${esc(unit)}')"
                    class="w-full py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl mt-1">
                    + Tambah Stok (${r.reorder_qty} ${esc(unit)})
                </button>`;

            document.getElementById('ai-modal-content').innerHTML = html;
        } catch (e) {
            document.getElementById('ai-modal-content').innerHTML =
                '<p class="text-red-400 text-sm">Gagal memuat analisis AI.</p>';
        }
    }

    function prefillAddStock(productId, qty, name, unit) {
        document.getElementById('modal-ai-inventory').classList.add('hidden');
        document.getElementById('stock-product-name').textContent = name + ' (' + unit + ')';
        document.getElementById('form-add-stock').action = '{{ url("inventory") }}/' + productId + '/stock';
        document.querySelector('#modal-add-stock input[name="quantity"]').value = qty;
        document.getElementById('modal-add-stock').classList.remove('hidden');
    }

    function esc(s) {
        return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/'/g,'&#39;');
    }

    document.addEventListener('DOMContentLoaded', loadBatchAnalysis);
    </script>
    @endpush
</x-app-layout>
