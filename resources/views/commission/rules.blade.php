<x-app-layout>
    <x-slot name="header">Rule Komisi</x-slot>

    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <a href="{{ route('commission.index') }}" class="px-3 py-2 text-sm text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-white">← Komisi Sales</a>
        <div class="flex-1"></div>
        @canmodule('commission', 'create')
        <button onclick="document.getElementById('modal-add-rule').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Rule</button>
        @endcanmodule
    </div>

    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nama</th>
                        <th class="px-4 py-3 text-center">Tipe</th>
                        <th class="px-4 py-3 text-right">Rate</th>
                        <th class="px-4 py-3 text-center">Basis</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($rules as $r)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $r->name }}</td>
                        <td class="px-4 py-3 text-center text-xs">
                            <span class="px-2 py-0.5 rounded-full {{ ['flat_pct'=>'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400','tiered'=>'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-400','flat_amount'=>'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400'][$r->type] ?? 'bg-gray-100 text-gray-500' }}">
                                {{ ['flat_pct'=>'Flat %','tiered'=>'Tiered','flat_amount'=>'Flat Rp'][$r->type] ?? $r->type }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right text-gray-900 dark:text-white">
                            @if($r->type === 'flat_pct') {{ $r->rate }}%
                            @elseif($r->type === 'flat_amount') Rp {{ number_format($r->rate, 0, ',', '.') }}
                            @else <span class="text-xs text-gray-400">{{ count($r->tiers ?? []) }} tier</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center text-xs text-gray-500 dark:text-slate-400">{{ ['revenue'=>'Revenue','profit'=>'Profit','quantity'=>'Qty'][$r->basis] ?? $r->basis }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($r->is_active)<span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400">Aktif</span>
                            @else<span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-slate-400">Nonaktif</span>@endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @canmodule('commission', 'delete')
                            <form method="POST" action="{{ route('commission.rules.destroy', $r) }}" class="inline" onsubmit="return confirm('Hapus rule ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs px-2 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700">Hapus</button>
                            </form>
                            @endcanmodule
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada rule komisi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($rules->hasPages())<div class="px-4 py-3 border-t border-gray-100 dark:border-white/5">{{ $rules->links() }}</div>@endif
    </div>

    {{-- Modal Add Rule --}}
    <div id="modal-add-rule" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Buat Rule Komisi</h3>
                <button onclick="document.getElementById('modal-add-rule').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('commission.rules.store') }}" class="p-6 space-y-4">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; @endphp
                <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama *</label><input type="text" name="name" required placeholder="Komisi Sales Standard" class="{{ $cls }}"></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tipe *</label>
                        <select name="type" id="rule-type" required onchange="toggleTiers()" class="{{ $cls }}">
                            <option value="flat_pct">Flat %</option><option value="flat_amount">Flat Rp</option><option value="tiered">Tiered</option>
                        </select>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Basis *</label>
                        <select name="basis" required class="{{ $cls }}">
                            <option value="revenue">Revenue</option><option value="profit">Profit</option><option value="quantity">Quantity</option>
                        </select>
                    </div>
                </div>
                <div id="rate-field"><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Rate (% atau Rp)</label><input type="number" name="rate" min="0" step="0.01" class="{{ $cls }}"></div>
                <div id="tiers-field" class="hidden">
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tiers (JSON)</label>
                    <textarea name="tiers" rows="3" placeholder='[{"min":0,"max":10000000,"rate":2},{"min":10000000,"max":null,"rate":3}]' class="{{ $cls }}"></textarea>
                    <p class="text-xs text-gray-400 mt-1">Format: [{"min":0,"max":10000000,"rate":2}, ...]</p>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-add-rule').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function toggleTiers() {
        const t = document.getElementById('rule-type').value;
        document.getElementById('tiers-field').classList.toggle('hidden', t !== 'tiered');
        document.getElementById('rate-field').classList.toggle('hidden', t === 'tiered');
    }
    </script>
    @endpush
</x-app-layout>
