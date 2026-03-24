<x-app-layout>
    <x-slot name="header">Detail Surat Peringatan</x-slot>

    <div class="flex flex-col lg:flex-row gap-5">

        {{-- ── Sidebar: actions + history ─────────────────────── --}}
        <div class="lg:w-64 shrink-0 space-y-4">

            {{-- Actions --}}
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4 space-y-2">
                <button onclick="window.print()"
                    class="w-full py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak / PDF
                </button>

                @if($letter->status === 'issued')
                <form method="POST" action="{{ route('hrm.disciplinary.acknowledge', $letter) }}">
                    @csrf @method('PATCH')
                    <div class="mb-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tanggapan Karyawan</label>
                        <textarea name="employee_response" rows="2" placeholder="Opsional..."
                            class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                    </div>
                    <button type="submit" class="w-full py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">
                        ✓ Konfirmasi Penerimaan
                    </button>
                </form>
                @endif

                @if(in_array($letter->status, ['issued','acknowledged']))
                <form method="POST" action="{{ route('hrm.disciplinary.expire', $letter) }}">
                    @csrf @method('PATCH')
                    <button type="submit" onclick="return confirm('Tandai SP ini sebagai expired?')"
                        class="w-full py-2 text-sm border border-gray-200 dark:border-white/10 text-gray-600 dark:text-slate-300 rounded-xl hover:bg-gray-50 dark:hover:bg-white/5">
                        Tandai Expired
                    </button>
                </form>
                @endif

                <a href="{{ route('hrm.disciplinary.index') }}"
                    class="block text-center py-2 text-sm border border-gray-200 dark:border-white/10 text-gray-600 dark:text-slate-300 rounded-xl hover:bg-gray-50 dark:hover:bg-white/5">
                    ← Kembali
                </a>
            </div>

            {{-- Riwayat SP karyawan --}}
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                <p class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wide mb-3">Riwayat SP — {{ $letter->employee->name }}</p>
                <div class="space-y-2">
                    @foreach($history as $h)
                    <a href="{{ route('hrm.disciplinary.show', $h) }}"
                        class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 {{ $h->id === $letter->id ? 'bg-blue-50 dark:bg-blue-500/10' : '' }}">
                        <span class="px-1.5 py-0.5 rounded text-xs font-bold {{ $h->levelColor() }}">{{ $h->levelLabel() }}</span>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-700 dark:text-slate-300 truncate">{{ $h->violation_type }}</p>
                            <p class="text-xs text-gray-400 dark:text-slate-500">{{ $h->issued_date->format('d M Y') }}</p>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ── Surat Peringatan (printable) ────────────────────── --}}
        <div class="flex-1 min-w-0">
            <div id="print-area" class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-8 print:shadow-none print:border-none print:rounded-none print:p-6">

                {{-- Kop surat --}}
                <div class="text-center border-b-2 border-gray-800 dark:border-white/30 pb-4 mb-6 print:border-gray-800">
                    <p class="text-lg font-bold text-gray-900 dark:text-white uppercase tracking-wide">SURAT PERINGATAN</p>
                    <p class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $letter->levelLabel() }}</p>
                    <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">No: {{ $letter->letter_number }}</p>
                </div>

                {{-- Pembuka --}}
                <div class="mb-6 text-sm text-gray-700 dark:text-slate-300 space-y-1">
                    <p>Yang bertanda tangan di bawah ini:</p>
                    <div class="ml-4 space-y-1">
                        <div class="flex gap-2"><span class="w-32 shrink-0">Nama</span><span>: {{ $letter->issuer->name ?? '-' }}</span></div>
                        <div class="flex gap-2"><span class="w-32 shrink-0">Jabatan</span><span>: {{ $letter->issuer?->role ? ucfirst($letter->issuer->role) : 'HRD' }}</span></div>
                    </div>
                    <p class="mt-2">Dengan ini memberikan Surat Peringatan kepada:</p>
                    <div class="ml-4 space-y-1">
                        <div class="flex gap-2"><span class="w-32 shrink-0">Nama</span><span>: {{ $letter->employee->name ?? '-' }}</span></div>
                        <div class="flex gap-2"><span class="w-32 shrink-0">Jabatan</span><span>: {{ $letter->employee->position ?? '-' }}</span></div>
                        <div class="flex gap-2"><span class="w-32 shrink-0">Departemen</span><span>: {{ $letter->employee->department ?? '-' }}</span></div>
                    </div>
                </div>

                {{-- Isi surat --}}
                <div class="space-y-4 text-sm text-gray-700 dark:text-slate-300">
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white mb-1">Jenis Pelanggaran:</p>
                        <p class="ml-4">{{ $letter->violation_type }}</p>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white mb-1">Uraian Pelanggaran:</p>
                        <p class="ml-4 whitespace-pre-line">{{ $letter->violation_description }}</p>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white mb-1">Tindakan Perbaikan yang Diminta:</p>
                        <p class="ml-4 whitespace-pre-line">{{ $letter->corrective_action }}</p>
                    </div>
                    @if($letter->consequences)
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white mb-1">Konsekuensi:</p>
                        <p class="ml-4 whitespace-pre-line">{{ $letter->consequences }}</p>
                    </div>
                    @endif
                    @if($letter->valid_until)
                    <p>Surat peringatan ini berlaku hingga <strong>{{ $letter->valid_until->format('d F Y') }}</strong>.</p>
                    @endif
                </div>

                {{-- Tanggapan karyawan --}}
                @if($letter->employee_response)
                <div class="mt-6 p-4 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10">
                    <p class="text-xs font-semibold text-gray-500 dark:text-slate-400 mb-1">Tanggapan Karyawan:</p>
                    <p class="text-sm text-gray-700 dark:text-slate-300 whitespace-pre-line">{{ $letter->employee_response }}</p>
                </div>
                @endif

                {{-- Tanda tangan --}}
                <div class="mt-10 grid grid-cols-3 gap-6 text-sm text-center text-gray-700 dark:text-slate-300">
                    <div>
                        <p>{{ $letter->issued_date->format('d F Y') }}</p>
                        <div class="h-16 border-b border-gray-400 dark:border-white/30 mt-2 mb-1"></div>
                        <p class="font-semibold">{{ $letter->issuer->name ?? 'HRD' }}</p>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Yang Menerbitkan</p>
                    </div>
                    @if($letter->witness)
                    <div>
                        <p>&nbsp;</p>
                        <div class="h-16 border-b border-gray-400 dark:border-white/30 mt-2 mb-1"></div>
                        <p class="font-semibold">{{ $letter->witness->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Saksi</p>
                    </div>
                    @else
                    <div></div>
                    @endif
                    <div>
                        @if($letter->acknowledged_at)
                        <p>{{ $letter->acknowledged_at->format('d F Y') }}</p>
                        @else
                        <p>&nbsp;</p>
                        @endif
                        <div class="h-16 border-b border-gray-400 dark:border-white/30 mt-2 mb-1"></div>
                        <p class="font-semibold">{{ $letter->employee->name ?? '-' }}</p>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Yang Menerima</p>
                    </div>
                </div>

                {{-- AI badge --}}
                @if($letter->source === 'ai_anomaly')
                <div class="mt-4 flex items-center gap-1.5 text-xs text-purple-400 print:hidden">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Draft dibuat oleh AI berdasarkan anomali absensi
                </div>
                @endif
            </div>
        </div>
    </div>

    @push('head')
    <style>
    @media print {
        body > * { display: none !important; }
        #print-area { display: block !important; position: fixed; top: 0; left: 0; width: 100%; }
        .dark #print-area { background: white !important; color: black !important; }
        .dark #print-area * { color: black !important; border-color: #333 !important; }
    }
    </style>
    @endpush
</x-app-layout>
