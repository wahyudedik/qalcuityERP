<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Barcode - {{ $asset->asset_code }}</title>
    @vite(['resources/css/app.css'])
</head>

<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-6">
            {{-- Header --}}
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h1 class="text-lg font-bold text-gray-900">{{ $asset->name }}</h1>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ ucfirst($asset->category) }} &bull; {{ $asset->asset_code }}
                    </p>
                </div>
                @php $sc = ['active'=>'green','maintenance'=>'amber','disposed'=>'red','retired'=>'gray'][$asset->status] ?? 'gray'; @endphp
                <span
                    class="px-2 py-0.5 rounded-full text-xs bg-{{ $sc }}-100 text-{{ $sc }}-700 $sc }}-500/20 $sc }}-400">
                    {{ ucfirst($asset->status) }}
                </span>
            </div>

            {{-- Barcode Image --}}
            <div class="bg-white rounded-xl p-6 text-center mb-5">
                <img src="data:image/png;base64,{{ base64_encode($barcodeImage) }}" alt="{{ $asset->asset_code }}"
                    class="mx-auto max-w-full h-auto" style="max-height: 120px;">
                <p class="mt-3 text-sm font-mono tracking-widest text-gray-700">
                    {{ $asset->asset_code }}
                </p>
            </div>

            {{-- Asset Details --}}
            <div class="space-y-2 text-sm mb-6">
                @if ($asset->serial_number)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Serial Number</span>
                        <span class="font-medium text-gray-900">{{ $asset->serial_number }}</span>
                    </div>
                @endif
                @if ($asset->brand)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Brand</span>
                        <span class="font-medium text-gray-900">{{ $asset->brand }}</span>
                    </div>
                @endif
                @if ($asset->model)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Model</span>
                        <span class="font-medium text-gray-900">{{ $asset->model }}</span>
                    </div>
                @endif
                @if ($asset->location)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Location</span>
                        <span class="font-medium text-gray-900">{{ $asset->location }}</span>
                    </div>
                @endif
                <div class="flex justify-between pt-2 border-t border-gray-100 mt-3">
                    <span class="text-gray-500">Purchase Date</span>
                    <span class="font-medium text-gray-900">
                        {{ $asset->purchase_date?->format('d M Y') ?? '-' }}
                    </span>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex gap-2">
                <button onclick="window.print()"
                    class="flex-1 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition">
                    <svg class="w-4 h-4 inline mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print Label
                </button>
                <a href="{{ route('assets.index') }}"
                    class="px-4 py-2.5 border border-gray-200 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-50 transition">
                    Back
                </a>
            </div>
        </div>

        {{-- Print Styles --}}
        <style media="print">
            @page {
                margin: 15mm;
                size: A4;
            }

            body {
                background: white !important;
            }

            .shadow-xl {
                box-shadow: none !important;
            }

            button,
            a[href] {
                display: none !important;
            }
        </style>
    </div>
</body>

</html>
