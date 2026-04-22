<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #1a1a1a; }
    .header { text-align: center; border-bottom: 2px solid #1e40af; padding-bottom: 8px; margin-bottom: 12px; }
    .header h1 { font-size: 15px; font-weight: bold; color: #1e40af; }
    .header p { font-size: 9px; color: #555; margin-top: 2px; }
    .summary { display: table; width: 100%; margin-bottom: 12px; }
    .summary-cell { display: table-cell; width: 25%; text-align: center; padding: 6px; background: #f3f4f6; border-right: 1px solid #e5e7eb; }
    .summary-cell:last-child { border-right: none; }
    .summary-cell .val { font-size: 12px; font-weight: bold; color: #1e40af; }
    .summary-cell .lbl { font-size: 8px; color: #6b7280; margin-top: 2px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #1e40af; color: #fff; padding: 5px 6px; text-align: left; font-size: 8px; text-transform: uppercase; }
    th:nth-child(n+4) { text-align: right; }
    td { padding: 4px 6px; border-bottom: 1px solid #f3f4f6; font-size: 9px; }
    td:nth-child(n+4) { text-align: right; }
    tr:nth-child(even) { background: #f9fafb; }
    .dept-row td { background: #eff6ff; font-weight: bold; font-size: 8px; color: #1d4ed8; }
    .over { color: #dc2626; font-weight: bold; }
    .warn { color: #d97706; font-weight: bold; }
    .ok   { color: #16a34a; }
    .badge-over { background: #fee2e2; color: #991b1b; padding: 1px 5px; border-radius: 3px; font-size: 8px; }
    .badge-warn { background: #fef3c7; color: #92400e; padding: 1px 5px; border-radius: 3px; font-size: 8px; }
    .badge-ok   { background: #dcfce7; color: #166534; padding: 1px 5px; border-radius: 3px; font-size: 8px; }
    tfoot td { font-weight: bold; background: #1e40af; color: #fff; padding: 5px 6px; }
    tfoot td:nth-child(n+4) { text-align: right; }
    .footer { margin-top: 12px; font-size: 7px; color: #9ca3af; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 6px; }
    .progress-bar { background: #e5e7eb; border-radius: 2px; height: 6px; width: 80px; display: inline-block; vertical-align: middle; }
    .progress-fill { height: 6px; border-radius: 2px; }
</style>
</head>
<body>

<div class="header">
    <h1>LAPORAN BUDGET VS AKTUAL</h1>
    <p><?php echo e($tenant_name); ?></p>
    <p>Periode: <?php echo e(\Carbon\Carbon::createFromFormat('Y-m', $period)->translatedFormat('F Y')); ?></p>
</div>

<?php
    $fmt = fn($n) => number_format(abs($n), 0, ',', '.');
?>


<div class="summary">
    <div class="summary-cell">
        <div class="val">Rp <?php echo e($fmt($total_budget)); ?></div>
        <div class="lbl">Total Anggaran</div>
    </div>
    <div class="summary-cell">
        <div class="val">Rp <?php echo e($fmt($total_realized)); ?></div>
        <div class="lbl">Total Realisasi</div>
    </div>
    <div class="summary-cell">
        <div class="val <?php echo e($total_realized > $total_budget ? 'over' : 'ok'); ?>">
            Rp <?php echo e($fmt($total_budget - $total_realized)); ?>

        </div>
        <div class="lbl">Sisa Anggaran</div>
    </div>
    <div class="summary-cell">
        <div class="val <?php echo e($usage_pct > 100 ? 'over' : ($usage_pct >= 90 ? 'warn' : 'ok')); ?>">
            <?php echo e($usage_pct); ?>%
        </div>
        <div class="lbl">Penggunaan</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th style="width:25%">Nama Anggaran</th>
            <th style="width:12%">Departemen</th>
            <th style="width:12%">Kategori</th>
            <th style="width:14%">Anggaran (Rp)</th>
            <th style="width:14%">Realisasi (Rp)</th>
            <th style="width:12%">Selisih (Rp)</th>
            <th style="width:7%">%</th>
            <th style="width:10%">Status</th>
        </tr>
    </thead>
    <tbody>
        <?php $currentDept = null; ?>
        <?php $__empty_1 = true; $__currentLoopData = $budgets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php if($b->department && $b->department !== $currentDept): ?>
                <?php $currentDept = $b->department; ?>
                <tr class="dept-row">
                    <td colspan="8"><?php echo e(strtoupper($b->department)); ?></td>
                </tr>
            <?php endif; ?>
            <?php
                $variance = $b->amount - $b->realized;
                $pct      = $b->amount > 0 ? round($b->realized / $b->amount * 100, 1) : 0;
                $isOver   = $b->realized > $b->amount;
                $isWarn   = !$isOver && $pct >= 90;
                $fillColor = $isOver ? '#dc2626' : ($isWarn ? '#d97706' : '#16a34a');
                $fillWidth = min($pct, 100);
            ?>
            <tr>
                <td><?php echo e($b->name); ?></td>
                <td><?php echo e($b->department ?? '-'); ?></td>
                <td><?php echo e($b->category ?? '-'); ?></td>
                <td><?php echo e($fmt($b->amount)); ?></td>
                <td class="<?php echo e($isOver ? 'over' : ''); ?>"><?php echo e($fmt($b->realized)); ?></td>
                <td class="<?php echo e($isOver ? 'over' : ($isWarn ? 'warn' : 'ok')); ?>">
                    <?php echo e($isOver ? '(' . $fmt(abs($variance)) . ')' : $fmt($variance)); ?>

                </td>
                <td>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width:<?php echo e($fillWidth); ?>%;background:<?php echo e($fillColor); ?>;"></div>
                    </div>
                    <?php echo e($pct); ?>%
                </td>
                <td>
                    <?php if($isOver): ?>
                        <span class="badge-over">OVER</span>
                    <?php elseif($isWarn): ?>
                        <span class="badge-warn">HAMPIR</span>
                    <?php else: ?>
                        <span class="badge-ok">NORMAL</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="8" style="text-align:center;color:#9ca3af;padding:12px;">Tidak ada data anggaran untuk periode ini.</td></tr>
        <?php endif; ?>
    </tbody>
    <?php if($budgets->isNotEmpty()): ?>
    <tfoot>
        <tr>
            <td colspan="3">TOTAL</td>
            <td><?php echo e($fmt($total_budget)); ?></td>
            <td><?php echo e($fmt($total_realized)); ?></td>
            <td><?php echo e($fmt($total_budget - $total_realized)); ?></td>
            <td><?php echo e($usage_pct); ?>%</td>
            <td><?php echo e($over_count); ?> over</td>
        </tr>
    </tfoot>
    <?php endif; ?>
</table>

<div class="footer">Dicetak pada <?php echo e(now()->translatedFormat('d F Y H:i')); ?> — Qalcuity ERP</div>
</body>
</html>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\reports\budget-pdf.blade.php ENDPATH**/ ?>