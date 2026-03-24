<x-app-layout>
    <x-slot name="header">Manajemen Shift & Jadwal Kerja</x-slot>

    @php
    $days     = ['Sen','Sel','Rab','Kam','Jum','Sab','Min'];
    $prevWeek = $weekStart->copy()->subWeek()->format('Y-m-d');
    $nextWeek = $weekStart->copy()->addWeek()->format('Y-m-d');
    @endphp

    <div class="flex flex-col lg:flex-row gap-5">

        {{-- ── Sidebar ──────────────────────────────────────────── --}}
        <div class="lg:w-64 shrink-0 space-y-4">

            {{-- Shift legend / palette --}}
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Template Shift</p>
                    <button onclick="document.getElementById('modal-add-shift').classList.remove('hidden')"
                        class="text-xs px-2 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700">+ Baru</button>
                </div>
                <div class="space-y-1.5" id="shift-palette">
                    @forelse($shifts as $shift)
                    {{-- Draggable shift tile --}}
                    <div draggable="true"
                         data-shift-id="{{ $shift->id }}"
                         data-shift-name="{{ $shift->name }}"
                         data-shift-color="{{ $shift->color }}"
                         data-shift-time="{{ $shift->timeLabel() }}"
                         ondragstart="onPaletteDragStart(event)"
                         class="shift-palette-item flex items-center gap-2 px-3 py-2 rounded-xl border border-gray-100 dark:border-white/10 cursor-grab active:cursor-grabbing hover:bg-gray-50 dark:hover:bg-white/5 group select-none">
                        <div class="w-3 h-3 rounded-full shrink-0" style="background:{{ $shift->color }}"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900 dark:text-white truncate">{{ $shift->name }}</p>
                            <p class="text-xs text-gray-400 dark:text-slate-500">{{ $shift->timeLabel() }}</p>
                        </div>
                        <button onclick="openEditShift({{ $shift->id }}, {{ json_encode($shift->only(['name','color','start_time','end_time','break_minutes','crosses_midnight','description'])) }})"
                            class="opacity-0 group-hover:opacity-100 p-1 rounded text-gray-400 hover:text-white transition shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                    </div>
                    @empty
                    <p class="text-xs text-gray-400 dark:text-slate-500">Belum ada shift.</p>
                    @endforelse
                </div>
                <p class="text-xs text-gray-400 dark:text-slate-500 mt-3 text-center">↑ Drag ke sel jadwal</p>
            </div>

            {{-- AI Conflict Detection --}}
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-6 h-6 rounded-lg bg-orange-100 dark:bg-orange-500/20 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                    </div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">AI Conflict Detection</p>
                </div>
                <button onclick="runConflictDetection()" id="conflict-btn"
                    class="w-full py-2 text-sm bg-orange-600 text-white rounded-xl hover:bg-orange-700 flex items-center justify-center gap-1.5 disabled:opacity-50">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    Analisis Konflik
                </button>
                <div id="conflict-summary" class="hidden mt-3 space-y-1.5 text-xs"></div>
            </div>

            {{-- Copy week --}}
            <form method="POST" action="{{ route('hrm.shifts.copy-week') }}">
                @csrf
                <input type="hidden" name="week_start" value="{{ $weekStart->format('Y-m-d') }}">
                <button type="submit" onclick="return confirm('Salin jadwal minggu ini ke minggu depan?')"
                    class="w-full px-4 py-2.5 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5 text-center">
                    📋 Salin ke Minggu Depan
                </button>
            </form>
        </div>

        {{-- ── Main: Weekly Scheduler ───────────────────────────── --}}
        <div class="flex-1 min-w-0 space-y-4">

            {{-- Week nav --}}
            <div class="flex items-center justify-between">
                <a href="{{ route('hrm.shifts.index', ['week' => $prevWeek]) }}"
                   class="p-2 rounded-xl border border-gray-200 dark:border-white/10 text-gray-500 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-white/5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div class="text-center">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ $weekStart->format('d M') }} – {{ $weekEnd->format('d M Y') }}
                    </p>
                    @if($weekStart->isCurrentWeek())
                    <p class="text-xs text-blue-400">Minggu Ini</p>
                    @endif
                </div>
                <a href="{{ route('hrm.shifts.index', ['week' => $nextWeek]) }}"
                   class="p-2 rounded-xl border border-gray-200 dark:border-white/10 text-gray-500 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-white/5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            {{-- AI Conflict Results Panel --}}
            <div id="conflict-panel" class="hidden bg-white dark:bg-[#1e293b] rounded-2xl border border-orange-200 dark:border-orange-500/30 p-4">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Hasil Analisis Konflik Jadwal</p>
                    <button onclick="document.getElementById('conflict-panel').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white text-xs">✕ Tutup</button>
                </div>
                <div id="conflict-loading" class="hidden py-4 text-center">
                    <div class="inline-flex items-center gap-2 text-sm text-gray-500 dark:text-slate-400">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Menganalisis jadwal...
                    </div>
                </div>
                <div id="conflict-content"></div>
            </div>

            {{-- Scheduler table --}}
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm border-collapse" id="scheduler-table">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-white/5">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase w-40 border-b border-gray-100 dark:border-white/10 sticky left-0 bg-gray-50 dark:bg-[#1e293b] z-10">Karyawan</th>
                                @foreach($weekDays as $i => $day)
                                <th class="px-2 py-3 text-center text-xs font-semibold border-b border-gray-100 dark:border-white/10 min-w-[90px]
                                    {{ $day->isToday() ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-slate-400' }}">
                                    <p>{{ $days[$i] }}</p>
                                    <p class="text-base font-bold {{ $day->isToday() ? 'text-blue-600 dark:text-blue-400' : 'text-gray-900 dark:text-white' }}">
                                        {{ $day->format('d') }}
                                    </p>
                                </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @forelse($employees as $emp)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] group" data-emp-id="{{ $emp->id }}">
                                <td class="px-4 py-2 border-r border-gray-100 dark:border-white/10 sticky left-0 bg-white dark:bg-[#1e293b] group-hover:bg-gray-50/50 dark:group-hover:bg-white/[0.02] z-10">
                                    <p class="font-medium text-gray-900 dark:text-white text-xs truncate max-w-[140px]">{{ $emp->name }}</p>
                                    <p class="text-xs text-gray-400 dark:text-slate-500 truncate">{{ $emp->department ?? $emp->position ?? '-' }}</p>
                                </td>
                                @foreach($weekDays as $day)
                                @php
                                    $dateStr  = $day->format('Y-m-d');
                                    $schedule = $schedules[$emp->id][$dateStr] ?? null;
                                    $isWeekend = $day->isWeekend();
                                @endphp
                                <td class="px-1 py-1 text-center {{ $isWeekend ? 'bg-gray-50/50 dark:bg-white/[0.02]' : '' }}"
                                    data-emp="{{ $emp->id }}" data-date="{{ $dateStr }}"
                                    ondragover="onDragOver(event)"
                                    ondragleave="onDragLeave(event)"
                                    ondrop="onDrop(event)">
                                    <button
                                        onclick="openShiftPicker({{ $emp->id }}, '{{ $dateStr }}', {{ $schedule?->work_shift_id ?? 'null' }})"
                                        draggable="{{ $schedule ? 'true' : 'false' }}"
                                        ondragstart="onCellDragStart(event, {{ $emp->id }}, '{{ $dateStr }}', {{ $schedule?->work_shift_id ?? 'null' }})"
                                        data-emp="{{ $emp->id }}" data-date="{{ $dateStr }}"
                                        class="shift-cell w-full min-h-[52px] rounded-lg text-xs transition flex flex-col items-center justify-center gap-0.5 px-1 relative
                                            {{ $schedule ? 'cursor-grab active:cursor-grabbing' : ($isWeekend ? 'cursor-default' : 'hover:bg-gray-100 dark:hover:bg-white/5 cursor-pointer') }}"
                                        @if($schedule)
                                        style="background-color: {{ $schedule->shift->color }}22; border: 1px solid {{ $schedule->shift->color }}55; color: {{ $schedule->shift->color }}"
                                        @endif
                                    >
                                        @if($schedule)
                                        <span class="font-semibold leading-tight">{{ $schedule->shift->name }}</span>
                                        <span class="opacity-75 leading-tight text-[10px]">{{ $schedule->shift->timeLabel() }}</span>
                                        @else
                                        <span class="text-lg leading-none text-gray-300 dark:text-slate-700">{{ $isWeekend ? '—' : '+' }}</span>
                                        @endif
                                    </button>
                                </td>
                                @endforeach
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada karyawan aktif.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    {{-- ── Modal: Pilih Shift ───────────────────────────────────── --}}
    <div id="modal-shift-picker" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 w-full max-w-sm shadow-2xl">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-white/10">
                <div>
                    <p class="font-semibold text-gray-900 dark:text-white text-sm">Pilih Shift</p>
                    <p id="picker-label" class="text-xs text-gray-400 dark:text-slate-500 mt-0.5"></p>
                </div>
                <button onclick="document.getElementById('modal-shift-picker').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <div class="p-4 space-y-2">
                @forelse($shifts as $shift)
                <button onclick="assignShift(pickerEmpId, pickerDate, {{ $shift->id }})"
                    data-shift-id="{{ $shift->id }}"
                    class="picker-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl border border-gray-100 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/5 text-left transition">
                    <div class="w-4 h-4 rounded-full shrink-0" style="background:{{ $shift->color }}"></div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $shift->name }}</p>
                        <p class="text-xs text-gray-400 dark:text-slate-500">{{ $shift->timeLabel() }} · {{ round($shift->workMinutes()/60,1) }} jam</p>
                    </div>
                </button>
                @empty
                <p class="text-sm text-gray-400 dark:text-slate-500 text-center py-4">Belum ada shift.</p>
                @endforelse
            </div>
            <div class="px-4 pb-4">
                <button onclick="assignShift(pickerEmpId, pickerDate, null)"
                    class="w-full px-4 py-2.5 text-sm border border-red-500/30 text-red-400 rounded-xl hover:bg-red-500/10 transition">
                    🗑 Hapus Jadwal
                </button>
            </div>
        </div>
    </div>

    {{-- ── Modal: Tambah Shift ──────────────────────────────────── --}}
    <div id="modal-add-shift" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 w-full max-w-md shadow-2xl">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-white/10">
                <p class="font-semibold text-gray-900 dark:text-white text-sm">Tambah Template Shift</p>
                <button onclick="document.getElementById('modal-add-shift').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('hrm.shifts.shift.store') }}" class="p-5 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama Shift</label>
                        <input type="text" name="name" required placeholder="cth: Shift Pagi"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Mulai</label>
                        <input type="time" name="start_time" required value="08:00"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Selesai</label>
                        <input type="time" name="end_time" required value="17:00"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Istirahat (menit)</label>
                        <input type="number" name="break_minutes" value="60" min="0" max="480"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Warna</label>
                        <input type="color" name="color" value="#3b82f6"
                            class="w-full h-10 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] cursor-pointer">
                    </div>
                    <div class="col-span-2 flex items-center gap-2">
                        <input type="checkbox" name="crosses_midnight" id="add-crosses-midnight" value="1" class="rounded">
                        <label for="add-crosses-midnight" class="text-sm text-gray-600 dark:text-slate-400">Melewati tengah malam</label>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Deskripsi (opsional)</label>
                        <input type="text" name="description" placeholder="Keterangan tambahan..."
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('modal-add-shift').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Modal: Edit Shift ────────────────────────────────────── --}}
    <div id="modal-edit-shift" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 w-full max-w-md shadow-2xl">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-white/10">
                <p class="font-semibold text-gray-900 dark:text-white text-sm">Edit Shift</p>
                <button onclick="document.getElementById('modal-edit-shift').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form id="form-edit-shift" method="POST" class="p-5 space-y-4">
                @csrf @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama Shift</label>
                        <input type="text" name="name" id="edit-name" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Mulai</label>
                        <input type="time" name="start_time" id="edit-start"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Selesai</label>
                        <input type="time" name="end_time" id="edit-end"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Istirahat (menit)</label>
                        <input type="number" name="break_minutes" id="edit-break" min="0" max="480"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Warna</label>
                        <input type="color" name="color" id="edit-color"
                            class="w-full h-10 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] cursor-pointer">
                    </div>
                    <div class="col-span-2 flex items-center gap-2">
                        <input type="checkbox" name="crosses_midnight" id="edit-crosses-midnight" value="1" class="rounded">
                        <label for="edit-crosses-midnight" class="text-sm text-gray-600 dark:text-slate-400">Melewati tengah malam</label>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Deskripsi (opsional)</label>
                        <input type="text" name="description" id="edit-description"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex justify-between items-center pt-2">
                    <button type="button" id="btn-delete-shift"
                        class="px-4 py-2 text-sm border border-red-500/30 text-red-400 rounded-xl hover:bg-red-500/10">Nonaktifkan</button>
                    <div class="flex gap-2">
                        <button type="button" onclick="document.getElementById('modal-edit-shift').classList.add('hidden')"
                            class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                        <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@push('scripts')
<script>
const ASSIGN_URL   = '{{ route("hrm.shifts.assign") }}';
const CONFLICT_URL = '{{ route("hrm.shifts.conflicts") }}';
const WEEK_START   = '{{ $weekStart->format("Y-m-d") }}';
const CSRF         = document.querySelector('meta[name="csrf-token"]').content;

// ── Picker state ───────────────────────────────────────────────
let pickerEmpId = null, pickerDate = null;

// ── Drag state ─────────────────────────────────────────────────
let dragShiftId    = null;  // shift being dragged
let dragShiftName  = null;
let dragShiftColor = null;
let dragShiftTime  = null;
let dragFromEmp    = null;  // source cell (for cell→cell move)
let dragFromDate   = null;

// ── Palette drag start (shift tile → cell) ─────────────────────
function onPaletteDragStart(e) {
    const el = e.currentTarget;
    dragShiftId    = parseInt(el.dataset.shiftId);
    dragShiftName  = el.dataset.shiftName;
    dragShiftColor = el.dataset.shiftColor;
    dragShiftTime  = el.dataset.shiftTime;
    dragFromEmp    = null;
    dragFromDate   = null;
    e.dataTransfer.effectAllowed = 'copy';
    e.dataTransfer.setData('text/plain', dragShiftId);
}

// ── Cell drag start (move existing shift) ─────────────────────
function onCellDragStart(e, empId, date, shiftId) {
    if (!shiftId
) return;
    dragShiftId    = shiftId;
    dragFromEmp    = empId;
    dragFromDate   = date;
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', shiftId);
}

// ── Drag over / leave ──────────────────────────────────────────
function onDragOver(e) {
    e.preventDefault();
    e.currentTarget.classList.add('ring-2', 'ring-blue-400', 'ring-inset');
    e.dataTransfer.dropEffect = dragFromEmp ? 'move' : 'copy';
}
function onDragLeave(e) {
    e.currentTarget.classList.remove('ring-2', 'ring-blue-400', 'ring-inset');
}

// ── Drop ───────────────────────────────────────────────────────
function onDrop(e) {
    e.preventDefault();
    const td   = e.currentTarget;
    td.classList.remove('ring-2', 'ring-blue-400', 'ring-inset');
    const empId = parseInt(td.dataset.emp);
    const date  = td.dataset.date;
    if (!dragShiftId) return;
    // If moving from another cell, clear source first
    if (dragFromEmp && (dragFromEmp !== empId || dragFromDate !== date)) {
        doAssign(dragFromEmp, dragFromDate, null);
    }
    doAssign(empId, date, dragShiftId);
}

// ── Shift picker ───────────────────────────────────────────────
function openShiftPicker(empId, date, currentShiftId) {
    pickerEmpId = empId;
    pickerDate  = date;
    document.getElementById('picker-label').textContent = date;
    // Highlight current
    document.querySelectorAll('.picker-btn').forEach(btn => {
        btn.classList.toggle('ring-2', parseInt(btn.dataset.shiftId) === currentShiftId);
        btn.classList.toggle('ring-blue-500', parseInt(btn.dataset.shiftId) === currentShiftId);
    });
    document.getElementById('modal-shift-picker').classList.remove('hidden');
}

function assignShift(empId, date, shiftId) {
    document.getElementById('modal-shift-picker').classList.add('hidden');
    doAssign(empId, date, shiftId);
}

// ── Core assign (AJAX) ─────────────────────────────────────────
function doAssign(empId, date, shiftId) {
    fetch(ASSIGN_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ employee_id: empId, date, shift_id: shiftId }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
    })
    .catch(() => alert('Gagal menyimpan jadwal.'));
}

// ── Edit shift modal ───────────────────────────────────────────
function openEditShift(id, data) {
    const form = document.getElementById('form-edit-shift');
    form.action = '{{ url("hrm/shifts/shifts") }}/' + id;
    document.getElementById('edit-name').value        = data.name;
    document.getElementById('edit-start').value       = data.start_time;
    document.getElementById('edit-end').value         = data.end_time;
    document.getElementById('edit-break').value       = data.break_minutes;
    document.getElementById('edit-color').value       = data.color;
    document.getElementById('edit-description').value = data.description ?? '';
    document.getElementById('edit-crosses-midnight').checked = !!data.crosses_midnight;
    document.getElementById('btn-delete-shift').onclick = () => {
        if (confirm('Nonaktifkan shift ini?')) {
            fetch('{{ url("hrm/shifts/shifts") }}/' + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            }).then(() => location.reload());
        }
    };
    document.getElementById('modal-add-shift').classList.add('hidden');
    document.getElementById('modal-edit-shift').classList.remove('hidden');
}

// ── AI Conflict Detection ──────────────────────────────────────
function runConflictDetection() {
    const panel = document.getElementById('conflict-panel');
    const loading = document.getElementById('conflict-loading');
    const content = document.getElementById('conflict-content');
    const btn = document.getElementById('conflict-btn');

    panel.classList.remove('hidden');
    loading.classList.remove('hidden');
    content.innerHTML = '';
    btn.disabled = true;

    fetch(CONFLICT_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ week_start: WEEK_START }),
    })
    .then(r => r.json())
    .then(data => {
        loading.classList.add('hidden');
        btn.disabled = false;
        if (data.conflicts && data.conflicts.length > 0) {
            content.innerHTML = data.conflicts.map(c => `
                <div class="flex items-start gap-2 p-2 rounded-lg bg-orange-50 dark:bg-orange-500/10 border border-orange-200 dark:border-orange-500/20">
                    <span class="text-orange-500 mt-0.5">⚠</span>
                    <p class="text-xs text-orange-700 dark:text-orange-300">${c}</p>
                </div>`).join('');
        } else {
            content.innerHTML = '<p class="text-xs text-green-600 dark:text-green-400 text-center py-2">✓ Tidak ada konflik jadwal ditemukan.</p>';
        }
        if (data.summary) {
            document.getElementById('conflict-summary').innerHTML =
                `<p class="text-xs text-gray-500 dark:text-slate-400">${data.summary}</p>`;
            document.getElementById('conflict-summary').classList.remove('hidden');
        }
    })
    .catch(() => {
        loading.classList.add('hidden');
        btn.disabled = false;
        content.innerHTML = '<p class="text-xs text-red-400 text-center py-2">Gagal menganalisis konflik.</p>';
    });
}
</script>
@endpush

</x-app-layout>
