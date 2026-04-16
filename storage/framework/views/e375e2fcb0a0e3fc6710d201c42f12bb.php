<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sertifikat Keaslian Produk</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 13px;
            color: #1a1a1a;
            background: #fff;
            padding: 40px;
        }

        .certificate {
            border: 2px solid #2c5f2e;
            border-radius: 8px;
            padding: 36px 40px;
            max-width: 680px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            border-bottom: 1px solid #d0d0d0;
            padding-bottom: 20px;
            margin-bottom: 28px;
        }

        .header h1 {
            font-size: 20px;
            color: #2c5f2e;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .header p {
            font-size: 11px;
            color: #666;
        }

        .cert-number {
            text-align: center;
            background: #f4f9f4;
            border: 1px solid #b8d8b8;
            border-radius: 4px;
            padding: 10px 16px;
            margin-bottom: 28px;
            font-size: 15px;
            font-weight: bold;
            color: #2c5f2e;
            letter-spacing: 1px;
        }

        .body {
            display: table;
            width: 100%;
        }

        .info-section {
            display: table-cell;
            vertical-align: top;
            width: 60%;
            padding-right: 24px;
        }

        .qr-section {
            display: table-cell;
            vertical-align: top;
            width: 40%;
            text-align: center;
        }

        .field {
            margin-bottom: 16px;
        }

        .field-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #888;
            margin-bottom: 3px;
        }

        .field-value {
            font-size: 14px;
            color: #1a1a1a;
            font-weight: bold;
        }

        .field-value.sku {
            font-family: DejaVu Sans Mono, Courier New, monospace;
            font-size: 13px;
        }

        .qr-image {
            width: 160px;
            height: 160px;
            border: 1px solid #e0e0e0;
            padding: 6px;
            border-radius: 4px;
        }

        .qr-label {
            font-size: 10px;
            color: #888;
            margin-top: 6px;
        }

        .qr-placeholder {
            width: 160px;
            height: 160px;
            border: 1px dashed #ccc;
            display: inline-block;
            line-height: 160px;
            text-align: center;
            font-size: 11px;
            color: #bbb;
            border-radius: 4px;
        }

        .footer {
            border-top: 1px solid #d0d0d0;
            margin-top: 28px;
            padding-top: 14px;
            text-align: center;
            font-size: 10px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="header">
            <h1>Sertifikat Keaslian Produk</h1>
            <p>Certificate of Product Authenticity</p>
        </div>

        <div class="cert-number">
            <?php echo e($certificate->certificate_number); ?>

        </div>

        <div class="body">
            <div class="info-section">
                <div class="field">
                    <div class="field-label">Nama Produk</div>
                    <div class="field-value"><?php echo e($product->name); ?></div>
                </div>

                <div class="field">
                    <div class="field-label">SKU</div>
                    <div class="field-value sku"><?php echo e($product->sku); ?></div>
                </div>

                <div class="field">
                    <div class="field-label">Penerbit / Tenant</div>
                    <div class="field-value"><?php echo e($tenant->name); ?></div>
                </div>

                <div class="field">
                    <div class="field-label">Tanggal Terbit</div>
                    <div class="field-value"><?php echo e($certificate->issued_at->translatedFormat('d F Y')); ?></div>
                </div>

                <?php if($certificate->expires_at): ?>
                <div class="field">
                    <div class="field-label">Berlaku Hingga</div>
                    <div class="field-value"><?php echo e($certificate->expires_at->translatedFormat('d F Y')); ?></div>
                </div>
                <?php endif; ?>
            </div>

            <div class="qr-section">
                <?php if($qrBase64): ?>
                    <img src="<?php echo e($qrBase64); ?>" class="qr-image" alt="QR Code Verifikasi">
                    <div class="qr-label">Scan untuk verifikasi</div>
                <?php else: ?>
                    <span class="qr-placeholder">QR Code<br>tidak tersedia</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="footer">
            Dokumen ini diterbitkan secara digital dan dapat diverifikasi melalui sistem.
            Certificate Hash: <?php echo e(substr($certificate->certificate_hash, 0, 16)); ?>...
        </div>
    </div>
</body>
</html>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/certificates/pdf.blade.php ENDPATH**/ ?>