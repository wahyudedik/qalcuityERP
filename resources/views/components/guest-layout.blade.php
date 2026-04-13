@props(['title' => null])

<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Qalcuity ERP') }}</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-[Inter,sans-serif] antialiased">
    <div class="min-h-full flex">
        {{-- Left panel branding --}}
        <div class="hidden lg:flex lg:w-1/2 xl:w-3/5 flex-col justify-between p-12 relative overflow-hidden bg-[#0a0f1e]">
            <div class="absolute inset-0 bg-gradient-to-br from-[#0a0f1e] via-[#0f1f3d] to-[#0a0f1e]"></div>
            <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-blue-600/15 rounded-full blur-[120px] -translate-y-1/3 translate-x-1/3"></div>
            <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-indigo-600/15 rounded-full blur-[100px] translate-y-1/3 -translate-x-1/3"></div>
            <div class="relative z-10">
                <img src="/logo.png" alt="Qalcuity ERP" class="h-9 w-auto object-contain" style="filter:brightness(0) invert(1);" loading="lazy">
            </div>
            <div class="relative z-10 space-y-6">
                <h1 class="text-5xl font-extrabold text-white leading-tight">
                    ERP Cerdas<br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-indigo-400 to-violet-400">Berbasis AI</span>
                </h1>
                <p class="text-slate-300 text-lg max-w-md">Kelola bisnis dengan bantuan AI. Inventory, penjualan, keuangan, dan SDM dalam satu platform.</p>
            </div>
            <div class="relative z-10 text-xs text-slate-600">© {{ date('Y') }} Qalcuity ERP.</div>
        </div>
        {{-- Right panel form --}}
        <div class="flex-1 flex flex-col justify-center px-6 py-12 lg:px-12 bg-[#f8fafc]">
            <div class="mx-auto w-full max-w-sm">
                <div class="lg:hidden flex items-center gap-2 mb-8">
                    <img src="/logo.png" alt="Qalcuity ERP" class="h-8 w-auto object-contain brightness-0" loading="lazy">
                </div>
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>
