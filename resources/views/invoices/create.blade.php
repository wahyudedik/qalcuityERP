<x-app-layout>
    <x-slot name="header">Buat Invoice Baru</x-slot>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('invoices.store') }}" class="space-y-5">
            @csrf

            <div class="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 p-6 space-y-5">

                {{-- Customer --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1.5">Customer <span class="text-red-500">*</span></label>
                    <select name="customer_id" required class="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-800 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih Customer --</option>
                        @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}{{ $c->company ? ' — ' . $c->company : '' }}
                        </option>
                        @endforeach
                    </select>
                    @error('customer_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                {{-- Sales Order (optional) --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1.5">Sales Order <span class="text-gray-400 font-normal">(opsional)</span></label>
                    <select name="sales_order_id" id="sales_order_id" class="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-800 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Tanpa Sales Order --</option>
                        @foreach($orders as $o)
                        <option value="{{ $o->id }}" data-total="{{ $o->total }}" {{ old('sales_order_id') == $o->id ? 'selected' : '' }}>
                            {{ $o->number }} — {{ $o->customer?->name ?? 'Walk-in' }} — Rp {{ number_format($o->total, 0, ',', '.') }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Total --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1.5">Total Tagihan (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="total_amount" id="total_amount" value="{{ old('total_amount') }}" min="0" step="1" required
                        class="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="0">
                    @error('total_amount')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                {{-- Due date --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1.5">Jatuh Tempo <span class="text-red-500">*</span></label>
                    <input type="date" name="due_date" value="{{ old('due_date', now()->addDays(14)->format('Y-m-d')) }}" required
                        class="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('due_date')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                {{-- Currency --}}
                @if(isset($currencies) && $currencies->count() > 1)
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1.5">Mata Uang</label>
                    <select name="currency_code"
                        class="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-800 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach($currencies as $cur)
                        <option value="{{ $cur->code }}" {{ old('currency_code', 'IDR') === $cur->code ? 'selected' : '' }}>
                            {{ $cur->code }} — {{ $cur->name }} {{ $cur->is_base ? '(Base)' : '(Kurs: '.number_format($cur->rate_to_idr,0,',','.').')' }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Notes --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1.5">Catatan <span class="text-gray-400 font-normal">(opsional)</span></label>
                    <textarea name="notes" rows="3" placeholder="Instruksi pembayaran, nomor rekening, dll."
                        class="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition">
                    Buat Invoice
                </button>
                <a href="{{ route('invoices.index') }}" class="px-6 py-2.5 rounded-xl border border-gray-200 dark:border-white/10 text-sm text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        // Auto-fill total from selected sales order
        document.getElementById('sales_order_id')?.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            const total = opt.dataset.total;
            if (total) document.getElementById('total_amount').value = Math.round(total);
        });
    </script>
    @endpush
</x-app-layout>
