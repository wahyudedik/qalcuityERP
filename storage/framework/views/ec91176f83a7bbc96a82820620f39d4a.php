<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Slip Gaji — <?php echo e($item->payrollRun?->period ?? '-'); ?></title>
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

    
    <div class="header">
        <div class="header-left">
            <div class="company-name"><?php echo e($profile?->company_name ?? $companyName); ?></div>
            <?php if($profile?->address): ?>
            <div class="company-address"><?php echo e($profile->address); ?></div>
            <?php endif; ?>
            <?php if($profile?->npwp): ?>
            <div class="company-address">NPWP: <?php echo e($profile->npwp); ?></div>
            <?php endif; ?>
        </div>
        <div class="header-right">
            <div class="slip-title">Slip Gaji</div>
            <?php
                $period = $item->payrollRun?->period ?? '-';
                [$yr, $mo] = str_contains($period, '-') ? explode('-', $period) : [$period, ''];
                $monthName = $mo ? \Carbon\Carbon::createFromFormat('m', $mo)->locale('id')->translatedFormat('F Y') : $period;
            ?>
            <div class="slip-period"><?php echo e(ucfirst($monthName)); ?></div>
            <div class="slip-printed">Dicetak: <?php echo e(now()->format('d/m/Y H:i')); ?></div>
        </div>
    </div>

    
    <div class="employee-info">
        <table>
            <tr>
                <td class="label">Nama</td>
                <td class="sep">:</td>
                <td class="value"><?php echo e($item->employee?->name ?? '-'); ?></td>
                <td class="label">NIK</td>
                <td class="sep">:</td>
                <td class="value"><?php echo e($item->employee?->employee_id ?? '-'); ?></td>
            </tr>
            <tr>
                <td class="label">Jabatan</td>
                <td class="sep">:</td>
                <td class="value"><?php echo e($item->employee?->position ?? '-'); ?></td>
                <td class="label">Departemen</td>
                <td class="sep">:</td>
                <td class="value"><?php echo e($item->employee?->department ?? '-'); ?></td>
            </tr>
            <tr>
                <td class="label">Bank</td>
                <td class="sep">:</td>
                <td class="value"><?php echo e($item->employee?->bank_name ?? '-'); ?></td>
                <td class="label">No. Rekening</td>
                <td class="sep">:</td>
                <td class="value"><?php echo e($item->employee?->bank_account ?? '-'); ?></td>
            </tr>
            <tr>
                <td class="label">Kehadiran</td>
                <td class="sep">:</td>
                <td class="value"><?php echo e($item->present_days ?? 0); ?> / <?php echo e($item->working_days ?? 0); ?> hari</td>
                <td class="label">Status</td>
                <td class="sep">:</td>
                <td class="value"><?php echo e($item->status === 'paid' ? 'Sudah Dibayar' : 'Diproses'); ?></td>
            </tr>
        </table>
    </div>

    
    <div class="two-col">
        
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
                        <td class="right"><?php echo e(number_format($item->base_salary, 0, ',', '.')); ?></td>
                    </tr>
                    <?php $compAllowances = $item->components->where('type','allowance'); ?>
                    <?php if($compAllowances->count()): ?>
                        <?php $__currentLoopData = $compAllowances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($c->name); ?></td>
                            <td class="right text-green"><?php echo e(number_format($c->amount, 0, ',', '.')); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php elseif(($item->allowances ?? 0) > 0): ?>
                    <tr>
                        <td>Tunjangan</td>
                        <td class="right text-green"><?php echo e(number_format($item->allowances, 0, ',', '.')); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if(($item->overtime_pay ?? 0) > 0): ?>
                    <tr>
                        <td>Upah Lembur<?php echo e($overtimes->count() ? ' ('.$overtimes->count().'x)' : ''); ?></td>
                        <td class="right text-green"><?php echo e(number_format($item->overtime_pay, 0, ',', '.')); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr class="subtotal">
                        <td>Total Pendapatan</td>
                        <td class="right"><?php echo e(number_format($item->base_salary + ($item->allowances ?? 0) + ($item->overtime_pay ?? 0), 0, ',', '.')); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        
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
                    <?php if(($item->deduction_absent ?? 0) > 0): ?>
                    <tr>
                        <td>Potongan Absen (<?php echo e($item->absent_days); ?>h)</td>
                        <td class="right text-red"><?php echo e(number_format($item->deduction_absent, 0, ',', '.')); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if(($item->deduction_late ?? 0) > 0): ?>
                    <tr>
                        <td>Potongan Terlambat (<?php echo e($item->late_days); ?>h)</td>
                        <td class="right text-red"><?php echo e(number_format($item->deduction_late, 0, ',', '.')); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if(($item->bpjs_employee ?? 0) > 0): ?>
                    <tr>
                        <td>BPJS Ketenagakerjaan (3%)</td>
                        <td class="right text-red"><?php echo e(number_format($item->bpjs_employee, 0, ',', '.')); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if(($item->tax_pph21 ?? 0) > 0): ?>
                    <tr>
                        <td>PPh 21</td>
                        <td class="right text-red"><?php echo e(number_format($item->tax_pph21, 0, ',', '.')); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if(($item->deduction_other ?? 0) > 0): ?>
                        <?php $compDeductions = $item->components->where('type','deduction'); ?>
                        <?php if($compDeductions->count()): ?>
                            <?php $__currentLoopData = $compDeductions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($c->name); ?></td>
                                <td class="right text-red"><?php echo e(number_format($c->amount, 0, ',', '.')); ?></td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php else: ?>
                        <tr>
                            <td>Potongan Lain</td>
                            <td class="right text-red"><?php echo e(number_format($item->deduction_other, 0, ',', '.')); ?></td>
                        </tr>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php
                        $totalDeduct = ($item->deduction_absent ?? 0)
                            + ($item->deduction_late ?? 0)
                            + ($item->bpjs_employee ?? 0)
                            + ($item->tax_pph21 ?? 0)
                            + ($item->deduction_other ?? 0);
                    ?>
                    <tr class="subtotal">
                        <td>Total Potongan</td>
                        <td class="right text-red"><?php echo e(number_format($totalDeduct, 0, ',', '.')); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    
    <?php if($overtimes->count()): ?>
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
            <?php $__currentLoopData = $overtimes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($ot->date->format('d/m/Y')); ?></td>
                <td class="center"><?php echo e(substr($ot->start_time, 0, 5)); ?> – <?php echo e(substr($ot->end_time, 0, 5)); ?></td>
                <td class="center"><?php echo e($ot->durationLabel()); ?></td>
                <td class="right"><?php echo e(number_format($ot->overtime_pay, 0, ',', '.')); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    <?php endif; ?>

    
    <div class="thp-box">
        <div class="thp-left">
            <div class="thp-label">Take Home Pay (Gaji Bersih)</div>
            <div class="thp-sub">Kehadiran: <?php echo e($item->present_days ?? 0); ?>/<?php echo e($item->working_days ?? 0); ?> hari kerja</div>
        </div>
        <div class="thp-right">
            <div class="thp-amount">Rp <?php echo e(number_format($item->net_salary, 0, ',', '.')); ?></div>
        </div>
    </div>

    
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
            <div class="sig-name"><?php echo e($item->employee?->name ?? 'Karyawan'); ?></div>
        </div>
    </div>

    
    <div class="footer">
        Slip gaji ini diterbitkan secara otomatis oleh sistem <?php echo e($profile?->company_name ?? $companyName); ?>.
        Dokumen ini sah tanpa tanda tangan basah. Periode: <?php echo e(ucfirst($monthName)); ?>.
    </div>

</body>
</html>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\pdf\payslip.blade.php ENDPATH**/ ?>