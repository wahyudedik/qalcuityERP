<x-app-layout>
    <x-slot name="header">Manajemen Lembur</x-slot>

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Menunggu Persetujuan</p>
            <p class="text-2xl font-bold text-amber-500 mt-1">{{ $summary['pending'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Disetujui Bulan Ini</p>
            <p class="text-2xl font-bold text-green-500 mt-1">{{ $summary['approved'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">Upah Lembur Belum Dibayar</p>
            <p class="text-xl font-bold text-blue-500 mt-1">Rp {{ number_format($summary['total_pay'], 0, ',', '.') }}
            </p>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-5">

        {{-- ── Sidebar: Form Pengajuan ──────────────────────────── --}}
        <div class="lg:w-72 shrink-0 space-y-4">
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4 text-sm">Ajukan Lembur</h3>
                <form method="POST" action="{{ route('hrm.overtime.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Karyawan
                            *</label>
                        <select name="employee_id" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih karyawan...</option>
                            @foreach ($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanggal
                            *</label>
                        <input type="date" name="date" required value="{{ today()->format('Y-m-d') }}"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Mulai
                                *</label>
                            <input type="time" name="start_time" required value="17:00"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Selesai
                                *</label>
                            <input type="time" name="end_time" required value="20:00"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Alasan</label>
                        <textarea name="reason" rows="2" placeholder="Keterangan pekerjaan lembur..."
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                    </div>
                    <button type="submit"
                        class="w-full py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                        Ajukan Lembur
                    </button>
                </form>
            </div>

            {{-- Info kalkulasi --}}
            <div class="bg-blue-500/10 border border-blue-500/20 rounded-2xl p-4 text-xs text-blue-300 space-y-1.5">
                <p class="font-semibold text-blue-200">Perhitungan Upah Lembur</p>
                <p>Berdasarkan Permenaker No.102/2004:</p>
                <p>• Jam ke-1: 1,5× upah/jam</p>
                <p>• Jam ke-2 dst: 2× upah/jam</p>
                <p>• Upah/jam = Gaji Pokok ÷ 173</p>
            </div>
        </div>

        {{-- ── Main: Daftar Pengajuan ───────────────────────────── --}}
        <div class="flex-1 min-w-0">

            {{-- Filter --}}
            <form method="GET" class="flex flex-wrap items-center gap-2 mb-4">
                <input type="month" name="month" value="{{ $month }}"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="status"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all" @selected($status === 'all')>Semua Status</option>
                    <option value="pending" @selected($status === 'pending')>Menunggu</option>
                    <option value="approved" @selected($status === 'approved')>Disetujui</option>
                    <option value="rejected" @selected($status === 'rejected')>Ditolak</option>
                </select>
                <button type="submit"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
            </form>

            {{-- Table --}}
            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Karyawan</th>
                                <th class="px-4 py-3 text-left">Tanggal</th>
                                <th class="px-4 py-3 text-center">Waktu</th>
                                <th class="px-4 py-3 text-center">Durasi</th>
                                <th class="px-4 py-3 text-right hidden sm:table-cell">Upah</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @forelse($requests as $ot)
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ $ot->employee->name ?? '-' }}</p>
                                        <p class="text-xs text-gray-400 dark:text-slate-500">
                                            {{ $ot->employee->department ?? ($ot->employee->position ?? '') }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-slate-300 whitespace-nowrap">
                                        {{ $ot->date->format('d M Y') }}
                                    </td>
                                    <td
                                        class="px-4 py-3 text-center text-gray-600 dark:text-slate-400 whitespace-nowrap text-xs">
                                        {{ $ot->start_time }} – {{ $ot->end_time }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span
                                            class="text-sm font-medium text-gray-900 dark:text-white">{{ $ot->durationLabel() }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right hidden sm:table-cell">
                                        @if ($ot->status === 'approved')
                                            <span class="text-green-600 dark:text-green-400 font-medium">Rp
                                                {{ number_format($ot->overtime_pay, 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-gray-400 dark:text-slate-500 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($ot->status === 'pending')
                                            <span
                                                class="px-2 py-0.5 rounded-full text-xs bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400">Menunggu</span>
                                        @elseif($ot->status === 'approved')
                                            <div>
                                                <span
                                                    class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400">Disetujui</span>
                                                @if ($ot->included_in_payroll)
                                                    <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5">Payroll
                                                        {{ $ot->payroll_period }}</p>
                                                @else
                                                    <p class="text-xs text-blue-400 mt-0.5">Belum dibayar</p>
                                                @endif
                                            </div>
                                        @else
                                            <div>
                                                <span
                                                    class="px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400">Ditolak</span>
                                                @if ($ot->rejection_reason)
                                                    <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5 max-w-[120px] truncate"
                                                        title="{{ $ot->rejection_reason }}">
                                                        {{ $ot->rejection_reason }}</p>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($ot->status === 'pending')
                                            <div class="flex items-center justify-center gap-1">
                                                <form method="POST" action="{{ route('hrm.overtime.approve', $ot) }}">
                                                    @csrf @method('PATCH')
                                                    <button type="submit"
                                                        class="px-2.5 py-1 text-xs bg-green-600 text-white rounded-lg hover:bg-green-700">Setuju</button>
                                                </form>
                                                <button onclick="openReject({{ $ot->id }})"
                                                    class="px-2.5 py-1 text-xs border border-red-500/30 text-red-400 rounded-lg hover:bg-red-500/10">✕
                                                    Tolak</button>
                                            </div>
                                        @elseif($ot->status === 'pending')
                                            <form method="POST" action="{{ route('hrm.overtime.destroy', $ot) }}">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    onclick="return confirm('Hapus pengajuan ini?')"
                                                    class="text-xs text-gray-400 hover:text-red-400">Hapus</button>
                                            </form>
                                        @else
                                            <span class="text-xs text-gray-300 dark:text-slate-600">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @if ($ot->reason && $ot->status !== 'rejected')
                                    <tr class="bg-gray-50/50 dark:bg-white/[0.02]">
                                        <td colspan="7"
                                            class="px-4 py-1.5 text-xs text-gray-400 dark:text-slate-500 italic">
                                            Alasan: {{ $ot->reason }}
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="7"
                                        class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">
                                        Tidak ada pengajuan lembur untuk filter ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($requests->hasPages())
                    <div class="px-4 py-3 border-t border-gray-100 dark:border-white/10">
                        {{ $requests->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal: Tolak Lembur --}}
    <div id="modal-reject"
        class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div
            class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 w-full max-w-sm shadow-2xl">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-white/10">
                <p class="font-semibold text-gray-900 dark:text-white text-sm">Tolak Pengajuan Lembur</p>
                <button onclick="document.getElementById('modal-reject').classList.add('hidden')"
                    class="text-gray-400 hover:text-white">✕</button>
            </div>
            <form id="form-reject" method="POST" class="p-5 space-y-4">
                @csrf @method('PATCH')
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Alasan Penolakan
                        (opsional)</label>
                    <textarea name="rejection_reason" rows="3" placeholder="Masukkan alasan penolakan..."
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-reject').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-red-600 text-white rounded-xl hover:bg-red-700">Tolak</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            const REJECT_BASE = '{{ url('hrm/overtime') }}';

            function openReject(id) {
                document.getElementById('form-reject').action = `${REJECT_BASE}/${id}/reject`;
                document.getElementById('modal-reject').classList.remove('hidden');
            }

            document.getElementById('modal-reject').addEventListener('click', function(e) {
                if (e.target === this) this.classList.add('hidden');
            });
        </script>
    @endpush
</x-app-layout>
