<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1a1a1a; background: #fff; }
    .header { text-align: center; border-bottom: 2px solid #1e40af; padding-bottom: 10px; margin-bottom: 16px; }
    .header h1 { font-size: 16px; font-weight: bold; color: #1e40af; }
    .header p { font-size: 10px; color: #555; margin-top: 2px; }
    .section-title { font-size: 9px; font-weight: bold; text-transform: uppercase; padding: 5px 8px; border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb; margin-bottom: 0; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
    table td { padding: 4px 8px; border-bottom: 1px solid #f3f4f6; }
    table td:last-child { text-align: right; }
    table td:nth-child(3) { text-align: right; color: #9ca3af; font-size: 9px; width: 50px; }
    .subtotal td { font-weight: bold; background: #f9fafb; border-top: 1px solid #e5e7eb; }
    .summary-row td { padding: 6px 8px; font-weight: bold; }
    .code { color: #9ca3af; font-size: 8px; margin-right: 4px; }
    .footer { margin-top: 20px; font-size: 8px; color: #9ca3af; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 8px; }
    .net-laba { background: #f0fdf4; border: 2px solid #86efac; }
    .net-rugi { background: #fef2f2; border: 2px solid #fca5a5; }
    .net-laba td { color: #166534; font-size: 12px; font-weight: bold; }
    .net-rugi td { color: #991b1b; font-size: 12px; font-weight: bold; }
</style>
</head>
<body>
<?php
    $logoUrl = $tenant?->logo ? Storage::disk('public')->url($tenant->logo) : null;
    $lhColor = $tenant?->letter_head_color ?? '#1e40af';
?>
<?php if($logoUrl || ($tenant?->npwp) || ($tenant?->tagline)): ?>
<div style="border-bottom:3px solid <?php echo e($lhColor); ?>;padding-bottom:10px;margin-bottom:14px;display:flex;justify-content:space-between;align-items:flex-start;">
    <div style="display:flex;align-items:flex-start;">
        <?php if($logoUrl): ?><img src="<?php echo e($logoUrl); ?>" style="max-height:50px;max-width:130px;object-fit:contain;" alt="<?php echo e($tenant->name); ?>"><?php endif; ?>
        <div style="padding-left:<?php echo e($logoUrl ? '12px' : '0'); ?>;">
            <div style="font-size:14px;font-weight:bold;color:<?php echo e($lhColor); ?>;"><?php echo e($tenant->name); ?></div>
            <?php if($tenant->tagline): ?><div style="font-size:8px;color:#6b7280;"><?php echo e($tenant->tagline); ?></div><?php endif; ?>
            <div style="font-size:8px;color:#374151;margin-top:3px;line-height:1.5;">
                <?php if($tenant->address): ?><?php echo e($tenant->address); ?><?php if($tenant->city): ?>, <?php echo e($tenant->city); ?><?php endif; ?><br><?php endif; ?>
                <?php if($tenant->phone): ?>Telp: <?php echo e($tenant->phone); ?><?php endif; ?>
                <?php if($tenant->email): ?> | <?php echo e($tenant->email); ?><?php endif; ?>
            </div>
            <?php if($tenant->npwp): ?><div style="font-size:8px;color:#6b7280;">NPWP: <?php echo e($tenant->npwp); ?></div><?php endif; ?>
        </div>
    </div>
    <div style="text-align:right;">
        <div style="font-size:14px;font-weight:bold;color:<?php echo e($lhColor); ?>;text-transform:uppercase;">Laba Rugi</div>
        <div style="font-size:9px;color:#6b7280;">Income Statement</div>
    </div>
</div>
<?php endif; ?>
<div class="header">
    <h1>LAPORAN LABA RUGI</h1>
    <p><?php echo e($tenant?->name ?? 'Qalcuity ERP'); ?></p>
    <p>Periode: <?php echo e(\Carbon\Carbon::parse($from)->translatedFormat('d F Y')); ?> s/d <?php echo e(\Carbon\Carbon::parse($to)->translatedFormat('d F Y')); ?></p>
</div>

<?php
    $fmt = fn($n) => 'Rp ' . number_format(abs($n), 0, ',', '.');
    $pct = fn($part, $total) => $total > 0 ? round(($part / $total) * 100, 1) . '%' : '-';
?>


<div class="section-title" style="background:#f0fdf4;color:#166534;">PENDAPATAN</div>
<table>
    <?php $__currentLoopData = $data['revenue']['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <tr>
        <td><span class="code"><?php echo e($acc['code']); ?></span><?php echo e($acc['name']); ?></td>
        <td><?php echo e($fmt($acc['balance'])); ?></td>
        <td><?php echo e($pct($acc['balance'], $data['revenue']['total'])); ?></td>
    </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <tr class="subtotal"><td>Total Pendapatan</td><td><?php echo e($fmt($data['revenue']['total'])); ?></td><td></td></tr>
</table>


<?php if($data['cogs']['items']->isNotEmpty()): ?>
<div class="section-title" style="background:#f9fafb;color:#374151;">HARGA POKOK PENJUALAN</div>
<table>
    <?php $__currentLoopData = $data['cogs']['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <tr>
        <td><span class="code"><?php echo e($acc['code']); ?></span><?php echo e($acc['name']); ?></td>
        <td>(<?php echo e($fmt($acc['balance'])); ?>)</td>
        <td><?php echo e($pct($acc['balance'], $data['revenue']['total'])); ?></td>
    </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <tr class="subtotal"><td>Total HPP</td><td>(<?php echo e($fmt($data['cogs']['total'])); ?>)</td><td></td></tr>
</table>
<?php endif; ?>

<table>
    <tr class="summary-row" style="background:#eff6ff;">
        <td>LABA KOTOR</td>
        <td><?php echo e($data['gross_profit'] < 0 ? '(' : ''); ?><?php echo e($fmt($data['gross_profit'])); ?><?php echo e($data['gross_profit'] < 0 ? ')' : ''); ?></td>
        <td></td>
    </tr>
</table>


<?php if($data['opex']['items']->isNotEmpty()): ?>
<div class="section-title" style="background:#f9fafb;color:#374151;">BEBAN OPERASIONAL</div>
<table>
    <?php $__currentLoopData = $data['opex']['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <tr>
        <td><span class="code"><?php echo e($acc['code']); ?></span><?php echo e($acc['name']); ?></td>
        <td>(<?php echo e($fmt($acc['balance'])); ?>)</td>
        <td><?php echo e($pct($acc['balance'], $data['revenue']['total'])); ?></td>
    </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <tr class="subtotal"><td>Total Beban Operasional</td><td>(<?php echo e($fmt($data['opex']['total'])); ?>)</td><td></td></tr>
</table>
<?php endif; ?>

<table>
    <tr class="summary-row" style="background:#eff6ff;">
        <td>LABA OPERASI</td>
        <td><?php echo e($data['operating_income'] < 0 ? '(' : ''); ?><?php echo e($fmt($data['operating_income'])); ?><?php echo e($data['operating_income'] < 0 ? ')' : ''); ?></td>
        <td></td>
    </tr>
</table>


<?php if($data['other_expense']['items']->isNotEmpty()): ?>
<div class="section-title" style="background:#f9fafb;color:#374151;">BEBAN / PENDAPATAN LAIN-LAIN</div>
<table>
    <?php $__currentLoopData = $data['other_expense']['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <tr>
        <td><span class="code"><?php echo e($acc['code']); ?></span><?php echo e($acc['name']); ?></td>
        <td>(<?php echo e($fmt($acc['balance'])); ?>)</td>
        <td></td>
    </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</table>
<?php endif; ?>


<table>
    <tr class="<?php echo e($data['net_income'] >= 0 ? 'net-laba' : 'net-rugi'); ?>">
        <td><?php echo e($data['net_income'] >= 0 ? 'LABA BERSIH' : 'RUGI BERSIH'); ?></td>
        <td><?php echo e($data['net_income'] < 0 ? '(' : ''); ?><?php echo e($fmt($data['net_income'])); ?><?php echo e($data['net_income'] < 0 ? ')' : ''); ?></td>
        <td></td>
    </tr>
</table>

<div class="footer">Dicetak pada <?php echo e(now()->translatedFormat('d F Y H:i')); ?> — Qalcuity ERP</div>
</body>
</html>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\accounting\pdf\income-statement.blade.php ENDPATH**/ ?>