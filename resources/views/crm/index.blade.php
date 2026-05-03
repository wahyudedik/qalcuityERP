<x-app-layout>
    <x-slot name="header">CRM & Pipeline Penjualan</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('crm.kanban') }}"
                class="text-xs text-gray-500 hover:text-blue-600">Tampilan
                Kanban →</a>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        @php
            $tid = auth()->user()->tenant_id;
            $totalLeads = \App\Models\CrmLead::where('tenant_id', $tid)->count();
            $activeLeads = \App\Models\CrmLead::where('tenant_id', $tid)
                ->whereNotIn('stage', ['won', 'lost'])
                ->count();
        @endphp
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Total Lead</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $totalLeads }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Pipeline Aktif</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $activeLeads }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Won Bulan Ini</p>
            <p class="text-xl font-bold text-green-600 mt-1">Rp
                {{ number_format($wonThisMonth, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Follow-up Hari Ini</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $followUpToday }}</p>
        </div>
    </div>

    {{-- Pipeline Kanban Summary --}}
    @if ($pipeline->count() > 0)
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
            @foreach (['new' => 'Baru', 'contacted' => 'Dihubungi', 'qualified' => 'Qualified', 'proposal' => 'Proposal', 'negotiation' => 'Negosiasi'] as $stage => $label)
                @if ($pipeline->has($stage))
                    <div class="bg-white rounded-xl p-3 border border-gray-200">
                        <p class="text-xs text-gray-500">{{ $label }}</p>
                        <p class="text-lg font-bold text-gray-900">{{ $pipeline[$stage]->count }}</p>
                        <p class="text-xs text-gray-500">Rp
                            {{ number_format($pipeline[$stage]->total_value / 1000000, 1) }}jt</p>
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 mb-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-2 flex-1">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / perusahaan..."
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="stage"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900">
                <option value="">Semua Stage</option>
                @foreach (['new' => 'Baru', 'contacted' => 'Dihubungi', 'qualified' => 'Qualified', 'proposal' => 'Proposal', 'negotiation' => 'Negosiasi', 'won' => 'Won', 'lost' => 'Lost'] as $v => $l)
                    <option value="{{ $v }}" @selected(request('stage') === $v)>{{ $l }}</option>
                @endforeach
            </select>
            <button type="submit"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
        </form>
        @canmodule('crm', 'create')
        <button onclick="document.getElementById('modal-add-lead').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Tambah Lead</button>
        @endcanmodule
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Lead</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Kontak</th>
                        <th class="px-4 py-3 text-center">Stage</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Nilai</th>
                        <th class="px-4 py-3 text-center hidden md:table-cell">Prob.</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Last Contact</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">AI Score</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($leads as $lead)
                        @php
                            $stageColors = [
                                'new' => 'gray',
                                'contacted' => 'blue',
                                'qualified' => 'indigo',
                                'proposal' => 'purple',
                                'negotiation' => 'amber',
                                'won' => 'green',
                                'lost' => 'red',
                            ];
                            $stageLabels = [
                                'new' => 'Baru',
                                'contacted' => 'Dihubungi',
                                'qualified' => 'Qualified',
                                'proposal' => 'Proposal',
                                'negotiation' => 'Negosiasi',
                                'won' => 'Won',
                                'lost' => 'Lost',
                            ];
                            $c = $stageColors[$lead->stage] ?? 'gray';
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900">{{ $lead->name }}</p>
                                <p class="text-xs text-gray-500">{{ $lead->company ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3 hidden sm:table-cell">
                                <p class="text-xs text-gray-500">{{ $lead->phone ?? '-' }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $lead->product_interest ?? '' }}</p>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="px-2 py-0.5 rounded-full text-xs bg-{{ $c }}-100 text-{{ $c }}-700 $c }}-500/20 $c }}-400">
                                    {{ $stageLabels[$lead->stage] ?? $lead->stage }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right hidden md:table-cell text-gray-900">
                                {{ $lead->estimated_value > 0 ? 'Rp ' . number_format($lead->estimated_value, 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-center hidden md:table-cell text-gray-500">
                                {{ $lead->probability }}%</td>
                            <td class="px-4 py-3 hidden lg:table-cell text-xs text-gray-500">
                                {{ $lead->last_contact_at?->diffForHumans() ?? '-' }}</td>
                            <td class="px-4 py-3 text-center hidden sm:table-cell">
                                <span id="score-badge-{{ $lead->id }}"
                                    class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-500">...</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button
                                        onclick="openAiModal({{ $lead->id }}, '{{ addslashes($lead->name) }}')"
                                        class="p-1.5 rounded-lg text-purple-600 hover:bg-purple-50"
                                        title="AI Score & Follow-up">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.347.347a3.5 3.5 0 01-4.95 0l-.347-.347z" />
                                        </svg>
                                    </button>
                                    <button
                                        onclick="openActivity({{ $lead->id }}, '{{ addslashes($lead->name) }}')"
                                        class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50"
                                        title="Log Aktivitas">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                        </svg>
                                    </button>
                                    <button
                                        onclick="openStage({{ $lead->id }}, '{{ addslashes($lead->name) }}', '{{ $lead->stage }}', {{ $lead->probability }})"
                                        class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100"
                                        title="Update Stage">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                    @if ($lead->stage === 'won' && !$lead->converted_to_customer_id)
                                        <button type="button"
                                            onclick="convertLeadWithDuplicateCheck({{ $lead->id }}, '{{ addslashes($lead->name) }}')"
                                            class="p-1.5 rounded-lg text-green-600 hover:bg-green-50"
                                            title="Konversi ke Customer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                            </svg>
                                        </button>
                                    @elseif($lead->converted_to_customer_id)
                                        <span class="p-1.5 text-green-500"
                                            title="Sudah dikonversi ke Customer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </span>
                                    @endif
                                    @canmodule('crm', 'delete')
                                    <form method="POST" action="{{ route('crm.destroy', $lead) }}"
                                        onsubmit="return confirm('Hapus lead ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="p-1.5 rounded-lg text-red-500 hover:bg-red-50">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                    @endcanmodule
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-400">Belum
                                ada lead. Tambahkan prospek pertama Anda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($leads->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $leads->links() }}</div>
        @endif
    </div>

    {{-- Modal AI Score & Follow-up --}}
    <div id="modal-ai" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl max-h-[90vh] overflow-y-auto">
            <div
                class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white">
                <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.347.347a3.5 3.5 0 01-4.95 0l-.347-.347z" />
                    </svg>
                    AI Insight Lead
                </h3>
                <button onclick="document.getElementById('modal-ai').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <div id="ai-modal-body" class="p-6">
                <div class="flex items-center justify-center py-8">
                    <svg class="animate-spin w-6 h-6 text-purple-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Tambah Lead --}}
    <div id="modal-add-lead" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto">
            <div
                class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white">
                <h3 class="font-semibold text-gray-900">Tambah Lead</h3>
                <button onclick="document.getElementById('modal-add-lead').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="{{ route('crm.store') }}" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama Kontak
                            *</label>
                        <input type="text" name="name" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label
                            class="block text-xs font-medium text-gray-600 mb-1">Perusahaan</label>
                        <input type="text" name="company"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Sumber</label>
                        <select name="source"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih --</option>
                            <option value="referral">Referral</option>
                            <option value="website">Website</option>
                            <option value="cold_call">Cold Call</option>
                            <option value="social_media">Social Media</option>
                            <option value="exhibition">Pameran</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">No.
                            Telepon</label>
                        <input type="text" name="phone"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                        <input type="email" name="email"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Produk
                            Diminati</label>
                        <input type="text" name="product_interest"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Estimasi Nilai
                            (Rp)</label>
                        <input type="number" name="estimated_value" min="0" step="100000"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Target
                            Closing</label>
                        <input type="date" name="expected_close_date"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Catatan</label>
                        <textarea name="notes" rows="2"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-add-lead').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Update Stage --}}
    <div id="modal-stage" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-sm shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Update Stage</h3>
                <button onclick="document.getElementById('modal-stage').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form id="form-stage" method="POST" class="p-6 space-y-4">
                @csrf @method('PATCH')
                <p id="stage-lead-name" class="text-sm font-medium text-gray-900"></p>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Stage</label>
                    <select id="stage-select" name="stage"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach (['new' => 'Baru', 'contacted' => 'Dihubungi', 'qualified' => 'Qualified', 'proposal' => 'Proposal', 'negotiation' => 'Negosiasi', 'won' => 'Won', 'lost' => 'Lost'] as $v => $l)
                            <option value="{{ $v }}">{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Probabilitas
                        (%)</label>
                    <input type="number" id="stage-prob" name="probability" min="0" max="100"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-stage').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Update</button>
                </div>
            </form>
        </div>
    </div>

    {{-- BUG-CRM-001 FIX: Modal Duplicate Warning --}}
    <div id="modal-duplicate-warning"
        class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-2xl shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Potential Duplicate Detected
                </h3>
                <button onclick="document.getElementById('modal-duplicate-warning').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <div class="p-6">
                <div
                    class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                    <p class="text-sm text-amber-800">
                        <strong>Lead: <span id="duplicate-lead-name"></span></strong><br>
                        Ditemukan <span id="duplicate-count" class="font-bold"></span> potential duplicate(s). Pilih
                        aksi:
                    </p>
                </div>

                <div class="overflow-x-auto mb-4">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-3 py-2 text-left">Customer Name</th>
                                <th class="px-3 py-2 text-left">Email</th>
                                <th class="px-3 py-2 text-left">Phone</th>
                                <th class="px-3 py-2 text-left">Match</th>
                                <th class="px-3 py-2 text-center">Confidence</th>
                                <th class="px-3 py-2 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="duplicates-tbody" class="divide-y divide-gray-100">
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-between items-center">
                    <button onclick="document.getElementById('modal-duplicate-warning').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                        Batal
                    </button>
                    <button id="btn-force-create" onclick="forceCreateCustomer(this.dataset.leadId)"
                        class="px-4 py-2 text-sm bg-red-600 text-white rounded-xl hover:bg-red-700">
                        Force Create New Customer
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Hidden form for linking to existing customer --}}
    <form id="form-link-customer" method="POST" action="" class="hidden">
        @csrf
        <input type="hidden" id="link-customer-id" name="link_to_customer_id" value="">
    </form>

    {{-- Modal Log Aktivitas --}}
    <div id="modal-activity" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-sm shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Log Aktivitas</h3>
                <button onclick="document.getElementById('modal-activity').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form id="form-activity" method="POST" class="p-6 space-y-4">
                @csrf
                <p id="activity-lead-name" class="text-sm font-medium text-gray-900"></p>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tipe
                        Aktivitas</label>
                    <select name="type"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="call">Telepon</option>
                        <option value="whatsapp">WhatsApp</option>
                        <option value="email">Email</option>
                        <option value="meeting">Meeting</option>
                        <option value="demo">Demo</option>
                        <option value="proposal">Proposal</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Catatan *</label>
                    <textarea name="description" rows="3" required
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Hasil</label>
                    <select name="outcome"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih --</option>
                        <option value="interested">Tertarik</option>
                        <option value="follow_up">Perlu Follow-up</option>
                        <option value="not_interested">Tidak Tertarik</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Follow-up
                        Berikutnya</label>
                    <input type="date" name="next_follow_up"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-activity').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            // Batch load AI scores on page load
            document.addEventListener('DOMContentLoaded', async () => {
                try {
                    const res = await fetch('{{ route('crm.ai.score-all') }}');
                    const data = await res.json();
                    const tierClasses = {
                        hot: 'bg-red-100 text-red-700',
                        warm: 'bg-amber-100 text-amber-700',
                        cold: 'bg-blue-100 text-blue-700',
                    };
                    Object.entries(data).forEach(([id, s]) => {
                        const el = document.getElementById('score-badge-' + id);
                        if (el) {
                            el.textContent = s.tier_label + ' ' + s.score;
                            el.className = 'px-2 py-0.5 rounded-full text-xs ' + (tierClasses[s.tier] ||
                                '');
                        }
                    });
                } catch (e) {}
            });

            async function openAiModal(id, name) {
                document.getElementById('modal-ai').classList.remove('hidden');
                document.getElementById('ai-modal-body').innerHTML = `
            <div class="flex items-center justify-center py-8">
                <svg class="animate-spin w-6 h-6 text-purple-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
            </div>`;

                const [scoreRes, followRes] = await Promise.all([
                    fetch('{{ url('crm/ai/score') }}/' + id),
                    fetch('{{ url('crm/ai/follow-up') }}/' + id),
                ]);
                const score = await scoreRes.json();
                const follow = await followRes.json();

                const tierClasses = {
                    hot: 'bg-red-100 text-red-700',
                    warm: 'bg-amber-100 text-amber-700',
                    cold: 'bg-blue-100 text-blue-700',
                };
                const priorityClasses = {
                    high: 'bg-red-50 border-red-200 text-red-700',
                    normal: 'bg-blue-50 border-blue-200 text-blue-700',
                    low: 'bg-gray-50 border-gray-200 text-gray-600',
                };

                const breakdownRows = score.breakdown.map(b =>
                    `<tr class="border-t border-gray-100">
                <td class="py-1.5 text-gray-600">${b.label}</td>
                <td class="py-1.5 text-gray-500 text-xs">${b.value}</td>
                <td class="py-1.5 text-right font-medium text-gray-900">+${b.points}</td>
            </tr>`
                ).join('');

                const suggestionItems = (follow.suggestions || []).map(s =>
                    `<li class="flex items-start gap-2"><span class="text-purple-400 mt-0.5">•</span><span>${s}</span></li>`
                ).join('');

                document.getElementById('ai-modal-body').innerHTML = `
            <p class="text-sm font-semibold text-gray-900 mb-4">${name}</p>

            <div class="flex items-center gap-3 mb-4">
                <div class="w-14 h-14 rounded-full flex items-center justify-center text-xl font-bold border-4 ${score.score >= 70 ? 'border-red-400 text-red-600' : score.score >= 40 ? 'border-amber-400 text-amber-600' : 'border-blue-400 text-blue-600'}">
                    ${score.score}
                </div>
                <div>
                    <span class="px-2 py-0.5 rounded-full text-xs ${tierClasses[score.tier]}">${score.tier_label}</span>
                    <p class="text-xs text-gray-500 mt-1">Lead Score</p>
                </div>
            </div>

            <table class="w-full text-sm mb-5">
                <thead><tr class="text-xs text-gray-400 uppercase">
                    <th class="text-left pb-1">Faktor</th><th class="text-left pb-1">Detail</th><th class="text-right pb-1">Poin</th>
                </tr></thead>
                <tbody>${breakdownRows}</tbody>
            </table>

            <div class="border-t border-gray-100 pt-4">
                <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Saran Follow-up AI</p>
                <div class="p-3 rounded-xl border ${priorityClasses[follow.priority] || priorityClasses.normal} mb-3">
                    <p class="text-sm font-medium">${follow.action_label}: ${follow.message}</p>
                    ${follow.days_since_last !== null ? `<p class="text-xs mt-1 opacity-75">Terakhir kontak: ${follow.days_since_last} hari lalu</p>` : ''}
                </div>
                <ul class="space-y-1 text-sm text-gray-600">${suggestionItems}</ul>
            </div>`;
            }

            function openStage(id, name, stage, prob) {
                document.getElementById('form-stage').action = '{{ url('crm') }}/' + id + '/stage';
                document.getElementById('stage-lead-name').textContent = name;
                document.getElementById('stage-select').value = stage;
                document.getElementById('stage-prob').value = prob;
                document.getElementById('modal-stage').classList.remove('hidden');
            }

            function openActivity(id, name) {
                document.getElementById('form-activity').action = '{{ url('crm') }}/' + id + '/activity';
                document.getElementById('activity-lead-name').textContent = name;
                document.getElementById('modal-activity').classList.remove('hidden');
            }

            // BUG-CRM-001 FIX: Duplicate detection before lead conversion
            async function convertLeadWithDuplicateCheck(leadId, leadName) {
                try {
                    // Check for duplicates first
                    const response = await fetch(`{{ url('crm') }}/${leadId}/check-duplicates`);
                    const result = await response.json();

                    if (!result.success) {
                        alert('Error checking duplicates');
                        return;
                    }

                    const data = result.data;

                    // If already converted
                    if (data.already_converted) {
                        alert(data.suggestion);
                        return;
                    }

                    // If duplicates found, show modal
                    if (data.has_duplicates) {
                        showDuplicateWarningModal(leadId, leadName, data);
                        return;
                    }

                    // No duplicates - confirm and convert
                    if (confirm(`No duplicates found. Convert lead "${leadName}" to customer?`)) {
                        convertLead(leadId);
                    }
                } catch (error) {
                    console.error('Error checking duplicates:', error);
                    // Fallback to direct conversion if check fails
                    if (confirm(`Convert lead "${leadName}" to customer?`)) {
                        convertLead(leadId);
                    }
                }
            }

            function showDuplicateWarningModal(leadId, leadName, data) {
                const modal = document.getElementById('modal-duplicate-warning');
                const tbody = document.getElementById('duplicates-tbody');

                tbody.innerHTML = '';

                // Store leadId on the force-create button for later use
                document.getElementById('btn-force-create').dataset.leadId = leadId;
                document.getElementById('duplicate-lead-name').dataset.leadId = leadId;

                data.duplicates.forEach((dup, index) => {
                    const confidenceColor = dup.confidence >= 90 ? 'text-red-600' :
                        dup.confidence >= 80 ? 'text-amber-600' : 'text-blue-600';

                    tbody.innerHTML += `
                <tr class="border-b">
                    <td class="px-3 py-2">${dup.customer_name}</td>
                    <td class="px-3 py-2 text-xs">${dup.customer_email || '-'}</td>
                    <td class="px-3 py-2 text-xs">${dup.customer_phone || '-'}</td>
                    <td class="px-3 py-2 text-xs">${dup.match_field}</td>
                    <td class="px-3 py-2 text-center font-semibold ${confidenceColor}">${dup.confidence}%</td>
                    <td class="px-3 py-2 text-center">
                        <button onclick="linkToExistingCustomer(${leadId}, ${dup.customer_id})"
                            class="px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">
                            Link to This
                        </button>
                    </td>
                </tr>
            `;
                });

                document.getElementById('duplicate-lead-name').textContent = leadName;
                document.getElementById('duplicate-count').textContent = data.duplicates.length;
                modal.classList.remove('hidden');
            }

            function linkToExistingCustomer(leadId, customerId) {
                if (confirm('Link this lead to the existing customer instead of creating a new one?')) {
                    const form = document.getElementById('form-link-customer');
                    form.action = `{{ url('crm') }}/${leadId}/convert-customer`;
                    document.getElementById('link-customer-id').value = customerId;
                    form.submit();
                }
            }

            function convertLead(leadId) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ url('crm') }}/${leadId}/convert-customer`;

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';
                form.appendChild(csrf);

                document.body.appendChild(form);
                form.submit();
            }

            function forceCreateCustomer(leadId) {
                if (confirm('This will create a NEW customer even though duplicates exist. Continue?')) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `{{ url('crm') }}/${leadId}/convert-customer`;

                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = '{{ csrf_token() }}';
                    form.appendChild(csrf);

                    const forceCreate = document.createElement('input');
                    forceCreate.type = 'hidden';
                    forceCreate.name = 'force_create';
                    forceCreate.value = '1';
                    form.appendChild(forceCreate);

                    document.body.appendChild(form);
                    form.submit();
                }
            }
        </script>
    @endpush
</x-app-layout>
