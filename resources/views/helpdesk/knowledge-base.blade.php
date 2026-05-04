<x-app-layout>
    <x-slot name="header">Knowledge Base</x-slot>

    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <form method="GET" class="flex-1 flex flex-wrap gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari artikel..."
                class="flex-1 min-w-[150px] px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="category" class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900">
                <option value="">Semua Kategori</option>
                @foreach($categories ?? [] as $cat)<option value="{{ $cat }}" @selected(request('category')===$cat)>{{ ucfirst($cat) }}</option>@endforeach
            </select>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
        </form>
        <a href="{{ route('helpdesk.index') }}" class="px-3 py-2 text-sm text-gray-500">← Helpdesk</a>
        @canmodule('helpdesk', 'create')
        <button onclick="document.getElementById('modal-add-kb').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Artikel</button>
        @endcanmodule
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($articles as $a)
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <div class="flex items-start justify-between mb-2">
                <span class="px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700">{{ ucfirst($a->category) }}</span>
                @canmodule('helpdesk', 'delete')
                <form method="POST" action="{{ route('helpdesk.kb.destroy', $a) }}" onsubmit="return confirm('Hapus?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-gray-400 hover:text-red-500 text-xs">✕</button>
                </form>
                @endcanmodule
            </div>
            <h3 class="font-semibold text-gray-900 mb-2">{{ $a->title }}</h3>
            <p class="text-xs text-gray-500 line-clamp-3">{{ Str::limit(strip_tags($a->body), 150) }}</p>
            <p class="text-xs text-gray-400 mt-2">{{ $a->views }} views · {{ $a->created_at->format('d/m/Y') }}</p>
        </div>
        @empty
        <div class="col-span-full text-center py-12 text-gray-400">Belum ada artikel.</div>
        @endforelse
    </div>
    @if($articles->hasPages())<div class="mt-4">{{ $articles->links() }}</div>@endif

    {{-- Modal Add Article --}}
    <div id="modal-add-kb" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Buat Artikel</h3>
                <button onclick="document.getElementById('modal-add-kb').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="{{ route('helpdesk.kb.store') }}" class="p-6 space-y-4">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900'; @endphp
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Judul *</label><input type="text" name="title" required class="{{ $cls }}"></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Kategori *</label>
                    <select name="category" required class="{{ $cls }}">
                        <option value="general">Umum</option><option value="billing">Billing</option><option value="technical">Teknis</option><option value="delivery">Pengiriman</option><option value="product">Produk</option><option value="faq">FAQ</option>
                    </select>
                </div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Isi Artikel *</label><textarea name="body" required rows="6" class="{{ $cls }}"></textarea></div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-add-kb').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
