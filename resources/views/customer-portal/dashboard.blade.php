<x-app-layout>
    <x-slot name="header">Portal Pelanggan</x-slot>

    {{-- Welcome --}}
    <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-900">Selamat datang, {{ $customer->name }}</h2>
        <p class="text-sm text-gray-500">{{ $customer->company ?? $customer->email }}</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Total Pesanan</p>
            <p class="text-2xl font-bold text-blue-600">{{ number_format($stats['total_orders']) }}
            </p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Pesanan Aktif</p>
            <p class="text-2xl font-bold text-amber-600">
                {{ number_format($stats['pending_orders']) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Total Invoice</p>
            <p class="text-2xl font-bold text-indigo-600">
                {{ number_format($stats['total_invoices']) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Invoice Belum Lunas</p>
            <p class="text-2xl font-bold text-red-600">{{ number_format($stats['unpaid_invoices']) }}
            </p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Saldo Terutang</p>
            <p class="text-2xl font-bold text-red-600">Rp
                {{ number_format($stats['outstanding_balance'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Tiket Aktif</p>
            <p class="text-2xl font-bold text-green-600">
                {{ number_format($stats['active_tickets']) }}</p>
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        <a href="{{ route('customer-portal.orders.index') }}"
            class="flex items-center gap-3 p-4 bg-white rounded-2xl border border-gray-200 hover:bg-gray-50 transition">
            <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
            <span class="text-sm font-medium text-gray-700">Pesanan</span>
        </a>
        <a href="{{ route('customer-portal.invoices.index') }}"
            class="flex items-center gap-3 p-4 bg-white rounded-2xl border border-gray-200 hover:bg-gray-50 transition">
            <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <span class="text-sm font-medium text-gray-700">Invoice</span>
        </a>
        <a href="{{ route('customer-portal.transactions.index') }}"
            class="flex items-center gap-3 p-4 bg-white rounded-2xl border border-gray-200 hover:bg-gray-50 transition">
            <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <span class="text-sm font-medium text-gray-700">Transaksi</span>
        </a>
        <a href="{{ route('customer-portal.tickets.index') }}"
            class="flex items-center gap-3 p-4 bg-white rounded-2xl border border-gray-200 hover:bg-gray-50 transition">
            <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <span class="text-sm font-medium text-gray-700">Support</span>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Recent Orders --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Pesanan Terbaru</h3>
                <a href="{{ route('customer-portal.orders.index') }}"
                    class="text-xs text-blue-600 hover:underline">Lihat Semua</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentOrders as $order)
                    <a href="{{ route('customer-portal.orders.show', $order) }}"
                        class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition">
                        <div>
                            <p class="text-sm font-medium text-gray-900">
                                {{ $order->number ?? '#' . $order->id }}</p>
                            <p class="text-xs text-gray-500">
                                {{ $order->created_at?->format('d/m/Y') }}</p>
                        </div>
                        <span
                            class="px-2 py-1 text-xs rounded-full font-medium
                        @if (in_array($order->status, ['completed', 'delivered'])) bg-green-100 text-green-700
                        @elseif(in_array($order->status, ['cancelled'])) bg-red-100 text-red-700
                        @else bg-blue-100 text-blue-700 @endif">{{ ucfirst($order->status) }}</span>
                    </a>
                @empty
                    <div class="px-4 py-6 text-center text-sm text-gray-400">Belum ada pesanan</div>
                @endforelse
            </div>
        </div>

        {{-- Recent Invoices --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Invoice Terbaru</h3>
                <a href="{{ route('customer-portal.invoices.index') }}"
                    class="text-xs text-blue-600 hover:underline">Lihat Semua</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentInvoices as $invoice)
                    <a href="{{ route('customer-portal.invoices.show', $invoice) }}"
                        class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition">
                        <div>
                            <p class="text-sm font-medium text-gray-900">
                                {{ $invoice->number ?? '#' . $invoice->id }}</p>
                            <p class="text-xs text-gray-500">Rp
                                {{ number_format($invoice->total_amount ?? 0, 0, ',', '.') }}</p>
                        </div>
                        <span
                            class="px-2 py-1 text-xs rounded-full font-medium
                        @if ($invoice->status === 'paid') bg-green-100 text-green-700
                        @elseif(in_array($invoice->status, ['voided', 'cancelled'])) bg-red-100 text-red-700
                        @elseif($invoice->status === 'overdue') bg-orange-100 text-orange-700
                        @else bg-amber-100 text-amber-700 @endif">{{ ucfirst($invoice->status) }}</span>
                    </a>
                @empty
                    <div class="px-4 py-6 text-center text-sm text-gray-400">Belum ada invoice</div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
