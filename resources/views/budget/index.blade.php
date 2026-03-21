<x-app-layout>
    <x-slot name="header">Anggaran vs Realisasi</x-slot>

    {{-- Period selector + Stats --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 mb-6">
        <form method="GET" class="flex gap-2">
            <input type="month" name="period" value="{{ $period }}"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Tampilkan</button>
        </form>
        <div class="flex gap-3 flex-wrap">
            <div class="bg-white dark:bg-[#1e293b] rounded-xl px-4 py-2 border border-gray-200 dark:border-white/10 text-sm">
                <span class="text-gray-500 dark:text-slate-400">Total Anggaran: </span>
                <span class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($totalBudget,0,',','.') }}</span>
            </div>
            <div class="bg-white dark:bg-[#1e293b] rounded-xl px-4 py-2 border border-gray-200 dark:border-white/10 text-sm">
                <span class="text-gray-500 dark:text-slate-400">Realisasi: </span>
                <span class="font-semibold {{ $totalRealized > $totalBudget ? 'text-red-500' : 'text-green-600 dark:text-green-400' }}">Rp {{ number_format($totalRealized,0,',','.') }}</span>
            </div>
            @if($overCount > 0)
            <div class="bg-red-50 dark:bg-red-500/10 rounded-xl px-4 py-2 border border-red-200 dark:border-red-500/20 text-sm text-red-600 dark:text-red-400 font-medium">
                ⚠️ {{ $overCount }} over budget
            </div>
            @endif
        </div>
        <button onclick="document.getElementById('modal-add-budget').classList.remove('hidden')"
            class="ml-auto px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 shrink-0">+ Anggaran Baru</button>
    </div>

    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 rounded-xl text-sm text-green-700 dark:text-green-400">{{ session('success') }}</div>
    @endif

    {{-- Budget Table --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nama Anggaran</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Departemen</th>
                        <th class="px-4 py-3 text-right">Anggaran</th>
                        <th class="px-4 py-3 text-right">Realisasi</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Sisa</th>
                        <th class="px-4 py-3 text-center">Pemakaian</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($budgets as $budget)
                    @php
                        $pct = $budget->usage_percent;
                        $over = $budget->realized > $budget->amount;
                        $warn = !$over && $pct >= 80;
                        $barColor = $over ? 'bg-red-500' : ($warn ? 'bg-amber-500' : 'bg-blue-500');
                        $statusText = $over ? 'OVER' : ($warn ? 'HAMPIR' : 'AMAN');
                        $statusColor = $over ? 'text-red-500 bg-red-50 dark:bg-red-500/10' : ($warn ? 'text-amber-600 bg-amber-50 dark:bg-amber-500/10' : 'text-green-600 bg-green-50 dark:bg-green-500/10');
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900 dark:text-white">{{ $budget->name }}</p>
                            @if($budget->category)<p class="text-xs text-gray-500 dark:text-slate-400">{{ $budget->category }}</p>@endif
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell text-gray-500 dark:text-slate-400">{{ $budget->department ?? '-' }}</td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">Rp {{ number_format($budget->amount,0,',','.') }}</td>
                        <td class="px-4 py-3 text-right font-medium {{ $over ? 'text-red-500' : 'text-gray-900 dark:text-white' }}">Rp {{ number_format($budget->realized,0,',','.') }}</td>
                        <td class="px-4 py-3 text-right hidden md:table-cell {{ $over ? 'text-red-500' : 'text-gray-500 dark:text-slate-400' }}">
                            {{ $over ? '-' : '' }}Rp {{ number_format(abs($budget->variance),0,',','.') }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-100 dark:bg-white/10 rounded-full h-2 min-w-[60px]">
                                    <div class="{{ $barColor }} h-2 rounded-full transition-all" style="width:{{ min(100,$pct) }}%"></div>
                                </div>
                                <span class="text-xs font-medium px-1.5 py-0.5 rounded {{ $statusColor }}">{{ $statusText }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <button onclick="openEdit({{ $budget->id }}, '{{ addslashes($budget->name) }}', {{ $budget->amount }}, {{ $budget->realized }}, '{{ $budget->department }}', '{{ $budget->category }}')"
                                    class="p-1.5 rounded-lg text-gray-500 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-white/10">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <form method="POST" action="{{ route('budget.destroy', $budget) }}" onsubmit="return confirm('Nonaktifkan anggaran ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada anggaran untuk periode {{ $period }}.</td></tr>
                    @endforelse
                </tbody>
                @if($budgets->count() > 0)
                <tfoot class="bg-gray-50 dark:bg-white/5 text-sm font-semibold">
                    <tr>
                        <td class="px-4 py-3 text-gray-900 dark:text-white" colspan="2">Total</td>
                        <td class="px-4 py-3 text-right text-gray-900 dark:text-white">Rp {{ number_format($totalBudget,0,',','.') }}</td>
                        <td class="px-4 py-3 text-right {{ $totalRealized > $totalBudget ? 'text-red-500' : 'text-gray-900 dark:text-white' }}">Rp {{ number_format($totalRealized,0,',','.') }}</td>
                        <td class="px-4 py-3 text-right hidden md:table-cell {{ $totalRealized > $totalBudget ? 'text-red-500' : 'text-gray-500 dark:text-slate-400' }}">
                            Rp {{ number_format(abs($totalBudget - $totalRealized),0,',','.') }}
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- Modal Tambah Anggaran --}}
    <div id="modal-add-budget" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Anggaran Baru</h3>
                <button onclick="document.getElementById('modal-add-budget').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('budget.store') }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama Anggaran *</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Departemen</label>
                        <input type="text" name="department" placeholder="Marketing, Ops..." class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Kategori</label>
                        <input type="text" name="category" placeholder="Gaji, Material..." class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Jumlah Anggaran (Rp) *</label>
                    <input type="number" name="amount" min="0" step="100000" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Periode *</label>
                        <input type="month" name="period" value="{{ $period }}" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tipe</label>
                        <select name="period_type" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="monthly">Bulanan</option>
                            <option value="quarterly">Kuartalan</option>
                            <option value="annual">Tahunan</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-add-budget').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit Anggaran --}}
    <div id="modal-edit-budget" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Edit Anggaran</h3>
                <button onclick="document.getElementById('modal-edit-budget').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form id="form-edit-budget" method="POST" class="p-6 space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama *</label>
                    <input type="text" id="edit-name" name="name" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Departemen</label>
                        <input type="text" id="edit-dept" name="department" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Kategori</label>
                        <input type="text" id="edit-cat" name="category" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Anggaran (Rp) *</label>
                        <input type="number" id="edit-amount" name="amount" min="0" step="100000" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Realisasi (Rp)</label>
                        <input type="number" id="edit-realized" name="realized" min="0" step="1000" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-edit-budget').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openEdit(id, name, amount, realized, dept, cat) {
        document.getElementById('form-edit-budget').action = '/budget/' + id;
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-amount').value = amount;
        document.getElementById('edit-realized').value = realized;
        document.getElementById('edit-dept').value = dept;
        document.getElementById('edit-cat').value = cat;
        document.getElementById('modal-edit-budget').classList.remove('hidden');
    }
    </script>
    @endpush
</x-app-layout>
