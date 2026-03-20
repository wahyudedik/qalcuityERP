<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kasir POS — Qalcuity</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" href="/favicon.png">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gray-950 font-[Inter,sans-serif] text-white overflow-hidden">

<div class="flex h-full" id="pos-app">

    {{-- ── Left: Product Grid ─────────────────────────────── --}}
    <div class="flex-1 flex flex-col min-w-0">

        {{-- Topbar --}}
        <div class="flex items-center gap-3 px-4 h-14 bg-gray-900 border-b border-gray-800 shrink-0">
            <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <span class="font-semibold text-white">Kasir POS</span>
            <span class="text-xs text-gray-500 ml-1">{{ now()->format('d M Y, H:i') }}</span>

            {{-- Barcode search --}}
            <div class="flex-1 max-w-sm ml-4 relative">
                <input id="barcode-input" type="text" placeholder="Scan barcode atau cari produk..."
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-blue-500 pr-10">
                <svg class="absolute right-3 top-2.5 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            {{-- Category filter --}}
            <div class="flex gap-1 overflow-x-auto" id="category-tabs">
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
        </div>

        {{-- Product Grid --}}
        <div class="flex-1 overflow-y-auto p-4">
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3" id="product-grid">
                @foreach($products as $product)
                <div class="product-card bg-gray-800 rounded-2xl p-3 cursor-pointer hover:bg-gray-700 hover:ring-2 hover:ring-blue-500 transition select-none"
                     data-id="{{ $product->id }}"
                     data-name="{{ $product->name }}"
                     data-price="{{ $product->price_sell }}"
                     data-stock="{{ $product->total_stock }}"
                     data-sku="{{ $product->sku }}"
                     data-barcode="{{ $product->barcode }}"
                     data-category="{{ $product->category }}"
                     onclick="addToCart(this)">
                    <div class="w-full aspect-square bg-gray-700 rounded-xl mb-2 flex items-center justify-center overflow-hidden">
                        @if($product->image)
                            <img src="{{ $product->image }}" class="w-full h-full object-cover rounded-xl" alt="">
                        @else
                            <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        @endif
                    </div>
                    <p class="text-xs font-medium text-white leading-tight line-clamp-2">{{ $product->name }}</p>
                    <p class="text-xs text-blue-400 font-semibold mt-1">Rp {{ number_format($product->price_sell, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Stok: {{ $product->total_stock }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Right: Cart & Payment ───────────────────────────── --}}
    <div class="w-80 xl:w-96 bg-gray-900 border-l border-gray-800 flex flex-col shrink-0">

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
                <div class="grid grid-cols-2 gap-1.5 mt-1.5">
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

// ── Cart ──────────────────────────────────────────────────────────────────

function addToCart(el) {
    const id    = parseInt(el.dataset.id);
    const name  = el.dataset.name;
    const price = parseFloat(el.dataset.price);
    const stock = parseInt(el.dataset.stock);

    const existing = cart.find(i => i.id === id);
    if (existing) {
        if (existing.qty >= stock) { alert('Stok tidak cukup'); return; }
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

async function processCheckout() {
    if (cart.length === 0) { alert('Keranjang kosong'); return; }

    const total    = getTotal();
    const paid     = parseFloat(document.getElementById('paid-input').value.replace(/\./g, '')) || 0;
    const discount = parseFloat(document.getElementById('discount-input').value) || 0;
    const tax      = parseFloat(document.getElementById('tax-input').value) || 0;

    if (paymentMethod === 'cash' && paid < total) {
        alert('Uang yang diterima kurang dari total'); return;
    }

    const payload = {
        items: cart.map(i => ({ id: i.id, qty: i.qty, price: i.price })),
        payment_method: paymentMethod,
        paid_amount: paymentMethod === 'cash' ? paid : total,
        discount,
        tax,
        customer_id: document.getElementById('customer-select').value || null,
        _token: document.querySelector('meta[name="csrf-token"]').content,
    };

    try {
        const res  = await fetch('{{ route("pos.checkout") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': payload._token },
            body: JSON.stringify(payload),
        });
        const data = await res.json();

        if (data.status === 'success') {
            lastReceipt = { ...data, items: [...cart], discount, tax, paid: payload.paid_amount };
            showReceipt(data);
        } else {
            alert('Gagal memproses transaksi');
        }
    } catch (e) {
        alert('Terjadi kesalahan: ' + e.message);
    }
}

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
    if (e.key === 'Escape') closeReceipt();
});

// ── Helpers ───────────────────────────────────────────────────────────────
function formatRp(n) {
    return 'Rp ' + Math.round(n).toLocaleString('id-ID');
}

// Init
setPayment('cash');
</script>
</body>
</html>
