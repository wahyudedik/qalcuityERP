<x-app-layout>
    <x-slot name="header">Resep / Bill of Materials (BOM)</x-slot>

    <div class="flex justify-between items-center mb-4">
        <a href="{{ route('production.index') }}" class="text-sm text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-white">← Work Order</a>
        <button onclick="document.getElementById('modal-create-recipe').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Tambah Resep</button>
    </div>

    <div class="space-y-4">
        @forelse($recipes as $recipe)
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ $recipe->name }}</h3>
                    <p class="text-sm text-gray-500 dark:text-slate-400">
                        Produk: {{ $recipe->product->name }} — Batch: {{ $recipe->batch_size }} {{ $recipe->batch_unit }}
                    </p>
                </div>
                <span class="text-xs px-2 py-1 rounded-full {{ $recipe->is_active ? 'bg-green-500/20 text-green-400' : 'bg-gray-500/20 text-gray-400' }}">
                    {{ $recipe->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach($recipe->ingredients as $ing)
                <div class="px-3 py-1.5 text-xs rounded-xl bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-slate-300">
                    <span class="font-medium">{{ $ing->product->name }}</span>
                    <span class="text-gray-500 dark:text-slate-500 ml-1">{{ $ing->quantity_per_batch }} {{ $ing->unit }}</span>
                </div>
                @endforeach
            </div>
            @if($recipe->notes)
            <p class="mt-2 text-xs text-gray-400 dark:text-slate-500">{{ $recipe->notes }}</p>
            @endif
        </div>
        @empty
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-12 text-center text-gray-400 dark:text-slate-500">
            Belum ada resep. Tambahkan resep untuk menghitung biaya bahan baku otomatis.
        </div>
        @endforelse
    </div>

    @if($recipes->hasPages())
    <div class="mt-4">{{ $recipes->links() }}</div>
    @endif

    {{-- Modal Tambah Resep --}}
    <div id="modal-create-recipe" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10 sticky top-0 bg-white dark:bg-[#1e293b]">
                <h3 class="font-semibold text-gray-900 dark:text-white">Tambah Resep / BOM</h3>
                <button onclick="document.getElementById('modal-create-recipe').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('production.recipes.store') }}" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Produk Jadi *</label>
                        <select name="product_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Produk --</option>
                            @foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama Resep *</label>
                        <input type="text" name="name" required placeholder="Resep Standar A"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Ukuran Batch *</label>
                        <input type="number" name="batch_size" required min="0.001" step="0.001" placeholder="100"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Satuan Batch *</label>
                        <input type="text" name="batch_unit" required placeholder="pcs / kg / liter"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Catatan</label>
                        <input type="text" name="notes" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                {{-- Ingredients --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-medium text-gray-600 dark:text-slate-400">Bahan Baku *</label>
                        <button type="button" onclick="addIngredient()" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">+ Tambah Bahan</button>
                    </div>
                    <div id="ingredients" class="space-y-2">
                        <div class="ingredient grid grid-cols-12 gap-2 items-center">
                            <div class="col-span-5">
                                <select name="ingredients[0][product_id]" required class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">-- Bahan --</option>
                                    @foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                                </select>
                            </div>
                            <div class="col-span-3">
                                <input type="number" name="ingredients[0][quantity_per_batch]" placeholder="Qty" min="0.001" step="0.001" required class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="col-span-3">
                                <input type="text" name="ingredients[0][unit]" placeholder="Satuan" required class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="col-span-1 text-center">
                                <button type="button" onclick="this.closest('.ingredient').remove()" class="text-red-500 hover:text-red-700 text-lg leading-none">×</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-create-recipe').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan Resep</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    let ingCount = 1;
    const productOptions = `@foreach($products as $p)<option value="{{ $p->id }}">{{ addslashes($p->name) }}</option>@endforeach`;

    function addIngredient() {
        const i = ingCount++;
        const div = document.createElement('div');
        div.className = 'ingredient grid grid-cols-12 gap-2 items-center';
        div.innerHTML = `
            <div class="col-span-5">
                <select name="ingredients[${i}][product_id]" required class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Bahan --</option>${productOptions}
                </select>
            </div>
            <div class="col-span-3">
                <input type="number" name="ingredients[${i}][quantity_per_batch]" placeholder="Qty" min="0.001" step="0.001" required class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="col-span-3">
                <input type="text" name="ingredients[${i}][unit]" placeholder="Satuan" required class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="col-span-1 text-center">
                <button type="button" onclick="this.closest('.ingredient').remove()" class="text-red-500 hover:text-red-700 text-lg leading-none">×</button>
            </div>`;
        document.getElementById('ingredients').appendChild(div);
    }
    </script>
    @endpush
</x-app-layout>
