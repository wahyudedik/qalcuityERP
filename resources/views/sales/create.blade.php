<x-app-layout>
    <x-slot name="header">Buat Sales Order</x-slot>

    <div class="max-w-4xl mx-auto">
        <form method="POST" action="{{ route('sales.store') }}" id="so-form">
            @csrf

            @if($errors->any())
                <div class="mb-4 bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-4 py-3 text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                    </ul>
                </div>
            @endif

            <div class="space-y-5">

                {{-- Header Info --}}
                <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Informasi Order</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Customer <span class="text-red-400">*</span></label>
                            <select name="customer_id" required
                                class="w-full bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Pilih customer...</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Gudang Sumber <span class="text-red-400">*</span></label>
                            <select name="warehouse_id" required
                                class="w-full bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Pilih gudang...</option>
                                @foreach($warehouses as $w)
                                    <option value="{{ $w->id }}" {{ old('warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Tanggal Order <span class="text-red-400">*</span></label>
                            <input type="date" name="date" value="{{ old('date', today()->format('Y-m-d')) }}" required
                                class="w-full bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Tanggal Pengiriman</label>
                            <input type="date" name="delivery_date" value="{{ old('delivery_date') }}"
                                class="w-full bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Tipe Pembayaran <span class="text-red-400">*</span></label>
                            <select name="payment_type" id="payment_type" required
                                class="w-full bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="cash" {{ old('payment_type') === 'cash' ? 'selected' : '' }}>Tunai</option>
                                <option value="credit" {{ old('payment_type') === 'credit' ? 'selected' : '' }}>Kredit</option>
                            </select>
                        </div>
                        <div id="due_date_wrap" class="{{ old('payment_type') === 'credit' ? '' : 'hidden' }}">
                            <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Jatuh Tempo</label>
                            <input type="date" name="due_date" value="{{ old('due_date') }}"
                                class="w-full bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Tarif Pajak</label>
                            <select name="tax_rate_id"
                                class="w-full bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Tanpa Pajak</option>
                                @foreach($taxRates as $t)
                                    <option value="{{ $t->id }}" {{ old('tax_rate_id') == $t->id ? 'selected' : '' }}>{{ $t->name }} ({{ $t->rate }}%)</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Diskon Global (Rp)</label>
                            <input type="number" name="discount" value="{{ old('discount', 0) }}" min="0" step="1000"
                                class="w-full bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Alamat Pengiriman</label>
                            <input type="text" name="shipping_address" value="{{ old('shipping_address') }}" placeholder="Opsional"
                                class="w-full bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Catatan</label>
                            <textarea name="notes" rows="2" placeholder="Opsional"
                                class="w-full bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Items --}}
                <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-semibold text-gray-900 dark:text-white">Item Produk</h2>
                        <button type="button" id="add-item"
                            class="text-sm px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition">+ Tambah Item</button>
                    </div>

                    <div id="items-container" class="space-y-3">
                        <div class="item-row grid grid-cols-12 gap-2 items-end">
                            <div class="col-span-5">
                                <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Produk</label>
                                <select name="items[0][product_id]" required
                                    class="product-select w-full bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Pilih produk...</option>
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}" data-price="{{ $p->price_sell }}">{{ $p->name }} ({{ $p->unit }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Qty</label>
                                <input type="number" name="items[0][quantity]" min="0.001" step="0.001" value="1" required
                                    class="qty-input w-full bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Harga</label>
                                <input type="number" name="items[0][price]" min="0" step="100" value="0" required
                                    class="price-input w-full bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Subtotal</label>
                                <div class="row-total text-sm font-medium text-gray-900 dark:text-white py-2">Rp 0</div>
                            </div>
                            <div class="col-span-1 flex justify-end">
                                <button type="button" class="remove-item text-red-400 hover:text-red-300 transition mt-5">✕</button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-white/10 flex justify-end">
                        <div class="text-right space-y-1">
                            <p class="text-sm text-gray-500 dark:text-slate-400">Subtotal: <span id="grand-total" class="font-semibold text-gray-900 dark:text-white">Rp 0</span></p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('sales.index') }}" class="px-5 py-2 bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-white rounded-xl text-sm hover:bg-gray-200 dark:hover:bg-white/20 transition">Batal</a>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">Buat Sales Order</button>
                </div>

            </div>
        </form>
    </div>

    <script>
    (function() {
        let idx = 1;
        const products = @json($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'price' => $p->price_sell, 'unit' => $p->unit]));

        const AI_PRICE_URL = '{{ route('sales.ai.price-suggest') }}';
        const AI_DESC_URL  = '{{ route('sales.ai.item-description') }}';
        const CSRF         = '{{ csrf_token() }}';

        function formatRp(n) {
            return 'Rp ' + Math.round(n).toLocaleString('id-ID');
        }

        function getCustomerId() {
            return document.querySelector('[name="customer_id"]')?.value || '';
        }

        function recalcRow(row) {
            const qty   = parseFloat(row.querySelector('.qty-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            row.querySelector('.row-total').textContent = formatRp(qty * price);
            recalcTotal();
        }

        function recalcTotal() {
            let total = 0;
            document.querySelectorAll('.item-row').forEach(row => {
                const qty   = parseFloat(row.querySelector('.qty-input').value) || 0;
                const price = parseFloat(row.querySelector('.price-input').value) || 0;
                total += qty * price;
            });
            document.getElementById('grand-total').textContent = formatRp(total);
        }

        // ── AI: Fetch price suggestion ──────────────────────────
        async function fetchPriceSuggestion(row, productId, qty) {
            const customerId = getCustomerId();
            if (!customerId || !productId) return;

            const badge = row.querySelector('.ai-price-badge');
            if (badge) badge.remove();

            try {
                const res  = await fetch(`${AI_PRICE_URL}?customer_id=${customerId}&product_id=${productId}&qty=${qty}`);
                const data = await res.json();

                if (!data.suggested_price) return;

                const priceInput = row.querySelector('.price-input');
                const container  = priceInput.parentElement;

                // Buat badge saran harga
                const confidenceColor = { high: 'text-green-400', medium: 'text-yellow-400', low: 'text-gray-400' };
                const confidenceLabel = { high: '✓ Tinggi', medium: '~ Sedang', low: '? Rendah' };
                const color = confidenceColor[data.confidence] || 'text-gray-400';
                const label = confidenceLabel[data.confidence] || '';

                const el = document.createElement('div');
                el.className = 'ai-price-badge mt-1 p-2 rounded-lg bg-indigo-500/10 border border-indigo-500/20 text-xs';
                el.innerHTML = `
                    <div class="flex items-center justify-between gap-1 mb-0.5">
                        <span class="text-indigo-400 font-medium">✦ AI Saran: ${formatRp(data.suggested_price)}</span>
                        <span class="${color} text-[10px]">${label}</span>
                    </div>
                    <p class="text-gray-400 leading-tight">${data.basis}</p>
                    <button type="button" class="apply-price mt-1 text-indigo-400 hover:text-indigo-300 font-medium"
                        data-price="${data.suggested_price}">Gunakan harga ini →</button>
                `;
                container.appendChild(el);

                el.querySelector('.apply-price').addEventListener('click', function() {
                    priceInput.value = this.dataset.price;
                    recalcRow(row);
                    el.remove();
                });
            } catch (e) {
                // silent fail
            }
        }

        // ── AI: Fetch item description ──────────────────────────
        async function fetchItemDescription(row, productId) {
            if (!productId) return;

            try {
                const res  = await fetch(`${AI_DESC_URL}?product_id=${productId}`);
                const data = await res.json();

                if (!data.description) return;

                // Cek apakah sudah ada notes input di row, kalau tidak ada skip
                const notesInput = row.querySelector('.item-notes');
                if (notesInput && !notesInput.value) {
                    notesInput.value = data.description;
                    notesInput.classList.add('ring-1', 'ring-indigo-500/40');
                    setTimeout(() => notesInput.classList.remove('ring-1', 'ring-indigo-500/40'), 2000);
                }
            } catch (e) {
                // silent fail
            }
        }

        function buildOptions() {
            return `<option value="">Pilih produk...</option>` +
                products.map(p => `<option value="${p.id}" data-price="${p.price}">${p.name} (${p.unit})</option>`).join('');
        }

        function addRow() {
            const tpl = `
            <div class="item-row grid grid-cols-12 gap-2 items-end">
                <div class="col-span-5">
                    <select name="items[${idx}][product_id]" required
                        class="product-select w-full bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        ${buildOptions()}
                    </select>
                </div>
                <div class="col-span-2">
                    <input type="number" name="items[${idx}][quantity]" min="0.001" step="0.001" value="1" required
                        class="qty-input w-full bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="col-span-2">
                    <input type="number" name="items[${idx}][price]" min="0" step="100" value="0" required
                        class="price-input w-full bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="col-span-2">
                    <div class="row-total text-sm font-medium text-gray-900 dark:text-white py-2">Rp 0</div>
                </div>
                <div class="col-span-1 flex justify-end">
                    <button type="button" class="remove-item text-red-400 hover:text-red-300 transition">✕</button>
                </div>
            </div>`;
            const container = document.getElementById('items-container');
            container.insertAdjacentHTML('beforeend', tpl);
            bindRow(container.lastElementChild);
            idx++;
        }

        function bindRow(row) {
            const productSelect = row.querySelector('.product-select');
            const qtyInput      = row.querySelector('.qty-input');
            const priceInput    = row.querySelector('.price-input');

            productSelect.addEventListener('change', function() {
                const opt = this.selectedOptions[0];
                if (opt && opt.dataset.price) {
                    priceInput.value = opt.dataset.price;
                    recalcRow(row);
                }
                const productId = this.value;
                const qty = parseFloat(qtyInput.value) || 1;

                // Remove old AI badge
                row.querySelector('.ai-price-badge')?.remove();

                if (productId) {
                    fetchPriceSuggestion(row, productId, qty);
                    fetchItemDescription(row, productId);
                }
            });

            qtyInput.addEventListener('change', function() {
                recalcRow(row);
                const productId  = productSelect.value;
                const qty        = parseFloat(this.value) || 1;
                if (productId) {
                    row.querySelector('.ai-price-badge')?.remove();
                    fetchPriceSuggestion(row, productId, qty);
                }
            });

            priceInput.addEventListener('input', () => recalcRow(row));

            row.querySelector('.remove-item').addEventListener('click', function() {
                if (document.querySelectorAll('.item-row').length > 1) {
                    row.remove();
                    recalcTotal();
                }
            });
        }

        // Bind existing rows
        document.querySelectorAll('.item-row').forEach(bindRow);
        document.getElementById('add-item').addEventListener('click', addRow);

        // Re-fetch price suggestions when customer changes
        document.querySelector('[name="customer_id"]').addEventListener('change', function() {
            document.querySelectorAll('.item-row').forEach(row => {
                const productId = row.querySelector('.product-select')?.value;
                const qty       = parseFloat(row.querySelector('.qty-input')?.value) || 1;
                row.querySelector('.ai-price-badge')?.remove();
                if (productId) fetchPriceSuggestion(row, productId, qty);
            });
        });

        // Show/hide due date
        document.getElementById('payment_type').addEventListener('change', function() {
            document.getElementById('due_date_wrap').classList.toggle('hidden', this.value !== 'credit');
        });
    })();
    </script>
</x-app-layout>
