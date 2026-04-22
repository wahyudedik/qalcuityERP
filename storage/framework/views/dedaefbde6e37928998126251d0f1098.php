<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwitansi Check-out — <?php echo e($receipt->receipt_number); ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { font-size: 18px; margin: 0 0 5px; }
        .header p { margin: 2px 0; font-size: 11px; color: #666; }
        .receipt-number { font-size: 14px; font-weight: bold; color: #2563eb; }
        .section { margin-bottom: 15px; }
        .section-title { font-weight: bold; font-size: 11px; text-transform: uppercase; color: #666; border-bottom: 1px solid #ddd; padding-bottom: 4px; margin-bottom: 8px; }
        .row { display: flex; justify-content: space-between; margin-bottom: 4px; }
        .row .label { color: #666; }
        .row .value { font-weight: 500; }
        .total-row { display: flex; justify-content: space-between; font-size: 14px; font-weight: bold; border-top: 2px solid #333; padding-top: 8px; margin-top: 8px; }
        .footer { text-align: center; margin-top: 20px; font-size: 10px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; }
        .badge-paid { background: #dcfce7; color: #166534; }
        .badge-partial { background: #fef9c3; color: #854d0e; }
    </style>
</head>
<body>
    <div class="header">
        <h1>KWITANSI CHECK-OUT</h1>
        <p class="receipt-number"><?php echo e($receipt->receipt_number); ?></p>
        <p><?php echo e(now()->format('d F Y, H:i')); ?> WIB</p>
    </div>

    <div class="section">
        <div class="section-title">Informasi Tamu</div>
        <div class="row">
            <span class="label">Nama Tamu</span>
            <span class="value"><?php echo e($reservation->guest?->name ?? '-'); ?></span>
        </div>
        <div class="row">
            <span class="label">No. Reservasi</span>
            <span class="value"><?php echo e($reservation->reservation_number); ?></span>
        </div>
        <div class="row">
            <span class="label">Kamar</span>
            <span class="value"><?php echo e($reservation->room?->number ?? '-'); ?></span>
        </div>
        <div class="row">
            <span class="label">Tipe Kamar</span>
            <span class="value"><?php echo e($reservation->roomType?->name ?? '-'); ?></span>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Detail Menginap</div>
        <div class="row">
            <span class="label">Check-in</span>
            <span class="value"><?php echo e(\Carbon\Carbon::parse($reservation->check_in_date)->format('d M Y')); ?></span>
        </div>
        <div class="row">
            <span class="label">Check-out</span>
            <span class="value"><?php echo e(\Carbon\Carbon::parse($reservation->check_out_date)->format('d M Y')); ?></span>
        </div>
        <div class="row">
            <span class="label">Jumlah Malam</span>
            <span class="value"><?php echo e($reservation->nights); ?> malam</span>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Rincian Tagihan</div>
        <div class="row">
            <span class="label">Biaya Kamar (<?php echo e($charges['nights'] ?? $reservation->nights); ?> malam × Rp <?php echo e(number_format($charges['rate_per_night'] ?? $reservation->rate_per_night, 0, ',', '.')); ?>)</span>
            <span class="value">Rp <?php echo e(number_format($charges['room_charge'] ?? $reservation->total_amount, 0, ',', '.')); ?></span>
        </div>
        <?php if(!empty($charges['minibar_charges']) && $charges['minibar_charges'] > 0): ?>
        <div class="row">
            <span class="label">Minibar</span>
            <span class="value">Rp <?php echo e(number_format($charges['minibar_charges'], 0, ',', '.')); ?></span>
        </div>
        <?php endif; ?>
        <?php if(!empty($charges['additional_charges']) && $charges['additional_charges'] > 0): ?>
        <div class="row">
            <span class="label">Biaya Tambahan</span>
            <span class="value">Rp <?php echo e(number_format($charges['additional_charges'], 0, ',', '.')); ?></span>
        </div>
        <?php endif; ?>
        <?php if(!empty($charges['discount']) && $charges['discount'] > 0): ?>
        <div class="row">
            <span class="label">Diskon</span>
            <span class="value" style="color: #16a34a;">- Rp <?php echo e(number_format($charges['discount'], 0, ',', '.')); ?></span>
        </div>
        <?php endif; ?>
        <div class="row">
            <span class="label">Pajak (<?php echo e($charges['tax_rate'] ?? 11); ?>%)</span>
            <span class="value">Rp <?php echo e(number_format($charges['tax_amount'] ?? $reservation->tax, 0, ',', '.')); ?></span>
        </div>
        <?php if(!empty($charges['deposit_paid']) && $charges['deposit_paid'] > 0): ?>
        <div class="row">
            <span class="label">Deposit Dibayar</span>
            <span class="value" style="color: #16a34a;">- Rp <?php echo e(number_format($charges['deposit_paid'], 0, ',', '.')); ?></span>
        </div>
        <?php endif; ?>
        <div class="total-row">
            <span>TOTAL</span>
            <span>Rp <?php echo e(number_format($receipt->grand_total, 0, ',', '.')); ?></span>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Pembayaran</div>
        <div class="row">
            <span class="label">Metode Pembayaran</span>
            <span class="value"><?php echo e(strtoupper(str_replace('_', ' ', $receipt->payment_method))); ?></span>
        </div>
        <div class="row">
            <span class="label">Jumlah Dibayar</span>
            <span class="value">Rp <?php echo e(number_format($receipt->amount_paid, 0, ',', '.')); ?></span>
        </div>
        <?php if($receipt->change_amount > 0): ?>
        <div class="row">
            <span class="label">Kembalian</span>
            <span class="value">Rp <?php echo e(number_format($receipt->change_amount, 0, ',', '.')); ?></span>
        </div>
        <?php endif; ?>
        <div class="row">
            <span class="label">Status</span>
            <span class="value">
                <span class="badge <?php echo e($receipt->payment_status === 'paid' ? 'badge-paid' : 'badge-partial'); ?>">
                    <?php echo e($receipt->payment_status === 'paid' ? 'LUNAS' : 'SEBAGIAN'); ?>

                </span>
            </span>
        </div>
        <?php if($receipt->transaction_reference): ?>
        <div class="row">
            <span class="label">Referensi</span>
            <span class="value"><?php echo e($receipt->transaction_reference); ?></span>
        </div>
        <?php endif; ?>
    </div>

    <div class="footer">
        <p>Terima kasih telah menginap bersama kami.</p>
        <p>Kwitansi ini dicetak secara otomatis oleh sistem.</p>
        <p><?php echo e($receipt->receipt_number); ?> | <?php echo e(now()->format('d/m/Y H:i')); ?></p>
    </div>
</body>
</html>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hotel\check-out\receipt.blade.php ENDPATH**/ ?>