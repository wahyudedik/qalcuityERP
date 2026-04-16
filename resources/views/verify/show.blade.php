<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Produk</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-lg overflow-hidden">

        {{-- Header --}}
        <div class="px-6 pt-8 pb-4 text-center">
            <div class="text-sm font-medium text-gray-400 uppercase tracking-widest mb-2">Verifikasi Keaslian Produk</div>

            {{-- Status Badge --}}
            @if ($status === 'VALID')
                <div class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-green-100 text-green-700 font-bold text-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    VALID
                </div>
                <p class="mt-3 text-gray-600 text-sm">Produk ini terverifikasi asli</p>

            @elseif ($status === 'TIDAK VALID')
                <div class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-red-100 text-red-700 font-bold text-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    TIDAK VALID
                </div>
                <p class="mt-3 text-gray-600 text-sm">Sertifikat tidak dapat diverifikasi. Data produk mungkin telah diubah.</p>

            @elseif ($status === 'DICABUT')
                <div class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-orange-100 text-orange-700 font-bold text-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    DICABUT
                </div>
                <p class="mt-3 text-gray-600 text-sm">
                    Sertifikat ini telah dicabut
                    @if ($certificate && $certificate->revoked_at)
                        pada {{ $certificate->revoked_at->translatedFormat('d F Y') }}
                    @endif
                </p>

            @else {{-- TIDAK DITEMUKAN --}}
                <div class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-gray-100 text-gray-600 font-bold text-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    TIDAK DITEMUKAN
                </div>
                <p class="mt-3 text-gray-600 text-sm">Produk tidak terdaftar dalam sistem kami</p>
            @endif
        </div>

        {{-- Product Details --}}
        @if ($certificate && $product)
            <div class="mx-6 mb-6 rounded-xl bg-gray-50 divide-y divide-gray-100">
                <div class="flex justify-between items-start px-4 py-3">
                    <span class="text-xs text-gray-400 uppercase tracking-wide">Nama Produk</span>
                    <span class="text-sm font-medium text-gray-800 text-right max-w-[60%]">{{ $product->name }}</span>
                </div>
                <div class="flex justify-between items-center px-4 py-3">
                    <span class="text-xs text-gray-400 uppercase tracking-wide">SKU</span>
                    <span class="text-sm font-mono text-gray-800">{{ $product->sku }}</span>
                </div>
                <div class="flex justify-between items-start px-4 py-3">
                    <span class="text-xs text-gray-400 uppercase tracking-wide">Brand / Tenant</span>
                    <span class="text-sm font-medium text-gray-800 text-right max-w-[60%]">
                        {{ optional($product->tenant)->name ?? '-' }}
                    </span>
                </div>
                <div class="flex justify-between items-center px-4 py-3">
                    <span class="text-xs text-gray-400 uppercase tracking-wide">Tanggal Terbit</span>
                    <span class="text-sm text-gray-800">
                        {{ $certificate->issued_at ? $certificate->issued_at->translatedFormat('d F Y') : '-' }}
                    </span>
                </div>
                <div class="flex justify-between items-center px-4 py-3">
                    <span class="text-xs text-gray-400 uppercase tracking-wide">No. Sertifikat</span>
                    <span class="text-xs font-mono text-gray-600">{{ $certificate->certificate_number }}</span>
                </div>
            </div>
        @endif

        {{-- Footer --}}
        <div class="px-6 pb-6 text-center">
            <p class="text-xs text-gray-300">Diverifikasi oleh sistem ERP &bull; {{ now()->format('d/m/Y H:i') }}</p>
        </div>

    </div>
</body>
</html>
