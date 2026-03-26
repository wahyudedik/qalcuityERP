<x-app-layout>
    <x-slot name="header">Template Kontrak</x-slot>

    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <a href="{{ route('contracts.index') }}" class="px-3 py-2 text-sm text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-white">← Daftar Kontrak</a>
        <div class="flex-1"></div>
        @canmodule('contracts', 'create')
        <button onclick="document.getElementById('modal-add-tpl').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Template</button>
        @endcanmodule
    </div>

    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nama</th>
                        <th class="px-4 py-3 text-center">Kategori</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($templates as $t)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $t->name }}</td>
                        <td class="px-4 py-3 text-center text-xs text-gray-500 dark:text-slate-400">{{ ['service'=>'Jasa','lease'=>'Sewa','supply'=>'Supply','maintenance'=>'Maintenance','subscription'=>'Langganan'][$t->category] ?? $t->category }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($t->is_active)<span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400">Aktif</span>
                            @else<span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-slate-400">Nonaktif</span>@endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @canmodule('contracts', 'delete')
                            <form method="POST" action="{{ route('contracts.templates.destroy', $t) }}" class="inline" onsubmit="return confirm('Hapus template ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs px-2 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700">Hapus</button>
                            </form>
                            @endcanmodule
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada template.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($templates->hasPages())<div class="px-4 py-3 border-t border-gray-100 dark:border-white/5">{{ $templates->links() }}</div>@endif
    </div>

    {{-- Modal Add Template --}}
    <div id="modal-add-tpl" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-lg shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Buat Template</h3>
                <button onclick="document.getElementById('modal-add-tpl').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('contracts.templates.store') }}" class="p-6 space-y-4">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; @endphp
                <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama *</label><input type="text" name="name" required placeholder="Template Kontrak Sewa" class="{{ $cls }}"></div>
                <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Kategori *</label>
                    <select name="category" required class="{{ $cls }}">
                        <option value="service">Jasa</option><option value="lease">Sewa</option><option value="supply">Supply</option><option value="maintenance">Maintenance</option><option value="subscription">Langganan</option>
                    </select>
                </div>
                <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Body Template</label><textarea name="body_template" rows="4" placeholder="Isi template kontrak... Gunakan {customer_name}, {start_date}, {end_date}, {value}" class="{{ $cls }}"></textarea></div>
                <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Default Terms</label><textarea name="default_terms" rows="3" class="{{ $cls }}"></textarea></div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-add-tpl').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
