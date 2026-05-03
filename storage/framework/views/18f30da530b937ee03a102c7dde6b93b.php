<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> Checkout Langganan <?php $__env->endSlot(); ?>

    <div class="max-w-lg mx-auto">
        <div class="rounded-2xl border border-gray-200 bg-white p-8 text-center space-y-6">

            <div class="w-16 h-16 rounded-2xl bg-blue-100 flex items-center justify-center mx-auto">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            </div>

            <div>
                <h2 class="text-xl font-bold text-gray-900"><?php echo e($plan->name); ?></h2>
                <p class="text-3xl font-bold text-blue-600 mt-2">
                    Rp <?php echo e(number_format($amount, 0, ',', '.')); ?>

                </p>
                <p class="text-sm text-gray-500 mt-1">
                    Tagihan <?php echo e($billing === 'yearly' ? 'tahunan' : 'bulanan'); ?>

                </p>
            </div>

            <div class="bg-gray-50 rounded-xl p-4 text-left space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Paket</span>
                    <span class="font-medium text-gray-900"><?php echo e($plan->name); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Periode</span>
                    <span class="font-medium text-gray-900"><?php echo e($billing === 'yearly' ? '1 Tahun' : '1 Bulan'); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">No. Order</span>
                    <span class="font-mono text-xs text-gray-600"><?php echo e($orderId); ?></span>
                </div>
                <div class="flex justify-between border-t border-gray-200 pt-2">
                    <span class="font-semibold text-gray-700">Total</span>
                    <span class="font-bold text-gray-900">Rp <?php echo e(number_format($amount, 0, ',', '.')); ?></span>
                </div>
            </div>

            <?php if($gateway === 'midtrans'): ?>
            <button id="pay-btn"
                class="w-full py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold transition flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                Bayar Sekarang
            </button>
            <p class="text-xs text-gray-400">Pembayaran diproses aman oleh Midtrans</p>

            <script src="<?php echo e($isProduction ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js'); ?>"
                data-client-key="<?php echo e(config('services.midtrans.client_key')); ?>"></script>
            <script>
                document.getElementById('pay-btn').addEventListener('click', function () {
                    snap.pay('<?php echo e($snapToken); ?>', {
                        onSuccess: function(result) {
                            window.location.href = '<?php echo e(route("payment.midtrans.finish")); ?>?order_id=' + result.order_id + '&transaction_status=' + result.transaction_status;
                        },
                        onPending: function(result) {
                            window.location.href = '<?php echo e(route("subscription.index")); ?>';
                        },
                        onError: function(result) {
                            alert('Pembayaran gagal. Silakan coba lagi.');
                        },
                    });
                });
            </script>
            <?php endif; ?>

            <a href="<?php echo e(route('subscription.index')); ?>" class="block text-sm text-gray-400 hover:text-gray-600 transition">
                Batal, kembali ke halaman langganan
            </a>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\subscription\checkout.blade.php ENDPATH**/ ?>