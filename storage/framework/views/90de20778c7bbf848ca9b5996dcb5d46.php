<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1f2937; background: #fff; }
        .page { padding: 32px 36px; }

        /* Status badge */
        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 9999px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .status-unpaid  { background: #fee2e2; color: #991b1b; }
        .status-partial { background: #fef3c7; color: #92400e; }
        .status-paid    { background: #d1fae5; color: #065f46; }

        /* Divider */
        .divider { border: none; border-top: 1px solid #e5e7eb; margin: 16px 0; }

        /* Parties */
        .parties { display: flex; gap: 32px; margin-bottom: 24px; }
        .party { flex: 1; }
        .party-label { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #9ca3af; font-weight: 600; margin-bottom: 6px; }
        .party-name  { font-size: 13px; font-weight: 700; color: #111827; }
        .party-detail { font-size: 10px; color: #6b7280; margin-top: 2px; line-height: 1.5; }

        /* Dates */
        .dates { display: flex; gap: 16px; margin-bottom: 24px; }
        .date-box { flex: 1; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px 14px; }
        .date-box .label { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #9ca3af; font-weight: 600; }
        .date-box .value { font-size: 12px; font-weight: 600; color: #111827; margin-top: 3px; }

        /* Items table */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        thead tr { background: #1d4ed8; }
        th { padding: 9px 12px; text-align: left; font-size: 10px; text-transform: uppercase; color: #fff; font-weight: 600; letter-spacing: 0.5px; }
        th:last-child, td:last-child { text-align: right; }
        td { padding: 9px 12px; border-bottom: 1px solid #f3f4f6; font-size: 11px; color: #374151; }
        tr:nth-child(even) td { background: #f9fafb; }

        /* Totals */
        .totals { margin-left: auto; width: 260px; }
        .totals-row { display: flex; justify-content: space-between; padding: 5px 0; font-size: 11px; color: #6b7280; }
        .totals-row.grand { border-top: 2px solid #1d4ed8; margin-top: 6px; padding-top: 10px; font-size: 14px; font-weight: 700; color: #111827; }
        .totals-row.paid-row { color: #059669; }
        .totals-row.remaining-row { color: #dc2626; font-weight: 600; }

        /* Notes */
        .notes { background: #f9fafb; border-left: 3px solid #1d4ed8; padding: 10px 14px; border-radius: 0 6px 6px 0; margin-bottom: 16px; }
        .notes .label { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #9ca3af; font-weight: 600; margin-bottom: 4px; }
        .notes p { font-size: 11px; color: #374151; line-height: 1.5; }

        /* Payment history */
        .payments-section { margin-bottom: 16px; }
        .section-title { font-size: 11px; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
        .payment-row { display: flex; justify-content: space-between; padding: 6px 10px; background: #f0fdf4; border-radius: 6px; margin-bottom: 4px; font-size: 10px; color: #065f46; }

        /* Bank info */
        .bank-box { background:#f0fdf4; border:1px solid #bbf7d0; border-radius:6px; padding:10px 14px; margin-bottom:16px; font-size:10px; }
        .bank-box .blabel { font-size:9px; text-transform:uppercase; letter-spacing:1px; color:#9ca3af; font-weight:600; margin-bottom:4px; }

        /* Footer */
        .footer { border-top: 1px solid #e5e7eb; padding-top: 14px; display: flex; justify-content: space-between; align-items: flex-start; }
        .footer-left { font-size: 10px; color: #6b7280; line-height: 1.6; flex: 1; }
        .footer-stamp { text-align: center; padding: 0 20px; }
        .footer-stamp img { max-height: 70px; max-width: 100px; opacity: 0.85; }
        .footer-stamp p { font-size: 9px; color: #6b7280; margin-top: 4px; }
        .footer-right { text-align: right; font-size: 10px; color: #9ca3af; }
        .footer-brand { font-size: 11px; font-weight: 700; color: #1d4ed8; }

        /* Letterhead */
        .lh-wrap { border-bottom: 3px solid #1d4ed8; padding-bottom: 12px; margin-bottom: 14px; }
        .lh-inner { display: flex; justify-content: space-between; align-items: flex-start; }
        .lh-logo { max-height: 60px; max-width: 150px; object-fit: contain; }
        .lh-company { flex: 1; padding-left: 14px; }
        .lh-company-name { font-size: 16px; font-weight: bold; color: #1d4ed8; }
        .lh-company-tagline { font-size: 9px; color: #6b7280; margin-top: 1px; }
        .lh-company-detail { font-size: 9px; color: #374151; margin-top: 4px; line-height: 1.6; }
        .lh-npwp { font-size: 9px; color: #6b7280; margin-top: 2px; }
        .lh-doc-title { text-align: right; }
        .lh-doc-title h2 { font-size: 20px; font-weight: bold; color: #1d4ed8; text-transform: uppercase; letter-spacing: 1px; }
        .lh-doc-title .inv-num { font-size: 13px; font-weight: 600; color: #374151; margin-top: 4px; }
    </style>
</head>
<body>
<div class="page">

<?php
    $tenant = $invoice->tenant;
    $logoUrl = $tenant->logo ? Storage::disk('public')->url($tenant->logo) : null;
    $stampUrl = $tenant->stamp_image ? Storage::disk('public')->url($tenant->stamp_image) : null;
    $statusClass = match($invoice->status) { 'paid' => 'status-paid', 'partial' => 'status-partial', default => 'status-unpaid' };
    $statusLabel = match($invoice->status) { 'paid' => 'Lunas', 'partial' => 'Sebagian', default => 'Belum Dibayar' };
?>


<div class="lh-wrap">
    <div class="lh-inner">
        <div style="display:flex;align-items:flex-start;">
            <?php if($logoUrl): ?>
            <img src="<?php echo e($logoUrl); ?>" class="lh-logo" alt="<?php echo e($tenant->name); ?>">
            <?php endif; ?>
            <div class="lh-company" style="<?php echo e($logoUrl ? '' : 'padding-left:0'); ?>">
                <div class="lh-company-name"><?php echo e($tenant->name); ?></div>
                <?php if($tenant->tagline): ?><div class="lh-company-tagline"><?php echo e($tenant->tagline); ?></div><?php endif; ?>
                <div class="lh-company-detail">
                    <?php if($tenant->address): ?><?php echo e($tenant->address); ?><?php endif; ?>
                    <?php if($tenant->city): ?>, <?php echo e($tenant->city); ?><?php endif; ?>
                    <?php if($tenant->province): ?>, <?php echo e($tenant->province); ?><?php endif; ?>
                    <?php if($tenant->postal_code): ?> <?php echo e($tenant->postal_code); ?><?php endif; ?>
                    <?php if($tenant->phone): ?><br>Telp: <?php echo e($tenant->phone); ?><?php endif; ?>
                    <?php if($tenant->email): ?> | <?php echo e($tenant->email); ?><?php endif; ?>
                    <?php if($tenant->website): ?><br><?php echo e($tenant->website); ?><?php endif; ?>
                </div>
                <?php if($tenant->npwp): ?><div class="lh-npwp">NPWP: <?php echo e($tenant->npwp); ?></div><?php endif; ?>
            </div>
        </div>
        <div class="lh-doc-title">
            <h2>Invoice</h2>
            <div class="inv-num"><?php echo e($invoice->number); ?></div>
            <div style="margin-top:6px;"><span class="status-badge <?php echo e($statusClass); ?>"><?php echo e($statusLabel); ?></span></div>
        </div>
    </div>
</div>


<div class="parties">
    <div class="party">
        <div class="party-label">Dari</div>
        <div class="party-name"><?php echo e($tenant->name); ?></div>
        <?php if($tenant->address): ?><div class="party-detail"><?php echo e($tenant->address); ?><?php if($tenant->city): ?>, <?php echo e($tenant->city); ?><?php endif; ?></div><?php endif; ?>
        <?php if($tenant->npwp): ?><div class="party-detail">NPWP: <?php echo e($tenant->npwp); ?></div><?php endif; ?>
    </div>
    <div class="party">
        <div class="party-label">Kepada</div>
        <div class="party-name"><?php echo e($invoice->customer?->name ?? '-'); ?></div>
        <?php if($invoice->customer?->company): ?><div class="party-detail"><?php echo e($invoice->customer->company); ?></div><?php endif; ?>
        <?php if($invoice->customer?->address): ?><div class="party-detail"><?php echo e($invoice->customer->address); ?></div><?php endif; ?>
        <?php if($invoice->customer?->phone): ?><div class="party-detail"><?php echo e($invoice->customer->phone); ?></div><?php endif; ?>
        <?php if($invoice->customer?->email): ?><div class="party-detail"><?php echo e($invoice->customer->email); ?></div><?php endif; ?>
    </div>
</div>


<div class="dates">
    <div class="date-box">
        <div class="label">Tanggal Invoice</div>
        <div class="value"><?php echo e($invoice->created_at->format('d M Y')); ?></div>
    </div>
    <div class="date-box">
        <div class="label">Jatuh Tempo</div>
        <div class="value" style="<?php echo e($invoice->status !== 'paid' && $invoice->due_date && $invoice->due_date < now() ? 'color:#dc2626' : ''); ?>">
            <?php echo e($invoice->due_date?->format('d M Y') ?? '-'); ?>

        </div>
    </div>
    <?php if($invoice->salesOrder): ?>
    <div class="date-box">
        <div class="label">No. Sales Order</div>
        <div class="value"><?php echo e($invoice->salesOrder->number); ?></div>
    </div>
    <?php endif; ?>
</div>


<?php if($invoice->salesOrder && $invoice->salesOrder->items->count()): ?>
<table>
    <thead>
        <tr>
            <th style="width:40%">Produk / Layanan</th>
            <th style="width:10%;text-align:center">Qty</th>
            <th style="width:10%;text-align:center">Satuan</th>
            <th style="width:20%;text-align:right">Harga Satuan</th>
            <th style="width:20%;text-align:right">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        <?php $__currentLoopData = $invoice->salesOrder->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td><?php echo e($item->product?->name ?? '-'); ?></td>
            <td style="text-align:center"><?php echo e($item->quantity); ?></td>
            <td style="text-align:center"><?php echo e($item->product?->unit ?? 'pcs'); ?></td>
            <td style="text-align:right">Rp <?php echo e(number_format($item->price, 0, ',', '.')); ?></td>
            <td style="text-align:right">Rp <?php echo e(number_format($item->total, 0, ',', '.')); ?></td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<div class="totals">
    <?php if($invoice->salesOrder->discount > 0): ?>
    <div class="totals-row"><span>Subtotal</span><span>Rp <?php echo e(number_format($invoice->salesOrder->subtotal, 0, ',', '.')); ?></span></div>
    <div class="totals-row"><span>Diskon</span><span>- Rp <?php echo e(number_format($invoice->salesOrder->discount, 0, ',', '.')); ?></span></div>
    <?php endif; ?>
    <?php if($invoice->salesOrder->tax > 0): ?>
    <div class="totals-row"><span>Pajak</span><span>Rp <?php echo e(number_format($invoice->salesOrder->tax, 0, ',', '.')); ?></span></div>
    <?php endif; ?>
    <div class="totals-row grand"><span>Total</span><span>Rp <?php echo e(number_format($invoice->total_amount, 0, ',', '.')); ?></span></div>
    <?php if($invoice->paid_amount > 0): ?>
    <div class="totals-row paid-row"><span>Terbayar</span><span>- Rp <?php echo e(number_format($invoice->paid_amount, 0, ',', '.')); ?></span></div>
    <div class="totals-row remaining-row"><span>Sisa Tagihan</span><span>Rp <?php echo e(number_format($invoice->remaining_amount, 0, ',', '.')); ?></span></div>
    <?php endif; ?>
</div>
<?php else: ?>
<div class="totals" style="margin-bottom:20px">
    <div class="totals-row grand"><span>Total Tagihan</span><span>Rp <?php echo e(number_format($invoice->total_amount, 0, ',', '.')); ?></span></div>
    <?php if($invoice->paid_amount > 0): ?>
    <div class="totals-row paid-row"><span>Terbayar</span><span>- Rp <?php echo e(number_format($invoice->paid_amount, 0, ',', '.')); ?></span></div>
    <div class="totals-row remaining-row"><span>Sisa Tagihan</span><span>Rp <?php echo e(number_format($invoice->remaining_amount, 0, ',', '.')); ?></span></div>
    <?php endif; ?>
</div>
<?php endif; ?>


<?php if($tenant->bank_name && $tenant->bank_account): ?>
<div class="bank-box">
    <div class="blabel">Informasi Pembayaran</div>
    Bank: <?php echo e($tenant->bank_name); ?> &nbsp;|&nbsp; No. Rekening: <?php echo e($tenant->bank_account); ?>

    <?php if($tenant->bank_account_name): ?> &nbsp;|&nbsp; A/N: <?php echo e($tenant->bank_account_name); ?><?php endif; ?>
</div>
<?php endif; ?>


<?php if($invoice->payments && $invoice->payments->count()): ?>
<div class="payments-section">
    <div class="section-title">Riwayat Pembayaran</div>
    <?php $__currentLoopData = $invoice->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pay): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="payment-row">
        <span><?php echo e($pay->payment_date?->format('d M Y') ?? '-'); ?> &nbsp;·&nbsp; <?php echo e(strtoupper($pay->payment_method ?? '-')); ?></span>
        <span>Rp <?php echo e(number_format($pay->amount, 0, ',', '.')); ?></span>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<?php $footerNotes = $invoice->notes ?: $tenant->invoice_footer_notes; ?>
<?php if($footerNotes): ?>
<div class="notes">
    <div class="label">Catatan</div>
    <p><?php echo e($footerNotes); ?></p>
</div>
<?php endif; ?>
<?php if($tenant->invoice_payment_terms): ?>
<div class="notes" style="border-left-color:#f59e0b;">
    <div class="label">Syarat Pembayaran</div>
    <p><?php echo e($tenant->invoice_payment_terms); ?></p>
</div>
<?php endif; ?>


<div class="footer">
    <div class="footer-left">
        Terima kasih atas kepercayaan Anda.<br>
        Dicetak: <?php echo e(now()->format('d M Y H:i')); ?>

    </div>
    <?php if($stampUrl): ?>
    <div class="footer-stamp">
        <img src="<?php echo e($stampUrl); ?>" alt="Stempel">
        <p><?php echo e($tenant->name); ?></p>
    </div>
    <?php endif; ?>
    <div class="footer-right">
        <?php
            $dirSigUrl = $tenant->director_signature ? Storage::disk('public')->url($tenant->director_signature) : null;
            $digitalSigs = \App\Models\DigitalSignature::where('model_type', 'App\\Models\\Invoice')
                ->where('model_id', $invoice->id)
                ->with('user')
                ->latest('signed_at')
                ->limit(2)
                ->get();
        ?>
        <?php if($dirSigUrl): ?>
        <div style="margin-bottom:8px;">
            <img src="<?php echo e($dirSigUrl); ?>" alt="TTD" style="max-height:50px;max-width:120px;opacity:0.9;">
        </div>
        <?php endif; ?>
        <?php $__currentLoopData = $digitalSigs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sig): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div style="margin-bottom:4px;">
            <img src="<?php echo e($sig->signature_data); ?>" alt="TTD Digital" style="max-height:40px;max-width:100px;border:1px solid #e5e7eb;border-radius:4px;">
            <div style="font-size:8px;color:#9ca3af;"><?php echo e($sig->user?->name); ?> · <?php echo e($sig->signed_at?->format('d/m/Y H:i')); ?></div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <div class="footer-brand"><?php echo e($tenant->name); ?></div>
        <?php if($tenant->npwp): ?><div>NPWP: <?php echo e($tenant->npwp); ?></div><?php endif; ?>
    </div>
</div>

</div>
</body>
</html>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\invoices\pdf.blade.php ENDPATH**/ ?>