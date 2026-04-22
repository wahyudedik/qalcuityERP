<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembelian #<?php echo e($order->number); ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 480px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        .header { background: #1e293b; color: #fff; padding: 24px; text-align: center; }
        .header h1 { margin: 0 0 4px; font-size: 20px; }
        .header p { margin: 0; font-size: 13px; color: #94a3b8; }
        .badge { display: inline-block; background: #22c55e; color: #fff; border-radius: 20px; padding: 4px 14px; font-size: 12px; font-weight: 600; margin-top: 10px; }
        .body { padding: 24px; }
        .meta { font-size: 13px; color: #64748b; margin-bottom: 16px; }
        .meta span { display: block; margin-bottom: 4px; }
        .divider { border: none; border-top: 1px dashed #e2e8f0; margin: 16px 0; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th { text-align: left; color: #64748b; font-weight: 600; padding: 6px 0; border-bottom: 1px solid #e2e8f0; }
        td { padding: 8px 0; vertical-align: top; }
        td.right { text-align: right; }
        .totals { margin-top: 8px; }
        .totals tr td { padding: 4px 0; font-size: 13px; }
        .totals tr.grand td { font-size: 16px; font-weight: 700; color: #1e293b; border-top: 2px solid #e2e8f0; padding-top: 10px; }
        .payment-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 16px; margin-top: 16px; font-size: 13px; }
        .payment-box .label { color: #64748b; }
        .payment-box .value { font-weight: 600; color: #1e293b; }
        .footer { background: #f8fafc; padding: 20px 24px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
        .footer strong { color: #475569; }
    </style>
</head>
<body>
<div class="container">
    
    <div class="header">
        <h1><?php echo e($storeName); ?></h1>
        <?php if($storeAddress): ?>
            <p><?php echo e($storeAddress); ?></p>
        <?php endif; ?>
        <span class="badge">✓ Pembayaran Berhasil</span>
    </div>

    
    <div class="body">
        <div class="meta">
            <span><strong>No. Transaksi:</strong> #<?php echo e($order->number); ?></span>
            <span><strong>Tanggal:</strong> <?php echo e($order->date ? \Carbon\Carbon::parse($order->date)->format('d M Y, H:i') : now()->format('d M Y, H:i')); ?></span>
            <span><strong>Kasir:</strong> <?php echo e($order->user?->name ?? '-'); ?></span>
            <?php if($order->customer): ?>
                <span><strong>Pelanggan:</strong> <?php echo e($order->customer->name); ?></span>
            <?php endif; ?>
        </div>

        <hr class="divider">

        
        <table>
            <thead>
                <tr>
                    <th>Produk</th>
                    <th class="right">Qty</th>
                    <th class="right">Harga</th>
                    <th class="right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($item->product?->name ?? 'Produk'); ?></td>
                    <td class="right"><?php echo e($item->quantity); ?></td>
                    <td class="right">Rp <?php echo e(number_format($item->price, 0, ',', '.')); ?></td>
                    <td class="right">Rp <?php echo e(number_format($item->total, 0, ',', '.')); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>

        <hr class="divider">

        
        <table class="totals">
            <tr>
                <td>Subtotal</td>
                <td class="right">Rp <?php echo e(number_format($order->subtotal, 0, ',', '.')); ?></td>
            </tr>
            <?php if($order->discount > 0): ?>
            <tr>
                <td>Diskon</td>
                <td class="right" style="color:#ef4444">- Rp <?php echo e(number_format($order->discount, 0, ',', '.')); ?></td>
            </tr>
            <?php endif; ?>
            <?php if($order->tax > 0): ?>
            <tr>
                <td>Pajak</td>
                <td class="right">Rp <?php echo e(number_format($order->tax, 0, ',', '.')); ?></td>
            </tr>
            <?php endif; ?>
            <tr class="grand">
                <td>Total</td>
                <td class="right">Rp <?php echo e(number_format($order->total, 0, ',', '.')); ?></td>
            </tr>
        </table>

        
        <div class="payment-box">
            <div style="display:flex;justify-content:space-between;margin-bottom:4px">
                <span class="label">Metode Pembayaran</span>
                <span class="value"><?php echo e(strtoupper($order->payment_method ?? '-')); ?></span>
            </div>
            <?php if($order->paid_amount): ?>
            <div style="display:flex;justify-content:space-between;margin-bottom:4px">
                <span class="label">Dibayar</span>
                <span class="value">Rp <?php echo e(number_format($order->paid_amount, 0, ',', '.')); ?></span>
            </div>
            <?php endif; ?>
            <?php if($order->change_amount > 0): ?>
            <div style="display:flex;justify-content:space-between">
                <span class="label">Kembalian</span>
                <span class="value" style="color:#22c55e">Rp <?php echo e(number_format($order->change_amount, 0, ',', '.')); ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="footer">
        <strong><?php echo e($footerText); ?></strong><br>
        <span>Email ini dikirim otomatis oleh sistem <?php echo e($storeName); ?></span>
    </div>
</div>
</body>
</html>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\mail\pos-receipt.blade.php ENDPATH**/ ?>