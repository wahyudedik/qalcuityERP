<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kasir POS — Qalcuity</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" href="/favicon.png">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0f172a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="/favicon.png">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gray-950 font-[Inter,sans-serif] text-white overflow-hidden">

<div class="flex h-full" id="pos-app">

    {{-- ── Left: Product Grid ─────────────────────────────── --}}
    <div class="flex-1 flex flex-col min-w-0">

        {{-- Topbar --}}
        <div class="flex items-center gap-2 px-3 sm:px-4 h-14 bg-gray-900 border-b border-gray-800 shrink-0">
            <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-white transition shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <span class="font-semibold text-white shrink-0">Kasir POS</span>
            <span class="text-xs text-gray-500 ml-1 hidden sm:inline shrink-0">{{ now()->format('d M Y, H:i') }}</span>
            {{-- Offline indicator --}}
            <span id="offline-badge" class="hidden items-center gap-1 text-xs font-medium bg-amber-500/20 text-amber-400 border border-amber-500/30 px-2 py-0.5 rounded-full shrink-0">
                <span class="w-1.5 h-1.5 bg-amber-400 rounded-full animate-pulse"></span>
                Offline
            </span>

            {{-- Barcode search --}}
            <div class="flex-1 max-w-sm ml-2 sm:ml-4 relative">
                <input id="barcode-input" type="text" placeholder="Scan barcode atau cari..."
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-blue-500 pr-20">
                {{-- Camera scan button --}}
                <button id="btn-camera-scan" onclick="openCameraScanner()"
                    title="Scan barcode via kamera"
                    class="absolute right-8 top-1.5 w-7 h-7 flex items-center justify-center text-gray-400 hover:text-blue-400 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 9V6a1 1 0 011-1h3M3 15v3a1 1 0 001 1h3m11-4v3a1 1 0 01-1 1h-3m4-11h-3a1 1 0 00-1 1v3M9 3H6a1 1 0 00-1 1v3m0 6v3a1 1 0 001 1h3m6-10h3a1 1 0 011 1v3"/>
                    </svg>
                </button>
                <svg class="absolute right-3 top-2.5 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            {{-- Category filter (hidden on very small screens) --}}
            <div class="hidden sm:flex gap-1 overflow-x-auto" id="category-tabs">
                <button onclick="filterCategory('')" data-cat=""
                    class="cat-btn px-3 py-1.5 rounded-lg text-xs font-medium bg-blue-600 text-white whitespace-nowrap">
                    Semua
                </button>
                @foreach($products->pluck('category')->filter()->unique() as $cat)
                <button onclick="filterCategory('{{ $cat }}')" data-cat="{{ $cat }}"
                    class="cat-btn px-3 py-1.5 rounded-lg text-xs font-medium bg-gray-700 text-gray-300 hover:bg-gray-600 whitespace-nowrap">
                    {{ $cat }}
                </button>
                @endforeach
            </div>

            {{-- Cart toggle button (mobile only) --}}
            <button onclick="toggleCart()" id="cart-toggle-btn"
                class="sm:hidden relative shrink-0 w-9 h-9 bg-gray-800 rounded-xl flex items-center justify-center text-gray-300 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span id="cart-badge" class="hidden absolute -top-1 -right-1 w-4 h-4 bg-blue-500 rounded-full text-white text-xs flex items-center justify-center font-bold">0</span>
            </button>
        </div>

        {{-- Product Grid --}}
        <div class="flex-1 overflow-y-auto p-4">
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3" id="product-grid">
                @foreach($products as $product)
                @php $outOfStock = $product->total_stock <= 0; @endphp
                <div class="product-card bg-gray-800 rounded-2xl p-3 transition select-none relative
                     {{ $outOfStock ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer hover:bg-gray-700 hover:ring-2 hover:ring-blue-500' }}"
                     data-id="{{ $product->id }}"
                     data-name="{{ $product->name }}"
                     data-price="{{ $product->price_sell }}"
                     data-stock="{{ $product->total_stock }}"
                     data-sku="{{ $product->sku }}"
                     data-barcode="{{ $product->barcode }}"
                     data-category="{{ $product->category }}"
                     onclick="{{ $outOfStock ? 'showToast(\'Stok ' . addslashes($product->name) . ' habis\', \'warning\')' : 'addToCart(this)' }}">
                    <div class="w-full aspect-square bg-gray-700 rounded-xl mb-2 flex items-center justify-center overflow-hidden relative">
                        @if($product->image)
                            <img src="{{ $product->image }}" class="w-full h-full object-cover rounded-xl" alt="">
                        @else
                            <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        @endif
                        @if($outOfStock)
                        <div class="absolute inset-0 bg-black/60 rounded-xl flex items-center justify-center">
                            <span class="bg-red-600 text-white text-xs font-bold px-2 py-1 rounded-lg tracking-wide">HABIS</span>
                        </div>
                        @endif
                    </div>
                    <p class="text-xs font-medium text-white leading-tight line-clamp-2">{{ $product->name }}</p>
                    <p class="text-xs text-blue-400 font-semibold mt-1">Rp {{ number_format($product->price_sell, 0, ',', '.') }}</p>
                    <p class="text-xs mt-0.5 {{ $outOfStock ? 'text-red-400 font-semibold' : 'text-gray-500' }}">
                        Stok: {{ $product->total_stock }}
                    </p>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Right: Cart & Payment ───────────────────────────── --}}
    <div id="cart-panel" class="fixed inset-y-0 right-0 z-30 w-80 xl:w-96 bg-gray-900 border-l border-gray-800 flex flex-col shrink-0 translate-x-full sm:translate-x-0 sm:relative sm:inset-auto transition-transform duration-300">

        {{-- Cart Header --}}
        <div class="flex items-center justify-between px-4 h-14 border-b border-gray-800 shrink-0">
            <span class="font-semibold text-white">Keranjang</span>
            <button onclick="clearCart()" class="text-xs text-red-400 hover:text-red-300 transition">Kosongkan</button>
        </div>

        {{-- Customer --}}
        <div class="px-4 py-2 border-b border-gray-800 shrink-0">
            <select id="customer-select" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-blue-500">
                <option value="">Pelanggan umum</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}">{{ $c->name }} {{ $c->phone ? "({$c->phone})" : '' }}</option>
                @endforeach
            </select>
        </div>

        {{-- Cart Items --}}
        <div class="flex-1 overflow-y-auto px-4 py-2 space-y-2" id="cart-items">
            <p class="text-center text-gray-600 text-sm py-8" id="cart-empty">Belum ada item</p>
        </div>

        {{-- Totals --}}
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
            <div class="flex justify-between font-bold text-white text-base pt-1 border-t border-gray-700">
                <span>Total</span>
                <span id="total-display">Rp 0</span>
            </div>
        </div>

        {{-- Payment Method --}}
        <div class="px-4 pb-2 shrink-0">
            <div class="grid grid-cols-3 gap-2 mb-3">
                @foreach(['cash' => 'Tunai', 'transfer' => 'Transfer', 'qris' => 'QRIS'] as $val => $label)
                <button onclick="setPayment('{{ $val }}')" data-method="{{ $val }}"
                    class="pay-btn py-2 rounded-xl text-xs font-medium border border-gray-700 text-gray-400 hover:border-blue-500 hover:text-blue-400 transition">
                    {{ $label }}
                </button>
                @endforeach
            </div>

            {{-- Numpad for cash --}}
            <div id="numpad-section" class="mb-3">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs text-gray-500">Uang diterima</span>
                    <span id="change-display" class="text-xs text-green-400 font-medium"></span>
                </div>
                <input id="paid-input" type="text" value="0" readonly
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-right text-lg font-bold text-white mb-2 focus:outline-none">
                <div class="grid grid-cols-3 gap-1.5">
                    @foreach(['7','8','9','4','5','6','1','2','3','000','0','⌫'] as $k)
                    <button onclick="numpad('{{ $k }}')"
                        class="py-2.5 rounded-xl bg-gray-800 hover:bg-gray-700 text-sm font-medium text-white transition active:scale-95">
                        {{ $k }}
                    </button>
                    @endforeach
                </div>
                <div class="grid grid-cols-3 gap-1.5 mt-1.5">
                    @foreach(['Pas' => 'exact', '+50rb' => '50000', '+100rb' => '100000'] as $label => $val)
                    <button onclick="quickCash({{ $val === 'exact' ? 'null' : $val }})"
                        class="py-2 rounded-xl bg-gray-700 hover:bg-gray-600 text-xs font-medium text-gray-300 transition">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
            </div>

            <button onclick="processCheckout()"
                class="w-full py-3.5 bg-blue-600 hover:bg-blue-500 rounded-2xl font-semibold text-white transition text-sm active:scale-95">
                Proses Pembayaran
            </button>
        </div>
    </div>
</div>

{{-- Cart overlay (mobile) --}}
<div id="cart-overlay" class="fixed inset-0 z-20 bg-black/60 hidden sm:hidden" onclick="toggleCart()"></div>

{{-- Camera Barcode Scanner Modal --}}
<div id="camera-modal" class="fixed inset-0 bg-black/80 z-50 hidden items-center justify-center p-4">
    <div class="bg-gray-900 rounded-2xl w-full max-w-sm overflow-hidden shadow-2xl">
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-800">
            <p class="text-sm font-semibold text-white">Scan Barcode via Kamera</p>
            <button onclick="closeCameraScanner()" class="text-gray-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        {{-- Video preview --}}
        <div class="relative bg-black" style="aspect-ratio:4/3">
            <video id="camera-video" autoplay playsinline muted class="w-full h-full object-cover"></video>
            {{-- Scan overlay --}}
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="relative w-52 h-32">
                    {{-- Corner brackets --}}
                    <div class="absolute top-0 left-0 w-6 h-6 border-t-2 border-l-2 border-blue-400 rounded-tl"></div>
                    <div class="absolute top-0 right-0 w-6 h-6 border-t-2 border-r-2 border-blue-400 rounded-tr"></div>
                    <div class="absolute bottom-0 left-0 w-6 h-6 border-b-2 border-l-2 border-blue-400 rounded-bl"></div>
                    <div class="absolute bottom-0 right-0 w-6 h-6 border-b-2 border-r-2 border-blue-400 rounded-br"></div>
                    {{-- Scan line --}}
                    <div id="scan-line" class="absolute left-1 right-1 h-0.5 bg-blue-400/70 rounded" style="top:50%;animation:scanline 1.8s ease-in-out infinite"></div>
                </div>
            </div>
            {{-- Status --}}
            <div id="camera-status" class="absolute bottom-3 left-0 right-0 text-center">
                <span class="text-xs text-white/70 bg-black/50 px-3 py-1 rounded-full">Arahkan kamera ke barcode</span>
            </div>
        </div>
        {{-- Fallback: manual input --}}
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
    0%   { top: 10%; }
    50%  { top: 85%; }
    100% { top: 10%; }
}
</style>

{{-- Receipt Modal --}}
<div id="receipt-modal" class="fixed inset-0 bg-black/70 z-50 hidden items-center justify-center">
    <div class="bg-white text-gray-900 rounded-2xl w-80 p-6 shadow-2xl">
        <div class="text-center mb-4">
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h3 class="font-bold text-lg">Pembayaran Berhasil</h3>
            <p id="receipt-order" class="text-sm text-gray-500 mt-1"></p>
        </div>
        <div id="receipt-body" class="text-sm space-y-1 border-t border-b border-dashed border-gray-200 py-3 mb-4"></div>
        <div class="flex gap-2">
            <button onclick="printReceipt()" class="flex-1 py-2 border border-gray-300 rounded-xl text-sm font-medium hover:bg-gray-50 transition">Cetak</button>
            <button onclick="closeReceipt()" class="flex-1 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-500 transition">Transaksi Baru</button>
        </div>
    </div>
</div>

<script>
const products = @json($products);
let cart = [];
let paymentMethod = 'cash';
let lastReceipt = null;

// ── Toast ─────────────────────────────────────────────────────────────────
function showToast(message, type = 'success') {
    const colors = { success: '#16a34a', error: '#dc2626', warning: '#d97706', info: '#2563eb' };
    const icons  = { success: '✓', error: '✕', warning: '⚠', info: 'ℹ' };
    const toast = document.createElement('div');
    toast.style.cssText = `position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;align-items:center;gap:10px;padding:12px 18px;border-radius:14px;background:${colors[type]||colors.success};color:#fff;font-size:13px;font-weight:500;box-shadow:0 8px 24px rgba(0,0,0,.4);transition:all .3s;transform:translateY(12px);opacity:0;`;
    toast.innerHTML = `<span>${icons[type]||icons.success}</span><span>${message}</span>`;
    document.body.appendChild(toast);
    requestAnimationFrame(() => { toast.style.transform = 'translateY(0)'; toast.style.opacity = '1'; });
    setTimeout(() => {
        toast.style.transform = 'translateY(12px)'; toast.style.opacity = '0';
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
    const id    = parseInt(el.dataset.id);
    const name  = el.dataset.name;
    const price = parseFloat(el.dataset.price);
    const stock = parseInt(el.dataset.stock);

    const existing = cart.find(i => i.id === id);
    if (existing) {
        if (existing.qty >= stock) { showToast('Stok tidak cukup', 'warning'); return; }
        existing.qty++;
    } else {
        cart.push({ id, name, price, stock, qty: 1 });
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
    if (item.qty <= 0) { removeFromCart(id); return; }
    if (item.qty > item.stock) { item.qty = item.stock; }
    renderCart();
}

function clearCart() {
    cart = [];
    renderCart();
}

function renderCart() {
    const container = document.getElementById('cart-items');
    const empty     = document.getElementById('cart-empty');

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
    const tax      = parseFloat(document.getElementById('tax-input').value) || 0;
    const total    = Math.max(0, subtotal - discount + tax);

    document.getElementById('subtotal-display').textContent = formatRp(subtotal);
    document.getElementById('total-display').textContent    = formatRp(total);

    updateChange();
}

function updateChange() {
    const total = getTotal();
    const paid  = parseFloat(document.getElementById('paid-input').value.replace(/\./g, '')) || 0;
    const change = paid - total;
    const el = document.getElementById('change-display');
    if (paymentMethod === 'cash' && paid > 0) {
        el.textContent = change >= 0 ? `Kembalian: ${formatRp(change)}` : `Kurang: ${formatRp(-change)}`;
        el.className = `text-xs font-medium ${change >= 0 ? 'text-green-400' : 'text-red-400'}`;
    } else {
        el.textContent = '';
    }
}

function getTotal() {
    const subtotal = cart.reduce((s, i) => s + i.price * i.qty, 0);
    const discount = parseFloat(document.getElementById('discount-input').value) || 0;
    const tax      = parseFloat(document.getElementById('tax-input').value) || 0;
    return Math.max(0, subtotal - discount + tax);
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

function setPayment(method) {
    paymentMethod = method;
    document.querySelectorAll('.pay-btn').forEach(b => {
        b.classList.toggle('border-blue-500', b.dataset.method === method);
        b.classList.toggle('text-blue-400', b.dataset.method === method);
        b.classList.toggle('border-gray-700', b.dataset.method !== method);
        b.classList.toggle('text-gray-400', b.dataset.method !== method);
    });
    document.getElementById('numpad-section').style.display = method === 'cash' ? 'block' : 'none';
}

// ── Checkout ──────────────────────────────────────────────────────────────
// (implementasi lengkap dengan offline support ada di bawah)

function showReceipt(data) {
    document.getElementById('receipt-order').textContent = '#' + data.order_number;
    const body = document.getElementById('receipt-body');
    const items = lastReceipt.items.map(i =>
        `<div class="flex justify-between"><span>${i.name} x${i.qty}</span><span>${formatRp(i.price * i.qty)}</span></div>`
    ).join('');
    body.innerHTML = items +
        `<div class="flex justify-between mt-2 font-bold"><span>Total</span><span>${formatRp(data.total)}</span></div>` +
        (paymentMethod === 'cash' ? `<div class="flex justify-between text-green-600"><span>Kembalian</span><span>${formatRp(data.change)}</span></div>` : '');

    document.getElementById('receipt-modal').classList.remove('hidden');
    document.getElementById('receipt-modal').classList.add('flex');
}

function closeReceipt() {
    document.getElementById('receipt-modal').classList.add('hidden');
    document.getElementById('receipt-modal').classList.remove('flex');
    clearCart();
    document.getElementById('paid-input').value = '0';
    document.getElementById('discount-input').value = '0';
    document.getElementById('tax-input').value = '0';
}

function printReceipt() {
    window.print();
}

// ── Search & Filter ───────────────────────────────────────────────────────

document.getElementById('barcode-input').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.product-card').forEach(card => {
        const match = card.dataset.name.toLowerCase().includes(q) ||
                      card.dataset.sku.toLowerCase().includes(q) ||
                      card.dataset.barcode.toLowerCase().includes(q);
        card.style.display = match ? '' : 'none';
    });
});

// Barcode scanner: Enter key triggers add
document.getElementById('barcode-input').addEventListener('keydown', async function(e) {
    if (e.key !== 'Enter') return;
    const barcode = this.value.trim();
    if (!barcode) return;

    const res  = await fetch(`{{ route('pos.barcode') }}?barcode=${encodeURIComponent(barcode)}`);
    if (res.ok) {
        const p = await res.json();
        const card = document.querySelector(`.product-card[data-id="${p.id}"]`);
        if (card) addToCart(card);
    }
    this.value = '';
    this.dispatchEvent(new Event('input'));
});

function filterCategory(cat) {
    document.querySelectorAll('.cat-btn').forEach(b => {
        const active = b.dataset.cat === cat;
        b.className = `cat-btn px-3 py-1.5 rounded-lg text-xs font-medium whitespace-nowrap transition ${active ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600'}`;
    });
    document.querySelectorAll('.product-card').forEach(card => {
        card.style.display = (!cat || card.dataset.category === cat) ? '' : 'none';
    });
}

// ── Keyboard shortcuts ────────────────────────────────────────────────────
document.addEventListener('keydown', e => {
    if (e.key === 'F2') document.getElementById('barcode-input').focus();
    if (e.key === 'F12') processCheckout();
    if (e.key === 'Escape') { closeReceipt(); closeCameraScanner(); }
});

// ── Helpers ───────────────────────────────────────────────────────────────
function formatRp(n) {
    return 'Rp ' + Math.round(n).toLocaleString('id-ID');
}

// ── Offline Queue (IndexedDB) ─────────────────────────────────────────────
const DB_NAME    = 'qalcuity-pos';
const DB_VERSION = 1;
let posDb = null;

async function getDb() {
    if (posDb) return posDb;
    return new Promise((resolve, reject) => {
        const req = indexedDB.open(DB_NAME, DB_VERSION);
        req.onupgradeneeded = e => {
            const db = e.target.result;
            if (!db.objectStoreNames.contains('pos_queue')) {
                const store = db.createObjectStore('pos_queue', { keyPath: 'id', autoIncrement: true });
                store.createIndex('queued_at', 'queued_at');
            }
        };
        req.onsuccess = e => { posDb = e.target.result; resolve(posDb); };
        req.onerror   = e => reject(e.target.error);
    });
}

async function queueTransaction(payload) {
    const db = await getDb();
    return new Promise((resolve, reject) => {
        const tx    = db.transaction('pos_queue', 'readwrite');
        const store = tx.objectStore('pos_queue');
        const req   = store.add({ payload, queued_at: Date.now(), csrf: document.querySelector('meta[name="csrf-token"]').content });
        req.onsuccess = () => resolve(req.result);
        req.onerror   = e => reject(e.target.error);
    });
}

async function getPendingCount() {
    try {
        const db = await getDb();
        return new Promise(resolve => {
            const tx  = db.transaction('pos_queue', 'readonly');
            const req = tx.objectStore('pos_queue').count();
            req.onsuccess = () => resolve(req.result);
            req.onerror   = () => resolve(0);
        });
    } catch { return 0; }
}

async function flushQueue() {
    const db = await getDb();
    const tx = db.transaction('pos_queue', 'readwrite');
    const store = tx.objectStore('pos_queue');
    const items = await new Promise(r => { const q = store.getAll(); q.onsuccess = () => r(q.result); });

    let synced = 0;
    for (const item of items) {
        try {
            const res = await fetch('{{ route("pos.checkout") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': item.csrf },
                body: JSON.stringify(item.payload),
            });
            if (res.ok) {
                const delTx = db.transaction('pos_queue', 'readwrite');
                delTx.objectStore('pos_queue').delete(item.id);
                synced++;
            }
        } catch { /* will retry */ }
    }
    if (synced > 0) showToast(`${synced} transaksi offline berhasil disinkronisasi`, 'success');
}

// ── Offline / Online detection ────────────────────────────────────────────
function updateOnlineStatus() {
    const badge = document.getElementById('offline-badge');
    if (navigator.onLine) {
        badge.classList.add('hidden');
        badge.classList.remove('flex');
        flushQueue(); // try to sync pending transactions
    } else {
        badge.classList.remove('hidden');
        badge.classList.add('flex');
    }
}
window.addEventListener('online',  updateOnlineStatus);
window.addEventListener('offline', updateOnlineStatus);
updateOnlineStatus();

// Listen for SW sync success messages
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.addEventListener('message', e => {
        if (e.data?.type === 'POS_SYNC_SUCCESS') {
            showToast('Transaksi offline berhasil disinkronisasi', 'success');
        }
    });
}

// ── Checkout (dengan offline support) ────────────────────────────────────
async function processCheckout() {
    if (cart.length === 0) { showToast('Keranjang kosong', 'warning'); return; }

    const total    = getTotal();
    const paid     = parseFloat(document.getElementById('paid-input').value.replace(/\./g, '')) || 0;
    const discount = parseFloat(document.getElementById('discount-input').value) || 0;
    const tax      = parseFloat(document.getElementById('tax-input').value) || 0;

    if (paymentMethod === 'cash' && paid < total) {
        showToast('Uang yang diterima kurang dari total', 'error'); return;
    }

    const payload = {
        items: cart.map(i => ({ id: i.id, qty: i.qty, price: i.price })),
        payment_method: paymentMethod,
        paid_amount: paymentMethod === 'cash' ? paid : total,
        discount,
        tax,
        customer_id: document.getElementById('customer-select').value || null,
    };

    // If offline, queue the transaction
    if (!navigator.onLine) {
        await queueTransaction(payload);
        const count = await getPendingCount();
        showToast(`Offline: transaksi disimpan (${count} antrian)`, 'warning');

        // Show a local receipt
        lastReceipt = { items: [...cart], discount, tax, paid: payload.paid_amount };
        document.getElementById('receipt-order').textContent = '#OFFLINE-' + Date.now().toString().slice(-6);
        const body = document.getElementById('receipt-body');
        body.innerHTML = cart.map(i =>
            `<div class="flex justify-between"><span>${i.name} x${i.qty}</span><span>${formatRp(i.price * i.qty)}</span></div>`
        ).join('') + `<div class="flex justify-between mt-2 font-bold"><span>Total</span><span>${formatRp(total)}</span></div>
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
        const res  = await fetch('{{ route("pos.checkout") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify(payload),
        });
        const data = await res.json();

        if (data.status === 'success') {
            lastReceipt = { ...data, items: [...cart], discount, tax, paid: payload.paid_amount };
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
            barcodeDetector = new BarcodeDetector({ formats });
        } catch {
            barcodeDetector = new BarcodeDetector({ formats: ['ean_13', 'ean_8', 'code_128', 'code_39', 'qr_code', 'upc_a', 'upc_e'] });
        }
    }

    // Start camera
    try {
        cameraStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } }
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
        } catch { /* continue scanning */ }
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

    // Try to find product in current DOM first (fast path)
    const card = document.querySelector(`.product-card[data-barcode="${code}"]`)
               || document.querySelector(`.product-card[data-sku="${code}"]`);

    if (card) {
        closeCameraScanner();
        addToCart(card);
        showToast('Produk ditambahkan: ' + card.dataset.name, 'success');
        return;
    }

    // Fallback: API lookup
    try {
        const res = await fetch(`{{ route('pos.barcode') }}?barcode=${encodeURIComponent(code)}`);
        if (res.ok) {
            const p = await res.json();
            const apiCard = document.querySelector(`.product-card[data-id="${p.id}"]`);
            if (apiCard) {
                closeCameraScanner();
                addToCart(apiCard);
                showToast('Produk ditambahkan: ' + p.name, 'success');
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
    if (window._zxingReader) { window._zxingReader.reset(); window._zxingReader = null; }
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
    const code  = input.value.trim();
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
        navigator.serviceWorker.register('/sw.js', { scope: '/' })
            .then(reg => {
                // Check for updates every 60s
                setInterval(() => reg.update(), 60_000);
            })
            .catch(err => console.warn('SW registration failed:', err));
    });
}

// Init
setPayment('cash');
</script>
</body>
</html>
