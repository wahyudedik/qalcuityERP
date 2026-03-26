<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Qalcuity ERP — ERP Cerdas Berbasis AI untuk Bisnis Indonesia</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" href="/favicon.png">
    <meta name="description" content="Kelola inventory, penjualan, keuangan, SDM, dan 25+ modul bisnis dengan AI. Platform ERP SaaS modern untuk bisnis Indonesia.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .gradient-text { background: linear-gradient(135deg, #60a5fa, #818cf8, #a78bfa); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .hero-glow { background: radial-gradient(ellipse 80% 50% at 50% -20%, rgba(99,102,241,0.3), transparent); }
        .card-hover { transition: transform .2s ease, box-shadow .2s ease; }
        .card-hover:hover { transform: translateY(-3px); box-shadow: 0 12px 40px -8px rgba(0,0,0,.1); }
    </style>
</head>
<body class="font-[Inter,sans-serif] bg-white text-gray-900 antialiased">

{{-- ══ NAVBAR ══ --}}
<nav x-data="{ open: false }" class="fixed top-0 inset-x-0 z-50 bg-white/90 backdrop-blur-xl border-b border-gray-100/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <a href="{{ route('landing') }}" class="flex items-center gap-2.5">
            <img src="/logo.png" alt="Qalcuity ERP" class="h-8 w-auto object-contain brightness-0">
        </a>
        <div class="hidden md:flex items-center gap-0.5 text-sm font-medium">
            <a href="#fitur" class="px-4 py-2 rounded-xl text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition">Fitur</a>
            <a href="#modul" class="px-4 py-2 rounded-xl text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition">Modul</a>
            <a href="#harga" class="px-4 py-2 rounded-xl text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition">Harga</a>
            <a href="#open-clow" class="px-4 py-2 rounded-xl text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition">Open Clow</a>
            <a href="#affiliate" class="px-4 py-2 rounded-xl text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition">Affiliate</a>
            <a href="#faq"   class="px-4 py-2 rounded-xl text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition">FAQ</a>
            <a href="#kontak" class="px-4 py-2 rounded-xl text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition">Kontak</a>
        </div>
        <div class="hidden md:flex items-center gap-3">
            <a href="https://wa.me/6281654932383" target="_blank" rel="noopener"
               class="inline-flex items-center gap-1.5 text-sm font-medium text-green-600 hover:text-green-700 px-4 py-2 rounded-xl hover:bg-green-50 transition">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                Hubungi Kami
            </a>
            <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 px-4 py-2 rounded-xl hover:bg-gray-50 transition">Masuk</a>
            <a href="{{ route('register') }}" class="text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 px-5 py-2.5 rounded-xl transition shadow-sm shadow-blue-200">
                Coba Gratis
            </a>
        </div>
        <button @click="open = !open" class="md:hidden p-2 rounded-xl hover:bg-gray-100 text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                <path x-show="open"  stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    <div x-show="open" x-transition class="md:hidden border-t border-gray-100 bg-white px-4 py-4 space-y-1">
        <a href="#fitur" @click="open=false" class="block px-4 py-2.5 rounded-xl text-sm text-gray-700 hover:bg-gray-50">Fitur</a>
        <a href="#modul" @click="open=false" class="block px-4 py-2.5 rounded-xl text-sm text-gray-700 hover:bg-gray-50">Modul</a>
        <a href="#harga" @click="open=false" class="block px-4 py-2.5 rounded-xl text-sm text-gray-700 hover:bg-gray-50">Harga</a>
        <a href="#open-clow" @click="open=false" class="block px-4 py-2.5 rounded-xl text-sm text-gray-700 hover:bg-gray-50">Open Clow</a>
        <a href="#affiliate" @click="open=false" class="block px-4 py-2.5 rounded-xl text-sm text-gray-700 hover:bg-gray-50">Affiliate</a>
        <a href="#faq"   @click="open=false" class="block px-4 py-2.5 rounded-xl text-sm text-gray-700 hover:bg-gray-50">FAQ</a>
        <a href="#kontak" @click="open=false" class="block px-4 py-2.5 rounded-xl text-sm text-gray-700 hover:bg-gray-50">Kontak</a>
        <div class="pt-3 flex flex-col gap-2 border-t border-gray-100 mt-2">
            <a href="https://wa.me/6281654932383" target="_blank" rel="noopener"
               class="flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-medium border border-green-200 text-green-700 bg-green-50">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                Hubungi via WhatsApp
            </a>
            <a href="{{ route('login') }}"    class="block text-center py-2.5 rounded-xl text-sm font-medium border border-gray-200 text-gray-700">Masuk</a>
            <a href="{{ route('register') }}" class="block text-center py-2.5 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600">Coba Gratis</a>
        </div>
    </div>
</nav>

{{-- ══ HERO ══ --}}
<section class="relative pt-28 pb-24 overflow-hidden bg-[#0a0f1e]">
    <div class="hero-glow absolute inset-0 pointer-events-none"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[900px] h-[600px] bg-indigo-600/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="inline-flex items-center gap-2 bg-white/5 border border-white/10 text-blue-300 text-xs font-semibold px-4 py-2 rounded-full mb-8 backdrop-blur">
            <span class="w-1.5 h-1.5 bg-blue-400 rounded-full animate-pulse"></span>
            Didukung Qalcuity AI — Generasi Terbaru
        </div>

        <h1 class="text-5xl sm:text-6xl lg:text-7xl font-black text-white leading-[1.08] tracking-tight mb-6">
            ERP yang bisa<br>
            <span class="gradient-text">Anda ajak bicara</span>
        </h1>

        <p class="text-lg text-slate-400 max-w-2xl mx-auto leading-relaxed mb-10">
            Kelola inventory, penjualan, keuangan, SDM, produksi, CRM, fleet, kontrak, helpdesk, dan 35+ modul bisnis lainnya — cukup dengan mengetik perintah. AI kami memahami konteks bisnis Anda dan langsung bertindak.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-20">
            <a href="{{ route('register') }}"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-semibold px-8 py-3.5 rounded-2xl transition shadow-lg shadow-blue-900/40 text-sm">
                Mulai Gratis 14 Hari
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
            <a href="#demo"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-white/8 hover:bg-white/12 border border-white/15 text-white font-medium px-8 py-3.5 rounded-2xl transition text-sm backdrop-blur">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
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
                        <div class="flex-1 mx-4 bg-white/5 rounded-lg px-3 py-1 text-xs text-slate-500 text-center">Qalcuity AI Chat</div>
                    </div>
                    <div class="p-5 space-y-4 text-left">
                        <div class="flex gap-3">
                            <div class="w-7 h-7 rounded-full bg-slate-700 flex items-center justify-center text-xs text-slate-300 shrink-0 mt-0.5 font-semibold">A</div>
                            <div class="bg-slate-800/80 border border-white/5 rounded-2xl rounded-tl-sm px-4 py-3 text-sm text-slate-200 max-w-xs">
                                Stok produk apa yang hampir habis minggu ini?
                            </div>
                        </div>
                        <div class="flex gap-3 justify-end">
                            <div class="bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl rounded-tr-sm px-4 py-3 text-sm text-white max-w-sm">
                                <p class="font-semibold mb-2.5">📦 3 Produk Stok Menipis</p>
                                <div class="space-y-1.5 text-xs text-blue-100 bg-white/10 rounded-xl p-3">
                                    <div class="flex justify-between"><span>Laptop ASUS X515</span><span class="text-red-300 font-bold">2 unit</span></div>
                                    <div class="flex justify-between"><span>Mouse Wireless</span><span class="text-yellow-300 font-bold">5 unit</span></div>
                                    <div class="flex justify-between"><span>Keyboard Mech.</span><span class="text-yellow-300 font-bold">8 unit</span></div>
                                </div>
                                <p class="text-xs text-blue-200 mt-2.5">Mau saya buatkan Purchase Order ke supplier?</p>
                            </div>
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center shrink-0 mt-0.5 text-white text-xs font-bold">Q</div>
                        </div>
                        <div class="flex gap-3">
                            <div class="w-7 h-7 rounded-full bg-slate-700 flex items-center justify-center text-xs text-slate-300 shrink-0 mt-0.5 font-semibold">A</div>
                            <div class="bg-slate-800/80 border border-white/5 rounded-2xl rounded-tl-sm px-4 py-3 text-sm text-slate-200 max-w-xs">
                                Ya, buatkan PO untuk Laptop ASUS ke PT. Supplier Jaya
                            </div>
                        </div>
                        <div class="flex gap-3 justify-end">
                            <div class="bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl rounded-tr-sm px-4 py-3 text-sm text-white max-w-sm">
                                ✅ Purchase Order <span class="font-bold">PO-2026-0042</span> berhasil dibuat untuk PT. Supplier Jaya — 10 unit Laptop ASUS X515.
                            </div>
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center shrink-0 mt-0.5 text-white text-xs font-bold">Q</div>
                        </div>
                    </div>
                    <div class="px-5 pb-5">
                        <div class="flex items-center gap-3 bg-white/5 border border-white/10 rounded-2xl px-4 py-3">
                            <span class="text-sm text-slate-500 flex-1">Ketik perintah ERP Anda...</span>
                            <div class="w-7 h-7 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center">
                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
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
            @foreach([
                ['value' => '35+',    'label' => 'Modul ERP Terintegrasi'],
                ['value' => '99.9%',  'label' => 'Uptime SLA'],
                ['value' => 'AI',     'label' => 'Powered by Qalcuity AI'],
                ['value' => '4.9★',   'label' => 'Rating Pengguna'],
            ] as $s)
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
            <h2 class="text-4xl font-black text-gray-900 mt-3 leading-tight">ERP yang bekerja<br>seperti asisten pribadi</h2>
            <p class="text-gray-400 mt-4 max-w-lg mx-auto text-base">Tidak perlu klik menu berlapis. Cukup ketik apa yang Anda butuhkan.</p>
        </div>
        <div class="grid md:grid-cols-3 gap-5">
            @php
            $features = [
                ['icon'=>'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z','title'=>'AI Chat ERP','color'=>'blue','desc'=>'Tanya stok, buat PO, cek laporan keuangan — semua lewat percakapan natural. Qalcuity AI memahami konteks bisnis Anda dan langsung bertindak.'],
                ['icon'=>'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z','title'=>'Rich Output Visual','color'=>'indigo','desc'=>'AI merespons dengan grafik interaktif, KPI cards, tabel data, invoice, surat resmi, dan tombol aksi langsung di dalam chat.'],
                ['icon'=>'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z','title'=>'Analisis Gambar & Dokumen','color'=>'purple','desc'=>'Upload foto struk, PDF laporan, atau CSV data. AI mengekstrak informasi dan menawarkan untuk langsung dicatat ke sistem.'],
                ['icon'=>'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z','title'=>'Laporan & Export','color'=>'green','desc'=>'Laporan laba rugi, aging piutang, valuasi inventori, payroll — unduh Excel/PDF atau visualisasikan sebagai grafik interaktif.'],
                ['icon'=>'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4','title'=>'Multi-tenant & Multi-role','color'=>'orange','desc'=>'Data setiap perusahaan terisolasi penuh. Kelola tim dengan role Admin, Manager, dan Staff dengan kontrol akses fleksibel.'],
                ['icon'=>'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z','title'=>'Bot WA & Telegram','color'=>'pink','desc'=>'Hubungkan WhatsApp atau Telegram ke sistem ERP. Terima notifikasi dan kelola bisnis langsung dari aplikasi chat favorit Anda.'],
            ];
            $cm = ['blue'=>['bg'=>'bg-blue-50','ic'=>'text-blue-600'],'indigo'=>['bg'=>'bg-indigo-50','ic'=>'text-indigo-600'],'purple'=>['bg'=>'bg-purple-50','ic'=>'text-purple-600'],'green'=>['bg'=>'bg-green-50','ic'=>'text-green-600'],'orange'=>['bg'=>'bg-orange-50','ic'=>'text-orange-600'],'pink'=>['bg'=>'bg-pink-50','ic'=>'text-pink-600']];
            @endphp
            @foreach($features as $f)
            @php $c = $cm[$f['color']]; @endphp
            <div class="card-hover bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                <div class="w-10 h-10 rounded-xl {{ $c['bg'] }} flex items-center justify-center mb-4">
                    <svg class="w-5 h-5 {{ $c['ic'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $f['icon'] }}"/>
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
            <p class="text-gray-400 mt-4 max-w-lg mx-auto">35+ modul terintegrasi, dikelola lewat satu antarmuka AI.</p>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
            @php
            $modules = [
                ['emoji'=>'📦','title'=>'Inventory',    'color'=>'blue',  'items'=>['Multi-gudang','Transfer stok','Stock opname','Konsinyasi']],
                ['emoji'=>'🛒','title'=>'Penjualan',    'color'=>'green', 'items'=>['Quotation → SO → Invoice','Piutang','Loyalty','Subscription Billing']],
                ['emoji'=>'🏭','title'=>'Pembelian',    'color'=>'orange','items'=>['Purchase Order','Supplier','Landed Cost','3-Way Matching']],
                ['emoji'=>'👥','title'=>'SDM & Payroll','color'=>'purple','items'=>['Karyawan & Absensi','Penggajian','Overtime','Komisi Sales']],
                ['emoji'=>'💰','title'=>'Keuangan',     'color'=>'indigo','items'=>['Jurnal GL','Anggaran','Rekonsiliasi Bank','Multi-currency']],
                ['emoji'=>'🏗️','title'=>'Manufaktur',   'color'=>'amber', 'items'=>['Work Order','BOM Multi-Level','MRP Planning','Work Center']],
                ['emoji'=>'📋','title'=>'Proyek',       'color'=>'teal',  'items'=>['Manajemen proyek','Timesheet','Project Billing','Milestone']],
                ['emoji'=>'🤝','title'=>'CRM & Sales',  'color'=>'rose',  'items'=>['Pipeline lead','Helpdesk & Tiket','Knowledge Base','SLA Tracking']],
                ['emoji'=>'🚛','title'=>'Fleet & Aset', 'color'=>'slate', 'items'=>['Kendaraan & Driver','BBM & Maintenance','Aset & Depresiasi','Trip Tracking']],
                ['emoji'=>'📝','title'=>'Kontrak & Dok','color'=>'yellow','items'=>['Manajemen Kontrak','Recurring Billing','Digital Signature','AI Forecasting']],
            ];
            $mc = ['blue'=>['b'=>'border-blue-200','t'=>'text-blue-700','d'=>'bg-blue-400'],'green'=>['b'=>'border-green-200','t'=>'text-green-700','d'=>'bg-green-400'],'orange'=>['b'=>'border-orange-200','t'=>'text-orange-700','d'=>'bg-orange-400'],'purple'=>['b'=>'border-purple-200','t'=>'text-purple-700','d'=>'bg-purple-400'],'indigo'=>['b'=>'border-indigo-200','t'=>'text-indigo-700','d'=>'bg-indigo-400'],'amber'=>['b'=>'border-amber-200','t'=>'text-amber-700','d'=>'bg-amber-400'],'teal'=>['b'=>'border-teal-200','t'=>'text-teal-700','d'=>'bg-teal-400'],'rose'=>['b'=>'border-rose-200','t'=>'text-rose-700','d'=>'bg-rose-400'],'slate'=>['b'=>'border-slate-200','t'=>'text-slate-700','d'=>'bg-slate-400'],'yellow'=>['b'=>'border-yellow-200','t'=>'text-yellow-700','d'=>'bg-yellow-400']];
            @endphp
            @foreach($modules as $m)
            @php $c = $mc[$m['color']]; @endphp
            <div class="card-hover bg-white border {{ $c['b'] }} rounded-2xl p-4">
                <div class="text-2xl mb-2.5">{{ $m['emoji'] }}</div>
                <h3 class="font-bold {{ $c['t'] }} mb-2.5 text-xs uppercase tracking-wide">{{ $m['title'] }}</h3>
                <ul class="space-y-1.5">
                    @foreach($m['items'] as $item)
                    <li class="flex items-center gap-1.5 text-xs text-gray-500">
                        <span class="w-1 h-1 rounded-full {{ $c['d'] }} shrink-0"></span>{{ $item }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endforeach
        </div>
        <div class="mt-5 flex flex-wrap gap-2 justify-center">
            @foreach(['🚚 Pengiriman','🛍️ E-Commerce','🤖 Bot WA/Telegram','🏦 Rekonsiliasi Bank','⭐ Loyalty Program','✍️ Tanda Tangan Digital','📊 AI Forecasting','🔔 Push Notification','🏪 Konsinyasi','📐 Project Billing','🔄 Subscription Billing','🎫 Helpdesk & Tiket','🚢 Landed Cost','💵 Komisi Sales'] as $tag)
            <span class="text-xs bg-white border border-gray-200 rounded-full px-3 py-1.5 text-gray-500 shadow-sm">{{ $tag }}</span>
            @endforeach
        </div>
        <div class="mt-8 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-3xl p-8 text-white text-center">
            <p class="text-lg font-bold mb-2">Semua modul terhubung ke Qalcuity AI</p>
            <p class="text-blue-200 text-sm max-w-lg mx-auto">
                Cukup ketik <span class="bg-white/20 px-2 py-0.5 rounded-lg font-mono text-xs">"Buatkan laporan penjualan bulan ini"</span> atau
                <span class="bg-white/20 px-2 py-0.5 rounded-lg font-mono text-xs">"Cek karyawan yang belum absen hari ini"</span> — AI langsung mengeksekusi.
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
            <p class="text-gray-400 mt-4 max-w-lg mx-auto">Grafik, tabel, KPI cards, invoice, surat, dan tombol aksi — semua langsung di dalam chat.</p>
        </div>
        <div class="grid md:grid-cols-3 gap-5">
            {{-- KPI --}}
            <div class="card-hover bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center gap-2 mb-4">
                    <span class="text-base">📊</span>
                    <p class="text-sm font-semibold text-gray-800">KPI Cards</p>
                    <span class="ml-auto text-xs bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full font-medium">Dashboard</span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div class="bg-blue-50 rounded-xl p-3"><p class="text-xs text-gray-400">Omzet Bulan Ini</p><p class="text-sm font-bold text-gray-800 mt-1">Rp 48,5 jt</p><p class="text-xs text-green-600 mt-0.5 font-medium">▲ 12%</p></div>
                    <div class="bg-green-50 rounded-xl p-3"><p class="text-xs text-gray-400">Laba Bersih</p><p class="text-sm font-bold text-gray-800 mt-1">Rp 18,2 jt</p><p class="text-xs text-green-600 mt-0.5 font-medium">▲ 8%</p></div>
                    <div class="bg-amber-50 rounded-xl p-3"><p class="text-xs text-gray-400">Stok Menipis</p><p class="text-sm font-bold text-gray-800 mt-1">7 produk</p><p class="text-xs text-red-500 mt-0.5 font-medium">Perlu PO</p></div>
                    <div class="bg-purple-50 rounded-xl p-3"><p class="text-xs text-gray-400">Karyawan Hadir</p><p class="text-sm font-bold text-gray-800 mt-1">24/26</p><p class="text-xs text-gray-400 mt-0.5">2 izin</p></div>
                </div>
            </div>
            {{-- Table --}}
            <div class="card-hover bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center gap-2 mb-4">
                    <span class="text-base">📋</span>
                    <p class="text-sm font-semibold text-gray-800">Tabel Interaktif</p>
                    <span class="ml-auto text-xs bg-green-50 text-green-600 px-2 py-0.5 rounded-full font-medium">+ Ekspor CSV</span>
                </div>
                <div class="overflow-hidden rounded-xl border border-gray-100">
                    <table class="min-w-full text-xs">
                        <thead><tr class="bg-gray-50"><th class="px-3 py-2 text-left text-gray-400 font-semibold">Produk</th><th class="px-3 py-2 text-right text-gray-400 font-semibold">Stok</th><th class="px-3 py-2 text-left text-gray-400 font-semibold">Status</th></tr></thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr><td class="px-3 py-2 text-gray-700">Kopi Arabika</td><td class="px-3 py-2 text-right text-gray-600">3 kg</td><td class="px-3 py-2"><span class="bg-red-100 text-red-700 px-1.5 py-0.5 rounded-full">Habis</span></td></tr>
                            <tr class="bg-gray-50/50"><td class="px-3 py-2 text-gray-700">Susu UHT</td><td class="px-3 py-2 text-right text-gray-600">12 ltr</td><td class="px-3 py-2"><span class="bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded-full">Rendah</span></td></tr>
                            <tr><td class="px-3 py-2 text-gray-700">Gula Pasir</td><td class="px-3 py-2 text-right text-gray-600">45 kg</td><td class="px-3 py-2"><span class="bg-green-100 text-green-700 px-1.5 py-0.5 rounded-full">Aman</span></td></tr>
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
                    <span class="ml-auto text-xs bg-purple-50 text-purple-600 px-2 py-0.5 rounded-full font-medium">Baru</span>
                </div>
                <p class="text-xs text-gray-400 mb-3 leading-relaxed">Setelah AI menampilkan data, tombol aksi muncul langsung di chat untuk tindak lanjut cepat.</p>
                <div class="space-y-2">
                    <div class="flex flex-wrap gap-1.5">
                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg bg-blue-600 text-white">📦 Buat PO Otomatis</span>
                        <span class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-600">🔍 Lihat Detail</span>
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg bg-green-600 text-white">✅ Konfirmasi Semua</span>
                        <span class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-600">📊 Lihat Laporan</span>
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-3">Klik tombol → AI langsung mengeksekusi</p>
            </div>
            {{-- Invoice --}}
            <div class="card-hover bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm">
                <div class="flex items-center justify-between px-4 py-2.5 bg-gray-50 border-b border-gray-100">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">🧾 Invoice / Faktur</span>
                    <button class="text-xs bg-blue-600 text-white px-2.5 py-1 rounded-lg font-medium">🖨 Cetak</button>
                </div>
                <div class="p-4 text-xs">
                    <div class="flex justify-between mb-3"><div><p class="font-bold text-gray-800">PT Contoh Jaya</p><p class="text-gray-400 mt-0.5">Jl. Sudirman No. 1</p></div><div class="text-right"><p class="text-blue-600 font-black text-lg leading-none">INVOICE</p><p class="text-gray-400 mt-1">INV-2026-042</p></div></div>
                    <div class="border border-gray-100 rounded-xl overflow-hidden mb-3">
                        <table class="min-w-full"><thead><tr class="bg-gray-50"><th class="px-3 py-2 text-left text-gray-400 font-semibold">Item</th><th class="px-3 py-2 text-right text-gray-400 font-semibold">Total</th></tr></thead>
                        <tbody class="divide-y divide-gray-50"><tr><td class="px-3 py-2 text-gray-700">Kopi Arabika 10kg</td><td class="px-3 py-2 text-right text-gray-700">Rp 500.000</td></tr><tr><td class="px-3 py-2 text-gray-700">Susu UHT 20ltr</td><td class="px-3 py-2 text-right text-gray-700">Rp 300.000</td></tr></tbody></table>
                    </div>
                    <div class="flex justify-end"><p class="text-gray-500">Total: <span class="font-bold text-blue-600 text-sm">Rp 880.000</span></p></div>
                </div>
            </div>
            {{-- Letter --}}
            <div class="card-hover bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm">
                <div class="flex items-center justify-between px-4 py-2.5 bg-gray-50 border-b border-gray-100">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">📄 Surat Resmi</span>
                    <div class="flex gap-1.5">
                        <button class="text-xs bg-blue-600 text-white px-2.5 py-1 rounded-lg font-medium">🖨 Cetak</button>
                        <button class="text-xs bg-gray-200 text-gray-600 px-2.5 py-1 rounded-lg font-medium">📋 Salin</button>
                    </div>
                </div>
                <div class="p-5 text-xs text-gray-700 leading-relaxed" style="font-family:'Times New Roman',serif">
                    <p class="text-right text-gray-400 mb-3">Jakarta, 20 Maret 2026</p>
                    <p class="mb-2">Kepada Yth.<br><strong>Bapak/Ibu Pelanggan</strong></p>
                    <p class="mb-3"><strong>Perihal: Penawaran Produk Kopi Premium</strong></p>
                    <p class="text-gray-500">Dengan hormat, kami mengajukan penawaran produk kopi arabika premium grade A dengan harga spesial untuk pembelian bulan ini...</p>
                    <p class="mt-4">Hormat kami,<br><br><strong>Direktur PT Contoh Jaya</strong></p>
                </div>
            </div>
            {{-- Chart --}}
            <div class="card-hover bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center gap-2 mb-4">
                    <span class="text-base">📈</span>
                    <p class="text-sm font-semibold text-gray-800">Grafik Interaktif</p>
                    <span class="ml-auto text-xs bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-full font-medium">Chart.js</span>
                </div>
                <div class="space-y-2.5">
                    @foreach([['Jan','60%','Rp 28jt'],['Feb','75%','Rp 35jt'],['Mar','55%','Rp 26jt'],['Apr','90%','Rp 42jt'],['Mei','80%','Rp 38jt']] as [$m,$p,$v])
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-400 w-6 shrink-0">{{ $m }}</span>
                        <div class="flex-1 bg-gray-100 rounded-full h-1.5"><div class="bg-gradient-to-r from-blue-500 to-indigo-500 h-1.5 rounded-full" style="width:{{ $p }}"></div></div>
                        <span class="text-xs text-gray-500 w-12 text-right shrink-0">{{ $v }}</span>
                    </div>
                    @endforeach
                </div>
                <p class="text-xs text-gray-400 mt-3">Omzet 5 bulan terakhir — klik "Unduh" untuk simpan PNG</p>
            </div>
        </div>
    </div>
</section>

{{-- ══ HOW IT WORKS ══ --}}
<section class="py-24 bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="text-xs font-bold text-blue-600 uppercase tracking-widest">Cara Kerja</span>
            <h2 class="text-4xl font-black text-gray-900 mt-3">Mulai dalam 3 langkah</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
            @foreach([
                ['num'=>'01','title'=>'Daftar & Setup','desc'=>'Buat akun perusahaan dalam 2 menit. Masukkan nama perusahaan, email, dan password. Langsung aktif.','icon'=>'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z'],
                ['num'=>'02','title'=>'Input Data Bisnis','desc'=>'Tambahkan produk, pelanggan, supplier, dan karyawan. Atau minta AI untuk membantu setup awal bisnis Anda.','icon'=>'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4'],
                ['num'=>'03','title'=>'Kelola Lewat AI','desc'=>'Tanyakan apa saja ke Qalcuity AI. Cek stok, buat order, lihat laporan — semua lewat chat natural.','icon'=>'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
            ] as $i => $step)
            <div class="relative text-center">
                <div class="relative w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center mx-auto mb-5 shadow-lg shadow-blue-200">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $step['icon'] }}"/>
                    </svg>
                    <span class="absolute -top-2 -right-2 w-5 h-5 bg-white border-2 border-blue-200 rounded-full text-xs font-black text-blue-600 flex items-center justify-center">{{ $i+1 }}</span>
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
                    'name'      => 'Starter',
                    'price'     => 'Rp 99.000',
                    'period'    => '/bulan',
                    'yearly'    => 'Rp 999.000/tahun',
                    'desc'      => 'Untuk UMKM yang baru mulai digitalisasi.',
                    'highlight' => false,
                    'badge'     => null,
                    'features'  => [
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
                    'name'      => 'Business',
                    'price'     => 'Rp 249.000',
                    'period'    => '/bulan',
                    'yearly'    => 'Rp 2.499.000/tahun',
                    'desc'      => 'Untuk bisnis yang butuh fitur penjualan lengkap.',
                    'highlight' => false,
                    'badge'     => null,
                    'features'  => [
                        ['text' => 'Semua fitur Starter', 'ok' => true],
                        ['text' => 'Pembelian & Supplier', 'ok' => true],
                        ['text' => 'CRM, Helpdesk, Konsinyasi', 'ok' => true],
                        ['text' => 'Komisi Sales & Subscription', 'ok' => true],
                        ['text' => 'AI Chat (300 pesan/bulan)', 'ok' => true],
                        ['text' => 'Hingga 10 pengguna', 'ok' => true],
                    ],
                    'cta' => 'Mulai Trial Gratis',
                ],
                [
                    'name'      => 'Professional',
                    'price'     => 'Rp 499.000',
                    'period'    => '/bulan',
                    'yearly'    => 'Rp 4.999.000/tahun',
                    'desc'      => 'Untuk bisnis menengah dengan operasi kompleks.',
                    'highlight' => true,
                    'badge'     => 'Paling Populer',
                    'features'  => [
                        ['text' => 'Semua fitur Business', 'ok' => true],
                        ['text' => 'HRM, Payroll, Aset', 'ok' => true],
                        ['text' => 'Manufaktur (BOM & MRP)', 'ok' => true],
                        ['text' => 'Fleet, Kontrak, Landed Cost', 'ok' => true],
                        ['text' => 'Project Billing & Forecasting', 'ok' => true],
                        ['text' => 'AI Chat (1.000 pesan/bulan)', 'ok' => true],
                    ],
                    'cta' => 'Mulai Trial Gratis',
                ],
                [
                    'name'      => 'Enterprise',
                    'price'     => 'Rp 999.000',
                    'period'    => '/bulan',
                    'yearly'    => 'Rp 9.999.000/tahun',
                    'desc'      => 'Untuk bisnis besar tanpa batas.',
                    'highlight' => false,
                    'badge'     => null,
                    'features'  => [
                        ['text' => 'Semua fitur Professional', 'ok' => true],
                        ['text' => 'AI & User Tak Terbatas', 'ok' => true],
                        ['text' => 'Multi Company & Konsolidasi', 'ok' => true],
                        ['text' => 'Zero Input OCR & WhatsApp Bot', 'ok' => true],
                        ['text' => 'Custom API & Digital Signature', 'ok' => true],
                        ['text' => 'Prioritas Support & SLA 99.9%', 'ok' => true],
                    ],
                    'cta' => 'Hubungi Kami',
                ],
            ];
            @endphp
            @foreach($plans as $plan)
            <div class="relative flex flex-col rounded-3xl border {{ $plan['highlight'] ? 'border-blue-500 shadow-2xl shadow-blue-100 scale-[1.02]' : 'border-gray-200 shadow-sm' }} bg-white p-7">
                @if($plan['badge'])
                <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-xs font-bold px-4 py-1.5 rounded-full shadow">
                    {{ $plan['badge'] }}
                </div>
                @endif
                <div class="mb-6">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">{{ $plan['name'] }}</p>
                    <div class="flex items-end gap-1 mb-1">
                        <span class="text-3xl font-black text-gray-900 tracking-tight">{{ $plan['price'] }}</span>
                        <span class="text-sm text-gray-400 mb-1">{{ $plan['period'] }}</span>
                    </div>
                    <p class="text-xs text-gray-400">{{ $plan['yearly'] }}</p>
                    <p class="text-sm text-gray-500 mt-3">{{ $plan['desc'] }}</p>
                </div>
                <ul class="space-y-2.5 flex-1 mb-7">
                    @foreach($plan['features'] as $feat)
                    <li class="flex items-center gap-2.5 text-sm {{ $feat['ok'] ? 'text-gray-700' : 'text-gray-300' }}">
                        @if($feat['ok'])
                        <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        @else
                        <svg class="w-4 h-4 text-gray-200 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
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
        <p class="text-center text-xs text-gray-400 mt-8">Semua plan sudah termasuk trial gratis. Tidak perlu kartu kredit. Batalkan kapan saja.</p>
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
                ['q'=>'Apakah data saya aman?','a'=>'Ya. Setiap perusahaan memiliki data yang terisolasi penuh. Tidak ada akses silang antar perusahaan.'],
                ['q'=>'Apakah AI bisa salah mengeksekusi perintah?','a'=>'Qalcuity AI memiliki lapisan validasi sebelum melakukan operasi write ke database. Untuk aksi kritis, AI akan meminta konfirmasi terlebih dahulu.'],
                ['q'=>'Berapa banyak pengguna yang bisa ditambahkan?','a'=>'Starter: 2 pengguna, Business: 10 pengguna, Professional: 25 pengguna, Enterprise: tidak terbatas.'],
                ['q'=>'Apakah bisa diakses dari mobile?','a'=>'Ya, antarmuka Qalcuity ERP responsif dan dapat diakses dari browser mobile manapun.'],
                ['q'=>'Bagaimana cara migrasi data dari sistem lama?','a'=>'Kami menyediakan template import Excel untuk produk, pelanggan, dan supplier. Tim support kami siap membantu proses migrasi.'],
                ['q'=>'Apakah ada kontrak jangka panjang?','a'=>'Tidak. Semua plan berbasis bulanan dan bisa dibatalkan kapan saja tanpa penalti.'],
                ['q'=>'Apa itu Qalcuity AI?','a'=>'Qalcuity AI adalah asisten ERP cerdas yang terintegrasi langsung ke semua modul bisnis Anda. Anda bisa mengelola seluruh operasional bisnis hanya lewat percakapan natural.'],
            ];
            @endphp
            @foreach($faqs as $i => $faq)
            <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm">
                <button @click="active = active === {{ $i }} ? null : {{ $i }}"
                    class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-gray-50/50 transition">
                    <span class="font-semibold text-gray-800 text-sm pr-4">{{ $faq['q'] }}</span>
                    <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform duration-200"
                         :class="active === {{ $i }} ? 'rotate-180' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
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
        <p class="text-gray-400 text-sm mb-10">Tim kami siap membantu Anda via WhatsApp — respons cepat di jam kerja.</p>
        <a href="https://wa.me/6281654932383"
           target="_blank" rel="noopener"
           class="inline-flex items-center gap-3 bg-green-500 hover:bg-green-600 text-white font-semibold px-8 py-4 rounded-2xl transition shadow-lg shadow-green-200 text-sm">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
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
            Rekomendasikan Qalcuity ERP ke bisnis lain dan dapatkan komisi 10% dari setiap pembayaran subscription mereka. Komisi berlaku selamanya selama pelanggan aktif.
        </p>
        <div class="grid sm:grid-cols-3 gap-5 mb-10 text-left">
            @foreach([
                ['icon'=>'🔗','title'=>'Dapatkan Link Referral','desc'=>'Daftar sebagai affiliate dan dapatkan link unik Anda.'],
                ['icon'=>'📢','title'=>'Bagikan ke Jaringan','desc'=>'Share link ke teman, komunitas, atau media sosial Anda.'],
                ['icon'=>'💰','title'=>'Terima Komisi','desc'=>'Setiap kali referral Anda berlangganan, Anda dapat 10% komisi.'],
            ] as $step)
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
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                Daftar Jadi Affiliate via WhatsApp
            </a>
        </div>
        <p class="text-xs text-purple-300/60 mt-4">Pendaftaran diproses manual oleh tim kami. Anda akan mendapat akun affiliate dalam 1x24 jam.</p>
    </div>
</section>

{{-- ══ JASA INSTALASI OPEN CLOW ══ --}}
<section id="open-clow" class="py-24 bg-[#0f172a]">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="text-xs font-bold text-cyan-400 uppercase tracking-widest">Layanan Tambahan</span>
            <h2 class="text-4xl font-black text-white mt-3">Jasa Instalasi Open Clow Agent</h2>
            <p class="text-slate-400 mt-4 max-w-xl mx-auto">AI Agent khusus untuk bisnis Anda — custom training, integrasi sistem existing, dan maintenance selamanya.</p>
        </div>
        <div class="grid md:grid-cols-2 gap-6 max-w-3xl mx-auto">
            {{-- Paket Instalasi --}}
            <div class="relative bg-white/5 border border-white/10 rounded-3xl p-8 backdrop-blur">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center mb-5">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-white mb-1">Paket Instalasi</h3>
                <p class="text-3xl font-black text-cyan-400 mb-1">Rp 7.000.000</p>
                <p class="text-xs text-slate-400 mb-6">Sekali bayar + FREE maintenance selamanya</p>
                <ul class="space-y-2.5 mb-8">
                    @foreach(['Custom AI training sesuai bisnis kamu','Integrasi dengan sistem existing','Setup & konfigurasi lengkap','Training team (2 sesi)','Maintenance & update GRATIS','24/7 technical support'] as $f)
                    <li class="flex items-center gap-2.5 text-sm text-slate-300">
                        <svg class="w-4 h-4 text-cyan-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
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
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center mb-5">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-white mb-1">Biaya Operasional</h3>
                <p class="text-3xl font-black text-violet-400 mb-1">Rp 1.000.000</p>
                <p class="text-xs text-slate-400 mb-6">Per bulan untuk 100.000 AI tokens</p>
                <ul class="space-y-2.5 mb-8">
                    @foreach(['100.000 AI tokens per bulan','~3.000 interaksi AI per bulan','Unlimited users dalam tim','Real-time analytics & reporting','Auto-scaling sesuai kebutuhan','Priority support'] as $f)
                    <li class="flex items-center gap-2.5 text-sm text-slate-300">
                        <svg class="w-4 h-4 text-violet-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
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
                @foreach([
                    ['🤖','Customer Service AI','Jawab pertanyaan pelanggan 24/7 otomatis'],
                    ['📊','Analisis Data','Analisis penjualan, stok, dan keuangan real-time'],
                    ['📝','Pembuatan Konten','Generate deskripsi produk, email, dan laporan'],
                    ['🔗','Integrasi Sistem','Hubungkan dengan WhatsApp, Shopee, Tokopedia'],
                ] as [$emoji, $title, $desc])
                <div class="bg-white/5 border border-white/10 rounded-2xl p-5 text-left">
                    <span class="text-2xl">{{ $emoji }}</span>
                    <h4 class="font-bold text-white text-sm mt-3 mb-1">{{ $title }}</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">{{ $desc }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ══ CTA BOTTOM ══ --}}
<section class="py-24 relative overflow-hidden bg-[#0a0f1e]">
    <div class="hero-glow absolute inset-0 pointer-events-none"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[400px] bg-indigo-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-4xl sm:text-5xl font-black text-white leading-tight mb-4 tracking-tight">
            Siap transformasi bisnis<br>Anda dengan AI?
        </h2>
        <p class="text-slate-400 mb-10 text-lg">Mulai gratis. Tidak perlu kartu kredit.</p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('register') }}"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-semibold px-8 py-4 rounded-2xl transition shadow-lg shadow-blue-900/40 text-sm">
                Mulai Gratis Sekarang
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
            <a href="{{ route('login') }}"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-white/8 hover:bg-white/12 border border-white/15 text-white font-medium px-8 py-4 rounded-2xl transition text-sm backdrop-blur">
                Sudah punya akun? Masuk
            </a>
        </div>
        <div class="mt-10 flex items-center justify-center gap-8 text-xs text-slate-500">
            <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>Tanpa kartu kredit</span>
            <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>Setup 2 menit</span>
            <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>Batalkan kapan saja</span>
        </div>
    </div>
</section>

{{-- ══ FOOTER ══ --}}
<footer class="bg-white border-t border-gray-100 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-4 gap-8 mb-10">
            <div class="md:col-span-2">
                <div class="flex items-center gap-2.5 mb-4">
                    <img src="/logo.png" alt="Qalcuity ERP" class="h-8 w-auto object-contain brightness-0">
                </div>
                <p class="text-sm text-gray-400 leading-relaxed max-w-xs">
                    Platform ERP berbasis AI untuk bisnis Indonesia. Kelola semua aspek bisnis Anda lewat percakapan natural.
                </p>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Produk</p>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="#fitur" class="text-gray-500 hover:text-gray-900 transition">Fitur</a></li>
                    <li><a href="#modul" class="text-gray-500 hover:text-gray-900 transition">Modul</a></li>
                    <li><a href="#harga" class="text-gray-500 hover:text-gray-900 transition">Harga</a></li>
                </ul>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Akun & Kontak</p>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="{{ route('login') }}"    class="text-gray-500 hover:text-gray-900 transition">Masuk</a></li>
                    <li><a href="{{ route('register') }}" class="text-gray-500 hover:text-gray-900 transition">Daftar Gratis</a></li>
                    <li>
                        <a href="https://wa.me/6281654932383" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-1.5 text-green-600 hover:text-green-700 transition font-medium">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            WhatsApp Kami
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="border-t border-gray-100 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-400">
            <p>© {{ date('Y') }} Qalcuity ERP. All rights reserved.</p>
            <p>Powered by <span class="text-blue-500 font-semibold">Noteds Technology</span></p>
        </div>
    </div>
</footer>

</body>
</html>
