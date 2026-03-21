<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            CRM & Pipeline Penjualan
            <a href="{{ route('crm.kanban') }}" class="text-xs text-gray-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400">Tampilan Kanban →</a>
        </div>
    </x-slot>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        @php
            $tid = auth()->user()->tenant_id;
            $totalLeads = \App\Models\CrmLead::where('tenant_id',$tid)->count();
            $activeLeads = \App\Models\CrmLead::where('tenant_id',$tid)->whereNotIn('stage',['won','lost'])->count();
        @endphp
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Lead</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalLeads }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Pipeline Aktif</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ $activeLeads }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Won Bulan Ini</p>
            <p class="text-xl font-bold text-green-600 dark:text-green-400 mt-1">Rp {{ number_format($wonThisMonth,0,',','.') }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Follow-up Hari Ini</p>
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $followUpToday }}</p>
        </div>
    </div>

    {{-- Pipeline Kanban Summary --}}
    @if($pipeline->count() > 0)
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
        @foreach(['new'=>'Baru','contacted'=>'Dihubungi','qualified'=>'Qualified','proposal'=>'Proposal','negotiation'=>'Negosiasi'] as $stage=>$label)
        @if($pipeline->has($stage))
        <div class="bg-white dark:bg-[#1e293b] rounded-xl p-3 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">{{ $label }}</p>
            <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $pipeline[$stage]->count }}</p>
            <p class="text-xs text-gray-500 dark:text-slate-400">Rp {{ number_format($pipeline[$stage]->total_value/1000000,1) }}jt</p>
        </div>
        @endif
        @endforeach
    </div>
    @endif

    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 mb-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-2 flex-1">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / perusahaan..."
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="stage" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                <option value="">Semua Stage</option>
                @foreach(['new'=>'Baru','contacted'=>'Dihubungi','qualified'=>'Qualified','proposal'=>'Proposal','negotiation'=>'Negosiasi','won'=>'Won','lost'=>'Lost'] as $v=>$l)
                <option value="{{ $v }}" @selected(request('stage')===$v)>{{ $l }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
        </form>
        <button onclick="document.getElementById('modal-add-lead').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Tambah Lead</button>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Lead</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Kontak</th>
                        <th class="px-4 py-3 text-center">Stage</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Nilai</th>
                        <th class="px-4 py-3 text-center hidden md:table-cell">Prob.</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Last Contact</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($leads as $lead)
                    @php
                        $stageColors = ['new'=>'gray','contacted'=>'blue','qualified'=>'indigo','proposal'=>'purple','negotiation'=>'amber','won'=>'green','lost'=>'red'];
                        $stageLabels = ['new'=>'Baru','contacted'=>'Dihubungi','qualified'=>'Qualified','proposal'=>'Proposal','negotiation'=>'Negosiasi','won'=>'Won','lost'=>'Lost'];
                        $c = $stageColors[$lead->stage] ?? 'gray';
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900 dark:text-white">{{ $lead->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-slate-400">{{ $lead->company ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell">
                            <p class="text-xs text-gray-500 dark:text-slate-400">{{ $lead->phone ?? '-' }}</p>
                            <p class="text-xs text-gray-500 dark:text-slate-400">{{ $lead->product_interest ?? '' }}</p>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs bg-{{ $c }}-100 text-{{ $c }}-700 dark:bg-{{ $c }}-500/20 dark:text-{{ $c }}-400">
                                {{ $stageLabels[$lead->stage] ?? $lead->stage }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right hidden md:table-cell text-gray-900 dark:text-white">
                            {{ $lead->estimated_value > 0 ? 'Rp '.number_format($lead->estimated_value,0,',','.') : '-' }}
                        </td>
                        <td class="px-4 py-3 text-center hidden md:table-cell text-gray-500 dark:text-slate-400">{{ $lead->probability }}%</td>
                        <td class="px-4 py-3 hidden lg:table-cell text-xs text-gray-500 dark:text-slate-400">{{ $lead->last_contact_at?->diffForHumans() ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <button onclick="openActivity({{ $lead->id }}, '{{ addslashes($lead->name) }}')"
                                    class="p-1.5 rounded-lg text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-500/10" title="Log Aktivitas">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                </button>
                                <button onclick="openStage({{ $lead->id }}, '{{ addslashes($lead->name) }}', '{{ $lead->stage }}', {{ $lead->probability }})"
                                    class="p-1.5 rounded-lg text-gray-500 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-white/10" title="Update Stage">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                                <form method="POST" action="{{ route('crm.destroy', $lead) }}" onsubmit="return confirm('Hapus lead ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada lead. Tambahkan prospek pertama Anda.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($leads->hasPages())
        <div class="px-4 py-3 border-t border-gray-100 dark:border-white/5">{{ $leads->links() }}</div>
        @endif
    </div>

    {{-- Modal Tambah Lead --}}
    <div id="modal-add-lead" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10 sticky top-0 bg-white dark:bg-[#1e293b]">
                <h3 class="font-semibold text-gray-900 dark:text-white">Tambah Lead</h3>
                <button onclick="document.getElementById('modal-add-lead').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('crm.store') }}" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama Kontak *</label>
                        <input type="text" name="name" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Perusahaan</label>
                        <input type="text" name="company" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Sumber</label>
                        <select name="source" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih --</option>
                            <option value="referral">Referral</option>
                            <option value="website">Website</option>
                            <option value="cold_call">Cold Call</option>
                            <option value="social_media">Social Media</option>
                            <option value="exhibition">Pameran</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">No. Telepon</label>
                        <input type="text" name="phone" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Email</label>
                        <input type="email" name="email" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Produk Diminati</label>
                        <input type="text" name="product_interest" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Estimasi Nilai (Rp)</label>
                        <input type="number" name="estimated_value" min="0" step="100000" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Target Closing</label>
                        <input type="date" name="expected_close_date" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Catatan</label>
                        <textarea name="notes" rows="2" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-add-lead').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Update Stage --}}
    <div id="modal-stage" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-sm shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Update Stage</h3>
                <button onclick="document.getElementById('modal-stage').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form id="form-stage" method="POST" class="p-6 space-y-4">
                @csrf @method('PATCH')
                <p id="stage-lead-name" class="text-sm font-medium text-gray-900 dark:text-white"></p>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Stage</label>
                    <select id="stage-select" name="stage" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach(['new'=>'Baru','contacted'=>'Dihubungi','qualified'=>'Qualified','proposal'=>'Proposal','negotiation'=>'Negosiasi','won'=>'Won','lost'=>'Lost'] as $v=>$l)
                        <option value="{{ $v }}">{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Probabilitas (%)</label>
                    <input type="number" id="stage-prob" name="probability" min="0" max="100" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-stage').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Update</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Log Aktivitas --}}
    <div id="modal-activity" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-sm shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Log Aktivitas</h3>
                <button onclick="document.getElementById('modal-activity').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form id="form-activity" method="POST" class="p-6 space-y-4">
                @csrf
                <p id="activity-lead-name" class="text-sm font-medium text-gray-900 dark:text-white"></p>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tipe Aktivitas</label>
                    <select name="type" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="call">Telepon</option>
                        <option value="whatsapp">WhatsApp</option>
                        <option value="email">Email</option>
                        <option value="meeting">Meeting</option>
                        <option value="demo">Demo</option>
                        <option value="proposal">Proposal</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Catatan *</label>
                    <textarea name="description" rows="3" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Hasil</label>
                    <select name="outcome" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih --</option>
                        <option value="interested">Tertarik</option>
                        <option value="follow_up">Perlu Follow-up</option>
                        <option value="not_interested">Tidak Tertarik</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Follow-up Berikutnya</label>
                    <input type="date" name="next_follow_up" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-activity').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openStage(id, name, stage, prob) {
        document.getElementById('form-stage').action = '/crm/' + id + '/stage';
        document.getElementById('stage-lead-name').textContent = name;
        document.getElementById('stage-select').value = stage;
        document.getElementById('stage-prob').value = prob;
        document.getElementById('modal-stage').classList.remove('hidden');
    }
    function openActivity(id, name) {
        document.getElementById('form-activity').action = '/crm/' + id + '/activity';
        document.getElementById('activity-lead-name').textContent = name;
        document.getElementById('modal-activity').classList.remove('hidden');
    }
    </script>
    @endpush
</x-app-layout>
