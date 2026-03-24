<x-app-layout>
    <x-slot name="header">Rekrutmen & Onboarding</x-slot>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Lowongan Aktif</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ $stats['open'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Pelamar</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['applications'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Jadwal Interview</p>
            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-1">{{ $stats['interview'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Diterima Bulan Ini</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $stats['hired_month'] }}</p>
        </div>
    </div>

    {{-- Onboarding in progress --}}
    @if($onboardings->count())
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 mb-6 p-4">
        <div class="flex items-center justify-between mb-3">
            <p class="text-sm font-semibold text-gray-900 dark:text-white">Onboarding Berjalan</p>
            <a href="{{ route('hrm.onboarding') }}" class="text-xs text-blue-500 hover:underline">Lihat semua</a>
        </div>
        <div class="flex flex-wrap gap-3">
            @foreach($onboardings as $ob)
            @php $pct = $ob->progressPercent(); @endphp
            <a href="{{ route('hrm.onboarding.detail', $ob) }}"
               class="flex items-center gap-3 px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/5 transition min-w-[200px]">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $ob->employee->name }}</p>
                    <p class="text-xs text-gray-500 dark:text-slate-400">{{ $ob->employee->position ?? '-' }}</p>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-sm font-bold {{ $pct >= 100 ? 'text-green-400' : 'text-blue-400' }}">{{ $pct }}%</p>
                    <div class="w-16 h-1.5 bg-gray-200 dark:bg-white/10 rounded-full mt-1">
                        <div class="h-1.5 rounded-full {{ $pct >= 100 ? 'bg-green-500' : 'bg-blue-500' }}" style="width:{{ $pct }}%"></div>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4">
        <form method="GET" class="flex gap-2">
            <select name="status" onchange="this.form.submit()"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                <option value="">Semua Status</option>
                <option value="open"   @selected(request('status')==='open')>Buka</option>
                <option value="draft"  @selected(request('status')==='draft')>Draft</option>
                <option value="closed" @selected(request('status')==='closed')>Tutup</option>
            </select>
        </form>
        <div class="flex gap-2">
            <a href="{{ route('hrm.onboarding') }}"
               class="px-3 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">
                Onboarding
            </a>
            <button onclick="document.getElementById('modal-add-posting').classList.remove('hidden')"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Buat Lowongan</button>
        </div>
    </div>

    {{-- Lowongan list --}}
    <div class="space-y-3">
        @forelse($postings as $posting)
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <div class="flex flex-col sm:flex-row sm:items-start gap-3">
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <h3 class="font-semibold text-gray-900 dark:text-white">{{ $posting->title }}</h3>
                        <span class="px-2 py-0.5 rounded-full text-xs
                            {{ $posting->status === 'open' ? 'bg-green-500/20 text-green-400' :
                               ($posting->status === 'draft' ? 'bg-gray-500/20 text-gray-400' : 'bg-red-500/20 text-red-400') }}">
                            {{ $posting->statusLabel() }}
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-xs bg-blue-500/20 text-blue-400">{{ $posting->typeLabel() }}</span>
                    </div>
                    <div class="flex flex-wrap gap-3 text-xs text-gray-500 dark:text-slate-400">
                        @if($posting->department)<span>{{ $posting->department }}</span>@endif
                        @if($posting->location)<span>📍 {{ $posting->location }}</span>@endif
                        @if($posting->deadline)<span>Deadline: {{ $posting->deadline->format('d M Y') }}</span>@endif
                        @if($posting->salary_min || $posting->salary_max)
                        <span>Rp {{ number_format($posting->salary_min,0,',','.') }}
                            @if($posting->salary_max) – {{ number_format($posting->salary_max,0,',','.') }}@endif
                        </span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-4 shrink-0">
                    <div class="text-center">
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $posting->applications_count }}</p>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Pelamar</p>
                    </div>
                    <div class="text-center">
                        <p class="text-lg font-bold text-green-600 dark:text-green-400">{{ $posting->hired_count }}</p>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Diterima</p>
                    </div>
                    <div class="text-center">
                        <p class="text-lg font-bold text-gray-500 dark:text-slate-400">{{ $posting->quota }}</p>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Kuota</p>
                    </div>
                    <div class="flex gap-1">
                        <a href="{{ route('hrm.recruitment.applications', $posting) }}"
                           class="px-3 py-1.5 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700">Lihat Pelamar</a>
                        <button onclick="openEditPosting({{ $posting->id }}, {{ json_encode($posting->only(['title','department','location','type','description','requirements','salary_min','salary_max','quota','deadline','status'])) }})"
                            class="p-1.5 rounded-lg text-gray-500 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-white/10">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <form method="POST" action="{{ route('hrm.recruitment.posting.destroy', $posting) }}" onsubmit="return confirm('Hapus lowongan ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-1.5 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-12 text-center">
            <p class="text-gray-400 dark:text-slate-500 text-sm">Belum ada lowongan. Buat lowongan pertama untuk mulai rekrutmen.</p>
        </div>
        @endforelse
    </div>
    @if($postings->hasPages())
    <div class="mt-4">{{ $postings->links() }}</div>
    @endif

    {{-- Modal Buat Lowongan --}}
    <div id="modal-add-posting" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10 sticky top-0 bg-white dark:bg-[#1e293b]">
                <h3 class="font-semibold text-gray-900 dark:text-white">Buat Lowongan</h3>
                <button onclick="document.getElementById('modal-add-posting').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('hrm.recruitment.posting.store') }}" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Judul Posisi *</label>
                        <input type="text" name="title" required placeholder="e.g. Staff Akuntansi"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Departemen</label>
                        <input type="text" name="department" placeholder="e.g. Keuangan"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Lokasi</label>
                        <input type="text" name="location" placeholder="e.g. Jakarta"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tipe</label>
                        <select name="type" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                            <option value="full_time">Full Time</option>
                            <option value="part_time">Part Time</option>
                            <option value="contract">Kontrak</option>
                            <option value="internship">Magang</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Status</label>
                        <select name="status" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                            <option value="open">Buka</option>
                            <option value="draft">Draft</option>
                            <option value="closed">Tutup</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Kuota</label>
                        <input type="number" name="quota" value="1" min="1"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Deadline</label>
                        <input type="date" name="deadline"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Gaji Min</label>
                        <input type="number" name="salary_min" min="0" step="100000" placeholder="0"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Gaji Max</label>
                        <input type="number" name="salary_max" min="0" step="100000" placeholder="0"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Deskripsi Pekerjaan</label>
                        <textarea name="description" rows="3" placeholder="Tanggung jawab dan deskripsi posisi..."
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Persyaratan</label>
                        <textarea name="requirements" rows="3" placeholder="Kualifikasi dan persyaratan pelamar..."
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-add-posting').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit Lowongan --}}
    <div id="modal-edit-posting" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10 sticky top-0 bg-white dark:bg-[#1e293b]">
                <h3 class="font-semibold text-gray-900 dark:text-white">Edit Lowongan</h3>
                <button onclick="document.getElementById('modal-edit-posting').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form id="form-edit-posting" method="POST" class="p-6 space-y-4">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Judul Posisi *</label>
                        <input type="text" id="ep-title" name="title" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Departemen</label>
                        <input type="text" id="ep-department" name="department" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Lokasi</label>
                        <input type="text" id="ep-location" name="location" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tipe</label>
                        <select id="ep-type" name="type" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                            <option value="full_time">Full Time</option>
                            <option value="part_time">Part Time</option>
                            <option value="contract">Kontrak</option>
                            <option value="internship">Magang</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Status</label>
                        <select id="ep-status" name="status" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                            <option value="open">Buka</option>
                            <option value="draft">Draft</option>
                            <option value="closed">Tutup</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Kuota</label>
                        <input type="number" id="ep-quota" name="quota" min="1" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Deadline</label>
                        <input type="date" id="ep-deadline" name="deadline" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Gaji Min</label>
                        <input type="number" id="ep-salary-min" name="salary_min" min="0" step="100000" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Gaji Max</label>
                        <input type="number" id="ep-salary-max" name="salary_max" min="0" step="100000" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Deskripsi</label>
                        <textarea id="ep-description" name="description" rows="3" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Persyaratan</label>
                        <textarea id="ep-requirements" name="requirements" rows="3" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-edit-posting').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openEditPosting(id, data) {
        document.getElementById('form-edit-posting').action = '/hrm/recruitment/postings/' + id;
        document.getElementById('ep-title').value       = data.title ?? '';
        document.getElementById('ep-department').value  = data.department ?? '';
        document.getElementById('ep-location').value    = data.location ?? '';
        document.getElementById('ep-type').value        = data.type ?? 'full_time';
        document.getElementById('ep-status').value      = data.status ?? 'open';
        document.getElementById('ep-quota').value       = data.quota ?? 1;
        document.getElementById('ep-deadline').value    = data.deadline ?? '';
        document.getElementById('ep-salary-min').value  = data.salary_min ?? '';
        document.getElementById('ep-salary-max').value  = data.salary_max ?? '';
        document.getElementById('ep-description').value = data.description ?? '';
        document.getElementById('ep-requirements').value= data.requirements ?? '';
        document.getElementById('modal-edit-posting').classList.remove('hidden');
    }
    </script>
    @endpush
</x-app-layout>
