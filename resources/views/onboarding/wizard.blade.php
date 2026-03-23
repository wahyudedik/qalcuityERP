<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Awal — Qalcuity ERP</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="h-full font-[Inter,sans-serif] bg-gradient-to-br from-blue-50 via-white to-indigo-50 flex items-center justify-center p-4 min-h-screen">

<div class="w-full max-w-2xl">

    {{-- Header --}}
    <div class="text-center mb-6">
        <img src="/logo.png" alt="Qalcuity" class="h-10 mx-auto mb-4 brightness-0">
        <h1 class="text-2xl font-bold text-gray-900">Selamat datang di Qalcuity ERP! 👋</h1>
        <p class="text-gray-500 mt-1 text-sm">Pilih cara setup yang Anda inginkan.</p>
    </div>

    {{-- Mode Selector --}}
    <div id="mode-selector" class="grid grid-cols-2 gap-4 mb-6">
        <button onclick="selectMode('ai')"
            class="mode-btn group flex flex-col items-center gap-3 p-5 rounded-2xl border-2 border-gray-200 bg-white hover:border-blue-400 hover:shadow-md transition text-left">
            <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center text-2xl group-hover:bg-blue-200 transition">🤖</div>
            <div>
                <div class="font-semibold text-gray-900 text-sm">Setup dengan AI</div>
                <div class="text-xs text-gray-500 mt-0.5">Chat dengan AI, setup otomatis sesuai bisnis Anda</div>
            </div>
            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium">Direkomendasikan</span>
        </button>
        <button onclick="selectMode('manual')"
            class="mode-btn group flex flex-col items-center gap-3 p-5 rounded-2xl border-2 border-gray-200 bg-white hover:border-gray-400 hover:shadow-md transition text-left">
            <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center text-2xl group-hover:bg-gray-200 transition">📝</div>
            <div>
                <div class="font-semibold text-gray-900 text-sm">Setup Manual</div>
                <div class="text-xs text-gray-500 mt-0.5">Isi form langkah demi langkah</div>
            </div>
            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full font-medium">Klasik</span>
        </button>
    </div>

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- AI MODE                                                        --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div id="ai-mode" class="hidden">

        {{-- Step indicator --}}
        <div class="flex items-center justify-center gap-2 mb-5" id="ai-step-indicator">
            @foreach([1 => 'Jenis Bisnis', 2 => 'Chat AI', 3 => 'Modul', 4 => 'Selesai!'] as $n => $label)
            <div class="flex items-center gap-2">
                <div class="ai-step-dot w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold transition-all
                    {{ $n === 1 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-500' }}" data-step="{{ $n }}">
                    {{ $n }}
                </div>
                <span class="text-xs hidden sm:block {{ $n === 1 ? 'text-blue-600 font-medium' : 'text-gray-400' }}" data-ai-step-label="{{ $n }}">{{ $label }}</span>
                @if($n < 4)<div class="w-8 h-px bg-gray-200 mx-1"></div>@endif
            </div>
            @endforeach
        </div>

        {{-- AI Step 1: Pilih Jenis Bisnis --}}
        <div id="ai-step-1" class="ai-step-panel">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <h2 class="font-semibold text-gray-900 text-lg mb-1">Bisnis Anda bergerak di bidang apa?</h2>
                <p class="text-sm text-gray-500 mb-5">AI akan menyiapkan template yang sesuai untuk bisnis Anda.</p>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @php
                    $industries = [
                        'fnb'          => ['icon' => '🍜', 'label' => 'F&B / Kuliner',    'desc' => 'Resto, kafe, warung'],
                        'retail'       => ['icon' => '🏪', 'label' => 'Retail / Toko',    'desc' => 'Toko, minimarket'],
                        'manufacture'  => ['icon' => '🏭', 'label' => 'Manufaktur',        'desc' => 'Konveksi, pabrik'],
                        'distributor'  => ['icon' => '📦', 'label' => 'Distributor',       'desc' => 'Grosir, agen'],
                        'construction' => ['icon' => '🏗️', 'label' => 'Konstruksi',        'desc' => 'Kontraktor, builder'],
                        'service'      => ['icon' => '🔧', 'label' => 'Jasa',              'desc' => 'Konsultan, servis'],
                        'agriculture'  => ['icon' => '🌾', 'label' => 'Pertanian',         'desc' => 'Kebun, ternak'],
                        'other'        => ['icon' => '💼', 'label' => 'Lainnya',           'desc' => 'Jenis bisnis lain'],
                    ];
                    @endphp
                    @foreach($industries as $key => $ind)
                    <button onclick="selectIndustry('{{ $key }}', '{{ $ind['label'] }}')"
                        class="industry-card flex flex-col items-start gap-1.5 p-3.5 rounded-xl border-2 border-gray-200 hover:border-blue-400 hover:bg-blue-50 transition text-left group"
                        data-industry="{{ $key }}">
                        <span class="text-2xl">{{ $ind['icon'] }}</span>
                        <div class="font-medium text-gray-900 text-sm">{{ $ind['label'] }}</div>
                        <div class="text-xs text-gray-400">{{ $ind['desc'] }}</div>
                    </button>
                    @endforeach
                </div>
            </div>
            <div class="flex justify-between mt-4">
                <button onclick="selectMode(null)" class="text-sm text-gray-400 hover:text-gray-600 transition py-2">← Kembali</button>
                <a href="{{ route('onboarding.skip') }}" class="text-sm text-gray-400 hover:text-gray-600 transition py-2">Lewati setup</a>
            </div>
        </div>

        {{-- AI Step 2: Chat Interface --}}
        <div id="ai-step-2" class="ai-step-panel hidden">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                {{-- Chat header --}}
                <div class="flex items-center gap-3 px-5 py-3.5 border-b border-gray-100 bg-gradient-to-r from-blue-600 to-indigo-600">
                    <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-lg">🤖</div>
                    <div>
                        <div class="font-semibold text-white text-sm">Qalcuity AI Setup</div>
                        <div class="text-xs text-blue-100" id="ai-industry-label">Mempersiapkan setup...</div>
                    </div>
                    <div class="ml-auto flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                        <span class="text-xs text-blue-100">Online</span>
                    </div>
                </div>

                {{-- Messages --}}
                <div id="ai-chat-messages" class="h-72 overflow-y-auto p-4 space-y-3 bg-gray-50">
                    {{-- Messages injected by JS --}}
                </div>

                {{-- Input --}}
                <div class="border-t border-gray-100 p-3 bg-white">
                    <div class="flex gap-2">
                        <input type="text" id="ai-chat-input"
                            placeholder="Ketik jawaban atau pertanyaan Anda..."
                            class="flex-1 rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            onkeydown="if(event.key==='Enter' && !event.shiftKey){event.preventDefault();sendAiMessage();}">
                        <button onclick="sendAiMessage()" id="ai-send-btn"
                            class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            Kirim
                        </button>
                    </div>
                    <p class="text-xs text-gray-400 mt-1.5 px-1">AI akan setup gudang, produk, dan kategori pengeluaran secara otomatis.</p>
                </div>
            </div>
            <div class="flex justify-between mt-4">
                <button onclick="goAiStep(1)" class="text-sm text-gray-400 hover:text-gray-600 transition py-2">← Ganti jenis bisnis</button>
                <a href="{{ route('onboarding.skip') }}" class="text-sm text-gray-400 hover:text-gray-600 transition py-2">Lewati setup</a>
            </div>
        </div>

        {{-- AI Step 3: Pilih Modul --}}
        <div id="ai-step-3" class="ai-step-panel hidden">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <div class="flex items-center justify-between mb-1">
                    <h2 class="font-semibold text-gray-900 text-lg">Modul yang direkomendasikan AI</h2>
                    <span id="module-ai-badge" class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium hidden">✨ AI Rekomendasi</span>
                </div>
                <p class="text-sm text-gray-500 mb-4">Aktifkan atau nonaktifkan modul sesuai kebutuhan. Anda bisa mengubah ini kapan saja di Pengaturan Modul.</p>
                <div id="module-loading" class="flex items-center gap-2 text-sm text-gray-400 py-4">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    AI sedang menganalisis industri Anda...
                </div>
                <div id="module-grid" class="grid grid-cols-2 sm:grid-cols-3 gap-2 hidden"></div>
                <p id="module-reason" class="text-xs text-blue-600 bg-blue-50 rounded-xl px-3 py-2 mt-3 hidden"></p>
            </div>
            <div class="flex justify-between mt-4">
                <button onclick="goAiStep(2)" class="text-sm text-gray-400 hover:text-gray-600 transition py-2">← Kembali</button>
                <button onclick="saveModulesAndFinish()" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition">Lanjut →</button>
            </div>
        </div>

        {{-- AI Step 4: Selesai --}}
        <div id="ai-step-4" class="ai-step-panel hidden">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8 text-center space-y-4">
                <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto text-3xl">🎉</div>
                <h2 class="font-bold text-gray-900 text-xl">Setup Selesai!</h2>
                <p class="text-gray-500 text-sm">AI telah menyiapkan bisnis Anda. Semua data sudah dikonfigurasi dan siap digunakan.</p>
                <div class="bg-gray-50 rounded-xl p-4 text-left space-y-2 text-sm" id="setup-summary">
                    <div class="flex items-center gap-2 text-gray-600">
                        <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Template industri diterapkan
                    </div>
                    <div class="flex items-center gap-2 text-gray-600">
                        <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Gudang & produk awal dibuat
                    </div>
                    <div class="flex items-center gap-2 text-gray-600">
                        <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Kategori pengeluaran siap
                    </div>
                    <div class="flex items-center gap-2 text-gray-600">
                        <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        Qalcuity AI siap membantu Anda
                    </div>
                </div>
                <a href="{{ route('dashboard') }}" id="finish-btn" class="inline-block px-8 py-3 rounded-xl bg-green-600 hover:bg-green-700 text-white text-sm font-semibold transition mt-2">
                    Mulai Gunakan ERP 🚀
                </a>
            </div>
        </div>

    </div>{{-- end #ai-mode --}}

    {{-- ══════════════════════════════════════════════════════════════ --}}
    {{-- MANUAL MODE                                                    --}}
    {{-- ══════════════════════════════════════════════════════════════ --}}
    <div id="manual-mode" class="hidden">

        {{-- Step indicator --}}
        <div class="flex items-center justify-center gap-2 mb-5" id="manual-step-indicator">
            @foreach([1 => 'Bisnis', 2 => 'Gudang & Produk', 3 => 'Modul', 4 => 'Siap!'] as $n => $label)
            <div class="flex items-center gap-2">
                <div class="step-dot w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold transition-all
                    {{ $n === 1 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-500' }}" data-step="{{ $n }}">
                    {{ $n }}
                </div>
                <span class="text-xs hidden sm:block {{ $n === 1 ? 'text-blue-600 font-medium' : 'text-gray-400' }}" data-step-label="{{ $n }}">{{ $label }}</span>
                @if($n < 4)<div class="manual-connector w-8 h-px bg-gray-200 mx-1"></div>@endif
            </div>
            @endforeach
        </div>

        <form method="POST" action="{{ route('onboarding.complete') }}" id="onboarding-form">
            @csrf

            {{-- Step 1: Info Bisnis --}}
            <div class="step-panel" data-panel="1">
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-5">
                    <h2 class="font-semibold text-gray-900 text-lg">Informasi Bisnis</h2>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Bisnis <span class="text-red-500">*</span></label>
                        <input type="text" name="business_name" value="{{ old('business_name', $tenant->name) }}" required
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Contoh: Toko Maju Jaya">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Jenis Bisnis</label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            @php
                            $types = [
                                'warung_makan' => ['icon' => '🍜', 'label' => 'Warung / Resto'],
                                'kafe'         => ['icon' => '☕', 'label' => 'Kafe'],
                                'toko_retail'  => ['icon' => '🏪', 'label' => 'Toko Retail'],
                                'konveksi'     => ['icon' => '👕', 'label' => 'Konveksi'],
                                'distributor'  => ['icon' => '📦', 'label' => 'Distributor'],
                                'jasa'         => ['icon' => '🔧', 'label' => 'Jasa'],
                            ];
                            @endphp
                            @foreach($types as $value => $type)
                            <label class="business-type-card cursor-pointer">
                                <input type="radio" name="business_type" value="{{ $value }}" class="sr-only"
                                    {{ old('business_type', $tenant->business_type) === $value ? 'checked' : '' }}>
                                <div class="flex items-center gap-2 px-3 py-2.5 rounded-xl border-2 border-gray-200 hover:border-blue-300 transition text-sm font-medium text-gray-700 select-none">
                                    <span class="text-lg">{{ $type['icon'] }}</span>{{ $type['label'] }}
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">No. Telepon</label>
                            <input type="text" name="phone" value="{{ old('phone', $tenant->phone) }}"
                                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="08xxxxxxxxxx">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Alamat</label>
                            <input type="text" name="address" value="{{ old('address', $tenant->address) }}"
                                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Kota, Provinsi">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Deskripsi Singkat <span class="text-gray-400 font-normal">(opsional)</span></label>
                        <textarea name="business_description" rows="2"
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                            placeholder="Contoh: Toko kelontong kebutuhan sehari-hari">{{ old('business_description', $tenant->business_description) }}</textarea>
                    </div>
                </div>
                <div class="flex justify-between mt-4">
                    <button type="button" onclick="selectMode(null)" class="text-sm text-gray-400 hover:text-gray-600 transition py-2">← Kembali</button>
                    <button type="button" onclick="nextStep(2)" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition">Lanjut →</button>
                </div>
            </div>

            {{-- Step 2: Gudang & Produk --}}
            <div class="step-panel hidden" data-panel="2">
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-5">
                    <h2 class="font-semibold text-gray-900 text-lg">Gudang & Produk Awal</h2>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Gudang Utama <span class="text-red-500">*</span></label>
                        <input type="text" name="warehouse_name" value="{{ old('warehouse_name', 'Gudang Utama') }}" required
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Produk Awal <span class="text-gray-400 font-normal">(opsional, maks. 5)</span></label>
                        <div id="product-list" class="space-y-2">
                            <div class="product-row flex gap-2">
                                <input type="text" name="products[0][name]" placeholder="Nama produk"
                                    class="flex-1 rounded-xl border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <input type="number" name="products[0][price]" placeholder="Harga jual" min="0"
                                    class="w-28 rounded-xl border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <input type="text" name="products[0][unit]" placeholder="Satuan" value="pcs"
                                    class="w-20 rounded-xl border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        <button type="button" id="add-product-btn" onclick="addProductRow()"
                            class="mt-2 text-sm text-blue-600 hover:text-blue-700 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Tambah produk
                        </button>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Kategori Pengeluaran <span class="text-gray-400 font-normal">(pisahkan koma)</span></label>
                        <input type="text" name="expense_categories" value="{{ old('expense_categories', 'Bahan Baku, Operasional, Gaji Karyawan') }}"
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="bg-blue-50 rounded-xl p-4">
                        <p class="text-sm font-medium text-blue-700 mb-2">💡 Isi otomatis berdasarkan industri:</p>
                        <div class="flex flex-wrap gap-2">
                            @php
                            $presets = [
                                'fnb'         => ['label' => 'F&B',         'warehouse' => 'Dapur Utama',       'cats' => 'Bahan Baku, Gas & Listrik, Gaji Karyawan, Sewa Tempat'],
                                'retail'      => ['label' => 'Retail',      'warehouse' => 'Toko Utama',        'cats' => 'Pembelian Barang, Gaji Karyawan, Sewa Toko, Listrik & Air'],
                                'manufacture' => ['label' => 'Manufaktur',  'warehouse' => 'Gudang Bahan Baku', 'cats' => 'Bahan Baku, Upah Produksi, Overhead Pabrik, Listrik Mesin'],
                                'distributor' => ['label' => 'Distributor', 'warehouse' => 'Gudang Pusat',      'cats' => 'Pembelian Barang, Ongkos Kirim, Gaji Driver, Sewa Gudang'],
                                'jasa'        => ['label' => 'Jasa',        'warehouse' => 'Kantor Utama',      'cats' => 'Gaji Karyawan, Sewa Kantor, Listrik & Internet, Transportasi'],
                            ];
                            @endphp
                            @foreach($presets as $key => $preset)
                            <button type="button" onclick="applyPreset('{{ $key }}')"
                                class="px-3 py-1.5 rounded-lg bg-white border border-blue-200 text-xs text-blue-700 hover:bg-blue-100 transition">
                                {{ $preset['label'] }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="flex justify-between mt-4">
                    <button type="button" onclick="nextStep(1)" class="text-sm text-gray-500 hover:text-gray-700 transition py-2">← Kembali</button>
                    <button type="button" onclick="nextStep(3)" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition">Lanjut →</button>
                </div>
            </div>

            {{-- Step 3: Pilih Modul --}}
            <div class="step-panel hidden" data-panel="3">
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <h2 class="font-semibold text-gray-900 text-lg mb-1">Pilih Modul yang Dibutuhkan</h2>
                    <p class="text-sm text-gray-500 mb-4">Aktifkan modul sesuai kebutuhan bisnis Anda. Bisa diubah kapan saja di Pengaturan Modul.</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2" id="manual-module-grid">
                        @foreach(\App\Services\ModuleRecommendationService::MODULE_META as $key => $m)
                        @php $isOn = true; @endphp
                        <label class="manual-module-card flex items-center gap-2 p-2.5 rounded-xl border-2 cursor-pointer transition text-sm border-blue-500 bg-blue-50">
                            <input type="checkbox" name="modules[]" value="{{ $key }}" checked class="sr-only" onchange="toggleManualModule(this)">
                            <span class="text-lg shrink-0">{{ $m['icon'] }}</span>
                            <span class="font-medium text-gray-800 leading-tight">{{ $m['label'] }}</span>
                            <span class="manual-module-check ml-auto text-xs text-blue-500">✓</span>
                        </label>
                        @endforeach
                    </div>
                    <div class="flex gap-3 mt-3">
                        <button type="button" onclick="manualSelectAll(true)" class="text-xs text-blue-600 hover:underline">Aktifkan semua</button>
                        <span class="text-gray-300">|</span>
                        <button type="button" onclick="manualSelectAll(false)" class="text-xs text-gray-500 hover:underline">Nonaktifkan semua</button>
                    </div>
                </div>
                <div class="flex justify-between mt-4">
                    <button type="button" onclick="nextStep(2)" class="text-sm text-gray-500 hover:text-gray-700 transition py-2">← Kembali</button>
                    <button type="button" onclick="nextStep(4)" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition">Lanjut →</button>
                </div>
            </div>

            {{-- Step 4: Konfirmasi --}}
            <div class="step-panel hidden" data-panel="4">
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 text-center space-y-4">
                    <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <h2 class="font-bold text-gray-900 text-xl">Siap untuk memulai!</h2>
                    <p class="text-gray-500 text-sm">Semua data sudah diisi. Klik tombol di bawah untuk menyelesaikan setup.</p>
                    <div class="bg-gray-50 rounded-xl p-4 text-left space-y-2 text-sm">
                        <div class="flex items-center gap-2 text-gray-600"><svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Profil bisnis dikonfigurasi</div>
                        <div class="flex items-center gap-2 text-gray-600"><svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Gudang utama dibuat</div>
                        <div class="flex items-center gap-2 text-gray-600"><svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Produk & kategori pengeluaran siap</div>
                        <div class="flex items-center gap-2 text-gray-600 manual-module-summary-line"><svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span>Modul dipilih</span></div>
                    </div>
                </div>
                <div class="flex justify-between mt-4">
                    <button type="button" onclick="nextStep(3)" class="text-sm text-gray-500 hover:text-gray-700 transition py-2">← Kembali</button>
                    <button type="submit" class="px-8 py-2.5 rounded-xl bg-green-600 hover:bg-green-700 text-white text-sm font-semibold transition">Mulai Gunakan ERP 🚀</button>
                </div>
            </div>

        </form>
    </div>{{-- end #manual-mode --}}

</div>{{-- end .max-w-2xl --}}

@php
$jsPresets = json_encode([
    'fnb'         => ['warehouse' => 'Dapur Utama',       'cats' => 'Bahan Baku, Gas & Listrik, Gaji Karyawan, Sewa Tempat'],
    'retail'      => ['warehouse' => 'Toko Utama',        'cats' => 'Pembelian Barang, Gaji Karyawan, Sewa Toko, Listrik & Air'],
    'manufacture' => ['warehouse' => 'Gudang Bahan Baku', 'cats' => 'Bahan Baku, Upah Produksi, Overhead Pabrik, Listrik Mesin'],
    'distributor' => ['warehouse' => 'Gudang Pusat',      'cats' => 'Pembelian Barang, Ongkos Kirim, Gaji Driver, Sewa Gudang'],
    'jasa'        => ['warehouse' => 'Kantor Utama',      'cats' => 'Gaji Karyawan, Sewa Kantor, Listrik & Internet, Transportasi'],
]);
@endphp

<script>
const presets = {!! $jsPresets !!};
const aiChatEndpoint = '{{ route('onboarding.ai-chat') }}';
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

// ── Mode selection ──────────────────────────────────────────────
function selectMode(mode) {
    document.getElementById('mode-selector').classList.toggle('hidden', mode !== null);
    document.getElementById('ai-mode').classList.toggle('hidden', mode !== 'ai');
    document.getElementById('manual-mode').classList.toggle('hidden', mode !== 'manual');
}

// ── AI Mode ─────────────────────────────────────────────────────
let selectedIndustry = null;
let aiHistory = [];
let aiIsLoading = false;

function selectIndustry(key, label) {
    selectedIndustry = key;
    document.querySelectorAll('.industry-card').forEach(c => {
        c.classList.toggle('border-blue-500', c.dataset.industry === key);
        c.classList.toggle('bg-blue-50', c.dataset.industry === key);
        c.classList.toggle('border-gray-200', c.dataset.industry !== key);
    });
    document.getElementById('ai-industry-label').textContent = label;
    goAiStep(2);
    // Kirim pesan pembuka otomatis
    const greeting = 'Halo! Saya ingin setup bisnis ' + label + '.';
    appendAiMessage('user', greeting);
    callAiChat(greeting);
}

function goAiStep(n) {
    document.querySelectorAll('.ai-step-panel').forEach(p => p.classList.add('hidden'));
    document.getElementById('ai-step-' + n).classList.remove('hidden');

    document.querySelectorAll('.ai-step-dot').forEach(d => {
        const s = parseInt(d.dataset.step);
        d.className = 'ai-step-dot w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold transition-all';
        if (s < n) {
            d.classList.add('bg-green-500', 'text-white');
            d.innerHTML = '&#10003;';
        } else if (s === n) {
            d.classList.add('bg-blue-600', 'text-white');
            d.textContent = s;
        } else {
            d.classList.add('bg-gray-200', 'text-gray-500');
            d.textContent = s;
        }
    });

    // Update connector lines between steps
    document.querySelectorAll('#ai-step-indicator .w-8.h-px').forEach((line, idx) => {
        // idx 0 = connector between step 1-2, idx 1 = 2-3, idx 2 = 3-4
        if (idx + 2 <= n) {
            line.classList.remove('bg-gray-200');
            line.classList.add('bg-green-400');
        } else {
            line.classList.remove('bg-green-400');
            line.classList.add('bg-gray-200');
        }
    });

    document.querySelectorAll('[data-ai-step-label]').forEach(l => {
        const s = parseInt(l.dataset.aiStepLabel);
        l.className = l.className.replace(/text-\w+-\d+|font-medium/g, '');
        l.classList.add(s === n ? 'text-blue-600' : 'text-gray-400');
        if (s === n) l.classList.add('font-medium');
    });
    if (n === 3) loadModuleRecommendations();
    if (n === 4) updateSetupSummary();
}

function appendAiMessage(role, text) {
    const container = document.getElementById('ai-chat-messages');
    const isUser = role === 'user';
    const div = document.createElement('div');
    div.className = 'flex ' + (isUser ? 'justify-end' : 'justify-start');

    // Convert markdown-like bold to <strong>
    const formatted = text
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\n/g, '<br>');

    div.innerHTML = '<div class="max-w-xs sm:max-w-sm px-4 py-2.5 rounded-2xl text-sm ' +
        (isUser
            ? 'bg-blue-600 text-white rounded-br-sm'
            : 'bg-white border border-gray-200 text-gray-800 rounded-bl-sm shadow-sm') +
        '">' + formatted + '</div>';
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
}

function appendTypingIndicator() {
    const container = document.getElementById('ai-chat-messages');
    const div = document.createElement('div');
    div.id = 'typing-indicator';
    div.className = 'flex justify-start';
    div.innerHTML = '<div class="px-4 py-3 rounded-2xl rounded-bl-sm bg-white border border-gray-200 shadow-sm">' +
        '<div class="flex gap-1 items-center">' +
        '<span class="w-2 h-2 rounded-full bg-gray-400 animate-bounce" style="animation-delay:0ms"></span>' +
        '<span class="w-2 h-2 rounded-full bg-gray-400 animate-bounce" style="animation-delay:150ms"></span>' +
        '<span class="w-2 h-2 rounded-full bg-gray-400 animate-bounce" style="animation-delay:300ms"></span>' +
        '</div></div>';
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
}

function removeTypingIndicator() {
    const el = document.getElementById('typing-indicator');
    if (el) el.remove();
}

function sendAiMessage() {
    if (aiIsLoading) return;
    const input = document.getElementById('ai-chat-input');
    const text = input.value.trim();
    if (!text) return;
    input.value = '';
    appendAiMessage('user', text);
    callAiChat(text);
}

async function callAiChat(message) {
    if (aiIsLoading) return;
    aiIsLoading = true;
    document.getElementById('ai-send-btn').disabled = true;
    document.getElementById('ai-chat-input').disabled = true;
    appendTypingIndicator();

    try {
        const res = await fetch(aiChatEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ message, history: aiHistory }),
        });

        const data = await res.json();
        removeTypingIndicator();

        const reply = data.message || 'Maaf, terjadi kesalahan.';
        appendAiMessage('ai', reply);

        // Simpan ke history untuk konteks percakapan
        aiHistory.push({ role: 'user', parts: [{ text: message }] });
        aiHistory.push({ role: 'model', parts: [{ text: reply }] });

        if (data.setup_complete) {
            setTimeout(() => goAiStep(3), 1200);
        }
    } catch (e) {
        removeTypingIndicator();
        appendAiMessage('ai', 'Terjadi kesalahan koneksi. Silakan coba lagi.');
    } finally {
        aiIsLoading = false;
        document.getElementById('ai-send-btn').disabled = false;
        document.getElementById('ai-chat-input').disabled = false;
        document.getElementById('ai-chat-input').focus();
    }
}

// ── Module Selection (Step 3) ────────────────────────────────────
const moduleRecommendEndpoint = '{{ route('settings.modules.recommend') }}';
const moduleSaveEndpoint = '{{ route('settings.modules.update') }}';
const allModuleMeta = @json(\App\Services\ModuleRecommendationService::MODULE_META);
let selectedModules = [];

async function loadModuleRecommendations() {
    const grid = document.getElementById('module-grid');
    const loading = document.getElementById('module-loading');
    const reason = document.getElementById('module-reason');
    const badge = document.getElementById('module-ai-badge');

    loading.classList.remove('hidden');
    grid.classList.add('hidden');
    reason.classList.add('hidden');

    try {
        const res = await fetch(moduleRecommendEndpoint + '?industry=' + encodeURIComponent(selectedIndustry));
        const data = await res.json();
        selectedModules = [...data.modules];

        grid.innerHTML = '';
        Object.entries(allModuleMeta).forEach(([key, m]) => {
            const isOn = selectedModules.includes(key);
            const card = document.createElement('label');
            card.className = 'module-toggle-card flex items-center gap-2 p-2.5 rounded-xl border-2 cursor-pointer transition text-sm ' +
                (isOn ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300');
            card.innerHTML =
                '<input type="checkbox" class="sr-only" value="' + key + '" ' + (isOn ? 'checked' : '') + ' onchange="toggleModule(this)">' +
                '<span class="text-lg shrink-0">' + m.icon + '</span>' +
                '<span class="font-medium text-gray-800 leading-tight">' + m.label + '</span>' +
                (isOn ? '<span class="module-check ml-auto text-xs text-blue-500">✓</span>' : '');
            grid.appendChild(card);
        });

        if (data.reason) {
            reason.textContent = '💡 ' + data.reason;
            reason.classList.remove('hidden');
        }
        badge.classList.remove('hidden');
        loading.classList.add('hidden');
        grid.classList.remove('hidden');
    } catch (e) {
        loading.textContent = 'Gagal memuat rekomendasi. Semua modul akan diaktifkan.';
        selectedModules = Object.keys(allModuleMeta);
    }
}

function toggleModule(checkbox) {
    const key = checkbox.value;
    const card = checkbox.closest('label');
    // Remove existing checkmark if any
    const existingCheck = card.querySelector('.module-check');
    if (existingCheck) existingCheck.remove();

    if (checkbox.checked) {
        selectedModules.push(key);
        card.classList.add('border-blue-500', 'bg-blue-50');
        card.classList.remove('border-gray-200');
        const check = document.createElement('span');
        check.className = 'module-check ml-auto text-xs text-blue-500';
        check.textContent = '✓';
        card.appendChild(check);
    } else {
        selectedModules = selectedModules.filter(k => k !== key);
        card.classList.remove('border-blue-500', 'bg-blue-50');
        card.classList.add('border-gray-200');
    }
}

async function saveModulesAndFinish() {
    try {
        const form = new FormData();
        form.append('_method', 'PUT');
        form.append('_token', csrfToken);
        selectedModules.forEach(m => form.append('modules[]', m));
        await fetch(moduleSaveEndpoint, { method: 'POST', body: form });
    } catch (e) { /* non-blocking */ }
    goAiStep(4);
}

function updateSetupSummary() {
    const summary = document.getElementById('setup-summary');
    if (!summary) return;
    const count = selectedModules.length;
    // Update or insert the module count line
    let moduleLine = summary.querySelector('.module-count-line');
    if (!moduleLine) {
        moduleLine = document.createElement('div');
        moduleLine.className = 'module-count-line flex items-center gap-2 text-gray-600';
        moduleLine.innerHTML = '<svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span></span>';
        summary.insertBefore(moduleLine, summary.children[1]);
    }
    moduleLine.querySelector('span').textContent = count + ' modul diaktifkan';
}

// ── Manual Mode ─────────────────────────────────────────────────
function nextStep(n) {
    document.querySelectorAll('.step-panel').forEach(p => p.classList.add('hidden'));
    document.querySelector('[data-panel="' + n + '"]').classList.remove('hidden');

    document.querySelectorAll('.step-dot').forEach(d => {
        const s = parseInt(d.dataset.step);
        d.className = 'step-dot w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold transition-all';
        if (s < n) {
            d.classList.add('bg-green-500', 'text-white');
            d.innerHTML = '&#10003;';
        } else if (s === n) {
            d.classList.add('bg-blue-600', 'text-white');
            d.textContent = s;
        } else {
            d.classList.add('bg-gray-200', 'text-gray-500');
            d.textContent = s;
        }
    });

    // Update connector lines
    document.querySelectorAll('.manual-connector').forEach((line, idx) => {
        if (idx + 2 <= n) {
            line.classList.remove('bg-gray-200');
            line.classList.add('bg-green-400');
        } else {
            line.classList.remove('bg-green-400');
            line.classList.add('bg-gray-200');
        }
    });

    document.querySelectorAll('[data-step-label]').forEach(l => {
        const s = parseInt(l.dataset.stepLabel);
        l.className = l.className.replace(/text-\w+-\d+|font-medium/g, '');
        l.classList.add(s === n ? 'text-blue-600' : 'text-gray-400');
        if (s === n) l.classList.add('font-medium');
    });

    if (n === 4) {
        const count = document.querySelectorAll('#manual-module-grid input:checked').length;
        const line = document.querySelector('.manual-module-summary-line span');
        if (line) line.textContent = count + ' modul diaktifkan';
    }
}

function toggleManualModule(checkbox) {
    const card = checkbox.closest('label');
    const check = card.querySelector('.manual-module-check');
    if (checkbox.checked) {
        card.classList.add('border-blue-500', 'bg-blue-50');
        card.classList.remove('border-gray-200');
        if (check) check.style.display = '';
    } else {
        card.classList.remove('border-blue-500', 'bg-blue-50');
        card.classList.add('border-gray-200');
        if (check) check.style.display = 'none';
    }
}

function manualSelectAll(state) {
    document.querySelectorAll('#manual-module-grid input[type=checkbox]').forEach(cb => {
        cb.checked = state;
        toggleManualModule(cb);
    });
}

document.querySelectorAll('.business-type-card input[type=radio]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.business-type-card > div').forEach(d => {
            d.classList.remove('border-blue-500', 'bg-blue-50');
            d.classList.add('border-gray-200');
        });
        if (this.checked) {
            this.nextElementSibling.classList.add('border-blue-500', 'bg-blue-50');
            this.nextElementSibling.classList.remove('border-gray-200');
        }
    });
    if (radio.checked) {
        radio.nextElementSibling.classList.add('border-blue-500', 'bg-blue-50');
        radio.nextElementSibling.classList.remove('border-gray-200');
    }
});

let productCount = 1;
function addProductRow() {
    if (productCount >= 5) return;
    const list = document.getElementById('product-list');
    const row = document.createElement('div');
    row.className = 'product-row flex gap-2';
    row.innerHTML =
        '<input type="text" name="products[' + productCount + '][name]" placeholder="Nama produk"' +
        ' class="flex-1 rounded-xl border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">' +
        '<input type="number" name="products[' + productCount + '][price]" placeholder="Harga jual" min="0"' +
        ' class="w-28 rounded-xl border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">' +
        '<input type="text" name="products[' + productCount + '][unit]" placeholder="Satuan" value="pcs"' +
        ' class="w-20 rounded-xl border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">' +
        '<button type="button" onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-600 px-1">&times;</button>';
    list.appendChild(row);
    productCount++;
    if (productCount >= 5) document.getElementById('add-product-btn').classList.add('hidden');
}

function applyPreset(key) {
    const p = presets[key];
    if (!p) return;
    document.querySelector('[name="warehouse_name"]').value = p.warehouse;
    document.querySelector('[name="expense_categories"]').value = p.cats;
}
</script>
</body>
</html>
