<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Slip Gaji — {{ $item->payrollRun?->period ?? '-' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            background: #fff;
            padding: 24px 32px;
        }

        /* ── Kop Surat ── */
        .header {
            display: table;
            width: 100%;
            border-bottom: 2px solid #1e3a5f;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .header-left  { display: table-cell; vertical-align: middle; width: 60%; }
        .header-right { display: table-cell; vertical-align: middle; text-align: right; }
        .company-name { font-size: 16px; font-weight: bold; color: #1e3a5f; text-transform: uppercase; letter-spacing: 0.5px; }
        .company-address { font-size: 9px; color: #555; margin-top: 2px; }
        .slip-title { font-size: 13px; font-weight: bold; color: #1e3a5f; text-transform: uppercase; letter-spacing: 1px; }
        .slip-period { font-size: 18px; font-weight: bold; color: #2563eb; margin-top: 2px; }
        .slip-printed { font-size: 9px; color: #888; margin-top: 3px; }

        /* ── Info Karyawan ── */
        .employee-info {
            background: #f0f4ff;
            border: 1px solid #c7d7f5;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 16px;
        }
        .employee-info table { width: 100%; }
        .employee-info td { padding: 2px 4px; font-size: 10.5px; }
        .employee-info .label { color: #555; width: 110px; }
        .employee-info .value { font-weight: bold; color: #1a1a1a; }
        .employee-info .sep { color: #888; width: 10px; }

        /* ── Tabel Komponen ── */
        .section-title {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #555;
            margin-bottom: 5px;
        }
        .comp-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .comp-table th {
            background: #1e3a5f;
            color: #fff;
            padding: 5px 8px;
            text-align: left;
            font-size: 9.5px;
            font-weight: bold;
        }
        .comp-table th.right { text-align: right; }
        .comp-table td { padding: 4px 8px; font-size: 10px; border-bottom: 1px solid #e8ecf0; }
        .comp-table td.right { text-align: right; }
        .comp-table tr:nth-child(even) td { background: #f8fafc; }
        .comp-table .subtotal td {
            border-top: 1.5px solid #1e3a5f;
            font-weight: bold;
            background: #eef2ff !important;
        }
        .text-green { color: #16a34a; }
        .text-red   { color: #dc2626; }

        /* ── Grid 2 kolom ── */
        .two-col { display: table; width: 100%; margin-bottom: 14px; }
        .col-left  { display: table-cell; width: 49%; vertical-align: top; padding-right: 8px; }
        .col-right { display: table-cell; width: 49%; vertical-align: top; padding-left: 8px; }

        /* ── Lembur ── */
        .overtime-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .overtime-table th { background: #374151; color: #fff; padding: 4px 8px; font-size: 9px; }
        .overtime-table td { padding: 3px 8px; font-size: 9.5px; border-bottom: 1px solid #e8ecf0; }
        .overtime-table td.right { text-align: right; }
        .overtime-table td.center { text-align: center; }

        /* ── Take Home Pay ── */
        .thp-box {
            background: #1e3a5f;
            color: #fff;
            border-radius: 6px;
            padding: 12px 16px;
            display: table;
            width: 100%;
            margin-bottom: 16px;
        }
        .thp-left  { display: table-cell; vertical-align: middle; }
        .thp-right { display: table-cell; vertical-align: middle; text-align: right; }
        .thp-label { font-size: 10px; color: #93c5fd; }
        .thp-sub   { font-size: 9px; color: #7dd3fc; margin-top: 2px; }
        .thp-amount { font-size: 20px; font-weight: bold; color: #fff; }

        /* ── Footer ── */
        .footer {
            border-top: 1px solid #e0e0e0;
            padding-top: 8px;
            text-align: center;
            font-size: 8.5px;
            color: #888;
        }

        /* ── Tanda Tangan ── */
        .signature-row { display: table; width: 100%; margin-top: 20px; margin-bottom: 8px; }
        .sig-cell { display: table-cell; text-align: center; width: 33%; font-size: 9.5px; }
        .sig-line { border-top: 1px solid #555; margin: 40px 20px 4px; }
        .sig-name { font-weight: bold; }
    </style>
</head>
<body>

    {{-- ── Kop Surat ── --}}
    <div class="header">
        <div class="header-left">
            <div class="company-name">{{ $profile?->company_name ?? $companyName }}</div>
            @if($profile?->address)
            <div class="company-address">{{ $profile->address }}</div>
            @endif
            @if($profile?->npwp)
            <div class="company-address">NPWP: {{ $profile->npwp }}</div>
            @endif
        </div>
        <div class="header-right">
            <div class="slip-title">Slip Gaji</div>
            @php
                $period = $item->payrollRun?->period ?? '-';
                [$yr, $mo] = str_contains($period, '-') ? explode('-', $period) : [$period, ''];
                $monthName = $mo ? \Carbon\Carbon::createFromFormat('m', $mo)->locale('id')->translatedFormat('F Y') : $period;
            @endphp
            <div class="slip-period">{{ ucfirst($monthName) }}</div>
            <div class="slip-printed">Dicetak: {{ now()->format('d/m/Y H:i') }}</div>
        </div>
    </div>

    {{-- ── Info Karyawan ── --}}
    <div class="employee-info">
        <table>
            <tr>
                <td class="label">Nama</td>
                <td class="sep">:</td>
                <td class="value">{{ $item->employee?->name ?? '-' }}</td>
                <td class="label">NIK</td>
                <td class="sep">:</td>
                <td class="value">{{ $item->employee?->employee_id ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Jabatan</td>
                <td class="sep">:</td>
                <td class="value">{{ $item->employee?->position ?? '-' }}</td>
                <td class="label">Departemen</td>
                <td class="sep">:</td>
                <td class="value">{{ $item->employee?->department ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Bank</td>
                <td class="sep">:</td>
                <td class="value">{{ $item->employee?->bank_name ?? '-' }}</td>
                <td class="label">No. Rekening</td>
                <td class="sep">:</td>
                <td class="value">{{ $item->employee?->bank_account ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Kehadiran</td>
                <td class="sep">:</td>
                <td class="value">{{ $item->present_days ?? 0 }} / {{ $item->working_days ?? 0 }} hari</td>
                <td class="label">Status</td>
                <td class="sep">:</td>
                <td class="value">{{ $item->status === 'paid' ? 'Sudah Dibayar' : 'Diproses' }}</td>
            </tr>
        </table>
    </div>

    {{-- ── Komponen Gaji (2 kolom) ── --}}
    <div class="two-col">
        {{-- Pendapatan --}}
        <div class="col-left">
            <div class="section-title">Pendapatan</div>
            <table class="comp-table">
                <thead>
                    <tr>
                        <th>Komponen</th>
                        <th class="right">Jumlah (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Gaji Pokok</td>
                        <td class="right">{{ number_format($item->base_salary, 0, ',', '.') }}</td>
                    </tr>
                    @php $compAllowances = $item->components->where('type','allowance'); @endphp
                    @if($compAllowances->count())
                        @foreach($compAllowances as $c)
                        <tr>
                            <td>{{ $c->name }}</td>
                            <td class="right text-green">{{ number_format($c->amount, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    @elseif(($item->allowances ?? 0) > 0)
                    <tr>
                        <td>Tunjangan</td>
                        <td class="right text-green">{{ number_format($item->allowances, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if(($item->overtime_pay ?? 0) > 0)
                    <tr>
                        <td>Upah Lembur{{ $overtimes->count() ? ' ('.$overtimes->count().'x)' : '' }}</td>
                        <td class="right text-green">{{ number_format($item->overtime_pay, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr class="subtotal">
                        <td>Total Pendapatan</td>
                        <td class="right">{{ number_format($item->base_salary + ($item->allowances ?? 0) + ($item->overtime_pay ?? 0), 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Potongan --}}
        <div class="col-right">
            <div class="section-title">Potongan</div>
            <table class="comp-table">
                <thead>
                    <tr>
                        <th>Komponen</th>
                        <th class="right">Jumlah (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @if(($item->deduction_absent ?? 0) > 0)
                    <tr>
                        <td>Potongan Absen ({{ $item->absent_days }}h)</td>
                        <td class="right text-red">{{ number_format($item->deduction_absent, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if(($item->deduction_late ?? 0) > 0)
                    <tr>
                        <td>Potongan Terlambat ({{ $item->late_days }}h)</td>
                        <td class="right text-red">{{ number_format($item->deduction_late, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if(($item->bpjs_employee ?? 0) > 0)
                    <tr>
                        <td>BPJS Ketenagakerjaan (3%)</td>
                        <td class="right text-red">{{ number_format($item->bpjs_employee, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if(($item->tax_pph21 ?? 0) > 0)
                    <tr>
                        <td>PPh 21</td>
                        <td class="right text-red">{{ number_format($item->tax_pph21, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if(($item->deduction_other ?? 0) > 0)
                        @php $compDeductions = $item->components->where('type','deduction'); @endphp
                        @if($compDeductions->count())
                            @foreach($compDeductions as $c)
                            <tr>
                                <td>{{ $c->name }}</td>
                                <td class="right text-red">{{ number_format($c->amount, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        @else
                        <tr>
                            <td>Potongan Lain</td>
                            <td class="right text-red">{{ number_format($item->deduction_other, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                    @endif
                    @php
                        $totalDeduct = ($item->deduction_absent ?? 0)
                            + ($item->deduction_late ?? 0)
                            + ($item->bpjs_employee ?? 0)
                            + ($item->tax_pph21 ?? 0)
                            + ($item->deduction_other ?? 0);
                    @endphp
                    <tr class="subtotal">
                        <td>Total Potongan</td>
                        <td class="right text-red">{{ number_format($totalDeduct, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Rincian Lembur ── --}}
    @if($overtimes->count())
    <div class="section-title">Rincian Lembur</div>
    <table class="overtime-table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th class="center">Waktu</th>
                <th class="center">Durasi</th>
                <th class="right">Upah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($overtimes as $ot)
            <tr>
                <td>{{ $ot->date->format('d/m/Y') }}</td>
                <td class="center">{{ substr($ot->start_time, 0, 5) }} – {{ substr($ot->end_time, 0, 5) }}</td>
                <td class="center">{{ $ot->durationLabel() }}</td>
                <td class="right">{{ number_format($ot->overtime_pay, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- ── Take Home Pay ── --}}
    <div class="thp-box">
        <div class="thp-left">
            <div class="thp-label">Take Home Pay (Gaji Bersih)</div>
            <div class="thp-sub">Kehadiran: {{ $item->present_days ?? 0 }}/{{ $item->working_days ?? 0 }} hari kerja</div>
        </div>
        <div class="thp-right">
            <div class="thp-amount">Rp {{ number_format($item->net_salary, 0, ',', '.') }}</div>
        </div>
    </div>

    {{-- ── Tanda Tangan ── --}}
    <div class="signature-row">
        <div class="sig-cell">
            <div>Dibuat oleh,</div>
            <div class="sig-line"></div>
            <div class="sig-name">HRD / Payroll</div>
        </div>
        <div class="sig-cell">
            <div>Disetujui oleh,</div>
            <div class="sig-line"></div>
            <div class="sig-name">Direktur / Manager</div>
        </div>
        <div class="sig-cell">
            <div>Diterima oleh,</div>
            <div class="sig-line"></div>
            <div class="sig-name">{{ $item->employee?->name ?? 'Karyawan' }}</div>
        </div>
    </div>

    {{-- ── Footer ── --}}
    <div class="footer">
        Slip gaji ini diterbitkan secara otomatis oleh sistem {{ $profile?->company_name ?? $companyName }}.
        Dokumen ini sah tanpa tanda tangan basah. Periode: {{ ucfirst($monthName) }}.
    </div>

</body>
</html>
