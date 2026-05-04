<x-app-layout>
    <x-slot name="title">Penilaian Kinerja — Qalcuity ERP</x-slot>
    <x-slot name="header">Penilaian Kinerja</x-slot>
    <x-slot name="pageHeader">
        <button onclick="document.getElementById('modal-add-review').classList.remove('hidden')"
            class="flex items-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat Penilaian
        </button>
    </x-slot>

    {{-- Filter --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-4 mb-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <select name="employee_id" class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                <option value="">Semua Karyawan</option>
                @foreach($employees ?? [] as $emp)
                    <option value="{{ $emp->id }}" @selected(request('employee_id') == $emp->id)>{{ $emp->name }}</option>
                @endforeach
            </select>
            <select name="period_type" class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                <option value="">Semua Periode</option>
                <option value="monthly" @selected(request('period_type')==='monthly')>Bulanan</option>
                <option value="quarterly" @selected(request('period_type')==='quarterly')>Kuartalan</option>
                <option value="annual" @selected(request('period_type')==='annual')>Tahunan</option>
            </select>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
            <a href="{{ route('hrm.performance') }}" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Reset</a>
        </form>
    </div>

    {{-- AI Career Path Panel --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-5 mb-4">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-violet-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <p class="font-semibold text-gray-900 text-sm">AI Career Path Prediction</p>
            </div>
            <div class="flex items-center gap-2">
                <select id="career-emp-select" class="px-3 py-1.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-violet-500">
                    <option value="">Pilih karyawan...</option>
                    @foreach($employees ?? [] as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->name }} — {{ $emp->position ?? '-' }}</option>
                    @endforeach
                </select>
                <button onclick="loadCareerPath()" id="career-btn"
                    class="px-3 py-1.5 text-sm bg-violet-600 text-white rounded-xl hover:bg-violet-700 flex items-center gap-1.5 disabled:opacity-50">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    Analisis
                </button>
            </div>
        </div>

        {{-- Result area --}}
        <div id="career-result" class="hidden">
            {{-- Loading --}}
            <div id="career-loading" class="hidden py-6 text-center">
                <div class="inline-flex items-center gap-2 text-sm text-gray-500">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Menganalisis data kinerja...
                </div>
            </div>

            {{-- Content --}}
            <div id="career-content"></div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
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
                <tbody class="divide-y divide-gray-100">
                    @forelse($reviews as $review)
                    <tr class="hover:bg-gray-50 cursor-pointer" onclick="openDetail({{ $review->id }})">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900">{{ $review->employee?->name }}</p>
                            <p class="text-xs text-gray-500">{{ $review->employee?->department ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-700">
                            {{ $review->period }}
                            <span class="text-xs text-gray-400 ml-1">({{ match($review->period_type) { 'monthly' => 'Bulanan', 'quarterly' => 'Kuartalan', default => 'Tahunan' } }})</span>
                        </td>
                        <td class="px-4 py-3 hidden md:table-cell text-gray-500">{{ $review->reviewer?->name }}</td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $score = (float) $review->overall_score;
                                $color = $score >= 4 ? 'text-green-600' : ($score >= 3 ? 'text-blue-600' : 'text-red-600');
                            @endphp
                            <span class="font-bold text-lg {{ $color }}">{{ number_format($score, 1) }}</span>
                            <span class="text-xs text-gray-400">/5</span>
                        </td>
                        <td class="px-4 py-3 text-center hidden lg:table-cell">
                            @if($review->recommendation)
                            @php
                                $recColor = match($review->recommendation) {
                                    'promote'   => 'bg-green-100 text-green-700',
                                    'pip'       => 'bg-amber-100 text-amber-700',
                                    'terminate' => 'bg-red-100 text-red-700',
                                    default     => 'bg-blue-100 text-blue-700',
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
                                    'acknowledged' => 'bg-green-100 text-green-700',
                                    'submitted'    => 'bg-blue-100 text-blue-700',
                                    default        => 'bg-gray-100 text-gray-500',
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
                                    <button type="submit" class="p-1.5 rounded-lg text-red-500 hover:bg-red-50">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">Belum ada penilaian kinerja.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reviews->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $reviews->links() }}</div>
        @endif
    </div>

    {{-- Modal Buat Penilaian --}}
    <div id="modal-add-review" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white">
                <h3 class="font-semibold text-gray-900">Buat Penilaian Kinerja</h3>
                <button onclick="document.getElementById('modal-add-review').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="{{ route('hrm.performance.store') }}" class="p-6 space-y-5">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Karyawan *</label>
                        <select name="employee_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                            <option value="">Pilih...</option>
                            @foreach($employees ?? [] as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Penilai *</label>
                        <select name="reviewer_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                            <option value="">Pilih...</option>
                            @foreach($employees ?? [] as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Periode *</label>
                        <input type="text" name="period" placeholder="cth: Q1 2026 / 2026-01" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tipe Periode *</label>
                        <select name="period_type" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                            <option value="quarterly">Kuartalan</option>
                            <option value="monthly">Bulanan</option>
                            <option value="annual">Tahunan</option>
                        </select>
                    </div>
                </div>

                {{-- Score sliders --}}
                <div class="space-y-3">
                    <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Penilaian (1 = Sangat Buruk, 5 = Luar Biasa)</p>
                    @foreach([
                        ['score_work_quality', 'Kualitas Kerja'],
                        ['score_productivity', 'Produktivitas'],
                        ['score_teamwork', 'Kerja Tim'],
                        ['score_initiative', 'Inisiatif'],
                        ['score_attendance', 'Kehadiran'],
                    ] as [$field, $label])
                    <div class="flex items-center gap-4">
                        <label class="w-36 text-sm text-gray-700 shrink-0">{{ $label }}</label>
                        <div class="flex gap-2">
                            @for($i = 1; $i <= 5; $i++)
                            <label class="cursor-pointer">
                                <input type="radio" name="{{ $field }}" value="{{ $i }}" {{ $i === 3 ? 'checked' : '' }} class="sr-only peer">
                                <span class="w-9 h-9 flex items-center justify-center rounded-xl border-2 border-gray-200 text-sm font-semibold text-gray-500 peer-checked:border-blue-500 peer-checked:bg-blue-500 peer-checked:text-white transition cursor-pointer hover:border-blue-400">{{ $i }}</span>
                            </label>
                            @endfor
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kelebihan</label>
                        <textarea name="strengths" rows="2" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 resize-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Area Perbaikan</label>
                        <textarea name="improvements" rows="2" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 resize-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Target Periode Berikutnya</label>
                        <textarea name="goals_next_period" rows="2" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 resize-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Rekomendasi</label>
                        <select name="recommendation" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                            <option value="">Tidak ada</option>
                            <option value="promote">Promosi</option>
                            <option value="retain">Pertahankan</option>
                            <option value="pip">PIP (Rencana Perbaikan)</option>
                            <option value="terminate">Pertimbangkan PHK</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-add-review').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan Penilaian</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    // Detail modal placeholder — bisa dikembangkan
    function openDetail(id) { /* future: show detail panel */ }

    // ── AI Career Path ──────────────────────────────────────────
    async function loadCareerPath() {
        const empId = document.getElementById('career-emp-select').value;
        if (!empId) { alert('Pilih karyawan terlebih dahulu.'); return; }

        const btn = document.getElementById('career-btn');
        btn.disabled = true;
        document.getElementById('career-result').classList.remove('hidden');
        document.getElementById('career-loading').classList.remove('hidden');
        document.getElementById('career-content').innerHTML = '';

        try {
            const res  = await fetch('{{ url("hrm/ai/career-path") }}/' + empId);
            const data = await res.json();
            document.getElementById('career-loading').classList.add('hidden');
            document.getElementById('career-content').innerHTML = renderCareerPath(data);
        } catch(e) {
            document.getElementById('career-loading').classList.add('hidden');
            document.getElementById('career-content').innerHTML = `<p class="text-sm text-red-500">Gagal memuat prediksi. Coba lagi.</p>`;
        } finally {
            btn.disabled = false;
        }
    }

    function renderCareerPath(d) {
        const colorMap = {
            green:  { bg: 'bg-green-100', text: 'text-green-700', ring: 'ring-green-500', bar: 'bg-green-500' },
            blue:   { bg: 'bg-blue-100',   text: 'text-blue-700',   ring: 'ring-blue-500',  bar: 'bg-blue-500' },
            amber:  { bg: 'bg-amber-100', text: 'text-amber-700', ring: 'ring-amber-500', bar: 'bg-amber-500' },
            orange: { bg: 'bg-orange-100',text:'text-orange-700',ring:'ring-orange-500',bar:'bg-orange-500' },
            red:    { bg: 'bg-red-100',     text: 'text-red-700',     ring: 'ring-red-500',   bar: 'bg-red-500' },
        };
        const c = colorMap[d.readiness_color] || colorMap.blue;
        const trendIcon = d.trend === 'improving' ? '↑' : d.trend === 'declining' ? '↓' : '→';
        const trendColor = d.trend === 'improving' ? 'text-green-500' : d.trend === 'declining' ? 'text-red-500' : 'text-gray-400';

        // Data quality warning
        const dqWarn = d.data_quality !== 'good'
            ? `<div class="mb-4 px-3 py-2 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-700">
                ⚠ Data ${d.data_quality === 'insufficient' ? 'tidak mencukupi' : 'terbatas'} (${d.review_count} review). Prediksi mungkin kurang akurat — tambahkan lebih banyak data penilaian kinerja.
               </div>` : '';

        // Readiness gauge
        const gauge = `
        <div class="flex flex-col sm:flex-row gap-5 mb-5">
            <div class="flex flex-col items-center justify-center ${c.bg} rounded-2xl p-5 min-w-[140px]">
                <div class="relative w-24 h-24 mb-2">
                    <svg class="w-24 h-24 -rotate-90" viewBox="0 0 36 36">
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="currentColor" stroke-width="2.5" class="text-gray-200"/>
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="currentColor" stroke-width="2.5"
                            stroke-dasharray="${d.readiness_score} ${100 - d.readiness_score}"
                            stroke-linecap="round"
                            class="${c.text}"/>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-2xl font-black ${c.text}">${d.readiness_score}</span>
                        <span class="text-xs ${c.text} opacity-70">/ 100</span>
                    </div>
                </div>
                <span class="text-sm font-bold ${c.text}">${d.readiness_label}</span>
            </div>
            <div class="flex-1 space-y-3">
                <div>
                    <p class="text-xs text-gray-500 mb-0.5">Karyawan</p>
                    <p class="font-semibold text-gray-900">${d.employee.name}</p>
                    <p class="text-xs text-gray-500">${d.employee.position} · ${d.employee.department} · ${d.employee.tenure_label}</p>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-xs text-gray-400">Rata-rata Skor</p>
                        <p class="text-lg font-bold text-gray-900">${d.avg_score !== null ? parseFloat(d.avg_score).toFixed(1) : '—'}<span class="text-xs text-gray-400">/5</span></p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-xs text-gray-400">Tren Kinerja</p>
                        <p class="text-lg font-bold ${trendColor}">${trendIcon} ${d.trend_label}</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-xs text-gray-400">Estimasi Promosi</p>
                        <p class="text-sm font-semibold text-gray-900 leading-tight">${d.promotion_eta}</p>
                    </div>
                </div>
            </div>
        </div>`;

        // Suggested roles
        const fitColor = { high: 'bg-green-100 text-green-700', medium: 'bg-blue-100 text-blue-700', low: 'bg-gray-100 text-gray-500' };
        const roles = d.suggested_roles.map(r => `
            <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-xl">
                <svg class="w-4 h-4 text-violet-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="text-sm font-medium text-gray-900">${r.title}</p>
                        <span class="text-xs px-1.5 py-0.5 rounded-full ${fitColor[r.fit] || fitColor.medium}">${r.fit === 'high' ? 'Cocok' : r.fit === 'medium' ? 'Potensial' : 'Jangka Panjang'}</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5">${r.note}</p>
                </div>
            </div>`).join('');

        // Factors
        const posFactors = d.factors.positive.map(f => `<li class="flex items-start gap-1.5 text-xs text-gray-700"><span class="text-green-500 shrink-0 mt-0.5">✓</span>${f}</li>`).join('');
        const negFactors = d.factors.negative.map(f => `<li class="flex items-start gap-1.5 text-xs text-gray-700"><span class="text-red-500 shrink-0 mt-0.5">✗</span>${f}</li>`).join('');

        // Action plan
        const prioColor = { high: 'bg-red-100 text-red-700', medium: 'bg-amber-100 text-amber-700', low: 'bg-gray-100 text-gray-500' };
        const prioLabel = { high: 'Prioritas', medium: 'Disarankan', low: 'Opsional' };
        const actions = d.action_plan.map(a => `
            <div class="flex items-start gap-2.5">
                <span class="text-xs px-1.5 py-0.5 rounded-full shrink-0 mt-0.5 ${prioColor[a.priority]}">${prioLabel[a.priority]}</span>
                <p class="text-xs text-gray-700">${a.action}</p>
            </div>`).join('');

        return `
        ${dqWarn}
        ${gauge}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="space-y-2">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Jalur Karir yang Disarankan</p>
                ${roles}
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Faktor Penilaian</p>
                <div class="space-y-3">
                    ${posFactors ? `<ul class="space-y-1.5">${posFactors}</ul>` : ''}
                    ${negFactors ? `<ul class="space-y-1.5 mt-2">${negFactors}</ul>` : ''}
                    ${!posFactors && !negFactors ? '<p class="text-xs text-gray-400">Tidak cukup data untuk analisis faktor.</p>' : ''}
                </div>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Rencana Tindak Lanjut</p>
                <div class="space-y-2">${actions}</div>
            </div>
        </div>`;
    }
    </script>
    @endpush
</x-app-layout>
