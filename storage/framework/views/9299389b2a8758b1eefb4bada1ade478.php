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
    .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 9px; font-weight: bold; }
    .badge-ok { background: #dcfce7; color: #166534; }
    .badge-err { background: #fee2e2; color: #991b1b; }
    .grid { display: table; width: 100%; }
    .col { display: table-cell; width: 50%; vertical-align: top; padding-right: 8px; }
    .col:last-child { padding-right: 0; padding-left: 8px; }
    .section-title { font-size: 9px; font-weight: bold; text-transform: uppercase; color: #6b7280; background: #f3f4f6; padding: 5px 8px; border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb; margin-bottom: 0; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
    table td { padding: 4px 8px; border-bottom: 1px solid #f3f4f6; }
    table td:last-child { text-align: right; }
    .subtotal td { font-weight: bold; background: #f9fafb; border-top: 1px solid #e5e7eb; }
    .total-row { background: #1e40af; color: #fff; }
    .total-row td { padding: 6px 8px; font-weight: bold; font-size: 11px; }
    .code { color: #9ca3af; font-size: 8px; margin-right: 4px; }
    .net-income { background: #f0fdf4; border: 1px solid #bbf7d0; }
    .net-income td { color: #166534; font-style: italic; }
    .footer { margin-top: 20px; font-size: 8px; color: #9ca3af; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 8px; }
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
        <div style="font-size:14px;font-weight:bold;color:<?php echo e($lhColor); ?>;text-transform:uppercase;">Neraca</div>
        <div style="font-size:9px;color:#6b7280;">Balance Sheet</div>
    </div>
</div>
<?php endif; ?>
<div class="header">
    <h1>NERACA (BALANCE SHEET)</h1>
    <p><?php echo e($tenant?->name ?? 'Qalcuity ERP'); ?></p>
    <p>Per Tanggal: <?php echo e(\Carbon\Carbon::parse($asOf)->translatedFormat('d F Y')); ?></p>
    <br>
    <span class="badge <?php echo e($data['is_balanced'] ? 'badge-ok' : 'badge-err'); ?>">
        <?php echo e($data['is_balanced'] ? '✓ Balance' : '✗ Tidak Balance'); ?>

    </span>
</div>

<?php $fmt = fn($n) => number_format(abs($n), 0, ',', '.'); ?>

<div class="grid">
    
    <div class="col">
        <div class="section-title">ASET</div>

        <div class="section-title" style="background:#eff6ff;color:#1d4ed8;">Aset Lancar</div>
        <table>
            <?php $__currentLoopData = $data['assets']['current']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr><td><span class="code"><?php echo e($acc['code']); ?></span><?php echo e($acc['name']); ?></td><td><?php echo e($fmt($acc['balance'])); ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <tr class="subtotal"><td>Total Aset Lancar</td><td><?php echo e($fmt($data['assets']['current']->sum('balance'))); ?></td></tr>
        </table>

        <div class="section-title" style="background:#eff6ff;color:#1d4ed8;">Aset Tidak Lancar</div>
        <table>
            <?php $__currentLoopData = $data['assets']['non_current']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr><td><span class="code"><?php echo e($acc['code']); ?></span><?php echo e($acc['name']); ?></td><td><?php echo e($fmt($acc['balance'])); ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if($data['assets']['non_current']->isEmpty()): ?>
            <tr><td colspan="2" style="color:#9ca3af;text-align:center;">-</td></tr>
            <?php endif; ?>
            <tr class="subtotal"><td>Total Aset Tidak Lancar</td><td><?php echo e($fmt($data['assets']['non_current']->sum('balance'))); ?></td></tr>
        </table>

        <table>
            <tr class="total-row"><td>TOTAL ASET</td><td>Rp <?php echo e($fmt($data['total_assets'])); ?></td></tr>
        </table>
    </div>

    
    <div class="col">
        <div class="section-title">KEWAJIBAN & EKUITAS</div>

        <div class="section-title" style="background:#fef3c7;color:#92400e;">Kewajiban Lancar</div>
        <table>
            <?php $__currentLoopData = $data['liabilities']['current']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr><td><span class="code"><?php echo e($acc['code']); ?></span><?php echo e($acc['name']); ?></td><td><?php echo e($fmt($acc['balance'])); ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <tr class="subtotal"><td>Total Kewajiban Lancar</td><td><?php echo e($fmt($data['liabilities']['current']->sum('balance'))); ?></td></tr>
        </table>

        <?php if($data['liabilities']['long_term']->isNotEmpty()): ?>
        <div class="section-title" style="background:#fef3c7;color:#92400e;">Kewajiban Jangka Panjang</div>
        <table>
            <?php $__currentLoopData = $data['liabilities']['long_term']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr><td><span class="code"><?php echo e($acc['code']); ?></span><?php echo e($acc['name']); ?></td><td><?php echo e($fmt($acc['balance'])); ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <tr class="subtotal"><td>Total Kewajiban Jangka Panjang</td><td><?php echo e($fmt($data['liabilities']['long_term']->sum('balance'))); ?></td></tr>
        </table>
        <?php endif; ?>

        <div class="section-title" style="background:#f0fdf4;color:#166534;">Ekuitas</div>
        <table>
            <?php $__currentLoopData = $data['equity']['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr><td><span class="code"><?php echo e($acc['code']); ?></span><?php echo e($acc['name']); ?></td><td><?php echo e($fmt($acc['balance'])); ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <tr class="net-income">
                <td>Laba/Rugi Tahun Berjalan</td>
                <td><?php echo e($data['net_income'] < 0 ? '(' : ''); ?><?php echo e($fmt($data['net_income'])); ?><?php echo e($data['net_income'] < 0 ? ')' : ''); ?></td>
            </tr>
            <tr class="subtotal"><td>Total Ekuitas</td><td><?php echo e($fmt($data['equity']['total'] + $data['net_income'])); ?></td></tr>
        </table>

        <table>
            <tr class="total-row"><td>TOTAL KEWAJIBAN & EKUITAS</td><td>Rp <?php echo e($fmt($data['total_l_e'])); ?></td></tr>
        </table>
    </div>
</div>

<div class="footer">Dicetak pada <?php echo e(now()->translatedFormat('d F Y H:i')); ?> — Qalcuity ERP</div>
</body>
</html>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\accounting\pdf\balance-sheet.blade.php ENDPATH**/ ?>