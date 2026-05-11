<x-app-layout>
    <x-slot name="header">SDM & Karyawan</x-slot>

    @php $tid = auth()->user()->tenant_id; @endphp

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        @php
            $totalEmp    = \App\Models\Employee::where('tenant_id',$tid)->count();
            $todayPresent = \App\Models\Attendance::where('tenant_id',$tid)->whereDate('date',today())->where('status','present')->count();
            $todayAbsent  = \App\Models\Attendance::where('tenant_id',$tid)->whereDate('date',today())->where('status','absent')->count();
        @endphp
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Total Karyawan</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $totalEmp }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Aktif</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ $totalActive }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Hadir Hari Ini</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $todayPresent }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Tidak Hadir</p>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ $todayAbsent }}</p>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="bg-white rounded-2xl border border-gray-200 mb-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-2 flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / jabatan..."
                    class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="department" class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    <option value="">Semua Departemen</option>
                    @foreach($departments ?? [] as $dep)<option value="{{ $dep }}" @selected(request('department')===$dep)>{{ $dep }}</option>@endforeach
                </select>
                <select name="status" class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    <option value="">Semua Status</option>
                    <option value="active" @selected(request('status')==='active')>Aktif</option>
                    <option value="inactive" @selected(request('status')==='inactive')>Nonaktif</option>
                    <option value="resigned" @selected(request('status')==='resigned')>Resign</option>
                </select>
                <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
            </form>
            <div class="flex gap-2">
                <a href="{{ route('hrm.attendance') }}" class="px-3 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Absensi</a>
                @canmodule('hrm', 'create')
                <button onclick="document.getElementById('modal-add-emp').classList.remove('hidden')"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Karyawan</button>
                @endcanmodule
            </div>
        </div>
    </div>

    {{-- AI Turnover Risk Panel --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-5 mb-4">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-red-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                </div>
                <p class="font-semibold text-gray-900 text-sm">AI Turnover Risk Score</p>
                <span class="text-xs text-gray-400">— deteksi dini risiko resign karyawan</span>
            </div>
            <button onclick="loadTurnoverRisk()" id="turnover-btn"
                class="px-3 py-1.5 text-sm bg-red-600 text-white rounded-xl hover:bg-red-700 flex items-center gap-1.5 disabled:opacity-50">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                Analisis Risiko
            </button>
        </div>
        <div id="turnover-result" class="hidden">
            <div id="turnover-loading" class="hidden py-6 text-center">
                <div class="inline-flex items-center gap-2 text-sm text-gray-500">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Menganalisis pola karyawan...
                </div>
            </div>
            <div id="turnover-content"></div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Karyawan</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">ID</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Departemen</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Gaji Pokok</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Status</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Bergabung</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($employees as $emp)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900">{{ $emp->name }}</p>
                            <p class="text-xs text-gray-500">{{ $emp->position ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell font-mono text-xs text-gray-500">{{ $emp->employee_id }}</td>
                        <td class="px-4 py-3 hidden md:table-cell text-gray-500">{{ $emp->department ?? '-' }}</td>
                        <td class="px-4 py-3 text-right hidden md:table-cell text-gray-900">
                            {{ $emp->salary ? 'Rp '.number_format($emp->salary,0,',','.') : '-' }}
                        </td>
                        <td class="px-4 py-3 text-center hidden sm:table-cell">
                            <span class="px-2 py-0.5 rounded-full text-xs
                                {{ $emp->status === 'active' ? 'bg-green-100 text-green-700' :
                                   ($emp->status === 'resigned' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-500') }}">
                                {{ ucfirst($emp->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 hidden lg:table-cell text-xs text-gray-500">{{ $emp->join_date?->format('d M Y') ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                @canmodule('hrm', 'edit')
                                <button onclick="openEditEmp({{ $emp->id }}, '{{ addslashes($emp->name) }}', '{{ addslashes($emp->position ?? '') }}', '{{ addslashes($emp->department ?? '') }}', {{ $emp->salary ?? 0 }}, '{{ $emp->phone ?? '' }}', '{{ $emp->email ?? '' }}', '{{ $emp->join_date?->format('Y-m-d') ?? '' }}', '{{ $emp->status }}')"
                                    class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                @endcanmodule
                                <button onclick="openSalarySuggest({{ $emp->id }}, '{{ addslashes($emp->name) }}')"
                                    class="p-1.5 rounded-lg text-purple-500 hover:bg-purple-50" title="Saran Gaji AI">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.364.364A4.004 4.004 0 0112 16a4.004 4.004 0 01-2.772-1.1l-.364-.364z"/></svg>
                                </button>
                                @canmodule('hrm', 'delete')
                                <form method="POST" action="{{ route('hrm.destroy', $emp) }}" data-confirm="Tandai karyawan ini sebagai resign?" data-confirm-type="danger">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-red-500 hover:bg-red-50" title="Resign">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    </button>
                                </form>
                                @endcanmodule
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">Belum ada karyawan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($employees->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $employees->links() }}</div>
        @endif
    </div>

    {{-- Modal Tambah Karyawan --}}
    <div id="modal-add-emp" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white">
                <h3 class="font-semibold text-gray-900">Tambah Karyawan</h3>
                <button onclick="document.getElementById('modal-add-emp').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">?</button>
            </div>
            <form method="POST" action="{{ route('hrm.store') }}" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama Lengkap *</label>
                        <input type="text" name="name" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Jabatan</label>
                        <input type="text" name="position" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Departemen</label>
                        <input type="text" name="department" list="dept-list" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <datalist id="dept-list">@foreach($departments ?? [] as $d)<option value="{{ $d }}">@endforeach</datalist>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Gaji Pokok</label>
                        <input type="number" name="salary" min="0" step="50000" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Bergabung</label>
                        <input type="date" name="join_date" value="{{ today()->format('Y-m-d') }}" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">No. Telepon</label>
                        <input type="text" name="phone" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                        <input type="email" name="email" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-add-emp').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit Karyawan --}}
    <div id="modal-edit-emp" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white">
                <h3 class="font-semibold text-gray-900">Edit Karyawan</h3>
                <button onclick="document.getElementById('modal-edit-emp').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">?</button>
            </div>
            <form id="form-edit-emp" method="POST" class="p-6 space-y-4">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama Lengkap *</label>
                        <input type="text" id="ee-name" name="name" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Jabatan</label>
                        <input type="text" id="ee-position" name="position" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Departemen</label>
                        <input type="text" id="ee-department" name="department" list="dept-list" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Gaji Pokok</label>
                        <input type="number" id="ee-salary" name="salary" min="0" step="50000" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">No. Telepon</label>
                        <input type="text" id="ee-phone" name="phone" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                        <input type="email" id="ee-email" name="email" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Bergabung</label>
                        <input type="date" id="ee-join-date" name="join_date" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                        <select id="ee-status" name="status" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                            <option value="resigned">Resign</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-edit-emp').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Saran Gaji AI --}}
    <div id="modal-salary-suggest" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white">
                <h3 id="salary-modal-title" class="font-semibold text-gray-900 text-sm">Saran Gaji AI</h3>
                <button onclick="document.getElementById('modal-salary-suggest').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">?</button>
            </div>
            <div id="salary-modal-content" class="p-6"></div>
        </div>
    </div>

    @push('scripts')
    <script>
    function openEditEmp(id, name, position, department, salary, phone, email, joinDate, status) {
        document.getElementById('form-edit-emp').action = '{{ url("hrm") }}/' + id;
        document.getElementById('ee-name').value = name;
        document.getElementById('ee-position').value = position;
        document.getElementById('ee-department').value = department;
        document.getElementById('ee-salary').value = salary;
        document.getElementById('ee-phone').value = phone;
        document.getElementById('ee-email').value = email;
        document.getElementById('ee-join-date').value = joinDate;
        document.getElementById('ee-status').value = status;
        document.getElementById('modal-edit-emp').classList.remove('hidden');
    }

    // -- AI: Salary Suggestion -------------------------------------
    const salaryBaseUrl = '/hrm/ai/salary-suggest/';

    async function openSalarySuggest(empId, empName) {
        document.getElementById('salary-modal-title').textContent = 'Saran Gaji AI — ' + empName;
        document.getElementById('salary-modal-content').innerHTML =
            '<div class="animate-pulse text-slate-500 text-sm py-4 text-center">Menganalisis data...</div>';
        document.getElementById('modal-salary-suggest').classList.remove('hidden');

        try {
            const res  = await fetch(salaryBaseUrl + empId, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();
            const s    = data.suggestion;

            const fmt = v => v != null ? 'Rp ' + Number(v).toLocaleString('id-ID') : '—';
            const confColor = { high: 'text-green-400', medium: 'text-yellow-400', low: 'text-slate-400' };

            let html = `
                <div class="space-y-4">
                    ${s.benchmark_note ? `<p class="text-xs text-slate-400 bg-white/5 rounded-lg px-3 py-2">${esc(s.benchmark_note)}</p>` : ''}
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-white/5 rounded-xl p-3 border border-white/10">
                            <p class="text-xs text-slate-400 mb-1">Gaji Saat Ini</p>
                            <p class="text-lg font-bold text-white">${fmt(s.current_salary)}</p>
                        </div>
                        <div class="bg-purple-500/10 rounded-xl p-3 border border-purple-500/30">
                            <p class="text-xs text-slate-400 mb-1">Total Saran AI</p>
                            <p class="text-lg font-bold text-purple-300">${fmt(s.total_suggested)}</p>
                        </div>
                    </div>
                    <table class="w-full text-sm">
                        <thead><tr class="text-xs text-slate-500 border-b border-white/10">
                            <th class="py-2 text-left">Komponen</th>
                            <th class="py-2 text-right">Saran</th>
                            <th class="py-2 text-left pl-3 hidden sm:table-cell">Basis</th>
                        </tr></thead>
                        <tbody class="divide-y divide-white/5">
                            <tr>
                                <td class="py-2 text-white">Gaji Pokok</td>
                                <td class="py-2 text-right font-semibold text-white">${fmt(s.base_salary.suggested)}</td>
                                <td class="py-2 pl-3 hidden sm:table-cell ${confColor[s.base_salary.confidence] ?? 'text-slate-400'} text-xs">${esc(s.base_salary.basis)}</td>
                            </tr>
                            <tr>
                                <td class="py-2 text-slate-300">Tunjangan Transport</td>
                                <td class="py-2 text-right text-slate-300">${fmt(s.allowance_transport.suggested)}</td>
                                <td class="py-2 pl-3 hidden sm:table-cell text-slate-500 text-xs">${esc(s.allowance_transport.basis)}</td>
                            </tr>
                            <tr>
                                <td class="py-2 text-slate-300">Tunjangan Makan</td>
                                <td class="py-2 text-right text-slate-300">${fmt(s.allowance_meal.suggested)}</td>
                                <td class="py-2 pl-3 hidden sm:table-cell text-slate-500 text-xs">${esc(s.allowance_meal.basis)}</td>
                            </tr>
                            <tr>
                                <td class="py-2 text-slate-300">Tunjangan Jabatan</td>
                                <td class="py-2 text-right text-slate-300">${fmt(s.allowance_position.suggested)}</td>
                                <td class="py-2 pl-3 hidden sm:table-cell text-slate-500 text-xs">${esc(s.allowance_position.basis)}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="flex justify-end gap-2 pt-2">
                        <button onclick="applySalary(${s.employee_id}, ${s.base_salary.suggested})"
                            class="px-4 py-2 text-sm bg-purple-600 hover:bg-purple-700 text-white rounded-xl">
                            Terapkan Gaji Pokok
                        </button>
                    </div>
                </div>`;

            document.getElementById('salary-modal-content').innerHTML = html;
        } catch (e) {
            document.getElementById('salary-modal-content').innerHTML =
                '<p class="text-red-400 text-sm">Gagal memuat saran AI.</p>';
        }
    }

    function applySalary(empId, salary) {
        document.getElementById('modal-salary-suggest').classList.add('hidden');
        // Pre-fill edit modal with suggested salary
        document.getElementById('form-edit-emp').action = '{{ url("hrm") }}/' + empId;
        document.getElementById('ee-salary').value = salary;
        document.getElementById('modal-edit-emp').classList.remove('hidden');
    }

    function esc(s) {
        return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    // -- AI: Turnover Risk Score -----------------------------------
    async function loadTurnoverRisk() {
        const btn = document.getElementById('turnover-btn');
        btn.disabled = true;
        document.getElementById('turnover-result').classList.remove('hidden');
        document.getElementById('turnover-loading').classList.remove('hidden');
        document.getElementById('turnover-content').innerHTML = '';

        try {
            const res  = await fetch('{{ route("hrm.ai.turnover-risk") }}');
            const data = await res.json();
            document.getElementById('turnover-loading').classList.add('hidden');
            document.getElementById('turnover-content').innerHTML = renderTurnoverRisk(data);
        } catch(e) {
            document.getElementById('turnover-loading').classList.add('hidden');
            document.getElementById('turnover-content').innerHTML =
                '<p class="text-sm text-red-500">Gagal memuat analisis risiko. Coba lagi.</p>';
        } finally {
            btn.disabled = false;
        }
    }

    function renderTurnoverRisk(data) {
        if (!data.employees || data.employees.length === 0) {
            return '<p class="text-sm text-gray-400 py-4 text-center">Tidak ada sinyal risiko resign yang terdeteksi. Semua karyawan tampak stabil.</p>';
        }

        const levelCfg = {
            critical: { bg: 'bg-red-50',    border: 'border-red-200',    badge: 'bg-red-100 text-red-700',    bar: 'bg-red-500',    label: 'Kritis' },
            high:     { bg: 'bg-orange-50', border: 'border-orange-200', badge: 'bg-orange-100 text-orange-700', bar: 'bg-orange-500', label: 'Tinggi' },
            medium:   { bg: 'bg-amber-50',  border: 'border-amber-200',  badge: 'bg-amber-100 text-amber-700',  bar: 'bg-amber-500',  label: 'Sedang' },
            low:      { bg: 'bg-blue-50',   border: 'border-blue-200',   badge: 'bg-blue-100 text-blue-700',   bar: 'bg-blue-500',   label: 'Rendah' },
        };

        const signalIcon = {
            performance:  '??',
            attendance:   '???',
            compensation: '??',
            tenure:       '??',
            burnout:      '??',
            engagement:   '??',
        };

        const prioColor = {
            high:   'bg-red-100 text-red-700',
            medium: 'bg-amber-100 text-amber-700',
            low:    'bg-gray-100 text-gray-500',
        };

        // Summary bar
        const summary = `
        <div class="flex flex-wrap gap-3 mb-4 p-3 bg-gray-50 rounded-xl">
            <span class="text-xs text-gray-500 self-center">Terdeteksi ${data.total} karyawan berisiko:</span>
            ${data.critical > 0 ? `<span class="text-xs px-2 py-1 rounded-full bg-red-100 text-red-700 font-semibold">${data.critical} Kritis</span>` : ''}
            ${data.high > 0    ? `<span class="text-xs px-2 py-1 rounded-full bg-orange-100 text-orange-700 font-semibold">${data.high} Tinggi</span>` : ''}
        </div>`;

        const cards = data.employees.map(emp => {
            const cfg = levelCfg[emp.risk_level] || levelCfg.medium;

            const signals = emp.signals.map(s =>
                `<div class="flex items-start gap-1.5 text-xs text-gray-600">
                    <span class="shrink-0">${signalIcon[s.type] || '??'}</span>
                    <span>${esc(s.message)}</span>
                </div>`
            ).join('');

            const recs = emp.recommendations.map(r =>
                `<div class="flex items-start gap-2">
                    <span class="text-xs px-1.5 py-0.5 rounded-full shrink-0 mt-0.5 ${prioColor[r.priority]}">${r.priority === 'high' ? 'Segera' : r.priority === 'medium' ? 'Disarankan' : 'Opsional'}</span>
                    <p class="text-xs text-gray-600">${esc(r.action)}</p>
                </div>`
            ).join('');

            return `
            <div class="border ${cfg.border} ${cfg.bg} rounded-xl p-4 space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="font-semibold text-gray-900 text-sm">${esc(emp.name)}</p>
                            <span class="text-xs px-2 py-0.5 rounded-full ${cfg.badge} font-semibold">${cfg.label}</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">${esc(emp.position)} · ${esc(emp.department)} · ${esc(emp.tenure_label)}</p>
                    </div>
                    <div class="flex flex-col items-end shrink-0">
                        <span class="text-2xl font-black text-gray-900">${emp.risk_score}</span>
                        <span class="text-xs text-gray-400">/ 100</span>
                    </div>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-1.5">
                    <div class="${cfg.bar} h-1.5 rounded-full transition-all" style="width:${emp.risk_score}%"></div>
                </div>
                <details class="group">
                    <summary class="text-xs text-gray-500 cursor-pointer hover:text-gray-700 select-none flex items-center gap-1">
                        <svg class="w-3 h-3 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        ${emp.signals.length} sinyal risiko · ${emp.recommendations.length} rekomendasi
                    </summary>
                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-1.5">Sinyal Terdeteksi</p>
                            <div class="space-y-1.5">${signals}</div>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-1.5">Rekomendasi HRD</p>
                            <div class="space-y-2">${recs}</div>
                        </div>
                    </div>
                </details>
            </div>`;
        }).join('');

        return `${summary}<div class="grid grid-cols-1 lg:grid-cols-2 gap-3">${cards}</div>`;
    }
    </script>
    @endpush
</x-app-layout>
