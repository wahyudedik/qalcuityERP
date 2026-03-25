<x-app-layout>
    <x-slot name="title">Laporan — Qalcuity ERP</x-slot>
    <x-slot name="header">Export Laporan</x-slot>

    <div class="max-w-4xl space-y-4">
        <p class="text-sm text-gray-500 dark:text-slate-400">Download laporan dalam format Excel (.xlsx) atau PDF. Gunakan AI Chat untuk analisis mendalam.</p>

        @php
        $reports = [
            [
                'key'   => 'sales',
                'label' => 'Laporan Penjualan',
                'desc'  => 'Data order, omzet, dan transaksi penjualan',
                'icon'  => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
                'bg'    => 'bg-blue-50', 'ic' => 'text-blue-600', 'date' => true,
                'excel' => 'reports.sales.excel', 'pdf' => 'reports.sales.pdf',
            ],
            [
                'key'   => 'finance',
                'label' => 'Laporan Keuangan',
                'desc'  => 'Pemasukan, pengeluaran, dan arus kas',
                'icon'  => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'bg'    => 'bg-green-50', 'ic' => 'text-green-600', 'date' => true,
                'excel' => 'reports.finance.excel', 'pdf' => 'reports.finance.pdf',
            ],
            [
                'key'   => 'profit-loss',
                'label' => 'Laporan Laba Rugi',
                'desc'  => 'P&L detail: pendapatan, HPP, biaya, dan margin',
                'icon'  => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                'bg'    => 'bg-purple-50', 'ic' => 'text-purple-600', 'date' => true,
                'excel' => null, 'pdf' => 'reports.profit-loss.pdf',
            ],
            [
                'key'   => 'inventory',
                'label' => 'Laporan Inventori',
                'desc'  => 'Stok produk per gudang dan valuasi',
                'icon'  => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
                'bg'    => 'bg-amber-50', 'ic' => 'text-amber-600', 'date' => false,
                'excel' => 'reports.inventory.excel', 'pdf' => 'reports.inventory.pdf',
            ],
            [
                'key'   => 'receivables',
                'label' => 'Laporan Piutang',
                'desc'  => 'Invoice, aging piutang, dan status pembayaran',
                'icon'  => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
                'bg'    => 'bg-red-50', 'ic' => 'text-red-600', 'date' => true,
                'excel' => 'reports.receivables.excel', 'pdf' => 'reports.receivables.pdf',
            ],
            [
                'key'   => 'hrm',
                'label' => 'Laporan Kehadiran',
                'desc'  => 'Absensi, keterlambatan, dan rekap karyawan',
                'icon'  => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                'bg'    => 'bg-indigo-50', 'ic' => 'text-indigo-600', 'date' => true,
                'excel' => 'reports.hrm.excel', 'pdf' => 'reports.hrm.pdf',
            ],
        ];
        @endphp

        @foreach($reports as $r)
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-11 h-11 rounded-2xl {{ $r['bg'] }} flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 {{ $r['ic'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $r['icon'] }}"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $r['label'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">{{ $r['desc'] }}</p>
                </div>
            </div>

            <div class="flex flex-wrap items-end gap-3" id="form-{{ $r['key'] }}">
                @if($r['date'])
                <div class="w-full sm:w-auto">
                    <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1.5">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ now()->startOfMonth()->format('Y-m-d') }}"
                        class="w-full sm:w-auto px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                </div>
                <div class="w-full sm:w-auto">
                    <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1.5">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ now()->format('Y-m-d') }}"
                        class="w-full sm:w-auto px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                </div>
                @endif

                <div class="flex gap-2 ml-auto">
                    @if($r['excel'])
                    <button onclick="exportReport('{{ $r['key'] }}', 'excel')"
                        class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-gray-900 dark:text-white text-sm font-medium px-4 py-2 rounded-xl transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Excel
                    </button>
                    @endif
                    @if($r['pdf'])
                    <button onclick="exportReport('{{ $r['key'] }}', 'pdf')"
                        class="flex items-center gap-2 bg-red-600 hover:bg-red-700 text-gray-900 dark:text-white text-sm font-medium px-4 py-2 rounded-xl transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        PDF
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @endforeach

        {{-- ── Laporan Keuangan Formal (GL) ── --}}
        <div class="pt-2 pb-1">
            <p class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Laporan Keuangan Formal (dari GL)</p>
        </div>

        {{-- Balance Sheet --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-11 h-11 rounded-2xl bg-cyan-50 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-900 dark:text-white">Neraca (Balance Sheet)</p>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Posisi aset, kewajiban, dan ekuitas per tanggal tertentu dari GL</p>
                </div>
            </div>
            <div class="flex flex-wrap items-end gap-3" id="form-balance-sheet">
                <div class="w-full sm:w-auto">
                    <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1.5">Per Tanggal</label>
                    <input type="date" name="as_of" value="{{ now()->format('Y-m-d') }}"
                        class="w-full sm:w-auto px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                </div>
                <div class="flex gap-2 ml-auto">
                    <button onclick="exportBalanceSheet('excel')"
                        class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Excel
                    </button>
                    <button onclick="exportBalanceSheet('pdf')"
                        class="flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        PDF
                    </button>
                </div>
            </div>
        </div>

        {{-- Cash Flow --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-11 h-11 rounded-2xl bg-teal-50 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-900 dark:text-white">Laporan Arus Kas (Cash Flow)</p>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Arus kas operasi, investasi, dan pendanaan — metode tidak langsung</p>
                </div>
            </div>
            <div class="flex flex-wrap items-end gap-3" id="form-cash-flow">
                <div class="w-full sm:w-auto">
                    <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1.5">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ now()->startOfMonth()->format('Y-m-d') }}"
                        class="w-full sm:w-auto px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                </div>
                <div class="w-full sm:w-auto">
                    <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1.5">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ now()->format('Y-m-d') }}"
                        class="w-full sm:w-auto px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                </div>
                <div class="flex gap-2 ml-auto">
                    <button onclick="exportCashFlow('excel')"
                        class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Excel
                    </button>
                    <button onclick="exportCashFlow('pdf')"
                        class="flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        PDF
                    </button>
                </div>
            </div>
        </div>

        {{-- Budget vs Actual --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-11 h-11 rounded-2xl bg-orange-50 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-900 dark:text-white">Budget vs Aktual</p>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Perbandingan anggaran vs realisasi per departemen dan kategori</p>
                </div>
            </div>
            <div class="flex flex-wrap items-end gap-3" id="form-budget">
                <div class="w-full sm:w-auto">
                    <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1.5">Periode (Bulan)</label>
                    <input type="month" name="period" value="{{ now()->format('Y-m') }}"
                        class="w-full sm:w-auto px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                </div>
                <div class="flex gap-2 ml-auto">
                    <button onclick="exportBudget('excel')"
                        class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Excel
                    </button>
                    <button onclick="exportBudget('pdf')"
                        class="flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        PDF
                    </button>
                </div>
            </div>
        </div>

        {{-- Payroll Export --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-11 h-11 rounded-2xl bg-cyan-50 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-900 dark:text-white">Laporan Penggajian</p>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Detail gaji per karyawan: pokok, tunjangan, potongan, dan gaji bersih</p>
                </div>
            </div>
            <div class="flex flex-wrap items-end gap-3" id="form-payroll">
                <div class="w-full sm:w-auto">
                    <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1.5">Periode</label>
                    <input type="month" name="period" value="{{ now()->format('Y-m') }}"
                        class="w-full sm:w-auto px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <button onclick="exportPayroll()"
                    class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition shadow-sm ml-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Excel
                </button>
            </div>
        </div>

        {{-- AR Aging Export --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-11 h-11 rounded-2xl bg-rose-50 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white">Aging Piutang (AR Aging)</p>
                        <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Analisis umur piutang per customer: current, 1-30, 31-60, 61-90, >90 hari</p>
                    </div>
                </div>
                <a href="{{ route('reports.aging.excel') }}"
                    class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition shadow-sm shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Excel
                </a>
            </div>
        </div>

        {{-- AI Analysis Tip --}}
        <div class="bg-gradient-to-r from-slate-800 to-slate-700 rounded-2xl p-5 text-gray-900 dark:text-white">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-xl bg-[#f8f8f8] dark:bg-white/10 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5 text-gray-900 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-sm mb-1">Analisis Lebih Dalam dengan AI</p>
                    <p class="text-xs text-blue-100 leading-relaxed">Gunakan <strong class="text-gray-900 dark:text-white">AI Chat</strong> untuk analisis mendalam: tren penjualan, laba rugi detail, valuasi inventori, dan laporan karyawan. Cukup ketik perintah seperti <em>"laporan laba rugi bulan ini"</em> atau <em>"produk terlaris minggu ini"</em>.</p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    const reportRoutes = {
        sales:        { excel: '{{ route("reports.sales.excel") }}',        pdf: '{{ route("reports.sales.pdf") }}' },
        finance:      { excel: '{{ route("reports.finance.excel") }}',      pdf: '{{ route("reports.finance.pdf") }}' },
        'profit-loss':{ excel: '{{ route("reports.income-statement.excel") }}', pdf: '{{ route("reports.profit-loss.pdf") }}' },
        inventory:    { excel: '{{ route("reports.inventory.excel") }}',    pdf: '{{ route("reports.inventory.pdf") }}' },
        receivables:  { excel: '{{ route("reports.receivables.excel") }}',  pdf: '{{ route("reports.receivables.pdf") }}' },
        hrm:          { excel: '{{ route("reports.hrm.excel") }}',          pdf: '{{ route("reports.hrm.pdf") }}' },
    };

    function exportReport(type, format) {
        const url = reportRoutes[type]?.[format];
        if (!url) return;
        const form  = document.getElementById('form-' + type);
        const start = form.querySelector('[name="start_date"]')?.value ?? '';
        const end   = form.querySelector('[name="end_date"]')?.value ?? '';
        const p = new URLSearchParams();
        if (start) p.append('start_date', start);
        if (end)   p.append('end_date', end);
        window.location.href = url + (p.toString() ? '?' + p.toString() : '');
    }

    function exportBalanceSheet(format) {
        const form  = document.getElementById('form-balance-sheet');
        const asOf  = form.querySelector('[name="as_of"]')?.value ?? '';
        const urls  = { excel: '{{ route("reports.balance-sheet.excel") }}', pdf: '{{ route("reports.balance-sheet.pdf") }}' };
        window.location.href = urls[format] + '?as_of=' + asOf;
    }

    function exportCashFlow(format) {
        const form  = document.getElementById('form-cash-flow');
        const start = form.querySelector('[name="start_date"]')?.value ?? '';
        const end   = form.querySelector('[name="end_date"]')?.value ?? '';
        const urls  = { excel: '{{ route("reports.cash-flow.excel") }}', pdf: '{{ route("reports.cash-flow.pdf") }}' };
        window.location.href = urls[format] + '?start_date=' + start + '&end_date=' + end;
    }

    function exportBudget(format) {
        const form   = document.getElementById('form-budget');
        const period = form.querySelector('[name="period"]')?.value ?? '';
        const urls   = { excel: '{{ route("reports.budget.excel") }}', pdf: '{{ route("reports.budget.pdf") }}' };
        window.location.href = urls[format] + '?period=' + period;
    }

    function exportPayroll() {
        const form   = document.getElementById('form-payroll');
        const period = form.querySelector('[name="period"]')?.value ?? '';
        window.location.href = '{{ route("reports.payroll.excel") }}?period=' + period;
    }
    </script>
    @endpush
</x-app-layout>
