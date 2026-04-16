<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate Labels - Thermal</title>
    <style>
        @page {
            size: 50mm 25mm;
            margin: 0;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        .label {
            width: 48mm;
            height: 23mm;
            padding: 1mm;
            text-align: center;
            page-break-inside: avoid;
            border: 0.5pt solid #ccc;
            display: flex;
            flex-direction: row;
            align-items: center;
        }
        .qr-img {
            width: 18mm;
            height: 18mm;
            flex-shrink: 0;
        }
        .info {
            flex: 1;
            padding-left: 1mm;
            text-align: left;
            overflow: hidden;
        }
        .product-name {
            font-size: 7pt;
            font-weight: bold;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .sku {
            font-size: 6pt;
            font-family: 'Courier New', monospace;
            color: #333;
            margin-top: 1px;
        }
        .cert-number {
            font-size: 5pt;
            color: #555;
            margin-top: 1px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    @foreach ($labels as $item)
        @if ($item)
            <div class="label">
                @if ($item['qr_image'])
                    <img src="data:image/png;base64,{{ $item['qr_image'] }}" class="qr-img" alt="QR">
                @endif
                <div class="info">
                    <div class="product-name">{{ $item['product']->name }}</div>
                    <div class="sku">{{ $item['product']->sku }}</div>
                    @if ($item['certificate_number'])
                        <div class="cert-number">{{ $item['certificate_number'] }}</div>
                    @endif
                </div>
            </div>
        @endif
    @endforeach
</body>
</html>
