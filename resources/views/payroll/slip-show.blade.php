<x-app-layout>
    <x-slot name="header">Slip Gaji — {{ $item->payrollRun?->period ?? '—' }}</x-slot>

    <div class="flex flex-col lg:flex-row gap-5">

        {{-- Sidebar actions --}}
        <div class="lg:w-52 shrink-0 space-y-3 print:hidden">
            <button onclick="window.print()"
                class="w-full py-2.5 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak
            </button>
            <a href="{{ route('payroll.slip.pdf', $item) }}"
                class="w-full py-2.5 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700 flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Unduh PDF
            </a>
            <a href="{{ route('payroll.slip.index') }}"
                class="block text-center py-2.5 text-sm border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50">
                ← Kembali
            </a>

            {{-- Status badge --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-4 text-center">
                <p class="text-xs text-gray-400 mb-1">Status</p>
                <span class="px-3 py-1 rounded-full text-sm font-medium
                    {{ $item->status === 'paid'
                        ? 'bg-green-100 text-green-700'
                        : 'bg-amber-100 text-amber-700' }}">
                    {{ $item->status === 'paid' ? '✓ Sudah Dibayar' : 'Diproses' }}
                </span>
                @if($item->payrollRun?->processed_at)
                <p class="text-xs text-gray-400 mt-2">
                    {{ $item->payrollRun?->processed_at->format('d M Y') }}
                </p>
                @endif
            </div>
        </div>

        {{-- Slip Gaji (printable) --}}
        <div class="flex-1 min-w-0">
            <div id="slip-print"
                class="bg-white rounded-2xl border border-gray-200 p-8
                       print:shadow-none print:border-none print:rounded-none print:p-6 print:bg-white print:text-black">

                {{-- Kop --}}
                <div class="flex items-start justify-between border-b-2 border-gray-800 pb-5 mb-6 print:border-gray-800">
                    <div>
                        @if($profile?->logo_path)
                        <img src="{{ asset('storage/'.$profile->logo_path) }}" alt="Logo" class="h-10 mb-2 object-contain">
                        @endif
                        <p class="text-lg font-black text-gray-900 print:text-black uppercase">
                            {{ $profile?->company_name ?? $companyName }}
                        </p>
                        @if($profile?->address)
                        <p class="text-xs text-gray-500 print:text-gray-600 mt-0.5">{{ $profile->address }}</p>
                        @endif
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-gray-700 print:text-gray-800 uppercase tracking-wide">Slip Gaji</p>
                        @php
                            $period = $item->payrollRun?->period ?? '—';
                            [$yr, $mo] = str_contains($period, '-') ? explode('-', $period) : [$period, ''];
                            $monthName = $mo ? \Carbon\Carbon::createFromFormat('m', $mo)->locale('id')->translatedFormat('F Y') : $period;
                        @endphp
                        <p class="text-xl font-black text-blue-600 print:text-blue-700 capitalize">{{ $monthName }}</p>
                        <p class="text-xs text-gray-400 print:text-gray-500 mt-1">
                            Dicetak: {{ now()->format('d M Y H:i') }}
                        </p>
                    </div>
                </div>

                {{-- Info karyawan --}}
                <div class="grid grid-cols-2 gap-x-8 gap-y-1.5 mb-6 text-sm">
                    @foreach([
                        'Nama'         => $item->employee?->name ?? '-',
                        'NIK'          => $item->employee?->employee_id ?? '-',
                        'Jabatan'      => $item->employee?->position ?? '-',
                        'Departemen'   => $item->employee?->department ?? '-',
                        'Bank'         => $item->employee?->bank_name ?? '-',
                        'No. Rekening' => $item->employee?->bank_account ?? '-',
                    ] as $label => $value)
                    <div class="flex gap-2">
                        <span class="w-28 shrink-0 text-gray-500 print:text-gray-500">{{ $label }}</span>
                        <span class="text-gray-900 print:text-black font-medium">: {{ $value }}</span>
                    </div>
                    @endforeach
                </div>

                {{-- Tabel komponen gaji --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-6">

                    {{-- Pendapatan --}}
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 print:text-gray-500 mb-2">Pendapatan</p>
                        <table class="w-full text-sm">
                            <tbody class="divide-y divide-gray-100 print:divide-gray-200">
                                <tr>
                                    <td class="py-1.5 text-gray-600 print:text-gray-700">Gaji Pokok</td>
                                    <td class="py-1.5 text-right font-medium text-gray-900 print:text-black">Rp {{ number_format($item->base_salary, 0, ',', '.') }}</td>
                                </tr>
                                @php $compAllowances = $item->components->where('type','allowance'); @endphp
                                @if($compAllowances->count())
                                    @foreach($compAllowances ?? [] as $c)
                                    <tr>
                                        <td class="py-1.5 text-gray-600 print:text-gray-700">{{ $c->name }}</td>
                                        <td class="py-1.5 text-right font-medium text-gray-900 print:text-black">Rp {{ number_format($c->amount, 0, ',', '.') }}</td>
                                    </tr>
                                    @endforeach
                                @elseif(($item->allowances ?? 0) > 0)
                                <tr>
                                    <td class="py-1.5 text-gray-600 print:text-gray-700">Tunjangan</td>
                                    <td class="py-1.5 text-right font-medium text-gray-900 print:text-black">Rp {{ number_format($item->allowances, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                                @if(($item->overtime_pay ?? 0) > 0)
                                <tr>
                                    <td class="py-1.5 text-gray-600 print:text-gray-700">
                                        Upah Lembur
                                        @if($overtimes->count())
                                        <span class="text-xs text-gray-400">({{ $overtimes->count() }}x)</span>
                                        @endif
                                    </td>
                                    <td class="py-1.5 text-right font-medium text-green-600 print:text-green-700">+Rp {{ number_format($item->overtime_pay, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                                <tr class="border-t-2 border-gray-300 print:border-gray-400">
                                    <td class="py-2 font-semibold text-gray-900 print:text-black">Total Pendapatan</td>
                                    <td class="py-2 text-right font-bold text-gray-900 print:text-black">
                                        Rp {{ number_format($item->base_salary + ($item->allowances ?? 0) + ($item->overtime_pay ?? 0), 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Potongan --}}
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 print:text-gray-500 mb-2">Potongan</p>
                        <table class="w-full text-sm">
                            <tbody class="divide-y divide-gray-100 print:divide-gray-200">
                                @if(($item->deduction_absent ?? 0) > 0)
                                <tr>
                                    <td class="py-1.5 text-gray-600 print:text-gray-700">Potongan Absen ({{ $item->absent_days }}h)</td>
                                    <td class="py-1.5 text-right font-medium text-red-600 print:text-red-700">-Rp {{ number_format($item->deduction_absent, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                                @if(($item->deduction_late ?? 0) > 0)
                                <tr>
                                    <td class="py-1.5 text-gray-600 print:text-gray-700">Potongan Terlambat ({{ $item->late_days }}h)</td>
                                    <td class="py-1.5 text-right font-medium text-red-600 print:text-red-700">-Rp {{ number_format($item->deduction_late, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                                @if(($item->bpjs_employee ?? 0) > 0)
                                <tr>
                                    <td class="py-1.5 text-gray-600 print:text-gray-700">BPJS (Kesehatan + Ketenagakerjaan)</td>
                                    <td class="py-1.5 text-right font-medium text-red-600 print:text-red-700">-Rp {{ number_format($item->bpjs_employee, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                                @if(($item->tax_pph21 ?? 0) > 0)
                                <tr>
                                    <td class="py-1.5 text-gray-600 print:text-gray-700">PPh 21</td>
                                    <td class="py-1.5 text-right font-medium text-red-600 print:text-red-700">-Rp {{ number_format($item->tax_pph21, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                                @if(($item->deduction_other ?? 0) > 0)
                                @php $compDeductions = $item->components->where('type','deduction'); @endphp
                                @if($compDeductions->count())
                                    @foreach($compDeductions ?? [] as $c)
                                    <tr>
                                        <td class="py-1.5 text-gray-600 print:text-gray-700">{{ $c->name }}</td>
                                        <td class="py-1.5 text-right font-medium text-red-600 print:text-red-700">-Rp {{ number_format($c->amount, 0, ',', '.') }}</td>
                                    </tr>
                                    @endforeach
                                @else
                                <tr>
                                    <td class="py-1.5 text-gray-600 print:text-gray-700">Potongan Lain</td>
                                    <td class="py-1.5 text-right font-medium text-red-600 print:text-red-700">-Rp {{ number_format($item->deduction_other, 0, ',', '.') }}</td>
                                </tr>
                                @endif
                                @endif
                                @php $totalDeduct = ($item->deduction_absent ?? 0) + ($item->deduction_late ?? 0) + ($item->bpjs_employee ?? 0) + ($item->tax_pph21 ?? 0) + ($item->deduction_other ?? 0); @endphp
                                <tr class="border-t-2 border-gray-300 print:border-gray-400">
                                    <td class="py-2 font-semibold text-gray-900 print:text-black">Total Potongan</td>
                                    <td class="py-2 text-right font-bold text-red-600 print:text-red-700">-Rp {{ number_format($totalDeduct, 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Detail lembur --}}
                @if($overtimes->count())
                <div class="mb-6">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 print:text-gray-500 mb-2">Rincian Lembur</p>
                    <table class="w-full text-xs border border-gray-100 print:border-gray-200 rounded-xl overflow-hidden">
                        <thead class="bg-gray-50 print:bg-gray-100">
                            <tr>
                                <th class="px-3 py-2 text-left text-gray-500 print:text-gray-600">Tanggal</th>
                                <th class="px-3 py-2 text-center text-gray-500 print:text-gray-600">Waktu</th>
                                <th class="px-3 py-2 text-center text-gray-500 print:text-gray-600">Durasi</th>
                                <th class="px-3 py-2 text-right text-gray-500 print:text-gray-600">Upah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 print:divide-gray-200">
                            @foreach($overtimes ?? [] as $ot)
                            <tr>
                                <td class="px-3 py-1.5 text-gray-700 print:text-gray-700">{{ $ot->date->format('d M Y') }}</td>
                                <td class="px-3 py-1.5 text-center text-gray-600 print:text-gray-600">{{ substr($ot->start_time, 0, 5) }} – {{ substr($ot->end_time, 0, 5) }}</td>
                                <td class="px-3 py-1.5 text-center text-gray-700 print:text-gray-700">{{ $ot->durationLabel() }}</td>
                                <td class="px-3 py-1.5 text-right text-green-600 print:text-green-700">Rp {{ number_format($ot->overtime_pay, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                {{-- Take Home Pay --}}
                <div class="bg-blue-50 print:bg-blue-50 border border-blue-200 print:border-blue-200 rounded-2xl p-5 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-blue-600 print:text-blue-700 font-medium">Take Home Pay (Gaji Bersih)</p>
                        <p class="text-xs text-blue-400 print:text-blue-500 mt-0.5">
                            Kehadiran: {{ $item->present_days }}/{{ $item->working_days }} hari
                        </p>
                    </div>
                    <p class="text-2xl font-black text-blue-700 print:text-blue-800">
                        Rp {{ number_format($item->net_salary, 0, ',', '.') }}
                    </p>
                </div>

                {{-- Footer --}}
                <div class="mt-8 pt-4 border-t border-gray-100 print:border-gray-200 text-xs text-gray-400 print:text-gray-500 text-center">
                    Slip gaji ini diterbitkan secara otomatis oleh sistem. Tidak memerlukan tanda tangan.
                </div>
            </div>
        </div>
    </div>

    @push('head')
    <style>
    @media print {
        body > * { display: none !important; }
        #slip-print { display: block !important; position: fixed; top: 0; left: 0; width: 100%; background: white !important; color: black !important; }
        #slip-print * { color: inherit !important; }
    }
    </style>
    @endpush
</x-app-layout>
