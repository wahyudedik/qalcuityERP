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
</head>
<body class="h-full font-[Inter,sans-serif] bg-gradient-to-br from-blue-50 via-white to-indigo-50 flex items-center justify-center p-4">

<div class="w-full max-w-2xl">

    {{-- Header --}}
    <div class="text-center mb-8">
        <img src="/logo.png" alt="Qalcuity" class="h-10 mx-auto mb-4 brightness-0">
        <h1 class="text-2xl font-bold text-gray-900">Selamat datang di Qalcuity ERP! 👋</h1>
        <p class="text-gray-500 mt-2">Isi informasi bisnis Anda untuk memulai. Hanya butuh 2 menit.</p>
    </div>

    {{-- Step indicator --}}
    <div class="flex items-center justify-center gap-2 mb-8" id="step-indicator">
        @foreach([1 => 'Bisnis', 2 => 'Gudang & Produk', 3 => 'Siap!'] as $n => $label)
        <div class="flex items-center gap-2">
            <div class="step-dot w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold transition-all
                {{ $n === 1 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-500' }}" data-step="{{ $n }}">
                {{ $n }}
            </div>
            <span class="text-sm hidden sm:block {{ $n === 1 ? 'text-blue-600 font-medium' : 'text-gray-400' }}" data-step-label="{{ $n }}">{{ $label }}</span>
            @if($n < 3)<div class="w-8 h-px bg-gray-200 mx-1"></div>@endif
        </div>
        @endforeach
    </div>

    <form method="POST" action="{{ route('onboarding.complete') }}" id="onboarding-form">
        @csrf

        {{-- ── Step 1: Info Bisnis ── --}}
        <div class="step-panel" data-panel="1">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-5">
                <h2 class="font-semibold text-gray-900 text-lg">Informasi Bisnis</h2>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Bisnis / Perusahaan <span class="text-red-500">*</span></label>
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
                                <span class="text-lg">{{ $type['icon'] }}</span>
                                {{ $type['label'] }}
                            </div>
                        </label>
                        @endforeach
                    </div>
                    <input type="hidden" name="business_type" id="business_type_hidden" value="{{ old('business_type', $tenant->business_type) }}">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">No. Telepon / WhatsApp</label>
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
                        placeholder="Contoh: Toko kelontong yang menjual kebutuhan sehari-hari">{{ old('business_description', $tenant->business_description) }}</textarea>
                </div>
            </div>

            <div class="flex justify-between mt-4">
                <a href="{{ route('onboarding.skip') }}" class="text-sm text-gray-400 hover:text-gray-600 transition py-2">Lewati setup</a>
                <button type="button" onclick="nextStep(2)" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition">
                    Lanjut →
                </button>
            </div>
        </div>

        {{-- ── Step 2: Gudang & Produk ── --}}
        <div class="step-panel hidden" data-panel="2">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-5">
                <h2 class="font-semibold text-gray-900 text-lg">Gudang & Produk Awal</h2>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Gudang Utama <span class="text-red-500">*</span></label>
                    <input type="text" name="warehouse_name" value="{{ old('warehouse_name', 'Gudang Utama') }}" required
                        class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Contoh: Gudang Utama">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Produk Awal <span class="text-gray-400 font-normal">(opsional, maks. 5)</span></label>
                    <div id="product-list" class="space-y-2">
                        <div class="product-row flex gap-2">
                            <input type="text" name="products[0][name]" placeholder="Nama produk"
                                class="flex-1 rounded-xl border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <input type="number" name="products[0][price]" placeholder="Harga jual" min="0"
                                class="w-32 rounded-xl border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Kategori Pengeluaran <span class="text-gray-400 font-normal">(pisahkan dengan koma)</span></label>
                    <input type="text" name="expense_categories" value="{{ old('expense_categories', 'Bahan Baku, Operasional, Gaji Karyawan') }}"
                        class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Bahan Baku, Operasional, Gaji Karyawan">
                    <p class="text-xs text-gray-400 mt-1">Kategori ini digunakan untuk mencatat pengeluaran bisnis Anda.</p>
                </div>

                {{-- Industry preset quick-fill --}}
                <div class="bg-blue-50 rounded-xl p-4">
                    <p class="text-sm font-medium text-blue-700 mb-2">💡 Isi otomatis berdasarkan jenis bisnis:</p>
                    <div class="flex flex-wrap gap-2">
                        @php
                        $presets = [
                            'fnb'          => ['label' => 'F&B / Kuliner',   'warehouse' => 'Dapur Utama',   'cats' => 'Bahan Baku, Gas & Listrik, Gaji Karyawan, Sewa Tempat'],
                            'retail'       => ['label' => 'Retail / Toko',   'warehouse' => 'Toko Utama',    'cats' => 'Pembelian Barang, Gaji Karyawan, Sewa Toko, Listrik & Air'],
                            'manufacture'  => ['label' => 'Manufaktur',      'warehouse' => 'Gudang Bahan Baku', 'cats' => 'Bahan Baku, Upah Produksi, Overhead Pabrik, Listrik Mesin'],
                            'distributor'  => ['label' => 'Distributor',     'warehouse' => 'Gudang Pusat',  'cats' => 'Pembelian Barang, Ongkos Kirim, Gaji Driver, Sewa Gudang'],
                            'jasa'         => ['label' => 'Jasa',            'warehouse' => 'Kantor Utama',  'cats' => 'Gaji Karyawan, Sewa Kantor, Listrik & Internet, Transportasi'],
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
                <button type="button" onclick="nextStep(3)" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition">
                    Lanjut →
                </button>
            </div>
        </div>

        {{-- ── Step 3: Konfirmasi ── --}}
        <div class="step-panel hidden" data-panel="3">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 text-center space-y-4">
                <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h2 class="font-bold text-gray-900 text-xl">Siap untuk memulai!</h2>
                <p class="text-gray-500 text-sm">Semua data sudah diisi. Klik tombol di bawah untuk menyelesaikan setup dan masuk ke dashboard.</p>

                <div class="bg-gray-50 rounded-xl p-4 text-left space-y-2 text-sm">
                    <div class="flex items-center gap-2 text-gray-600">
                        <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Profil bisnis dikonfigurasi
                    </div>
                    <div class="flex items-center gap-2 text-gray-600">
                        <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Gudang utama dibuat
                    </div>
                    <div class="flex items-center gap-2 text-gray-600">
                        <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Produk & kategori pengeluaran siap
                    </div>
                    <div class="flex items-center gap-2 text-gray-600">
                        <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        Qalcuity AI siap membantu Anda
                    </div>
                </div>
            </div>

            <div class="flex justify-between mt-4">
                <button type="button" onclick="nextStep(2)" class="text-sm text-gray-500 hover:text-gray-700 transition py-2">← Kembali</button>
                <button type="submit" class="px-8 py-2.5 rounded-xl bg-green-600 hover:bg-green-700 text-white text-sm font-semibold transition">
                    Mulai Gunakan ERP 🚀
                </button>
            </div>
        </div>

    </form>
</div>

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

@verbatim
function nextStep(n) {
    document.querySelectorAll('.step-panel').forEach(function(p) { p.classList.add('hidden'); });
    document.querySelector('[data-panel="' + n + '"]').classList.remove('hidden');

    document.querySelectorAll('.step-dot').forEach(function(d) {
        const s = parseInt(d.dataset.step);
        d.className = d.className.replace(/bg-\w+-\d+|text-\w+-\d+/g, '');
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

    document.querySelectorAll('[data-step-label]').forEach(function(l) {
        const s = parseInt(l.dataset.stepLabel);
        l.className = l.className.replace(/text-\w+-\d+|font-medium/g, '');
        l.classList.add(s === n ? 'text-blue-600' : 'text-gray-400');
        if (s === n) l.classList.add('font-medium');
    });
}

// Business type radio cards
document.querySelectorAll('.business-type-card input[type=radio]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.business-type-card > div').forEach(function(d) {
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
        ' class="w-32 rounded-xl border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">' +
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
@endverbatim
</script>
</body>
</html>
