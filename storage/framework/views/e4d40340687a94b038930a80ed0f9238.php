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
    .subtotal td { font-weight: bold; background: #f9fafb; border-top: 1px solid #e5e7eb; }
    .indent td:first-child { padding-left: 20px; color: #6b7280; font-size: 9px; }
    .summary-row td { padding: 6px 8px; font-weight: bold; }
    .total-row { background: #1e40af; color: #fff; }
    .total-row td { padding: 8px; font-weight: bold; font-size: 12px; }
    .footer { margin-top: 20px; font-size: 8px; color: #9ca3af; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 8px; }
    .neg { color: #dc2626; }
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
        <div style="font-size:14px;font-weight:bold;color:<?php echo e($lhColor); ?>;text-transform:uppercase;">Arus Kas</div>
        <div style="font-size:9px;color:#6b7280;">Cash Flow Statement</div>
    </div>
</div>
<?php endif; ?>
<div class="header">
    <h1>LAPORAN ARUS KAS</h1>
    <p><?php echo e($tenant?->name ?? 'Qalcuity ERP'); ?></p>
    <p>Periode: <?php echo e(\Carbon\Carbon::parse($from)->translatedFormat('d F Y')); ?> s/d <?php echo e(\Carbon\Carbon::parse($to)->translatedFormat('d F Y')); ?></p>
    <p style="margin-top:4px;font-size:9px;color:#6b7280;">Metode Tidak Langsung (Indirect Method)</p>
</div>

<?php
    $fmt = fn($n) => ($n < 0 ? '(' : '') . 'Rp ' . number_format(abs($n), 0, ',', '.') . ($n < 0 ? ')' : '');
    $cls = fn($n) => $n < 0 ? 'neg' : '';
?>


<table>
    <tr class="summary-row" style="background:#f3f4f6;">
        <td>Saldo Kas Awal Periode</td>
        <td class="<?php echo e($cls($data['opening_cash'])); ?>"><?php echo e($fmt($data['opening_cash'])); ?></td>
    </tr>
</table>


<div class="section-title" style="background:#eff6ff;color:#1d4ed8;">I. ARUS KAS DARI AKTIVITAS OPERASI</div>
<table>
    <tr><td>Laba/Rugi Bersih</td><td class="<?php echo e($cls($data['operating']['net_income'])); ?>"><?php echo e($fmt($data['operating']['net_income'])); ?></td></tr>
    <?php $__currentLoopData = $data['operating']['wc_adjustments']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <tr class="indent"><td><?php echo e($item['label']); ?></td><td class="<?php echo e($cls($item['amount'])); ?>"><?php echo e($fmt($item['amount'])); ?></td></tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <tr class="subtotal"><td>Arus Kas Bersih dari Operasi</td><td class="<?php echo e($cls($data['operating']['total'])); ?>"><?php echo e($fmt($data['operating']['total'])); ?></td></tr>
</table>


<div class="section-title" style="background:#faf5ff;color:#7e22ce;">II. ARUS KAS DARI AKTIVITAS INVESTASI</div>
<table>
    <?php $__empty_1 = true; $__currentLoopData = $data['investing']['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <tr><td><?php echo e($item['label']); ?></td><td class="<?php echo e($cls($item['amount'])); ?>"><?php echo e($fmt($item['amount'])); ?></td></tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <tr><td colspan="2" style="color:#9ca3af;text-align:center;padding:6px;">Tidak ada aktivitas investasi</td></tr>
    <?php endif; ?>
    <tr class="subtotal"><td>Arus Kas Bersih dari Investasi</td><td class="<?php echo e($cls($data['investing']['total'])); ?>"><?php echo e($fmt($data['investing']['total'])); ?></td></tr>
</table>


<div class="section-title" style="background:#fff7ed;color:#c2410c;">III. ARUS KAS DARI AKTIVITAS PENDANAAN</div>
<table>
    <?php $__empty_1 = true; $__currentLoopData = $data['financing']['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <tr><td><?php echo e($item['label']); ?></td><td class="<?php echo e($cls($item['amount'])); ?>"><?php echo e($fmt($item['amount'])); ?></td></tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <tr><td colspan="2" style="color:#9ca3af;text-align:center;padding:6px;">Tidak ada aktivitas pendanaan</td></tr>
    <?php endif; ?>
    <tr class="subtotal"><td>Arus Kas Bersih dari Pendanaan</td><td class="<?php echo e($cls($data['financing']['total'])); ?>"><?php echo e($fmt($data['financing']['total'])); ?></td></tr>
</table>


<table>
    <tr class="summary-row" style="background:#f3f4f6;">
        <td>Kenaikan (Penurunan) Kas Bersih</td>
        <td class="<?php echo e($cls($data['net_change'])); ?>"><?php echo e($fmt($data['net_change'])); ?></td>
    </tr>
    <tr class="summary-row" style="background:#f3f4f6;">
        <td>Saldo Kas Awal Periode</td>
        <td><?php echo e($fmt($data['opening_cash'])); ?></td>
    </tr>
    <tr class="total-row">
        <td>SALDO KAS AKHIR PERIODE</td>
        <td><?php echo e($fmt($data['closing_cash'])); ?></td>
    </tr>
</table>

<div class="footer">Dicetak pada <?php echo e(now()->translatedFormat('d F Y H:i')); ?> — Qalcuity ERP</div>
</body>
</html>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\accounting\pdf\cash-flow.blade.php ENDPATH**/ ?>