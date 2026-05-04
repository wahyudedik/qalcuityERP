<x-app-layout>
    <x-slot name="title">{{ $plan->exists ? 'Edit Paket' : 'Tambah Paket' }} — Qalcuity ERP</x-slot>
    <x-slot name="header">{{ $plan->exists ? 'Edit Paket: ' . $plan->name : 'Tambah Paket Baru' }}</x-slot>
    <x-slot name="pageHeader">
        <a href="{{ route('super-admin.plans.index') }}"
           class="flex items-center gap-2 text-sm text-gray-500 hover:text-gray-900 px-3 py-2 rounded-xl hover:bg-gray-100 transition">
            ← Kembali
        </a>
    </x-slot>

    {{-- Mobile back --}}
    <div class="sm:hidden mb-4">
        <a href="{{ route('super-admin.plans.index') }}" class="text-sm text-gray-500">← Kembali ke daftar paket</a>
    </div>

    <div class="max-w-2xl">
        <form method="POST"
              action="{{ $plan->exists ? route('super-admin.plans.update', $plan) : route('super-admin.plans.store') }}"
              class="bg-white rounded-2xl border border-gray-200 p-6 space-y-5">
            @csrf
            @if($plan->exists) @method('PUT') @endif

            @if($errors->any())
            <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
            @endif

            @php $cls = 'w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-xl bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition'; @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Nama Paket</label>
                    <input type="text" name="name" value="{{ old('name', $plan->name) }}" required class="{{ $cls }}">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $plan->slug) }}" required class="{{ $cls }} font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Harga Bulanan (Rp)</label>
                    <input type="number" name="price_monthly" value="{{ old('price_monthly', $plan->price_monthly) }}" required min="0" class="{{ $cls }}">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Harga Tahunan (Rp)</label>
                    <input type="number" name="price_yearly" value="{{ old('price_yearly', $plan->price_yearly) }}" required min="0" class="{{ $cls }}">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Maks. User</label>
                    <input type="number" name="max_users" value="{{ old('max_users', $plan->max_users ?? 5) }}" required min="-1" class="{{ $cls }}">
                    <p class="text-xs text-gray-400 mt-1">-1 = tak terbatas</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Maks. Pesan AI/Bln</label>
                    <input type="number" name="max_ai_messages" value="{{ old('max_ai_messages', $plan->max_ai_messages ?? 100) }}" required min="-1" class="{{ $cls }}">
                    <p class="text-xs text-gray-400 mt-1">-1 = tak terbatas</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Hari Trial</label>
                    <input type="number" name="trial_days" value="{{ old('trial_days', $plan->trial_days ?? 14) }}" required min="0" class="{{ $cls }}">
                </div>
            </div>

            {{-- Features checkboxes --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Fitur Paket (centang yang tersedia)</label>
                @php
                $allFeatures = ['POS Kasir','Inventori & Stok','Penjualan & Invoice','Laporan Dasar','Pembelian & Supplier','Piutang & Hutang (AR/AP)','Multi Gudang','Quotation → SO → Invoice','CRM & Pipeline','Konsinyasi (Stok Titipan)','Komisi Sales & Target','Reimbursement Karyawan','Helpdesk & Tiket Support','Subscription Billing (Recurring)','Laporan Keuangan (Neraca, Laba Rugi)','Export Excel & PDF','HRM & Payroll','Aset & Depresiasi','Budget vs Aktual','Rekonsiliasi Bank + AI','Multi Currency','Approval Workflow','Manufaktur (BOM & MRP)','Fleet Management','Manajemen Kontrak & SLA','Landed Cost (Biaya Impor)','Project Billing (T&M/Milestone)','AI Forecasting Dashboard','POS Thermal Printer & Scanner','E-Commerce (Shopee/Tokopedia)','AI Anomaly Detection','Simulasi Bisnis (What If)','Multi Company & Konsolidasi','Zero Input OCR','Custom Integrasi API','WhatsApp Bot Notifikasi','Digital Signature','Prioritas Support'];
                $currentFeatures = old('features_list', $plan->features ?? []);
                @endphp
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-1 max-h-64 overflow-y-auto border border-gray-200 rounded-xl p-3 bg-gray-50">
                    @foreach($allFeatures ?? [] as $feat)
                    <label class="flex items-center gap-2 cursor-pointer py-1 px-1 rounded hover:bg-gray-100">
                        <input type="checkbox" name="features_list[]" value="{{ $feat }}" {{ in_array($feat, $currentFeatures) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-xs text-gray-700">{{ $feat }}</span>
                    </label>
                    @endforeach
                </div>
                <input type="text" name="features_custom" placeholder="Fitur tambahan (pisah koma)" class="{{ $cls }} text-xs mt-2">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Urutan Tampil</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $plan->sort_order ?? 0) }}" required min="0" class="{{ $cls }}">
                </div>
                <div class="flex items-end pb-2.5">
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $plan->is_active ?? true) ? 'checked' : '' }}
                            class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700 font-medium">Paket aktif</span>
                    </label>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-2 border-t border-gray-200">
                <a href="{{ route('super-admin.plans.index') }}" class="w-full sm:w-auto text-center text-sm text-gray-600 px-4 py-2.5 rounded-xl hover:bg-gray-100 transition">Batal</a>
                <button type="submit" class="w-full sm:w-auto text-sm bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-xl transition">
                    {{ $plan->exists ? 'Simpan Perubahan' : 'Buat Paket' }}
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
