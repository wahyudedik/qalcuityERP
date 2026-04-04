<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barcode - {{ $product->name }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: white !important;
            }

            .print-card {
                box-shadow: none !important;
                border: none !important;
            }
        }
    </style>
</head>

<body class="bg-gray-100 dark:bg-gray-900 min-h-screen flex items-center justify-center p-6">

    <div class="w-full max-w-sm">

        {{-- Action Buttons --}}
        <div class="no-print flex items-center justify-between mb-4">
            <a href="{{ url()->previous() }}"
                class="inline-flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back
            </a>
            <button onclick="window.print()"
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print
            </button>
        </div>

        {{-- Barcode Card --}}
        <div
            class="print-card bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 text-center">

            {{-- Product Name --}}
            <h1 class="text-base font-bold text-gray-900 dark:text-white mb-1 leading-tight">
                {{ $product->name }}
            </h1>

            {{-- SKU --}}
            @if ($product->sku)
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                    SKU: {{ $product->sku }}
                </p>
            @endif

            {{-- Barcode Image --}}
            <div class="bg-white rounded-xl p-3 inline-block border border-gray-100">
                <img src="data:image/png;base64,{{ base64_encode($barcodeImage) }}" alt="{{ $barcodeValue }}"
                    class="max-w-full h-auto" style="min-width: 200px;">
            </div>

            {{-- Barcode Value --}}
            <p class="mt-3 text-xs font-mono tracking-widest text-gray-600 dark:text-gray-300">
                {{ $barcodeValue }}
            </p>

            {{-- Price --}}
            @if (!empty($product->price_sell))
                <p class="mt-3 text-xl font-bold text-gray-900 dark:text-white">
                    Rp {{ number_format($product->price_sell, 0, ',', '.') }}
                </p>
            @endif

            {{-- Category / Unit --}}
            <div class="mt-3 flex items-center justify-center gap-3 text-xs text-gray-400 dark:text-gray-500">
                @if (!empty($product->category))
                    <span>{{ $product->category }}</span>
                @endif
                @if (!empty($product->unit))
                    <span>/ {{ $product->unit }}</span>
                @endif
            </div>

        </div>

        {{-- Info --}}
        <p class="no-print text-center text-xs text-gray-400 dark:text-gray-600 mt-4">
            Use Ctrl+P / Cmd+P to print, or click the Print button above.
        </p>

    </div>

</body>

</html>
