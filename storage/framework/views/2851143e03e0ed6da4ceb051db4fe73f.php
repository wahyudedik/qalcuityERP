<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Kasir POS — Qalcuity</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" href="/favicon.png">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0f172a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="/favicon.png">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js', 'resources/js/offline-manager.js']); ?>
</head>

<body class="h-full bg-gray-950 font-[Inter,sans-serif] text-white overflow-hidden">

    <div class="flex h-full" id="pos-app">

        
        <div class="flex-1 flex flex-col min-w-0">

            
            <div class="flex items-center gap-2 px-3 sm:px-4 h-14 bg-gray-900 border-b border-gray-800 shrink-0">
                <a href="<?php echo e(route('dashboard')); ?>" class="text-gray-400 hover:text-white transition shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <span class="font-semibold text-white shrink-0">Kasir POS</span>
                <span
                    class="text-xs text-gray-500 ml-1 hidden sm:inline shrink-0"><?php echo e(now()->format('d M Y, H:i')); ?></span>

                
                <?php if(isset($activeSession) && $activeSession): ?>
                    <a href="<?php echo e(route('pos.sessions.close-form', $activeSession)); ?>"
                        title="Sesi aktif — klik untuk tutup sesi"
                        class="shrink-0 hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-500/20 text-green-400 border border-green-500/30 hover:bg-green-500/30 transition">
                        <span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span>
                        Sesi Aktif
                    </a>
                <?php else: ?>
                    <a href="<?php echo e(route('pos.sessions.create')); ?>" title="Buka sesi kasir"
                        class="shrink-0 hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-500/20 text-amber-400 border border-amber-500/30 hover:bg-amber-500/30 transition">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Buka Sesi
                    </a>
                <?php endif; ?>

                
                <button onclick="openPosSettings()" title="Pengaturan POS"
                    class="shrink-0 w-8 h-8 flex items-center justify-center text-gray-400 hover:text-white transition rounded-lg hover:bg-gray-800">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </button>

                
                <button onclick="toggleFullscreen()" id="btn-fullscreen" title="Layar penuh (F11)"
                    class="shrink-0 w-8 h-8 flex items-center justify-center text-gray-400 hover:text-white transition rounded-lg hover:bg-gray-800">
                    
                    <svg id="icon-fullscreen-enter" class="w-4 h-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 8V6a2 2 0 012-2h2M4 16v2a2 2 0 002 2h2m8-16h2a2 2 0 012 2v2m0 8v2a2 2 0 01-2 2h-2" />
                    </svg>
                    
                    <svg id="icon-fullscreen-exit" class="w-4 h-4 hidden" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 3v3a2 2 0 01-2 2H3m18 0h-3a2 2 0 01-2-2V3m0 18v-3a2 2 0 012-2h3M3 16h3a2 2 0 012 2v3" />
                    </svg>
                </button>
                
                <span id="offline-badge"
                    class="hidden items-center gap-1 text-xs font-medium bg-amber-500/20 text-amber-400 border border-amber-500/30 px-2 py-0.5 rounded-full shrink-0 cursor-pointer"
                    onclick="flushQueue()" title="Klik untuk sinkronisasi manual">
                    <span class="w-1.5 h-1.5 bg-amber-400 rounded-full animate-pulse"></span>
                    <span id="offline-badge-text">Offline</span>
                </span>

                
                <div class="flex-1 max-w-sm ml-2 sm:ml-4 relative">
                    <input id="barcode-input" type="text" placeholder="Scan barcode atau cari..."
                        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-blue-500 pr-20">
                    
                    <button id="btn-camera-scan" onclick="openCameraScanner()" title="Scan barcode via kamera"
                        class="absolute right-8 top-1.5 w-7 h-7 flex items-center justify-center text-gray-400 hover:text-blue-400 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M3 9V6a1 1 0 011-1h3M3 15v3a1 1 0 001 1h3m11-4v3a1 1 0 01-1 1h-3m4-11h-3a1 1 0 00-1 1v3M9 3H6a1 1 0 00-1 1v3m0 6v3a1 1 0 001 1h3m6-10h3a1 1 0 011 1v3" />
                        </svg>
                    </button>
                    <svg class="absolute right-3 top-2.5 w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                
                <div class="hidden sm:flex gap-1 overflow-x-auto" id="category-tabs">
                    <button onclick="filterCategory('')" data-cat=""
                        class="cat-btn px-3 py-1.5 rounded-lg text-xs font-medium bg-blue-600 text-white whitespace-nowrap">
                        Semua
                    </button>
                    <?php $__currentLoopData = $products->pluck('category')->filter()->unique(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <button onclick="filterCategory('<?php echo e($cat); ?>')" data-cat="<?php echo e($cat); ?>"
                            class="cat-btn px-3 py-1.5 rounded-lg text-xs font-medium bg-gray-700 text-gray-300 hover:bg-gray-600 whitespace-nowrap">
                            <?php echo e($cat); ?>

                        </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                
                <button onclick="toggleCart()" id="cart-toggle-btn"
                    class="sm:hidden relative shrink-0 w-9 h-9 bg-gray-800 rounded-xl flex items-center justify-center text-gray-300 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span id="cart-badge"
                        class="hidden absolute -top-1 -right-1 w-4 h-4 bg-blue-500 rounded-full text-white text-xs flex items-center justify-center font-bold">0</span>
                </button>
            </div>

            
            <div class="flex-1 overflow-y-auto p-4">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3" id="product-grid">
                    <?php $__currentLoopData = $products ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $outOfStock = $product->total_stock <= 0; ?>
                        <div class="product-card bg-gray-800 rounded-2xl p-3 transition select-none relative
                     <?php echo e($outOfStock ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer hover:bg-gray-700 hover:ring-2 hover:ring-blue-500'); ?>"
                            data-id="<?php echo e($product->id); ?>" data-name="<?php echo e($product->name); ?>"
                            data-price="<?php echo e($product->price_sell); ?>" data-stock="<?php echo e($product->total_stock); ?>"
                            data-sku="<?php echo e($product->sku); ?>" data-barcode="<?php echo e($product->barcode); ?>"
                            data-category="<?php echo e($product->category); ?>"
                            onclick="<?php echo e($outOfStock ? 'showToast(\'Stok ' . addslashes($product->name) . ' habis\', \'warning\')' : 'addToCart(this)'); ?>">
                            <div
                                class="w-full aspect-square bg-gray-700 rounded-xl mb-2 flex items-center justify-center overflow-hidden relative">
                                <?php if($product->image): ?>
                                    <img src="<?php echo e($product->image); ?>" class="w-full h-full object-cover rounded-xl"
                                        alt="">
                                <?php else: ?>
                                    <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                <?php endif; ?>
                                <?php if($outOfStock): ?>
                                    <div
                                        class="absolute inset-0 bg-black/60 rounded-xl flex items-center justify-center">
                                        <span
                                            class="bg-red-600 text-white text-xs font-bold px-2 py-1 rounded-lg tracking-wide">HABIS</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <p class="text-xs font-medium text-white leading-tight line-clamp-2"><?php echo e($product->name); ?>

                            </p>
                            <p class="text-xs text-blue-400 font-semibold mt-1">Rp
                                <?php echo e(number_format($product->price_sell, 0, ',', '.')); ?></p>
                            <p
                                class="text-xs mt-0.5 <?php echo e($outOfStock ? 'text-red-400 font-semibold' : 'text-gray-500'); ?>">
                                Stok: <?php echo e($product->total_stock); ?>

                            </p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>

        
        <div id="cart-panel"
            class="fixed inset-y-0 right-0 z-30 w-80 xl:w-96 bg-gray-900 border-l border-gray-800 flex flex-col shrink-0 translate-x-full sm:translate-x-0 sm:relative sm:inset-auto transition-transform duration-300">

            
            <div class="flex items-center justify-between px-4 h-14 border-b border-gray-800 shrink-0">
                <span class="font-semibold text-white">Keranjang</span>
                <button onclick="clearCart()"
                    class="text-xs text-red-400 hover:text-red-300 transition">Kosongkan</button>
            </div>

            
            <div class="px-4 py-2 border-b border-gray-800 shrink-0">
                <select id="customer-select" onchange="onCustomerChange(this.value)"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-blue-500">
                    <option value="">Pelanggan umum</option>
                    <?php $__currentLoopData = $customers ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?>

                            <?php echo e($c->phone ? "({$c->phone})" : ''); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                
                <div id="loyalty-info"
                    class="hidden mt-2 bg-yellow-500/10 border border-yellow-500/30 rounded-xl px-3 py-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <span class="text-sm">⭐</span>
                            <span class="text-xs text-yellow-300 font-medium">Poin: <span
                                    id="loyalty-balance-display">0</span></span>
                        </div>
                        <button onclick="toggleLoyaltyRedeem()" id="btn-loyalty-redeem"
                            class="text-xs text-yellow-400 hover:text-yellow-300 font-medium transition">
                            Tukar Poin
                        </button>
                    </div>
                    
                    <div id="loyalty-redeem-section" class="hidden mt-2 pt-2 border-t border-yellow-500/20">
                        <div class="flex items-center gap-2">
                            <input type="number" id="loyalty-redeem-input" min="0" value="0"
                                placeholder="Jumlah poin"
                                class="flex-1 bg-gray-800 border border-gray-700 rounded-lg px-2 py-1.5 text-xs text-white focus:outline-none focus:border-yellow-500"
                                oninput="onLoyaltyRedeemChange()">
                            <button onclick="applyLoyaltyRedeem()"
                                class="px-3 py-1.5 bg-yellow-600 hover:bg-yellow-500 text-white text-xs font-medium rounded-lg transition">
                                Terapkan
                            </button>
                            <button onclick="clearLoyaltyRedeem()"
                                class="px-2 py-1.5 text-gray-500 hover:text-red-400 text-xs transition">✕</button>
                        </div>
                        <p id="loyalty-redeem-preview" class="text-xs text-yellow-400 mt-1"></p>
                    </div>
                </div>
            </div>

            
            <div class="flex-1 overflow-y-auto px-4 py-2 space-y-2" id="cart-items">
                <p class="text-center text-gray-600 text-sm py-8" id="cart-empty">Belum ada item</p>
            </div>

            
            <div class="px-4 py-3 border-t border-gray-800 space-y-2 shrink-0">
                <div class="flex justify-between text-sm text-gray-400">
                    <span>Subtotal</span>
                    <span id="subtotal-display">Rp 0</span>
                </div>
                <div class="flex justify-between text-sm text-gray-400 items-center">
                    <span>Diskon</span>
                    <input id="discount-input" type="number" min="0" value="0" placeholder="0"
                        class="w-28 bg-gray-800 border border-gray-700 rounded-lg px-2 py-1 text-sm text-right text-white focus:outline-none focus:border-blue-500"
                        oninput="recalculate()">
                </div>
                <div class="flex justify-between text-sm text-gray-400 items-center">
                    <span>Pajak (PPN)</span>
                    <input id="tax-input" type="number" min="0" value="0" placeholder="0"
                        class="w-28 bg-gray-800 border border-gray-700 rounded-lg px-2 py-1 text-sm text-right text-white focus:outline-none focus:border-blue-500"
                        oninput="recalculate()">
                </div>
                
                <div id="loyalty-discount-row"
                    class="hidden flex justify-between text-sm text-yellow-400 items-center">
                    <span>Diskon Poin ⭐</span>
                    <span id="loyalty-discount-display">- Rp 0</span>
                </div>
                <div class="flex justify-between font-bold text-white text-base pt-1 border-t border-gray-700">
                    <span>Total</span>
                    <span id="total-display">Rp 0</span>
                </div>
            </div>

            
            <div class="px-4 pb-2 shrink-0">
                <div class="grid grid-cols-2 gap-2 mb-2">
                    <?php $__currentLoopData = ['cash' => ['label' => 'Tunai', 'icon' => '💵'], 'card' => ['label' => 'Kartu', 'icon' => '💳'], 'qris' => ['label' => 'QRIS', 'icon' => '📱'], 'split' => ['label' => 'Split', 'icon' => '⚡']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $info): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <button onclick="setPayment('<?php echo e($val); ?>')" data-method="<?php echo e($val); ?>"
                            class="pay-btn py-2 rounded-xl text-xs font-medium border border-gray-700 text-gray-400 hover:border-blue-500 hover:text-blue-400 transition flex items-center justify-center gap-1.5">
                            <span><?php echo e($info['icon']); ?></span><span><?php echo e($info['label']); ?></span>
                        </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <div class="grid grid-cols-2 gap-2 mb-3">
                    <?php $__currentLoopData = ['transfer' => ['label' => 'Transfer', 'icon' => '🏦'], 'bank_transfer' => ['label' => 'Bank', 'icon' => '🏛']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $info): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <button onclick="setPayment('<?php echo e($val); ?>')" data-method="<?php echo e($val); ?>"
                            class="pay-btn py-2 rounded-xl text-xs font-medium border border-gray-700 text-gray-400 hover:border-blue-500 hover:text-blue-400 transition flex items-center justify-center gap-1.5">
                            <span><?php echo e($info['icon']); ?></span><span><?php echo e($info['label']); ?></span>
                        </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                
                <div id="numpad-section" class="mb-3">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs text-gray-500">Uang diterima</span>
                    </div>
                    <input id="paid-input" type="text" value="0" readonly
                        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-right text-lg font-bold text-white mb-2 focus:outline-none">
                    
                    <div id="change-display-box" class="hidden rounded-xl px-3 py-2 mb-2 text-center">
                        <span class="text-xs text-gray-400 block">Kembalian</span>
                        <span id="change-display" class="text-2xl font-bold text-green-400"></span>
                    </div>
                    <div class="grid grid-cols-3 gap-1.5">
                        <?php $__currentLoopData = ['7', '8', '9', '4', '5', '6', '1', '2', '3', '000', '0', '⌫']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <button onclick="numpad('<?php echo e($k); ?>')"
                                class="py-2.5 rounded-xl bg-gray-800 hover:bg-gray-700 text-sm font-medium text-white transition active:scale-95">
                                <?php echo e($k); ?>

                            </button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <div class="grid grid-cols-3 gap-1.5 mt-1.5">
                        <?php $__currentLoopData = ['Pas' => 'exact', '+50rb' => '50000', '+100rb' => '100000']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <button onclick="quickCash(<?php echo e($val === 'exact' ? 'null' : $val); ?>)"
                                class="py-2 rounded-xl bg-gray-700 hover:bg-gray-600 text-xs font-medium text-gray-300 transition">
                                <?php echo e($label); ?>

                            </button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                
                <div id="card-section" class="mb-3 hidden">
                    <div class="bg-gray-800 rounded-xl p-4 text-center">
                        <div class="text-3xl mb-2">💳</div>
                        <p class="text-sm font-semibold text-white mb-1">Pembayaran Kartu</p>
                        <p class="text-xs text-gray-400 mb-3">Gesek/tap kartu debit atau kredit pada mesin EDC</p>
                        <div class="flex gap-2 justify-center mb-2">
                            <button onclick="setCardType('debit')" id="btn-card-debit"
                                class="card-type-btn px-3 py-1.5 rounded-lg text-xs font-medium border border-blue-500 text-blue-400 transition">
                                Debit
                            </button>
                            <button onclick="setCardType('credit')" id="btn-card-credit"
                                class="card-type-btn px-3 py-1.5 rounded-lg text-xs font-medium border border-gray-700 text-gray-400 transition">
                                Kredit
                            </button>
                        </div>
                        <p class="text-xs text-gray-500">Total: <span id="card-total-display"
                                class="text-white font-semibold">Rp 0</span></p>
                    </div>
                </div>

                
                <div id="qris-section" class="mb-3 hidden">
                    <div class="bg-gray-800 rounded-xl p-4 text-center" id="qris-container">
                        
                        <div id="qris-initial">
                            <div class="text-3xl mb-2">📱</div>
                            <p class="text-sm font-semibold text-white mb-1">Pembayaran QRIS</p>
                            <p class="text-xs text-gray-400 mb-3">Klik tombol di bawah untuk generate QR code</p>
                            <button onclick="generateQrisCode()" id="btn-generate-qris"
                                class="px-4 py-2 bg-purple-600 hover:bg-purple-500 rounded-xl text-xs font-semibold text-white transition">
                                Generate QR Code
                            </button>
                        </div>
                        
                        <div id="qris-loading" class="hidden">
                            <div class="text-3xl mb-2 animate-pulse">⏳</div>
                            <p class="text-sm text-gray-400">Membuat QR code...</p>
                        </div>
                        
                        <div id="qris-display" class="hidden">
                            <p class="text-xs text-gray-400 mb-2">Scan QR code berikut untuk membayar</p>
                            <div class="bg-white rounded-xl p-2 mx-auto mb-3 inline-block">
                                <img id="qris-image" src="" alt="QRIS Code"
                                    class="w-40 h-40 object-contain">
                            </div>
                            <p class="text-xs text-gray-500 mb-1">Total: <span id="qris-total-display"
                                    class="text-white font-semibold">Rp 0</span></p>
                            <div id="qris-timer" class="text-xs text-amber-400 mb-2"></div>
                            <div id="qris-status-badge" class="mb-2">
                                <span
                                    class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-900/50 text-yellow-300">
                                    Menunggu pembayaran...
                                </span>
                            </div>
                            <div id="qris-success-badge" class="hidden mb-2">
                                <span
                                    class="px-3 py-1 rounded-full text-xs font-semibold bg-green-900/50 text-green-300">
                                    ✓ Pembayaran berhasil!
                                </span>
                            </div>
                            <button onclick="cancelQris()" id="btn-cancel-qris"
                                class="text-xs text-gray-500 hover:text-red-400 transition mt-1">
                                Batalkan
                            </button>
                        </div>
                        
                        <div id="qris-error" class="hidden">
                            <div class="text-3xl mb-2">❌</div>
                            <p class="text-sm text-red-400 mb-2" id="qris-error-msg">Gagal membuat QR code</p>
                            <button onclick="generateQrisCode()"
                                class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-xl text-xs text-white transition">
                                Coba Lagi
                            </button>
                        </div>
                    </div>
                </div>

                
                <div id="transfer-section" class="mb-3 hidden">
                    <div class="bg-gray-800 rounded-xl p-4 text-center">
                        <div class="text-3xl mb-2">🏦</div>
                        <p class="text-sm font-semibold text-white mb-1">Transfer Bank</p>
                        <p class="text-xs text-gray-400 mb-3">Konfirmasi setelah pelanggan melakukan transfer</p>
                        <p class="text-xs text-gray-500">Total: <span id="transfer-total-display"
                                class="text-white font-semibold">Rp 0</span></p>
                    </div>
                </div>

                
                <div id="split-section" class="mb-3 hidden">
                    <p class="text-xs text-gray-400 mb-2">Tambah metode pembayaran:</p>
                    <div id="split-rows" class="space-y-2 mb-2"></div>
                    <button onclick="addSplitRow()"
                        class="w-full py-2 border border-dashed border-gray-600 rounded-xl text-xs text-gray-400 hover:border-blue-500 hover:text-blue-400 transition">
                        + Tambah Metode
                    </button>
                    <div class="flex justify-between text-xs mt-2 pt-2 border-t border-gray-700">
                        <span class="text-gray-400">Sisa belum dibayar:</span>
                        <span id="split-remaining" class="font-semibold text-amber-400">Rp 0</span>
                    </div>
                </div>

                <button onclick="processCheckout()"
                    class="w-full py-3.5 bg-blue-600 hover:bg-blue-500 rounded-2xl font-semibold text-white transition text-sm active:scale-95">
                    Proses Pembayaran
                </button>
            </div>
        </div>
    </div>

    
    <div id="cart-overlay" class="fixed inset-0 z-20 bg-black/60 hidden sm:hidden" onclick="toggleCart()"></div>

    
    <div id="fs-restore-overlay" onclick="_doRestoreFs()" class="hidden fixed inset-0 z-[9999] cursor-pointer"
        style="background: rgba(0,0,0,0.55); backdrop-filter: blur(2px);">
        <div
            style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;color:#fff;pointer-events:none;">
            <div
                style="width:56px;height:56px;border-radius:16px;background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 8V6a2 2 0 012-2h2M4 16v2a2 2 0 002 2h2m8-16h2a2 2 0 012 2v2m0 8v2a2 2 0 01-2 2h-2" />
                </svg>
            </div>
            <p style="font-size:16px;font-weight:600;margin-bottom:6px;">Klik untuk melanjutkan layar penuh</p>
            <p style="font-size:13px;opacity:0.6;">atau tekan tombol mana saja</p>
        </div>
    </div>

    
    <div id="camera-modal" class="fixed inset-0 bg-black/80 z-50 hidden items-center justify-center p-4">
        <div class="bg-gray-900 rounded-2xl w-full max-w-sm overflow-hidden shadow-2xl">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-800">
                <p class="text-sm font-semibold text-white">Scan Barcode via Kamera</p>
                <button onclick="closeCameraScanner()" class="text-gray-400 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <div class="relative bg-black" style="aspect-ratio:4/3">
                <video id="camera-video" autoplay playsinline muted class="w-full h-full object-cover"></video>
                
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div class="relative w-52 h-32">
                        
                        <div class="absolute top-0 left-0 w-6 h-6 border-t-2 border-l-2 border-blue-400 rounded-tl">
                        </div>
                        <div class="absolute top-0 right-0 w-6 h-6 border-t-2 border-r-2 border-blue-400 rounded-tr">
                        </div>
                        <div class="absolute bottom-0 left-0 w-6 h-6 border-b-2 border-l-2 border-blue-400 rounded-bl">
                        </div>
                        <div
                            class="absolute bottom-0 right-0 w-6 h-6 border-b-2 border-r-2 border-blue-400 rounded-br">
                        </div>
                        
                        <div id="scan-line" class="absolute left-1 right-1 h-0.5 bg-blue-400/70 rounded"
                            style="top:50%;animation:scanline 1.8s ease-in-out infinite"></div>
                    </div>
                </div>
                
                <div id="camera-status" class="absolute bottom-3 left-0 right-0 text-center">
                    <span class="text-xs text-white/70 bg-black/50 px-3 py-1 rounded-full">Arahkan kamera ke
                        barcode</span>
                </div>
            </div>
            
            <div class="px-4 py-3 border-t border-gray-800">
                <p class="text-xs text-gray-500 mb-2">Atau ketik barcode manual:</p>
                <div class="flex gap-2">
                    <input id="manual-barcode-input" type="text" placeholder="Masukkan barcode..."
                        class="flex-1 bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-blue-500">
                    <button onclick="submitManualBarcode()"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-xl transition">
                        Cari
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes scanline {
            0% {
                top: 10%;
            }

            50% {
                top: 85%;
            }

            100% {
                top: 10%;
            }
        }
    </style>

    
    <div id="receipt-modal" class="fixed inset-0 bg-black/70 z-50 hidden items-center justify-center p-4">
        <div class="bg-white text-gray-900 rounded-2xl w-full max-w-sm shadow-2xl overflow-hidden">
            
            <div class="text-center px-6 pt-6 pb-4">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h3 class="font-bold text-lg">Pembayaran Berhasil</h3>
                <p id="receipt-order" class="text-sm text-gray-500 mt-1"></p>
            </div>

            
            <div id="receipt-body"
                class="text-sm space-y-1 border-t border-b border-dashed border-gray-200 mx-6 py-3 mb-4"></div>

            
            <div class="px-6 pb-6 space-y-2">
                
                <div class="flex gap-2">
                    <button onclick="printReceipt()"
                        class="flex-1 flex items-center justify-center gap-1.5 py-2.5 border border-gray-300 rounded-xl text-sm font-medium hover:bg-gray-50 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak Struk
                    </button>
                    <button onclick="closeReceipt()"
                        class="flex-1 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-500 transition">
                        Transaksi Baru
                    </button>
                </div>
                
                <div class="flex gap-2">
                    <button onclick="openSendEmail()"
                        class="flex-1 flex items-center justify-center gap-1.5 py-2 border border-gray-200 rounded-xl text-xs font-medium text-gray-600 hover:bg-gray-50 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Kirim Email
                    </button>
                    <button onclick="openSendWhatsApp()"
                        class="flex-1 flex items-center justify-center gap-1.5 py-2 border border-green-200 rounded-xl text-xs font-medium text-green-700 hover:bg-green-50 transition">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                        </svg>
                        Kirim WhatsApp
                    </button>
                </div>
            </div>
        </div>
    </div>

    
    <div id="send-email-modal" class="fixed inset-0 bg-black/70 z-[60] hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-xs shadow-2xl p-6">
            <h4 class="font-bold text-gray-900 mb-1">Kirim Struk via Email</h4>
            <p class="text-xs text-gray-500 mb-4">Masukkan alamat email penerima</p>
            <input type="email" id="send-email-input" placeholder="contoh@email.com"
                class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 mb-3">
            <div id="send-email-status" class="text-xs mb-3 hidden"></div>
            <div class="flex gap-2">
                <button onclick="closeSendEmail()"
                    class="flex-1 py-2 border border-gray-300 rounded-xl text-sm font-medium hover:bg-gray-50 transition">Batal</button>
                <button onclick="doSendEmail()" id="btn-do-send-email"
                    class="flex-1 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-500 transition">
                    Kirim
                </button>
            </div>
        </div>
    </div>

    
    <div id="send-wa-modal" class="fixed inset-0 bg-black/70 z-[60] hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-xs shadow-2xl p-6">
            <h4 class="font-bold text-gray-900 mb-1">Kirim Struk via WhatsApp</h4>
            <p class="text-xs text-gray-500 mb-4">Masukkan nomor WhatsApp penerima</p>
            <input type="tel" id="send-wa-input" placeholder="08xxxxxxxxxx"
                class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-green-500 mb-3">
            <div id="send-wa-status" class="text-xs mb-3 hidden"></div>
            <div class="flex gap-2">
                <button onclick="closeSendWa()"
                    class="flex-1 py-2 border border-gray-300 rounded-xl text-sm font-medium hover:bg-gray-50 transition">Batal</button>
                <button onclick="doSendWhatsApp()" id="btn-do-send-wa"
                    class="flex-1 py-2 bg-green-600 text-white rounded-xl text-sm font-medium hover:bg-green-500 transition">
                    Kirim
                </button>
            </div>
        </div>
    </div>

    
    <div id="pos-settings-modal" class="fixed inset-0 bg-black/70 z-50 hidden items-center justify-center p-4">
        <div
            class="bg-gray-900 rounded-2xl w-full max-w-md shadow-2xl border border-gray-800 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-800">
                <h3 class="font-semibold text-white">⚙ Pengaturan POS</h3>
                <button onclick="closePosSettings()" class="text-gray-400 hover:text-white">✕</button>
            </div>
            <div class="p-5 space-y-5">
                
                <div>
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">🖨 Printer Struk</h4>
                    <div class="space-y-3">
                        <label class="flex items-center justify-between cursor-pointer">
                            <span class="text-sm text-gray-300">Aktifkan cetak struk</span>
                            <input type="checkbox" id="pos-print-enabled" onchange="savePosSettings()"
                                class="rounded bg-gray-700 border-gray-600 text-blue-500 focus:ring-blue-500">
                        </label>
                        <label class="flex items-center justify-between cursor-pointer">
                            <span class="text-sm text-gray-300">Auto-print setelah checkout</span>
                            <input type="checkbox" id="pos-auto-print" onchange="savePosSettings()"
                                class="rounded bg-gray-700 border-gray-600 text-blue-500 focus:ring-blue-500">
                        </label>
                        <div>
                            <label class="block text-sm text-gray-300 mb-1">Metode Cetak</label>
                            <select id="pos-print-method" onchange="savePosSettings()"
                                class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-blue-500">
                                <option value="browser">Browser Print (semua printer)</option>
                                <option value="thermal">Thermal Printer (USB/Serial)</option>
                                <option value="bluetooth">Bluetooth Printer</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-300 mb-1">Lebar Kertas</label>
                            <select id="pos-paper-width" onchange="savePosSettings()"
                                class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-blue-500">
                                <option value="58">58mm (Thermal kecil)</option>
                                <option value="80">80mm (Thermal standar)</option>
                                <option value="a4">A4 (Printer biasa)</option>
                            </select>
                        </div>
                        <div id="thermal-connect-section" class="hidden">
                            <button onclick="connectThermalPrinter()" id="btn-connect-printer"
                                class="w-full py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-xl transition">
                                🔌 Hubungkan Printer Thermal
                            </button>
                            <p id="printer-status" class="text-xs text-gray-500 mt-1 text-center"></p>
                        </div>
                        <button onclick="testPrint()"
                            class="w-full py-2 border border-gray-700 text-gray-300 text-sm rounded-xl hover:bg-gray-800 transition">
                            🧪 Test Print
                        </button>
                    </div>
                </div>

                
                <div>
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">📷 Scanner Barcode</h4>
                    <div class="space-y-3">
                        <label class="flex items-center justify-between cursor-pointer">
                            <span class="text-sm text-gray-300">Aktifkan scanner kamera</span>
                            <input type="checkbox" id="pos-camera-enabled" onchange="savePosSettings()"
                                class="rounded bg-gray-700 border-gray-600 text-blue-500 focus:ring-blue-500">
                        </label>
                        <label class="flex items-center justify-between cursor-pointer">
                            <span class="text-sm text-gray-300">Aktifkan scanner hardware (USB)</span>
                            <input type="checkbox" id="pos-hw-scanner-enabled" onchange="savePosSettings()"
                                class="rounded bg-gray-700 border-gray-600 text-blue-500 focus:ring-blue-500">
                        </label>
                        <label class="flex items-center justify-between cursor-pointer">
                            <span class="text-sm text-gray-300">Suara saat scan berhasil</span>
                            <input type="checkbox" id="pos-scan-sound" onchange="savePosSettings()"
                                class="rounded bg-gray-700 border-gray-600 text-blue-500 focus:ring-blue-500">
                        </label>
                    </div>
                </div>

                
                <div>
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">🧾 Struk</h4>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm text-gray-300 mb-1">Nama Toko di Struk</label>
                            <input type="text" id="pos-store-name" onchange="savePosSettings()"
                                placeholder="<?php echo e(auth()->user()->tenant?->name ?? 'Nama Toko'); ?>"
                                class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-300 mb-1">Alamat di Struk</label>
                            <input type="text" id="pos-store-address" onchange="savePosSettings()"
                                placeholder="Alamat toko"
                                class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-300 mb-1">Footer Struk</label>
                            <input type="text" id="pos-receipt-footer" onchange="savePosSettings()"
                                placeholder="Terima kasih atas kunjungan Anda!"
                                class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-blue-500">
                        </div>
                        <label class="flex items-center justify-between cursor-pointer">
                            <span class="text-sm text-gray-300">Tampilkan logo di struk</span>
                            <input type="checkbox" id="pos-show-logo" onchange="savePosSettings()"
                                class="rounded bg-gray-700 border-gray-600 text-blue-500 focus:ring-blue-500">
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div id="thermal-receipt" class="hidden">
        <div id="thermal-receipt-content"
            style="font-family:monospace;font-size:12px;width:100%;max-width:300px;padding:8px;color:#000;background:#fff;">
        </div>
    </div>

    <style>
        @media print {
            body * {
                visibility: hidden !important;
            }

            #print-receipt-frame,
            #print-receipt-frame * {
                visibility: visible !important;
            }

            #print-receipt-frame {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                z-index: 99999;
            }
        }
    </style>

    <script>
        const products = <?php echo json_encode($products, 15, 512) ?>;
        let cart = [];
        let paymentMethod = 'cash';
        let lastReceipt = null;

        // ── Loyalty Points ────────────────────────────────────────────────────────
        let loyaltyBalance = 0;
        let loyaltyPointsToRedeem = 0;
        let loyaltyDiscountValue = 0;
        let loyaltyIdrPerPoint = 0;
        let loyaltyMinRedeem = 100;
        let loyaltyProgramActive = false;

        async function onCustomerChange(customerId) {
            // Reset loyalty state
            clearLoyaltyRedeem();
            document.getElementById('loyalty-info').classList.add('hidden');
            loyaltyBalance = 0;

            if (!customerId) return;

            try {
                const res = await fetch(`<?php echo e(url('/pos/loyalty-balance')); ?>/${customerId}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!res.ok) return;
                const data = await res.json();

                if (data.program_active) {
                    loyaltyBalance = data.balance || 0;
                    loyaltyIdrPerPoint = data.idr_per_point || 0;
                    loyaltyMinRedeem = data.min_redeem || 100;
                    loyaltyProgramActive = true;

                    document.getElementById('loyalty-balance-display').textContent = loyaltyBalance.toLocaleString(
                        'id-ID');
                    document.getElementById('loyalty-info').classList.remove('hidden');
                }
            } catch {
                /* ignore */
            }
        }

        function toggleLoyaltyRedeem() {
            const section = document.getElementById('loyalty-redeem-section');
            section.classList.toggle('hidden');
            if (!section.classList.contains('hidden')) {
                document.getElementById('loyalty-redeem-input').focus();
            }
        }

        function onLoyaltyRedeemChange() {
            const pts = parseInt(document.getElementById('loyalty-redeem-input').value) || 0;
            const preview = document.getElementById('loyalty-redeem-preview');
            if (pts <= 0) {
                preview.textContent = '';
                return;
            }
            const discountVal = pts * loyaltyIdrPerPoint;
            preview.textContent = `${pts.toLocaleString('id-ID')} poin = diskon Rp ${discountVal.toLocaleString('id-ID')}`;
        }

        function applyLoyaltyRedeem() {
            const pts = parseInt(document.getElementById('loyalty-redeem-input').value) || 0;
            if (pts <= 0) {
                showToast('Masukkan jumlah poin yang ingin ditukarkan', 'warning');
                return;
            }
            if (pts < loyaltyMinRedeem) {
                showToast(`Minimum penukaran ${loyaltyMinRedeem.toLocaleString('id-ID')} poin`, 'warning');
                return;
            }
            if (pts > loyaltyBalance) {
                showToast(`Poin tidak mencukupi. Saldo: ${loyaltyBalance.toLocaleString('id-ID')} poin`, 'error');
                return;
            }

            const discountVal = pts * loyaltyIdrPerPoint;
            const total = getTotal();
            if (discountVal > total) {
                showToast('Nilai penukaran melebihi total transaksi', 'error');
                return;
            }

            loyaltyPointsToRedeem = pts;
            loyaltyDiscountValue = discountVal;

            document.getElementById('loyalty-redeem-section').classList.add('hidden');
            recalculate();
            showToast(
                `${pts.toLocaleString('id-ID')} poin diterapkan sebagai diskon Rp ${discountVal.toLocaleString('id-ID')}`,
                'success');
        }

        function clearLoyaltyRedeem() {
            loyaltyPointsToRedeem = 0;
            loyaltyDiscountValue = 0;
            document.getElementById('loyalty-redeem-input').value = '0';
            document.getElementById('loyalty-redeem-preview').textContent = '';
            document.getElementById('loyalty-redeem-section').classList.add('hidden');
            document.getElementById('loyalty-discount-row').classList.add('hidden');
            recalculate();
        }

        // ── Toast ─────────────────────────────────────────────────────────────────
        function showToast(message, type = 'success') {
            const colors = {
                success: '#16a34a',
                error: '#dc2626',
                warning: '#d97706',
                info: '#2563eb'
            };
            const icons = {
                success: '✓',
                error: '✕',
                warning: '⚠',
                info: 'ℹ'
            };
            const toast = document.createElement('div');
            toast.style.cssText =
                `position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;align-items:center;gap:10px;padding:12px 18px;border-radius:14px;background:${colors[type]||colors.success};color:#fff;font-size:13px;font-weight:500;box-shadow:0 8px 24px rgba(0,0,0,.4);transition:all .3s;transform:translateY(12px);opacity:0;`;
            toast.innerHTML = `<span>${icons[type]||icons.success}</span><span>${message}</span>`;
            document.body.appendChild(toast);
            requestAnimationFrame(() => {
                toast.style.transform = 'translateY(0)';
                toast.style.opacity = '1';
            });
            setTimeout(() => {
                toast.style.transform = 'translateY(12px)';
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // ── Cart ──────────────────────────────────────────────────────────────────

        function toggleCart() {
            const panel = document.getElementById('cart-panel');
            const overlay = document.getElementById('cart-overlay');
            panel.classList.toggle('translate-x-full');
            overlay.classList.toggle('hidden');
        }

        function addToCart(el) {
            const id = parseInt(el.dataset.id);
            const name = el.dataset.name;
            const price = parseFloat(el.dataset.price);
            const stock = parseInt(el.dataset.stock);

            const existing = cart.find(i => i.id === id);
            if (existing) {
                if (existing.qty >= stock) {
                    showToast('Stok tidak cukup', 'warning');
                    return;
                }
                existing.qty++;
            } else {
                cart.push({
                    id,
                    name,
                    price,
                    stock,
                    qty: 1
                });
            }
            renderCart();
        }

        function removeFromCart(id) {
            cart = cart.filter(i => i.id !== id);
            renderCart();
        }

        function changeQty(id, delta) {
            const item = cart.find(i => i.id === id);
            if (!item) return;
            item.qty += delta;
            if (item.qty <= 0) {
                removeFromCart(id);
                return;
            }
            if (item.qty > item.stock) {
                item.qty = item.stock;
            }
            renderCart();
        }

        function clearCart() {
            cart = [];
            renderCart();
        }

        function renderCart() {
            const container = document.getElementById('cart-items');
            const empty = document.getElementById('cart-empty');

            if (cart.length === 0) {
                container.innerHTML = '<p class="text-center text-gray-600 text-sm py-8">Belum ada item</p>';
                recalculate();
                updateCartBadge();
                return;
            }

            container.innerHTML = cart.map(item => `
        <div class="flex items-center gap-2 bg-gray-800 rounded-xl px-3 py-2">
            <div class="flex-1 min-w-0">
                <p class="text-xs font-medium text-white truncate">${item.name}</p>
                <p class="text-xs text-blue-400">${formatRp(item.price)}</p>
            </div>
            <div class="flex items-center gap-1 shrink-0">
                <button onclick="changeQty(${item.id}, -1)" class="w-6 h-6 rounded-lg bg-gray-700 hover:bg-gray-600 text-white text-sm flex items-center justify-center">−</button>
                <span class="w-6 text-center text-sm font-medium text-white">${item.qty}</span>
                <button onclick="changeQty(${item.id}, 1)" class="w-6 h-6 rounded-lg bg-gray-700 hover:bg-gray-600 text-white text-sm flex items-center justify-center">+</button>
            </div>
            <button onclick="removeFromCart(${item.id})" class="text-gray-600 hover:text-red-400 transition ml-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    `).join('');

            recalculate();
            updateCartBadge();
        }

        function updateCartBadge() {
            const total = cart.reduce((s, i) => s + i.qty, 0);
            const badge = document.getElementById('cart-badge');
            if (total > 0) {
                badge.textContent = total > 9 ? '9+' : total;
                badge.classList.remove('hidden');
                badge.classList.add('flex');
            } else {
                badge.classList.add('hidden');
                badge.classList.remove('flex');
            }
        }

        // ── Totals ────────────────────────────────────────────────────────────────

        function recalculate() {
            const subtotal = cart.reduce((s, i) => s + i.price * i.qty, 0);
            const discount = parseFloat(document.getElementById('discount-input').value) || 0;
            const tax = parseFloat(document.getElementById('tax-input').value) || 0;
            const total = Math.max(0, subtotal - discount + tax - loyaltyDiscountValue);

            document.getElementById('subtotal-display').textContent = formatRp(subtotal);
            document.getElementById('total-display').textContent = formatRp(total);

            // Show/hide loyalty discount row
            const loyaltyRow = document.getElementById('loyalty-discount-row');
            if (loyaltyDiscountValue > 0) {
                loyaltyRow.classList.remove('hidden');
                loyaltyRow.classList.add('flex');
                document.getElementById('loyalty-discount-display').textContent = '- ' + formatRp(loyaltyDiscountValue);
            } else {
                loyaltyRow.classList.add('hidden');
                loyaltyRow.classList.remove('flex');
            }

            updateChange();
        }

        function updateChange() {
            const total = getTotal();
            const paid = parseFloat(document.getElementById('paid-input').value.replace(/\./g, '')) || 0;
            const change = paid - total;
            const el = document.getElementById('change-display');
            const box = document.getElementById('change-display-box');
            if (paymentMethod === 'cash' && paid > 0) {
                if (change >= 0) {
                    el.textContent = formatRp(change);
                    el.className = 'text-2xl font-bold text-green-400';
                    box.className = 'rounded-xl px-3 py-2 mb-2 text-center bg-green-500/10 border border-green-500/30';
                } else {
                    el.textContent = `Kurang ${formatRp(-change)}`;
                    el.className = 'text-xl font-bold text-red-400';
                    box.className = 'rounded-xl px-3 py-2 mb-2 text-center bg-red-500/10 border border-red-500/30';
                }
                box.classList.remove('hidden');
            } else {
                box.classList.add('hidden');
                el.textContent = '';
            }
        }

        function getTotal() {
            const subtotal = cart.reduce((s, i) => s + i.price * i.qty, 0);
            const discount = parseFloat(document.getElementById('discount-input').value) || 0;
            const tax = parseFloat(document.getElementById('tax-input').value) || 0;
            return Math.max(0, subtotal - discount + tax - loyaltyDiscountValue);
        }

        // ── Numpad ────────────────────────────────────────────────────────────────

        function numpad(key) {
            const input = document.getElementById('paid-input');
            let val = input.value.replace(/\./g, '');
            if (key === '⌫') {
                val = val.slice(0, -1) || '0';
            } else if (key === '000') {
                val = val === '0' ? '0' : val + '000';
            } else {
                val = val === '0' ? key : val + key;
            }
            input.value = parseInt(val).toLocaleString('id-ID');
            updateChange();
        }

        function quickCash(amount) {
            const input = document.getElementById('paid-input');
            if (amount === null) {
                input.value = getTotal().toLocaleString('id-ID');
            } else {
                const current = parseInt(input.value.replace(/\./g, '')) || 0;
                input.value = (current + amount).toLocaleString('id-ID');
            }
            updateChange();
        }

        // ── Payment Method ────────────────────────────────────────────────────────

        let cardType = 'debit'; // 'debit' or 'credit'
        let splitRows = []; // [{method, amount}]

        function setPayment(method) {
            paymentMethod = method;
            document.querySelectorAll('.pay-btn').forEach(b => {
                const active = b.dataset.method === method;
                b.classList.toggle('border-blue-500', active);
                b.classList.toggle('text-blue-400', active);
                b.classList.toggle('border-gray-700', !active);
                b.classList.toggle('text-gray-400', !active);
            });

            // Show/hide sections
            document.getElementById('numpad-section').style.display = method === 'cash' ? 'block' : 'none';
            document.getElementById('card-section').style.display = method === 'card' ? 'block' : 'none';
            document.getElementById('qris-section').style.display = method === 'qris' ? 'block' : 'none';
            document.getElementById('transfer-section').style.display = (method === 'transfer' || method ===
                'bank_transfer') ? 'block' : 'none';
            document.getElementById('split-section').style.display = method === 'split' ? 'block' : 'none';

            // Reset QRIS state when switching away
            if (method !== 'qris') {
                cancelQris();
            }

            // Update total displays for non-cash methods
            const total = getTotal();
            document.getElementById('card-total-display').textContent = formatRp(total);
            document.getElementById('qris-total-display').textContent = formatRp(total);
            document.getElementById('transfer-total-display').textContent = formatRp(total);

            if (method === 'split') {
                if (splitRows.length === 0) {
                    addSplitRow();
                    addSplitRow();
                }
                updateSplitRemaining();
            }

            updateChange();
        }

        // ── QRIS Payment Gateway Integration ─────────────────────────────────────

        let qrisTransactionNumber = null;
        let qrisPendingOrderId = null;
        let qrisPollingInterval = null;
        let qrisTimerInterval = null;
        let qrisExpiryTime = null;

        function showQrisState(state) {
            ['qris-initial', 'qris-loading', 'qris-display', 'qris-error'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.classList.add('hidden');
            });
            const target = document.getElementById('qris-' + state);
            if (target) target.classList.remove('hidden');
        }

        async function generateQrisCode() {
            if (cart.length === 0) {
                showToast('Keranjang kosong', 'warning');
                return;
            }

            showQrisState('loading');

            const total = getTotal();
            const discount = parseFloat(document.getElementById('discount-input').value) || 0;
            const tax = parseFloat(document.getElementById('tax-input').value) || 0;
            const customerId = document.getElementById('customer-select').value || null;

            try {
                // Step 1: Create pending order
                const orderRes = await fetch('<?php echo e(route('pos.initiate-payment')); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        items: cart.map(i => ({
                            id: i.id,
                            qty: i.qty,
                            price: i.price
                        })),
                        customer_id: customerId,
                        discount,
                        tax,
                    }),
                });

                const orderData = await orderRes.json();
                if (!orderData.success) {
                    throw new Error(orderData.error || 'Gagal membuat order');
                }

                qrisPendingOrderId = orderData.order.id;

                // Step 2: Generate QRIS from payment gateway
                const qrisRes = await fetch(`/api/payment/qris/${qrisPendingOrderId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });

                const qrisData = await qrisRes.json();

                if (!qrisData.success) {
                    throw new Error(qrisData.error || 'Gagal membuat QR code');
                }

                qrisTransactionNumber = qrisData.transaction_number;
                qrisExpiryTime = qrisData.expiry_time ? new Date(qrisData.expiry_time * 1000) : new Date(Date.now() +
                    15 * 60 * 1000);

                // Show QR code
                const qrisImg = document.getElementById('qris-image');
                if (qrisData.qr_image_url) {
                    qrisImg.src = qrisData.qr_image_url;
                } else if (qrisData.qr_string) {
                    // Generate QR from string using QR API
                    qrisImg.src =
                        `https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=${encodeURIComponent(qrisData.qr_string)}`;
                }

                document.getElementById('qris-total-display').textContent = formatRp(total);
                showQrisState('display');
                document.getElementById('qris-status-badge').classList.remove('hidden');
                document.getElementById('qris-success-badge').classList.add('hidden');

                // Start countdown timer
                startQrisTimer();

                // Start polling for payment status
                startQrisPolling();

            } catch (err) {
                document.getElementById('qris-error-msg').textContent = err.message || 'Gagal membuat QR code';
                showQrisState('error');
            }
        }

        function startQrisTimer() {
            clearInterval(qrisTimerInterval);
            qrisTimerInterval = setInterval(() => {
                const now = new Date();
                const diff = Math.max(0, Math.floor((qrisExpiryTime - now) / 1000));
                const mins = Math.floor(diff / 60);
                const secs = diff % 60;
                const timerEl = document.getElementById('qris-timer');
                if (timerEl) {
                    timerEl.textContent = diff > 0 ?
                        `Berlaku ${mins}:${String(secs).padStart(2, '0')}` :
                        'QR code kadaluarsa';
                }
                if (diff === 0) {
                    clearInterval(qrisTimerInterval);
                    clearInterval(qrisPollingInterval);
                    showToast('QR code kadaluarsa. Silakan generate ulang.', 'warning');
                    showQrisState('initial');
                }
            }, 1000);
        }

        function startQrisPolling() {
            clearInterval(qrisPollingInterval);
            qrisPollingInterval = setInterval(async () => {
                if (!qrisTransactionNumber) return;
                try {
                    const res = await fetch(
                        `/api/payment/status?transaction_number=${encodeURIComponent(qrisTransactionNumber)}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                        });
                    const data = await res.json();

                    if (data.status === 'success') {
                        clearInterval(qrisPollingInterval);
                        clearInterval(qrisTimerInterval);

                        document.getElementById('qris-status-badge').classList.add('hidden');
                        document.getElementById('qris-success-badge').classList.remove('hidden');

                        showToast('Pembayaran QRIS berhasil!', 'success');

                        // Auto-complete after 1.5s
                        setTimeout(() => {
                            completeQrisTransaction(data);
                        }, 1500);
                    } else if (['failed', 'expired', 'cancelled'].includes(data.status)) {
                        clearInterval(qrisPollingInterval);
                        clearInterval(qrisTimerInterval);
                        showToast('Pembayaran gagal atau kadaluarsa', 'error');
                        showQrisState('initial');
                    }
                } catch {
                    /* ignore polling errors */
                }
            }, 3000); // Poll every 3 seconds
        }

        async function completeQrisTransaction(paymentData) {
            if (!qrisPendingOrderId) return;

            try {
                const res = await fetch(`<?php echo e(url('/pos/complete-payment')); ?>/${qrisPendingOrderId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        payment_method: 'qris',
                        amount_paid: getTotal(),
                        change: 0,
                        transaction_number: qrisTransactionNumber,
                    }),
                });

                const data = await res.json();

                if (data.success) {
                    lastReceipt = {
                        order_id: data.order_id ?? null,
                        order_number: data.order_number,
                        total: getTotal(),
                        paid_amount: getTotal(),
                        change: 0,
                        payment_method: 'qris',
                        items: [...cart],
                    };
                    showReceipt({
                        ...data,
                        total: getTotal(),
                        paid_amount: getTotal(),
                        change: 0,
                        payment_method: 'qris'
                    });
                } else {
                    showToast(data.error || 'Gagal menyelesaikan transaksi', 'error');
                }
            } catch (err) {
                showToast('Gagal menyelesaikan transaksi QRIS', 'error');
            } finally {
                resetQrisState();
            }
        }

        function cancelQris() {
            clearInterval(qrisPollingInterval);
            clearInterval(qrisTimerInterval);
            qrisTransactionNumber = null;
            qrisPendingOrderId = null;
            showQrisState('initial');
        }

        function resetQrisState() {
            clearInterval(qrisPollingInterval);
            clearInterval(qrisTimerInterval);
            qrisTransactionNumber = null;
            qrisPendingOrderId = null;
            showQrisState('initial');
        }

        function setCardType(type) {
            cardType = type;
            document.querySelectorAll('.card-type-btn').forEach(b => {
                const active = b.id === 'btn-card-' + type;
                b.classList.toggle('border-blue-500', active);
                b.classList.toggle('text-blue-400', active);
                b.classList.toggle('border-gray-700', !active);
                b.classList.toggle('text-gray-400', !active);
            });
        }

        // ── Split Payment ─────────────────────────────────────────────────────────

        const SPLIT_METHODS = {
            cash: 'Tunai',
            card: 'Kartu',
            qris: 'QRIS',
            transfer: 'Transfer',
            bank_transfer: 'Bank'
        };

        function addSplitRow() {
            const id = Date.now();
            splitRows.push({
                id,
                method: 'cash',
                amount: 0
            });
            renderSplitRows();
        }

        function removeSplitRow(id) {
            splitRows = splitRows.filter(r => r.id !== id);
            renderSplitRows();
            updateSplitRemaining();
        }

        function renderSplitRows() {
            const container = document.getElementById('split-rows');
            container.innerHTML = splitRows.map(row => `
        <div class="flex items-center gap-2" id="split-row-${row.id}">
            <select onchange="updateSplitRow(${row.id}, 'method', this.value)"
                class="flex-1 bg-gray-800 border border-gray-700 rounded-lg px-2 py-1.5 text-xs text-white focus:outline-none focus:border-blue-500">
                ${Object.entries(SPLIT_METHODS).map(([v, l]) =>
                    `<option value="${v}" ${row.method === v ? 'selected' : ''}>${l}</option>`
                ).join('')}
            </select>
            <input type="number" min="0" value="${row.amount || ''}" placeholder="0"
                onchange="updateSplitRow(${row.id}, 'amount', parseFloat(this.value)||0)"
                oninput="updateSplitRow(${row.id}, 'amount', parseFloat(this.value)||0)"
                class="w-28 bg-gray-800 border border-gray-700 rounded-lg px-2 py-1.5 text-xs text-right text-white focus:outline-none focus:border-blue-500">
            <button onclick="removeSplitRow(${row.id})" class="text-gray-600 hover:text-red-400 transition shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    `).join('');
            updateSplitRemaining();
        }

        function updateSplitRow(id, field, value) {
            const row = splitRows.find(r => r.id === id);
            if (row) {
                row[field] = value;
            }
            updateSplitRemaining();
        }

        function updateSplitRemaining() {
            const total = getTotal();
            const paid = splitRows.reduce((s, r) => s + (parseFloat(r.amount) || 0), 0);
            const remaining = total - paid;
            const el = document.getElementById('split-remaining');
            if (el) {
                el.textContent = formatRp(Math.abs(remaining));
                el.className = `font-semibold ${remaining <= 0 ? 'text-green-400' : 'text-amber-400'}`;
            }
        }

        // ── Checkout ──────────────────────────────────────────────────────────────
        // (implementasi lengkap dengan offline support ada di bawah)

        function showReceipt(data) {
            document.getElementById('receipt-order').textContent = '#' + data.order_number;
            const body = document.getElementById('receipt-body');
            const items = lastReceipt.items.map(i =>
                `<div class="flex justify-between"><span>${i.name} x${i.qty}</span><span>${formatRp(i.price * i.qty)}</span></div>`
            ).join('');

            let paymentInfo = '';
            if (data.payment_method === 'cash' || paymentMethod === 'cash') {
                const paidAmt = data.paid_amount ?? data.paid ?? lastReceipt?.paid ?? 0;
                paymentInfo = `<div class="flex justify-between text-sm mt-1"><span class="text-gray-500">Dibayar</span><span>${formatRp(paidAmt)}</span></div>
            <div class="flex justify-between font-bold text-green-600 text-xl mt-2 pt-2 border-t border-dashed border-gray-200">
                <span>Kembalian</span><span>${formatRp(data.change || 0)}</span>
            </div>`;
            } else if (data.payment_method === 'split' || paymentMethod === 'split') {
                const splitBreakdown = (lastReceipt.split_payments || splitRows).map(r =>
                    `<div class="flex justify-between text-xs text-gray-500"><span>${SPLIT_METHODS[r.method] || r.method}</span><span>${formatRp(r.amount)}</span></div>`
                ).join('');
                paymentInfo = `<div class="mt-1 text-xs text-gray-500 font-medium">Split Payment:</div>${splitBreakdown}`;
            } else {
                const methodLabel = {
                    card: 'Kartu Debit/Kredit',
                    qris: 'QRIS',
                    transfer: 'Transfer',
                    bank_transfer: 'Transfer Bank'
                };
                paymentInfo =
                    `<div class="flex justify-between text-xs text-gray-500 mt-1"><span>Metode</span><span>${methodLabel[data.payment_method] || data.payment_method}</span></div>`;
            }

            body.innerHTML = items +
                `<div class="flex justify-between mt-2 font-bold"><span>Total</span><span>${formatRp(data.total)}</span></div>` +
                paymentInfo;

            // Show earned points if any
            if (data.earned_points > 0) {
                body.innerHTML +=
                    `<div class="mt-2 text-center text-xs text-yellow-600 font-medium">⭐ +${data.earned_points.toLocaleString('id-ID')} poin diperoleh</div>`;
            }
            if (data.loyalty_discount > 0) {
                body.innerHTML +=
                    `<div class="text-center text-xs text-yellow-600">⭐ ${loyaltyPointsToRedeem.toLocaleString('id-ID')} poin ditukarkan</div>`;
            }

            document.getElementById('receipt-modal').classList.remove('hidden');
            document.getElementById('receipt-modal').classList.add('flex');

            // Auto-print if enabled
            if (posSettings.autoPrint && posSettings.printEnabled) {
                setTimeout(() => printReceiptSmart(data), 500);
            }
        }

        function closeReceipt() {
            document.getElementById('receipt-modal').classList.add('hidden');
            document.getElementById('receipt-modal').classList.remove('flex');
            clearCart();
            clearLoyaltyRedeem();
            document.getElementById('paid-input').value = '0';
            document.getElementById('discount-input').value = '0';
            document.getElementById('tax-input').value = '0';
        }

        function printReceipt() {
            if (lastReceipt) {
                printReceiptSmart(lastReceipt);
            } else {
                printViaBrowser({
                    order_number: 'N/A',
                    total: getTotal(),
                    items: cart
                });
            }
        }

        // ── Send Receipt via Email ────────────────────────────────────────────────
        function openSendEmail() {
            // Pre-fill with customer email if available
            document.getElementById('send-email-input').value = '';
            document.getElementById('send-email-status').classList.add('hidden');
            document.getElementById('send-email-modal').classList.remove('hidden');
            document.getElementById('send-email-modal').classList.add('flex');
            setTimeout(() => document.getElementById('send-email-input').focus(), 100);
        }

        function closeSendEmail() {
            document.getElementById('send-email-modal').classList.add('hidden');
            document.getElementById('send-email-modal').classList.remove('flex');
        }

        async function doSendEmail() {
            const email = document.getElementById('send-email-input').value.trim();
            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                showSendStatus('send-email-status', 'Masukkan alamat email yang valid', 'error');
                return;
            }
            if (!lastReceipt?.order_id) {
                showSendStatus('send-email-status', 'Data transaksi tidak tersedia', 'error');
                return;
            }

            const btn = document.getElementById('btn-do-send-email');
            btn.disabled = true;
            btn.textContent = 'Mengirim...';

            try {
                const res = await fetch('<?php echo e(route('pos.send-receipt')); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        order_id: lastReceipt.order_id,
                        email
                    }),
                });
                const data = await res.json();

                if (data.success) {
                    showSendStatus('send-email-status', data.message, 'success');
                    showToast(data.message, 'success');
                    setTimeout(closeSendEmail, 1500);
                } else {
                    showSendStatus('send-email-status', data.message || 'Gagal mengirim email', 'error');
                }
            } catch {
                showSendStatus('send-email-status', 'Gagal mengirim email. Coba lagi.', 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Kirim';
            }
        }

        // ── Send Receipt via WhatsApp ─────────────────────────────────────────────
        function openSendWhatsApp() {
            document.getElementById('send-wa-input').value = '';
            document.getElementById('send-wa-status').classList.add('hidden');
            document.getElementById('send-wa-modal').classList.remove('hidden');
            document.getElementById('send-wa-modal').classList.add('flex');
            setTimeout(() => document.getElementById('send-wa-input').focus(), 100);
        }

        function closeSendWa() {
            document.getElementById('send-wa-modal').classList.add('hidden');
            document.getElementById('send-wa-modal').classList.remove('flex');
        }

        async function doSendWhatsApp() {
            const phone = document.getElementById('send-wa-input').value.trim();
            if (!phone || phone.length < 8) {
                showSendStatus('send-wa-status', 'Masukkan nomor WhatsApp yang valid', 'error');
                return;
            }
            if (!lastReceipt?.order_id) {
                showSendStatus('send-wa-status', 'Data transaksi tidak tersedia', 'error');
                return;
            }

            const btn = document.getElementById('btn-do-send-wa');
            btn.disabled = true;
            btn.textContent = 'Mengirim...';

            try {
                const res = await fetch('<?php echo e(route('pos.send-receipt-whatsapp')); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        order_id: lastReceipt.order_id,
                        phone
                    }),
                });
                const data = await res.json();

                if (data.success) {
                    showSendStatus('send-wa-status', data.message, 'success');
                    showToast(data.message, 'success');
                    setTimeout(closeSendWa, 1500);
                } else {
                    showSendStatus('send-wa-status', data.message || 'Gagal mengirim WhatsApp', 'error');
                }
            } catch {
                showSendStatus('send-wa-status', 'Gagal mengirim WhatsApp. Coba lagi.', 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Kirim';
            }
        }

        function showSendStatus(elId, message, type) {
            const el = document.getElementById(elId);
            el.textContent = message;
            el.className = `text-xs mb-3 ${type === 'success' ? 'text-green-600' : 'text-red-600'}`;
            el.classList.remove('hidden');
        }

        // ── Search & Filter ───────────────────────────────────────────────────────

        // Barcode scanner detection: hardware scanners type very fast (< 50ms between chars)
        // and end with Enter. We track timing to distinguish scanner from keyboard.
        let barcodeBuffer = '';
        let barcodeLastKeyTime = 0;
        const BARCODE_SPEED_THRESHOLD = 50; // ms between keystrokes — scanner is faster
        const BARCODE_MIN_LENGTH = 3;

        // Debounce timer for text search
        let searchDebounceTimer = null;
        let searchDropdown = null;

        // Create search dropdown element
        function getSearchDropdown() {
            if (!searchDropdown) {
                searchDropdown = document.createElement('div');
                searchDropdown.id = 'search-dropdown';
                searchDropdown.className =
                    'absolute left-0 right-0 top-full mt-1 bg-gray-800 border border-gray-700 rounded-xl shadow-2xl z-50 max-h-72 overflow-y-auto hidden';
                document.getElementById('barcode-input').parentElement.appendChild(searchDropdown);
            }
            return searchDropdown;
        }

        function hideSearchDropdown() {
            const dd = document.getElementById('search-dropdown');
            if (dd) dd.classList.add('hidden');
        }

        function showSearchResults(results) {
            const dd = getSearchDropdown();
            if (!results || results.length === 0) {
                dd.innerHTML = '<div class="px-4 py-3 text-sm text-gray-500">Produk tidak ditemukan</div>';
                dd.classList.remove('hidden');
                return;
            }
            dd.innerHTML = results.map(p => {
                const outOfStock = p.stock <= 0;
                return `<div class="flex items-center gap-3 px-3 py-2.5 hover:bg-gray-700 cursor-pointer transition ${outOfStock ? 'opacity-50' : ''}"
                     onclick="addProductFromSearch(${JSON.stringify(p).replace(/"/g, '&quot;')})">
            <div class="w-8 h-8 bg-gray-700 rounded-lg flex items-center justify-center shrink-0 overflow-hidden">
                ${p.image_url ? `<img src="${p.image_url}" class="w-full h-full object-cover rounded-lg" alt="">` : '<svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>'}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-medium text-white truncate">${p.name}</p>
                <p class="text-xs text-gray-500">${p.sku || ''} ${p.barcode ? '· ' + p.barcode : ''}</p>
            </div>
            <div class="text-right shrink-0">
                <p class="text-xs font-semibold text-blue-400">${formatRp(p.price)}</p>
                <p class="text-xs ${outOfStock ? 'text-red-400' : 'text-gray-500'}">Stok: ${p.stock}</p>
            </div>
        </div>`;
            }).join('');
            dd.classList.remove('hidden');
        }

        function addProductFromSearch(p) {
            hideSearchDropdown();
            document.getElementById('barcode-input').value = '';

            if (p.stock <= 0) {
                showToast('Stok ' + p.name + ' habis', 'warning');
                return;
            }

            // Try to find existing card in DOM first
            const card = document.querySelector(`.product-card[data-id="${p.id}"]`);
            if (card) {
                addToCart(card);
                return;
            }

            // Product not in DOM (loaded via search) — add directly to cart
            const existing = cart.find(i => i.id === p.id);
            if (existing) {
                if (existing.qty >= p.stock) {
                    showToast('Stok tidak cukup', 'warning');
                    return;
                }
                existing.qty++;
            } else {
                cart.push({
                    id: p.id,
                    name: p.name,
                    price: p.price,
                    stock: p.stock,
                    qty: 1
                });
            }
            renderCart();
            showToast(p.name + ' ditambahkan', 'success');
        }

        async function doServerSearch(q) {
            if (!q || q.length < 1) {
                hideSearchDropdown();
                return;
            }
            try {
                const res = await fetch(`<?php echo e(route('pos.search')); ?>?q=${encodeURIComponent(q)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (res.ok) {
                    const data = await res.json();
                    showSearchResults(data);
                }
            } catch {
                hideSearchDropdown();
            }
        }

        const barcodeInput = document.getElementById('barcode-input');

        barcodeInput.addEventListener('keydown', function(e) {
            const now = Date.now();
            const timeSinceLast = now - barcodeLastKeyTime;
            barcodeLastKeyTime = now;

            if (e.key === 'Enter') {
                e.preventDefault();
                const val = this.value.trim();
                if (!val) return;

                // Always treat Enter as barcode lookup (hardware scanner or manual)
                handleBarcodeEnter(val);
                this.value = '';
                hideSearchDropdown();
                clearTimeout(searchDebounceTimer);
                barcodeBuffer = '';
                return;
            }

            // Track if this looks like a hardware scanner (fast keystrokes)
            if (timeSinceLast < BARCODE_SPEED_THRESHOLD && e.key.length === 1) {
                barcodeBuffer += e.key;
            } else {
                barcodeBuffer = e.key.length === 1 ? e.key : '';
            }
        });

        barcodeInput.addEventListener('input', function() {
            const q = this.value.trim();

            // Client-side filter on product grid
            document.querySelectorAll('.product-card').forEach(card => {
                const match = !q ||
                    card.dataset.name.toLowerCase().includes(q.toLowerCase()) ||
                    (card.dataset.sku || '').toLowerCase().includes(q.toLowerCase()) ||
                    (card.dataset.barcode || '').toLowerCase().includes(q.toLowerCase());
                card.style.display = match ? '' : 'none';
            });

            // Debounced server search for dropdown (handles large catalogs)
            clearTimeout(searchDebounceTimer);
            if (q.length >= 2) {
                searchDebounceTimer = setTimeout(() => doServerSearch(q), 300);
            } else {
                hideSearchDropdown();
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!barcodeInput.contains(e.target) && !document.getElementById('search-dropdown')?.contains(e
                    .target)) {
                hideSearchDropdown();
            }
        });

        async function handleBarcodeEnter(barcode) {
            // First try DOM (instant)
            const card = document.querySelector(`.product-card[data-barcode="${barcode}"]`) ||
                document.querySelector(`.product-card[data-sku="${barcode}"]`);
            if (card) {
                if (parseInt(card.dataset.stock) > 0) {
                    addToCart(card);
                    showToast(card.dataset.name + ' ditambahkan', 'success');
                } else {
                    showToast('Stok ' + card.dataset.name + ' habis', 'warning');
                }
                return;
            }

            // API lookup (with cache on server side)
            try {
                const res = await fetch(`<?php echo e(route('pos.barcode')); ?>?barcode=${encodeURIComponent(barcode)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (res.ok) {
                    const p = await res.json();
                    if (p.id) {
                        addProductFromSearch(p);
                        return;
                    }
                }
            } catch {}

            showToast('Produk tidak ditemukan: ' + barcode, 'error');
        }

        function filterCategory(cat) {
            document.querySelectorAll('.cat-btn').forEach(b => {
                const active = b.dataset.cat === cat;
                b.className =
                    `cat-btn px-3 py-1.5 rounded-lg text-xs font-medium whitespace-nowrap transition ${active ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600'}`;
            });
            document.querySelectorAll('.product-card').forEach(card => {
                card.style.display = (!cat || card.dataset.category === cat) ? '' : 'none';
            });
        }

        // ── Keyboard shortcuts ────────────────────────────────────────────────────
        document.addEventListener('keydown', e => {
            if (e.key === 'F2') document.getElementById('barcode-input').focus();
            if (e.key === 'F12') processCheckout();
            if (e.key === 'Escape') {
                closeReceipt();
                closeCameraScanner();
            }
        });

        // ── Helpers ───────────────────────────────────────────────────────────────
        function formatRp(n) {
            return 'Rp ' + Math.round(n).toLocaleString('id-ID');
        }

        // ── Offline Queue (IndexedDB) ─────────────────────────────────────────────
        const DB_NAME = 'qalcuity-pos';
        const DB_VERSION = 1;
        let posDb = null;

        async function getDb() {
            if (posDb) return posDb;
            return new Promise((resolve, reject) => {
                const req = indexedDB.open(DB_NAME, DB_VERSION);
                req.onupgradeneeded = e => {
                    const db = e.target.result;
                    if (!db.objectStoreNames.contains('pos_queue')) {
                        const store = db.createObjectStore('pos_queue', {
                            keyPath: 'id',
                            autoIncrement: true
                        });
                        store.createIndex('queued_at', 'queued_at');
                    }
                };
                req.onsuccess = e => {
                    posDb = e.target.result;
                    resolve(posDb);
                };
                req.onerror = e => reject(e.target.error);
            });
        }

        async function queueTransaction(payload) {
            const db = await getDb();
            return new Promise((resolve, reject) => {
                const tx = db.transaction('pos_queue', 'readwrite');
                const store = tx.objectStore('pos_queue');
                const req = store.add({
                    payload,
                    queued_at: Date.now(),
                    csrf: document.querySelector('meta[name="csrf-token"]').content
                });
                req.onsuccess = () => {
                    resolve(req.result);
                    // Update badge count after queuing
                    updateOfflineBadge();
                };
                req.onerror = e => reject(e.target.error);
            });
        }

        async function getPendingCount() {
            try {
                const db = await getDb();
                return new Promise(resolve => {
                    const tx = db.transaction('pos_queue', 'readonly');
                    const req = tx.objectStore('pos_queue').count();
                    req.onsuccess = () => resolve(req.result);
                    req.onerror = () => resolve(0);
                });
            } catch {
                return 0;
            }
        }

        async function flushQueue() {
            const db = await getDb();
            const tx = db.transaction('pos_queue', 'readwrite');
            const store = tx.objectStore('pos_queue');
            const items = await new Promise(r => {
                const q = store.getAll();
                q.onsuccess = () => r(q.result);
            });

            if (items.length === 0) return;

            // Update badge to show syncing state
            const badge = document.getElementById('offline-badge');
            const badgeText = document.getElementById('offline-badge-text');
            if (badgeText) badgeText.textContent = `Menyinkronkan ${items.length} antrian...`;

            // Build transactions array for batch sync endpoint
            const transactions = items.map(item => ({
                offline_id: String(item.id),
                ...item.payload,
            }));

            let synced = 0;
            let failed = 0;

            try {
                const res = await fetch('<?php echo e(route('pos.sync-offline')); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Offline-Sync': '1',
                    },
                    body: JSON.stringify({
                        transactions
                    }),
                });

                if (res.ok) {
                    const result = await res.json();
                    synced = result.synced || 0;
                    failed = result.failed || 0;

                    // Remove successfully synced items from IndexedDB
                    const successIds = (result.results || [])
                        .filter(r => r.success)
                        .map(r => r.offline_id);

                    for (const offlineId of successIds) {
                        const item = items.find(i => String(i.id) === offlineId);
                        if (item) {
                            const delDb = await getDb();
                            const delTx = delDb.transaction('pos_queue', 'readwrite');
                            delTx.objectStore('pos_queue').delete(item.id);
                            await new Promise(r => {
                                delTx.oncomplete = r;
                                delTx.onerror = r;
                            });
                        }
                    }

                    if (synced > 0) showToast(`${synced} transaksi offline berhasil disinkronisasi`, 'success');
                    if (failed > 0) showToast(`${failed} transaksi gagal disinkronisasi, akan dicoba lagi`, 'warning');
                } else {
                    // Fallback: try one by one via checkout endpoint
                    for (const item of items) {
                        try {
                            const r = await fetch('<?php echo e(route('pos.checkout')); ?>', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': item.csrf
                                },
                                body: JSON.stringify(item.payload),
                            });
                            if (r.ok) {
                                const delDb = await getDb();
                                const delTx = delDb.transaction('pos_queue', 'readwrite');
                                delTx.objectStore('pos_queue').delete(item.id);
                                await new Promise(r2 => {
                                    delTx.oncomplete = r2;
                                    delTx.onerror = r2;
                                });
                                synced++;
                            } else {
                                failed++;
                            }
                        } catch {
                            failed++;
                        }
                    }
                    if (synced > 0) showToast(`${synced} transaksi offline berhasil disinkronisasi`, 'success');
                    if (failed > 0) showToast(`${failed} transaksi gagal disinkronisasi, akan dicoba lagi`, 'warning');
                }
            } catch (e) {
                // Network still unavailable — restore badge
                console.warn('[POS] Flush queue failed:', e);
            }

            // Update badge count after sync
            await updateOfflineBadge();
        }

        // ── Offline / Online detection ────────────────────────────────────────────
        async function updateOfflineBadge() {
            const badge = document.getElementById('offline-badge');
            const badgeText = document.getElementById('offline-badge-text');
            if (!badge || !badgeText) return;

            if (navigator.onLine) {
                badge.classList.add('hidden');
                badge.classList.remove('flex');
            } else {
                const count = await getPendingCount();
                badgeText.textContent = count > 0 ? `Offline (${count} antrian)` : 'Offline';
                badge.classList.remove('hidden');
                badge.classList.add('flex');
            }
        }

        function updateOnlineStatus() {
            if (navigator.onLine) {
                const badge = document.getElementById('offline-badge');
                if (badge) {
                    badge.classList.add('hidden');
                    badge.classList.remove('flex');
                }
                flushQueue(); // try to sync pending transactions
            } else {
                updateOfflineBadge();
            }
        }
        window.addEventListener('online', updateOnlineStatus);
        window.addEventListener('offline', updateOnlineStatus);
        updateOnlineStatus();

        // Listen for SW sync success messages
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.addEventListener('message', e => {
                if (e.data?.type === 'POS_SYNC_SUCCESS') {
                    const synced = e.data.synced || 0;
                    const failed = e.data.failed || 0;
                    if (synced > 0) showToast(`${synced} transaksi offline berhasil disinkronisasi`, 'success');
                    if (failed > 0) showToast(`${failed} transaksi gagal disinkronisasi`, 'warning');
                    updateOfflineBadge();
                }
                // Respond to SW requesting CSRF token
                if (e.data?.type === 'GET_CSRF_TOKEN' && e.ports?.[0]) {
                    e.ports[0].postMessage({
                        csrf_token: document.querySelector('meta[name="csrf-token"]')?.content || null,
                    });
                }
            });
        }

        // ── Checkout (dengan offline support) ────────────────────────────────────
        async function processCheckout() {
            if (cart.length === 0) {
                showToast('Keranjang kosong', 'warning');
                return;
            }

            const total = getTotal();
            const paid = parseFloat(document.getElementById('paid-input').value.replace(/\./g, '')) || 0;
            const discount = parseFloat(document.getElementById('discount-input').value) || 0;
            const tax = parseFloat(document.getElementById('tax-input').value) || 0;

            if (paymentMethod === 'cash' && paid < total) {
                showToast('Uang yang diterima kurang dari total', 'error');
                return;
            }

            // Validate split payment
            if (paymentMethod === 'split') {
                const splitTotal = splitRows.reduce((s, r) => s + (parseFloat(r.amount) || 0), 0);
                if (Math.abs(splitTotal - total) > 1) {
                    showToast(`Total split (${formatRp(splitTotal)}) harus sama dengan total (${formatRp(total)})`,
                        'error');
                    return;
                }
                if (splitRows.length === 0) {
                    showToast('Tambahkan minimal satu metode pembayaran', 'error');
                    return;
                }
            }

            // Determine actual payment method for card (debit/credit)
            let actualMethod = paymentMethod;
            if (paymentMethod === 'card') {
                actualMethod = cardType === 'credit' ? 'credit' : 'card';
            }

            // Determine paid_amount
            let paidAmount;
            if (paymentMethod === 'cash') {
                paidAmount = paid;
            } else if (paymentMethod === 'split') {
                paidAmount = splitRows.reduce((s, r) => s + (parseFloat(r.amount) || 0), 0);
            } else {
                paidAmount = total;
            }

            const payload = {
                items: cart.map(i => ({
                    id: i.id,
                    qty: i.qty,
                    price: i.price
                })),
                payment_method: actualMethod,
                paid_amount: paidAmount,
                discount,
                tax,
                customer_id: document.getElementById('customer-select').value || null,
                loyalty_points_redeemed: loyaltyPointsToRedeem > 0 ? loyaltyPointsToRedeem : undefined,
            };

            if (paymentMethod === 'split') {
                payload.split_payments = splitRows.map(r => ({
                    method: r.method,
                    amount: parseFloat(r.amount) || 0
                }));
            }

            // If QRIS, use payment gateway flow instead
            if (paymentMethod === 'qris') {
                await generateQrisCode();
                return;
            }

            // If offline, queue the transaction
            if (!navigator.onLine) {
                await queueTransaction(payload);
                const count = await getPendingCount();
                showToast(`Offline: transaksi disimpan (${count} antrian)`, 'warning');

                // Show a local receipt
                lastReceipt = {
                    items: [...cart],
                    discount,
                    tax,
                    paid: payload.paid_amount
                };
                document.getElementById('receipt-order').textContent = '#OFFLINE-' + Date.now().toString().slice(-6);
                const body = document.getElementById('receipt-body');
                body.innerHTML = cart.map(i =>
                        `<div class="flex justify-between"><span>${i.name} x${i.qty}</span><span>${formatRp(i.price * i.qty)}</span></div>`
                    ).join('') +
                    `<div class="flex justify-between mt-2 font-bold"><span>Total</span><span>${formatRp(total)}</span></div>
        <div class="mt-2 text-xs text-amber-600 text-center">⚠ Transaksi offline — akan disinkronisasi saat online</div>`;
                document.getElementById('receipt-modal').classList.remove('hidden');
                document.getElementById('receipt-modal').classList.add('flex');

                // Register background sync if supported
                if ('serviceWorker' in navigator && 'SyncManager' in window) {
                    const reg = await navigator.serviceWorker.ready;
                    await reg.sync.register('pos-checkout-sync').catch(() => {});
                }
                return;
            }

            // Online: normal checkout
            try {
                const res = await fetch('<?php echo e(route('pos.checkout')); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(payload),
                });
                const data = await res.json();

                if (data.status === 'success') {
                    lastReceipt = {
                        ...data,
                        order_id: data.order_id ?? null,
                        items: [...cart],
                        discount,
                        tax,
                        paid: payload.paid_amount,
                        paid_amount: payload.paid_amount
                    };
                    showReceipt(data);
                } else {
                    showToast('Gagal memproses transaksi', 'error');
                }
            } catch (e) {
                // Network error while supposedly online — queue it
                await queueTransaction(payload);
                showToast('Koneksi bermasalah. Transaksi disimpan untuk sinkronisasi.', 'warning');
            }
        }

        // ── Camera Barcode Scanner ────────────────────────────────────────────────
        let cameraStream = null;
        let barcodeDetector = null;
        let scanInterval = null;

        async function openCameraScanner() {
            const modal = document.getElementById('camera-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            // Init BarcodeDetector
            if ('BarcodeDetector' in window) {
                try {
                    const formats = await BarcodeDetector.getSupportedFormats();
                    barcodeDetector = new BarcodeDetector({
                        formats
                    });
                } catch {
                    barcodeDetector = new BarcodeDetector({
                        formats: ['ean_13', 'ean_8', 'code_128', 'code_39', 'qr_code', 'upc_a', 'upc_e']
                    });
                }
            }

            // Start camera
            try {
                cameraStream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'environment',
                        width: {
                            ideal: 1280
                        },
                        height: {
                            ideal: 720
                        }
                    }
                });
                const video = document.getElementById('camera-video');
                video.srcObject = cameraStream;
                await video.play();

                if (barcodeDetector) {
                    startBarcodeDetection();
                } else {
                    // Fallback: ZXing via CDN
                    loadZxingFallback();
                }
            } catch (err) {
                setCameraStatus('Kamera tidak dapat diakses: ' + err.message, true);
            }
        }

        function startBarcodeDetection() {
            const video = document.getElementById('camera-video');
            setCameraStatus('Arahkan kamera ke barcode...');

            scanInterval = setInterval(async () => {
                if (video.readyState !== video.HAVE_ENOUGH_DATA) return;
                try {
                    const barcodes = await barcodeDetector.detect(video);
                    if (barcodes.length > 0) {
                        const code = barcodes[0].rawValue;
                        clearInterval(scanInterval);
                        setCameraStatus('✓ Barcode terdeteksi: ' + code, false, true);
                        await handleScannedBarcode(code);
                    }
                } catch {
                    /* continue scanning */
                }
            }, 200);
        }

        async function loadZxingFallback() {
            setCameraStatus('Memuat scanner fallback...');
            // Dynamically load ZXing
            if (!window.ZXing) {
                const script = document.createElement('script');
                script.src = 'https://unpkg.com/@zxing/library@0.21.3/umd/index.min.js';
                script.onload = () => startZxingDetection();
                script.onerror = () => setCameraStatus('Scanner tidak tersedia. Gunakan input manual.', true);
                document.head.appendChild(script);
            } else {
                startZxingDetection();
            }
        }

        function startZxingDetection() {
            try {
                const codeReader = new ZXing.BrowserMultiFormatReader();
                const video = document.getElementById('camera-video');
                setCameraStatus('Arahkan kamera ke barcode...');

                codeReader.decodeFromVideoElement(video, (result, err) => {
                    if (result) {
                        codeReader.reset();
                        const code = result.getText();
                        setCameraStatus('✓ Barcode terdeteksi: ' + code, false, true);
                        handleScannedBarcode(code);
                    }
                });

                // Store reader for cleanup
                window._zxingReader = codeReader;
            } catch (e) {
                setCameraStatus('Scanner error: ' + e.message, true);
            }
        }

        async function handleScannedBarcode(code) {
            // Vibrate on success
            if ('vibrate' in navigator) navigator.vibrate([100, 50, 100]);
            playScanSound();

            // Try to find product in current DOM first (fast path)
            const card = document.querySelector(`.product-card[data-barcode="${code}"]`) ||
                document.querySelector(`.product-card[data-sku="${code}"]`);

            if (card) {
                closeCameraScanner();
                addToCart(card);
                showToast('Produk ditambahkan: ' + card.dataset.name, 'success');
                return;
            }

            // Fallback: API lookup (with cache)
            try {
                const res = await fetch(`<?php echo e(route('pos.barcode')); ?>?barcode=${encodeURIComponent(code)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (res.ok) {
                    const p = await res.json();
                    if (p.id) {
                        closeCameraScanner();
                        addProductFromSearch(p);
                        return;
                    }
                }
            } catch {}

            setCameraStatus('Produk tidak ditemukan: ' + code, true);
            // Resume scanning after 2s
            setTimeout(() => {
                setCameraStatus('Arahkan kamera ke barcode...');
                if (barcodeDetector) startBarcodeDetection();
            }, 2000);
        }

        function closeCameraScanner() {
            clearInterval(scanInterval);
            if (window._zxingReader) {
                window._zxingReader.reset();
                window._zxingReader = null;
            }
            if (cameraStream) {
                cameraStream.getTracks().forEach(t => t.stop());
                cameraStream = null;
            }
            const video = document.getElementById('camera-video');
            video.srcObject = null;
            document.getElementById('camera-modal').classList.add('hidden');
            document.getElementById('camera-modal').classList.remove('flex');
        }

        async function submitManualBarcode() {
            const input = document.getElementById('manual-barcode-input');
            const code = input.value.trim();
            if (!code) return;
            input.value = '';
            await handleScannedBarcode(code);
        }

        document.getElementById('manual-barcode-input')?.addEventListener('keydown', e => {
            if (e.key === 'Enter') submitManualBarcode();
        });

        function setCameraStatus(msg, isError = false, isSuccess = false) {
            const el = document.getElementById('camera-status');
            const color = isError ? 'text-red-400' : isSuccess ? 'text-green-400' : 'text-white/70';
            el.innerHTML = `<span class="text-xs ${color} bg-black/50 px-3 py-1 rounded-full">${msg}</span>`;
        }

        // ── Service Worker Registration ───────────────────────────────────────────
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js', {
                        scope: '/'
                    })
                    .then(reg => {
                        // Check for updates every 60s
                        setInterval(() => reg.update(), 60_000);
                    })
                    .catch(err => console.warn('SW registration failed:', err));
            });
        }

        // ── POS Settings ──────────────────────────────────────────────────────────
        const POS_SETTINGS_KEY = 'qalcuity_pos_settings';
        let posSettings = {
            printEnabled: false,
            autoPrint: false,
            printMethod: 'browser',
            paperWidth: '58',
            cameraEnabled: true,
            hwScannerEnabled: true,
            scanSound: true,
            storeName: '<?php echo e(addslashes(auth()->user()->tenant?->name ?? 'Toko')); ?>',
            storeAddress: '<?php echo e(addslashes(auth()->user()->tenant?->address ?? '')); ?>',
            receiptFooter: 'Terima kasih atas kunjungan Anda!',
            showLogo: false,
        };
        let thermalPort = null;
        let thermalWriter = null;

        function loadPosSettings() {
            try {
                const saved = localStorage.getItem(POS_SETTINGS_KEY);
                if (saved) posSettings = {
                    ...posSettings,
                    ...JSON.parse(saved)
                };
            } catch {}
            // Apply to UI
            document.getElementById('pos-print-enabled').checked = posSettings.printEnabled;
            document.getElementById('pos-auto-print').checked = posSettings.autoPrint;
            document.getElementById('pos-print-method').value = posSettings.printMethod;
            document.getElementById('pos-paper-width').value = posSettings.paperWidth;
            document.getElementById('pos-camera-enabled').checked = posSettings.cameraEnabled;
            document.getElementById('pos-hw-scanner-enabled').checked = posSettings.hwScannerEnabled;
            document.getElementById('pos-scan-sound').checked = posSettings.scanSound;
            document.getElementById('pos-store-name').value = posSettings.storeName;
            document.getElementById('pos-store-address').value = posSettings.storeAddress;
            document.getElementById('pos-receipt-footer').value = posSettings.receiptFooter;
            document.getElementById('pos-show-logo').checked = posSettings.showLogo;
            // Toggle camera button visibility
            document.getElementById('btn-camera-scan').style.display = posSettings.cameraEnabled ? '' : 'none';
            // Toggle thermal connect section
            document.getElementById('thermal-connect-section').classList.toggle('hidden', posSettings.printMethod ===
                'browser');
        }

        function savePosSettings() {
            posSettings.printEnabled = document.getElementById('pos-print-enabled').checked;
            posSettings.autoPrint = document.getElementById('pos-auto-print').checked;
            posSettings.printMethod = document.getElementById('pos-print-method').value;
            posSettings.paperWidth = document.getElementById('pos-paper-width').value;
            posSettings.cameraEnabled = document.getElementById('pos-camera-enabled').checked;
            posSettings.hwScannerEnabled = document.getElementById('pos-hw-scanner-enabled').checked;
            posSettings.scanSound = document.getElementById('pos-scan-sound').checked;
            posSettings.storeName = document.getElementById('pos-store-name').value;
            posSettings.storeAddress = document.getElementById('pos-store-address').value;
            posSettings.receiptFooter = document.getElementById('pos-receipt-footer').value;
            posSettings.showLogo = document.getElementById('pos-show-logo').checked;
            localStorage.setItem(POS_SETTINGS_KEY, JSON.stringify(posSettings));
            // Toggle UI
            document.getElementById('btn-camera-scan').style.display = posSettings.cameraEnabled ? '' : 'none';
            document.getElementById('thermal-connect-section').classList.toggle('hidden', posSettings.printMethod ===
                'browser');
        }

        // ── Fullscreen (persisten antar halaman) ─────────────────────────────────
        const FS_KEY = 'qalcuity_fullscreen';

        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(() => {});
                localStorage.setItem(FS_KEY, '1');
            } else {
                document.exitFullscreen().catch(() => {});
                localStorage.removeItem(FS_KEY);
            }
        }

        function updateFullscreenIcon() {
            const isFs = !!document.fullscreenElement;
            document.getElementById('icon-fullscreen-enter').classList.toggle('hidden', isFs);
            document.getElementById('icon-fullscreen-exit').classList.toggle('hidden', !isFs);
            document.getElementById('btn-fullscreen').title = isFs ? 'Keluar layar penuh (Esc)' : 'Layar penuh (F11)';
        }

        document.addEventListener('fullscreenchange', function() {
            if (!document.fullscreenElement) {
                localStorage.removeItem(FS_KEY);
                const el = document.getElementById('fs-restore-overlay');
                if (el) el.classList.add('hidden');
            }
            updateFullscreenIcon();
        });

        // F11 shortcut
        document.addEventListener('keydown', function(e) {
            if (e.key === 'F11') {
                e.preventDefault();
                toggleFullscreen();
            }
        });

        function _doRestoreFs() {
            const el = document.getElementById('fs-restore-overlay');
            if (el) el.classList.add('hidden');
            document.documentElement.requestFullscreen().catch(function() {
                localStorage.removeItem(FS_KEY);
            });
        }

        // Auto-restore fullscreen saat halaman load
        if (localStorage.getItem(FS_KEY) === '1' && !document.fullscreenElement) {
            const _showPosFsOverlay = function() {
                const el = document.getElementById('fs-restore-overlay');
                if (el) el.classList.remove('hidden');
            };
            document.addEventListener('DOMContentLoaded', _showPosFsOverlay);
            if (document.readyState !== 'loading') _showPosFsOverlay();
        }

        function openPosSettings() {
            loadPosSettings();
            const m = document.getElementById('pos-settings-modal');
            m.classList.remove('hidden');
            m.classList.add('flex');
        }

        function closePosSettings() {
            const m = document.getElementById('pos-settings-modal');
            m.classList.add('hidden');
            m.classList.remove('flex');
        }

        // ── Thermal Printer (Web Serial API) ──────────────────────────────────────
        async function connectThermalPrinter() {
            if (!('serial' in navigator)) {
                showToast('Browser tidak mendukung Web Serial API. Gunakan Chrome/Edge.', 'error');
                return;
            }
            try {
                thermalPort = await navigator.serial.requestPort();
                await thermalPort.open({
                    baudRate: 9600
                });
                thermalWriter = thermalPort.writable.getWriter();
                document.getElementById('printer-status').textContent = '✅ Printer terhubung';
                document.getElementById('btn-connect-printer').textContent = '✅ Terhubung';
                document.getElementById('btn-connect-printer').classList.replace('bg-blue-600', 'bg-green-600');
                showToast('Printer thermal terhubung', 'success');
            } catch (e) {
                document.getElementById('printer-status').textContent = '❌ Gagal: ' + e.message;
                showToast('Gagal menghubungkan printer: ' + e.message, 'error');
            }
        }

        // ESC/POS commands for thermal printer
        function escPos(text) {
            const encoder = new TextEncoder();
            return encoder.encode(text);
        }

        async function printToThermal(receiptText) {
            if (!thermalWriter) {
                showToast('Printer belum terhubung. Buka Settings → Hubungkan Printer.', 'warning');
                return;
            }
            try {
                const ESC = '\x1B';
                const GS = '\x1D';
                // Init printer
                await thermalWriter.write(escPos(ESC + '@'));
                // Center align
                await thermalWriter.write(escPos(ESC + 'a' + '\x01'));
                // Bold store name
                await thermalWriter.write(escPos(ESC + 'E' + '\x01'));
                await thermalWriter.write(escPos(posSettings.storeName + '\n'));
                await thermalWriter.write(escPos(ESC + 'E' + '\x00'));
                if (posSettings.storeAddress) await thermalWriter.write(escPos(posSettings.storeAddress + '\n'));
                await thermalWriter.write(escPos('================================\n'));
                // Left align
                await thermalWriter.write(escPos(ESC + 'a' + '\x00'));
                await thermalWriter.write(escPos(receiptText));
                // Footer
                await thermalWriter.write(escPos(ESC + 'a' + '\x01'));
                await thermalWriter.write(escPos('\n' + (posSettings.receiptFooter || '') + '\n\n'));
                // Cut paper
                await thermalWriter.write(escPos(GS + 'V' + '\x00'));
                showToast('Struk berhasil dicetak', 'success');
            } catch (e) {
                showToast('Gagal cetak: ' + e.message, 'error');
            }
        }

        // ── Receipt Builder ───────────────────────────────────────────────────────
        function buildReceiptText(data) {
            const now = new Date();
            const date = now.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            const time = now.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit'
            });
            let lines = [];
            lines.push(`Tanggal : ${date} ${time}`);
            lines.push(`No      : ${data.order_number || 'N/A'}`);
            lines.push(`Kasir   : <?php echo e(auth()->user()->name); ?>`);
            lines.push('--------------------------------');
            const items = data.items || lastReceipt?.items || cart;
            items.forEach(i => {
                const name = (i.name || '').substring(0, 20);
                const total = formatRpPlain(i.price * i.qty);
                lines.push(`${name}`);
                lines.push(`  ${i.qty} x ${formatRpPlain(i.price)}  ${total}`);
            });
            lines.push('--------------------------------');
            lines.push(`Subtotal     ${formatRpPlain(data.total || getTotal())}`);
            if (data.change !== undefined && paymentMethod === 'cash') {
                const paid = data.paid_amount || data.paid || (parseFloat(document.getElementById('paid-input').value
                    .replace(/\./g, '')) || 0);
                lines.push(`Bayar        ${formatRpPlain(paid)}`);
                lines.push(`Kembalian    ${formatRpPlain(data.change)}`);
            }
            lines.push(`Metode       ${paymentMethod.toUpperCase()}`);
            return lines.join('\n') + '\n';
        }

        function formatRpPlain(n) {
            return 'Rp ' + Math.round(n).toLocaleString('id-ID');
        }

        function buildReceiptHtml(data) {
            const w = posSettings.paperWidth === 'a4' ? '100%' : (posSettings.paperWidth === '80' ? '280px' : '220px');
            const items = data.items || lastReceipt?.items || cart;
            let itemsHtml = items.map(i =>
                `<div style="display:flex;justify-content:space-between;font-size:11px"><span>${(i.name||'').substring(0,22)}</span><span>${formatRpPlain(i.price*i.qty)}</span></div>
         <div style="font-size:10px;color:#666;margin-bottom:2px">&nbsp;&nbsp;${i.qty} x ${formatRpPlain(i.price)}</div>`
            ).join('');
            return `<div style="font-family:monospace;width:${w};padding:10px;color:#000;background:#fff;font-size:12px">
        <div style="text-align:center;font-weight:bold;font-size:14px">${posSettings.storeName}</div>
        ${posSettings.storeAddress ? `<div style="text-align:center;font-size:10px;color:#666">${posSettings.storeAddress}</div>` : ''}
        <hr style="border:none;border-top:1px dashed #999;margin:6px 0">
        <div style="font-size:10px;color:#666">Tanggal: ${new Date().toLocaleString('id-ID')}</div>
        <div style="font-size:10px;color:#666">No: ${data.order_number || 'N/A'}</div>
        <div style="font-size:10px;color:#666">Kasir: <?php echo e(auth()->user()->name); ?></div>
        <hr style="border:none;border-top:1px dashed #999;margin:6px 0">
        ${itemsHtml}
        <hr style="border:none;border-top:1px dashed #999;margin:6px 0">
        <div style="display:flex;justify-content:space-between;font-weight:bold"><span>TOTAL</span><span>${formatRpPlain(data.total || getTotal())}</span></div>
        ${data.change !== undefined && paymentMethod === 'cash' ? `<div style="display:flex;justify-content:space-between;font-size:11px"><span>Bayar</span><span>${formatRpPlain(data.paid_amount || data.paid || 0)}</span></div><div style="display:flex;justify-content:space-between;font-size:11px;font-weight:bold;color:#16a34a"><span>Kembali</span><span>${formatRpPlain(data.change)}</span></div>` : ''}
        <div style="font-size:10px;color:#666;margin-top:2px">Metode: ${paymentMethod.toUpperCase()}</div>
        <hr style="border:none;border-top:1px dashed #999;margin:6px 0">
        <div style="text-align:center;font-size:10px;color:#666">${posSettings.receiptFooter || ''}</div>
    </div>`;
        }

        // ── Print Dispatcher ──────────────────────────────────────────────────────
        async function printReceiptSmart(data) {
            if (!posSettings.printEnabled) return;

            if (posSettings.printMethod === 'thermal' && thermalWriter) {
                await printToThermal(buildReceiptText(data));
            } else if (posSettings.printMethod === 'bluetooth') {
                // Bluetooth printing via Web Bluetooth API
                await printViaBluetooth(buildReceiptText(data));
            } else {
                // Browser print
                printViaBrowser(data);
            }
        }

        function printViaBrowser(data) {
            const html = buildReceiptHtml(data);
            const paperW = posSettings.paperWidth === 'a4' ? 'auto' : (posSettings.paperWidth === '80' ? '80mm' : '58mm');
            const printWindow = window.open('', '_blank', 'width=400,height=600');
            printWindow.document.write(`<!DOCTYPE html><html><head><title>Struk #${data.order_number || ''}</title>
        <style>
            @media print {
                @page { size: ${paperW} auto; margin: 0; }
                body { margin: 0; padding: 0; }
            }
            body { margin: 0; padding: 0; background: #fff; }
        </style>
    </head><body style="margin:0;padding:0">${html}</body></html>`);
            printWindow.document.close();
            printWindow.focus();
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 300);
        }

        // ── Bluetooth Printer ─────────────────────────────────────────────────────
        async function printViaBluetooth(text) {
            if (!('bluetooth' in navigator)) {
                showToast('Browser tidak mendukung Bluetooth. Gunakan Chrome.', 'error');
                return;
            }
            try {
                const device = await navigator.bluetooth.requestDevice({
                    filters: [{
                        services: ['000018f0-0000-1000-8000-00805f9b34fb']
                    }],
                    optionalServices: ['000018f0-0000-1000-8000-00805f9b34fb']
                });
                const server = await device.gatt.connect();
                const service = await server.getPrimaryService('000018f0-0000-1000-8000-00805f9b34fb');
                const char = await service.getCharacteristic('00002af1-0000-1000-8000-00805f9b34fb');
                const encoder = new TextEncoder();
                const data = encoder.encode('\x1B@' + posSettings.storeName + '\n' + (posSettings.storeAddress || '') +
                    '\n================================\n' + text + '\n' + (posSettings.receiptFooter || '') +
                    '\n\n\n');
                // Send in chunks (BLE has 20-byte MTU)
                for (let i = 0; i < data.length; i += 20) {
                    await char.writeValue(data.slice(i, i + 20));
                }
                showToast('Struk dicetak via Bluetooth', 'success');
            } catch (e) {
                showToast('Bluetooth print gagal: ' + e.message, 'error');
            }
        }

        function testPrint() {
            const testData = {
                order_number: 'TEST-001',
                total: 50000,
                change: 0,
                paid: 50000,
                items: [{
                    name: 'Test Produk A',
                    qty: 2,
                    price: 15000
                }, {
                    name: 'Test Produk B',
                    qty: 1,
                    price: 20000
                }],
            };
            printReceiptSmart(testData);
        }

        // ── Scanner Sound ─────────────────────────────────────────────────────────
        const scanBeep = new Audio('data:audio/wav;base64,UklGRl9vT19teleVBFTVQAAAAGIAAABkYXRhAAAA');

        function playScanSound() {
            if (posSettings.scanSound) {
                try {
                    scanBeep.currentTime = 0;
                    scanBeep.play().catch(() => {});
                } catch {}
                if ('vibrate' in navigator) navigator.vibrate(100);
            }
        }

        // Init
        setPayment('cash');
        loadPosSettings();
    </script>
</body>

</html>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/pos/index.blade.php ENDPATH**/ ?>