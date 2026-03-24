<x-app-layout>
    <x-slot name="header">Pelamar — {{ $posting->title }}</x-slot>

    {{-- Pipeline stage tabs --}}
    @php
    $stages = ['applied'=>'Lamaran','screening'=>'Seleksi','interview'=>'Interview','offer'=>'Penawaran','hired'=>'Diterima','rejected'=>'Ditolak'];
    @endphp
    <div class="flex flex-wrap gap-2 mb-5">
        <a href="{{ route('hrm.recruitment.applications', $posting) }}"
           class="px-3 py-1.5 text-xs rounded-xl border {{ !request('stage') ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-200 dark:border-white/10 text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5' }}">
            Semua ({{ $stageCounts->sum() }})
        </a>
        @foreach($stages as $key => $label)
        <a href="{{ route('hrm.recruitment.applications', [$posting, 'stage' => $key]) }}"
           class="px-3 py-1.5 text-xs rounded-xl border {{ request('stage') === $key ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-200 dark:border-white/10 text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5' }}">
            {{ $label }} ({{ $stageCounts[$key] ?? 0 }})
        </a>
        @endforeach
    </div>

    <div class="flex items-center justify-between mb-4">
        <a href="{{ route('hrm.recruitment.index') }}" class="text-sm text-blue-500 hover:underline">← Kembali ke Lowongan</a>
        <button onclick="document.getElementById('modal-add-app').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Tambah Pelamar</button>
    </div>

    {{-- Applications table --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Pelamar</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Kontak</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Interview</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Gaji Ditawarkan</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($apps as $app)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900 dark:text-white">{{ $app->applicant_name }}</p>
                            @if($app->notes)<p class="text-xs text-gray-400 dark:text-slate-500 truncate max-w-[180px]">{{ $app->notes }}</p>@endif
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell text-xs text-gray-500 dark:text-slate-400">
                            @if($app->applicant_email)<p>{{ $app->applicant_email }}</p>@endif
                            @if($app->applicant_phone)<p>{{ $app->applicant_phone }}</p>@endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs {{ $app->stageBadgeClass() }}">{{ $app->stageLabel() }}</span>
                        </td>
                        <td class="px-4 py-3 hidden md:table-cell text-xs text-gray-500 dark:text-slate-400">
                            @if($app->interview_date)
                            <p>{{ $app->interview_date->format('d M Y') }}</p>
                            @if($app->interview_location)<p>{{ $app->interview_location }}</p>@endif
                            @else<span class="text-gray-300 dark:text-slate-600">—</span>@endif
                        </td>
                        <td class="px-4 py-3 text-right hidden md:table-cell text-gray-900 dark:text-white">
                            {{ $app->offered_salary ? 'Rp '.number_format($app->offered_salary,0,',','.') : '—' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button onclick="openUpdateStage({{ $app->id }}, '{{ addslashes($app->applicant_name) }}', '{{ $app->stage }}', '{{ $app->interview_date?->format('Y-m-d') ?? '' }}', '{{ addslashes($app->interview_location ?? '') }}', {{ $app->offered_salary ?? 'null' }}, '{{ $app->expected_join_date?->format('Y-m-d') ?? '' }}', {{ json_encode($app->notes) }})"
                                class="px-3 py-1.5 text-xs border border-gray-200 dark:border-white/10 rounded-lg text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">
                                Update Status
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada pelamar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($apps->hasPages())
        <div class="px-4 py-3 border-t border-gray-100 dark:border-white/5">{{ $apps->links() }}</div>
        @endif
    </div>

    {{-- Modal Tambah Pelamar --}}
    <div id="modal-add-app" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-lg shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Tambah Pelamar</h3>
                <button onclick="document.getElementById('modal-add-app').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('hrm.recruitment.application.store', $posting) }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama Pelamar *</label>
                    <input type="text" name="applicant_name" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Email</label>
                        <input type="email" name="applicant_email" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">No. HP</label>
                        <input type="text" name="applicant_phone" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Catatan</label>
                    <textarea name="notes" rows="2" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-add-app').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Update Stage --}}
    <div id="modal-update-stage" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10 sticky top-0 bg-white dark:bg-[#1e293b]">
                <h3 id="stage-modal-title" class="font-semibold text-gray-900 dark:text-white text-sm">Update Status Pelamar</h3>
                <button onclick="document.getElementById('modal-update-stage').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form id="form-update-stage" method="POST" class="p-6 space-y-4">
                @csrf @method('PATCH')
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Status / Tahap</label>
                    <select id="us-stage" name="stage" onchange="toggleStageFields(this.value)"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                        <option value="applied">Lamaran Masuk</option>
                        <option value="screening">Seleksi</option>
                        <option value="interview">Interview</option>
                        <option value="offer">Penawaran</option>
                        <option value="hired">Diterima ✓</option>
                        <option value="rejected">Ditolak ✗</option>
                    </select>
                </div>
                <div id="fields-interview" class="space-y-3 hidden">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanggal Interview</label>
                            <input type="date" id="us-interview-date" name="interview_date"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Lokasi / Link</label>
                            <input type="text" id="us-interview-loc" name="interview_location" placeholder="Ruang meeting / Zoom"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
                <div id="fields-offer" class="space-y-3 hidden">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Gaji Ditawarkan</label>
                            <input type="number" id="us-salary" name="offered_salary" min="0" step="100000"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Rencana Mulai Kerja</label>
                            <input type="date" id="us-join-date" name="expected_join_date"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="bg-green-500/10 border border-green-500/20 rounded-xl p-3 text-xs text-green-400">
                        Jika status diubah ke <strong>Diterima</strong>, karyawan baru akan otomatis dibuat dan onboarding dimulai.
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Catatan Internal</label>
                    <textarea id="us-notes" name="notes" rows="2"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-update-stage').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openUpdateStage(id, name, stage, interviewDate, interviewLoc, salary, joinDate, notes) {
        document.getElementById('stage-modal-title').textContent = 'Update Status — ' + name;
        document.getElementById('form-update-stage').action = '{{ url("hrm/recruitment/applications") }}/' + id + '/stage';
        document.getElementById('us-stage').value          = stage;
        document.getElementById('us-interview-date').value = interviewDate;
        document.getElementById('us-interview-loc').value  = interviewLoc;
        document.getElementById('us-salary').value         = salary ?? '';
        document.getElementById('us-join-date').value      = joinDate;
        document.getElementById('us-notes').value          = notes ?? '';
        toggleStageFields(stage);
        document.getElementById('modal-update-stage').classList.remove('hidden');
    }

    function toggleStageFields(stage) {
        document.getElementById('fields-interview').classList.toggle('hidden', stage !== 'interview');
        document.getElementById('fields-offer').classList.toggle('hidden', !['offer','hired'].includes(stage));
    }
    </script>
    @endpush
</x-app-layout>
