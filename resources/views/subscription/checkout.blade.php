<x-app-layout>
    <x-slot name="header">Checkout Langganan</x-slot>

    <div class="max-w-lg mx-auto">
        <div class="rounded-2xl border border-gray-200 bg-white p-8 text-center space-y-6">

            <div class="w-16 h-16 rounded-2xl bg-blue-100 flex items-center justify-center mx-auto">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
            </div>

            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ $plan->name }}</h2>
                <p class="text-3xl font-bold text-blue-600 mt-2">
                    Rp {{ number_format($amount, 0, ',', '.') }}
                </p>
                <p class="text-sm text-gray-500 mt-1">
                    Tagihan {{ $billing === 'yearly' ? 'tahunan' : 'bulanan' }}
                </p>
            </div>

            <div class="bg-gray-50 rounded-xl p-4 text-left space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Paket</span>
                    <span class="font-medium text-gray-900">{{ $plan->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Periode</span>
                    <span class="font-medium text-gray-900">{{ $billing === 'yearly' ? '1 Tahun' : '1 Bulan' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">No. Order</span>
                    <span class="font-mono text-xs text-gray-600">{{ $orderId }}</span>
                </div>
                <div class="flex justify-between border-t border-gray-200 pt-2">
                    <span class="font-semibold text-gray-700">Total</span>
                    <span class="font-bold text-gray-900">Rp {{ number_format($amount, 0, ',', '.') }}</span>
                </div>
            </div>

            @if ($gateway === 'midtrans')
                <button id="pay-btn"
                    class="w-full py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold transition flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    Bayar Sekarang
                </button>
                <p class="text-xs text-gray-400">Pembayaran diproses aman oleh Midtrans</p>

                <script
                    src="{{ $isProduction ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
                    data-client-key="{{ config('services.midtrans.client_key') }}"></script>
                <script>
                    document.getElementById('pay-btn').addEventListener('click', function() {
                        snap.pay('{{ $snapToken }}', {
                            onSuccess: function(result) {
                                window.location.href = '{{ route('payment.midtrans.finish') }}?order_id=' + result
                                    .order_id + '&transaction_status=' + result.transaction_status;
                            },
                            onPending: function(result) {
                                window.location.href = '{{ route('subscription.index') }}';
                            },
                            onError: function(result) {
                                Dialog.warning('Pembayaran gagal. Silakan coba lagi.');
                            },
                        });
                    });
                </script>
            @endif

            <a href="{{ route('subscription.index') }}"
                class="block text-sm text-gray-400 hover:text-gray-600 transition">
                Batal, kembali ke halaman langganan
            </a>
        </div>
    </div>
</x-app-layout>
