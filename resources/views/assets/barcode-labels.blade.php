<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Asset Barcode Labels</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: A4;
            margin: 10mm;
        }

        /* Batch mode - A4 grid (2 columns x N rows) */
        .labels-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8mm;
            width: 100%;
        }

        .label-batch {
            width: 95mm;
            height: 45mm;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 3mm;
            background: white;
            page-break-inside: avoid;
            position: relative;
        }

        .category-badge {
            position: absolute;
            top: 3mm;
            right: 3mm;
            padding: 1.5mm 3mm;
            border-radius: 3mm;
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .category-vehicle {
            background: #dbeafe;
            color: #1e40af;
        }

        .category-machine {
            background: #fed7aa;
            color: #9a3412;
        }

        .category-equipment {
            background: #d1fae5;
            color: #065f46;
        }

        .category-furniture {
            background: #e0e7ff;
            color: #3730a3;
        }

        .category-building {
            background: #f3e8ff;
            color: #6b21a8;
        }

        .asset-code {
            font-size: 14pt;
            font-weight: bold;
            color: #111827;
            margin-bottom: 2mm;
        }

        .asset-name {
            font-size: 9pt;
            color: #6b7280;
            margin-bottom: 2mm;
            line-height: 1.2;
            height: 11mm;
            overflow: hidden;
        }

        .barcode-container {
            text-align: center;
            margin: 2mm 0;
        }

        .barcode-image {
            max-width: 100%;
            height: auto;
            max-height: 18mm;
        }

        .barcode-value {
            font-family: 'Courier New', monospace;
            font-size: 9pt;
            letter-spacing: 1.5pt;
            color: #374151;
            margin-top: 1mm;
        }

        .location {
            position: absolute;
            bottom: 3mm;
            left: 3mm;
            right: 3mm;
            font-size: 7pt;
            color: #9ca3af;
            text-align: center;
        }

        /* Empty placeholder for padding */
        .label-empty {
            border: none;
            background: transparent;
        }
    </style>
</head>

<body>
    <div class="labels-grid">
        @foreach ($barcodes as $item)
            @if ($item)
                @php
                    $catClass = 'category-' . ($item['asset']->category ?? 'equipment');
                @endphp
                <div class="label-batch">
                    {{-- Category Badge --}}
                    <div class="category-badge {{ $catClass }}">
                        {{ ucfirst($item['asset']->category ?? 'Asset') }}
                    </div>

                    {{-- Asset Code & Name --}}
                    <div class="asset-code">{{ $item['asset']->asset_code }}</div>
                    <div class="asset-name">{{ $item['asset']->name }}</div>

                    {{-- Barcode Image --}}
                    <div class="barcode-container">
                        <img src="data:image/png;base64,{{ $item['image'] }}" alt="{{ $item['value'] }}"
                            class="barcode-image">
                        <div class="barcode-value">{{ $item['value'] }}</div>
                    </div>

                    {{-- Location --}}
                    @if ($item['asset']->location)
                        <div class="location">📍 {{ $item['asset']->location }}</div>
                    @endif
                </div>
            @else
                <div class="label-batch label-empty"></div>
            @endif
        @endforeach
    </div>
</body>

</html>
