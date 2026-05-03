<x-app-layout>
    <x-slot name="header">Buat Price List Baru</x-slot>

    @if($errors->any())
    <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
        <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form method="POST" action="{{ route('price-lists.store') }}" class="space-y-6">
        @csrf

        <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-4">
            <h3 class="font-semibold text-gray-900">Informasi Price List</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Harga Grosir, Kontrak PT ABC"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Kode (opsional)</label>
                    <input type="text" name="code" value="{{ old('code') }}" placeholder="PL-001"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tipe *</label>
                    <select name="type" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="tier" {{ old('type') === 'tier' ? 'selected' : '' }}>Tier / Level</option>
                        <option value="contract" {{ old('type') === 'contract' ? 'selected' : '' }}>Kontrak</option>
                        <option value="promo" {{ old('type') === 'promo' ? 'selected' : '' }}>Promosi</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Berlaku Dari</label>
                    <input type="date" name="valid_from" value="{{ old('valid_from') }}"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Berlaku Sampai</label>
                    <input type="date" name="valid_until" value="{{ old('valid_until') }}"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi</label>
                <textarea name="description" rows="2" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
            </div>
        </div>

        {{-- Product Items --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-900">Harga Produk</h3>
                <button type="button" onclick="addItem()" class="px-3 py-1.5 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700">+ Tambah Produk</button>
            </div>

            <div id="items-container" class="space-y-3">
                <div class="item-row grid grid-cols-12 gap-2 items-end">
                    <div class="col-span-4">
                        <label class="block text-xs text-gray-500 mb-1">Produk *</label>
                        <select name="items[0][product_id]" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih --</option>
                            @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-3">
                        <label class="block text-xs text-gray-500 mb-1">Harga Khusus (Rp) *</label>
                        <input type="number" name="items[0][price]" required min="0" step="1"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs text-gray-500 mb-1">Diskon %</label>
                        <input type="number" name="items[0][discount_percent]" min="0" max="100" step="0.1" value="0"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs text-gray-500 mb-1">Min Qty</label>
                        <input type="number" name="items[0][min_qty]" min="1" step="1" value="1"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="col-span-1 flex justify-end">
                        <button type="button" onclick="removeItem(this)" class="p-2 text-red-500 hover:bg-red-50 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Assign Customers --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Assign ke Customer (opsional)</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                @foreach($customers as $c)
                <label class="flex items-center gap-2 p-2 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50">
                    <input type="checkbox" name="customer_ids[]" value="{{ $c->id }}" class="rounded">
                    <span class="text-sm text-gray-700 truncate">{{ $c->name }}</span>
                </label>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('price-lists.index') }}" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</a>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan Price List</button>
        </div>
    </form>

    @push('scripts')
    <script>
    let itemCount = 1;
    const products = @json($products->map(function($p) { return ['id' => $p->id, 'name' => $p->name]; }));

    function addItem() {
        const container = document.getElementById('items-container');
        const idx = itemCount++;
        const opts = products.map(p => `<option value="${p.id}">${p.name}</option>`).join('');
        container.insertAdjacentHTML('beforeend', `
            <div class="item-row grid grid-cols-12 gap-2 items-end">
                <div class="col-span-4">
                    <select name="items[${idx}][product_id]" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih --</option>${opts}
                    </select>
                </div>
                <div class="col-span-3">
                    <input type="number" name="items[${idx}][price]" required min="0" step="1" placeholder="Harga"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="col-span-2">
                    <input type="number" name="items[${idx}][discount_percent]" min="0" max="100" step="0.1" value="0" placeholder="Diskon %"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="col-span-2">
                    <input type="number" name="items[${idx}][min_qty]" min="1" step="1" value="1" placeholder="Min Qty"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="col-span-1 flex justify-end">
                    <button type="button" onclick="removeItem(this)" class="p-2 text-red-500 hover:bg-red-50 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        `);
    }

    function removeItem(btn) {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length > 1) btn.closest('.item-row').remove();
    }
    </script>
    @endpush
</x-app-layout>
