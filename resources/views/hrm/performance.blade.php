<x-app-layout>
    <x-slot name="title">Penilaian Kinerja — Qalcuity ERP</x-slot>
    <x-slot name="header">Penilaian Kinerja</x-slot>
    <x-slot name="topbarActions">
        <button onclick="document.getElementById('modal-add-review').classList.remove('hidden')"
            class="flex items-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat Penilaian
        </button>
    </x-slot>

    {{-- Filter --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4 mb-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <select name="employee_id" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                <option value="">Semua Karyawan</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" @selected(request('employee_id') == $emp->id)>{{ $emp->name }}</option>
                @endforeach
            </select>
            <select name="period_type" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                <option value="">Semua Periode</option>
                <option value="monthly" @selected(request('period_type')==='monthly')>Bulanan</option>
                <option value="quarterly" @selected(request('period_type')==='quarterly')>Kuartalan</option>
                <option value="annual" @selected(request('period_type')==='annual')>Tahunan</option>
            </select>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
            <a href="{{ route('hrm.performance') }}" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Reset</a>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Karyawan</th>
                        <th class="px-4 py-3 text-left">Periode</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Penilai</th>
                        <th class="px-4 py-3 text-center">Skor</th>
                        <th class="px-4 py-3 text-center hidden lg:table-cell">Rekomendasi</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($reviews as $review)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5 cursor-pointer" onclick="openDetail({{ $review->id }})">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900 dark:text-white">{{ $review->employee->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-slate-400">{{ $review->employee->department ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-700 dark:text-slate-300">
                            {{ $review->period }}
                            <span class="text-xs text-gray-400 ml-1">({{ match($review->period_type) { 'monthly' => 'Bulanan', 'quarterly' => 'Kuartalan', default => 'Tahunan' } }})</span>
                        </td>
                        <td class="px-4 py-3 hidden md:table-cell text-gray-500 dark:text-slate-400">{{ $review->reviewer->name }}</td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $score = (float) $review->overall_score;
                                $color = $score >= 4 ? 'text-green-600 dark:text-green-400' : ($score >= 3 ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400');
                            @endphp
                            <span class="font-bold text-lg {{ $color }}">{{ number_format($score, 1) }}</span>
                            <span class="text-xs text-gray-400">/5</span>
                        </td>
                        <td class="px-4 py-3 text-center hidden lg:table-cell">
                            @if($review->recommendation)
                            @php
                                $recColor = match($review->recommendation) {
                                    'promote'   => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400',
                                    'pip'       => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400',
                                    'terminate' => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400',
                                    default     => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs {{ $recColor }}">{{ $review->recommendationLabel() }}</span>
                            @else
                            <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $sBadge = match($review->status) {
                                    'acknowledged' => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400',
                                    'submitted'    => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
                                    default        => 'bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-slate-400',
                                };
                                $sLabel = match($review->status) {
                                    'acknowledged' => 'Dikonfirmasi', 'submitted' => 'Disubmit', default => 'Draft',
                                };
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs {{ $sBadge }}">{{ $sLabel }}</span>
                        </td>
                        <td class="px-4 py-3 text-center" onclick="event.stopPropagation()">
                            <div class="flex items-center justify-center gap-1">
                                @if($review->status === 'submitted')
                                <form method="POST" action="{{ route('hrm.performance.acknowledge', $review) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="px-2 py-1 text-xs bg-green-600 text-white rounded-lg hover:bg-green-700">Konfirmasi</button>
                                </form>
                                @endif
                                <form method="POST" action="{{ route('hrm.performance.destroy', $review) }}"
                                      onsubmit="return confirm('Hapus penilaian ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada penilaian kinerja.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reviews->hasPages())
        <div class="px-4 py-3 border-t border-gray-100 dark:border-white/5">{{ $reviews->links() }}</div>
        @endif
    </div>

    {{-- Modal Buat Penilaian --}}
    <div id="modal-add-review" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10 sticky top-0 bg-white dark:bg-[#1e293b]">
                <h3 class="font-semibold text-gray-900 dark:text-white">Buat Penilaian Kinerja</h3>
                <button onclick="document.getElementById('modal-add-review').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="{{ route('hrm.performance.store') }}" class="p-6 space-y-5">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Karyawan *</label>
                        <select name="employee_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                            <option value="">Pilih...</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Penilai *</label>
                        <select name="reviewer_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                            <option value="">Pilih...</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Periode *</label>
                        <input type="text" name="period" placeholder="cth: Q1 2026 / 2026-01" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tipe Periode *</label>
                        <select name="period_type" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                            <option value="quarterly">Kuartalan</option>
                            <option value="monthly">Bulanan</option>
                            <option value="annual">Tahunan</option>
                        </select>
                    </div>
                </div>

                {{-- Score sliders --}}
                <div class="space-y-3">
                    <p class="text-xs font-semibold text-gray-600 dark:text-slate-400 uppercase tracking-wide">Penilaian (1 = Sangat Buruk, 5 = Luar Biasa)</p>
                    @foreach([
                        ['score_work_quality', 'Kualitas Kerja'],
                        ['score_productivity', 'Produktivitas'],
                        ['score_teamwork', 'Kerja Tim'],
                        ['score_initiative', 'Inisiatif'],
                        ['score_attendance', 'Kehadiran'],
                    ] as [$field, $label])
                    <div class="flex items-center gap-4">
                        <label class="w-36 text-sm text-gray-700 dark:text-slate-300 shrink-0">{{ $label }}</label>
                        <div class="flex gap-2">
                            @for($i = 1; $i <= 5; $i++)
                            <label class="cursor-pointer">
                                <input type="radio" name="{{ $field }}" value="{{ $i }}" {{ $i === 3 ? 'checked' : '' }} class="sr-only peer">
                                <span class="w-9 h-9 flex items-center justify-center rounded-xl border-2 border-gray-200 dark:border-white/10 text-sm font-semibold text-gray-500 dark:text-slate-400 peer-checked:border-blue-500 peer-checked:bg-blue-500 peer-checked:text-white transition cursor-pointer hover:border-blue-400">{{ $i }}</span>
                            </label>
                            @endfor
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Kelebihan</label>
                        <textarea name="strengths" rows="2" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white resize-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Area Perbaikan</label>
                        <textarea name="improvements" rows="2" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white resize-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Target Periode Berikutnya</label>
                        <textarea name="goals_next_period" rows="2" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white resize-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Rekomendasi</label>
                        <select name="recommendation" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                            <option value="">Tidak ada</option>
                            <option value="promote">Promosi</option>
                            <option value="retain">Pertahankan</option>
                            <option value="pip">PIP (Rencana Perbaikan)</option>
                            <option value="terminate">Pertimbangkan PHK</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-add-review').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan Penilaian</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    // Detail modal placeholder — bisa dikembangkan
    function openDetail(id) { /* future: show detail panel */ }
    </script>
    @endpush
</x-app-layout>
