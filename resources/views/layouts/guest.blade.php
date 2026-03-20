<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Qalcuity ERP') }}</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" href="/favicon.png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-[#0f172a] font-[Inter,sans-serif] antialiased">

<div class="min-h-full flex">

    {{-- Left panel — branding --}}
    <div class="hidden lg:flex lg:w-1/2 xl:w-3/5 flex-col justify-between p-12 relative overflow-hidden">
        {{-- Background gradient --}}
        <div class="absolute inset-0 bg-gradient-to-br from-[#0f172a] via-[#1e3a5f] to-[#0f172a]"></div>
        <div class="absolute top-0 right-0 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 bg-indigo-500/10 rounded-full blur-3xl translate-y-1/2 -translate-x-1/2"></div>

        <div class="relative z-10">
            <div class="flex items-center gap-3">
                <img src="/logo.png" alt="Qalcuity ERP" class="h-10 w-auto object-contain">
            </div>
        </div>

        <div class="relative z-10 space-y-6">
            <div>
                <h1 class="text-4xl font-bold text-gray-900 dark:text-white leading-tight">
                    ERP Cerdas<br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-400">Berbasis AI</span>
                </h1>
                <p class="mt-4 text-slate-400 text-lg leading-relaxed max-w-md">
                    Kelola bisnis Anda dengan bantuan AI. Inventory, penjualan, keuangan, dan SDM dalam satu platform.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 max-w-sm">
                @foreach(['Inventory Real-time', 'AI Chat ERP', 'Laporan Otomatis', 'Multi-tenant SaaS'] as $feat)
                <div class="flex items-center gap-2 text-sm text-slate-300">
                    <div class="w-5 h-5 rounded-full bg-blue-500/20 flex items-center justify-center shrink-0">
                        <svg class="w-3 h-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    {{ $feat }}
                </div>
                @endforeach
            </div>
        </div>

        <div class="relative z-10 text-xs text-slate-600">
            © {{ date('Y') }} Qalcuity ERP. All rights reserved.
        </div>
    </div>

    {{-- Right panel — form --}}
    <div class="flex-1 flex flex-col justify-center px-6 py-12 lg:px-12 bg-white">
        <div class="mx-auto w-full max-w-sm">
            {{-- Mobile logo --}}
            <div class="lg:hidden flex items-center gap-2 mb-8">
                <img src="/logo.png" alt="Qalcuity ERP" class="h-8 w-auto object-contain brightness-0 dark:brightness-100">
            </div>

            {{ $slot }}
        </div>
    </div>
</div>

</body>
</html>
