<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Bin Location Labels</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        /* ── Single label mode: 60mm x 30mm ── */
        .label-single {
            width: 58mm;
            height: 28mm;
            padding: 1.5mm 2mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        /* ── Batch A4 grid mode: 2 columns x N rows ── */
        .labels-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0;
        }

        .label-batch {
            width: 95mm;
            height: 45mm;
            padding: 3mm 4mm;
            border: 0.5pt dashed #aaa;
            page-break-inside: avoid;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .label-batch.empty {
            border-color: transparent;
        }

        /* Shared label internals */
        .bin-code {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            letter-spacing: 1px;
            color: #000;
            margin-bottom: 1mm;
        }

        .label-single .bin-code {
            font-size: 10pt;
        }

        .label-batch .bin-code {
            font-size: 12pt;
        }

        .barcode-img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        .label-single .barcode-img {
            max-height: 10mm;
        }

        .label-batch .barcode-img {
            max-height: 14mm;
        }

        .barcode-value {
            font-family: 'Courier New', monospace;
            color: #333;
            margin-top: 1mm;
        }

        .label-single .barcode-value {
            font-size: 5.5pt;
        }

        .label-batch .barcode-value {
            font-size: 6pt;
        }

        .meta {
            color: #666;
            line-height: 1.3;
            margin-top: 1mm;
        }

        .label-single .meta {
            font-size: 5pt;
        }

        .label-batch .meta {
            font-size: 5.5pt;
        }

        .type-badge {
            display: inline-block;
            padding: 0.5mm 2mm;
            border: 0.5pt solid #555;
            border-radius: 2pt;
            font-size: 5.5pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #555;
            margin-top: 1mm;
        }
    </style>
</head>

<body>
    @php $isBatch = count($barcodes) > 1; @endphp

    @if ($isBatch)
        <div class="labels-grid">
            @foreach ($barcodes as $item)
                @if ($item)
                    <div class="label-batch">
                        <div class="bin-code">{{ $item['value'] }}</div>
                        <img src="data:image/png;base64,{{ $item['image'] }}" class="barcode-img"
                            alt="{{ $item['value'] }}">
                        <div class="barcode-value">{{ $item['value'] }}</div>
                        <div class="meta">
                            @if (!empty($item['bin']->zone?->name))
                                Zone: {{ $item['bin']->zone->name }}
                            @endif
                            @if (!empty($item['bin']->aisle))
                                &bull; Aisle {{ $item['bin']->aisle }}
                            @endif
                            @if (!empty($item['bin']->rack))
                                &bull; Rack {{ $item['bin']->rack }}
                            @endif
                            @if (!empty($item['bin']->shelf))
                                &bull; Shelf {{ $item['bin']->shelf }}
                            @endif
                        </div>
                        @if (!empty($item['bin']->bin_type))
                            <div class="type-badge">{{ $item['bin']->bin_type }}</div>
                        @endif
                    </div>
                @else
                    <div class="label-batch empty"></div>
                @endif
            @endforeach
        </div>
    @else
        {{-- Single label --}}
        @php $item = $barcodes[0]; @endphp
        <div class="label-single">
            <div class="bin-code">{{ $item['value'] }}</div>
            <img src="data:image/png;base64,{{ $item['image'] }}" class="barcode-img" alt="{{ $item['value'] }}">
            <div class="barcode-value">{{ $item['value'] }}</div>
            <div class="meta">
                @if (!empty($item['bin']->zone?->name))
                    Zone: {{ $item['bin']->zone->name }}
                @endif
                @if (!empty($item['bin']->aisle))
                    A{{ $item['bin']->aisle }}
                @endif
                @if (!empty($item['bin']->rack))
                    R{{ $item['bin']->rack }}
                @endif
                @if (!empty($item['bin']->shelf))
                    S{{ $item['bin']->shelf }}
                @endif
            </div>
        </div>
    @endif
</body>

</html>
