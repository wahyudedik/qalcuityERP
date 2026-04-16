<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>QR Labels - A4</title>
    <style>
        @page {
            size: A4;
            margin: 10mm 7mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        .sheet {
            width: 210mm;
        }
        .labels-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0;
        }
        /* 3 columns x 7 rows = 21 labels per sheet, each 63.5mm x 38.1mm */
        .label {
            width: 63.5mm;
            height: 38.1mm;
            padding: 2mm 3mm;
            border: 0.5pt dashed #ccc;
            page-break-inside: avoid;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .label.empty {
            border-color: transparent;
        }
        .qr-img {
            width: 22mm;
            height: 22mm;
            display: block;
            margin: 0 auto;
        }
        .product-name {
            font-size: 7pt;
            font-weight: bold;
            text-align: center;
            margin-top: 1mm;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .sku {
            font-size: 6pt;
            font-family: 'Courier New', monospace;
            text-align: center;
            color: #333;
            margin-top: 0.5mm;
        }
        .cert-number {
            font-size: 5.5pt;
            text-align: center;
            color: #555;
            margin-top: 0.5mm;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 100%;
        }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="labels-grid">
            @foreach ($labels as $item)
                @if ($item)
                    <div class="label">
                        @if ($item['qr_image'])
                            <img src="data:image/png;base64,{{ $item['qr_image'] }}" class="qr-img" alt="QR">
                        @endif
                        <div class="product-name" title="{{ $item['product']->name }}">
                            {{ $item['product']->name }}
                        </div>
                        <div class="sku">{{ $item['product']->sku }}</div>
                        @if ($item['certificate_number'])
                            <div class="cert-number">{{ $item['certificate_number'] }}</div>
                        @endif
                    </div>
                @else
                    <div class="label empty"></div>
                @endif
            @endforeach
        </div>
    </div>
</body>
</html>
