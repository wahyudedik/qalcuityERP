<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Buku Besar - <?php echo e($account->code); ?> <?php echo e($account->name); ?></title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .company-name {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 12pt;
            font-weight: bold;
            margin-top: 10px;
        }
        .account-info {
            margin: 15px 0;
            padding: 10px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
        }
        .account-info table {
            width: 100%;
        }
        .account-info td {
            padding: 3px 5px;
        }
        .account-info .label {
            font-weight: bold;
            width: 150px;
        }
        table.ledger {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.ledger th {
            background-color: #333;
            color: white;
            padding: 8px 5px;
            text-align: left;
            font-size: 9pt;
            border: 1px solid #333;
        }
        table.ledger td {
            padding: 6px 5px;
            border: 1px solid #ddd;
            font-size: 9pt;
        }
        table.ledger tr.opening,
        table.ledger tr.closing {
            background-color: #e8f4f8;
            font-weight: bold;
        }
        table.ledger tr:hover {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            font-size: 8pt;
            text-align: center;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name"><?php echo e($tenant->name ?? 'Qalcuity ERP'); ?></div>
        <?php if($tenant->address): ?>
        <div style="font-size: 9pt;"><?php echo e($tenant->address); ?></div>
        <?php endif; ?>
        <div class="report-title">BUKU BESAR (GENERAL LEDGER)</div>
        <div style="font-size: 9pt; margin-top: 5px;">
            Periode: <?php echo e(\Carbon\Carbon::parse($from)->translatedFormat('d F Y')); ?> s/d <?php echo e(\Carbon\Carbon::parse($to)->translatedFormat('d F Y')); ?>

        </div>
    </div>

    <div class="account-info">
        <table>
            <tr>
                <td class="label">Kode Akun:</td>
                <td><?php echo e($account->code); ?></td>
                <td class="label">Tipe:</td>
                <td><?php echo e($account->getTypeLabel()); ?></td>
            </tr>
            <tr>
                <td class="label">Nama Akun:</td>
                <td><?php echo e($account->name); ?></td>
                <td class="label">Saldo Normal:</td>
                <td style="text-transform: capitalize;"><?php echo e($account->normal_balance); ?></td>
            </tr>
        </table>
    </div>

    <table class="ledger">
        <thead>
            <tr>
                <th style="width: 10%;">Tanggal</th>
                <th style="width: 12%;">Referensi</th>
                <th style="width: 38%;">Keterangan</th>
                <th style="width: 13%;" class="text-right">Debit</th>
                <th style="width: 13%;" class="text-right">Kredit</th>
                <th style="width: 14%;" class="text-right">Saldo</th>
            </tr>
        </thead>
        <tbody>
            
            <tr class="opening">
                <td colspan="3">Saldo Awal</td>
                <td class="text-right">-</td>
                <td class="text-right">-</td>
                <td class="text-right">
                    <?php echo e(number_format(abs($openingBalance), 0, ',', '.')); ?>

                    <?php if($openingBalance < 0): ?> (K) <?php endif; ?>
                </td>
            </tr>

            <?php $__empty_1 = true; $__currentLoopData = $entries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td><?php echo e(\Carbon\Carbon::parse($entry['date'])->format('d/m/Y')); ?></td>
                <td><?php echo e($entry['reference']); ?></td>
                <td><?php echo e($entry['description']); ?></td>
                <td class="text-right">
                    <?php echo e($entry['debit'] > 0 ? number_format($entry['debit'], 0, ',', '.') : '-'); ?>

                </td>
                <td class="text-right">
                    <?php echo e($entry['credit'] > 0 ? number_format($entry['credit'], 0, ',', '.') : '-'); ?>

                </td>
                <td class="text-right">
                    <?php echo e(number_format(abs($entry['balance']), 0, ',', '.')); ?>

                    <?php if($entry['balance'] < 0): ?> (K) <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="6" class="text-center">Tidak ada transaksi untuk periode ini.</td>
            </tr>
            <?php endif; ?>

            <?php if($entries->count() > 0): ?>
            
            <tr class="closing">
                <td colspan="3">Saldo Akhir</td>
                <td class="text-right"><?php echo e(number_format($entries->sum('debit'), 0, ',', '.')); ?></td>
                <td class="text-right"><?php echo e(number_format($entries->sum('credit'), 0, ',', '.')); ?></td>
                <td class="text-right">
                    <?php
                        $closingBalance = $entries->last()['balance'] ?? $openingBalance;
                    ?>
                    <?php echo e(number_format(abs($closingBalance), 0, ',', '.')); ?>

                    <?php if($closingBalance < 0): ?> (K) <?php endif; ?>
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: <?php echo e(now()->translatedFormat('d F Y H:i')); ?> | <?php echo e(config('app.name')); ?>

    </div>
</body>
</html>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\accounting\pdf\general-ledger.blade.php ENDPATH**/ ?>