@extends('layouts.app')
@section('title', 'Kunci Periode & Backup — Qalcuity ERP')
@section('header', 'Kunci Periode & Backup Data')

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 rounded-xl bg-green-500/10 border border-green-500/30 text-green-400 text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm">{{ session('error') }}</div>
@endif

{{-- Info Banner --}}
<div class="mb-6 bg-blue-500/10 border border-blue-500/20 rounded-2xl p-4 flex gap-3">
    <svg class="w-5 h-5 text-blue-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <div class="text-sm text-blue-300/90">
        <p class="font-semibold text-blue-300 mb-1">Cara kerja Period Lock</p>
        <ul class="space-y-0.5 text-blue-300/80 list-disc list-inside">
            <li><span class="font-medium">Closed</span> — periode ditutup, tidak bisa posting jurnal baru. Masih bisa dibuka kembali oleh admin.</li>
            <li><span class="font-medium">Locked</span> — dikunci permanen. Tidak ada yang bisa membuat, mengubah, atau menghapus data transaksi pada periode ini.</li>
            <li>Lock Tahun Fiskal otomatis mengunci semua periode bulanan di dalamnya.</li>
            <li>Backup menghasilkan file JSON yang bisa diunduh kapan saja sebagai arsip.</li>
        </ul>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

    {{-- ── Fiscal Years ── --}}
    <div class="space-y-4">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Tahun Fiskal</p>
                <button onclick="document.getElementById('modal-fy').classList.remove('hidden')"
                    class="text-xs px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium transition">+ Tambah</button>
            </div>

            @if($fiscalYears->isEmpty())
            <p class="text-sm text-gray-400 dark:text-slate-500 py-4 text-center">Belum ada tahun fiskal. Buat sekarang untuk mulai mengelola periode.</p>
            @else
            <div class="space-y-3">
                @foreach($fiscalYears as $fy)
                @php
                $statusColor = match($fy->status) {
                    'open'   => 'bg-green-500/20 text-green-400',
                    'closed' => 'bg-amber-500/20 text-amber-400',
                    'locked' => 'bg-red-500/20 text-red-400',
                };
                $statusLabel = match($fy->status) { 'open'=>'Terbuka','closed'=>'Ditutup','locked'=>'Dikunci', default=>$fy->status };
                @endphp
                <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10">
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $fy->name }}</p>
                        <p class="text-xs text-gray-400 dark:text-slate-500">{{ $fy->start_date->format('d M Y') }} — {{ $fy->end_date->format('d M Y') }}</p>
                        @if($fy->lockedBy)
                        <p class="text-xs text-red-400 mt-0.5">Dikunci oleh {{ $fy->lockedBy->name }} · {{ $fy->locked_at->format('d M Y H:i') }}</p>
                        @elseif($fy->closedBy)
                        <p class="text-xs text-amber-400 mt-0.5">Ditutup oleh {{ $fy->closedBy->name }} · {{ $fy->closed_at->format('d M Y H:i') }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $statusColor }}">{{ $statusLabel }}</span>
                        @if($fy->isOpen())
                        <form method="POST" action="{{ route('accounting.period-lock.fiscal-years.close', $fy) }}" class="inline"
                              onsubmit="return confirm('Tutup tahun fiskal {{ $fy->name }}? Semua periode di dalamnya akan ikut ditutup.')">
                            @csrf @method('PATCH')
                            <button class="text-xs px-2.5 py-1 rounded-lg bg-amber-500/20 hover:bg-amber-500/30 text-amber-400 font-medium transition">Tutup</button>
                        </form>
                        <form method="POST" action="{{ route('accounting.period-lock.fiscal-years.lock', $fy) }}" class="inline"
                              onsubmit="return confirm('KUNCI PERMANEN tahun fiskal {{ $fy->name }}? Tindakan ini tidak dapat dibatalkan!')">
                            @csrf @method('PATCH')
                            <button class="text-xs px-2.5 py-1 rounded-lg bg-red-500/20 hover:bg-red-500/30 text-red-400 font-medium transition">Kunci</button>
                        </form>
                        @elseif($fy->isClosed())
                        <form method="POST" action="{{ route('accounting.period-lock.fiscal-years.lock', $fy) }}" class="inline"
                              onsubmit="return confirm('KUNCI PERMANEN tahun fiskal {{ $fy->name }}? Tindakan ini tidak dapat dibatalkan!')">
                            @csrf @method('PATCH')
                            <button class="text-xs px-2.5 py-1 rounded-lg bg-red-500/20 hover:bg-red-500/30 text-red-400 font-medium transition">Kunci</button>
                        </form>
                        <form method="POST" action="{{ route('accounting.period-lock.fiscal-years.reopen', $fy) }}" class="inline"
                              onsubmit="return confirm('Buka kembali tahun fiskal {{ $fy->name }}?')">
                            @csrf @method('PATCH')
                            <button class="text-xs px-2.5 py-1 rounded-lg bg-green-500/20 hover:bg-green-500/30 text-green-400 font-medium transition">Buka</button>
                        </form>
                        @else
                        <span class="text-xs text-red-400 font-medium">🔒 Permanen</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- ── Accounting Periods ── --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <p class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Periode Bulanan</p>
            @if($periods->isEmpty())
            <p class="text-sm text-gray-400 dark:text-slate-500 py-4 text-center">Belum ada periode. Buat Tahun Fiskal dengan opsi "Auto-generate periode bulanan".</p>
            @else
            <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-white/10">
                        <th class="pb-2 text-left text-xs font-semibold text-gray-500 dark:text-slate-400">Periode</th>
                        <th class="pb-2 text-left text-xs font-semibold text-gray-500 dark:text-slate-400">Rentang</th>
                        <th class="pb-2 text-left text-xs font-semibold text-gray-500 dark:text-slate-400">Status</th>
                        <th class="pb-2 text-right text-xs font-semibold text-gray-500 dark:text-slate-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @foreach($periods as $period)
                    @php
                    $pColor = match($period->status) {
                        'open'   => 'bg-green-500/20 text-green-400',
                        'closed' => 'bg-amber-500/20 text-amber-400',
                        'locked' => 'bg-red-500/20 text-red-400',
                    };
                    @endphp
                    <tr>
                        <td class="py-2.5 font-medium text-gray-900 dark:text-white">{{ $period->name }}</td>
                        <td class="py-2.5 text-gray-500 dark:text-slate-400 text-xs">{{ $period->start_date->format('d M') }} — {{ $period->end_date->format('d M Y') }}</td>
                        <td class="py-2.5">
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $pColor }}">
                                {{ match($period->status) { 'open'=>'Terbuka','closed'=>'Ditutup','locked'=>'Dikunci', default=>$period->status } }}
                            </span>
                        </td>
                        <td class="py-2.5 text-right">
                            @if($period->isOpen())
                            <form method="POST" action="{{ route('accounting.period-lock.periods.lock', $period) }}" class="inline"
                                  onsubmit="return confirm('Kunci periode {{ $period->name }}?')">
                                @csrf @method('PATCH')
                                <button class="text-xs px-2 py-1 rounded-lg bg-red-500/20 hover:bg-red-500/30 text-red-400 font-medium transition">Kunci</button>
                            </form>
                            @elseif($period->isLocked())
                            <span class="text-xs text-red-400">🔒</span>
                            @else
                            <span class="text-xs text-amber-400">Ditutup</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            @endif
        </div>
    </div>

    {{-- ── Backup ── --}}
    <div class="space-y-4">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <p class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Buat Backup Manual</p>
            <form method="POST" action="{{ route('accounting.period-lock.backups.store') }}" class="space-y-3">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Tipe Backup</label>
                        <select name="type" id="backup-type" onchange="updateBackupLabel()"
                            class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-slate-800 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="monthly">Bulanan</option>
                            <option value="yearly">Tahunan</option>
                            <option value="manual">Manual / Custom</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Label</label>
                        <input type="text" name="label" id="backup-label" value="{{ now()->subMonth()->translatedFormat('F Y') }}"
                            class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Dari Tanggal</label>
                        <input type="date" name="period_start" id="backup-start" value="{{ now()->subMonth()->startOfMonth()->toDateString() }}"
                            class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Sampai Tanggal</label>
                        <input type="date" name="period_end" id="backup-end" value="{{ now()->subMonth()->endOfMonth()->toDateString() }}"
                            class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                </div>
                <button type="submit" class="w-full py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition">
                    Buat Backup Sekarang
                </button>
            </form>
            <p class="text-xs text-gray-400 dark:text-slate-500 mt-3">Backup otomatis: bulanan (tgl 2 setiap bulan) dan tahunan (2 Januari) berjalan otomatis via scheduler.</p>
        </div>

        {{-- Backup List --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <p class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Riwayat Backup</p>
            @if($backups->isEmpty())
            <p class="text-sm text-gray-400 dark:text-slate-500 py-4 text-center">Belum ada backup.</p>
            @else
            <div class="space-y-2">
                @foreach($backups as $backup)
                @php
                $bColor = match($backup->status) {
                    'completed'  => 'bg-green-500/20 text-green-400',
                    'failed'     => 'bg-red-500/20 text-red-400',
                    'processing' => 'bg-blue-500/20 text-blue-400',
                    default      => 'bg-gray-100 dark:bg-white/10 text-gray-500 dark:text-slate-400',
                };
                $typeLabel = match($backup->type) { 'monthly'=>'Bulanan','yearly'=>'Tahunan','manual'=>'Manual', default=>$backup->type };
                @endphp
                <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $backup->label }}</p>
                            <span class="text-xs px-1.5 py-0.5 rounded bg-gray-100 dark:bg-white/10 text-gray-500 dark:text-slate-400">{{ $typeLabel }}</span>
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $bColor }}">{{ ucfirst($backup->status) }}</span>
                        </div>
                        <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5">
                            {{ $backup->period_start->format('d M Y') }} — {{ $backup->period_end->format('d M Y') }}
                            @if($backup->isCompleted())
                            · {{ $backup->fileSizeHuman() }}
                            @if($backup->summary)
                            · {{ collect($backup->summary)->sum() }} records
                            @endif
                            @endif
                        </p>
                        @if($backup->isFailed())
                        <p class="text-xs text-red-400 mt-0.5">{{ $backup->error_message }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-1 ml-3 shrink-0">
                        @if($backup->isCompleted())
                        <a href="{{ route('accounting.period-lock.backups.download', $backup) }}"
                           class="p-1.5 rounded-lg text-gray-500 dark:text-slate-400 hover:text-blue-400 hover:bg-blue-500/10 transition" title="Unduh">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        </a>
                        @endif
                        <form method="POST" action="{{ route('accounting.period-lock.backups.destroy', $backup) }}" class="inline"
                              onsubmit="return confirm('Hapus backup ini?')">
                            @csrf @method('DELETE')
                            <button class="p-1.5 rounded-lg text-gray-500 dark:text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition" title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal: Tambah Fiscal Year --}}
<div id="modal-fy" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-5">
            <p class="text-base font-semibold text-gray-900 dark:text-white">Tambah Tahun Fiskal</p>
            <button onclick="document.getElementById('modal-fy').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('accounting.period-lock.fiscal-years.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Nama Tahun Fiskal</label>
                <input type="text" name="name" placeholder="e.g. 2025 atau 2025/2026" value="{{ now()->year }}"
                    class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ now()->startOfYear()->toDateString() }}"
                        class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Tanggal Selesai</label>
                    <input type="date" name="end_date" value="{{ now()->endOfYear()->toDateString() }}"
                        class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
            </div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="auto_periods" value="1" checked class="rounded border-gray-300 dark:border-white/20 text-blue-600 focus:ring-blue-500">
                <span class="text-sm text-gray-700 dark:text-slate-300">Auto-generate periode bulanan</span>
            </label>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal-fy').classList.add('hidden')"
                    class="flex-1 py-2.5 rounded-xl border border-gray-200 dark:border-white/10 text-sm font-medium text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5 transition">Batal</button>
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition">Buat</button>
            </div>
        </form>
    </div>
</div>

<script>
function updateBackupLabel() {
    const type  = document.getElementById('backup-type').value;
    const label = document.getElementById('backup-label');
    const start = document.getElementById('backup-start');
    const end   = document.getElementById('backup-end');
    const now   = new Date();
    const prev  = new Date(now.getFullYear(), now.getMonth() - 1, 1);

    if (type === 'monthly') {
        const m = prev.toLocaleString('id-ID', { month: 'long', year: 'numeric' });
        label.value = m.charAt(0).toUpperCase() + m.slice(1);
        start.value = new Date(prev.getFullYear(), prev.getMonth(), 1).toISOString().split('T')[0];
        end.value   = new Date(prev.getFullYear(), prev.getMonth() + 1, 0).toISOString().split('T')[0];
    } else if (type === 'yearly') {
        const yr = now.getFullYear() - 1;
        label.value = 'Tahun ' + yr;
        start.value = yr + '-01-01';
        end.value   = yr + '-12-31';
    }
}
</script>
@endsection
