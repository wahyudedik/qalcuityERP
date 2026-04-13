<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Qalcuity ERP — ERP Cerdas Berbasis AI untuk Semua Industri Indonesia</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" href="/favicon.png">
    <link rel="canonical" href="{{ url('/') }}">
    <meta name="description"
        content="Platform ERP AI untuk semua industri: retail, F&B, konstruksi, pabrik beton, pertanian, manufaktur, distributor, telecom/ISP, hospitality, perikanan, peternakan, healthcare/rumah sakit, dan jasa. 70+ modul terintegrasi dengan AI chat. Gratis 14 hari.">
    <meta name="keywords"
        content="ERP Indonesia, ERP AI, software akuntansi, aplikasi kasir POS, manajemen inventori, ERP konstruksi, ERP pertanian, ERP pabrik beton, ERP manufaktur, ERP telecom, ERP ISP, ERP hotel, ERP restoran, SaaS ERP, Qalcuity">
    <meta name="robots" content="index, follow">
    <meta name="author" content="Noteds Technology">

    {{-- Open Graph --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:title" content="Qalcuity ERP — ERP Cerdas Berbasis AI untuk Semua Industri">
    <meta property="og:description"
        content="Kelola inventory, penjualan, keuangan, SDM, produksi, konstruksi, pertanian, kosmetik & beauty, CRM, fleet, telecom/ISP, hospitality, e-commerce marketplace, healthcare/rumah sakit, dan 70+ modul bisnis dengan AI. Cukup ketik perintah, AI langsung bertindak.">
    <meta property="og:image" content="{{ url('/logo.png') }}">
    <meta property="og:locale" content="id_ID">
    <meta property="og:site_name" content="Qalcuity ERP">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Qalcuity ERP — ERP AI untuk Semua Industri Indonesia">
    <meta name="twitter:description"
        content="70+ modul ERP terintegrasi AI. Retail, F&B, konstruksi, pertanian, manufaktur, distributor, telecom/ISP, hospitality, e-commerce, perikanan, peternakan, healthcare/rumah sakit.">
    <meta name="twitter:image" content="{{ url('/logo.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800,900&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .gradient-text {
            background: linear-gradient(135deg, #60a5fa, #818cf8, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-glow {
            background: radial-gradient(ellipse 80% 50% at 50% -20%, rgba(99, 102, 241, 0.3), transparent);
        }

        .card-hover {
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .card-hover:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 40px -8px rgba(0, 0, 0, .1);
        }
    </style>

    {{-- Structured Data --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "SoftwareApplication",
        "name": "Qalcuity ERP",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "description": "Platform ERP berbasis AI untuk semua industri Indonesia. 70+ modul terintegrasi: inventori, penjualan, keuangan, SDM, konstruksi, pertanian, manufaktur, telecom/ISP, hospitality, e-commerce, perikanan, peternakan, healthcare/rumah sakit.",
        "url": "{{ url('/') }}",
        "offers": {
            "@@type": "AggregateOffer",
            "priceCurrency": "IDR",
            "lowPrice": "99000",
            "highPrice": "999000",
            "offerCount": "4"
        },
        "creator": {
            "@@type": "Organization",
            "name": "Noteds Technology"
        },
        "aggregateRating": {
            "@@type": "AggregateRating",
            "ratingValue": "4.9",
            "ratingCount": "150"
        }
    }
    </script>
</head>

<body class="font-[Inter,sans-serif] bg-white text-gray-900 antialiased">

    {{-- ══ NAVBAR ══ --}}
    <nav x-data="{ open: false }"
        class="fixed top-0 inset-x-0 z-50 bg-white/90 backdrop-blur-xl border-b border-gray-100/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('landing') }}" class="flex items-center gap-2.5">
                <img src="/logo.png" alt="Qalcuity ERP" class="h-8 w-auto object-contain brightness-0" loading="lazy">
            </a>
            <div class="hidden md:flex items-center gap-0.5 text-sm font-medium">
                <a href="#fitur"
                    class="px-4 py-2 rounded-xl text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition">Fitur</a>
                <a href="#modul"
                    class="px-4 py-2 rounded-xl text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition">Modul</a>
                <a href="#industri"
                    class="px-4 py-2 rounded-xl text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition">Industri</a>
                <a href="#harga"
                    class="px-4 py-2 rounded-xl text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition">Harga</a>

                {{-- Dropdown Resources --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.outside="open = false"
                        class="px-4 py-2 rounded-xl text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition flex items-center gap-1">
                        Resources
                        <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-180': open }" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" x-transition
                        class="absolute top-full left-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50">
                        <a href="{{ route('documentation') }}"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                            <div class="font-medium">Dokumentasi</div>
                            <div class="text-xs text-gray-500">Panduan lengkap</div>
                        </a>
                        <a href="{{ url('/api-docs') }}" target="_blank"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                            <div class="font-medium">API Docs</div>
                            <div class="text-xs text-gray-500">REST API reference</div>
                        </a>
                        <div class="border-t border-gray-100 my-1"></div>
                        <a href="#open-clow" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                            <div class="font-medium">Open Clow</div>
                            <div class="text-xs text-gray-500">Komunitas developer</div>
                        </a>
                        <a href="#faq" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                            <div class="font-medium">FAQ</div>
                            <div class="text-xs text-gray-500">Pertanyaan umum</div>
                        </a>
                    </div>
                </div>

                <a href="#affiliate"
                    class="px-4 py-2 rounded-xl text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition">Affiliate</a>
                <a href="#kontak"
                    class="px-4 py-2 rounded-xl text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition">Kontak</a>
            </div>
            <div class="hidden md:flex items-center gap-3">
                <a href="https://wa.me/6281654932383" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-1.5 text-sm font-medium text-green-600 hover:text-green-700 px-4 py-2 rounded-xl hover:bg-green-50 transition">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                    </svg>
                    Hubungi Kami
                </a>
                <a href="{{ route('login') }}"
                    class="text-sm font-medium text-gray-600 hover:text-gray-900 px-4 py-2 rounded-xl hover:bg-gray-50 transition">Masuk</a>
                <a href="{{ route('register') }}"
                    class="text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 px-5 py-2.5 rounded-xl transition shadow-sm shadow-blue-200">
                    Coba Gratis
                </a>
            </div>
            <button @click="open = !open" class="md:hidden p-2 rounded-xl hover:bg-gray-100 text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 12h16M4 18h16" />
                    <path x-show="open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div x-show="open" x-transition class="md:hidden border-t border-gray-100 bg-white px-4 py-4 space-y-1">
            <a href="#fitur" @click="open=false"
                class="block px-4 py-2.5 rounded-xl text-sm text-gray-700 hover:bg-gray-50">Fitur</a>
            <a href="#modul" @click="open=false"
                class="block px-4 py-2.5 rounded-xl text-sm text-gray-700 hover:bg-gray-50">Modul</a>
            <a href="#industri" @click="open=false"
                class="block px-4 py-2.5 rounded-xl text-sm text-gray-700 hover:bg-gray-50">Industri</a>
            <a href="#harga" @click="open=false"
                class="block px-4 py-2.5 rounded-xl text-sm text-gray-700 hover:bg-gray-50">Harga</a>

            {{-- Mobile Resources Accordion --}}
            <div x-data="{ resourcesOpen: false }" class="space-y-1">
                <button @click="resourcesOpen = !resourcesOpen"
                    class="w-full flex items-center justify-between px-4 py-2.5 rounded-xl text-sm text-gray-700 hover:bg-gray-50">
                    Resources
                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': resourcesOpen }" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="resourcesOpen" x-collapse class="pl-4 space-y-1">
                    <a href="{{ route('documentation') }}" @click="open=false"
                        class="block px-4 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Dokumentasi</a>
                    <a href="{{ url('/api-docs') }}" @click="open=false" target="_blank"
                        class="block px-4 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50">API Docs</a>
                    <a href="#open-clow" @click="open=false"
                        class="block px-4 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Open Clow</a>
                    <a href="#faq" @click="open=false"
                        class="block px-4 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50">FAQ</a>
                </div>
            </div>

            <a href="#affiliate" @click="open=false"
                class="block px-4 py-2.5 rounded-xl text-sm text-gray-700 hover:bg-gray-50">Affiliate</a>
            <a href="#kontak" @click="open=false"
                class="block px-4 py-2.5 rounded-xl text-sm text-gray-700 hover:bg-gray-50">Kontak</a>
            <div class="pt-3 flex flex-col gap-2 border-t border-gray-100 mt-2">
                <a href="https://wa.me/6281654932383" target="_blank" rel="noopener"
                    class="flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-medium border border-green-200 text-green-700 bg-green-50">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                    </svg>
                    Hubungi via WhatsApp
                </a>
                <a href="{{ route('login') }}"
                    class="block text-center py-2.5 rounded-xl text-sm font-medium border border-gray-200 text-gray-700">Masuk</a>
                <a href="{{ route('register') }}"
                    class="block text-center py-2.5 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600">Coba
                    Gratis</a>
            </div>
        </div>
    </nav>

    {{-- ══ HERO ══ --}}
    <section class="relative pt-28 pb-24 overflow-hidden bg-[#0a0f1e]">
        <div class="hero-glow absolute inset-0 pointer-events-none"></div>
        <div
            class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[900px] h-[600px] bg-indigo-600/10 rounded-full blur-3xl pointer-events-none">
        </div>

        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div
                class="inline-flex items-center gap-2 bg-white/5 border border-white/10 text-blue-300 text-xs font-semibold px-4 py-2 rounded-full mb-8 backdrop-blur">
                <span class="w-1.5 h-1.5 bg-blue-400 rounded-full animate-pulse"></span>
                Didukung Qalcuity AI — Generasi Terbaru
            </div>

            <h1 class="text-5xl sm:text-6xl lg:text-7xl font-black text-white leading-[1.08] tracking-tight mb-6">
                ERP yang bisa<br>
                <span class="gradient-text">Anda ajak bicara</span>
            </h1>

            <p class="text-lg text-slate-400 max-w-2xl mx-auto leading-relaxed mb-10">
                Kelola inventory, penjualan, keuangan, SDM, produksi, konstruksi, pertanian, kosmetik & beauty, CRM,
                fleet, telecom/ISP, hospitality, e-commerce marketplace, healthcare/rumah sakit, dan 70+ modul
                bisnis lainnya — cukup dengan mengetik perintah. AI kami memahami konteks bisnis Anda dan langsung
                bertindak. Cocok untuk retail, F&B, pabrik beton, perkebunan, distributor, manufaktur kosmetik, ISP,
                hotel, restoran, perikanan, peternakan, rumah sakit, klinik, dan semua jenis industri.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-20">
                <a href="{{ route('register') }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-semibold px-8 py-3.5 rounded-2xl transition shadow-lg shadow-blue-900/40 text-sm">
                    Mulai Gratis 14 Hari
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="#demo"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-white/8 hover:bg-white/12 border border-white/15 text-white font-medium px-8 py-3.5 rounded-2xl transition text-sm backdrop-blur">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Lihat Demo
                </a>
            </div>

            {{-- Chat Demo --}}
            <div id="demo" class="relative max-w-2xl mx-auto">
                <div class="bg-white/5 border border-white/10 rounded-3xl p-1 shadow-2xl backdrop-blur">
                    <div class="bg-[#0d1424] rounded-2xl overflow-hidden">
                        <div class="flex items-center gap-2 px-4 py-3 border-b border-white/5">
                            <div class="w-2.5 h-2.5 rounded-full bg-red-500/60"></div>
                            <div class="w-2.5 h-2.5 rounded-full bg-yellow-500/60"></div>
                            <div class="w-2.5 h-2.5 rounded-full bg-green-500/60"></div>
                            <div
                                class="flex-1 mx-4 bg-white/5 rounded-lg px-3 py-1 text-xs text-slate-500 text-center">
                                Qalcuity AI Chat</div>
                        </div>
                        <div class="p-5 space-y-4 text-left">
                            <div class="flex gap-3">
                                <div
                                    class="w-7 h-7 rounded-full bg-slate-700 flex items-center justify-center text-xs text-slate-300 shrink-0 mt-0.5 font-semibold">
                                    A</div>
                                <div
                                    class="bg-slate-800/80 border border-white/5 rounded-2xl rounded-tl-sm px-4 py-3 text-sm text-slate-200 max-w-xs">
                                    Stok produk apa yang hampir habis minggu ini?
                                </div>
                            </div>
                            <div class="flex gap-3 justify-end">
                                <div
                                    class="bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl rounded-tr-sm px-4 py-3 text-sm text-white max-w-sm">
                                    <p class="font-semibold mb-2.5">📦 3 Produk Stok Menipis</p>
                                    <div class="space-y-1.5 text-xs text-blue-100 bg-white/10 rounded-xl p-3">
                                        <div class="flex justify-between"><span>Laptop ASUS X515</span><span
                                                class="text-red-300 font-bold">2 unit</span></div>
                                        <div class="flex justify-between"><span>Mouse Wireless</span><span
                                                class="text-yellow-300 font-bold">5 unit</span></div>
                                        <div class="flex justify-between"><span>Keyboard Mech.</span><span
                                                class="text-yellow-300 font-bold">8 unit</span></div>
                                    </div>
                                    <p class="text-xs text-blue-200 mt-2.5">Mau saya buatkan Purchase Order ke
                                        supplier?</p>
                                </div>
                                <div
                                    class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center shrink-0 mt-0.5 text-white text-xs font-bold">
                                    Q</div>
                            </div>
                            <div class="flex gap-3">
                                <div
                                    class="w-7 h-7 rounded-full bg-slate-700 flex items-center justify-center text-xs text-slate-300 shrink-0 mt-0.5 font-semibold">
                                    A</div>
                                <div
                                    class="bg-slate-800/80 border border-white/5 rounded-2xl rounded-tl-sm px-4 py-3 text-sm text-slate-200 max-w-xs">
                                    Ya, buatkan PO untuk Laptop ASUS ke PT. Supplier Jaya
                                </div>
                            </div>
                            <div class="flex gap-3 justify-end">
                                <div
                                    class="bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl rounded-tr-sm px-4 py-3 text-sm text-white max-w-sm">
                                    ✅ Purchase Order <span class="font-bold">PO-2026-0042</span> berhasil dibuat untuk
                                    PT. Supplier Jaya — 10 unit Laptop ASUS X515.
                                </div>
                                <div
                                    class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center shrink-0 mt-0.5 text-white text-xs font-bold">
                                    Q</div>
                            </div>
                        </div>
                        <div class="px-5 pb-5">
                            <div
                                class="flex items-center gap-3 bg-white/5 border border-white/10 rounded-2xl px-4 py-3">
                                <span class="text-sm text-slate-500 flex-1">Ketik perintah ERP Anda...</span>
                                <div
                                    class="w-7 h-7 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="absolute -inset-6 bg-indigo-600/10 rounded-3xl blur-3xl -z-10"></div>
            </div>
        </div>
    </section>

    {{-- ══ STATS ══ --}}
    <section class="bg-white border-b border-gray-100 py-14">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                @foreach ([['value' => '40+', 'label' => 'Modul ERP Terintegrasi'], ['value' => '9+', 'label' => 'Industri Didukung'], ['value' => 'AI', 'label' => 'Powered by Qalcuity AI'], ['value' => '4.9★', 'label' => 'Rating Pengguna']] as $s)
                    <div>
                        <p class="text-3xl font-black text-gray-900 tracking-tight">{{ $s['value'] }}</p>
                        <p class="text-sm text-gray-400 mt-1.5">{{ $s['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══ FITUR ══ --}}
    <section id="fitur" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="text-xs font-bold text-blue-600 uppercase tracking-widest">Kenapa Qalcuity?</span>
                <h2 class="text-4xl font-black text-gray-900 mt-3 leading-tight">ERP yang bekerja<br>seperti asisten
                    pribadi</h2>
                <p class="text-gray-400 mt-4 max-w-lg mx-auto text-base">Tidak perlu klik menu berlapis. Cukup ketik
                    apa yang Anda butuhkan.</p>
            </div>
            <div class="grid md:grid-cols-3 gap-5">
                @php
                    $features = [
                        [
                            'icon' =>
                                'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
                            'title' => 'AI Chat ERP',
                            'color' => 'blue',
                            'desc' =>
                                'Tanya stok, buat PO, cek laporan keuangan — semua lewat percakapan natural. Qalcuity AI memahami konteks bisnis Anda dan langsung bertindak.',
                        ],
                        [
                            'icon' =>
                                'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                            'title' => 'Rich Output Visual',
                            'color' => 'indigo',
                            'desc' =>
                                'AI merespons dengan grafik interaktif, KPI cards, tabel data, invoice, surat resmi, dan tombol aksi langsung di dalam chat.',
                        ],
                        [
                            'icon' =>
                                'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
                            'title' => 'Analisis Gambar & Dokumen',
                            'color' => 'purple',
                            'desc' =>
                                'Upload foto struk, PDF laporan, atau CSV data. AI mengekstrak informasi dan menawarkan untuk langsung dicatat ke sistem.',
                        ],
                        [
                            'icon' =>
                                'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                            'title' => 'Laporan & Export',
                            'color' => 'green',
                            'desc' =>
                                'Laporan laba rugi, aging piutang, valuasi inventori, payroll — unduh Excel/PDF atau visualisasikan sebagai grafik interaktif.',
                        ],
                        [
                            'icon' =>
                                'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                            'title' => 'Multi-tenant & Multi-role',
                            'color' => 'orange',
                            'desc' =>
                                'Data setiap perusahaan terisolasi penuh. Kelola tim dengan role Admin, Manager, dan Staff dengan kontrol akses fleksibel.',
                        ],
                        [
                            'icon' =>
                                'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z',
                            'title' => 'Bot WA & Telegram',
                            'color' => 'pink',
                            'desc' =>
                                'Hubungkan WhatsApp atau Telegram ke sistem ERP. Terima notifikasi dan kelola bisnis langsung dari aplikasi chat favorit Anda.',
                        ],
                        [
                            'icon' =>
                                'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                            'title' => 'Analytics & Reporting',
                            'color' => 'teal',
                            'desc' =>
                                'Dashboard analitik dengan batch performance, QC trends, regulatory compliance, cost analysis, supplier quality, dan predictive expiry forecast.',
                        ],
                        [
                            'icon' =>
                                'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z',
                            'title' => 'Manufaktur Kosmetik',
                            'color' => 'rose',
                            'desc' =>
                                'Formula INCI, batch production, QC laboratory, BPOM registration, packaging & labeling, distribution channels, dan product lifecycle management.',
                        ],
                        [
                            'icon' =>
                                'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
                            'title' => 'Healthcare & Rumah Sakit',
                            'color' => 'red',
                            'desc' =>
                                'Electronic Medical Records (EMR), appointment scheduling, bed management, pharmacy, laboratory, radiology, BPJS claims, billing pasien, dan patient portal.',
                        ],
                        [
                            'icon' =>
                                'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0',
                            'title' => 'Distribution Channels',
                            'color' => 'cyan',
                            'desc' =>
                                'Kelola retail, online marketplace, distributor, dan reseller/MLM dengan pricing formulas, channel inventory, dan sales performance tracking.',
                        ],
                    ];
                    $cm = [
                        'blue' => ['bg' => 'bg-blue-50', 'ic' => 'text-blue-600'],
                        'indigo' => ['bg' => 'bg-indigo-50', 'ic' => 'text-indigo-600'],
                        'purple' => ['bg' => 'bg-purple-50', 'ic' => 'text-purple-600'],
                        'green' => ['bg' => 'bg-green-50', 'ic' => 'text-green-600'],
                        'orange' => ['bg' => 'bg-orange-50', 'ic' => 'text-orange-600'],
                        'pink' => ['bg' => 'bg-pink-50', 'ic' => 'text-pink-600'],
                        'teal' => ['bg' => 'bg-teal-50', 'ic' => 'text-teal-600'],
                        'rose' => ['bg' => 'bg-rose-50', 'ic' => 'text-rose-600'],
                        'red' => ['bg' => 'bg-red-50', 'ic' => 'text-red-600'],
                        'cyan' => ['bg' => 'bg-cyan-50', 'ic' => 'text-cyan-600'],
                    ];
                @endphp
                @foreach ($features as $f)
                    @php $c = $cm[$f['color']]; @endphp
                    <div class="card-hover bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                        <div class="w-10 h-10 rounded-xl {{ $c['bg'] }} flex items-center justify-center mb-4">
                            <svg class="w-5 h-5 {{ $c['ic'] }}" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="{{ $f['icon'] }}" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-900 mb-2 text-base">{{ $f['title'] }}</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">{{ $f['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══ MODUL ══ --}}
    <section id="modul" class="py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="text-xs font-bold text-blue-600 uppercase tracking-widest">Modul Lengkap</span>
                <h2 class="text-4xl font-black text-gray-900 mt-3">Semua yang bisnis Anda butuhkan</h2>
                <p class="text-gray-400 mt-4 max-w-lg mx-auto">50+ modul terintegrasi, dikelola lewat satu antarmuka
                    AI.</p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                @php
                    $modules = [
                        [
                            'emoji' => '📦',
                            'title' => 'Inventory',
                            'color' => 'blue',
                            'items' => ['Multi-gudang', 'Transfer stok', 'Stock opname', 'Konsinyasi', 'Bin location'],
                        ],
                        [
                            'emoji' => '🛒',
                            'title' => 'Penjualan',
                            'color' => 'green',
                            'items' => [
                                'Quotation → SO → Invoice',
                                'Piutang',
                                'Loyalty',
                                'Subscription Billing',
                                'Down Payment',
                            ],
                        ],
                        [
                            'emoji' => '🏭',
                            'title' => 'Pembelian',
                            'color' => 'orange',
                            'items' => ['Purchase Order', 'Supplier', 'Landed Cost', '3-Way Matching', 'RFQ'],
                        ],
                        [
                            'emoji' => '👥',
                            'title' => 'SDM & Payroll',
                            'color' => 'purple',
                            'items' => [
                                'Karyawan & Absensi',
                                'Penggajian',
                                'Overtime',
                                'Komisi Sales',
                                'Fingerprint/RFID',
                            ],
                        ],
                        [
                            'emoji' => '💰',
                            'title' => 'Keuangan',
                            'color' => 'indigo',
                            'items' => [
                                'Jurnal GL',
                                'Anggaran',
                                'Rekonsiliasi Bank',
                                'Multi-currency',
                                'Approval Workflow',
                            ],
                        ],
                        [
                            'emoji' => '🏗️',
                            'title' => 'Konstruksi',
                            'color' => 'amber',
                            'items' => [
                                'RAB Detail',
                                'Mix Design Beton',
                                'Progress Volume',
                                'Retensi & Termin',
                                'Subcontractor',
                            ],
                        ],
                        [
                            'emoji' => '🌾',
                            'title' => 'Pertanian',
                            'color' => 'teal',
                            'items' => [
                                'Manajemen Lahan',
                                'Siklus Tanam',
                                'Pencatatan Panen',
                                'Biaya per Hektar',
                                'Irigasi',
                            ],
                        ],
                        [
                            'emoji' => '⚙️',
                            'title' => 'Manufaktur',
                            'color' => 'slate',
                            'items' => ['Work Order', 'BOM Multi-Level', 'MRP Planning', 'Work Center', 'QC Lab'],
                        ],
                        [
                            'emoji' => '📋',
                            'title' => 'Proyek',
                            'color' => 'rose',
                            'items' => [
                                'Task & Milestone',
                                'Timesheet',
                                'Project Billing',
                                'Volume Tracking',
                                'Expense',
                            ],
                        ],
                        [
                            'emoji' => '🤝',
                            'title' => 'CRM & Sales',
                            'color' => 'yellow',
                            'items' => [
                                'Pipeline lead',
                                'Helpdesk & Tiket',
                                'Knowledge Base',
                                'SLA Tracking',
                                'Activity Log',
                            ],
                        ],
                        [
                            'emoji' => '🌐',
                            'title' => 'Telecom/ISP',
                            'color' => 'cyan',
                            'items' => [
                                'Pelanggan Internet',
                                'Bandwidth Mgmt',
                                'Voucher Hotspot',
                                'MikroTik API',
                                'Billing Otomatis',
                            ],
                        ],
                        [
                            'emoji' => '🏨',
                            'title' => 'Hospitality',
                            'color' => 'emerald',
                            'items' => ['Room Reservation', 'Housekeeping', 'Minibar', 'Restaurant POS', 'Spa & Tour'],
                        ],
                        [
                            'emoji' => '🛍️',
                            'title' => 'E-Commerce',
                            'color' => 'pink',
                            'items' => [
                                'Shopee/Tokopedia/Lazada',
                                'Auto Sync Stock',
                                'Price Sync',
                                'Order Integration',
                                'Webhook',
                            ],
                        ],
                        [
                            'emoji' => '🏥',
                            'title' => 'Healthcare',
                            'color' => 'red',
                            'items' => [
                                'Electronic Medical Records',
                                'Appointment Scheduling',
                                'Bed Management',
                                'BPJS Claims',
                                'Patient Portal',
                            ],
                        ],
                    ];
                    $mc = [
                        'blue' => ['b' => 'border-blue-200', 't' => 'text-blue-700', 'd' => 'bg-blue-400'],
                        'green' => ['b' => 'border-green-200', 't' => 'text-green-700', 'd' => 'bg-green-400'],
                        'orange' => ['b' => 'border-orange-200', 't' => 'text-orange-700', 'd' => 'bg-orange-400'],
                        'purple' => ['b' => 'border-purple-200', 't' => 'text-purple-700', 'd' => 'bg-purple-400'],
                        'indigo' => ['b' => 'border-indigo-200', 't' => 'text-indigo-700', 'd' => 'bg-indigo-400'],
                        'amber' => ['b' => 'border-amber-200', 't' => 'text-amber-700', 'd' => 'bg-amber-400'],
                        'teal' => ['b' => 'border-teal-200', 't' => 'text-teal-700', 'd' => 'bg-teal-400'],
                        'rose' => ['b' => 'border-rose-200', 't' => 'text-rose-700', 'd' => 'bg-rose-400'],
                        'slate' => ['b' => 'border-slate-200', 't' => 'text-slate-700', 'd' => 'bg-slate-400'],
                        'yellow' => ['b' => 'border-yellow-200', 't' => 'text-yellow-700', 'd' => 'bg-yellow-400'],
                        'cyan' => ['b' => 'border-cyan-200', 't' => 'text-cyan-700', 'd' => 'bg-cyan-400'],
                        'emerald' => ['b' => 'border-emerald-200', 't' => 'text-emerald-700', 'd' => 'bg-emerald-400'],
                        'pink' => ['b' => 'border-pink-200', 't' => 'text-pink-700', 'd' => 'bg-pink-400'],
                        'red' => ['b' => 'border-red-200', 't' => 'text-red-700', 'd' => 'bg-red-400'],
                    ];
                @endphp
                @foreach ($modules as $m)
                    @php $c = $mc[$m['color']]; @endphp
                    <div class="card-hover bg-white border {{ $c['b'] }} rounded-2xl p-4">
                        <div class="text-2xl mb-2.5">{{ $m['emoji'] }}</div>
                        <h3 class="font-bold {{ $c['t'] }} mb-2.5 text-xs uppercase tracking-wide">
                            {{ $m['title'] }}</h3>
                        <ul class="space-y-1.5">
                            @foreach ($m['items'] as $item)
                                <li class="flex items-center gap-1.5 text-xs text-gray-500">
                                    <span
                                        class="w-1 h-1 rounded-full {{ $c['d'] }} shrink-0"></span>{{ $item }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
            <div class="mt-5 flex flex-wrap gap-2 justify-center">
                @foreach ([
        '🚚 Pengiriman',
        '🛍️ E-Commerce',
        '🤖 Bot WA/Telegram',
        '🏦 Rekonsiliasi Bank',
        '⭐ Loyalty Program',
        '✍️ Tanda Tangan Digital',
        '📊 AI Forecasting',
        '🔔 Push Notification',
        '🏪 Konsinyasi',
        '📐 Project Billing',
        '🔄 Subscription Billing',
        '🎫 Helpdesk & Tiket',
        '🚢 Landed Cost',
        '💵 Komisi Sales',
        '🚛 Fleet & Aset',
        '📝 Kontrak',
        '🧮 Mix Design Beton',
        '📐 RAB Konstruksi',
        '🌱 Siklus Tanam',
        '🌾 Pencatatan Panen',
        '📦 Bulk Import/Export',
        '🔗 Webhook Outbound',
        '🌐 Telecom/ISP',
        '🏨 Hotel Management',
        '🍽️ Restaurant POS',
        '💆 Spa & Wellness',
        '✈️ Tour & Travel',
        '🐟 Budidaya Ikan',
        '🐄 Peternakan',
        '🖨️ Print Estimation',
        '👆 Fingerprint/RFID',
        '🏭 BOM Multi-Level',
        '📊 Custom Dashboard',
        '✅ Approval Workflow',
        '📷 Zero Input OCR',
        '🏢 Multi-Company',
        '🌍 Multi-Currency',
        '🏥 Electronic Medical Records',
        '🩺 Appointment Scheduling',
        '🛏️ Bed Management',
        '💊 Pharmacy Management',
        '🔬 Laboratory',
        '📡 Radiology',
        '🏥 BPJS Claims',
        '👤 Patient Portal',
        '🚑 Emergency Room',
        '🏨 Inpatient Ward',
    ] as $tag)
                    <span
                        class="text-xs bg-white border border-gray-200 rounded-full px-3 py-1.5 text-gray-500 shadow-sm">{{ $tag }}</span>
                @endforeach
            </div>
            <div class="mt-8 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-3xl p-8 text-white text-center">
                <p class="text-lg font-bold mb-2">Semua modul terhubung ke Qalcuity AI</p>
                <p class="text-blue-200 text-sm max-w-lg mx-auto">
                    Cukup ketik <span class="bg-white/20 px-2 py-0.5 rounded-lg font-mono text-xs">"Hitung kebutuhan
                        beton K-300 untuk 50 m³"</span> atau
                    <span class="bg-white/20 px-2 py-0.5 rounded-lg font-mono text-xs">"Panen 500 kg padi dari blok A1
                        grade A"</span> atau
                    <span class="bg-white/20 px-2 py-0.5 rounded-lg font-mono text-xs">"Jadwalkan dr. Andi untuk
                        pasien Budi besok jam 10"</span> — AI langsung mengeksekusi.
                </p>
            </div>
        </div>
    </section>

    {{-- ══ RICH OUTPUT SHOWCASE ══ --}}
    <section class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="text-xs font-bold text-blue-600 uppercase tracking-widest">Output AI yang Kaya</span>
                <h2 class="text-4xl font-black text-gray-900 mt-3">Bukan sekadar teks biasa</h2>
                <p class="text-gray-400 mt-4 max-w-lg mx-auto">Grafik, tabel, KPI cards, invoice, surat, dan tombol
                    aksi — semua langsung di dalam chat.</p>
            </div>
            <div class="grid md:grid-cols-3 gap-5">
                {{-- KPI --}}
                <div class="card-hover bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="text-base">📊</span>
                        <p class="text-sm font-semibold text-gray-800">KPI Cards</p>
                        <span
                            class="ml-auto text-xs bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full font-medium">Dashboard</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="bg-blue-50 rounded-xl p-3">
                            <p class="text-xs text-gray-400">Omzet Bulan Ini</p>
                            <p class="text-sm font-bold text-gray-800 mt-1">Rp 48,5 jt</p>
                            <p class="text-xs text-green-600 mt-0.5 font-medium">▲ 12%</p>
                        </div>
                        <div class="bg-green-50 rounded-xl p-3">
                            <p class="text-xs text-gray-400">Laba Bersih</p>
                            <p class="text-sm font-bold text-gray-800 mt-1">Rp 18,2 jt</p>
                            <p class="text-xs text-green-600 mt-0.5 font-medium">▲ 8%</p>
                        </div>
                        <div class="bg-amber-50 rounded-xl p-3">
                            <p class="text-xs text-gray-400">Stok Menipis</p>
                            <p class="text-sm font-bold text-gray-800 mt-1">7 produk</p>
                            <p class="text-xs text-red-500 mt-0.5 font-medium">Perlu PO</p>
                        </div>
                        <div class="bg-purple-50 rounded-xl p-3">
                            <p class="text-xs text-gray-400">Karyawan Hadir</p>
                            <p class="text-sm font-bold text-gray-800 mt-1">24/26</p>
                            <p class="text-xs text-gray-400 mt-0.5">2 izin</p>
                        </div>
                    </div>
                </div>
                {{-- Table --}}
                <div class="card-hover bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="text-base">📋</span>
                        <p class="text-sm font-semibold text-gray-800">Tabel Interaktif</p>
                        <span class="ml-auto text-xs bg-green-50 text-green-600 px-2 py-0.5 rounded-full font-medium">+
                            Ekspor CSV</span>
                    </div>
                    <div class="overflow-hidden rounded-xl border border-gray-100">
                        <table class="min-w-full text-xs">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-3 py-2 text-left text-gray-400 font-semibold">Produk</th>
                                    <th class="px-3 py-2 text-right text-gray-400 font-semibold">Stok</th>
                                    <th class="px-3 py-2 text-left text-gray-400 font-semibold">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr>
                                    <td class="px-3 py-2 text-gray-700">Kopi Arabika</td>
                                    <td class="px-3 py-2 text-right text-gray-600">3 kg</td>
                                    <td class="px-3 py-2"><span
                                            class="bg-red-100 text-red-700 px-1.5 py-0.5 rounded-full">Habis</span>
                                    </td>
                                </tr>
                                <tr class="bg-gray-50/50">
                                    <td class="px-3 py-2 text-gray-700">Susu UHT</td>
                                    <td class="px-3 py-2 text-right text-gray-600">12 ltr</td>
                                    <td class="px-3 py-2"><span
                                            class="bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded-full">Rendah</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-2 text-gray-700">Gula Pasir</td>
                                    <td class="px-3 py-2 text-right text-gray-600">45 kg</td>
                                    <td class="px-3 py-2"><span
                                            class="bg-green-100 text-green-700 px-1.5 py-0.5 rounded-full">Aman</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button class="mt-2.5 text-xs text-blue-600 hover:underline font-medium">⬇ Ekspor CSV</button>
                </div>
                {{-- Actions --}}
                <div class="card-hover bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="text-base">⚡</span>
                        <p class="text-sm font-semibold text-gray-800">Tombol Aksi Inline</p>
                        <span
                            class="ml-auto text-xs bg-purple-50 text-purple-600 px-2 py-0.5 rounded-full font-medium">Baru</span>
                    </div>
                    <p class="text-xs text-gray-400 mb-3 leading-relaxed">Setelah AI menampilkan data, tombol aksi
                        muncul langsung di chat untuk tindak lanjut cepat.</p>
                    <div class="space-y-2">
                        <div class="flex flex-wrap gap-1.5">
                            <span
                                class="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg bg-blue-600 text-white">📦
                                Buat PO Otomatis</span>
                            <span
                                class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-600">🔍
                                Lihat Detail</span>
                        </div>
                        <div class="flex flex-wrap gap-1.5">
                            <span
                                class="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg bg-green-600 text-white">✅
                                Konfirmasi Semua</span>
                            <span
                                class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-600">📊
                                Lihat Laporan</span>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-3">Klik tombol → AI langsung mengeksekusi</p>
                </div>
                {{-- Invoice --}}
                <div class="card-hover bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm">
                    <div class="flex items-center justify-between px-4 py-2.5 bg-gray-50 border-b border-gray-100">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">🧾 Invoice /
                            Faktur</span>
                        <button class="text-xs bg-blue-600 text-white px-2.5 py-1 rounded-lg font-medium">🖨
                            Cetak</button>
                    </div>
                    <div class="p-4 text-xs">
                        <div class="flex justify-between mb-3">
                            <div>
                                <p class="font-bold text-gray-800">PT Contoh Jaya</p>
                                <p class="text-gray-400 mt-0.5">Jl. Sudirman No. 1</p>
                            </div>
                            <div class="text-right">
                                <p class="text-blue-600 font-black text-lg leading-none">INVOICE</p>
                                <p class="text-gray-400 mt-1">INV-2026-042</p>
                            </div>
                        </div>
                        <div class="border border-gray-100 rounded-xl overflow-hidden mb-3">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="px-3 py-2 text-left text-gray-400 font-semibold">Item</th>
                                        <th class="px-3 py-2 text-right text-gray-400 font-semibold">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Kopi Arabika 10kg</td>
                                        <td class="px-3 py-2 text-right text-gray-700">Rp 500.000</td>
                                    </tr>
                                    <tr>
                                        <td class="px-3 py-2 text-gray-700">Susu UHT 20ltr</td>
                                        <td class="px-3 py-2 text-right text-gray-700">Rp 300.000</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="flex justify-end">
                            <p class="text-gray-500">Total: <span class="font-bold text-blue-600 text-sm">Rp
                                    880.000</span></p>
                        </div>
                    </div>
                </div>
                {{-- Letter --}}
                <div class="card-hover bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm">
                    <div class="flex items-center justify-between px-4 py-2.5 bg-gray-50 border-b border-gray-100">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">📄 Surat Resmi</span>
                        <div class="flex gap-1.5">
                            <button class="text-xs bg-blue-600 text-white px-2.5 py-1 rounded-lg font-medium">🖨
                                Cetak</button>
                            <button class="text-xs bg-gray-200 text-gray-600 px-2.5 py-1 rounded-lg font-medium">📋
                                Salin</button>
                        </div>
                    </div>
                    <div class="p-5 text-xs text-gray-700 leading-relaxed"
                        style="font-family:'Times New Roman',serif">
                        <p class="text-right text-gray-400 mb-3">Jakarta, 20 Maret 2026</p>
                        <p class="mb-2">Kepada Yth.<br><strong>Bapak/Ibu Pelanggan</strong></p>
                        <p class="mb-3"><strong>Perihal: Penawaran Produk Kopi Premium</strong></p>
                        <p class="text-gray-500">Dengan hormat, kami mengajukan penawaran produk kopi arabika premium
                            grade A dengan harga spesial untuk pembelian bulan ini...</p>
                        <p class="mt-4">Hormat kami,<br><br><strong>Direktur PT Contoh Jaya</strong></p>
                    </div>
                </div>
                {{-- Chart --}}
                <div class="card-hover bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="text-base">📈</span>
                        <p class="text-sm font-semibold text-gray-800">Grafik Interaktif</p>
                        <span
                            class="ml-auto text-xs bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-full font-medium">Chart.js</span>
                    </div>
                    <div class="space-y-2.5">
                        @php $chartData = [['m'=>'Jan','p'=>'60%','v'=>'Rp 28jt'],['m'=>'Feb','p'=>'75%','v'=>'Rp 35jt'],['m'=>'Mar','p'=>'55%','v'=>'Rp 26jt'],['m'=>'Apr','p'=>'90%','v'=>'Rp 42jt'],['m'=>'Mei','p'=>'80%','v'=>'Rp 38jt']]; @endphp
                        @foreach ($chartData as $cd)
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-400 w-6 shrink-0">{{ $cd['m'] }}</span>
                                <div class="flex-1 bg-gray-100 rounded-full h-1.5">
                                    <div class="bg-gradient-to-r from-blue-500 to-indigo-500 h-1.5 rounded-full"
                                        style="width:{{ $cd['p'] }}"></div>
                                </div>
                                <span
                                    class="text-xs text-gray-500 w-12 text-right shrink-0">{{ $cd['v'] }}</span>
                            </div>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-400 mt-3">Omzet 5 bulan terakhir — klik "Unduh" untuk simpan PNG</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ══ INDUSTRI ══ --}}
    <section id="industri" class="py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="text-xs font-bold text-blue-600 uppercase tracking-widest">Untuk Semua Industri</span>
                <h2 class="text-4xl font-black text-gray-900 mt-3">Satu platform, semua jenis bisnis</h2>
                <p class="text-gray-400 mt-4 max-w-lg mx-auto">Template industri siap pakai dengan AI yang memahami
                    konteks spesifik bisnis Anda.</p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @php
                    $industries = [
                        [
                            'emoji' => '🏪',
                            'title' => 'Retail & Toko',
                            'desc' => 'POS, inventori, loyalty, multi-gudang, e-commerce',
                            'color' => 'bg-blue-50 border-blue-200',
                        ],
                        [
                            'emoji' => '🍜',
                            'title' => 'F&B & Kuliner',
                            'desc' => 'POS kasir, resep/BOM, stok bahan baku, HPP per menu',
                            'color' => 'bg-orange-50 border-orange-200',
                        ],
                        [
                            'emoji' => '🏗️',
                            'title' => 'Konstruksi',
                            'desc' => 'RAB detail, mix design beton, progress volume, retensi & termin',
                            'color' => 'bg-amber-50 border-amber-200',
                        ],
                        [
                            'emoji' => '🌾',
                            'title' => 'Pertanian',
                            'desc' => 'Manajemen lahan, siklus tanam, pencatatan panen, biaya per hektar',
                            'color' => 'bg-green-50 border-green-200',
                        ],
                        [
                            'emoji' => '🏭',
                            'title' => 'Manufaktur',
                            'desc' => 'BOM multi-level, work order, MRP planning, work center',
                            'color' => 'bg-slate-50 border-slate-200',
                        ],
                        [
                            'emoji' => '📦',
                            'title' => 'Distributor',
                            'desc' => 'WMS gudang, landed cost, konsinyasi, fleet kendaraan',
                            'color' => 'bg-purple-50 border-purple-200',
                        ],
                        [
                            'emoji' => '🔧',
                            'title' => 'Jasa & Konsultan',
                            'desc' => 'Project billing, timesheet, kontrak, CRM pipeline',
                            'color' => 'bg-rose-50 border-rose-200',
                        ],
                        [
                            'emoji' => '🧱',
                            'title' => 'Pabrik Beton',
                            'desc' => 'Mix design SNI (K-175 s/d K-500), hitung kebutuhan material, HPP per m³',
                            'color' => 'bg-cyan-50 border-cyan-200',
                        ],
                        [
                            'emoji' => '💄',
                            'title' => 'Kosmetik & Beauty',
                            'desc' => 'Formula INCI, BPOM, batch production, QC lab, distribusi channel, analytics',
                            'color' => 'bg-pink-50 border-pink-200',
                        ],
                        [
                            'emoji' => '🐟',
                            'title' => 'Perikanan',
                            'desc' => 'Manajemen kolam, siklus budidaya, pakan, panen, cold chain',
                            'color' => 'bg-teal-50 border-teal-200',
                        ],
                        [
                            'emoji' => '🐄',
                            'title' => 'Peternakan',
                            'desc' => 'Manajemen ternak, breeding, pakan, kesehatan, produksi susu/telur',
                            'color' => 'bg-amber-50 border-amber-200',
                        ],
                        [
                            'emoji' => '🌐',
                            'title' => 'Telecom & ISP',
                            'desc' =>
                                'Manajemen pelanggan internet, bandwidth, voucher hotspot, billing otomatis, MikroTik integration',
                            'color' => 'bg-indigo-50 border-indigo-200',
                        ],
                        [
                            'emoji' => '🏨',
                            'title' => 'Hotel & Hospitality',
                            'desc' =>
                                'Reservasi kamar, housekeeping, minibar, restaurant, spa, tour package, night audit',
                            'color' => 'bg-blue-50 border-blue-200',
                        ],
                        [
                            'emoji' => '🍽️',
                            'title' => 'Restoran & F&B',
                            'desc' => 'POS restoran, meja, menu, kitchen order, table reservation, banquet event',
                            'color' => 'bg-orange-50 border-orange-200',
                        ],
                        [
                            'emoji' => '🏢',
                            'title' => 'Percetakan & Publishing',
                            'desc' => 'Print estimation, print job, prepress, plate management, web-to-print order',
                            'color' => 'bg-slate-50 border-slate-200',
                        ],
                        [
                            'emoji' => '🏥',
                            'title' => 'Healthcare & Rumah Sakit',
                            'desc' =>
                                'EMR, appointment, bed management, pharmacy, lab, radiology, BPJS, billing, patient portal',
                            'color' => 'bg-red-50 border-red-200',
                        ],
                    ];
                @endphp
                @foreach ($industries as $ind)
                    <div class="card-hover bg-white border {{ $ind['color'] }} rounded-2xl p-5">
                        <span class="text-3xl">{{ $ind['emoji'] }}</span>
                        <h3 class="font-bold text-gray-900 mt-3 mb-1.5 text-sm">{{ $ind['title'] }}</h3>
                        <p class="text-xs text-gray-500 leading-relaxed">{{ $ind['desc'] }}</p>
                    </div>
                @endforeach
            </div>
            <p class="text-center text-sm text-gray-400 mt-8">Ketik <span
                    class="font-mono bg-gray-100 px-2 py-0.5 rounded text-xs">"setup template konstruksi"</span> atau
                <span class="font-mono bg-gray-100 px-2 py-0.5 rounded text-xs">"setup template pertanian"</span> atau
                <span class="font-mono bg-gray-100 px-2 py-0.5 rounded text-xs">"setup template healthcare"</span> di
                AI
                Chat untuk langsung mulai.
            </p>
        </div>
    </section>

    {{-- ══ HOW IT WORKS ══ --}}
    <section class="py-24 bg-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="text-xs font-bold text-blue-600 uppercase tracking-widest">Cara Kerja</span>
                <h2 class="text-4xl font-black text-gray-900 mt-3">Mulai dalam 3 langkah</h2>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                @foreach ([['num' => '01', 'title' => 'Daftar & Setup', 'desc' => 'Buat akun perusahaan dalam 2 menit. Masukkan nama perusahaan, email, dan password. Langsung aktif.', 'icon' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z'], ['num' => '02', 'title' => 'Input Data Bisnis', 'desc' => 'Tambahkan produk, pelanggan, supplier, dan karyawan. Atau minta AI untuk membantu setup awal bisnis Anda.', 'icon' => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4'], ['num' => '03', 'title' => 'Kelola Lewat AI', 'desc' => 'Tanyakan apa saja ke Qalcuity AI. Cek stok, buat order, lihat laporan — semua lewat chat natural.', 'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z']] as $i => $step)
                    <div class="relative text-center">
                        <div
                            class="relative w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center mx-auto mb-5 shadow-lg shadow-blue-200">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="{{ $step['icon'] }}" />
                            </svg>
                            <span
                                class="absolute -top-2 -right-2 w-5 h-5 bg-white border-2 border-blue-200 rounded-full text-xs font-black text-blue-600 flex items-center justify-center">{{ $i + 1 }}</span>
                        </div>
                        <h3 class="font-bold text-gray-900 mb-2 text-base">{{ $step['title'] }}</h3>
                        <p class="text-sm text-gray-400 leading-relaxed">{{ $step['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══ PRICING ══ --}}
    <section id="harga" class="py-24 bg-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="text-xs font-bold text-blue-600 uppercase tracking-widest">Harga</span>
                <h2 class="text-4xl font-black text-gray-900 mt-3">Transparan, tanpa biaya tersembunyi</h2>
                <p class="text-gray-400 mt-4">Mulai gratis 14 hari, upgrade kapan saja.</p>
            </div>
            <div class="grid md:grid-cols-4 gap-5">
                @php
                    $plans = [
                        [
                            'name' => 'Starter',
                            'price' => 'Rp 99.000',
                            'period' => '/bulan',
                            'yearly' => 'Rp 999.000/tahun',
                            'desc' => 'Untuk UMKM yang baru mulai digitalisasi.',
                            'highlight' => false,
                            'badge' => null,
                            'features' => [
                                ['text' => 'POS, Inventori, Penjualan', 'ok' => true],
                                ['text' => 'Laporan Dasar', 'ok' => true],
                                ['text' => 'AI Chat (50 pesan/bulan)', 'ok' => true],
                                ['text' => 'Hingga 2 pengguna', 'ok' => true],
                                ['text' => '1 Gudang', 'ok' => true],
                                ['text' => 'Trial 14 hari gratis', 'ok' => true],
                            ],
                            'cta' => 'Mulai Trial Gratis',
                        ],
                        [
                            'name' => 'Business',
                            'price' => 'Rp 249.000',
                            'period' => '/bulan',
                            'yearly' => 'Rp 2.499.000/tahun',
                            'desc' => 'Untuk bisnis yang butuh fitur penjualan lengkap.',
                            'highlight' => false,
                            'badge' => null,
                            'features' => [
                                ['text' => 'Semua fitur Starter', 'ok' => true],
                                ['text' => 'Pembelian & Supplier', 'ok' => true],
                                ['text' => 'CRM, Helpdesk, Konsinyasi', 'ok' => true],
                                ['text' => 'Komisi Sales & Subscription', 'ok' => true],
                                ['text' => 'E-Commerce Integration', 'ok' => true],
                                ['text' => 'AI Chat (300 pesan/bulan)', 'ok' => true],
                                ['text' => 'Hingga 10 pengguna', 'ok' => true],
                            ],
                            'cta' => 'Mulai Trial Gratis',
                        ],
                        [
                            'name' => 'Professional',
                            'price' => 'Rp 499.000',
                            'period' => '/bulan',
                            'yearly' => 'Rp 4.999.000/tahun',
                            'desc' => 'Untuk bisnis menengah dengan operasi kompleks.',
                            'highlight' => true,
                            'badge' => 'Paling Populer',
                            'features' => [
                                ['text' => 'Semua fitur Business', 'ok' => true],
                                ['text' => 'HRM, Payroll, Aset', 'ok' => true],
                                ['text' => 'Manufaktur (BOM & MRP)', 'ok' => true],
                                ['text' => 'Fleet, Kontrak, Landed Cost', 'ok' => true],
                                ['text' => 'Project Billing & Forecasting', 'ok' => true],
                                ['text' => 'Telecom/ISP Module', 'ok' => true],
                                ['text' => 'Hospitality Module', 'ok' => true],
                                ['text' => 'AI Chat (1.000 pesan/bulan)', 'ok' => true],
                            ],
                            'cta' => 'Mulai Trial Gratis',
                        ],
                        [
                            'name' => 'Enterprise',
                            'price' => 'Rp 999.000',
                            'period' => '/bulan',
                            'yearly' => 'Rp 9.999.000/tahun',
                            'desc' => 'Untuk bisnis besar tanpa batas.',
                            'highlight' => false,
                            'badge' => null,
                            'features' => [
                                ['text' => 'Semua fitur Professional', 'ok' => true],
                                ['text' => 'AI & User Tak Terbatas', 'ok' => true],
                                ['text' => 'Multi Company & Konsolidasi', 'ok' => true],
                                ['text' => 'Zero Input OCR & WhatsApp Bot', 'ok' => true],
                                ['text' => 'Custom API & Digital Signature', 'ok' => true],
                                ['text' => 'Fingerprint/RFID Integration', 'ok' => true],
                                ['text' => 'Prioritas Support & SLA 99.9%', 'ok' => true],
                            ],
                            'cta' => 'Hubungi Kami',
                        ],
                    ];
                @endphp
                @foreach ($plans as $plan)
                    <div
                        class="relative flex flex-col rounded-3xl border {{ $plan['highlight'] ? 'border-blue-500 shadow-2xl shadow-blue-100 scale-[1.02]' : 'border-gray-200 shadow-sm' }} bg-white p-7">
                        @if ($plan['badge'])
                            <div
                                class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-xs font-bold px-4 py-1.5 rounded-full shadow">
                                {{ $plan['badge'] }}
                            </div>
                        @endif
                        <div class="mb-6">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">
                                {{ $plan['name'] }}</p>
                            <div class="flex items-end gap-1 mb-1">
                                <span
                                    class="text-3xl font-black text-gray-900 tracking-tight">{{ $plan['price'] }}</span>
                                <span class="text-sm text-gray-400 mb-1">{{ $plan['period'] }}</span>
                            </div>
                            <p class="text-xs text-gray-400">{{ $plan['yearly'] }}</p>
                            <p class="text-sm text-gray-500 mt-3">{{ $plan['desc'] }}</p>
                        </div>
                        <ul class="space-y-2.5 flex-1 mb-7">
                            @foreach ($plan['features'] as $feat)
                                <li
                                    class="flex items-center gap-2.5 text-sm {{ $feat['ok'] ? 'text-gray-700' : 'text-gray-300' }}">
                                    @if ($feat['ok'])
                                        <svg class="w-4 h-4 text-green-500 shrink-0" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 text-gray-200 shrink-0" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    @endif
                                    {{ $feat['text'] }}
                                </li>
                            @endforeach
                        </ul>
                        <a href="{{ route('register') }}"
                            class="block text-center py-3 rounded-2xl text-sm font-semibold transition
                          {{ $plan['highlight']
                              ? 'bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white shadow-sm shadow-blue-200'
                              : 'bg-gray-100 hover:bg-gray-200 text-gray-800' }}">
                            {{ $plan['cta'] }}
                        </a>
                    </div>
                @endforeach
            </div>
            <p class="text-center text-xs text-gray-400 mt-8">Semua plan sudah termasuk trial gratis. Tidak perlu kartu
                kredit. Batalkan kapan saja.</p>
        </div>
    </section>

    {{-- ══ DOKUMENTASI ══ --}}
    <section id="dokumentasi" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="text-xs font-bold text-blue-600 uppercase tracking-widest">Dokumentasi</span>
                <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mt-3">Panduan Lengkap Penggunaan</h2>
                <p class="text-gray-500 mt-4 max-w-2xl mx-auto">
                    Akses dokumentasi lengkap untuk semua modul dan fitur Qalcuity ERP
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                {{-- User Manual --}}
                <div
                    class="card-hover bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100 rounded-2xl p-8">
                    <div class="w-14 h-14 bg-white rounded-xl flex items-center justify-center mb-6 shadow-sm">
                        <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">User Manual</h3>
                    <p class="text-gray-600 mb-6 text-sm leading-relaxed">
                        Panduan lengkap penggunaan semua modul: Finance, Sales, Inventory, HRM, Healthcare, Hotel, dan
                        lainnya.
                    </p>
                    <ul class="space-y-2 mb-6 text-sm text-gray-700">
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            Quick Start Guide
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            Step-by-step Tutorials
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            Troubleshooting & FAQ
                        </li>
                    </ul>
                    <a href="{{ route('documentation') }}"
                        class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 font-semibold text-sm transition">
                        Baca Dokumentasi
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                </div>

                {{-- API Documentation --}}
                <div
                    class="card-hover bg-gradient-to-br from-purple-50 to-pink-50 border border-purple-100 rounded-2xl p-8">
                    <div class="w-14 h-14 bg-white rounded-xl flex items-center justify-center mb-6 shadow-sm">
                        <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">API Documentation</h3>
                    <p class="text-gray-600 mb-6 text-sm leading-relaxed">
                        Dokumentasi REST API lengkap dengan 233+ endpoints untuk integrasi dengan sistem eksternal.
                    </p>
                    <ul class="space-y-2 mb-6 text-sm text-gray-700">
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-purple-600 mt-0.5 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            OpenAPI/Swagger Spec
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-purple-600 mt-0.5 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            Authentication Guides
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-purple-600 mt-0.5 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            Code Examples
                        </li>
                    </ul>
                    <a href="{{ url('/api-docs') }}" target="_blank"
                        class="inline-flex items-center gap-2 text-purple-600 hover:text-purple-700 font-semibold text-sm transition">
                        Lihat API Docs
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                </div>

                {{-- Video Tutorials --}}
                <div
                    class="card-hover bg-gradient-to-br from-green-50 to-teal-50 border border-green-100 rounded-2xl p-8">
                    <div class="w-14 h-14 bg-white rounded-xl flex items-center justify-center mb-6 shadow-sm">
                        <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Video Tutorials</h3>
                    <p class="text-gray-600 mb-6 text-sm leading-relaxed">
                        Video tutorial interaktif untuk mempercepat proses belajar dan onboard tim Anda.
                    </p>
                    <ul class="space-y-2 mb-6 text-sm text-gray-700">
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-green-600 mt-0.5 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            Getting Started Series
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-green-600 mt-0.5 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            Module Walkthroughs
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-green-600 mt-0.5 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            Advanced Features
                        </li>
                    </ul>
                    <a href="#tutorial"
                        class="inline-flex items-center gap-2 text-green-600 hover:text-green-700 font-semibold text-sm transition">
                        Tonton Video
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Quick Links --}}
            <div class="mt-12 bg-gray-50 rounded-2xl p-8 border border-gray-200">
                <h3 class="text-lg font-bold text-gray-900 mb-6 text-center">Quick Links</h3>
                <div class="grid md:grid-cols-4 gap-4">
                    <a href="{{ route('documentation') }}"
                        class="flex items-center gap-3 p-4 bg-white rounded-xl border border-gray-200 hover:border-blue-300 hover:shadow-sm transition group">
                        <span class="text-2xl">📋</span>
                        <div>
                            <div class="font-semibold text-sm text-gray-900 group-hover:text-blue-600">Modul Guides
                            </div>
                            <div class="text-xs text-gray-500">10+ industri</div>
                        </div>
                    </a>
                    <a href="{{ route('documentation') }}#common-tasks"
                        class="flex items-center gap-3 p-4 bg-white rounded-xl border border-gray-200 hover:border-purple-300 hover:shadow-sm transition group">
                        <span class="text-2xl">📝</span>
                        <div>
                            <div class="font-semibold text-sm text-gray-900 group-hover:text-purple-600">Common Tasks
                            </div>
                            <div class="text-xs text-gray-500">Step-by-step</div>
                        </div>
                    </a>
                    <a href="{{ route('documentation') }}#troubleshooting"
                        class="flex items-center gap-3 p-4 bg-white rounded-xl border border-gray-200 hover:border-green-300 hover:shadow-sm transition group">
                        <span class="text-2xl">🔧</span>
                        <div>
                            <div class="font-semibold text-sm text-gray-900 group-hover:text-green-600">Troubleshooting
                            </div>
                            <div class="text-xs text-gray-500">Solusi cepat</div>
                        </div>
                    </a>
                    <a href="{{ route('documentation') }}#faq"
                        class="flex items-center gap-3 p-4 bg-white rounded-xl border border-gray-200 hover:border-orange-300 hover:shadow-sm transition group">
                        <span class="text-2xl">❓</span>
                        <div>
                            <div class="font-semibold text-sm text-gray-900 group-hover:text-orange-600">FAQ</div>
                            <div class="text-xs text-gray-500">30+ questions</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ══ FAQ ══ --}}
    <section id="faq" class="py-24 bg-gray-50">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="text-xs font-bold text-blue-600 uppercase tracking-widest">FAQ</span>
                <h2 class="text-4xl font-black text-gray-900 mt-3">Pertanyaan yang sering ditanyakan</h2>
            </div>
            <div x-data="{ active: null }" class="space-y-2">
                @php
                    $faqs = [
                        [
                            'q' => 'Apakah data saya aman?',
                            'a' =>
                                'Ya. Setiap perusahaan memiliki data yang terisolasi penuh (multi-tenant). Tidak ada akses silang antar perusahaan. Semua data dienkripsi.',
                        ],
                        [
                            'q' => 'Industri apa saja yang didukung?',
                            'a' =>
                                'Qalcuity ERP mendukung semua jenis industri: retail, F&B, konstruksi, pabrik beton, pertanian/perkebunan, manufaktur, distributor, jasa/konsultan, dan lainnya. Setiap industri memiliki template siap pakai dengan modul yang direkomendasikan.',
                        ],
                        [
                            'q' => 'Apakah cocok untuk bisnis konstruksi?',
                            'a' =>
                                'Ya. Kami memiliki modul khusus konstruksi: RAB (Rencana Anggaran Biaya) dengan breakdown volume × harga satuan × koefisien, mix design beton standar SNI (K-175 s/d K-500), progress fisik per volume, dan billing termin dengan retensi.',
                        ],
                        [
                            'q' => 'Apakah cocok untuk pertanian/perkebunan?',
                            'a' =>
                                'Ya. Modul pertanian mencakup: manajemen lahan/blok kebun, siklus tanam (persiapan → tanam → panen → pasca panen), pencatatan panen per grade kualitas, dan analisis biaya per hektar serta HPP per kg hasil panen.',
                        ],
                        [
                            'q' => 'Apakah AI bisa salah mengeksekusi perintah?',
                            'a' =>
                                'Qalcuity AI memiliki lapisan validasi sebelum melakukan operasi write ke database. Untuk aksi kritis, AI akan meminta konfirmasi terlebih dahulu.',
                        ],
                        [
                            'q' => 'Berapa banyak pengguna yang bisa ditambahkan?',
                            'a' =>
                                'Starter: 2 pengguna, Business: 10 pengguna, Professional: 25 pengguna, Enterprise: tidak terbatas.',
                        ],
                        [
                            'q' => 'Apakah bisa diakses dari mobile?',
                            'a' =>
                                'Ya. Qalcuity ERP adalah PWA (Progressive Web App) yang bisa diinstall di smartphone dan bekerja offline untuk modul tertentu.',
                        ],
                        [
                            'q' => 'Bagaimana cara migrasi data dari sistem lama?',
                            'a' =>
                                'Kami menyediakan bulk import CSV/Excel untuk semua master data: produk, pelanggan, supplier, karyawan, gudang, dan chart of accounts. Mendukung mode update untuk data yang sudah ada.',
                        ],
                        [
                            'q' => 'Apakah ada API untuk integrasi?',
                            'a' =>
                                'Ya. REST API lengkap dengan rate limiting per plan, webhook outbound untuk notifikasi real-time ke sistem pihak ketiga, dan HMAC signature verification.',
                        ],
                        [
                            'q' => 'Apakah ada kontrak jangka panjang?',
                            'a' => 'Tidak. Semua plan berbasis bulanan dan bisa dibatalkan kapan saja tanpa penalti.',
                        ],
                    ];
                @endphp
                @foreach ($faqs as $i => $faq)
                    <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm">
                        <button @click="active = active === {{ $i }} ? null : {{ $i }}"
                            class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-gray-50/50 transition">
                            <span class="font-semibold text-gray-800 text-sm pr-4">{{ $faq['q'] }}</span>
                            <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform duration-200"
                                :class="active === {{ $i }} ? 'rotate-180' : ''" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="active === {{ $i }}" x-transition class="px-6 pb-4">
                            <p class="text-sm text-gray-500 leading-relaxed">{{ $faq['a'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══ CONTACT ══ --}}
    <section id="kontak" class="py-20 bg-white border-t border-gray-100">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <span class="text-xs font-bold text-green-600 uppercase tracking-widest">Kontak</span>
            <h2 class="text-3xl font-black text-gray-900 mt-3 mb-3">Ada pertanyaan? Hubungi kami</h2>
            <p class="text-gray-400 text-sm mb-10">Tim kami siap membantu Anda via WhatsApp — respons cepat di jam
                kerja.</p>
            <a href="https://wa.me/6281654932383" target="_blank" rel="noopener"
                class="inline-flex items-center gap-3 bg-green-500 hover:bg-green-600 text-white font-semibold px-8 py-4 rounded-2xl transition shadow-lg shadow-green-200 text-sm">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                </svg>
                Chat WhatsApp Sekarang
            </a>
            <p class="text-xs text-gray-400 mt-4">+62 816-5493-2383 · Senin–Sabtu, 08.00–17.00 WIB</p>
        </div>
    </section>

    {{-- ══ AFFILIATE PROGRAM ══ --}}
    <section id="affiliate" class="py-20 bg-gradient-to-br from-indigo-900 via-purple-900 to-indigo-900">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <span class="text-xs font-bold text-purple-300 uppercase tracking-widest">Program Afiliasi</span>
            <h2 class="text-4xl font-black text-white mt-3 mb-4">Dapatkan Komisi 10%</h2>
            <p class="text-purple-200 max-w-xl mx-auto mb-8">
                Rekomendasikan Qalcuity ERP ke bisnis lain dan dapatkan komisi 10% dari setiap pembayaran subscription
                mereka. Komisi berlaku selamanya selama pelanggan aktif.
            </p>
            <div class="grid sm:grid-cols-3 gap-5 mb-10 text-left">
                @foreach ([['icon' => '🔗', 'title' => 'Dapatkan Link Referral', 'desc' => 'Daftar sebagai affiliate dan dapatkan link unik Anda.'], ['icon' => '📢', 'title' => 'Bagikan ke Jaringan', 'desc' => 'Share link ke teman, komunitas, atau media sosial Anda.'], ['icon' => '💰', 'title' => 'Terima Komisi', 'desc' => 'Setiap kali referral Anda berlangganan, Anda dapat 10% komisi.']] as $step)
                    <div class="bg-white/10 border border-white/10 rounded-2xl p-5 backdrop-blur">
                        <span class="text-2xl">{{ $step['icon'] }}</span>
                        <h3 class="font-bold text-white mt-3 mb-1 text-sm">{{ $step['title'] }}</h3>
                        <p class="text-xs text-purple-200 leading-relaxed">{{ $step['desc'] }}</p>
                    </div>
                @endforeach
            </div>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="https://wa.me/6281654932383?text=Halo%2C%20saya%20tertarik%20menjadi%20affiliate%20Qalcuity%20ERP"
                    target="_blank" rel="noopener"
                    class="inline-flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white font-semibold px-8 py-3.5 rounded-2xl transition shadow-lg text-sm">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                    </svg>
                    Daftar Jadi Affiliate via WhatsApp
                </a>
            </div>
            <p class="text-xs text-purple-300/60 mt-4">Pendaftaran diproses manual oleh tim kami. Anda akan mendapat
                akun affiliate dalam 1x24 jam.</p>
        </div>
    </section>

    {{-- ══ JASA INSTALASI OPEN CLOW ══ --}}
    <section id="open-clow" class="py-24 bg-[#0f172a]">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="text-xs font-bold text-cyan-400 uppercase tracking-widest">Layanan Tambahan</span>
                <h2 class="text-4xl font-black text-white mt-3">Jasa Instalasi Open Clow Agent</h2>
                <p class="text-slate-400 mt-4 max-w-xl mx-auto">AI Agent khusus untuk bisnis Anda — custom training,
                    integrasi sistem existing, dan maintenance selamanya.</p>
            </div>
            <div class="grid md:grid-cols-2 gap-6 max-w-3xl mx-auto">
                {{-- Paket Instalasi --}}
                <div class="relative bg-white/5 border border-white/10 rounded-3xl p-8 backdrop-blur">
                    <div
                        class="w-12 h-12 rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-1">Paket Instalasi</h3>
                    <p class="text-3xl font-black text-cyan-400 mb-1">Rp 7.000.000</p>
                    <p class="text-xs text-slate-400 mb-6">Sekali bayar + FREE maintenance selamanya</p>
                    <ul class="space-y-2.5 mb-8">
                        @foreach (['Custom AI training sesuai bisnis kamu', 'Integrasi dengan sistem existing', 'Setup & konfigurasi lengkap', 'Training team (2 sesi)', 'Maintenance & update GRATIS', '24/7 technical support'] as $f)
                            <li class="flex items-center gap-2.5 text-sm text-slate-300">
                                <svg class="w-4 h-4 text-cyan-400 shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                {{ $f }}
                            </li>
                        @endforeach
                    </ul>
                    <a href="https://wa.me/6281654932383?text=Halo%2C%20saya%20tertarik%20dengan%20Paket%20Instalasi%20Open%20Clow%20Agent"
                        target="_blank" rel="noopener"
                        class="block text-center py-3 rounded-2xl text-sm font-semibold bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white transition shadow-lg shadow-cyan-900/30">
                        Pesan via WhatsApp
                    </a>
                </div>

                {{-- Biaya Operasional --}}
                <div class="relative bg-white/5 border border-white/10 rounded-3xl p-8 backdrop-blur">
                    <div
                        class="w-12 h-12 rounded-2xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-1">Biaya Operasional</h3>
                    <p class="text-3xl font-black text-violet-400 mb-1">Rp 1.000.000</p>
                    <p class="text-xs text-slate-400 mb-6">Per bulan untuk 100.000 AI tokens</p>
                    <ul class="space-y-2.5 mb-8">
                        @foreach (['100.000 AI tokens per bulan', '~3.000 interaksi AI per bulan', 'Unlimited users dalam tim', 'Real-time analytics & reporting', 'Auto-scaling sesuai kebutuhan', 'Priority support'] as $f)
                            <li class="flex items-center gap-2.5 text-sm text-slate-300">
                                <svg class="w-4 h-4 text-violet-400 shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                {{ $f }}
                            </li>
                        @endforeach
                    </ul>
                    <a href="https://wa.me/6281654932383?text=Halo%2C%20saya%20tertarik%20dengan%20Biaya%20Operasional%20Open%20Clow%20Agent"
                        target="_blank" rel="noopener"
                        class="block text-center py-3 rounded-2xl text-sm font-semibold bg-gradient-to-r from-violet-500 to-purple-600 hover:from-violet-400 hover:to-purple-500 text-white transition shadow-lg shadow-violet-900/30">
                        Pesan via WhatsApp
                    </a>
                </div>
            </div>

            {{-- Fungsi Open Clow --}}
            <div class="mt-16 text-center">
                <h3 class="text-2xl font-black text-white mb-8">Fungsi Open Clow Agent untuk Bisnis Kamu</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @php
                        $clowFuncs = [
                            [
                                'emoji' => '🤖',
                                'title' => 'Customer Service AI',
                                'desc' => 'Jawab pertanyaan pelanggan 24/7 otomatis',
                            ],
                            [
                                'emoji' => '📊',
                                'title' => 'Analisis Data',
                                'desc' => 'Analisis penjualan, stok, dan keuangan real-time',
                            ],
                            [
                                'emoji' => '📝',
                                'title' => 'Pembuatan Konten',
                                'desc' => 'Generate deskripsi produk, email, dan laporan',
                            ],
                            [
                                'emoji' => '🔗',
                                'title' => 'Integrasi Sistem',
                                'desc' => 'Hubungkan dengan WhatsApp, Shopee, Tokopedia',
                            ],
                        ];
                    @endphp
                    @foreach ($clowFuncs as $cf)
                        <div class="bg-white/5 border border-white/10 rounded-2xl p-5 text-left">
                            <span class="text-2xl">{{ $cf['emoji'] }}</span>
                            <h4 class="font-bold text-white text-sm mt-3 mb-1">{{ $cf['title'] }}</h4>
                            <p class="text-xs text-slate-400 leading-relaxed">{{ $cf['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ══ CTA BOTTOM ══ --}}
    <section class="py-24 relative overflow-hidden bg-[#0a0f1e]">
        <div class="hero-glow absolute inset-0 pointer-events-none"></div>
        <div
            class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[400px] bg-indigo-600/15 rounded-full blur-3xl pointer-events-none">
        </div>
        <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl sm:text-5xl font-black text-white leading-tight mb-4 tracking-tight">
                Siap transformasi bisnis<br>Anda dengan AI?
            </h2>
            <p class="text-slate-400 mb-10 text-lg">Mulai gratis. Tidak perlu kartu kredit.</p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('register') }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-semibold px-8 py-4 rounded-2xl transition shadow-lg shadow-blue-900/40 text-sm">
                    Mulai Gratis Sekarang
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ route('login') }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-white/8 hover:bg-white/12 border border-white/15 text-white font-medium px-8 py-4 rounded-2xl transition text-sm backdrop-blur">
                    Sudah punya akun? Masuk
                </a>
            </div>
            <div class="mt-10 flex items-center justify-center gap-8 text-xs text-slate-500">
                <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-green-400" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>Tanpa kartu kredit</span>
                <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-green-400" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>Setup 2 menit</span>
                <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-green-400" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>Batalkan kapan saja</span>
            </div>
        </div>
    </section>

    {{-- ══ DEVELOPER API CALLOUT ══ --}}
    <section class="py-16 bg-gray-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div
                class="flex flex-col md:flex-row items-center justify-between gap-8 rounded-3xl border border-white/10 bg-white/5 backdrop-blur px-8 py-10">
                <div class="flex items-start gap-5">
                    <div
                        class="flex-shrink-0 w-12 h-12 rounded-2xl bg-blue-600/20 border border-blue-500/30 flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-blue-400 uppercase tracking-widest mb-1">Developer API</p>
                        <h3 class="text-xl font-black text-white mb-2">Integrasi REST API Lengkap</h3>
                        <p class="text-sm text-gray-400 max-w-xl leading-relaxed">
                            Hubungkan sistem Anda dengan Qalcuity ERP via REST API. Lihat semua endpoint, schema, dan
                            coba request langsung dari browser — tanpa perlu login.
                        </p>
                    </div>
                </div>
                <a href="{{ url('/api-docs') }}" target="_blank" rel="noopener"
                    class="flex-shrink-0 inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold transition shadow-lg shadow-blue-900/40 whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    API Documentation
                    <svg class="w-3.5 h-3.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    {{-- ══ FOOTER ══ --}}
    <footer class="bg-gray-50 border-t border-gray-200">
        {{-- Main Footer Content --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8 lg:gap-6">
                {{-- Brand Column --}}
                <div class="lg:col-span-2">
                    <div class="flex items-center gap-2.5 mb-4">
                        <img src="/logo.png" alt="Qalcuity ERP" class="h-8 w-auto object-contain brightness-0"
                            loading="lazy">
                    </div>
                    <p class="text-sm text-gray-600 leading-relaxed max-w-sm mb-6">
                        Platform ERP berbasis AI untuk bisnis Indonesia. Kelola semua aspek bisnis Anda lewat percakapan
                        natural.
                    </p>

                    {{-- Social Media Links --}}
                    <div class="flex items-center gap-3">
                        <a href="https://linkedin.com/company/qalcuity" target="_blank" rel="noopener"
                            class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-blue-600 hover:border-blue-300 transition">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                            </svg>
                        </a>
                        <a href="https://twitter.com/qalcuity" target="_blank" rel="noopener"
                            class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-gray-900 hover:border-gray-400 transition">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                            </svg>
                        </a>
                        <a href="https://github.com/qalcuity" target="_blank" rel="noopener"
                            class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-gray-900 hover:border-gray-400 transition">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z" />
                            </svg>
                        </a>
                        <a href="https://wa.me/6281654932383" target="_blank" rel="noopener"
                            class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-green-600 hover:border-green-300 transition">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Product Column --}}
                <div>
                    <p class="text-xs font-bold text-gray-900 uppercase tracking-widest mb-4">Produk</p>
                    <ul class="space-y-3 text-sm">
                        <li><a href="#fitur" class="text-gray-600 hover:text-blue-600 transition">Fitur</a></li>
                        <li><a href="#modul" class="text-gray-600 hover:text-blue-600 transition">Modul</a></li>
                        <li><a href="#industri" class="text-gray-600 hover:text-blue-600 transition">Industri</a></li>
                        <li><a href="#harga" class="text-gray-600 hover:text-blue-600 transition">Harga</a></li>
                        <li><a href="{{ route('documentation') }}"
                                class="text-gray-600 hover:text-blue-600 transition">Dokumentasi</a></li>
                        <li>
                            <a href="{{ url('/api-docs') }}" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-1.5 text-gray-600 hover:text-blue-600 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                </svg>
                                API Docs
                            </a>
                        </li>
                    </ul>
                </div>

                {{-- Company Column --}}
                <div>
                    <p class="text-xs font-bold text-gray-900 uppercase tracking-widest mb-4">Perusahaan</p>
                    <ul class="space-y-3 text-sm">
                        <li>
                            <a href="{{ route('about.index') }}"
                                class="text-gray-600 hover:text-blue-600 transition">Tentang Kami</a>
                        </li>
                        <li>
                            <a href="{{ route('about.careers') }}"
                                class="text-gray-600 hover:text-blue-600 transition">Karir</a>
                        </li>
                        <li>
                            <a href="{{ route('resources.blog') }}"
                                class="text-gray-600 hover:text-blue-600 transition">Blog</a>
                        </li>
                        <li>
                            <a href="{{ route('about.partners') }}"
                                class="text-gray-600 hover:text-blue-600 transition">Partner</a>
                        </li>
                        <li>
                            <a href="https://wa.me/6281654932383" target="_blank" rel="noopener"
                                class="text-gray-600 hover:text-blue-600 transition">Kontak</a>
                        </li>
                    </ul>
                </div>

                {{-- Support Column --}}
                <div>
                    <p class="text-xs font-bold text-gray-900 uppercase tracking-widest mb-4">Support</p>
                    <ul class="space-y-3 text-sm">
                        <li>
                            <a href="{{ route('resources.help') }}"
                                class="text-gray-600 hover:text-blue-600 transition">Help Center</a>
                        </li>
                        <li>
                            <a href="{{ route('resources.status') }}"
                                class="text-gray-600 hover:text-blue-600 transition">System Status</a>
                        </li>
                        <li>
                            <a href="{{ route('resources.community') }}"
                                class="text-gray-600 hover:text-blue-600 transition">Community</a>
                        </li>
                        <li>
                            <a href="{{ route('login') }}"
                                class="text-gray-600 hover:text-blue-600 transition">Login</a>
                        </li>
                        <li>
                            <a href="{{ route('register') }}"
                                class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-700 transition font-semibold">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                                Daftar Gratis
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Legal & Bottom Bar --}}
        <div class="border-t border-gray-200 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                {{-- Legal Links --}}
                <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2 mb-4 text-sm">
                    <a href="{{ route('legal.privacy') }}" class="text-gray-600 hover:text-blue-600 transition">
                        Privacy Policy
                    </a>
                    <span class="text-gray-300">|</span>
                    <a href="{{ route('legal.terms') }}" class="text-gray-600 hover:text-blue-600 transition">
                        Terms of Service
                    </a>
                    <span class="text-gray-300">|</span>
                    <a href="{{ route('legal.cookies') }}" class="text-gray-600 hover:text-blue-600 transition">
                        Cookie Policy
                    </a>
                    <span class="text-gray-300">|</span>
                    <a href="{{ route('legal.security') }}" class="text-gray-600 hover:text-blue-600 transition">
                        Security
                    </a>
                    <span class="text-gray-300">|</span>
                    <a href="{{ route('legal.gdpr') }}" class="text-gray-600 hover:text-blue-600 transition">
                        GDPR
                    </a>
                </div>

                {{-- Copyright & Credits --}}
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-500">
                    <p>© {{ date('Y') }} <a href="{{ url('/') }}"
                            class="hover:text-blue-600 transition">Qalcuity</a>. All rights reserved.</p>
                    <div class="flex items-center gap-4">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            SOC 2 Compliant
                        </span>
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            ISO 27001
                        </span>
                        <span class="text-gray-400">Powered by <a href="https://noteds.com" target="_blank"
                                rel="noopener"
                                class="text-blue-600 hover:text-blue-700 font-semibold transition">Noteds
                                Technology</a></span>
                    </div>
                </div>
            </div>
        </div>
    </footer>

</body>

</html>
